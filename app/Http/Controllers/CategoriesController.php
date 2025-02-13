<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class CategoriesController extends Controller
{
    public function show($slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $footerLatestPosts = Post::select('posts.*')
            ->join('book_chapters', 'posts.book_chapter_id', '=', 'book_chapters.id')
            ->join('books', 'book_chapters.book_id', '=', 'books.id')
            ->join('book_groups', 'books.book_group_id', '=', 'book_groups.id')
            ->join('categories', 'book_groups.category_id', '=', 'categories.id')
            ->where('categories.slug', $slug)
            ->orderBy('posts.created_at', 'desc')
            ->take(10)
            ->get();

        return view('categories.show', compact('category', 'footerLatestPosts'));
    }
}
