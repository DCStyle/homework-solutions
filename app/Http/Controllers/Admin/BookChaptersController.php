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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            'slug' => 'required|string',
        ]);

        $chapter->update($validated);

        return redirect()->route('admin.books.chapters', $chapter->book->id)->with('success', 'Cập nhật chương sách thành công.');
    }

    public function destroy($id)
    {
        $chapter = BookChapter::whereId($id)->firstOrFail();

        $book = $chapter->book;

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
            'uploaded_attachment_ids' => 'nullable|json'
        ]);

        $post = Post::create([
            'title' => $request->title,
            'content' => $request->message,
            'book_chapter_id' => $chapter->id,
            'user_id' => Auth::id(),
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

        return redirect()->route('admin.bookChapters.posts', $id)
            ->with('success', 'Tạo bài viết thành công.');
    }
}
