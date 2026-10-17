<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbNacc;
use App\Models\RdbProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\FileUploadService;
use App\Http\Requests\Frontend\{StoreRdbNaccRequest, UpdateRdbNaccRequest};

class RdbnaccController extends Controller
{
    private FileUploadService $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', RdbNacc::class);
        $query = RdbNacc::with(['project', 'creator', 'updater']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('nacc_note', 'like', "%{$search}%");
        }

        $items = $query->orderBy('nacc_id', 'desc')->paginate(10);
        
        return view('frontend.rdbnacc.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbNacc::with(['project', 'creator', 'updater'])->findOrFail($id);
        $this->authorize('view', $item);
        return view('frontend.rdbnacc.show', compact('item'));
    }


}