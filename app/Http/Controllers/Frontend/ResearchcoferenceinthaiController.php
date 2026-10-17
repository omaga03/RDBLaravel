<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ResearchConferenceInThai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
use App\Services\FileUploadService;
use App\Http\Requests\Frontend\{StoreResearchCoferenceinthaiRequest, UpdateResearchCoferenceinthaiRequest};

class ResearchcoferenceinthaiController extends Controller
{
    public function index(Request $request)
    {
        $query = ResearchConferenceInThai::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('con_name', 'like', "%{$search}%")
                  ->orWhere('con_venue', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_start')) {
            $query->whereDate('con_even_date', '>=', $request->date_start);
        }

        if ($request->filled('date_end')) {
            $query->whereDate('con_even_date', '<=', $request->date_end);
        }

        $items = $query->orderBy('id', 'desc')->paginate(10);
        
        return view('frontend.researchcoferenceinthai.index', compact('items'));
    }

    public function show($id)
    {
        $item = ResearchConferenceInThai::findOrFail($id);
        return view('frontend.researchcoferenceinthai.show', compact('item'));
    }


}
