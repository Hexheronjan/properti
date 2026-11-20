<?php

namespace App\Http\Controllers;

use App\Models\DeveloperCompany;
use App\Models\HousingProject;
use App\Models\HousingUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
     * Check if operator owns this unit
     */
    protected function checkUnitOwnership(HousingUnit $unit)
    {
        $user = Auth::user();
        if ($user->isOperator() && !$user->isSuperAdmin()) {
            $operatorCompanyId = $user->company_id;
            $unitCompanyId = $unit->company_id ?? $unit->project?->company_id;
            if ($operatorCompanyId && $unitCompanyId && $unitCompanyId !== $operatorCompanyId) {
                abort(403, 'Anda hanya dapat mengelola unit perumahan milik perusahaan Anda.');
            }
        }
    }

    /**
     * Display a listing of housing units
     */
    public function index(Request $request)
    {
        $this->checkAuthorization();

        $user = Auth::user();
        $query = HousingUnit::with(['project', 'company']);

        // Scoping: Jika Operator, hanya tampilkan unit miliknya!
        if ($user->isOperator() && !$user->isSuperAdmin()) {
            $companyId = $user->company_id;
            $query->where(function ($q) use ($companyId) {
                $q->where('company_id', $companyId)
                  ->orWhereHas('project', function ($pq) use ($companyId) {
                      $pq->where('company_id', $companyId);
                  });
            });
        } elseif ($request->filled('company_id')) {
            // Super Admin filter by company
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        $units = $query->orderBy('block')->orderBy('number')->paginate(20);
        
        $projects = $user->isOperator() && !$user->isSuperAdmin()
            ? HousingProject::where('company_id', $user->company_id)->get()
            : HousingProject::all();

        return view('admin.housing_units.index', compact('units', 'projects'));
    }

    /**
     * Show the form for creating a new housing unit
     */
    public function create(Request $request)
    {
        $this->checkAuthorization();

        $user = Auth::user();
        $unitTypes = config('nup.unit_types');

        // Dropdown perumahan disesuaikan dengan hak akses
        $projects = $user->isOperator() && !$user->isSuperAdmin()
            ? HousingProject::where('company_id', $user->company_id)->where('is_active', true)->get()
            : HousingProject::where('is_active', true)->get();

        $selectedProjectId = $request->get('project_id');

        return view('admin.housing_units.create', compact('unitTypes', 'projects', 'selectedProjectId'));
    }

    /**
     * Store a newly created housing unit in storage
     */
    public function store(Request $request)
    {
        $this->checkAuthorization();

        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:housing_projects,id',
            'unit_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('housing_units', 'unit_code')->where(function ($query) use ($request) {
                    return $query->where('project_id', $request->project_id);
                }),
            ],
            'block' => 'required|string|max:10',
            'number' => 'required|string|max:10',
            'type' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
            'building_area' => 'required|integer|min:1',
            'land_area' => 'required|integer|min:1',
            'bedrooms' => 'required|integer|min:1',
            'bathrooms' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'status' => 'required|in:available,booked,sold,unavailable',
            'image' => 'nullable|image|max:5120|mimes:jpeg,png,jpg,gif,webp',
        ], $this->validationMessages());

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $data = $request->except('image');

            // Scoping: Kaitkan company_id otomatis
            if ($user->isOperator() && !$user->isSuperAdmin()) {
                $data['company_id'] = $user->company_id;
                // Pastikan jika pilih project, project tersebut milik perusahaannya
                if (!empty($data['project_id'])) {
                    $proj = HousingProject::find($data['project_id']);
                    if ($proj && $proj->company_id !== $user->company_id) {
                        abort(403, 'Anda tidak dapat menambahkan unit ke perumahan milik perusahaan lain.');
                    }
                }
            } elseif (!empty($data['project_id'])) {
                $proj = HousingProject::find($data['project_id']);
                if ($proj && $proj->company_id) {
                    $data['company_id'] = $proj->company_id;
                }
            }

            if ($request->hasFile('image')) {
                $filePath = $request->file('image')->store('units', 'public');
                if (!$filePath) {
                    throw new \RuntimeException('File tidak dapat disimpan. Periksa izin tulis folder storage/app/public.');
                }
                $this->publishUnitImage($filePath);
                $data['image'] = $filePath;
            }

            if (empty($data['type']) || $data['type'] === '-') {
                $lb = $data['building_area'] ?? '';
                $lt = $data['land_area'] ?? '';
                $data['type'] = 'Tipe ' . $lb . ($lt ? '/' . $lt : '');
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

        $user = Auth::user();
        $unit = HousingUnit::findOrFail($id);
        $this->checkUnitOwnership($unit);

        $unitTypes = config('nup.unit_types');
        $projects = $user->isOperator() && !$user->isSuperAdmin()
            ? HousingProject::where('company_id', $user->company_id)->get()
            : HousingProject::all();

        return view('admin.housing_units.edit', compact('unit', 'unitTypes', 'projects'));
    }

    /**
     * Update the specified housing unit in storage
     */
    public function update(Request $request, $id)
    {
        $this->checkAuthorization();

        $unit = HousingUnit::findOrFail($id);
        $this->checkUnitOwnership($unit);

        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:housing_projects,id',
            'unit_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('housing_units', 'unit_code')
                    ->ignore($unit->id)
                    ->where(function ($query) use ($request, $unit) {
                        $projectId = $request->project_id ?? $unit->project_id;
                        return $query->where('project_id', $projectId);
                    }),
            ],
            'block' => 'required|string|max:10',
            'number' => 'required|string|max:10',
            'type' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
            'building_area' => 'required|integer|min:1',
            'land_area' => 'required|integer|min:1',
            'bedrooms' => 'required|integer|min:1',
            'bathrooms' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'status' => 'required|in:available,booked,sold,unavailable',
            'image' => 'nullable|image|max:5120|mimes:jpeg,png,jpg,gif,webp',
        ], $this->validationMessages());

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $data = $request->except('image');
            $oldImage = $unit->image;

            if ($request->hasFile('image')) {
                $filePath = $request->file('image')->store('units', 'public');
                if (!$filePath) {
                    throw new \RuntimeException('File tidak dapat disimpan. Periksa izin tulis folder storage/app/public.');
                }
                $this->publishUnitImage($filePath);
                $data['image'] = $filePath;
            }

            if (empty($data['type']) || $data['type'] === '-') {
                $lb = $data['building_area'] ?? '';
                $lt = $data['land_area'] ?? '';
                $data['type'] = 'Tipe ' . $lb . ($lt ? '/' . $lt : '');
            }

            $unit->update($data);

            if (isset($filePath) && $oldImage) {
                $this->deleteUnitImage($oldImage);
            }

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
        $this->checkUnitOwnership($unit);

        try {
            if ($unit->image && Storage::disk('public')->exists($unit->image)) {
                $this->deleteUnitImage($unit->image);
            }

            $unit->delete();

            return redirect()->route('admin.housing-units.index')
                ->with('success', 'Unit perumahan berhasil dihapus!');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus unit: ' . $e->getMessage());
        }
    }

    /**
     * Pesan validasi bahasa Indonesia
     */
    protected function validationMessages(): array
    {
        return [
            'unit_code.required'     => 'Kode unit wajib diisi.',
            'unit_code.unique'       => 'Kode unit ":input" sudah digunakan pada perumahan ini. Gunakan kode unit lain untuk perumahan ini.',
            'project_id.required'    => 'Pilih perumahan (proyek) terlebih dahulu.',
            'block.required'         => 'Blok unit wajib diisi.',
            'number.required'        => 'Nomor unit wajib diisi.',
            'type.required'          => 'Tipe unit wajib dipilih.',
            'price.required'         => 'Harga unit wajib diisi.',
            'price.numeric'          => 'Harga unit harus berupa angka nominal.',
            'building_area.required' => 'Luas bangunan (LB) wajib diisi.',
            'land_area.required'     => 'Luas tanah (LT) wajib diisi.',
            'bedrooms.required'      => 'Jumlah kamar tidur wajib diisi.',
            'bathrooms.required'     => 'Jumlah kamar mandi wajib diisi.',
            'status.required'        => 'Status ketersediaan unit wajib dipilih.',
            'image.image'            => 'File yang diunggah harus berupa gambar.',
            'image.max'              => 'Ukuran foto unit maksimal 5 MB.',
            'image.mimes'            => 'Format foto harus berupa: jpeg, png, jpg, gif, atau webp.',
        ];
    }

    /**
     * Sinkronisasi file gambar
     */
    protected function publishUnitImage(string $relativePath): void
    {
        try {
            $source = storage_path('app/public/' . $relativePath);
            $destination = public_path('storage/' . $relativePath);

            if (!File::exists($source)) {
                return;
            }

            $directory = dirname($destination);
            if (!File::isDirectory($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            File::copy($source, $destination);
        } catch (\Throwable $e) {
            // Non-critical fallback
        }
    }

    /**
     * Hapus file gambar
     */
    protected function deleteUnitImage(string $relativePath): void
    {
        try {
            Storage::disk('public')->delete($relativePath);
            $published = public_path('storage/' . $relativePath);
            if (File::exists($published)) {
                File::delete($published);
            }
        } catch (\Throwable $e) {
            // Non-critical fallback
        }
    }
}
