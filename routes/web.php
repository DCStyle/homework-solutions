<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\MultiSearchController;
use App\Http\Controllers\SearchController;
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
Route::get('/book-groups/{group_slug}.html', [App\Http\Controllers\BookGroupsController::class, 'show'])->name('bookGroups.show');

// Books
Route::get('/books/{group_slug}.html', [App\Http\Controllers\BooksController::class, 'show'])->name('books.show');

// Book chapters
//Route::get('/book-chapters/{chapter_slug}.html', [App\Http\Controllers\BookChaptersController::class, 'show'])->name('bookChapters.show');

// Posts
Route::get('/posts/{post_slug}.html', [PostsController::class, 'show'])->name('posts.show');

// Articles
Route::prefix('articles')->group(function() {
    Route::get('/latest', [App\Http\Controllers\ArticlesController::class, 'latest'])->name('articles.latest');
    Route::get('/{article_slug}.html', [App\Http\Controllers\ArticlesController::class, 'show'])->name('articles.show');

    Route::get('/tags/search', [\App\Http\Controllers\ArticlesController::class, 'searchTags'])->name('admin.articles.searchTags');
});

// Images
Route::prefix('images')->group(function() {
    Route::post('/upload', [ImageController::class, 'upload'])->name('images.upload');
    Route::post('/attach', [ImageController::class, 'attach'])->name('images.attach');
    Route::delete('/{image}', [ImageController::class, 'destroy'])->name('images.destroy');
});

// Search
Route::get('/search', [SearchController::class, 'search'])->name('search');
Route::get('/multi-search', [MultiSearchController::class, 'search'])->name('multi-search');

// Admin
Route::prefix('admin')->middleware(['admin'])->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');

    // Categories management
    Route::prefix('categories')->group(function() {
        Route::get('/', [App\Http\Controllers\Admin\CategoriesController::class, 'index'])->name('admin.categories.index');

        Route::get('/create', [App\Http\Controllers\Admin\CategoriesController::class, 'create'])->name('admin.categories.create');
        Route::post('/', [App\Http\Controllers\Admin\CategoriesController::class, 'store'])->name('admin.categories.store');

        Route::get('/{id}/edit', [App\Http\Controllers\Admin\CategoriesController::class, 'edit'])->name('admin.categories.edit');
        Route::put('/{id}', [App\Http\Controllers\Admin\CategoriesController::class, 'update'])->name('admin.categories.update');

        Route::delete('/{id}', [App\Http\Controllers\Admin\CategoriesController::class, 'destroy'])->name('admin.categories.destroy');
    });

    // Book groups management
    Route::prefix('book-groups')->group(function() {
        Route::get('/', [App\Http\Controllers\Admin\BookGroupsController::class, 'index'])->name('admin.bookGroups.index');

        Route::get('/create', [App\Http\Controllers\Admin\BookGroupsController::class, 'create'])->name('admin.bookGroups.create');
        Route::post('/', [App\Http\Controllers\Admin\BookGroupsController::class, 'store'])->name('admin.bookGroups.store');

        Route::get('/{id}/edit', [App\Http\Controllers\Admin\BookGroupsController::class, 'edit'])->name('admin.bookGroups.edit');
        Route::put('/{group_slug}', [App\Http\Controllers\Admin\BookGroupsController::class, 'update'])->name('admin.bookGroups.update');

        Route::get('/{id}/delete', [App\Http\Controllers\Admin\BookGroupsController::class, 'delete'])->name('admin.bookGroups.delete');
        Route::delete('/{id}', [App\Http\Controllers\Admin\BookGroupsController::class, 'destroy'])->name('admin.bookGroups.destroy');
    });

    // Books management
    Route::prefix('books')->group(function() {
        Route::get('/', [App\Http\Controllers\Admin\BooksController::class, 'index'])->name('admin.books.index');

        Route::get('/create', [App\Http\Controllers\Admin\BooksController::class, 'create'])->name('admin.books.create');
        Route::post('/', [App\Http\Controllers\Admin\BooksController::class, 'store'])->name('admin.books.store');

        Route::get('/{id}/edit', [App\Http\Controllers\Admin\BooksController::class, 'edit'])->name('admin.books.edit');
        Route::put('/{id}', [App\Http\Controllers\Admin\BooksController::class, 'update'])->name('admin.books.update');

        Route::delete('/{id}', [App\Http\Controllers\Admin\BooksController::class, 'destroy'])->name('admin.books.destroy');

        Route::get('/{id}/chapters', [App\Http\Controllers\Admin\BooksController::class, 'chapters'])->name('admin.books.chapters');
        Route::get('/{id}/create-chapter', [App\Http\Controllers\Admin\BooksController::class, 'createChapter'])->name('admin.books.createChapter');
        Route::post('/{id}/chapters', [App\Http\Controllers\Admin\BooksController::class, 'storeChapter'])->name('admin.books.storeChapter');
    });

    // Book chapters management
    Route::prefix('book-chapters')->group(function() {
        Route::get('/{id}/edit', [App\Http\Controllers\Admin\BookChaptersController::class, 'edit'])->name('admin.bookChapters.edit');
        Route::put('/{id}', [App\Http\Controllers\Admin\BookChaptersController::class, 'update'])->name('admin.bookChapters.update');

        Route::delete('/{id}', [App\Http\Controllers\Admin\BookChaptersController::class, 'destroy'])->name('admin.bookChapters.destroy');

        Route::get('/{id}/posts', [App\Http\Controllers\Admin\BookChaptersController::class, 'posts'])->name('admin.bookChapters.posts');
        Route::get('/{id}/create-post', [App\Http\Controllers\Admin\BookChaptersController::class, 'createPost'])->name('admin.bookChapters.createPost');
        Route::post('/{id}/posts', [App\Http\Controllers\Admin\BookChaptersController::class, 'storePost'])->name('admin.bookChapters.storePost');
    });

    // Posts management
    Route::prefix('posts')->group(function() {
        Route::get('/', [App\Http\Controllers\Admin\PostsController::class, 'index'])->name('admin.posts.index');

        Route::get('/create', [App\Http\Controllers\Admin\PostsController::class, 'create'])->name('admin.posts.create');
        Route::post('/', [App\Http\Controllers\Admin\PostsController::class, 'store'])->name('admin.posts.store');

        Route::get('/{id}/edit', [App\Http\Controllers\Admin\PostsController::class, 'edit'])->name('admin.posts.edit');
        Route::put('/{id}', [App\Http\Controllers\Admin\PostsController::class, 'update'])->name('admin.posts.update');

        Route::delete('/{id}', [App\Http\Controllers\Admin\PostsController::class, 'destroy'])->name('admin.posts.destroy');
    });

    // Articles management
    Route::prefix('articles')->group(function() {
        Route::get('/', [\App\Http\Controllers\Admin\ArticlesController::class, 'index'])->name('admin.articles.index');

        Route::get('/create', [\App\Http\Controllers\Admin\ArticlesController::class, 'create'])->name('admin.articles.create');
        Route::post('/', [\App\Http\Controllers\Admin\ArticlesController::class, 'store'])->name('admin.articles.store');

        Route::get('/{id}/edit', [\App\Http\Controllers\Admin\ArticlesController::class, 'edit'])->name('admin.articles.edit');
        Route::put('/{id}', [\App\Http\Controllers\Admin\ArticlesController::class, 'update'])->name('admin.articles.update');

        Route::delete('/{id}', [\App\Http\Controllers\Admin\ArticlesController::class, 'destroy'])->name('admin.articles.destroy');

        Route::resource('/tags', \App\Http\Controllers\Admin\ArticlesController::class)->except(['show']);
    });

    Route::prefix('article-categories')->group(function() {
        Route::get('/', [\App\Http\Controllers\Admin\ArticleCategoriesController::class, 'index'])->name('admin.articleCategories.index');
        Route::get('/create', [\App\Http\Controllers\Admin\ArticleCategoriesController::class, 'create'])->name('admin.articleCategories.create');
        Route::post('/', [\App\Http\Controllers\Admin\ArticleCategoriesController::class, 'store'])->name('admin.articleCategories.store');
        Route::get('/{id}/edit', [\App\Http\Controllers\Admin\ArticleCategoriesController::class, 'edit'])->name('admin.articleCategories.edit');
        Route::put('/{id}', [\App\Http\Controllers\Admin\ArticleCategoriesController::class, 'update'])->name('admin.articleCategories.update');
        Route::delete('/{id}', [\App\Http\Controllers\Admin\ArticleCategoriesController::class, 'destroy'])->name('admin.articleCategories.destroy');
    });
});
