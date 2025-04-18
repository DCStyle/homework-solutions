<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\Image;
use App\Models\Post;
use App\Models\PostAttachment;
use App\Services\SitemapService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
            'uploaded_attachment_ids' => 'nullable|json',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500'
        ]);

        $post->update([
            'title' => $validated['title'],
            'content' => $validated['message'],
            'slug' => $validated['slug'],
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null
        ]);

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

        // Update attachment associations
        if ($request->has('uploaded_attachment_ids')) {
            $attachmentIds = json_decode($request->uploaded_attachment_ids, true);
            if (is_array($attachmentIds)) {
                PostAttachment::whereIn('id', $attachmentIds)
                    ->whereNull('post_id')
                    ->update(['post_id' => $post->id]);
            }

            // Delete removed attachments
            $post->attachments()
                ->whereNotIn('id', $attachmentIds)
                ->get()
                ->each(function($attachment) {
                    $attachment->delete();
                });
        }

        // Update sitemap entry
        SitemapService::updateEntry('post', $post);

        // Clear sitemap cache
        $this->clearSitemapCache();

        return redirect()->route('admin.bookChapters.posts', $post->chapter->id)->with('success', 'Cập nhật bài viết thành công.');
    }

    // Handle post destroy
    public function destroy($id)
    {
        $post = Post::whereId($id)->firstOrFail();

        $chapter = $post->chapter;

        // Remove from sitemap before deleting
        SitemapService::removeEntry('post', $post->id);

        // Clear sitemap cache
        $this->clearSitemapCache();

        $post->delete();

        return redirect()->route('admin.bookChapters.posts', $chapter->id)->with('success', 'Xóa bài viết thành công.');
    }

    /**
     * Clear sitemap cache
     */
    private function clearSitemapCache()
    {
        Cache::forget('sitemap.index.data');
        Cache::forget('sitemap.post.page.1.data');
    }
}
