<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds indexes to foreign key columns for better performance on JOIN operations
     */
    public function up(): void
    {
        // Add indexes to posts table foreign keys
        $this->addIndexIfColumnExists('posts', 'book_chapter_id');
        $this->addIndexIfColumnExists('posts', 'user_id');
        
        // Add indexes to book_chapters table foreign keys
        $this->addIndexIfColumnExists('book_chapters', 'book_id');
        
        // Add indexes to books table foreign keys
        $this->addIndexIfColumnExists('books', 'book_group_id');
        
        // Add indexes to book_groups table foreign keys
        $this->addIndexIfColumnExists('book_groups', 'category_id');
        
        // Add indexes to articles table foreign keys
        $this->addIndexIfColumnExists('articles', 'article_category_id');
        $this->addIndexIfColumnExists('articles', 'user_id');
        
        // Add indexes to categories table
        $this->addIndexIfColumnExists('categories', 'parent_id');
        
        // Add indexes to article_categories table
        $this->addIndexIfColumnExists('article_categories', 'parent_id');
        
        // Add indexes to article_tag_article pivot table
        $this->addIndexIfColumnExists('article_tag_article', 'article_id');
        $this->addIndexIfColumnExists('article_tag_article', 'article_tag_id');
        
        // Add indexes to images table (polymorphic relationship)
        $this->addIndexIfColumnExists('images', 'imageable_id');
        $this->addIndexIfColumnExists('images', 'imageable_type');
        
        // Add indexes to post_attachments table
        $this->addIndexIfColumnExists('post_attachments', 'post_id');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove indexes from posts table foreign keys
        $this->dropIndexIfColumnExists('posts', 'book_chapter_id');
        $this->dropIndexIfColumnExists('posts', 'user_id');
        
        // Remove indexes from book_chapters table foreign keys
        $this->dropIndexIfColumnExists('book_chapters', 'book_id');
        
        // Remove indexes from books table foreign keys
        $this->dropIndexIfColumnExists('books', 'book_group_id');
        
        // Remove indexes from book_groups table foreign keys
        $this->dropIndexIfColumnExists('book_groups', 'category_id');
        
        // Remove indexes from articles table foreign keys
        $this->dropIndexIfColumnExists('articles', 'article_category_id');
        $this->dropIndexIfColumnExists('articles', 'user_id');
        
        // Remove indexes from categories table
        $this->dropIndexIfColumnExists('categories', 'parent_id');
        
        // Remove indexes from article_categories table
        $this->dropIndexIfColumnExists('article_categories', 'parent_id');
        
        // Remove indexes from article_tag_article pivot table
        $this->dropIndexIfColumnExists('article_tag_article', 'article_id');
        $this->dropIndexIfColumnExists('article_tag_article', 'article_tag_id');
        
        // Remove indexes from images table (polymorphic relationship)
        $this->dropIndexIfColumnExists('images', 'imageable_id');
        $this->dropIndexIfColumnExists('images', 'imageable_type');
        
        // Remove indexes from post_attachments table
        $this->dropIndexIfColumnExists('post_attachments', 'post_id');
    }
    
    /**
     * Add an index to a column if the table and column exist
     */
    private function addIndexIfColumnExists($table, $column)
    {
        try {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
                Schema::table($table, function (Blueprint $table) use ($column) {
                    $table->index($column);
                });
                Log::info("Added index to {$table}.{$column}");
            }
        } catch (\Exception $e) {
            Log::warning("Failed to add index to {$table}.{$column}: " . $e->getMessage());
        }
    }
    
    /**
     * Drop an index from a column if the table and column exist
     */
    private function dropIndexIfColumnExists($table, $column)
    {
        try {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
                Schema::table($table, function (Blueprint $table) use ($column) {
                    $table->dropIndex([$column]);
                });
                Log::info("Dropped index from {$table}.{$column}");
            }
        } catch (\Exception $e) {
            Log::warning("Failed to drop index from {$table}.{$column}: " . $e->getMessage());
        }
    }
};
