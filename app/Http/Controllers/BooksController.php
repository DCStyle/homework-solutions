<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Post;

class BooksController extends Controller
{
    public function show($slug)
    {
        $book = Book::where('slug', $slug)->firstOrFail();

        $category = $book->group->category;

        $footerLatestPosts = Post::select('posts.*')
            ->join('book_chapters', 'posts.book_chapter_id', '=', 'book_chapters.id')
            ->join('books', 'book_chapters.book_id', '=', 'books.id')
            ->where('books.book_group_id', $book->group->id)
            ->orderBy('posts.created_at', 'desc')
            ->limit(40)
            ->get();

        return view('books.show', compact('book', 'category', 'footerLatestPosts'));
    }
}
