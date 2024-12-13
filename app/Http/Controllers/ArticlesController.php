<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleTag;
use Illuminate\Http\Request;

class ArticlesController extends Controller
{
    public function latest(Request $request)
    {
        $perPage = 8;
        $page = $request->input('page', 1);

        $articles = Article::latest()
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('layouts.articles-grid', ['articles' => $articles])->render(),
                'hasMore' => $articles->count() == $perPage
            ]);
        }

        return view('articles.latest', [
            'articles' => $articles,
            'hasMore' => $articles->count() == $perPage
        ]);
    }

    public function show($slug)
    {
        $article = Article::where('slug', $slug)->firstOrFail();

        return view('articles.show', compact('article'));
    }

    public function searchTags(Request $request)
    {
        $term = $request->get('q');

        $tags = ArticleTag::where('name', 'LIKE', "%$term%")
            ->limit(10)
            ->get();

        return response()->json($tags);
    }
}
