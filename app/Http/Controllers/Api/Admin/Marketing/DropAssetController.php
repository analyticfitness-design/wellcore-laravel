<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\Marketing;

use App\Exceptions\Marketing\InvalidDropSchema;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Marketing\UploadDropAssetRequest;
use App\Http\Resources\Admin\Marketing\AdminDropResource;
use App\Models\CoachContentDrop;
use App\Services\Marketing\DropAssetStorage;
use App\Services\Marketing\DropSchemaValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class DropAssetController extends Controller
{
    public function __construct(
        private readonly DropAssetStorage $storage,
        private readonly DropSchemaValidator $validator,
    ) {}

    public function store(UploadDropAssetRequest $request, CoachContentDrop $drop): AdminDropResource
    {
        Gate::authorize('admin.marketing.manageAssets', $drop);

        $rawOrder = $request->input('order');
        $order    = ($rawOrder === null || $rawOrder === '') ? null : (int) $rawOrder;

        $asset = $this->storage->store(
            drop:         $drop,
            file:         $request->file('file'),
            role:         $request->input('role'),
            linkedTo:     $request->linkedTo(),
            caption:      $request->input('caption'),
            order:        $order,
            notes:        $request->input('notes'),
            uploadedById: Auth::id(),
        );

        $fresh = DB::transaction(function () use ($drop, $asset) {
            $locked = CoachContentDrop::lockForUpdate()->findOrFail($drop->id);
            $content = $locked->content ?? [];
            $assets  = $content['assets'] ?? [];
            $assets[] = $asset->toArray();
            $content['assets'] = array_values($assets);

            // Validate the patched content against the schema before persisting.
            try {
                $this->validator->validate($content);
            } catch (InvalidDropSchema $e) {
                $this->storage->deleteFile($drop, $asset->toArray());
                abort(422, 'Asset patch invalida el schema: ' . ($e->errors[0]['message'] ?? 'unknown'));
            }

            $locked->content = $content;
            $locked->save();
            return $locked->fresh()->load('coach', 'pieceStates');
        });

        $this->forgetCache($fresh);

        return new AdminDropResource($fresh);
    }

    public function destroy(Request $request, CoachContentDrop $drop, string $assetId): AdminDropResource|JsonResponse
    {
        Gate::authorize('admin.marketing.manageAssets', $drop);

        $fresh = DB::transaction(function () use ($drop, $assetId) {
            $locked  = CoachContentDrop::lockForUpdate()->findOrFail($drop->id);
            $content = $locked->content ?? [];
            $assets  = $content['assets'] ?? [];

            $idx = null;
            $removed = null;
            foreach ($assets as $i => $a) {
                if (($a['id'] ?? null) === $assetId) {
                    $idx = $i;
                    $removed = $a;
                    break;
                }
            }
            if ($idx === null) {
                abort(404, 'Asset no encontrado en este drop.');
            }

            array_splice($assets, $idx, 1);
            $content['assets'] = array_values($assets);

            $locked->content = $content;
            $locked->save();

            $this->storage->deleteFile($locked, $removed);

            return $locked->fresh()->load('coach', 'pieceStates');
        });

        $this->forgetCache($fresh);

        return new AdminDropResource($fresh);
    }

    private function forgetCache(CoachContentDrop $drop): void
    {
        Cache::forget("coach_drop_v3:{$drop->coach_id}:{$drop->iso_year}:{$drop->iso_week}");
    }
}
