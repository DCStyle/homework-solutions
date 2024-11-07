<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookChapter;
use App\Models\BookGroup;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function testApi()
    {
        // Retrieve some basic data to confirm API is working

        // Fetch a few categories
        $categories = Category::select('id', 'name', 'slug')->take(5)->get();

        // Fetch a few book groups
        $bookGroups = BookGroup::select('id', 'name', 'slug', 'category_id')->take(5)->get();

        // Fetch a few books
        $books = Book::select('id', 'title', 'slug', 'book_group_id')->take(5)->get();

        // Fetch a few book chapters
        $bookChapters = BookChapter::select('id', 'title', 'slug', 'book_id')->take(5)->get();

        // Fetch a few posts
        $posts = Post::select('id', 'title', 'slug', 'book_chapter_id')->take(5)->get();

        // Return data as JSON response
        return response()->json([
            'categories' => $categories,
            'book_groups' => $bookGroups,
            'books' => $books,
            'book_chapters' => $bookChapters,
            'posts' => $posts,
        ]);
    }

    public function importPostFromJSON(Request $request)
    {
        // Validate the incoming JSON structure for a single post object
        $validatedData = $request->validate([
            'category' => 'required|string',
            'book_group' => 'required|string',
            'book_title' => 'required|string',
            'chapter_title' => 'required|string',
            'post_title' => 'required|string',
            'content' => 'required|string',
        ]);

        // Map JSON fields to variables
        $categoryName = $validatedData['category'];
        $bookGroupName = $validatedData['book_group'];
        $bookTitle = $validatedData['book_title'];
        $chapterTitle = $validatedData['chapter_title'];
        $postTitle = $validatedData['post_title'];
        $content = $validatedData['content'];

        // Process Category
        $category = Category::firstOrCreate(['name' => $categoryName]);

        // Process Book Group
        $bookGroup = BookGroup::firstOrCreate(['name' => $bookGroupName, 'category_id' => $category->id],);

        // Process Book
        $book = Book::firstOrCreate(['name' => $bookTitle, 'book_group_id' => $bookGroup->id]);

        // Process Book Chapter
        $bookChapter = BookChapter::firstOrCreate(['name' => $chapterTitle, 'book_id' => $book->id]);

        // Check if Post exists
        $existingPost = Post::where('title', $postTitle)->where('book_chapter_id', $bookChapter->id)->first();
        if ($existingPost) {
            return response()->json(['message' => 'Post already exists'], 409); // Conflict response if post exists
        }

        // Create new Post
        $post = Post::create([
            'title' => $postTitle,
            'content' => $content,
            'user_id' => 1, // Default user ID or adjust as needed
            'book_chapter_id' => $bookChapter->id,
        ]);

        return response()->json(['success' => 'Post created successfully']);
    }
}
