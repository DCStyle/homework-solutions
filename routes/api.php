<?php

use App\Http\Controllers\Api\AttachmentController;
use App\Http\Controllers\Api\PostController;
use Illuminate\Http\Request;
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
