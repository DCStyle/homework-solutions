<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookGroup;
use App\Models\Category;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookGroupsController extends Controller
{
    public function index()
    {
        $groups = BookGroup::all()->sortBy('category_id');

        return view('admin.bookGroups.index', compact('groups'));
    }

    public function create()
    {
        $categories = Category::whereNull('parent_id')->with('children')->get();

        return view('admin.bookGroups.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
        ]);

        BookGroup::create([
            'name' => $request->name,
            'description' => $request->description,
            'category_id' => $request->category_id,
        ]);

        return redirect()->route('admin.bookGroups.index')->with('success', 'Book group created successfully.');
    }

    public function edit($slug)
    {
        $group = BookGroup::where('slug', $slug)->firstOrFail();

        $categories = Category::whereNull('parent_id')->with('children')->get();

        return view('admin.bookGroups.edit', compact('group', 'categories'));
    }

    public function update(Request $request, $slug)
    {
        $group = BookGroup::where('slug', $slug)->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'slug' => 'required|string',
            'category_id' => 'required|exists:categories,id',
        ]);

        $group->update([
            'name' => $request->name,
            'description' => $request->description,
            'slug' => $request->slug,
            'category_id' => $request->category_id,
        ]);

        return redirect()->route('admin.bookGroups.index')->with('success', 'Book group updated successfully.');
    }

    public function delete($slug)
    {
        $group = BookGroup::where('slug', $slug)->firstOrFail();

        return view('admin_layouts.delete', [
            'confirmLink' => route('admin.bookGroups.destroy', $group->slug),
            'name' => $group->name,
            'backLink' => route('admin.bookGroups.index'),
        ]);
    }

    public function destroy($slug)
    {
        $group = BookGroup::where('slug', $slug)->firstOrFail();
        $group->delete();

        return redirect()->route('admin.bookGroups.index')->with('success', 'Book group deleted successfully.');
    }
}
