<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArticleCategory;
use App\Services\SitemapService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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

        $category = ArticleCategory::create($validated);
        
        // Update sitemap entry
        SitemapService::updateEntry('article-category', $category);
        
        // Clear sitemap cache
        $this->clearSitemapCache();

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
        
        // Update sitemap entry
        SitemapService::updateEntry('article-category', $category);
        
        // Clear sitemap cache
        $this->clearSitemapCache();

        return redirect()->route('admin.articleCategories.index')->with('success', 'Cập nhật danh mục tin tức thành công.');
    }

    /**
     * Remove the specified article category from database.
     */
    public function destroy(string $id)
    {
        $category = ArticleCategory::findOrFail($id);
        
        // Remove from sitemap before deleting
        SitemapService::removeEntry('article-category', $category->id);
        
        // Clear sitemap cache
        $this->clearSitemapCache();
        
        $category->delete();

        return redirect()->route('admin.articleCategories.index')->with('success', 'Xóa danh mục tin tức thành công.');
    }
    
    /**
     * Clear sitemap cache
     */
    private function clearSitemapCache()
    {
        Cache::forget('sitemap.index.data');
        Cache::forget('sitemap.article-category.page.1.data');
    }
}
