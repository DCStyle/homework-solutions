<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $userCount = \App\Models\User::count();
        $categoryCount = \App\Models\Category::count();
        $groupCount = \App\Models\BookGroup::count();
        $bookCount = \App\Models\Book::count();
        $chapterCount = \App\Models\BookChapter::count();
        $postCount = \App\Models\Post::count();

        return view('admin.dashboard', compact(
            'categoryCount',
            'groupCount',
            'chapterCount',
            'postCount',
            'userCount',
            'bookCount'
        ));
    }
}
