<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Book;
use App\Models\BookChapter;
use App\Models\BookGroup;
use App\Models\Category;
use App\Models\Post;
use App\Services\SitemapService;
use Illuminate\Support\ServiceProvider;

class SitemapServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register model events for automatic sitemap updates
        $this->registerCategoryEvents();
        $this->registerBookGroupEvents();
        $this->registerBookEvents();
        $this->registerBookChapterEvents();
        $this->registerPostEvents();
        $this->registerArticleCategoryEvents();
        $this->registerArticleEvents();
    }
    
    /**
     * Register events for Category model
     */
    private function registerCategoryEvents(): void
    {
        Category::saved(function ($category) {
            SitemapService::updateEntry('category', $category);
        });
        
        Category::deleted(function ($category) {
            SitemapService::removeEntry('category', $category->id);
        });
    }
    
    /**
     * Register events for BookGroup model
     */
    private function registerBookGroupEvents(): void
    {
        BookGroup::saved(function ($bookGroup) {
            SitemapService::updateEntry('book-group', $bookGroup);
        });
        
        BookGroup::deleted(function ($bookGroup) {
            SitemapService::removeEntry('book-group', $bookGroup->id);
        });
    }
    
    /**
     * Register events for Book model
     */
    private function registerBookEvents(): void
    {
        Book::saved(function ($book) {
            SitemapService::updateEntry('book', $book);
        });
        
        Book::deleted(function ($book) {
            SitemapService::removeEntry('book', $book->id);
        });
    }
    
    /**
     * Register events for BookChapter model
     */
    private function registerBookChapterEvents(): void
    {
        BookChapter::saved(function ($bookChapter) {
            SitemapService::updateEntry('book-chapter', $bookChapter);
        });
        
        BookChapter::deleted(function ($bookChapter) {
            SitemapService::removeEntry('book-chapter', $bookChapter->id);
        });
    }
    
    /**
     * Register events for Post model
     */
    private function registerPostEvents(): void
    {
        Post::saved(function ($post) {
            SitemapService::updateEntry('post', $post);
        });
        
        Post::deleted(function ($post) {
            SitemapService::removeEntry('post', $post->id);
        });
    }
    
    /**
     * Register events for ArticleCategory model
     */
    private function registerArticleCategoryEvents(): void
    {
        ArticleCategory::saved(function ($articleCategory) {
            SitemapService::updateEntry('article-category', $articleCategory);
        });
        
        ArticleCategory::deleted(function ($articleCategory) {
            SitemapService::removeEntry('article-category', $articleCategory->id);
        });
    }
    
    /**
     * Register events for Article model
     */
    private function registerArticleEvents(): void
    {
        Article::saved(function ($article) {
            SitemapService::updateEntry('article', $article);
        });
        
        Article::deleted(function ($article) {
            SitemapService::removeEntry('article', $article->id);
        });
    }
}
