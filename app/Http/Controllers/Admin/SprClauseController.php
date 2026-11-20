<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HousingProject;
use App\Models\SprClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SprClauseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $projects = HousingProject::with(['sprClauses' => function ($query) {
            $query->orderBy('order', 'asc');
        }])->get();

        return view('admin.spr_clauses.index', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $projectId = $request->project_id;
        $projects = HousingProject::all();

        return view('admin.spr_clauses.create', compact('projects', 'projectId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'housing_project_id' => 'required|exists:housing_projects,id',
            'clause_number' => 'required|string|max:50',
            'clause_title' => 'required|string|max:255',
            'clause_content' => 'required|string',
            'order' => 'required|integer|min:0',
        ]);

        SprClause::create([
            'housing_project_id' => $request->housing_project_id,
            'clause_number' => $request->clause_number,
            'clause_title' => $request->clause_title,
            'clause_content' => $request->clause_content,
            'order' => $request->order,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.spr-clauses.index')
            ->with('success', 'Klausul SPR berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $clause = SprClause::findOrFail($id);
        $projects = HousingProject::all();

        return view('admin.spr_clauses.edit', compact('clause', 'projects'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $clause = SprClause::findOrFail($id);

        $request->validate([
            'housing_project_id' => 'required|exists:housing_projects,id',
            'clause_number' => 'required|string|max:50',
            'clause_title' => 'required|string|max:255',
            'clause_content' => 'required|string',
            'order' => 'required|integer|min:0',
        ]);

        $clause->update([
            'housing_project_id' => $request->housing_project_id,
            'clause_number' => $request->clause_number,
            'clause_title' => $request->clause_title,
            'clause_content' => $request->clause_content,
            'order' => $request->order,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.spr-clauses.index')
            ->with('success', 'Klausul SPR berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $clause = SprClause::findOrFail($id);
        $clause->delete();

        return redirect()->route('admin.spr-clauses.index')
            ->with('success', 'Klausul SPR berhasil dihapus.');
    }
}
