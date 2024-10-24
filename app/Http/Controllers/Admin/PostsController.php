<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostsController extends Controller
{
    public function edit($slug)
    {
        $post = Post::where('slug', $slug)->firstOrFail();

        $chapter = $post->chapter;

        return view('admin.posts.edit', compact('post', 'chapter'));
    }

    public function update(Request $request, $slug)
    {
        $post = Post::where('slug', $slug)->firstOrFail();

        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'slug' => 'required|string',
        ]);

        $post->update([
            'title' => $request->title,
            'message' => $request->message,
            'slug' => $request->slug,
        ]);

        return redirect()->route('admin.bookChapters.posts', $post->chapter->slug)->with('success', 'Post updated successfully.');
    }

    // Display post delete view
    public function delete($slug)
    {
        $post = Post::where('slug', $slug)->firstOrFail();

        return view('admin_layouts.delete', [
            'confirmLink' => route('admin.posts.destroy', $post->slug),
            'name' => $post->title,
            'backLink' => route('admin.bookChapters.posts', $post->chapter->slug),
        ]);
    }

    // Handle post destroy
    public function destroy($slug)
    {
        $post = Post::where('slug', $slug)->firstOrFail();

        $chapter = $post->chapter;

        $post->delete();

        return redirect()->route('admin.bookChapters.posts', $chapter->slug)->with('success', 'Post deleted successfully.');
    }
}
