<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\CategoriesController;
use App\Models\Category;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Auth::routes();

// Categories
Route::get('/categories/{category_slug}.html', [App\Http\Controllers\CategoriesController::class, 'show'])->name('categories.show');

// Book groups
//Route::get('/book-groups/{group_slug}.html', [App\Http\Controllers\BookGroupsController::class, 'show'])->name('bookGroups.show');

// Books
Route::get('/books/{group_slug}.html', [App\Http\Controllers\BooksController::class, 'show'])->name('books.show');

// Book chapters
//Route::get('/book-chapters/{chapter_slug}.html', [App\Http\Controllers\BookChaptersController::class, 'show'])->name('bookChapters.show');

// Posts
Route::get('/posts/{post_slug}.html', [PostsController::class, 'show'])->name('posts.show');

// Admin
Route::prefix('admin')->middleware(['admin'])->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');

    // Categories management
    Route::get('/categories', [App\Http\Controllers\Admin\CategoriesController::class, 'index'])->name('admin.categories.index');

    Route::get('/categories/create', [App\Http\Controllers\Admin\CategoriesController::class, 'create'])->name('admin.categories.create');
    Route::post('/categories', [App\Http\Controllers\Admin\CategoriesController::class, 'store'])->name('admin.categories.store');

    Route::get('/categories/{category_slug}/edit', [App\Http\Controllers\Admin\CategoriesController::class, 'edit'])->name('admin.categories.edit');
    Route::put('/categories/{category_slug}', [App\Http\Controllers\Admin\CategoriesController::class, 'update'])->name('admin.categories.update');

    Route::get('/categories/{category_slug}/delete', [App\Http\Controllers\Admin\CategoriesController::class, 'delete'])->name('admin.categories.delete');
    Route::delete('/categories/{category_slug}', [App\Http\Controllers\Admin\CategoriesController::class, 'destroy'])->name('admin.categories.destroy');

    // Book groups management
    Route::get('/book-groups', [App\Http\Controllers\Admin\BookGroupsController::class, 'index'])->name('admin.bookGroups.index');
    Route::get('/book-groups/create', [App\Http\Controllers\Admin\BookGroupsController::class, 'create'])->name('admin.bookGroups.create');
    Route::post('/book-groups', [App\Http\Controllers\Admin\BookGroupsController::class, 'store'])->name('admin.bookGroups.store');

    Route::get('/book-groups/{group_slug}/edit', [App\Http\Controllers\Admin\BookGroupsController::class, 'edit'])->name('admin.bookGroups.edit');
    Route::put('/book-groups/{group_slug}', [App\Http\Controllers\Admin\BookGroupsController::class, 'update'])->name('admin.bookGroups.update');

    Route::get('/book-groups/{group_slug}/delete', [App\Http\Controllers\Admin\BookGroupsController::class, 'delete'])->name('admin.bookGroups.delete');
    Route::delete('/book-groups/{group_slug}', [App\Http\Controllers\Admin\BookGroupsController::class, 'destroy'])->name('admin.bookGroups.destroy');

    // Books management
    Route::get('/books', [App\Http\Controllers\Admin\BooksController::class, 'index'])->name('admin.books.index');
    Route::get('/books/create', [App\Http\Controllers\Admin\BooksController::class, 'create'])->name('admin.books.create');
    Route::post('/books', [App\Http\Controllers\Admin\BooksController::class, 'store'])->name('admin.books.store');

    Route::get('/books/{book_slug}/edit', [App\Http\Controllers\Admin\BooksController::class, 'edit'])->name('admin.books.edit');
    Route::put('/books/{book_slug}', [App\Http\Controllers\Admin\BooksController::class, 'update'])->name('admin.books.update');

    Route::get('/books/{book_slug}/delete', [App\Http\Controllers\Admin\BooksController::class, 'delete'])->name('admin.books.delete');
    Route::delete('/books/{book_slug}', [App\Http\Controllers\Admin\BooksController::class, 'destroy'])->name('admin.books.destroy');

    Route::get('/books/{book_slug}/chapters', [App\Http\Controllers\Admin\BooksController::class, 'chapters'])->name('admin.books.chapters');
    Route::get('/books/{book_slug}/create-chapter', [App\Http\Controllers\Admin\BooksController::class, 'createChapter'])->name('admin.books.createChapter');
    Route::post('/books/{book_slug}/chapters', [App\Http\Controllers\Admin\BooksController::class, 'storeChapter'])->name('admin.books.storeChapter');

    // Book chapters management
    Route::get('/book-chapters/{chapter_slug}/edit', [App\Http\Controllers\Admin\BookChaptersController::class, 'edit'])->name('admin.bookChapters.edit');
    Route::put('/book-chapters/{chapter_slug}', [App\Http\Controllers\Admin\BookChaptersController::class, 'update'])->name('admin.bookChapters.update');

    Route::get('/book-chapters/{chapter_slug}/delete', [App\Http\Controllers\Admin\BookChaptersController::class, 'delete'])->name('admin.bookChapters.delete');
    Route::delete('/book-chapters/{chapter_slug}', [App\Http\Controllers\Admin\BookChaptersController::class, 'destroy'])->name('admin.bookChapters.destroy');

    Route::get('/book-chapters/{chapter_slug}/posts', [App\Http\Controllers\Admin\BookChaptersController::class, 'posts'])->name('admin.bookChapters.posts');
    Route::get('/book-chapters/{chapter_slug}/create-post', [App\Http\Controllers\Admin\BookChaptersController::class, 'createPost'])->name('admin.bookChapters.createPost');
    Route::post('/book-chapters/{chapter_slug}/posts', [App\Http\Controllers\Admin\BookChaptersController::class, 'storePost'])->name('admin.bookChapters.storePost');

    // Posts management
    Route::get('/posts', [App\Http\Controllers\Admin\PostsController::class, 'index'])->name('admin.posts.index');
    Route::get('/posts/create', [App\Http\Controllers\Admin\PostsController::class, 'create'])->name('admin.posts.create');
    Route::post('/posts', [App\Http\Controllers\Admin\PostsController::class, 'store'])->name('admin.posts.store');

    Route::get('/posts/{post_slug}/edit', [App\Http\Controllers\Admin\PostsController::class, 'edit'])->name('admin.posts.edit');
    Route::put('/posts/{post_slug}', [App\Http\Controllers\Admin\PostsController::class, 'update'])->name('admin.posts.update');

    Route::get('/posts/{post_slug}/delete', [App\Http\Controllers\Admin\PostsController::class, 'delete'])->name('admin.posts.delete');
    Route::delete('/posts/{post_slug}', [App\Http\Controllers\Admin\PostsController::class, 'destroy'])->name('admin.posts.destroy');
});
