<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbPublished;
use App\Models\RdbYear;
use App\Models\RdbResearcher;
use App\Models\RdbDepartment;
use App\Models\RdbPublishedType;
use App\Models\RdbProject;
use App\Models\RdbPublishedTypeAuthor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class RdbpublishedController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbPublished::with(['year', 'researcher', 'researcher.prefix', 'department', 'pubtype', 'project', 'authors', 'authors.prefix']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('pub_name', 'like', "%{$search}%")
                  ->orWhere('pub_name_journal', 'like', "%{$search}%")
                  ->orWhere('pub_keyword', 'like', "%{$search}%")
                  ->orWhereHas('researcher', function($query) use ($search) {
                      $query->where('researcher_fname', 'like', "%{$search}%")
                            ->orWhere('researcher_lname', 'like', "%{$search}%")
                            ->orWhereRaw("CONCAT(researcher_fname, ' ', researcher_lname) LIKE ?", ["%{$search}%"]);
                  })
                  ->orWhereHas('project', function($query) use ($search) {
                      $query->where('pro_nameTH', 'like', "%{$search}%");
                  });
            });
        }

        // Date range validation
        $hasDateStart = $request->filled('date_start');
        $hasDateEnd = $request->filled('date_end');
        
        if ($hasDateStart || $hasDateEnd) {
            // Both dates must be provided
            if (!$hasDateStart || !$hasDateEnd) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'กรุณาระบุทั้งวันที่เริ่มและวันที่สิ้นสุด');
            }
            
            // End date must be >= start date
            if ($request->date_end < $request->date_start) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'วันที่สิ้นสุดต้องมากกว่าหรือเท่ากับวันที่เริ่ม');
            }
            
            $query->whereDate('pub_date', '>=', $request->date_start)
                  ->whereDate('pub_date', '<=', $request->date_end);
        }

        $project = null;
        if ($request->filled('pro_id')) {
            $query->where('pro_id', $request->pro_id);
            $project = RdbProject::find($request->pro_id);
        }

        if ($request->filled('year_id')) {
            $query->where('year_id', $request->year_id);
        }
        
        // Sort by date DESC, then name ASC
        $query->orderBy('pub_date', 'desc')
              ->orderBy('pub_name', 'asc');
        
        $items = $query->paginate(10);
        
        $years = RdbYear::orderBy('year_name', 'desc')->get();

        return view('frontend.rdbpublished.index', compact('items', 'years', 'project'));
    }

    public function show($id)
    {
        $item = RdbPublished::with([
            'year', 
            'researcher', 
            'researcher.prefix',
            'department', 
            'pubtype', 
            'branch',
            'project',
            'authors',
            'authors.prefix',
            'authors.department'
        ])->findOrFail($id);
        
        // Load author types manually
        $authorTypeIds = $item->authors->pluck('pivot.pubta_id')->filter()->unique();
        if ($authorTypeIds->isNotEmpty()) {
            $authorTypes = RdbPublishedTypeAuthor::whereIn('pubta_id', $authorTypeIds)->get()->keyBy('pubta_id');
            foreach ($item->authors as $author) {
                if ($author->pivot && $author->pivot->pubta_id && isset($authorTypes[$author->pivot->pubta_id])) {
                    $author->authorType = $authorTypes[$author->pivot->pubta_id];
                }
            }
        }
        
        return view('frontend.rdbpublished.show', compact('item'));
    }

    public function create()
    {
        \Illuminate\Support\Facades\Gate::authorize('RdbPublished.Create');
        $data = $this->getDropdownData();
        return view('frontend.rdbpublished.create', $data);
    }

    public function store(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize('RdbPublished.Create');
        $validated = $this->validateRequest($request);

        DB::beginTransaction();
        try {
            if ($request->hasFile('pub_file')) {
                $path = $request->file('pub_file')->store('published_files', 'public');
                $validated['pub_file'] = $path;
            }

            $validated['created_at'] = now();
            $published = RdbPublished::create($validated);

            // Sync Authors
            $this->syncAuthors($published, $request);

            DB::commit();
            return redirect()->route('frontend.rdbpublished.index')->with('success', 'Published work created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error creating published work: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $item = RdbPublished::with('authors')->findOrFail($id);
        $data = $this->getDropdownData();
        $data['item'] = $item;
        return view('frontend.rdbpublished.edit', $data);
    }

    public function update(Request $request, $id)
    {
        $item = RdbPublished::findOrFail($id);
        $validated = $this->validateRequest($request, $id);

        DB::beginTransaction();
        try {
            if ($request->hasFile('pub_file')) {
                if ($item->pub_file) {
                    Storage::disk('public')->delete($item->pub_file);
                }
                $path = $request->file('pub_file')->store('published_files', 'public');
                $validated['pub_file'] = $path;
            }

            $item->update($validated);

            // Sync Authors
            $this->syncAuthors($item, $request);

            DB::commit();
            return redirect()->route('frontend.rdbpublished.index')->with('success', 'Published work updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error updating published work: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $item = RdbPublished::findOrFail($id);
        if ($item->pub_file) {
            Storage::disk('public')->delete($item->pub_file);
        }
        
        $item->authors()->detach();
        $item->delete();

        return redirect()->route('frontend.rdbpublished.index')->with('success', 'Published work deleted successfully.');
    }

    public function export(Request $request)
    {
        $query = RdbPublished::with(['year', 'researcher', 'department', 'pubtype']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('pub_name', 'like', "%{$search}%")
                  ->orWhere('pub_name_journal', 'like', "%{$search}%");
        }
        if ($request->filled('year_id')) {
            $query->where('year_id', $request->year_id);
        }

        $items = $query->orderBy('id', 'desc')->get();

        $filename = "published_works_" . date('Y-m-d_H-i-s') . ".csv";
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($items) {
            $file = fopen('php://output', 'w');
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
            
            fputcsv($file, ['ID', 'ชื่อผลงาน', 'วารสาร', 'ปีงบประมาณ', 'ประเภท', 'นักวิจัย', 'หน่วยงาน', 'งบประมาณ']);

            foreach ($items as $item) {
                fputcsv($file, [
                    $item->id,
                    $item->pub_name,
                    $item->pub_name_journal,
                    $item->year->year_name ?? '-',
                    $item->pubtype->pubtype_name ?? '-',
                    ($item->researcher->researcher_nameTH ?? '') . ' ' . ($item->researcher->researcher_surnameTH ?? ''),
                    $item->department->department_nameTH ?? '-',
                    $item->pub_budget
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function getDropdownData()
    {
        return [
            'years' => RdbYear::orderBy('year_name', 'desc')->get(),
            'researchers' => RdbResearcher::orderBy('researcher_nameTH')->get(),
            'departments' => RdbDepartment::orderBy('department_nameTH')->get(),
            'pubtypes' => RdbPublishedType::all(),
            'projects' => RdbProject::orderBy('pro_nameTH')->get(),
            'authorTypes' => RdbPublishedTypeAuthor::all(),
        ];
    }

    private function validateRequest(Request $request, $id = null)
    {
        return $request->validate([
            'pub_name' => 'required|string|max:255',
            'pub_name_journal' => 'nullable|string|max:255',
            'year_id' => 'required|exists:rdb_year,year_id',
            'pubtype_id' => 'nullable|exists:rdb_published_type,pubtype_id',
            'researcher_id' => 'required|exists:rdb_researcher,researcher_id', // Main owner
            'department_id' => 'nullable|exists:rdb_department,department_id',
            'pro_id' => 'nullable|exists:rdb_project,pro_id',
            'pub_date' => 'nullable|date',
            'pub_abstract' => 'nullable|string',
            'pub_keyword' => 'nullable|string',
            'pub_budget' => 'nullable|numeric',
            'pub_file' => 'nullable|file|max:10240',
            'data_show' => 'nullable|integer',
            // Authors
            'author_id' => 'nullable|array',
            'author_id.*' => 'exists:rdb_researcher,researcher_id',
            'pubta_id' => 'nullable|array',
            'pubta_id.*' => 'exists:rdb_published_type_author,pubta_id',
        ]);
    }

    private function syncAuthors(RdbPublished $published, Request $request)
    {
        if ($request->has('author_id')) {
            $syncData = [];
            foreach ($request->author_id as $key => $researcherId) {
                if ($researcherId) {
                    $syncData[$researcherId] = [
                        'pubta_id' => $request->pubta_id[$key] ?? null,
                    ];
                }
            }
            $published->authors()->sync($syncData);
        } else {
            $published->authors()->detach();
        }
    }
}
