<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\BookChapter;
use App\Models\BookGroup;
use App\Models\Category;
use App\Models\Book;
use App\Models\Image;
use App\Models\Post;
use App\Models\PostAttachment;
use App\Services\SitemapService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class BookChaptersController extends Controller
{
    public function edit($id)
    {
        $chapter = BookChapter::whereId($id)->firstOrFail();
        $book = $chapter->book;

        $books = Book::all()->sortBy('category_id');

        return view('admin.chapters.form', compact('chapter', 'book', 'books'));
    }

    public function update(Request $request, $id)
    {
        $chapter = BookChapter::whereId($id)->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'slug' => 'required|string',
            'book_id' => 'required|integer|exists:books,id',
        ]);

        $chapter->update($validated);
        
        // Update sitemap entry
        SitemapService::updateEntry('book-chapter', $chapter);
        
        // Clear sitemap cache
        $this->clearSitemapCache();

        return redirect()->route('admin.books.chapters', $chapter->book->id)->with('success', 'Cập nhật chương sách thành công.');
    }

    public function destroy($id)
    {
        $chapter = BookChapter::whereId($id)->firstOrFail();

        $book = $chapter->book;
        
        // Remove from sitemap before deleting
        SitemapService::removeEntry('book-chapter', $chapter->id);
        
        // Clear sitemap cache
        $this->clearSitemapCache();

        $chapter->delete();

        return redirect()->route('admin.books.chapters', $book->id)->with('success', 'Xóa chương sách thành công.');
    }

    public function posts($id)
    {
        $chapter = BookChapter::whereId($id)->firstOrFail();

        $posts = Post::where('book_chapter_id', $chapter->id)->orderBy('created_at')->paginate(50);

        return view('admin.posts.index', compact('chapter', 'posts'));
    }

    public function createPost($id)
    {
        $chapter = BookChapter::whereId($id)->firstOrFail();

        return view('admin.posts.form', compact('chapter'));
    }

    public function storePost(Request $request, $id)
    {
        $chapter = BookChapter::whereId($id)->firstOrFail();

        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'uploaded_attachment_ids' => 'nullable|json',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500'
        ]);

        $post = Post::create([
            'title' => $request->title,
            'content' => $request->message,
            'book_chapter_id' => $chapter->id,
            'user_id' => Auth::id(),
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description
        ]);

        // Handle image attachments
        if ($request->has('uploaded_image_ids')) {
            $imageIds = json_decode($request->uploaded_image_ids, true);
            if (is_array($imageIds)) {
                Image::whereIn('id', $imageIds)
                    ->update([
                        'imageable_id' => $post->id,
                        'imageable_type' => Post::class
                    ]);
            }
        }

        // Handle pre-uploaded attachments
        if ($request->has('uploaded_attachment_ids')) {
            $attachmentIds = json_decode($request->uploaded_attachment_ids, true);
            if (is_array($attachmentIds)) {
                PostAttachment::whereIn('id', $attachmentIds)
                    ->whereNull('post_id')
                    ->update(['post_id' => $post->id]);
            }
        }
        
        // Update sitemap entry
        SitemapService::updateEntry('post', $post);
        
        // Clear sitemap cache
        $this->clearPostSitemapCache();

        return redirect()->route('admin.bookChapters.posts', $id)
            ->with('success', 'Tạo bài viết thành công.');
    }
    
    /**
     * Clear book chapter sitemap cache
     */
    private function clearSitemapCache()
    {
        Cache::forget('sitemap.index.data');
        Cache::forget('sitemap.book-chapter.page.1.data');
    }
    
    /**
     * Clear post sitemap cache
     */
    private function clearPostSitemapCache()
    {
        Cache::forget('sitemap.index.data');
        Cache::forget('sitemap.post.page.1.data');
    }
}
