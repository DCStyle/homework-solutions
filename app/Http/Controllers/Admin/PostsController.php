<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\Image;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PostsController extends Controller
{
    public function edit($id)
    {
        $post = Post::whereId($id)->firstOrFail();

        $chapter = $post->chapter;

        return view('admin.posts.form', compact('post', 'chapter'));
    }

    public function update(Request $request, $id)
    {
        $post = Post::whereId($id)->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'slug' => 'required|string|max:255|unique:posts,slug,' . $post->id,
        ]);

        $post->update($validated);

        // Update image associations
        if ($request->has('uploaded_image_ids')) {
            $imageIds = json_decode($request->uploaded_image_ids, true);
            if (is_array($imageIds)) {
                // Attach new images
                Image::whereIn('id', $imageIds)
                    ->update([
                        'imageable_id' => $post->id,
                        'imageable_type' => Post::class
                    ]);

                // Delete removed images
                $post->images()
                    ->whereNotIn('id', $imageIds)
                    ->get()
                    ->each(function($image) {
                        Storage::disk('public')->delete($image->path);
                        $image->delete();
                    });
            }
        }

        return redirect()->route('admin.bookChapters.posts', $post->chapter->id)->with('success', 'Cập nhật bài viết thành công.');
    }

    // Handle post destroy
    public function destroy($id)
    {
        $post = Post::whereId($id)->firstOrFail();

        $chapter = $post->chapter;

        $post->delete();

        return redirect()->route('admin.bookChapters.posts', $chapter->$id)->with('success', 'Xóa bài viết thành công.');
    }
}
