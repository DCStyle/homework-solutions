<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WikiComment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class WikiCommentsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Store a newly created comment in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'question_id' => 'required|exists:wiki_questions,id',
            'content' => 'required|string',
            'parent_id' => 'nullable|exists:wiki_comments,id',
        ]);

        $comment = new WikiComment();
        $comment->fill($validated);
        $comment->user_id = Auth::id();
        $comment->save();

        return response()->json([
            'message' => 'Comment created successfully',
            'data' => $comment->load('user'),
        ], 201);
    }

    /**
     * Update the specified comment in storage.
     */
    public function update(Request $request, WikiComment $comment): JsonResponse
    {
        $this->authorize('update', $comment);

        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $comment->fill($validated);
        $comment->save();

        return response()->json([
            'message' => 'Comment updated successfully',
            'data' => $comment->load('user'),
        ]);
    }

    /**
     * Remove the specified comment from storage.
     */
    public function destroy(WikiComment $comment): JsonResponse
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return response()->json([
            'message' => 'Comment deleted successfully',
        ]);
    }
} 