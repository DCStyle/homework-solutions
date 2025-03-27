<?php

use App\Http\Controllers\Api\AttachmentController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\WikiAnswerController;
use App\Http\Controllers\Api\WikiCommentsController;
use App\Http\Controllers\Api\WikiFeedController;
use App\Http\Controllers\Api\WikiQuestionsController;
use App\Http\Controllers\WikiQuestionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('posts/import', [PostController::class, 'importPostFromJSON']);

Route::post('attachments/upload', [AttachmentController::class, 'upload']);
Route::delete('attachments/{attachment}', [AttachmentController::class, 'destroy']);

// Wiki Q&A System API Routes
Route::prefix('wiki')->group(function () {
    // Question-related endpoints
    Route::prefix('questions')->group(function () {
        // Search endpoint - public
        Route::get('/search', [WikiQuestionsController::class, 'search']);

        // Get detailed question with answers and comments - public
        Route::get('/{question}', [WikiQuestionsController::class, 'show'])
            ->where('question', '[0-9]+');

        // Stream AI-generated answer - public
        Route::get('/{question}/stream', [WikiQuestionController::class, 'stream'])
            ->name('api.wiki.questions.stream');

        // Check if an answer exists - public
        Route::get('/{question}/check-answer', [WikiQuestionController::class, 'checkAnswer'])
            ->name('api.wiki.questions.check-answer');

        // Answer-related endpoints - authenticated
        Route::middleware('auth:sanctum')->group(function () {
            // Create a new answer for a question
            Route::post('/{question}/answers', [WikiAnswerController::class, 'store'])
                ->name('api.wiki.answers.store');

            // Update an existing answer
            Route::put('/{question}/answers/{answerId}', [WikiAnswerController::class, 'update'])
                ->name('api.wiki.answers.update');

            // Delete an answer
            Route::delete('/{question}/answers/{answerId}', [WikiAnswerController::class, 'destroy'])
                ->name('api.wiki.answers.destroy');
        });
        
        // Get more answers for a question - public
        Route::get('/{question}/answers', [WikiAnswerController::class, 'getMoreAnswers'])
            ->name('api.wiki.answers.get-more');

    });

    // Comments resource - authenticated for mutations
    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('comments', WikiCommentsController::class)
            ->only(['store', 'update', 'destroy']);
    });

    // Public route for getting comments on a question
    Route::get('comments', [WikiCommentsController::class, 'getForQuestion'])
        ->name('api.wiki.comments.get-for-question');
    
    // Feed endpoints - public
    Route::prefix('feed')->group(function () {
        // Get paginated questions for the feed
        Route::get('/questions', [WikiFeedController::class, 'getFeedQuestions'])
            ->name('api.wiki.feed.questions');

        // Get trending questions
        Route::get('/trending', [WikiFeedController::class, 'getTrendingQuestions'])
            ->name('api.wiki.feed.trending');

        // Get latest questions
        Route::get('/latest', [WikiFeedController::class, 'getLatestQuestions'])
            ->name('api.wiki.feed.latest');
    });
});
