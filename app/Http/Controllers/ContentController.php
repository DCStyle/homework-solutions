<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\ContentMirrorService;
use App\Services\Scrapers\BaseScraper;
use App\Services\Scrapers\DefaultScraper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ContentController extends Controller
{
    public function __construct(ContentMirrorService $mirrorService)
    {
        $this->mirrorService = $mirrorService;
    }

    public function show(Request $request, string $path = '')
    {
        for($i = 1;$i <= 12;$i++)
        {
            if ($path == "lop-$i.html") {
                $category = Category::where('slug', "lop-$i")->first();
                if ($category)
                {
                    return redirect(
                        route('categories.show', ['category_slug' => $category->slug]),
                        301
                    );
                }
            }
        }

        // Get the query string
        $queryString = $request->getQueryString();

        // Build the full path with query string if it exists
        $fullPath = $path;
        if ($queryString) {
            $fullPath .= '?' . $queryString;
        }

        // For debugging
        \Log::info('Request details', [
            'path' => $path,
            'fullPath' => $fullPath,
            'params' => $request->all()
        ]);

        $result = $this->handlePath($request, $fullPath);

        if (!$result) {
            abort(404);
        }

        if ((bool) setting('cache_enabled')) {
            // Track cache metrics with full path including query string
            Cache::put('last_cached_url', $fullPath, now()->addDay());
            Cache::put('last_cache_time', now(), now()->addDay());
        }

        return $this->createResponse($result);
    }

    private function handlePath(Request $request, string $fullPath): ?array
    {
        // Split path and query parameters
        $pathParts = explode('?', $fullPath);
        $path = ltrim($pathParts[0], '/');

        // For debugging
        \Log::info('Handling path', [
            'path' => $path,
            'fullPath' => $fullPath
        ]);

        // Check custom paths first
        $urlMappings = config('url_mappings.paths');
        foreach ($urlMappings as $mapping) {
            if (isset($mapping['our_paths'][$path]) || isset($mapping['our_paths']["/$path"])) {
                return [
                    'content' => null,
                    'template' => $mapping['our_paths'][$path] ?? $mapping['our_paths']["/$path"]
                ];
            }
        }

        // Get all request parameters
        $params = $request->all();

        if (isset($this->scrapers[$path])) {
            return $this->handleScraper($request, $fullPath);
        }

        // Use DefaultScraper for unspecified paths
        $scraper = new DefaultScraper($fullPath);
        return $scraper->handle($params);
    }

    private function handleScraper(Request $request, string $path): ?array
    {
        $scraperFactory = $this->scrapers[$path];

        /** @var BaseScraper $scraper */
        $scraper = is_callable($scraperFactory) ? $scraperFactory() : app($scraperFactory);

        // Pass all parameters including query string
        $params = $request->all();
        return $scraper->handle($params);
    }

    private function createResponse(array $result, $path = ''): View
    {
        $metadata = $result['metadata'] ?? null;
        $viewData = $result['data'] ?? [];

        return view($result['template'], [
            'content' => $result['content'],
            'metadata' => $metadata
        ] + $viewData)->withHeaders([
            'X-Robots-Tag' => 'noindex, nofollow',
            'Cache-Control' => 'public, max-age=300'
        ]);
    }
}
