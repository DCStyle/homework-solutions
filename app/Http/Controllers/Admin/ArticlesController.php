<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SearchController;
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
        $articles = Article::with('category')->paginate(10);
        return view('admin.articles.index', compact('articles'));
    }

    public function create()
    {
        $categories = ArticleCategory::all();
        $tags = ArticleTag::all();
        return view('admin.articles.form', compact('categories', 'tags'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'article_category_id' => 'required|exists:article_categories,id',
        ]);

        $article = Article::create($validated);

        // Handle tags
        if ($request->has('tags')) {
            $tags = collect($request->tags)->map(function($tag) {
                // If the tag is numeric, it already exists
                if (is_numeric($tag)) {
                    return $tag;
                }
                // If not, create a new tag
                return ArticleTag::create([
                    'name' => $tag,
                    'slug' => Str::slug($tag)
                ])->id;
            });

            $article->tags()->sync($tags);
        }

        // Attach uploaded images
        if ($request->has('uploaded_image_ids')) {
            $imageIds = json_decode($request->uploaded_image_ids, true);
            if (is_array($imageIds)) {
                Image::whereIn('id', $imageIds)
                    ->update([
                        'imageable_id' => $article->id,
                        'imageable_type' => Article::class
                    ]);
            }
        }

        return redirect()->route('admin.articles.index')->with('success', 'Tạo bài viết thành công.');
    }

    public function edit($id)
    {
        $article = Article::findOrFail($id);
        $tags = ArticleTag::all();
        $categories = ArticleCategory::all();
        return view('admin.articles.form', compact('article', 'tags', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'article_category_id' => 'required|exists:article_categories,id',
        ]);

        $article = Article::findOrFail($id);
        $article->update($validated);

        // Handle tags
        if ($request->has('tags')) {
            $tags = collect($request->tags)->map(function($tag) {
                if (is_numeric($tag)) {
                    return $tag;
                }
                return ArticleTag::create([
                    'name' => $tag,
                    'slug' => Str::slug($tag)
                ])->id;
            });

            $article->tags()->sync($tags);
        } else {
            $article->tags()->detach();
        }

        // Update image associations
        if ($request->has('uploaded_image_ids')) {
            $imageIds = json_decode($request->uploaded_image_ids, true);
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

        return redirect()->route('admin.articles.index')->with('success', 'Cập nhật bài viết thành công.');
    }

    public function destroy($id)
    {
        $article = Article::findOrFail($id);
        $article->delete();

        return redirect()->route('admin.articles.index')->with('success', 'Xóa bài viết thành công.');
    }
}
