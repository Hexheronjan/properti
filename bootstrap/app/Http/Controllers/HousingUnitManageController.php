<?php

namespace App\Http\Controllers;

use App\Models\HousingUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class HousingUnitManageController extends Controller
{
    /**
     * Helper to verify if user can manage units
     */
    protected function checkAuthorization()
    {
        if (!Auth::user()->canPostUnit()) {
            abort(403, 'Anda tidak memiliki hak untuk mengelola unit perumahan.');
        }
    }

    /**
     * Display a listing of housing units
     */
    public function index()
    {
        $this->checkAuthorization();

        $units = HousingUnit::orderBy('block')->orderBy('number')->paginate(20);
        return view('admin.housing_units.index', compact('units'));
    }

    /**
     * Show the form for creating a new housing unit
     */
    public function create()
    {
        $this->checkAuthorization();

        $unitTypes = config('nup.unit_types');
        return view('admin.housing_units.create', compact('unitTypes'));
    }

    /**
     * Store a newly created housing unit in storage
     */
    public function store(Request $request)
    {
        $this->checkAuthorization();

        $validator = Validator::make($request->all(), [
            'unit_code' => 'required|string|unique:housing_units,unit_code|max:50',
            'block' => 'required|string|max:10',
            'number' => 'required|string|max:10',
            'type' => 'required|in:type_36,type_45,type_54,type_70,type_120',
            'price' => 'required|numeric|min:0',
            'building_area' => 'required|integer|min:1',
            'land_area' => 'required|integer|min:1',
            'bedrooms' => 'required|integer|min:1',
            'bathrooms' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'status' => 'required|in:available,booked,sold,unavailable',
            'image' => 'nullable|image|max:2048|mimes:jpeg,png,jpg,gif',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $data = $request->except('image');

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('units', $fileName, 'public');
                $data['image'] = $filePath;
            }

            HousingUnit::create($data);

            return redirect()->route('admin.housing-units.index')
                ->with('success', 'Unit perumahan berhasil diposting/ditambahkan!');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memposting unit: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show the form for editing the specified housing unit
     */
    public function edit($id)
    {
        $this->checkAuthorization();

        $unit = HousingUnit::findOrFail($id);
        $unitTypes = config('nup.unit_types');
        return view('admin.housing_units.edit', compact('unit', 'unitTypes'));
    }

    /**
     * Update the specified housing unit in storage
     */
    public function update(Request $request, $id)
    {
        $this->checkAuthorization();

        $unit = HousingUnit::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'unit_code' => 'required|string|max:50|unique:housing_units,unit_code,' . $unit->id,
            'block' => 'required|string|max:10',
            'number' => 'required|string|max:10',
            'type' => 'required|in:type_36,type_45,type_54,type_70,type_120',
            'price' => 'required|numeric|min:0',
            'building_area' => 'required|integer|min:1',
            'land_area' => 'required|integer|min:1',
            'bedrooms' => 'required|integer|min:1',
            'bathrooms' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'status' => 'required|in:available,booked,sold,unavailable',
            'image' => 'nullable|image|max:2048|mimes:jpeg,png,jpg,gif',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $data = $request->except('image');

            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($unit->image && Storage::disk('public')->exists($unit->image)) {
                    Storage::disk('public')->delete($unit->image);
                }

                $file = $request->file('image');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('units', $fileName, 'public');
                $data['image'] = $filePath;
            }

            $unit->update($data);

            return redirect()->route('admin.housing-units.index')
                ->with('success', 'Data unit perumahan berhasil diperbarui!');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui unit: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified housing unit from storage
     */
    public function destroy($id)
    {
        $this->checkAuthorization();

        $unit = HousingUnit::findOrFail($id);

        try {
            if ($unit->image && Storage::disk('public')->exists($unit->image)) {
                Storage::disk('public')->delete($unit->image);
            }

            $unit->delete();

            return redirect()->route('admin.housing-units.index')
                ->with('success', 'Unit perumahan berhasil dihapus!');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus unit: ' . $e->getMessage());
        }
    }
}
