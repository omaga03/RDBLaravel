<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\JournalStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
use App\Services\FileUploadService;
use App\Http\Requests\Frontend\{StoreJournalStatusRequest, UpdateJournalStatusRequest};

class JournalstatusController extends Controller
{
    private FileUploadService $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }
    public function index(Request $request)
    {
        Gate::authorize('Admin');
        $query = JournalStatus::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('jou_name', 'like', "%{$search}%")
                  ->orWhere('jou_respon', 'like', "%{$search}%");
        }

        $items = $query->orderBy('id', 'desc')->paginate(10);
        
        return view('frontend.journalstatus.index', compact('items'));
    }

    public function show($id)
    {
        Gate::authorize('Admin');
        $item = JournalStatus::findOrFail($id);
        return view('frontend.journalstatus.show', compact('item'));
    }


}
