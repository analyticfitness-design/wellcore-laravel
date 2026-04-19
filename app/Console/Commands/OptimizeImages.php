<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Encoders\JpegEncoder;
use Spatie\ImageOptimizer\OptimizerChainFactory;
use Symfony\Component\Finder\Finder;
use Throwable;

class OptimizeImages extends Command
{
    protected $signature = 'wellcore:optimize-images
                            {--dry-run : Solo estimar, no escribir cambios}
                            {--path= : Directorio relativo a public/ a procesar (ej: images)}
                            {--max-width=2000 : Ancho maximo}
                            {--quality=85 : Calidad WebP/JPG}';

    protected $description = 'Optimiza PNG/JPG en public/, genera WebP, redimensiona y respalda originales.';

    public function handle(): int
    {
        $dryRun  = (bool) $this->option('dry-run');
        $maxW    = (int) $this->option('max-width');
        $quality = (int) $this->option('quality');

        $roots = [];
        if ($only = $this->option('path')) {
            $roots[] = public_path($only);
        } else {
            $roots[] = public_path('images');
            $roots[] = public_path('uploads');
        }
        $roots = array_filter($roots, fn ($p) => is_dir($p));

        if (empty($roots)) {
            $this->error('No se encontraron directorios para procesar.');
            return self::FAILURE;
        }

        $manager = new ImageManager(new GdDriver());

        $optimizer = null;
        try {
            $optimizer = OptimizerChainFactory::create();
        } catch (Throwable $e) {
            $this->warn('spatie/image-optimizer no disponible (binarios faltantes): ' . $e->getMessage());
        }

        $backupRoot = storage_path('image-backups');
        if (! $dryRun && ! is_dir($backupRoot)) {
            File::makeDirectory($backupRoot, 0755, true);
        }

        $finder = (new Finder())->files()->in($roots)->name('/\.(png|jpe?g)$/i');

        $rows = [];
        $totalBefore = 0;
        $totalAfter  = 0;
        $bar = $this->output->createProgressBar(iterator_count($finder));
        $finder = (new Finder())->files()->in($roots)->name('/\.(png|jpe?g)$/i'); // re-init
        $bar->start();

        foreach ($finder as $file) {
            $path     = $file->getRealPath();
            $relative = ltrim(str_replace(public_path(), '', $path), DIRECTORY_SEPARATOR);
            $sizeBefore = filesize($path) ?: 0;
            $totalBefore += $sizeBefore;

            try {
                $img = $manager->decodePath($path);
                $w   = $img->width();
                $estimateAfter = $sizeBefore;

                if ($dryRun) {
                    // Conservative estimate: 30% reduction PNG via reopt, 60% via WebP, 50% if resize triggered
                    $reduction = 0.30;
                    if ($w > $maxW) $reduction += 0.30;
                    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                    if ($ext !== 'png') $reduction = max($reduction, 0.40);
                    $estimateAfter = (int) ($sizeBefore * (1 - $reduction));
                } else {
                    // Backup
                    $backupPath = $backupRoot . DIRECTORY_SEPARATOR . $relative;
                    $backupDir  = dirname($backupPath);
                    if (! is_dir($backupDir)) File::makeDirectory($backupDir, 0755, true);
                    if (! file_exists($backupPath)) copy($path, $backupPath);

                    // Resize
                    if ($w > $maxW) {
                        $img->scaleDown(width: $maxW);
                    }

                    // WebP sibling
                    $webpPath = preg_replace('/\.(png|jpe?g)$/i', '.webp', $path);
                    $img->encode(new WebpEncoder(quality: $quality))->save($webpPath);

                    // Re-encode original
                    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                    if ($ext === 'png') {
                        $img->encode(new PngEncoder())->save($path);
                    } else {
                        $img->encode(new JpegEncoder(quality: $quality))->save($path);
                    }

                    // Run binary optimizer if available
                    if ($optimizer) {
                        try { $optimizer->optimize($path); } catch (Throwable $e) {}
                        if (file_exists($webpPath)) {
                            try { $optimizer->optimize($webpPath); } catch (Throwable $e) {}
                        }
                    }

                    clearstatcache(true, $path);
                    $estimateAfter = filesize($path) ?: $sizeBefore;
                }

                $totalAfter += $estimateAfter;
                $saved   = $sizeBefore - $estimateAfter;
                $pct     = $sizeBefore > 0 ? round(($saved / $sizeBefore) * 100, 1) : 0;

                $rows[] = [
                    $relative,
                    $this->fmt($sizeBefore),
                    $this->fmt($estimateAfter),
                    $this->fmt($saved),
                    $pct . '%',
                ];
            } catch (Throwable $e) {
                $rows[] = [$relative, $this->fmt($sizeBefore), 'ERROR', $e->getMessage(), '-'];
                $totalAfter += $sizeBefore;
            }

            $bar->advance();
        }
        $bar->finish();
        $this->newLine(2);

        // Sort by saved bytes desc
        usort($rows, fn ($a, $b) => strcmp($b[3], $a[3]));

        $this->table(['archivo', 'antes', 'después', 'ahorro', '%'], $rows);

        $totalSaved = $totalBefore - $totalAfter;
        $totalPct   = $totalBefore > 0 ? round(($totalSaved / $totalBefore) * 100, 1) : 0;

        $this->info(sprintf(
            '%s — Total: %s → %s  (ahorro %s, %s%%)',
            $dryRun ? '[DRY-RUN]' : '[APLICADO]',
            $this->fmt($totalBefore),
            $this->fmt($totalAfter),
            $this->fmt($totalSaved),
            $totalPct
        ));

        if ($optimizer === null) {
            $this->warn('Aviso: spatie/image-optimizer no inicializado — se aplicó solo Intervention.');
        }

        return self::SUCCESS;
    }

    private function fmt(int $bytes): string
    {
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }
}
