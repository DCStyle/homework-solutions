<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SearchController;
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
        $books = Book::join('book_groups', 'books.book_group_id', '=', 'book_groups.id')
            ->join('categories', 'book_groups.category_id', '=', 'categories.id')
            ->orderBy('categories.id')
            ->orderBy('book_groups.id')
            ->select('books.*')
            ->paginate(200);

        return view('admin.books.index', compact('books'));
    }

    public function create()
    {
        $groups = BookGroup::all()->sortBy('category_id');

        return view('admin.books.form', compact('groups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'book_group_id' => 'required|exists:book_groups,id',
        ]);

        Book::create($validated);

        return redirect()->route('admin.books.index')->with('success', 'Thêm sách thành công.');
    }

    public function edit($id)
    {
        $book = Book::whereId($id)->firstOrFail();

        $groups = BookGroup::all()->sortBy('category_id');

        return view('admin.books.form', compact('book', 'groups'));
    }

    public function update(Request $request, $id)
    {
        $book = Book::whereId($id)->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'slug' => 'required|string|max:255|unique:books,slug,' . $book->id,
            'book_group_id' => 'required|exists:book_groups,id',
        ]);

        $book->update($validated);

        return redirect()->route('admin.books.index')->with('success', 'Cập nhật sách thành công.');
    }

    public function destroy($id)
    {
        $book = Book::whereId($id)->firstOrFail();
        $book->delete();

        return redirect()->route('admin.books.index')->with('success', 'Xóa sách thành công.');
    }

    public function chapters($id)
    {
        $book = Book::whereId($id)->firstOrFail();

        $chapters = $book->chapters()->get()->sortBy('created_at');

        return view('admin.chapters.index', compact('book', 'chapters'));
    }

    public function createChapter($id)
    {
        $book = Book::whereId($id)->firstOrFail();

        return view('admin.chapters.form', compact('book'));
    }

    public function storeChapter(Request $request, $id)
    {
        $book = Book::whereId($id)->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        BookChapter::create([
            'name' => $request->name,
            'book_id' => $book->id
        ]);

        return redirect()->route('admin.books.chapters', $id)->with('success', 'Thêm chương sách thành công.');
    }
}
