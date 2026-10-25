<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RdbPublishedTypeAuthor;
use App\Models\RdbPublishedWork;
use Illuminate\Http\Request;

class RdbPublishedTypeAuthorController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', RdbPublishedTypeAuthor::class);
        $query = RdbPublishedTypeAuthor::query();

        $search = $request->input('search', $request->input('q'));
        if ($search !== null && $search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('pubta_nameTH', 'like', "%{$search}%")
                  ->orWhere('pubta_nameEN', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('pubta_id', 'asc')->paginate(15)->withQueryString();

        return view('backend.rdbpublishedtypeauthor.index', compact('items'));
    }

    public function create()
    {
        $this->authorize('create', RdbPublishedTypeAuthor::class);
        $item = new RdbPublishedTypeAuthor();
        return view('backend.rdbpublishedtypeauthor.create', compact('item'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', RdbPublishedTypeAuthor::class);
        $validated = $request->validate([
            'pubta_nameTH' => 'required|string|max:255',
            'pubta_nameEN' => 'nullable|string|max:255',
            'pubta_score' => 'nullable|numeric|min:0',
        ]);

        $item = new RdbPublishedTypeAuthor();
        $item->fill($validated);
        $item->created_at = now();
        $item->save();

        return redirect()->route('backend.rdbpublishedtypeauthor.index')->with('success', 'บันทึกข้อมูลประเภทผู้แต่งบทความเรียบร้อยแล้ว');
    }

    public function show($id)
    {
        $item = RdbPublishedTypeAuthor::findOrFail($id);
        $this->authorize('view', $item);
        return view('backend.rdbpublishedtypeauthor.show', compact('item'));
    }

    public function edit($id)
    {
        $item = RdbPublishedTypeAuthor::findOrFail($id);
        $this->authorize('update', $item);
        return view('backend.rdbpublishedtypeauthor.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbPublishedTypeAuthor::findOrFail($id);
        $this->authorize('update', $item);

        $validated = $request->validate([
            'pubta_nameTH' => 'required|string|max:255',
            'pubta_nameEN' => 'nullable|string|max:255',
            'pubta_score' => 'nullable|numeric|min:0',
        ]);

        $item->fill($validated);
        $item->updated_at = now();
        $item->save();

        return redirect()->route('backend.rdbpublishedtypeauthor.index')->with('success', 'อัปเดตข้อมูลประเภทผู้แต่งบทความเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $item = RdbPublishedTypeAuthor::findOrFail($id);
        $this->authorize('delete', $item);

        if (RdbPublishedWork::where('pubta_id', $item->pubta_id)->exists()) {
            return redirect()->back()->with('error', 'ไม่สามารถลบได้เนื่องจากประเภทผู้แต่งนี้ถูกใช้งานในผลงานตีพิมพ์แล้ว');
        }

        $item->delete();

        return redirect()->route('backend.rdbpublishedtypeauthor.index')->with('success', 'ลบข้อมูลประเภทผู้แต่งบทความเรียบร้อยแล้ว');
    }
}
