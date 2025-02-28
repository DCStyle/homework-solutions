<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add unique constraint to articles.slug
        $this->addUniqueConstraint('articles', 'slug');
        
        // Add unique constraint to article_categories.slug
        $this->addUniqueConstraint('article_categories', 'slug');
        
        // Add unique constraint to article_tags.slug
        $this->addUniqueConstraint('article_tags', 'slug');
        
        // Add unique constraint to books.slug
        $this->addUniqueConstraint('books', 'slug');
        
        // Add unique constraint to book_chapters.slug
        $this->addUniqueConstraint('book_chapters', 'slug');
        
        // Add unique constraint to book_groups.slug
        $this->addUniqueConstraint('book_groups', 'slug');
        
        // Add unique constraint to categories.slug
        $this->addUniqueConstraint('categories', 'slug');
        
        // Add unique constraint to posts.slug
        $this->addUniqueConstraint('posts', 'slug');
        
        // Special handling for posts.source_url
        $this->addUniqueConstraintToPostsSourceUrl();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop unique constraint from articles.slug
        $this->dropUniqueConstraint('articles', 'slug');
        
        // Drop unique constraint from article_categories.slug
        $this->dropUniqueConstraint('article_categories', 'slug');
        
        // Drop unique constraint from article_tags.slug
        $this->dropUniqueConstraint('article_tags', 'slug');
        
        // Drop unique constraint from books.slug
        $this->dropUniqueConstraint('books', 'slug');
        
        // Drop unique constraint from book_chapters.slug
        $this->dropUniqueConstraint('book_chapters', 'slug');
        
        // Drop unique constraint from book_groups.slug
        $this->dropUniqueConstraint('book_groups', 'slug');
        
        // Drop unique constraint from categories.slug
        $this->dropUniqueConstraint('categories', 'slug');
        
        // Drop unique constraints from posts.slug and posts.source_url
        $this->dropUniqueConstraint('posts', 'slug');
        $this->dropUniqueConstraint('posts', 'source_url');
    }
    
    /**
     * Add a unique constraint to a table column
     */
    private function addUniqueConstraint($table, $column)
    {
        try {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
                Schema::table($table, function (Blueprint $table) use ($column) {
                    $table->unique($column);
                });
                Log::info("Added unique constraint to {$table}.{$column}");
            }
        } catch (\Exception $e) {
            Log::warning("Failed to add unique constraint to {$table}.{$column}: " . $e->getMessage());
        }
    }
    
    /**
     * Special handling for posts.source_url
     */
    private function addUniqueConstraintToPostsSourceUrl()
    {
        try {
            if (Schema::hasTable('posts') && Schema::hasColumn('posts', 'source_url')) {
                // Handle NULL values
                DB::statement("UPDATE posts SET source_url = '' WHERE source_url IS NULL");
                
                // Handle duplicate values
                DB::statement("
                    UPDATE posts 
                    SET source_url = CONCAT(source_url, '-', id) 
                    WHERE source_url IN (
                        SELECT source_url 
                        FROM (
                            SELECT source_url 
                            FROM posts 
                            WHERE source_url != '' 
                            GROUP BY source_url 
                            HAVING COUNT(*) > 1
                        ) AS duplicates
                    )
                ");
                
                // Add unique constraint
                Schema::table('posts', function (Blueprint $table) {
                    $table->unique('source_url');
                });
                
                Log::info("Added unique constraint to posts.source_url");
            }
        } catch (\Exception $e) {
            Log::warning("Failed to add unique constraint to posts.source_url: " . $e->getMessage());
        }
    }
    
    /**
     * Drop a unique constraint from a table column
     */
    private function dropUniqueConstraint($table, $column)
    {
        try {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) use ($column) {
                    $table->dropUnique([$column]);
                });
                Log::info("Dropped unique constraint from {$table}.{$column}");
            }
        } catch (\Exception $e) {
            Log::warning("Failed to drop unique constraint from {$table}.{$column}: " . $e->getMessage());
        }
    }
};
