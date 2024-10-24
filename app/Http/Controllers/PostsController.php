<?php

namespace App\Http\Controllers;

use App\Models\Post;

class PostsController extends Controller
{
    public function show($post_slug)
    {
        $post = Post::where('slug', $post_slug)->firstOrFail();

        return view('posts.show', compact('post'));
    }
}
