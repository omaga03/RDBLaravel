<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectdownload;
use Illuminate\Http\Request;

class RdbprojectdownloadController extends Controller
{
    public function index()
    {
        $items = RdbProjectdownload::paginate(10);
        return view('frontend.rdbprojectdownload.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbProjectdownload::findOrFail($id);
        return view('frontend.rdbprojectdownload.show', compact('item'));
    }

}
