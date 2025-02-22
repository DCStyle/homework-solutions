<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BookGroup;
use App\Models\Post;

class BookGroupsController extends Controller
{
    public function show()
    {
        $group = BookGroup::where('slug', request()->group_slug)->firstOrFail();
        $category = $group->category;

        $footerLatestPosts = Post::select('posts.*')
            ->join('book_chapters', 'posts.book_chapter_id', '=', 'book_chapters.id')
            ->join('books', 'book_chapters.book_id', '=', 'books.id')
            ->where('books.book_group_id', $group->id)
            ->orderBy('posts.created_at', 'desc')
            ->limit(40)
            ->get();

        return view('book-groups.show', compact('group', 'category', 'footerLatestPosts'));
    }
}
