<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Book;
use App\Models\BookChapter;
use App\Models\BookGroup;
use App\Models\Category;
use App\Models\Post;
use App\Services\SitemapService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateSitemapCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate {--type= : The type of content to generate sitemap for (all by default)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates sitemap entries for all content types or a specific type';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('type');
        
        $this->info('Starting sitemap generation...');
        
        // If no specific type is provided, process all types
        if (!$type) {
            $this->generateAll();
        } else {
            $this->generateForType($type);
        }
        
        $this->info('Sitemap generation completed!');
    }
    
    /**
     * Generate sitemap entries for all content types
     */
    private function generateAll()
    {
        // Clear the existing entries
        if ($this->confirm('This will clear all existing sitemap entries. Continue?', true)) {
            DB::table('sitemaps')->truncate();
            $this->info('Cleared existing sitemap entries.');
        }
        
        $this->generateCategories();
        $this->generateBookGroups();
        $this->generateBooks();
        $this->generateBookChapters();
        $this->generatePosts();
        $this->generateArticleCategories();
        $this->generateArticles();
    }
    
    /**
     * Generate sitemap entries for a specific content type
     */
    private function generateForType(string $type)
    {
        // Clear the existing entries for this type
        if ($this->confirm("This will clear existing sitemap entries for type '{$type}'. Continue?", true)) {
            DB::table('sitemaps')->where('type', $type)->delete();
            $this->info("Cleared existing sitemap entries for type '{$type}'.");
        }
        
        switch ($type) {
            case 'category':
                $this->generateCategories();
                break;
            case 'book-group':
                $this->generateBookGroups();
                break;
            case 'book':
                $this->generateBooks();
                break;
            case 'book-chapter':
                $this->generateBookChapters();
                break;
            case 'post':
                $this->generatePosts();
                break;
            case 'article-category':
                $this->generateArticleCategories();
                break;
            case 'article':
                $this->generateArticles();
                break;
            default:
                $this->error("Unknown type '{$type}'.");
                break;
        }
    }
    
    /**
     * Generate sitemap entries for categories
     */
    private function generateCategories()
    {
        $this->info('Generating sitemap entries for categories...');
        $processed = 0;
        $total = Category::count();
        
        Category::orderBy('id')->chunk(100, function ($categories) use (&$processed, $total) {
            foreach ($categories as $category) {
                SitemapService::updateEntry('category', $category);
                $processed++;
            }
            
            $this->output->write("\rProcessed {$processed} of {$total} categories...");
        });
        
        // Ensure indices are correct
        SitemapService::rebuildIndices('category');
        
        $this->info("\nCompleted categories.");
    }
    
    /**
     * Generate sitemap entries for book groups
     */
    private function generateBookGroups()
    {
        $this->info('Generating sitemap entries for book groups...');
        $processed = 0;
        $total = BookGroup::count();
        
        BookGroup::orderBy('id')->chunk(100, function ($bookGroups) use (&$processed, $total) {
            foreach ($bookGroups as $bookGroup) {
                SitemapService::updateEntry('book-group', $bookGroup);
                $processed++;
            }
            
            $this->output->write("\rProcessed {$processed} of {$total} book groups...");
        });
        
        // Ensure indices are correct
        SitemapService::rebuildIndices('book-group');
        
        $this->info("\nCompleted book groups.");
    }
    
    /**
     * Generate sitemap entries for books
     */
    private function generateBooks()
    {
        $this->info('Generating sitemap entries for books...');
        $processed = 0;
        $total = Book::count();
        
        Book::orderBy('id')->chunk(100, function ($books) use (&$processed, $total) {
            foreach ($books as $book) {
                SitemapService::updateEntry('book', $book);
                $processed++;
            }
            
            $this->output->write("\rProcessed {$processed} of {$total} books...");
        });
        
        // Ensure indices are correct
        SitemapService::rebuildIndices('book');
        
        $this->info("\nCompleted books.");
    }
    
    /**
     * Generate sitemap entries for book chapters
     */
    private function generateBookChapters()
    {
        $this->info('Generating sitemap entries for book chapters...');
        $processed = 0;
        $total = BookChapter::count();
        
        BookChapter::orderBy('id')->chunk(100, function ($bookChapters) use (&$processed, $total) {
            foreach ($bookChapters as $bookChapter) {
                SitemapService::updateEntry('book-chapter', $bookChapter);
                $processed++;
            }
            
            $this->output->write("\rProcessed {$processed} of {$total} book chapters...");
        });
        
        // Ensure indices are correct
        SitemapService::rebuildIndices('book-chapter');
        
        $this->info("\nCompleted book chapters.");
    }
    
    /**
     * Generate sitemap entries for posts
     */
    private function generatePosts()
    {
        $this->info('Generating sitemap entries for posts...');
        $processed = 0;
        $total = Post::count();
        
        Post::orderBy('id')->chunk(100, function ($posts) use (&$processed, $total) {
            foreach ($posts as $post) {
                SitemapService::updateEntry('post', $post);
                $processed++;
            }
            
            $this->output->write("\rProcessed {$processed} of {$total} posts...");
        });
        
        // Ensure indices are correct
        SitemapService::rebuildIndices('post');
        
        $this->info("\nCompleted posts.");
    }
    
    /**
     * Generate sitemap entries for article categories
     */
    private function generateArticleCategories()
    {
        $this->info('Generating sitemap entries for article categories...');
        $processed = 0;
        $total = ArticleCategory::count();
        
        ArticleCategory::orderBy('id')->chunk(100, function ($articleCategories) use (&$processed, $total) {
            foreach ($articleCategories as $articleCategory) {
                SitemapService::updateEntry('article-category', $articleCategory);
                $processed++;
            }
            
            $this->output->write("\rProcessed {$processed} of {$total} article categories...");
        });
        
        // Ensure indices are correct
        SitemapService::rebuildIndices('article-category');
        
        $this->info("\nCompleted article categories.");
    }
    
    /**
     * Generate sitemap entries for articles
     */
    private function generateArticles()
    {
        $this->info('Generating sitemap entries for articles...');
        $processed = 0;
        $total = Article::count();
        
        Article::orderBy('id')->chunk(100, function ($articles) use (&$processed, $total) {
            foreach ($articles as $article) {
                SitemapService::updateEntry('article', $article);
                $processed++;
            }
            
            $this->output->write("\rProcessed {$processed} of {$total} articles...");
        });
        
        // Ensure indices are correct
        SitemapService::rebuildIndices('article');
        
        $this->info("\nCompleted articles.");
    }
}
