<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BookGroup;

class BookGroupsController extends Controller
{
    public function show()
    {
        $group = BookGroup::where('slug', request()->group_slug)->firstOrFail();
        return view('book-groups.show', compact('group'));
    }
}
