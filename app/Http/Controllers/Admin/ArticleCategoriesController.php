<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArticleCategory;
use Illuminate\Http\Request;

class ArticleCategoriesController extends Controller
{
    /**
     * Display a listing of the article category.
     */
    public function index()
    {
        $categories = ArticleCategory::all();
        return view('admin.articleCategories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new article category.
     */
    public function create()
    {
        return view('admin.articleCategories.form');
    }

    /**
     * Store a newly created article category in database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        ArticleCategory::create($validated);

        return redirect()->route('admin.articleCategories.index')->with('success', 'Tạo danh mục tin tức thành công.');
    }

    /**
     * Show the form for editing the specified article category.
     */
    public function edit(string $id)
    {
        $category = ArticleCategory::findOrFail($id);
        return view('admin.articleCategories.form', compact('category'));
    }

    /**
     * Update the specified article category in database.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:article_categories,slug,' . $id,
            'description' => 'nullable|string',
        ]);

        $category = ArticleCategory::findOrFail($id);
        $category->update($validated);

        return redirect()->route('admin.articleCategories.index')->with('success', 'Cập nhật danh mục tin tức thành công.');
    }

    /**
     * Remove the specified article category from database.
     */
    public function destroy(string $id)
    {
        $category = ArticleCategory::findOrFail($id);
        $category->delete();

        return redirect()->route('admin.articleCategories.index')->with('success', 'Xóa danh mục tin tức thành công.');
    }
}
