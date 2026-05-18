<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\LintEngine\AutoFixEngine;
use App\Services\LintEngine\Fixers\FuzzyReplaceFixer;
use App\Services\LintEngine\Fixers\RegexReplaceTableFixer;
use App\Services\LintEngine\Fixers\RemoveSentenceContainingTriggerFixer;
use App\Services\LintEngine\Fixers\RenameKeysFixer;
use App\Services\LintEngine\Fixers\RewriteDomainFixer;
use App\Services\LintEngine\FixerRegistry;
use App\Services\LintEngine\JsonPath\JsonPathResolver;
use App\Services\LintEngine\JsonPath\PathMutator;
use App\Services\LintEngine\LintEngine;
use App\Services\LintEngine\Validators\AllowedValuesValidator;
use App\Services\LintEngine\Validators\AntiAILeakValidator;
use App\Services\LintEngine\Validators\ArrayNonEmptyValidator;
use App\Services\LintEngine\Validators\ArrayOfStringsValidator;
use App\Services\LintEngine\Validators\CardioMinPerSessionValidator;
use App\Services\LintEngine\Validators\CooldownMissingValidator;
use App\Services\LintEngine\Validators\CreatinaMissingValidator;
use App\Services\LintEngine\Validators\ExistsAndIntPositiveValidator;
use App\Services\LintEngine\Validators\ExistsAndNonEmptyValidator;
use App\Services\LintEngine\Validators\ExerciseGifFromV2RepoValidator;
use App\Services\LintEngine\Validators\ExistsInEachValidator;
use App\Services\LintEngine\Validators\ExternalHeadValidator;
use App\Services\LintEngine\Validators\HydrationTargetValidator;
use App\Services\LintEngine\Validators\MacrosCoherenciaValidator;
use App\Services\LintEngine\Validators\RestDayValidator;
use App\Services\LintEngine\Validators\SleepTrackingValidator;
use App\Services\LintEngine\Validators\UnilateralBalanceValidator;
use App\Services\LintEngine\Validators\VitaminaD3MissingValidator;
use App\Services\LintEngine\Validators\WarmupMinDurationValidator;
use App\Services\LintEngine\Validators\FrequencyMatchesMethodologyValidator;
use App\Services\LintEngine\Validators\HasRequiredKeysValidator;
use App\Services\LintEngine\Validators\MinVolumePerMuscleValidator;
use App\Services\LintEngine\Validators\ObjectKeysNotInValidator;
use App\Services\LintEngine\Validators\ObjectWithKeysValidator;
use App\Services\LintEngine\Validators\Omega3MissingValidator;
use App\Services\LintEngine\Validators\PercentageSameSetsRepsValidator;
use App\Services\LintEngine\Validators\ProgressionAdequateValidator;
use App\Services\LintEngine\Validators\ProteinaDailyTargetValidator;
use App\Services\LintEngine\Validators\PushPullBalanceValidator;
use App\Services\LintEngine\Validators\RegexPatternsValidator;
use App\Services\LintEngine\Validators\StartsWithValidator;
use App\Services\LintEngine\Validators\UrlMatchesPatternValidator;
use App\Services\LintEngine\Validators\VolumeBalancePerMuscleValidator;
use App\Services\LintEngine\Validators\WarmupLesionSpecificValidator;
use App\Services\LintEngine\Validators\WarmupMissingValidator;
use App\Services\LintEngine\Validators\WeeksAreIdenticalValidator;
use App\Services\LintEngine\ValidatorRegistry;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

/**
 * Registra el LintEngine + sus 16 validators como singleton.
 *
 * Resolución: la app puede pedir LintEngine via DI y lo recibe configurado.
 * Para agregar validators nuevos, registrarlos en boot() de este provider.
 */
final class LintEngineServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(JsonPathResolver::class);
        $this->app->singleton(PathMutator::class);

        $this->app->singleton(ValidatorRegistry::class, function ($app) {
            $registry = new ValidatorRegistry();
            $resolver = $app->make(JsonPathResolver::class);

            $registry->register(new ExistsAndNonEmptyValidator($resolver));
            $registry->register(new ExistsAndIntPositiveValidator($resolver));
            $registry->register(new ObjectWithKeysValidator($resolver));
            $registry->register(new HasRequiredKeysValidator($resolver));
            $registry->register(new ObjectKeysNotInValidator($resolver));
            $registry->register(new ExistsInEachValidator($resolver));
            $registry->register(new StartsWithValidator($resolver));
            $registry->register(new ArrayOfStringsValidator($resolver));
            $registry->register(new ArrayNonEmptyValidator($resolver));
            $registry->register(new AllowedValuesValidator($resolver));
            $registry->register(new PercentageSameSetsRepsValidator($resolver));
            $registry->register(new WeeksAreIdenticalValidator($resolver));
            $registry->register(new CardioMinPerSessionValidator($resolver));
            $registry->register(new RegexPatternsValidator($resolver));
            $registry->register(new ExternalHeadValidator($resolver));
            $registry->register(new UrlMatchesPatternValidator($resolver));
            $registry->register(new VolumeBalancePerMuscleValidator($resolver));
            $registry->register(new ProgressionAdequateValidator($resolver));
            $registry->register(new FrequencyMatchesMethodologyValidator($resolver));
            $registry->register(new MinVolumePerMuscleValidator($resolver));
            $registry->register(new PushPullBalanceValidator($resolver));
            $registry->register(new WarmupMissingValidator($resolver));
            $registry->register(new CreatinaMissingValidator($resolver));
            $registry->register(new Omega3MissingValidator($resolver));
            $registry->register(new ProteinaDailyTargetValidator($resolver));
            $registry->register(new WarmupLesionSpecificValidator($resolver));
            $registry->register(new HydrationTargetValidator($resolver));
            $registry->register(new CooldownMissingValidator($resolver));
            $registry->register(new MacrosCoherenciaValidator($resolver));
            $registry->register(new SleepTrackingValidator($resolver));
            $registry->register(new RestDayValidator($resolver));
            $registry->register(new VitaminaD3MissingValidator($resolver));
            $registry->register(new WarmupMinDurationValidator($resolver));
            $registry->register(new UnilateralBalanceValidator($resolver));
            $registry->register(new ExerciseGifFromV2RepoValidator($resolver));
            $registry->register(new AntiAILeakValidator($resolver));

            return $registry;
        });

        $this->app->singleton(LintEngine::class, function ($app) {
            return new LintEngine(
                registry: $app->make(ValidatorRegistry::class),
                logger: $app->make(LoggerInterface::class),
            );
        });

        $this->app->singleton(FixerRegistry::class, function ($app) {
            $registry = new FixerRegistry();
            $mutator = $app->make(PathMutator::class);

            $registry->register(new RenameKeysFixer($mutator));
            $registry->register(new FuzzyReplaceFixer($mutator));
            $registry->register(new RegexReplaceTableFixer($mutator));
            $registry->register(new RewriteDomainFixer($mutator));
            $registry->register(new RemoveSentenceContainingTriggerFixer($mutator));

            return $registry;
        });

        $this->app->singleton(AutoFixEngine::class, function ($app) {
            return new AutoFixEngine(
                fixers: $app->make(FixerRegistry::class),
                logger: $app->make(LoggerInterface::class),
            );
        });
    }
}
