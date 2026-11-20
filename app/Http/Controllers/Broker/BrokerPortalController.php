<?php

namespace App\Http\Controllers\Broker;

use App\Http\Controllers\Controller;
use App\Models\Broker;
use App\Models\HousingProject;
use App\Models\HousingUnit;
use App\Models\NupRegistration;
use App\Models\UnitSelection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class BrokerPortalController extends Controller
{
    /**
     * Dapatkan semua kemitraan broker dengan developer
     */
    protected function getBrokerPartnerships()
    {
        $user = Auth::user();
        if (!$user || !$user->isBroker()) {
            abort(403, 'Akses khusus untuk akun mitra broker.');
        }

        $partnerships = Broker::with('company')
            ->where('user_id', $user->id)
            ->get();

        if ($partnerships->isEmpty()) {
            // Fallback jika broker terdaftar via company_id di user
            $partnerships = Broker::with('company')
                ->where('company_id', $user->company_id)
                ->get();
        }

        return $partnerships;
    }

    /**
     * Tentukan ID perusahaan developer yang sedang dipilih oleh broker
     */
    protected function resolveSelectedCompanyId(Request $request, $partnerships)
    {
        if ($request->has('company_id')) {
            $companyId = $request->query('company_id');
            session(['broker_selected_company_id' => $companyId]);
            return $companyId;
        }

        return session('broker_selected_company_id', 'all');
    }

    /**
     * Dashboard Utama Portal Broker
     */
    public function dashboard(Request $request)
    {
        $partnerships = $this->getBrokerPartnerships();
        $selectedCompanyId = $this->resolveSelectedCompanyId($request, $partnerships);

        $activePartnerships = $partnerships;
        if ($selectedCompanyId !== 'all') {
            $matched = $partnerships->where('company_id', (int) $selectedCompanyId);
            if ($matched->isNotEmpty()) {
                $activePartnerships = $matched;
            } else {
                $selectedCompanyId = 'all';
            }
        }

        $brokerIds = $activePartnerships->pluck('id')->toArray();
        $companyIds = $activePartnerships->pluck('company_id')->filter()->toArray();

        // Hitung statistik
        $totalLeads = NupRegistration::whereIn('broker_id', $brokerIds)->count();
        $paidNupLeads = NupRegistration::whereIn('broker_id', $brokerIds)->where('payment_status', 'paid')->count();

        $totalClosing = UnitSelection::whereHas('housingUnit.housingProject', function ($q) use ($companyIds) {
            if (!empty($companyIds)) {
                $q->whereIn('company_id', $companyIds);
            }
        })->whereHas('nupRegistration', function ($q) use ($brokerIds) {
            $q->whereIn('broker_id', $brokerIds);
        })->whereIn('status', ['confirmed'])->count();

        $availableStock = HousingUnit::whereHas('housingProject', function ($q) use ($companyIds) {
            if (!empty($companyIds)) {
                $q->whereIn('company_id', $companyIds);
            }
        })->where('status', 'available')->count();

        // Lead terbaru
        $recentLeads = NupRegistration::with(['user', 'unitSelections.housingUnit.housingProject.company'])
            ->whereIn('broker_id', $brokerIds)
            ->latest()
            ->take(5)
            ->get();

        $primaryBroker = $activePartnerships->first() ?? $partnerships->first();
        $activeCompany = $selectedCompanyId !== 'all' ? $primaryBroker?->company : null;

        return view('broker.dashboard', compact(
            'partnerships',
            'selectedCompanyId',
            'primaryBroker',
            'activeCompany',
            'totalLeads',
            'paidNupLeads',
            'totalClosing',
            'availableStock',
            'recentLeads'
        ));
    }

    /**
     * Katalog & Stok Real-Time Unit (Hanya Perumahan Milik Developer Rekanan)
     */
    public function units(Request $request)
    {
        $partnerships = $this->getBrokerPartnerships();
        $selectedCompanyId = $this->resolveSelectedCompanyId($request, $partnerships);

        $activePartnerships = $partnerships;
        if ($selectedCompanyId !== 'all') {
            $matched = $partnerships->where('company_id', (int) $selectedCompanyId);
            if ($matched->isNotEmpty()) {
                $activePartnerships = $matched;
            } else {
                $selectedCompanyId = 'all';
            }
        }

        $companyIds = $activePartnerships->pluck('company_id')->filter()->toArray();

        // Ambil proyek perumahan milik developer mitra
        $projectsQuery = HousingProject::with('company')->active();
        if (!empty($companyIds)) {
            $projectsQuery->whereIn('company_id', $companyIds);
        }
        $projects = $projectsQuery->get();

        $query = HousingUnit::with(['housingProject.company'])
            ->whereHas('housingProject', function ($q) use ($companyIds) {
                if (!empty($companyIds)) {
                    $q->whereIn('company_id', $companyIds);
                }
            });

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $units = $query->orderBy('status', 'asc')
            ->orderBy('block', 'asc')
            ->orderBy('number', 'asc')
            ->paginate(24);

        $unitTypes = config('nup.unit_types', []);
        $primaryBroker = $activePartnerships->first() ?? $partnerships->first();
        $activeCompany = $selectedCompanyId !== 'all' ? $primaryBroker?->company : null;

        return view('broker.units', compact(
            'partnerships',
            'selectedCompanyId',
            'primaryBroker',
            'activeCompany',
            'projects',
            'units',
            'unitTypes'
        ));
    }

    /**
     * Daftar Calon Pembeli / Leads yang Terdaftar Lewat Broker Ini
     */
    public function leads(Request $request)
    {
        $partnerships = $this->getBrokerPartnerships();
        $selectedCompanyId = $this->resolveSelectedCompanyId($request, $partnerships);

        $activePartnerships = $partnerships;
        if ($selectedCompanyId !== 'all') {
            $matched = $partnerships->where('company_id', (int) $selectedCompanyId);
            if ($matched->isNotEmpty()) {
                $activePartnerships = $matched;
            } else {
                $selectedCompanyId = 'all';
            }
        }

        $brokerIds = $activePartnerships->pluck('id')->toArray();

        $query = NupRegistration::with([
            'user',
            'kprScreening',
            'unitSelections.housingUnit.housingProject.company',
            'unitSelections.utkPayments',
            'sprDocuments',
        ])->whereIn('broker_id', $brokerIds);

        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        $leads = $query->latest()->paginate(15);
        $primaryBroker = $activePartnerships->first() ?? $partnerships->first();
        $activeCompany = $selectedCompanyId !== 'all' ? $primaryBroker?->company : null;

        return view('broker.leads', compact(
            'partnerships',
            'selectedCompanyId',
            'primaryBroker',
            'activeCompany',
            'leads'
        ));
    }

    /**
     * Halaman Profil & Tanda Tangan Broker
     */
    public function profile()
    {
        $partnerships = $this->getBrokerPartnerships();
        $primaryBroker = $partnerships->first();
        $user = Auth::user();

        if (!$primaryBroker) {
            $companyId = $user->company_id ?? \App\Models\Company::first()?->id ?? 1;
            $primaryBroker = Broker::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'company_id' => $companyId,
                    'name' => $user->name,
                    'contact' => $user->phone ?? '',
                    'is_active' => true,
                ]
            );
            $partnerships = collect([$primaryBroker->load('company')]);
        }

        $broker = $primaryBroker;
        return view('broker.profile', compact('partnerships', 'broker', 'primaryBroker', 'user'));
    }

    /**
     * Simpan Perubahan Profil & Tanda Tangan
     */
    public function updateProfile(Request $request)
    {
        $partnerships = $this->getBrokerPartnerships();
        $user = Auth::user();

        if ($partnerships->isEmpty()) {
            $companyId = $user->company_id ?? \App\Models\Company::first()?->id ?? 1;
            $newBroker = Broker::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'company_id' => $companyId,
                    'name' => $user->name,
                    'contact' => $user->phone ?? '',
                    'is_active' => true,
                ]
            );
            $partnerships = collect([$newBroker]);
        }

        $request->validate([
            'office_name' => 'nullable|string|max:255',
            'contact' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:500',
            'signature_image' => 'nullable|image|mimes:png,webp|max:3072',
            'password' => 'nullable|string|min:6|confirmed',
        ], [
            'signature_image.mimes' => 'Format file tanda tangan harus PNG transparan atau WebP.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $data = [
            'office_name' => $request->office_name,
            'contact' => $request->contact,
            'address' => $request->address,
            'description' => $request->description,
        ];

        if ($request->hasFile('signature_image')) {
            $primary = $partnerships->first();
            if ($primary && $primary->signature_image && Storage::disk('public')->exists($primary->signature_image)) {
                Storage::disk('public')->delete($primary->signature_image);
            }
            $data['signature_image'] = $request->file('signature_image')->store('brokers/signatures', 'public');
        }

        // Sinkronisasi data ke semua entri kemitraan broker ini
        foreach ($partnerships as $b) {
            $b->update($data);
        }

        // Update akun user
        $userUpdate = [
            'phone' => $request->contact,
        ];
        if ($request->filled('password')) {
            $userUpdate['password'] = Hash::make($request->password);
        }
        $user->update($userUpdate);

        return back()->with('success', 'Profil dan tanda tangan broker berhasil diperbarui.');
    }
}
