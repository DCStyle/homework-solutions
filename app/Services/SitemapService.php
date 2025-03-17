<?php

namespace App\Services;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Book;
use App\Models\BookChapter;
use App\Models\BookGroup;
use App\Models\Category;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

class SitemapService
{
    /**
     * The maximum number of URLs per sitemap according to the protocol
     */
    const MAX_URLS_PER_SITEMAP = 50000;

    /**
     * For our implementation, we'll use a more conservative limit
     */
    const ITEMS_PER_SITEMAP = 1000;

    /**
     * Map of model types to their model classes
     */
    const TYPE_MAP = [
        'category' => Category::class,
        'book-group' => BookGroup::class,
        'book' => Book::class,
        'book-chapter' => BookChapter::class,
        'post' => Post::class,
        'article-category' => ArticleCategory::class,
        'article' => Article::class,
    ];

    /**
     * Map of model types to their route names for URL generation
     */
    const ROUTE_MAP = [
        'category' => 'categories.show',
        'book-group' => 'bookGroups.show',
        'book' => 'books.show',
        'book-chapter' => 'bookChapters.show',
        'post' => 'posts.show',
        'article-category' => 'article-categories.show',
        'article' => 'articles.show',
    ];

    /**
     * Map of model types to their priority in the sitemap
     */
    const PRIORITY_MAP = [
        'category' => 0.8,
        'book-group' => 0.8,
        'book' => 0.8,
        'book-chapter' => 0.7,
        'post' => 0.9,
        'article-category' => 0.8,
        'article' => 0.9,
    ];

    /**
     * Map of model types to their change frequency
     */
    const CHANGEFREQ_MAP = [
        'category' => 'weekly',
        'book-group' => 'weekly',
        'book' => 'weekly',
        'book-chapter' => 'weekly',
        'post' => 'weekly',
        'article-category' => 'weekly',
        'article' => 'weekly',
    ];

    /**
     * Update or create a sitemap entry for a model
     */
    public static function updateEntry(string $type, $model): void
    {
        try {
            // Calculate which sitemap index this entry belongs to
            $sitemapIndex = self::calculateSitemapIndex($type, $model->id);

            // Generate the URL for this model
            $url = self::getUrlForModel($type, $model);

            // Insert or update the sitemap entry
            DB::table('sitemaps')->updateOrInsert(
                ['type' => $type, 'model_id' => $model->id],
                [
                    'loc' => $url,
                    'lastmod' => $model->updated_at,
                    'changefreq' => self::getChangefreqForType($type),
                    'priority' => self::getPriorityForType($type),
                    'sitemap_index' => $sitemapIndex,
                    'updated_at' => now(),
                ]
            );
        } catch (\Exception $e) {
            Log::error('Error updating sitemap entry: ' . $e->getMessage(), [
                'type' => $type,
                'model_id' => $model->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Remove a sitemap entry for a model
     */
    public static function removeEntry(string $type, int $modelId): void
    {
        try {
            DB::table('sitemaps')
                ->where('type', $type)
                ->where('model_id', $modelId)
                ->delete();
        } catch (\Exception $e) {
            Log::error('Error removing sitemap entry: ' . $e->getMessage(), [
                'type' => $type,
                'model_id' => $modelId,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Calculate which sitemap index a model belongs to
     */
    public static function calculateSitemapIndex(string $type, int $modelId): int
    {
        $position = DB::table('sitemaps')
            ->where('type', $type)
            ->where('model_id', '<=', $modelId)
            ->count();

        return (int) ceil(($position + 1) / self::ITEMS_PER_SITEMAP);
    }

    /**
     * Generate a URL for a model based on its type
     */
    public static function getUrlForModel(string $type, $model): string
    {
        try {
            $routeName = self::ROUTE_MAP[$type] ?? null;

            if (!$routeName || !Route::has($routeName)) {
                throw new \Exception("Route not found for type: {$type}");
            }

            // Use the configured APP_URL from .env instead of the current request URL
            return route($routeName, $model->slug, true);
        } catch (\Exception $e) {
            Log::error('Error generating URL for model: ' . $e->getMessage(), [
                'type' => $type,
                'model_id' => $model->id ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);

            return '';
        }
    }

    /**
     * Get the priority for a model type
     */
    public static function getPriorityForType(string $type): float
    {
        return self::PRIORITY_MAP[$type] ?? 0.5;
    }

    /**
     * Get the change frequency for a model type
     */
    public static function getChangefreqForType(string $type): string
    {
        return self::CHANGEFREQ_MAP[$type] ?? 'weekly';
    }

    /**
     * Get the sitemap types and their page counts
     */
    public static function getSitemapTypes()
    {
        return DB::table('sitemaps')
            ->select('type')
            ->selectRaw('COUNT(DISTINCT sitemap_index) as page_count')
            ->selectRaw('MAX(lastmod) as lastmod')
            ->groupBy('type')
            ->get();
    }

    /**
     * Get the entries for a specific type and page
     */
    public static function getEntriesForTypePage(string $type, int $page)
    {
        return DB::table('sitemaps')
            ->where('type', $type)
            ->where('sitemap_index', $page)
            ->orderBy('lastmod', 'desc')
            ->get();
    }

    /**
     * Get the last modified date for a type
     */
    public static function getLastModifiedForType(string $type): string
    {
        $lastmod = DB::table('sitemaps')
            ->where('type', $type)
            ->max('lastmod');

        return $lastmod ? Carbon::parse($lastmod)->toIso8601String() : Carbon::now()->toIso8601String();
    }

    /**
     * Rebuild the sitemap indices after bulk operations
     */
    public static function rebuildIndices(string $type): void
    {
        $entries = DB::table('sitemaps')
            ->where('type', $type)
            ->orderBy('id')
            ->get(['id']);

        $index = 1;
        $count = 0;

        foreach ($entries as $entry) {
            $count++;

            if ($count > self::ITEMS_PER_SITEMAP) {
                $index++;
                $count = 1;
            }

            DB::table('sitemaps')
                ->where('id', $entry->id)
                ->update(['sitemap_index' => $index]);
        }
    }
}
