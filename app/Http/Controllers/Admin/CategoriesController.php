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
        $categories = Category::whereNull('parent_id')->with('children')->get();

        return view('admin.categories.index', compact('categories'));
    }

    // Display category creation view
    public function create()
    {
        $categories = Category::whereNull('parent_id')->with('children')->get();
        return view('admin.categories.create', compact('categories'));
    }

    // Store the new category in the database
    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id', // Ensure parent exists if provided
        ]);

        // Create the new category
        Category::create([
            'name' => $request->name,
            'description' => $request->description,
            'parent_id' => $request->parent_id,
        ]);

        // Redirect back to the form or another page
        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully.');
    }

    // Display the form for editing a category
    public function edit($slug)
    {
        // Find the category by slug
        $category = Category::where('slug', $slug)->firstOrFail();

        $categories = Category::whereNull('parent_id')->with('children')->get(); // For parent category selection

        return view('admin.categories.edit', compact('category', 'categories'));
    }

    // Handle the update of the category
    public function update(Request $request, $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $category->update([
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'parent_id' => $request->parent_id,
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully.');
    }

    // Display category delete view
    public function delete($slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        return view('admin_layouts.delete', [
            'confirmLink' => route('admin.categories.destroy', $category->slug),
            'name' => $category->name,
            'backLink' => route('admin.categories.index'),
        ]);
    }

    // Handle category destroy
    public function destroy($slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully.');
    }
}
