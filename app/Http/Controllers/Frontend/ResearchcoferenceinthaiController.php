<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ResearchConferenceInThai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ResearchcoferenceinthaiController extends Controller
{
    public function index(Request $request)
    {
        $query = ResearchConferenceInThai::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('con_name', 'like', "%{$search}%")
                  ->orWhere('con_venue', 'like', "%{$search}%")
                  ->orWhere('con_detail', 'like', "%{$search}%");
            });
        }

        // Date range validation
        $hasDateStart = $request->filled('date_start');
        $hasDateEnd = $request->filled('date_end');
        
        if ($hasDateStart || $hasDateEnd) {
            if (!$hasDateStart || !$hasDateEnd) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'กรุณาระบุทั้งวันที่เริ่มและวันที่สิ้นสุด');
            }
            
            if ($request->date_end < $request->date_start) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'วันที่สิ้นสุดต้องมากกว่าหรือเท่ากับวันที่เริ่ม');
            }
            
            $query->whereDate('con_even_date', '>=', $request->date_start)
                  ->whereDate('con_even_date', '<=', $request->date_end);
        }

        $items = $query->orderBy('con_even_date', 'desc')->orderBy('id', 'desc')->paginate(10);
        
        return view('frontend.researchcoferenceinthai.index', compact('items'));
    }

    public function show($id)
    {
        $item = ResearchConferenceInThai::findOrFail($id);
        $item->increment('con_count');
        $recentConferences = ResearchConferenceInThai::where('id', '!=', $item->id)
            ->orderBy('con_even_date', 'desc')
            ->orderBy('id', 'desc')
            ->take(4)
            ->get();

        return view('frontend.researchcoferenceinthai.show', compact('item', 'recentConferences'));
    }
}
