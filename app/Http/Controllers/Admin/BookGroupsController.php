<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookGroup;
use App\Models\Category;
use App\Models\Book;
use App\Services\SitemapService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BookGroupsController extends Controller
{
    public function index()
    {
        $groups = BookGroup::orderBy('category_id')->paginate(20);

        return view('admin.bookGroups.index', compact('groups'));
    }

    public function create()
    {
        $categories = Category::whereNull('parent_id')->get();

        return view('admin.bookGroups.form', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
        ]);

        $bookGroup = BookGroup::create($validated);
        
        // Update sitemap entry
        SitemapService::updateEntry('book-group', $bookGroup);
        
        // Clear sitemap cache
        $this->clearSitemapCache();

        return redirect()->route('admin.bookGroups.index')->with('success', 'Môn học đã được thêm thành công.');
    }

    public function edit($id)
    {
        $group = BookGroup::whereId($id)->firstOrFail();

        $categories = Category::whereNull('parent_id')->get();

        return view('admin.bookGroups.form', compact('group', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $group = BookGroup::whereId($id)->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'slug' => 'required|string|max:255|unique:book_groups,slug,' . $group->id,
            'category_id' => 'required|exists:categories,id',
        ]);

        $group->update($validated);
        
        // Update sitemap entry
        SitemapService::updateEntry('book-group', $group);
        
        // Clear sitemap cache
        $this->clearSitemapCache();

        return redirect()->route('admin.bookGroups.index')->with('success', 'Môn học đã được cập nhật thành công.');
    }

    public function destroy($id)
    {
        $group = BookGroup::whereId($id)->firstOrFail();
        
        // Remove from sitemap before deleting
        SitemapService::removeEntry('book-group', $group->id);
        
        // Clear sitemap cache
        $this->clearSitemapCache();
        
        $group->delete();

        return redirect()->route('admin.bookGroups.index')->with('success', 'Môn học đã được xóa thành công.');
    }
    
    /**
     * Clear sitemap cache
     */
    private function clearSitemapCache()
    {
        Cache::forget('sitemap.index.data');
        Cache::forget('sitemap.book-group.page.1.data');
    }
}
