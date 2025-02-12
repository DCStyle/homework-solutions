<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\ArticleTag;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArticlesController extends Controller
{
    public function index()
    {
        $articles = Article::with(['category', 'tags'])->paginate(10);
        return view('admin.articles.index', compact('articles'));
    }

    public function create()
    {
        $categories = ArticleCategory::all();
        return view('admin.articles.form', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'article_category_id' => 'required|exists:article_categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'required|string', // Allow both IDs and new tag names
        ]);

        $article = Article::create($validated);

        // Handle tags
        if ($request->has('tags')) {
            $tagIds = $this->processTagInput($request->tags);
            $article->tags()->sync($tagIds);
        }

        // Handle images
        if ($request->has('uploaded_image_ids')) {
            $this->handleImages($article, $request->uploaded_image_ids);
        }

        return redirect()->route('admin.articles.index')
            ->with('success', 'Tạo bài viết thành công.');
    }

    public function edit($id)
    {
        $article = Article::with('tags')->findOrFail($id);
        $categories = ArticleCategory::all();
        return view('admin.articles.form', compact('article', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'article_category_id' => 'required|exists:article_categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'required|string', // Allow both IDs and new tag names
        ]);

        $article = Article::findOrFail($id);
        $article->update($validated);

        // Handle tags
        if ($request->has('tags')) {
            $tagIds = $this->processTagInput($request->tags);
            $article->tags()->sync($tagIds);
        } else {
            $article->tags()->detach();
        }

        // Handle images
        if ($request->has('uploaded_image_ids')) {
            $this->handleImages($article, $request->uploaded_image_ids);
        }

        return redirect()->route('admin.articles.index')
            ->with('success', 'Cập nhật bài viết thành công.');
    }

    public function destroy($id)
    {
        $article = Article::findOrFail($id);

        // Delete associated images from storage
        foreach ($article->images as $image) {
            Storage::disk('public')->delete($image->path);
        }

        $article->delete();

        return redirect()->route('admin.articles.index')
            ->with('success', 'Xóa bài viết thành công.');
    }

    /**
     * Process tag input and return array of tag IDs
     *
     * @param array $tags
     * @return array
     */
    protected function processTagInput($tags)
    {
        return collect($tags)->map(function($tag) {
            // If the tag is numeric, it's an existing tag ID
            if (is_numeric($tag)) {
                return (int) $tag;
            }

            // If the tag is a string, it's a new tag name
            // First try to find an existing tag with the same name
            $existingTag = ArticleTag::where('name', $tag)->first();
            if ($existingTag) {
                return $existingTag->id;
            }

            // If no existing tag found, create a new one
            $newTag = ArticleTag::create([
                'name' => $tag,
                'slug' => Str::slug($tag)
            ]);

            return $newTag->id;
        })->toArray();
    }

    /**
     * Handle image attachments for articles
     *
     * @param Article $article
     * @param string $imageIds JSON string of image IDs
     * @return void
     */
    protected function handleImages(Article $article, $imageIds)
    {
        $imageIds = json_decode($imageIds, true);

        if (is_array($imageIds)) {
            // Attach new images
            Image::whereIn('id', $imageIds)
                ->update([
                    'imageable_id' => $article->id,
                    'imageable_type' => Article::class
                ]);

            // Delete removed images
            $article->images()
                ->whereNotIn('id', $imageIds)
                ->get()
                ->each(function($image) {
                    Storage::disk('public')->delete($image->path);
                    $image->delete();
                });
        }
    }
}
