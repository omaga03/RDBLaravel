<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\RdbTrainingRegister;
use App\Models\RdbTraining;
use Illuminate\Http\Request;

class RdbtrainingregisterController extends Controller
{
    public function index(Request $request)
    {
        $query = RdbTrainingRegister::with('training');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('treg_name', 'like', "%{$search}%");
        }

        $items = $query->orderBy('treg_id', 'desc')->paginate(10);
        
        return view('frontend.rdbtrainingregister.index', compact('items'));
    }

    public function show($id)
    {
        $item = RdbTrainingRegister::with('training')->findOrFail($id);
        return view('frontend.rdbtrainingregister.show', compact('item'));
    }

    public function create()
    {
        $trainings = RdbTraining::all();
        return view('frontend.rdbtrainingregister.create', compact('trainings'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tra_id' => 'nullable|exists:rdb_training,tra_id',
            'treg_perfix' => 'nullable|string|max:50',
            'treg_name' => 'required|string|max:255',
            'treg_department' => 'nullable|string|max:255',
            'treg_position' => 'nullable|string|max:255',
            'treg_address' => 'nullable|string',
            'treg_tel' => 'nullable|string|max:50',
            'treg_email' => 'nullable|email|max:255',
            'treg_session' => 'nullable|integer',
        ]);

        $validated['created_at'] = now();

        RdbTrainingRegister::create($validated);

        return redirect()->route('frontend.rdbtrainingregister.index')->with('success', 'Registration created successfully.');
    }

    public function edit($id)
    {
        $item = RdbTrainingRegister::findOrFail($id);
        $trainings = RdbTraining::all();
        return view('frontend.rdbtrainingregister.edit', compact('item', 'trainings'));
    }

    public function update(Request $request, $id)
    {
        $item = RdbTrainingRegister::findOrFail($id);
        
        $validated = $request->validate([
            'tra_id' => 'nullable|exists:rdb_training,tra_id',
            'treg_perfix' => 'nullable|string|max:50',
            'treg_name' => 'required|string|max:255',
            'treg_department' => 'nullable|string|max:255',
            'treg_position' => 'nullable|string|max:255',
            'treg_address' => 'nullable|string',
            'treg_tel' => 'nullable|string|max:50',
            'treg_email' => 'nullable|email|max:255',
            'treg_session' => 'nullable|integer',
        ]);

        $item->update($validated);

        return redirect()->route('frontend.rdbtrainingregister.index')->with('success', 'Registration updated successfully.');
    }

    public function destroy($id)
    {
        $item = RdbTrainingRegister::findOrFail($id);
        $item->delete();

        return redirect()->route('frontend.rdbtrainingregister.index')->with('success', 'Registration deleted successfully.');
    }
}
