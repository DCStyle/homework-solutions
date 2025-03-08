<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Book;
use App\Models\BookChapter;
use App\Models\BookGroup;
use App\Models\Category;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class SitemapController extends Controller
{
    // Maximum URLs per sitemap according to protocol
    const MAX_URLS_PER_SITEMAP = 50000;
    
    // For our implementation, we'll use a more conservative limit
    const ITEMS_PER_PAGE = 1000;

    /**
     * Constructor to increase memory limit for sitemap generation
     */
    public function __construct()
    {
        // Increase memory limit for sitemap generation
        ini_set('memory_limit', '512M');
    }

    /**
     * Generate the main sitemap index
     */
    public function index()
    {
        $sitemaps = [
            [
                'loc' => url('sitemap-categories.xml'),
                'lastmod' => $this->getLatestUpdate(Category::class)
            ],
            [
                'loc' => url('sitemap-book-groups.xml'),
                'lastmod' => $this->getLatestUpdate(BookGroup::class)
            ],
            [
                'loc' => url('sitemap-books.xml'),
                'lastmod' => $this->getLatestUpdate(Book::class)
            ],
            [
                'loc' => url('sitemap-book-chapters.xml'),
                'lastmod' => $this->getLatestUpdate(BookChapter::class)
            ],
            [
                'loc' => url('sitemap-article-categories.xml'),
                'lastmod' => $this->getLatestUpdate(ArticleCategory::class)
            ],
        ];
        
        // Add paginated posts sitemaps
        $postCount = Post::count();
        $postPages = ceil($postCount / self::ITEMS_PER_PAGE);
        
        for ($i = 1; $i <= $postPages; $i++) {
            $sitemaps[] = [
                'loc' => url("sitemap-posts-{$i}.xml"),
                'lastmod' => $this->getLatestUpdate(Post::class)
            ];
        }
        
        // Add paginated articles sitemaps
        $articleCount = Article::count();
        $articlePages = ceil($articleCount / self::ITEMS_PER_PAGE);
        
        for ($i = 1; $i <= $articlePages; $i++) {
            $sitemaps[] = [
                'loc' => url("sitemap-articles-{$i}.xml"),
                'lastmod' => $this->getLatestUpdate(Article::class)
            ];
        }

        return response()->view('sitemaps.index', compact('sitemaps'))
            ->header('Content-Type', 'text/xml');
    }

    /**
     * Generate robots.txt file
     */
    public function robots()
    {
        $content = "User-agent: *\nDisallow:\nSitemap: " . url('sitemap.xml');
        return response($content)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * Generate sitemap for categories
     */
    public function categories()
    {
        $categories = $this->getChunkedData(Category::class);
        return $this->generateSitemap('sitemaps.categories', $categories);
    }

    /**
     * Generate sitemap for book groups
     */
    public function bookGroups()
    {
        $bookGroups = $this->getChunkedData(BookGroup::class);
        return $this->generateSitemap('sitemaps.book-groups', $bookGroups);
    }

    /**
     * Generate sitemap for books
     */
    public function books()
    {
        $books = $this->getChunkedData(Book::class);
        return $this->generateSitemap('sitemaps.books', $books);
    }

    /**
     * Generate sitemap for book chapters
     */
    public function bookChapters()
    {
        $bookChapters = $this->getChunkedData(BookChapter::class);
        return $this->generateSitemap('sitemaps.book-chapters', $bookChapters);
    }

    /**
     * Generate sitemap for article categories
     */
    public function articleCategories()
    {
        $articleCategories = $this->getChunkedData(ArticleCategory::class);
        return $this->generateSitemap('sitemaps.article-categories', $articleCategories);
    }

    /**
     * Generate paginated sitemap for posts
     */
    public function paginatedPosts($page)
    {
        $offset = (max(1, $page) - 1) * self::ITEMS_PER_PAGE;
        
        $posts = Post::orderBy('updated_at', 'desc')
                    ->skip($offset)
                    ->take(self::ITEMS_PER_PAGE)
                    ->get();
                    
        return $this->generateSitemap('sitemaps.posts', $posts);
    }

    /**
     * Generate paginated sitemap for articles
     */
    public function paginatedArticles($page)
    {
        $offset = (max(1, $page) - 1) * self::ITEMS_PER_PAGE;
        
        $articles = Article::orderBy('updated_at', 'desc')
                        ->skip($offset)
                        ->take(self::ITEMS_PER_PAGE)
                        ->get();
                    
        return $this->generateSitemap('sitemaps.articles', $articles);
    }

    /**
     * Helper to generate sitemap response
     */
    private function generateSitemap($view, $items)
    {
        return response()->view($view, compact('items'))
            ->header('Content-Type', 'text/xml');
    }

    /**
     * Get the latest update date for a model
     */
    private function getLatestUpdate($modelClass)
    {
        $latest = $modelClass::orderBy('updated_at', 'desc')->first();
        
        return $latest ? $latest->updated_at->toIso8601String() : Carbon::now()->toIso8601String();
    }

    /**
     * Get data in chunks to avoid memory issues
     */
    private function getChunkedData($modelClass, $limit = 5000)
    {
        // Check if the table has more than the limit
        $count = $modelClass::count();
        if ($count <= $limit) {
            return $modelClass::orderBy('updated_at', 'desc')->get();
        }

        // For larger tables, we need to process in chunks
        $result = collect();
        try {
            $modelClass::orderBy('updated_at', 'desc')
                ->chunk(1000, function ($items) use (&$result, $limit) {
                    if ($result->count() < $limit) {
                        $result = $result->concat($items);
                    }
                });
        } catch (\Exception $e) {
            Log::error('Error generating sitemap: ' . $e->getMessage());
            // Fallback to a more memory efficient but less ideal approach for very large tables
            return $modelClass::orderBy('updated_at', 'desc')
                ->limit($limit)
                ->get();
        }

        return $result;
    }
} 