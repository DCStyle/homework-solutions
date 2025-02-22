<?php

namespace App\Http\Controllers;

use App\Models\BookChapter;
use App\Models\Post;

class BookChaptersController extends Controller
{
    public function show()
    {
        $chapter = BookChapter::where('slug', request()->chapter_slug)->firstOrFail();
        $book = $chapter->book;
        $group = $book->group;
        $category = $group->category;

        $footerLatestPosts = Post::select('posts.*')
            ->join('book_chapters', 'posts.book_chapter_id', '=', 'book_chapters.id')
            ->join('books', 'book_chapters.book_id', '=', 'books.id')
            ->join('book_groups', 'books.book_group_id', '=', 'book_groups.id')
            ->join('categories', 'book_groups.category_id', '=', 'categories.id')
            ->where('categories.id', $category->id)
            ->orderBy('posts.created_at', 'desc')
            ->limit(40)
            ->get();

        return view('book-chapters.show', compact('chapter', 'book', 'group', 'category', 'footerLatestPosts'));
    }
}
