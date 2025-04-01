<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\MultiSearchController;
use App\Http\Controllers\SearchController;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

// Google login
Route::get('login/google', [App\Http\Controllers\Auth\LoginController::class, 'redirectToGoogle'])
    ->name('login.google');
Route::get('login/google/callback', [App\Http\Controllers\Auth\LoginController::class, 'handleGoogleCallback']);

// Facebook login
Route::get('login/facebook', [App\Http\Controllers\Auth\LoginController::class, 'redirectToFacebook'])
    ->name('login.facebook');
Route::get('login/facebook/callback', [App\Http\Controllers\Auth\LoginController::class, 'handleFacebookCallback']);

// Twitter login
Route::get('login/twitter', [App\Http\Controllers\Auth\LoginController::class, 'redirectToTwitter'])
    ->name('login.twitter');
Route::get('login/twitter/callback', [App\Http\Controllers\Auth\LoginController::class, 'handleTwitterCallback']);

// User type selection routes
Route::middleware(['auth'])->group(function () {
    Route::get('/user-type', [App\Http\Controllers\Auth\UserTypeController::class, 'showUserTypeForm'])
        ->name('user-type.show');
    Route::post('/user-type', [App\Http\Controllers\Auth\UserTypeController::class, 'updateUserType'])
        ->name('user-type.update');
});

// Admin
Route::prefix('admin')->middleware(['admin'])->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');

    // AI Dashboard
    Route::prefix('ai-dashboard')->group(function () {
        // Main dashboard
        Route::get('/', [App\Http\Controllers\Admin\AIDashboardController::class, 'index'])->name('admin.ai-dashboard.index');
        Route::get('/stats-api', [App\Http\Controllers\Admin\AIDashboardController::class, 'getStats'])->name('admin.ai-dashboard.stats-api');
        Route::get('/lazy-load-prompts', [App\Http\Controllers\Admin\AIDashboardController::class, 'lazyLoadPrompts'])->name('lazy-load-prompts');
        Route::get('/system-message/{type}', [App\Http\Controllers\Admin\AIDashboardController::class, 'getSystemMessage'])->name('admin.ai-dashboard.system-message');

        // SEO Stats
        Route::get('/stats', [App\Http\Controllers\Admin\AIDashboardController::class, 'stats'])->name('admin.ai-dashboard.stats');
        Route::get('/stats/data', [App\Http\Controllers\Admin\AIDashboardController::class, 'getStatsData'])->name('admin.ai-dashboard.stats.data');

        // AI Playground
        Route::get('/playground', [App\Http\Controllers\Admin\AIDashboardController::class, 'playground'])->name('admin.ai-dashboard.playground');
        Route::get('/content/categories', [App\Http\Controllers\Admin\AIDashboardController::class, 'getCategories']);
        Route::get('/content/book-groups/{categoryId}', [App\Http\Controllers\Admin\AIDashboardController::class, 'getBookGroups']);
        Route::get('/content/books/{groupId}', [App\Http\Controllers\Admin\AIDashboardController::class, 'getBooks']);
        Route::get('/content/chapters/{bookId}', [App\Http\Controllers\Admin\AIDashboardController::class, 'getChapters']);
        Route::get('/content/posts/{chapterId}', [App\Http\Controllers\Admin\AIDashboardController::class, 'getPosts']);
        Route::get('/content/details/{type}/{id}', [App\Http\Controllers\Admin\AIDashboardController::class, 'getContentDetails']);

        // Provider and model selection
        Route::get('/providers', [App\Http\Controllers\Admin\AIDashboardController::class, 'getProviders'])->name('admin.ai-dashboard.providers');
        Route::get('/providers/{provider}/models', [App\Http\Controllers\Admin\AIDashboardController::class, 'getModelsForProvider'])->name('admin.ai-dashboard.provider-models');

        // API Endpoints
        Route::post('/generate-sample', [App\Http\Controllers\Admin\AIDashboardController::class, 'generateSample'])->name('admin.ai-dashboard.generate-sample');
        Route::post('/apply-prompt', [App\Http\Controllers\Admin\AIDashboardController::class, 'applyPrompt'])->name('admin.ai-dashboard.apply-prompt');
        Route::post('/save-prompt', [App\Http\Controllers\Admin\AIDashboardController::class, 'savePrompt'])->name('admin.ai-dashboard.save-prompt');
        Route::get('/prompts/default', [App\Http\Controllers\Admin\AIDashboardController::class, 'getDefaultPrompt'])->name('admin.ai-dashboard.prompts.default');
        Route::get('/prompts/by-type', [App\Http\Controllers\Admin\AIDashboardController::class, 'getPromptsByType'])->name('admin.ai-dashboard.prompts.by-type');
        Route::get('/prompts/{id}', [App\Http\Controllers\Admin\AIDashboardController::class, 'getPrompt'])->name('admin.ai-dashboard.get-prompt');
        Route::delete('/prompts/{id}', [App\Http\Controllers\Admin\AIDashboardController::class, 'deletePrompt'])->name('admin.ai-dashboard.delete-prompt');

        // Queue management routes
        Route::post('/queue-generation', [App\Http\Controllers\Admin\AIDashboardController::class, 'queueBulkGeneration'])->name('admin.ai-dashboard.queue-generation');
        Route::get('/jobs', [App\Http\Controllers\Admin\AIDashboardController::class, 'jobsView'])->name('admin.ai-dashboard.jobs');
        Route::get('/jobs/list', [App\Http\Controllers\Admin\AIDashboardController::class, 'listJobs'])->name('admin.ai-dashboard.jobs.list');
        Route::get('/jobs/{jobId}', [App\Http\Controllers\Admin\AIDashboardController::class, 'checkJobStatus'])->name('admin.ai-dashboard.jobs.status');
        Route::post('/jobs/{jobId}/rerun', [App\Http\Controllers\Admin\AIDashboardController::class, 'rerunJob'])->name('admin.ai-dashboard.rerun-job');
        Route::post('/jobs/{jobId}/retry', [App\Http\Controllers\Admin\AIDashboardController::class, 'retryFailedItems'])->name('admin.ai-dashboard.retry-job');
        Route::post('/jobs/{jobId}/cancel', [App\Http\Controllers\Admin\AIDashboardController::class, 'cancelJob'])->name('admin.ai-dashboard.cancel-job');

    });

    // AI API Keys management
    Route::prefix('ai-api-keys')->group(function() {
        Route::get('/', [App\Http\Controllers\Admin\AIApiKeyController::class, 'index'])->name('admin.ai_api_keys.index');
        Route::post('/', [App\Http\Controllers\Admin\AIApiKeyController::class, 'store'])->name('admin.ai_api_keys.store');
        Route::put('/{id}', [App\Http\Controllers\Admin\AIApiKeyController::class, 'update'])->name('admin.ai_api_keys.update');
        Route::delete('/{id}', [App\Http\Controllers\Admin\AIApiKeyController::class, 'destroy'])->name('admin.ai_api_keys.destroy');
        Route::patch('/{id}/toggle-active', [App\Http\Controllers\Admin\AIApiKeyController::class, 'toggleActive'])->name('admin.ai_api_keys.toggle_active');
        Route::get('/{id}/test', [App\Http\Controllers\Admin\AIApiKeyController::class, 'testKey'])->name('admin.ai_api_keys.test');
    });

    // Wiki Q&A Admin Management
    Route::prefix('wiki')->name('admin.wiki.')->group(function() {
        // Dashboard and settings
        Route::get('/', [App\Http\Controllers\Admin\WikiSettingsController::class, 'index'])->name('dashboard');
        Route::get('/settings', [App\Http\Controllers\Admin\WikiSettingsController::class, 'index'])->name('settings');
        Route::put('/settings', [App\Http\Controllers\Admin\WikiSettingsController::class, 'update'])->name('settings.update');

        // Moderation
        Route::get('/moderation', [App\Http\Controllers\Admin\WikiSettingsController::class, 'moderation'])->name('moderation');
        Route::get('/questions', [App\Http\Controllers\Admin\WikiSettingsController::class, 'questions'])->name('questions');

        // Question management
        Route::prefix('questions')->name('questions.')->group(function() {
            Route::patch('/{question}/status', [App\Http\Controllers\Admin\WikiSettingsController::class, 'updateStatus'])->name('status');
            Route::put('/{questionId}/approve', [App\Http\Controllers\Admin\WikiSettingsController::class, 'approveQuestion'])->name('approve');
            Route::put('/{questionId}/reject', [App\Http\Controllers\Admin\WikiSettingsController::class, 'rejectQuestion'])->name('reject');
        });
    });

    // Settings
    Route::prefix('settings')->group(function() {
        Route::get('/', [App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('admin.settings.index');
        Route::put('/', [App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('admin.settings.update');
        Route::get('/home', [App\Http\Controllers\Admin\SettingsController::class, 'home'])->name('admin.settings.home');
        Route::put('/home', [App\Http\Controllers\Admin\SettingsController::class, 'updateHome'])->name('admin.settings.updateHome');
    });

    // Footer Management
    Route::prefix('footer')->group(function() {
        Route::get('/', [App\Http\Controllers\Admin\FooterController::class, 'index'])->name('admin.footer.index');

        // Column routes
        Route::post('/columns', [App\Http\Controllers\Admin\FooterController::class, 'storeColumn'])->name('admin.footer.columns.store');
        Route::put('/columns/{column}', [App\Http\Controllers\Admin\FooterController::class, 'updateColumn'])->name('admin.footer.columns.update');
        Route::delete('/columns/{column}', [App\Http\Controllers\Admin\FooterController::class, 'destroyColumn'])->name('admin.footer.columns.destroy');

        // Link routes
        Route::post('/columns/{column}/links', [App\Http\Controllers\Admin\FooterController::class, 'storeLink'])->name('admin.footer.links.store');
        Route::put('/links/{link}', [App\Http\Controllers\Admin\FooterController::class, 'updateLink'])->name('admin.footer.links.update');
        Route::delete('/links/{link}', [App\Http\Controllers\Admin\FooterController::class, 'destroyLink'])->name('admin.footer.links.destroy');

        // Positions
        Route::post('/positions', [App\Http\Controllers\Admin\FooterController::class, 'updatePositions'])->name('admin.footer.positions.update');
    });

    // Menu management
    Route::prefix('menu')->group(function() {
        Route::get('/', [App\Http\Controllers\Admin\MenuController::class, 'index'])->name('admin.menu.index');

        Route::get('/create', [App\Http\Controllers\Admin\MenuController::class, 'create'])->name('admin.menu.create');
        Route::post('/', [App\Http\Controllers\Admin\MenuController::class, 'store'])->name('admin.menu.store');

        Route::get('/{menuItem}/edit', [App\Http\Controllers\Admin\MenuController::class, 'edit'])->name('admin.menu.edit');
        Route::put('/{menuItem}', [App\Http\Controllers\Admin\MenuController::class, 'update'])->name('admin.menu.update');

        Route::delete('/{menuItem}', [App\Http\Controllers\Admin\MenuController::class, 'destroy'])->name('admin.menu.destroy');

        Route::post('/reorder', [App\Http\Controllers\Admin\MenuController::class, 'reorder'])->name('admin.menu.reorder');
    });

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

        // Add route for clearing post cache
        Route::post('/{id}/clear-cache', [PostsController::class, 'clearCache'])->name('admin.posts.clearCache');
    });

    // Articles management
    Route::prefix('articles')->group(function() {
        Route::get('/', [\App\Http\Controllers\Admin\ArticlesController::class, 'index'])->name('admin.articles.index');

        Route::get('/create', [\App\Http\Controllers\Admin\ArticlesController::class, 'create'])->name('admin.articles.create');
        Route::post('/', [\App\Http\Controllers\Admin\ArticlesController::class, 'store'])->name('admin.articles.store');

        Route::get('/{id}/edit', [\App\Http\Controllers\Admin\ArticlesController::class, 'edit'])->name('admin.articles.edit');
        Route::put('/{id}', [\App\Http\Controllers\Admin\ArticlesController::class, 'update'])->name('admin.articles.update');

        Route::delete('/{id}', [\App\Http\Controllers\Admin\ArticlesController::class, 'destroy'])->name('admin.articles.destroy');
    });

    Route::name('admin.')->group(function() {
        Route::resource('article-tags', \App\Http\Controllers\Admin\ArticleTagsController::class);
        Route::get('article-tags-search', [\App\Http\Controllers\Admin\ArticleTagsController::class, 'search'])->name('article-tags.search');
    });

    Route::prefix('article-categories')->group(function() {
        Route::get('/', [\App\Http\Controllers\Admin\ArticleCategoriesController::class, 'index'])->name('admin.articleCategories.index');
        Route::get('/create', [\App\Http\Controllers\Admin\ArticleCategoriesController::class, 'create'])->name('admin.articleCategories.create');
        Route::post('/', [\App\Http\Controllers\Admin\ArticleCategoriesController::class, 'store'])->name('admin.articleCategories.store');
        Route::get('/{id}/edit', [\App\Http\Controllers\Admin\ArticleCategoriesController::class, 'edit'])->name('admin.articleCategories.edit');
        Route::put('/{id}', [\App\Http\Controllers\Admin\ArticleCategoriesController::class, 'update'])->name('admin.articleCategories.update');
        Route::delete('/{id}', [\App\Http\Controllers\Admin\ArticleCategoriesController::class, 'destroy'])->name('admin.articleCategories.destroy');
    });

    // User management routes
    Route::prefix('users')->group(function() {
        Route::get('/', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('admin.users.index');
        Route::get('/create', [App\Http\Controllers\Admin\UserController::class, 'create'])->name('admin.users.create');
        Route::post('/', [App\Http\Controllers\Admin\UserController::class, 'store'])->name('admin.users.store');
        Route::get('/{user}', [App\Http\Controllers\Admin\UserController::class, 'show'])->name('admin.users.show');
        Route::get('/{user}/edit', [App\Http\Controllers\Admin\UserController::class, 'edit'])->name('admin.users.edit');
        Route::put('/{user}', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('admin.users.update');
        Route::delete('/{user}', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('admin.users.destroy');
        Route::patch('/{user}/toggle-admin', [App\Http\Controllers\Admin\UserController::class, 'toggleAdmin'])->name('admin.users.toggle_admin');
    });
});

// Categories
Route::get('/categories/{category_slug}.html', [App\Http\Controllers\CategoriesController::class, 'show'])->name('categories.show');

// Book groups
Route::get('/book-groups/{group_slug}.html', [App\Http\Controllers\BookGroupsController::class, 'show'])->name('bookGroups.show');

// Books
Route::get('/books/{book_slug}.html', [App\Http\Controllers\BooksController::class, 'show'])->name('books.show');

// Book chapters
Route::get('/book-chapters/{chapter_slug}.html', [App\Http\Controllers\BookChaptersController::class, 'show'])->name('bookChapters.show');

// Posts
Route::get('/posts/{post_slug}.html', [PostsController::class, 'show'])->name('posts.show');

// Attachments
Route::get('/attachments/{attachment}/download', [App\Http\Controllers\AttachmentsController::class, 'download'])->name('attachments.download');
Route::get('/attachments/{attachment}/process-download', [App\Http\Controllers\AttachmentsController::class, 'processDownload'])->name('attachments.process-download');
Route::get('/attachments/{attachment}/preview', [App\Http\Controllers\AttachmentsController::class, 'preview'])->name('attachments.preview');

// Articles
Route::prefix('articles')->group(function() {
    Route::get('/latest', [App\Http\Controllers\ArticlesController::class, 'latest'])->name('articles.latest');
    Route::get('/{article_slug}.html', [App\Http\Controllers\ArticlesController::class, 'show'])->name('articles.show');

    Route::get('/tags/search', [\App\Http\Controllers\ArticlesController::class, 'searchTags'])->name('admin.articles.searchTags');
});

// Article categories
Route::get('/article-categories/{category_slug}.html', [App\Http\Controllers\ArticleCategoriesController::class, 'show'])->name('article-categories.show');

// Wiki Q&A System Routes
Route::prefix('hoi-dap')->name('wiki.')->group(function () {
    // Main pages
    Route::get('/', [App\Http\Controllers\WikiController::class, 'index'])->name('index');

    // Feed
    Route::get('feed', [App\Http\Controllers\WikiFeedController::class, 'index'])->name('feed');
    Route::get('feed/{categorySlug}', [App\Http\Controllers\WikiFeedController::class, 'categoryFeed'])->name('feed.category');
    Route::get('feed/{categorySlug}/{bookGroupSlug}', [App\Http\Controllers\WikiFeedController::class, 'bookGroupFeed'])->name('feed.bookGroup');

    Route::get('/tim-kiem', [App\Http\Controllers\WikiController::class, 'search'])->name('search');

    // Questions - Creation flow
    Route::prefix('cau-hoi')->name('questions.')->group(function() {
        // Create question form
        Route::get('/tao-moi', [App\Http\Controllers\WikiQuestionController::class, 'create'])
            ->name('create')
            ->middleware('auth');

        // Store a new question
        Route::post('/', [App\Http\Controllers\WikiQuestionController::class, 'store'])
            ->name('store')
            ->middleware('auth');

        // Success page after question submission
        Route::get('/{question}/thanh-cong', [App\Http\Controllers\WikiQuestionController::class, 'success'])
            ->name('success')
            ->middleware('auth');
    });

    // Show a specific question by category and slug - Public access
    Route::get('/{categorySlug}/{questionSlug}', [App\Http\Controllers\WikiController::class, 'show'])
        ->name('show');
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

// PayOS Integration
Route::prefix('/payos')->group(function() {
    Route::get('/', function () {
        return view('checkout');
    });

    Route::get('/success.html', function () {
        return view('success');
    });

    Route::get('/cancel.html', function () {
        return view('cancel');
    });

    Route::post('/create-payment-link', [App\Http\Controllers\PayOS\CheckoutController::class, 'createPaymentLink']);

    Route::prefix('/order')->group(function () {
        Route::post('/create', [App\Http\Controllers\PayOS\OrderController::class, 'createOrder']);
        Route::get('/{id}', [App\Http\Controllers\PayOS\OrderController::class, 'getPaymentLinkInfoOfOrder']);
        Route::put('/{id}', [App\Http\Controllers\PayOS\OrderController::class, 'cancelPaymentLinkOfOrder']);
    });

    Route::prefix('/payment')->group(function () {
        Route::post('/payos', [App\Http\Controllers\PayOS\PaymentController::class, 'handlePayOSWebhook']);
    });
});

// Route::get('/{path?}', [ContentController::class, 'show'])
//     ->where('path', '^(?!api|admin|proxy)(?!.*\.(jpg|jpeg|png|gif|bmp|webp|mp4|avi|mov|wmv|flv|mp3|wav|pdf|doc|docx|xls|xlsx|zip|rar)).*$')
//     ->middleware(['web'])
//     ->defaults('path', '')
//     ->name('content.show');

// Sitemap Routes
Route::get('sitemap.xml', [App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap.index');
Route::get('sitemap-{type}-{page}.xml', [App\Http\Controllers\SitemapController::class, 'showType'])
    ->where('type', '[a-z\-]+')
    ->where('page', '[0-9]+')
    ->name('sitemap.type');
Route::get('robots.txt', [App\Http\Controllers\SitemapController::class, 'robots']);

// Legacy redirects for old sitemap URLs
Route::redirect('sitemap-categories.xml', 'sitemap-category-1.xml');
Route::redirect('sitemap-book-groups.xml', 'sitemap-book-group-1.xml');
Route::redirect('sitemap-books.xml', 'sitemap-book-1.xml');
Route::redirect('sitemap-book-chapters.xml', 'sitemap-book-chapter-1.xml');
Route::redirect('sitemap-posts.xml', 'sitemap-post-1.xml');
Route::redirect('sitemap-article-categories.xml', 'sitemap-article-category-1.xml');
Route::redirect('sitemap-articles.xml', 'sitemap-article-1.xml');
Route::redirect('sitemap-posts-{page}.xml', 'sitemap-post-{page}.xml')->where('page', '[0-9]+');
Route::redirect('sitemap-articles-{page}.xml', 'sitemap-article-{page}.xml')->where('page', '[0-9]+');
