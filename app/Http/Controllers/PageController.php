<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function __invoke(Request $request, Page $page) {
        return view('page.show', compact('page'));
    }
}
