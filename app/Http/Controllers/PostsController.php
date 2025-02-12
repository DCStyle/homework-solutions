<?php

namespace App\Http\Controllers;

use App\Models\Post;

class PostsController extends Controller
{
    public function show($post_slug)
    {
        $post = Post::with(['chapter.book.group.category'])
            ->where('slug', $post_slug)
            ->firstOrFail();

        $footerLatestPosts = Post::select('posts.*')
            ->join('book_chapters', 'posts.book_chapter_id', '=', 'book_chapters.id')
            ->join('books', 'book_chapters.book_id', '=', 'books.id')
            ->where('books.book_group_id', $post->chapter->book->group->id)
            ->where('posts.id', '!=', $post->id)  // Exclude current post
            ->latest()
            ->limit(10)
            ->get();

        return view('posts.show', [
            'post' => $post,
            'category' => $post->chapter->book->group->category,
            'footerLatestPosts' => $footerLatestPosts
        ]);
    }
}
