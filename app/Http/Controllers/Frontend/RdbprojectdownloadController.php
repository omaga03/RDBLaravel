<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbProjectDownload; // Corrected model name
use Illuminate\Http\Request;

class RdbprojectdownloadController extends Controller
{
    public function index()
    {
        $items = RdbProjectDownload::with(['project', 'researcher', 'utilize'])->paginate(10);
        return view('frontend.rdbprojectdownload.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbProjectDownload::with(['project', 'researcher', 'utilize'])->findOrFail($id);
        return view('frontend.rdbprojectdownload.show', compact('item'));
    }

}
