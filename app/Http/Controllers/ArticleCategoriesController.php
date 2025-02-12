<?php

namespace App\Http\Controllers;

use App\Models\ArticleCategory;
use App\Models\Post;

class ArticleCategoriesController extends Controller
{
    public function show($slug) {
        $category = ArticleCategory::where('slug', $slug)->firstOrFail();

        $articles = $category->articles()->paginate(10);

        $posts = Post::latest()->take(5)->get();

        return view('article-categories.show', compact('category', 'articles', 'posts'));
    }
}
