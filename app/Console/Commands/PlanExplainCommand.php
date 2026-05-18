<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Kb\ComposedPlan;
use App\Models\Kb\DecisionRule;
use App\Models\Kb\Methodology;
use App\Models\Kb\Principle;
use Illuminate\Console\Command;

/**
 * plan:explain — muestra el razonamiento del motor v2 para un composed_plan.
 *
 * Para cada audit row, explica:
 *   1. SELECT — qué decision_rule matcheó y por qué (rationale)
 *   2. COMPOSE — qué methodology se usó (description + target_*)
 *   3. PRINCIPLES — qué principles se inyectaron y por qué (scoring breakdown)
 *   4. VALIDATE — qué violations detectó y de qué severidad
 *   5. PERSIST — status final + path de export
 *
 * Útil para:
 *   - Daniel: entender por qué el motor escogió X y no Y
 *   - Debugging: ver scoring de decisión
 *   - Documentación viva del motor
 */
final class PlanExplainCommand extends Command
{
    protected $signature = 'plan:explain {composed_id : ID en wellcore_kb.composed_plans}
                            {--json : output JSON estructurado}';

    protected $description = 'Explica el razonamiento del motor para un composed_plan (SELECT + COMPOSE + PRINCIPLES + VALIDATE).';

    public function handle(): int
    {
        $id = (int) $this->argument('composed_id');
        $cp = ComposedPlan::find($id);
        if (! $cp) {
            $this->error("composed_plans #$id no encontrado.");
            return 2;
        }

        $explanation = $this->build($cp);

        if ($this->option('json')) {
            $this->line(json_encode($explanation, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            return 0;
        }

        $this->renderHuman($explanation);
        return 0;
    }

    private function build(ComposedPlan $cp): array
    {
        $plan = $cp->planJson();
        $profile = $cp->profile_json ?? [];

        return [
            'audit_id' => $cp->id,
            'profile' => $profile,
            'select_stage' => $this->explainSelect($cp, $profile),
            'compose_stage' => $this->explainCompose($cp),
            'principles_injected' => $this->explainPrinciples($plan['principios_aplicados'] ?? [], $profile, $cp->plan_type),
            'validate_stage' => $this->explainValidate($cp),
            'persist_stage' => [
                'status' => $cp->status,
                'export_path' => $cp->export_path,
                'created_at' => (string) $cp->created_at,
            ],
        ];
    }

    private function explainSelect(ComposedPlan $cp, array $profile): array
    {
        $rule = DecisionRule::where('then_methodology_id', function ($q) use ($cp) {
            $q->select('id')->from('methodologies')->where('slug', $cp->methodology_slug);
        })->orderByDesc('confidence')->first();

        return [
            'methodology_slug' => $cp->methodology_slug,
            'matched_rule' => $rule ? [
                'name' => $rule->name,
                'confidence' => (float) $rule->confidence,
                'rationale' => $rule->rationale,
                'when_json' => $rule->when_json,
            ] : null,
        ];
    }

    private function explainCompose(ComposedPlan $cp): array
    {
        $methodology = Methodology::where('slug', $cp->methodology_slug)->first();
        if ($methodology === null) {
            return ['error' => 'Methodology no encontrada'];
        }
        return [
            'name' => $methodology->name,
            'vertical' => $methodology->vertical,
            'target_days' => "{$methodology->target_days_min}-{$methodology->target_days_max}",
            'target_level' => $methodology->target_level,
            'target_goal' => $methodology->target_goal,
            'periodization' => $methodology->periodization_pattern,
            'description_snippet' => mb_substr((string) $methodology->description, 0, 200),
        ];
    }

    /**
     * @param string[] $appliedSlugs
     */
    private function explainPrinciples(array $appliedSlugs, array $profile, string $planType): array
    {
        if ($appliedSlugs === []) {
            return [];
        }

        $principles = Principle::whereIn('slug', $appliedSlugs)->get()->keyBy('slug');
        $out = [];
        foreach ($appliedSlugs as $slug) {
            $p = $principles->get($slug);
            if ($p === null) {
                continue;
            }
            $tags = $p->tags ?? [];

            // Breakdown del scoring (similar al PrincipleInjector)
            $score = 0;
            $reasons = [];

            if ($p->vertical === $planType) {
                $score += 20;
                $reasons[] = "vertical match (+20)";
            }

            $contextTags = $this->buildContextTags($profile);
            $overlap = count(array_intersect($tags, $contextTags));
            if ($overlap > 0) {
                $score += $overlap * 5;
                $reasons[] = "tag overlap × {$overlap} (+" . ($overlap * 5) . ")";
            }

            if (in_array('fundamental', $tags, true)) {
                $score += 10;
                $reasons[] = "fundamental tag (+10)";
            }

            $universalTags = ['adherencia', 'prevencion', 'prevencion_lesiones', 'realismo', 'seguridad'];
            $universalOverlap = count(array_intersect($tags, $universalTags));
            if ($universalOverlap > 0) {
                $score += $universalOverlap * 2;
                $reasons[] = "universal tags × {$universalOverlap} (+" . ($universalOverlap * 2) . ")";
            }

            $out[] = [
                'slug' => $slug,
                'name' => $p->name,
                'tags' => $tags,
                'score' => $score,
                'reasons' => $reasons,
            ];
        }

        return $out;
    }

    private function explainValidate(ComposedPlan $cp): array
    {
        $pre = $cp->lint_result_pre_json ?? null;
        $post = $cp->lint_result_post_json ?? null;
        return [
            'violations_before_fix' => $cp->violations_before,
            'violations_after_fix' => $cp->violations_after,
            'pre_summary' => $pre['summary'] ?? null,
            'post_summary' => $post['summary'] ?? null,
            'top_violations' => array_slice($post['violations'] ?? $pre['violations'] ?? [], 0, 3),
        ];
    }

    /**
     * @return string[]
     */
    private function buildContextTags(array $profile): array
    {
        $tags = [];
        foreach (['level', 'goal'] as $k) {
            if (! empty($profile[$k])) {
                $tags[] = (string) $profile[$k];
            }
        }
        if (! empty($profile['injuries'])) {
            $tags[] = 'lesion';
            $tags[] = 'rehabilitacion';
            $tags[] = 'prevencion_lesiones';
        }
        return array_merge($tags, ['macros', 'timing', 'recuperacion', 'adherencia', 'progresion']);
    }

    private function renderHuman(array $exp): void
    {
        $this->info('═══ plan:explain — audit #' . $exp['audit_id'] . ' ═══');
        $this->newLine();

        $this->info('Profile:');
        $this->line('  ' . json_encode($exp['profile'], JSON_UNESCAPED_UNICODE));
        $this->newLine();

        $this->info('Stage 2 — SELECT:');
        $sel = $exp['select_stage'];
        $this->line('  methodology: ' . $sel['methodology_slug']);
        if ($sel['matched_rule']) {
            $r = $sel['matched_rule'];
            $this->line('  rule:        ' . $r['name']);
            $this->line('  confidence:  ' . $r['confidence']);
            $this->line('  rationale:   ' . mb_substr((string) $r['rationale'], 0, 200));
        }
        $this->newLine();

        $this->info('Stage 3 — COMPOSE (methodology details):');
        $cmp = $exp['compose_stage'];
        $this->line("  name:         {$cmp['name']}");
        $this->line("  vertical:     {$cmp['vertical']}");
        $this->line("  target:       {$cmp['target_days']} días · level={$cmp['target_level']} · goal={$cmp['target_goal']}");
        $this->newLine();

        $principles = $exp['principles_injected'];
        $this->info('Principles inyectados (' . count($principles) . '):');
        foreach ($principles as $p) {
            $this->line(sprintf('  · [%d pts] %s — %s', $p['score'], $p['slug'], $p['name']));
            foreach ($p['reasons'] as $reason) {
                $this->line('      → ' . $reason);
            }
        }
        $this->newLine();

        $this->info('Stage 4-6 — VALIDATE/AUTOFIX/VERIFY:');
        $val = $exp['validate_stage'];
        $this->line("  Violations pre-fix:  {$val['violations_before_fix']}");
        $this->line("  Violations post-fix: {$val['violations_after_fix']}");
        if (! empty($val['top_violations'])) {
            $this->line('  Top violations:');
            foreach ($val['top_violations'] as $v) {
                $this->line("    · [{$v['severity']}] {$v['rule_code']}: " . mb_substr((string) ($v['message'] ?? ''), 0, 100));
            }
        }
        $this->newLine();

        $this->info('Stage 5 — PERSIST:');
        $p = $exp['persist_stage'];
        $this->line("  status:      {$p['status']}");
        $this->line('  export:      ' . ($p['export_path'] ?? '—'));
        $this->line("  created_at:  {$p['created_at']}");
    }
}
