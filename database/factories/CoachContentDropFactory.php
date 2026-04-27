<?php
declare(strict_types=1);
namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\CoachContentDrop;
use Illuminate\Database\Eloquent\Factories\Factory;

final class CoachContentDropFactory extends Factory
{
    protected $model = CoachContentDrop::class;

    public function definition(): array
    {
        $monday = now()->startOfWeek();
        return [
            'coach_id'        => Admin::factory()->state(['role' => UserRole::Coach->value]),
            'iso_year'        => (int) $monday->isoFormat('GGGG'),
            'iso_week'        => (int) $monday->isoFormat('W'),
            'week_starts_on'  => $monday->toDateString(),
            'status'          => 'pending',
            'content'         => self::stubContent(),
            'intake_snapshot' => ['brand_name' => 'Stub Coach'],
            'schema_version'  => 'coach_drop_v1',
        ];
    }

    public function pending(): static { return $this->state(['status' => 'pending']); }
    public function inReview(): static { return $this->state(['status' => 'in_review', 'generated_at' => now()]); }
    public function approved(): static { return $this->state(['status' => 'approved', 'approved_at' => now()]); }
    public function ready(): static { return $this->state(['status' => 'ready', 'approved_at' => now(), 'ready_at' => now()]); }
    public function inProgress(): static { return $this->state(['status' => 'in_progress']); }
    public function completed(): static { return $this->state(['status' => 'completed', 'completed_at' => now()]); }
    public function archived(): static { return $this->state(['status' => 'archived', 'completed_at' => now()->subDays(40)]); }

    private static function stubContent(): array
    {
        $timecodes = [
            ['time'=>'00:00-00:03','dialogue'=>'D','visual'=>'V','edit_notes'=>'E'],
            ['time'=>'00:03-00:08','dialogue'=>'D2','visual'=>'V2','edit_notes'=>'E2'],
            ['time'=>'00:08-00:30','dialogue'=>'D3','visual'=>'V3','edit_notes'=>'E3'],
        ];
        $reel = fn(string $key, string $type) => [
            'key'=>$key,'type'=>$type,'title'=>'Stub Reel',
            'format_meta'=>['duration_sec_min'=>30,'duration_sec_max'=>45,'platforms'=>['instagram'],'bpm_hint'=>'100'],
            'hook'=>['text'=>'Hook stub','rationale'=>'Rationale stub'],
            'timecode_table'=>$timecodes,
            'caption'=>'Caption stub','music_note'=>'Music stub','production_notes'=>'Notes stub',
        ];
        $days    = ['LUN','MAR','MIE','JUE','VIE','SAB','DOM'];
        $pillars = ['activacion','nutricion','spotlight','bts','qa','motivacion','reset'];
        return [
            'schema_version' => 'coach_drop_v1',
            'brief'          => ['title'=>'Brief stub','objective'=>'Obj stub','priority_offer'=>'metodo','key_message'=>'Key stub','target_metric'=>'Metric stub','weekly_theme'=>'Theme stub','framing_copy'=>'Framing stub'],
            'reels'          => [$reel('reel_1','educativo'),$reel('reel_2','conversion')],
            'stories'        => array_map(fn($d,$p)=>['day'=>$d,'pillar'=>$p,'slides'=>[['kind'=>'text','text'=>'T','visual_hint'=>'V','sticker'=>'none']],'dm_followup_hint'=>''], $days, $pillars),
            'checklist'      => ['phases'=>[
                ['key'=>'pre','title'=>'Pre','items'=>[['title'=>'X','detail'=>'D']]],
                ['key'=>'cam','title'=>'Cam','items'=>[['title'=>'X','detail'=>'D']]],
                ['key'=>'edit','title'=>'Edit','items'=>[['title'=>'X','detail'=>'D']]],
                ['key'=>'pub','title'=>'Pub','items'=>[['title'=>'X','detail'=>'D']]],
            ]],
            'bank'     => ['alt_hooks'=>['a','b','c','d','e'],'alt_ctas'=>['x','y','z'],'alt_captions'=>['1','2','3']],
            'hashtags' => ['sets'=>[['name'=>'set1','tags'=>['#fitness']]]],
        ];
    }
}
