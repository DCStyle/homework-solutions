<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use function Laravel\Prompts\error;

class CategoriesController extends Controller
{
    public function index()
    {
        $categories = Category::whereNull('parent_id')->get();

        return view('admin.categories.index', compact('categories'));
    }

    // Display category creation view
    public function create()
    {
        $categories = Category::whereNull('parent_id')->get();
        return view('admin.categories.form', compact('categories'));
    }

    // Store the new category in the database
    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Create the new category
        Category::create($validated);

        // Redirect back to the form or another page
        return redirect()->route('admin.categories.index')->with('success', 'Thêm danh mục thành công.');
    }

    // Display the form for editing a category
    public function edit($id)
    {
        // Find the category by slug
        $category = Category::whereId($id)->firstOrFail();

        $categories = Category::whereNull('parent_id')->get();

        return view('admin.categories.form', compact('category', 'categories'));
    }

    // Handle the update of the category
    public function update(Request $request, $id)
    {
        $category = Category::whereId($id)->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug,' . $category->id,
            'description' => 'nullable|string',
        ]);

        $category->update($validated);

        return redirect()->route('admin.categories.index')->with('success', 'Cập nhật danh mục thành công.');
    }

    // Handle category destroy
    public function destroy($id)
    {
        $category = Category::whereId($id)->firstOrFail();
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Xóa danh mục thành công.');
    }
}
