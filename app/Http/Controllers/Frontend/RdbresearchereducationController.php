<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbResearcherEducation;
use App\Models\RdbResearcher;
use Illuminate\Http\Request;
use App\Http\Requests\Frontend\{StoreRdbResearcherEducationRequest, UpdateRdbResearcherEducationRequest};

class RdbresearchereducationController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', RdbResearcherEducation::class);
        $query = RdbResearcherEducation::with(['researcher', 'creator', 'updater']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('reedu_name', 'like', "%{$search}%");
        }

        $items = $query->orderBy('reedu_id', 'desc')->paginate(10);
        
        return view('frontend.rdbresearchereducation.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbResearcherEducation::with(['researcher', 'creator', 'updater'])->findOrFail($id);
        $this->authorize('view', $item);
        return view('frontend.rdbresearchereducation.show', compact('item'));
    }


}