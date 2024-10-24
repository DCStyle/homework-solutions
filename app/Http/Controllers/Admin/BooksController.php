<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookChapter;
use App\Models\BookGroup;
use App\Models\Category;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BooksController extends Controller
{
    public function index()
    {
        $books = Book::all()->sortBy('book_group_id');

        return view('admin.books.index', compact('books'));
    }

    public function create()
    {
        $groups = BookGroup::all()->sortBy('category_id');

        return view('admin.books.create', compact('groups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'group_id' => 'required|exists:book_groups,id',
        ]);

        Book::create([
            'name' => $request->name,
            'description' => $request->description,
            'book_group_id' => $request->group_id,
        ]);

        return redirect()->route('admin.books.index')->with('success', 'Book created successfully.');
    }

    public function edit($slug)
    {
        $book = Book::where('slug', $slug)->firstOrFail();

        $groups = BookGroup::all()->sortBy('category_id');

        return view('admin.books.edit', compact('book', 'groups'));
    }

    public function update(Request $request, $slug)
    {
        $book = Book::where('slug', $slug)->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'slug' => 'required|string',
            'group_id' => 'required|exists:book_groups,id',
        ]);

        $book->update([
            'name' => $request->name,
            'description' => $request->description,
            'slug' => $request->slug,
            'book_group_id' => $request->group_id,
        ]);

        return redirect()->route('admin.books.index')->with('success', 'Book updated successfully.');
    }

    public function delete($slug)
    {
        $book = Book::where('slug', $slug)->firstOrFail();

        return view('admin_layouts.delete', [
            'confirmLink' => route('admin.books.destroy', $book->slug),
            'name' => $book->name,
            'backLink' => route('admin.books.index'),
        ]);
    }

    public function destroy($slug)
    {
        $book = Book::where('slug', $slug)->firstOrFail();
        $book->delete();

        return redirect()->route('admin.books.index')->with('success', 'Book deleted successfully.');
    }

    public function chapters($slug)
    {
        $book = Book::where('slug', $slug)->firstOrFail();

        $chapters = $book->chapters()->get();

        return view('admin.chapters.index', compact('book', 'chapters'));
    }

    public function createChapter($slug)
    {
        $book = Book::where('slug', $slug)->firstOrFail();

        return view('admin.chapters.create', compact('book'));
    }

    public function storeChapter(Request $request, $slug)
    {
        $book = Book::where('slug', $slug)->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        BookChapter::create([
            'name' => $request->name,
            'book_id' => $book->id
        ]);

        return redirect()->route('admin.books.chapters', $slug)->with('success', 'Book chapter created successfully.');
    }
}
