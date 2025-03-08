<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookChapter;
use App\Models\BookGroup;
use App\Models\Category;
use App\Models\Post;
use App\Models\PostAttachment;
use App\Services\SitemapService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PostController extends Controller
{
    public function importPostFromJSON(Request $request)
    {
        // Validate the incoming JSON structure for a single post object
        $validatedData = $request->validate([
            'category' => 'required|string',
            'book_group' => 'required|string',
            'book_title' => 'required|string',
            'chapter_title' => 'required|string',
            'post_title' => 'required|string',
            'content' => 'nullable|string',
            'source_url' => 'nullable|url',
            'attachment_ids' => 'nullable|array',
        ]);

        // Map JSON fields to variables
        $categoryName = $validatedData['category'];
        $bookGroupName = $validatedData['book_group'];
        $bookTitle = $validatedData['book_title'];
        $chapterTitle = $validatedData['chapter_title'];
        $postTitle = $validatedData['post_title'];
        $content = $validatedData['content'] ?? null;

        // Process Category
        $category = Category::firstOrCreate(['name' => $categoryName]);
        
        // Update category sitemap
        SitemapService::updateEntry('category', $category);

        // Process Book Group
        $bookGroup = BookGroup::firstOrCreate(
            ['name' => $bookGroupName, 'category_id' => $category->id]
        );
        
        // Update book group sitemap
        SitemapService::updateEntry('book-group', $bookGroup);

        // Process Book
        $book = Book::firstOrCreate(
            ['name' => $bookTitle, 'book_group_id' => $bookGroup->id]
        );
        
        // Update book sitemap
        SitemapService::updateEntry('book', $book);

        // Process Book Chapter
        $bookChapter = BookChapter::firstOrCreate(
            ['name' => $chapterTitle, 'book_id' => $book->id]
        );
        
        // Update book chapter sitemap
        SitemapService::updateEntry('book-chapter', $bookChapter);

        // Check if Post exists
        $existingPost = Post::where('title', $postTitle)
            ->where('book_chapter_id', $bookChapter->id)
            ->first();

        if ($existingPost) {
            // Update existing Post
            $existingPostContent = $existingPost->content;
            if ($content !== null && trim($content) !== '' && strlen($content) > 0) {
                $existingPostContent = $content;
            }

            $existingPost->update([
                'content' => $existingPostContent,
                'source_url' => $validatedData['source_url'] ?? null
            ]);

            // Update attachment associations if any
            if (isset($validatedData['attachment_ids'])) {
                // Update post_id for the attachments
                PostAttachment::whereIn('id', $validatedData['attachment_ids'])
                    ->update(['post_id' => $existingPost->id]);

                // Delete removed attachments
                $existingPost->attachments()
                    ->whereNotIn('id', $validatedData['attachment_ids'])
                    ->get()
                    ->each(function ($attachment) {
                        $attachment->delete();
                    });
            }
            
            // Update post sitemap
            SitemapService::updateEntry('post', $existingPost);

            $this->clearSitemapCache();

            return response()->json([
                'success' => 'Post updated successfully. URL: ' .
                    route('posts.show', $existingPost->slug)
            ]);
        } else {
            // Create new Post
            $post = Post::create([
                'title' => $postTitle,
                'content' => $content,
                'user_id' => 1, // Default user ID or adjust as needed
                'book_chapter_id' => $bookChapter->id,
                'source_url' => $validatedData['source_url'] ?? null
            ]);

            // Associate existing attachments if any
            if (isset($validatedData['attachment_ids'])) {
                PostAttachment::whereIn('id', $validatedData['attachment_ids'])
                    ->update(['post_id' => $post->id]);
            }
            
            // Update post sitemap
            SitemapService::updateEntry('post', $post);
            
            $this->clearSitemapCache();

            return response()->json([
                'success' => 'Post created successfully. URL: ' .
                    route('posts.show', $post->slug)
            ]);
        }
    }
    
    /**
     * Clear all relevant sitemap caches
     */
    private function clearSitemapCache()
    {
        // Clear main sitemap index cache
        Cache::forget('sitemap.index.data');
        
        // Clear individual sitemap caches
        Cache::forget('sitemap.category.page.1.data');
        Cache::forget('sitemap.book-group.page.1.data');
        Cache::forget('sitemap.book.page.1.data');
        Cache::forget('sitemap.book-chapter.page.1.data');
        Cache::forget('sitemap.post.page.1.data');
    }
}
