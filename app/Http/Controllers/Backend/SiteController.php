<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\RdbProject;
use App\Models\RdbResearcher;
use App\Models\User;

class SiteController extends Controller
{
    public function index()
    {
        $totalProjects = RdbProject::count();
        $totalResearchers = RdbResearcher::count();
        
        // Projects by Status
        $projectsByStatus = RdbProject::select('ps_id', DB::raw('count(*) as total'))
            ->groupBy('ps_id')
            ->with('status')
            ->get();
            
        // Projects by Year
        $projectsByYear = RdbProject::select('year_id', DB::raw('count(*) as total'))
            ->groupBy('year_id')
            ->with('year')
            ->orderBy('year_id', 'desc')
            ->limit(5)
            ->get();

        return view('backend.site.index', compact('totalProjects', 'totalResearchers', 'projectsByStatus', 'projectsByYear'));
    }
}
