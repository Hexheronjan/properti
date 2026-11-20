<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeveloperCompany;
use App\Models\HousingProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class HousingProjectManageController extends Controller
{
    /**
     * Check authorization (Operator or Super Admin)
     */
    protected function checkAuthorization()
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && !$user->isOperator()) {
            abort(403, 'Anda tidak memiliki hak untuk mengelola perumahan.');
        }
    }

    /**
     * Display listing of housing projects
     */
    public function index(Request $request)
    {
        $this->checkAuthorization();

        $user = Auth::user();
        $query = HousingProject::with(['company', 'housingUnits']);

        // Jika Operator: HANYA tampilkan perumahan milik perusahaannya sendiri!
        if ($user->isOperator() && !$user->isSuperAdmin()) {
            $query->where('company_id', $user->company_id);
        } elseif ($request->filled('company_id')) {
            // Super Admin filter by company
            $query->where('company_id', $request->company_id);
        }

        $projects = $query->latest()->paginate(12);
        $companies = $user->isSuperAdmin() ? DeveloperCompany::where('status', 'approved')->get() : collect();

        return view('admin.housing_projects.index', compact('projects', 'companies'));
    }

    /**
     * Show form to create new housing project
     */
    public function create()
    {
        $this->checkAuthorization();

        $user = Auth::user();
        $companies = $user->isSuperAdmin() ? DeveloperCompany::where('status', 'approved')->get() : collect();
        $availableSchemes = HousingProject::availablePaymentSchemes();

        $companyId = ($user->isOperator() && !$user->isSuperAdmin()) ? $user->company_id : null;
        $brokers = \App\Models\Broker::active()
            ->when($companyId, function ($q) use ($companyId) {
                $q->where(function ($sub) use ($companyId) {
                    $sub->where('company_id', $companyId)
                        ->orWhereNull('company_id');
                });
            })
            ->get();

        return view('admin.housing_projects.create', compact('companies', 'availableSchemes', 'brokers'));
    }

    /**
     * Store newly created housing project
     */
    public function store(Request $request)
    {
        $this->checkAuthorization();

        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'province' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'village' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'full_address' => 'nullable|string|max:500',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'kkpr_number' => 'nullable|string|max:100',
            'doc_kkpr' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'doc_permit_other' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'site_plan' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'supported_payment_schemes' => 'nullable|array',
            'commitment_fee' => 'nullable|numeric|min:0',
            'commitment_deadline_hours' => 'nullable|integer|min:1',
            'nup_deadline_days' => 'nullable|integer|min:1',
            'screening_fee' => 'nullable|numeric|min:0',
            'cash_due_days' => 'nullable|integer|min:1',
            'cash_penalty_percent' => 'nullable|numeric|min:0|max:100',
            'cash_allow_installments' => 'nullable|boolean',
            'inhouse_max_tenor_months' => 'nullable|integer|min:1|max:360',
            'inhouse_interest_rate' => 'nullable|numeric|min:0|max:100',
            'inhouse_due_day' => 'nullable|integer|min:1|max:31',
            'inhouse_penalty_percent' => 'nullable|numeric|min:0|max:100',
            'broker_id' => 'nullable|exists:brokers,id',
            'is_active' => 'nullable|boolean',
            'company_id' => $user->isSuperAdmin() ? 'nullable|exists:developer_companies,id' : 'nullable',
        ], [
            'name.required' => 'Nama perumahan wajib diisi.',
            'doc_kkpr.max' => 'Dokumen KKPR maksimal 10 MB.',
            'site_plan.max' => 'File site plan maksimal 10 MB.',
            'image.max' => 'Ukuran foto maksimal 5 MB.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Keamanan Multi-Tenant: Pastikan broker milik perusahaannya sendiri atau Mitra Umum Platform
        if ($request->filled('broker_id') && $user->isOperator() && !$user->isSuperAdmin()) {
            $brokerValid = \App\Models\Broker::where('id', $request->broker_id)
                ->where(function ($q) use ($user) {
                    $q->where('company_id', $user->company_id)
                      ->orWhereNull('company_id');
                })
                ->exists();
            if (!$brokerValid) {
                return back()->with('error', 'Broker yang dipilih tidak valid atau bukan mitra resmi perusahaan Anda.')->withInput();
            }
        }

        try {
            $data = $request->except(['image', 'site_plan', 'doc_kkpr', 'doc_permit_other', 'company_id']);

            // Scoping: Jika Operator, company_id otomatis terikat ke perusahaannya!
            if ($user->isOperator() && !$user->isSuperAdmin()) {
                $data['company_id'] = $user->company_id;
            } else {
                $data['company_id'] = $request->company_id;
            }

            // Fallback lokasi
            if (empty($data['location'])) {
                $locationParts = array_filter([$request->district, $request->city, $request->province]);
                $data['location'] = !empty($locationParts) ? implode(', ', $locationParts) : ($request->city ?? $request->name);
            }

            $data['is_active'] = $request->has('is_active') ? true : false;
            $data['cash_allow_installments'] = $request->has('cash_allow_installments') ? true : false;
            $data['supported_payment_schemes'] = $request->input('supported_payment_schemes', ['cash', 'kpr_bank']);

            // Upload files
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('projects', 'public');
            }
            if ($request->hasFile('site_plan')) {
                $data['site_plan'] = $request->file('site_plan')->store('siteplans', 'public');
            }
            if ($request->hasFile('doc_kkpr')) {
                $data['doc_kkpr'] = $request->file('doc_kkpr')->store('permits', 'public');
            }
            if ($request->hasFile('doc_permit_other')) {
                $data['doc_permit_other'] = $request->file('doc_permit_other')->store('permits', 'public');
            }

            HousingProject::create($data);

            return redirect()->route('admin.housing-projects.index')
                ->with('success', 'Perumahan baru berhasil ditambahkan!');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menambahkan perumahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show form to edit housing project
     */
    public function edit($id)
    {
        $this->checkAuthorization();

        $user = Auth::user();
        $project = HousingProject::findOrFail($id);

        if ($user->isOperator() && !$user->isSuperAdmin()) {
            if ($project->company_id !== $user->company_id) {
                abort(403, 'Anda tidak memiliki hak untuk mengedit perumahan ini.');
            }
        }

        $companies = $user->isSuperAdmin() ? DeveloperCompany::where('status', 'approved')->get() : collect();
        $availableSchemes = HousingProject::availablePaymentSchemes();
        $companyId = ($user->isOperator() && !$user->isSuperAdmin()) ? $user->company_id : $project->company_id;
        $brokers = \App\Models\Broker::active()
            ->when($companyId, function ($q) use ($companyId) {
                $q->where(function ($sub) use ($companyId) {
                    $sub->where('company_id', $companyId)
                        ->orWhereNull('company_id');
                });
            })
            ->get();

        return view('admin.housing_projects.edit', compact('project', 'companies', 'availableSchemes', 'brokers'));
    }

    /**
     * Update housing project
     */
    public function update(Request $request, $id)
    {
        $this->checkAuthorization();

        $user = Auth::user();
        $project = HousingProject::findOrFail($id);

        if ($user->isOperator() && !$user->isSuperAdmin()) {
            if ($project->company_id !== $user->company_id) {
                abort(403, 'Anda tidak memiliki hak untuk mengedit perumahan ini.');
            }
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'province' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'village' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'full_address' => 'nullable|string|max:500',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'kkpr_number' => 'nullable|string|max:100',
            'doc_kkpr' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'doc_permit_other' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'site_plan' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'supported_payment_schemes' => 'nullable|array',
            'commitment_fee' => 'nullable|numeric|min:0',
            'commitment_deadline_hours' => 'nullable|integer|min:1',
            'nup_deadline_days' => 'nullable|integer|min:1',
            'screening_fee' => 'nullable|numeric|min:0',
            'cash_due_days' => 'nullable|integer|min:1',
            'cash_penalty_percent' => 'nullable|numeric|min:0|max:100',
            'cash_allow_installments' => 'nullable|boolean',
            'inhouse_max_tenor_months' => 'nullable|integer|min:1|max:360',
            'inhouse_interest_rate' => 'nullable|numeric|min:0|max:100',
            'inhouse_due_day' => 'nullable|integer|min:1|max:31',
            'inhouse_penalty_percent' => 'nullable|numeric|min:0|max:100',
            'broker_id' => 'nullable|exists:brokers,id',
            'company_id' => $user->isSuperAdmin() ? 'nullable|exists:developer_companies,id' : 'nullable',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Keamanan Multi-Tenant: Pastikan broker milik perusahaannya sendiri atau Mitra Umum Platform
        if ($request->filled('broker_id') && $user->isOperator() && !$user->isSuperAdmin()) {
            $brokerValid = \App\Models\Broker::where('id', $request->broker_id)
                ->where(function ($q) use ($user) {
                    $q->where('company_id', $user->company_id)
                      ->orWhereNull('company_id');
                })
                ->exists();
            if (!$brokerValid) {
                return back()->with('error', 'Broker yang dipilih tidak valid atau bukan mitra resmi perusahaan Anda.')->withInput();
            }
        }

        try {
            $data = $request->except(['image', 'site_plan', 'doc_kkpr', 'doc_permit_other', 'company_id']);

            if ($user->isSuperAdmin() && $request->has('company_id')) {
                $data['company_id'] = $request->company_id;
            }

            if (empty($data['location'])) {
                $locationParts = array_filter([$request->district, $request->city, $request->province]);
                $data['location'] = !empty($locationParts) ? implode(', ', $locationParts) : ($request->city ?? $request->name);
            }

            $data['is_active'] = $request->has('is_active') ? true : false;
            $data['cash_allow_installments'] = $request->has('cash_allow_installments') ? true : false;
            $data['supported_payment_schemes'] = $request->input('supported_payment_schemes', []);

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('projects', 'public');
            }
            if ($request->hasFile('site_plan')) {
                $data['site_plan'] = $request->file('site_plan')->store('siteplans', 'public');
            }
            if ($request->hasFile('doc_kkpr')) {
                $data['doc_kkpr'] = $request->file('doc_kkpr')->store('permits', 'public');
            }
            if ($request->hasFile('doc_permit_other')) {
                $data['doc_permit_other'] = $request->file('doc_permit_other')->store('permits', 'public');
            }

            $project->update($data);

            return redirect()->route('admin.housing-projects.index')
                ->with('success', 'Data perumahan berhasil diperbarui!');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui perumahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Delete housing project
     */
    public function destroy($id)
    {
        $this->checkAuthorization();

        $user = Auth::user();
        $project = HousingProject::findOrFail($id);

        if ($user->isOperator() && !$user->isSuperAdmin()) {
            if ($project->company_id !== $user->company_id) {
                abort(403, 'Anda tidak memiliki hak untuk menghapus perumahan ini.');
            }
        }

        $project->delete();

        return redirect()->route('admin.housing-projects.index')
            ->with('success', 'Perumahan berhasil dihapus.');
    }
}
