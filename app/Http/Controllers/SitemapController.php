<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BlogController;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = $this->getUrls();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($urls as $url) {
            $xml .= '<url>';
            $xml .= '<loc>' . htmlspecialchars($url['loc']) . '</loc>';
            $xml .= '<lastmod>' . $url['lastmod'] . '</lastmod>';
            $xml .= '<changefreq>' . $url['changefreq'] . '</changefreq>';
            $xml .= '<priority>' . $url['priority'] . '</priority>';
            $xml .= '</url>';
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    private function getUrls(): array
    {
        $baseUrl = config('app.url', 'https://wellcorefitness.com');
        $today = now()->toDateString();

        $urls = [
            ['loc' => $baseUrl, 'lastmod' => $today, 'changefreq' => 'weekly', 'priority' => '1.0'],
            ['loc' => $baseUrl . '/metodo', 'lastmod' => $today, 'changefreq' => 'monthly', 'priority' => '0.9'],
            ['loc' => $baseUrl . '/planes', 'lastmod' => $today, 'changefreq' => 'weekly', 'priority' => '0.9'],
            ['loc' => $baseUrl . '/proceso', 'lastmod' => $today, 'changefreq' => 'monthly', 'priority' => '0.8'],
            ['loc' => $baseUrl . '/reto-rise', 'lastmod' => $today, 'changefreq' => 'weekly', 'priority' => '0.8'],
            ['loc' => $baseUrl . '/nosotros', 'lastmod' => $today, 'changefreq' => 'monthly', 'priority' => '0.7'],
            ['loc' => $baseUrl . '/faq', 'lastmod' => $today, 'changefreq' => 'monthly', 'priority' => '0.7'],
            ['loc' => $baseUrl . '/coaches', 'lastmod' => $today, 'changefreq' => 'monthly', 'priority' => '0.6'],
            ['loc' => $baseUrl . '/presencial', 'lastmod' => $today, 'changefreq' => 'monthly', 'priority' => '0.6'],
            ['loc' => $baseUrl . '/fit', 'lastmod' => $today, 'changefreq' => 'monthly', 'priority' => '0.5'],
            ['loc' => $baseUrl . '/blog', 'lastmod' => $today, 'changefreq' => 'weekly', 'priority' => '0.7'],
        ];

        // Add blog articles
        try {
            $articles = BlogController::getArticles();
            foreach ($articles as $article) {
                $urls[] = [
                    'loc' => $baseUrl . '/blog/' . $article['slug'],
                    'lastmod' => $article['date'] ?? $today,
                    'changefreq' => 'monthly',
                    'priority' => '0.6',
                ];
            }
        } catch (\Exception $e) {
            // Skip if blog not available
        }

        return $urls;
    }
}
