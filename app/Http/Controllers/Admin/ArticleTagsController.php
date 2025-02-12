<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArticleTag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArticleTagsController extends Controller
{
    public function index()
    {
        $tags = ArticleTag::withCount('articles')->paginate(20);
        return view('admin.articleTags.index', compact('tags'));
    }

    public function create()
    {
        return view('admin.articleTags.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:article_tags',
        ]);

        ArticleTag::create($validated);

        return redirect()->route('admin.articleTags.index')
            ->with('success', 'Tag created successfully.');
    }

    public function edit($id)
    {
        $tag = ArticleTag::findOrFail($id);
        return view('admin.articleTags.form', compact('tag'));
    }

    public function update(Request $request, $id)
    {
        $tag = ArticleTag::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:article_tags,name,' . $id,
        ]);

        $tag->update($validated);

        return redirect()->route('admin.articleTags.index')
            ->with('success', 'Tag updated successfully.');
    }

    public function destroy($id)
    {
        $tag = ArticleTag::findOrFail($id);
        $tag->articles()->detach();
        $tag->delete();

        return redirect()->route('admin.articleTags.index')
            ->with('success', 'Tag deleted successfully.');
    }

    // API endpoint for Select2 integration
    public function search(Request $request)
    {
        $search = $request->get('q');
        $tags = ArticleTag::where('name', 'like', "%$search%")
            ->select('id', 'name as text')
            ->get();

        return response()->json(['results' => $tags]);
    }
}
