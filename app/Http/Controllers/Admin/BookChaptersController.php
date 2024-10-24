<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookChapter;
use App\Models\BookGroup;
use App\Models\Category;
use App\Models\Book;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookChaptersController extends Controller
{
    public function edit($slug)
    {
        $chapter = BookChapter::where('slug', $slug)->firstOrFail();
        $book = $chapter->book;

        $books = Book::all()->sortBy('category_id');

        return view('admin.chapters.edit', compact('chapter', 'book', 'books'));
    }

    public function update(Request $request, $slug)
    {
        $chapter = BookChapter::where('slug', $slug)->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string',
        ]);

        $chapter->update([
            'name' => $request->name,
            'slug' => $request->slug
        ]);

        return redirect()->route('admin.books.chapters', $chapter->book->slug)->with('success', 'Book chapter updated successfully.');
    }

    public function delete($slug)
    {
        $chapter = BookChapter::where('slug', $slug)->firstOrFail();

        return view('admin_layouts.delete', [
            'confirmLink' => route('admin.bookChapters.destroy', $chapter->slug),
            'name' => $chapter->name,
            'backLink' => route('admin.books.chapters', $chapter->book->slug),
        ]);
    }

    public function destroy($slug)
    {
        $chapter = BookChapter::where('slug', $slug)->firstOrFail();

        $book = $chapter->book;

        $chapter->delete();

        return redirect()->route('admin.books.chapters', $book->slug)->with('success', 'Book chapter deleted successfully.');
    }

    public function posts($slug)
    {
        $chapter = BookChapter::where('slug', $slug)->firstOrFail();

        $posts = Post::where('book_chapter_id', $chapter->id)->orderBy('created_at')->get();

        return view('admin.posts.index', compact('chapter', 'posts'));
    }

    public function createPost($slug)
    {
        $chapter = BookChapter::where('slug', $slug)->firstOrFail();

        return view('admin.posts.create', compact('chapter'));
    }

    public function storePost(Request $request, $slug)
    {
        $chapter = BookChapter::where('slug', $slug)->firstOrFail();

        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        Post::create([
            'title' => $request->title,
            'content' => $request->message, // HTML content from TinyMCE
            'book_chapter_id' => $chapter->id,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('admin.bookChapters.posts', $slug)->with('success', 'Post created successfully.');
    }
}
