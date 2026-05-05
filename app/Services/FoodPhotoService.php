<?php

namespace App\Services;

use App\Models\AssignedPlan;
use App\Models\Client;
use App\Models\ClientXp;
use App\Models\FoodPhoto;
use App\Models\HabitLog;
use App\Models\WellcoreNotification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class FoodPhotoService
{
    public function store(
        Client $client,
        UploadedFile $file,
        string $mealName,
        int $mealIndex,
        string $photoDate,
        ?string $clientNote = null
    ): FoodPhoto {
        Validator::make(['photo' => $file], [
            'photo' => 'required|file|mimetypes:image/jpeg,image/jpg,image/png,image/webp,image/heic,image/heif|max:15360',
        ])->validate();

        $existing = FoodPhoto::withoutGlobalScopes()
            ->where('client_id', $client->id)
            ->where('meal_index', $mealIndex)
            ->where('photo_date', $photoDate)
            ->first();

        $oldFilename = $existing?->filename;
        $newFilename = $this->processImage($file, $client->id);
        $note = $clientNote !== null ? trim($clientNote) : null;
        $note = $note === '' ? null : $note;

        try {
            $photo = DB::transaction(function () use ($client, $existing, $mealName, $mealIndex, $photoDate, $newFilename, $file, $note) {
                $payload = [
                    'client_id'  => $client->id,
                    'meal_name'  => $mealName,
                    'meal_index' => $mealIndex,
                    'photo_date' => $photoDate,
                    'filename'   => $newFilename,
                    'file_size'  => $file->getSize() ?: null,
                ];
                if ($note !== null) {
                    $payload['client_note'] = $note;
                }

                if ($existing) {
                    $existing->fill($payload)->save();

                    return $existing;
                }

                return FoodPhoto::create($payload);
            });
        } catch (QueryException $e) {
            if (($e->errorInfo[1] ?? null) === 1062) {
                Storage::disk('public')->delete($newFilename);

                return FoodPhoto::withoutGlobalScopes()
                    ->where('client_id', $client->id)
                    ->where('meal_index', $mealIndex)
                    ->where('photo_date', $photoDate)
                    ->firstOrFail();
            }
            Storage::disk('public')->delete($newFilename);
            throw $e;
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($newFilename);
            throw $e;
        }

        if ($oldFilename && $oldFilename !== $newFilename) {
            Storage::disk('public')->delete($oldFilename);
        }

        if (! $photo->xp_awarded) {
            $this->awardXp($client, $photo, $photoDate);
        }

        $this->notifyCoach($client, $mealName);

        return $photo->refresh();
    }

    public function delete(FoodPhoto $photo): void
    {
        if ($photo->coach_seen) {
            throw new AuthorizationException('Tu coach ya revisó esta foto y no puede eliminarse.');
        }

        $filename = $photo->filename;

        DB::transaction(function () use ($photo) {
            $photo->delete();
        });

        Storage::disk('public')->delete($filename);
    }

    private function processImage(UploadedFile $file, int $clientId): string
    {
        $manager = new ImageManager(new Driver);
        // Intervention v4.0.1 expone decode/decodePath/decodeBinary; algunas versiones
        // mas nuevas usan read(). Probamos read() primero (oficial v4 docs) y
        // fallback a decode() si no existe.
        if (method_exists($manager, 'read')) {
            $image = $manager->read($file->getRealPath());
        } else {
            $image = $manager->decode($file->getRealPath());
        }

        if (method_exists($image, 'orientate')) {
            $image->orientate();
        }
        $image->scaleDown(width: 1200);

        $filename = sprintf('food-photos/%d/%s.jpg', $clientId, Str::uuid());
        $encoded = $image->toJpeg(85);

        Storage::disk('public')->put($filename, (string) $encoded);

        return $filename;
    }

    private function awardXp(Client $client, FoodPhoto $photo, string $photoDate): void
    {
        try {
            DB::transaction(function () use ($client, $photo) {
                ClientXp::firstOrCreate(
                    ['client_id' => $client->id],
                    ['xp_total' => 0, 'level' => 1, 'streak_days' => 0]
                );
                ClientXp::where('client_id', $client->id)->increment('xp_total', 15);
                $photo->update(['xp_awarded' => true]);
            });
        } catch (\Throwable $e) {
            Log::warning('FoodPhotoService::awardXp skipped', [
                'client_id' => $client->id,
                'photo_id'  => $photo->id,
                'error'     => $e->getMessage(),
            ]);

            return;
        }

        $this->maybeAwardDayBonus($client, $photoDate);
    }

    private function maybeAwardDayBonus(Client $client, string $photoDate): void
    {
        $lockKey = "food_day_bonus:{$client->id}:{$photoDate}";

        Cache::lock($lockKey, 30)->block(5, function () use ($client, $photoDate) {
            $alreadyAwarded = HabitLog::where('client_id', $client->id)
                ->where('habit_type', 'food_day_bonus')
                ->where('log_date', $photoDate)
                ->exists();

            if ($alreadyAwarded) {
                return;
            }

            $expectedMeals = $this->countExpectedMeals($client);
            if ($expectedMeals === 0) {
                return;
            }

            $uploadedToday = FoodPhoto::withoutGlobalScopes()
                ->where('client_id', $client->id)
                ->where('photo_date', $photoDate)
                ->count();

            if ($uploadedToday < $expectedMeals) {
                return;
            }

            try {
                HabitLog::create([
                    'client_id'  => $client->id,
                    'log_date'   => $photoDate,
                    'habit_type' => 'food_day_bonus',
                    'value'      => 30,
                ]);
                ClientXp::where('client_id', $client->id)->increment('xp_total', 30);
            } catch (\Throwable $e) {
                Log::warning('FoodPhotoService::dayBonus skipped', [
                    'client_id' => $client->id,
                    'date'      => $photoDate,
                    'error'     => $e->getMessage(),
                ]);
            }
        });
    }

    private function countExpectedMeals(Client $client): int
    {
        try {
            $plan = AssignedPlan::where('client_id', $client->id)
                ->where('plan_type', 'nutricion')
                ->where('active', true)
                ->latest()
                ->first();
        } catch (\Throwable $e) {
            return 0;
        }

        if (! $plan || ! $plan->content) {
            return 0;
        }

        return count(NutritionPlanParser::extractMeals(is_array($plan->content) ? $plan->content : []));
    }

    private function notifyCoach(Client $client, string $mealName): void
    {
        try {
            $coachId = AssignedPlan::where('client_id', $client->id)
                ->where('plan_type', 'nutricion')
                ->where('active', true)
                ->value('assigned_by');

            $coachId ??= AssignedPlan::where('client_id', $client->id)
                ->where('active', true)
                ->value('assigned_by');
        } catch (\Throwable $e) {
            return;
        }

        if (! $coachId) {
            return;
        }

        $cacheKey = "food_notif:{$coachId}:{$client->id}";
        if (Cache::has($cacheKey)) {
            return;
        }
        Cache::put($cacheKey, true, 900);

        try {
            WellcoreNotification::create([
                'user_type' => 'admin',
                'user_id'   => $coachId,
                'type'      => 'food_photo_uploaded',
                'title'     => 'Foto de comida nueva',
                'body'      => "{$client->name} subió foto de {$mealName}",
                'link'      => '/coach/food-photos',
            ]);
        } catch (\Throwable $e) {
            Log::warning('FoodPhotoService::notifyCoach skipped', [
                'coach_id'  => $coachId,
                'client_id' => $client->id,
                'error'     => $e->getMessage(),
            ]);
        }
    }
}
