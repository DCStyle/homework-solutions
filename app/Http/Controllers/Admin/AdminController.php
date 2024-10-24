<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $categoryCount = \App\Models\Category::count();
        $postCount = \App\Models\Post::count();
        $userCount = \App\Models\User::count();
        $bookCount = \App\Models\Book::count();

        return view('admin.dashboard', compact('categoryCount', 'postCount', 'userCount', 'bookCount'));
    }
}
