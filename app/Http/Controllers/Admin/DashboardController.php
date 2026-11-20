<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeveloperCompany;
use App\Models\HousingUnit;
use App\Models\KprScreening;
use App\Models\NupRegistration;
use App\Models\PaymentTransaction;
use App\Models\SprDocument;
use App\Models\UnitSelection;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display admin dashboard
     */
    public function index()
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $companyId = Auth::user()->getOperatorCompanyId();

        // Get statistics
        $totalUsers = User::where('role', 'member')->count();
        $totalRegistrations = NupRegistration::when($companyId, fn($q) => $q->whereHas('unitSelections.housingUnit', fn($uq) => $uq->forCompany($companyId)))->count();
        $paidRegistrations = NupRegistration::when($companyId, fn($q) => $q->whereHas('unitSelections.housingUnit', fn($uq) => $uq->forCompany($companyId)))->where('payment_status', 'paid')->count();
        $pendingPayments = NupRegistration::when($companyId, fn($q) => $q->whereHas('unitSelections.housingUnit', fn($uq) => $uq->forCompany($companyId)))->where('payment_status', 'pending')->count();
        $totalUnits = HousingUnit::forCompany($companyId)->count();
        $availableUnits = HousingUnit::forCompany($companyId)->available()->count();
        $totalSelections = UnitSelection::when($companyId, fn($q) => $q->whereHas('housingUnit', fn($uq) => $uq->forCompany($companyId)))->count();
        $confirmedSelections = UnitSelection::where('status', 'confirmed')
            ->when($companyId, fn($q) => $q->whereHas('housingUnit', fn($uq) => $uq->forCompany($companyId)))
            ->count();
        $pendingScreenings = KprScreening::when($companyId, fn($q) => $q->whereHas('nupRegistration.unitSelections.housingUnit', fn($uq) => $uq->forCompany($companyId)))->where('status', 'pending')->count();
        $approvedScreenings = KprScreening::when($companyId, fn($q) => $q->whereHas('nupRegistration.unitSelections.housingUnit', fn($uq) => $uq->forCompany($companyId)))->where('status', 'approved')->count();
        $pendingSprs = SprDocument::where('status', 'draft')
            ->when($companyId, fn($q) => $q->whereHas('unitSelection.housingUnit', fn($uq) => $uq->forCompany($companyId)))
            ->count();
        $issuedSprs = SprDocument::whereIn('status', ['issued', 'paid'])
            ->when($companyId, fn($q) => $q->whereHas('unitSelection.housingUnit', fn($uq) => $uq->forCompany($companyId)))
            ->count();

        // Developer Companies (PT / CV) statistics
        $totalDevelopers = DeveloperCompany::count();
        $approvedDevelopers = DeveloperCompany::where('status', 'approved')->count();
        $pendingDevelopers = DeveloperCompany::where('status', 'pending')->count();
        $recentDevelopers = DeveloperCompany::with('user')->latest()->take(5)->get();

        // Recent activities
        $recentRegistrations = NupRegistration::with(['user', 'unitSelections.housingUnit'])
            ->when($companyId, fn($q) => $q->whereHas('unitSelections.housingUnit', fn($uq) => $uq->forCompany($companyId)))
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $recentPayments = PaymentTransaction::with('user')
            ->when($companyId, function ($q) use ($companyId) {
                $q->whereHas('nupRegistration.unitSelections.housingUnit', fn($uq) => $uq->forCompany($companyId));
            })
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $recentSelections = UnitSelection::with(['user', 'housingUnit'])
            ->when($companyId, fn($q) => $q->whereHas('housingUnit', fn($uq) => $uq->forCompany($companyId)))
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Revenue data
        $totalRevenue = NupRegistration::when($companyId, fn($q) => $q->whereHas('unitSelections.housingUnit', fn($uq) => $uq->forCompany($companyId)))
            ->where('payment_status', 'paid')
            ->sum('nup_fee');
        $todayRevenue = NupRegistration::when($companyId, fn($q) => $q->whereHas('unitSelections.housingUnit', fn($uq) => $uq->forCompany($companyId)))
            ->where('payment_status', 'paid')
            ->whereDate('payment_date', today())
            ->sum('nup_fee');

        // Rekap pembayaran rumah seluruh member
        $memberPaymentSummaries = SprDocument::whereIn('status', ['issued', 'paid'])
            ->when($companyId, fn($q) => $q->whereHas('unitSelection.housingUnit', fn($uq) => $uq->forCompany($companyId)))
            ->with([
                'user',
                'unitSelection.housingUnit',
                'kprPayments' => function ($q) {
                    $q->where('status', 'success')->orderBy('created_at', 'desc');
                },
            ])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($spr) {
                return [
                    'spr'            => $spr,
                    'user_name'      => $spr->user->name ?? '-',
                    'unit_identifier'=> $spr->unitSelection?->housingUnit?->full_identifier ?? '-',
                    'total_price'    => $spr->unit_price,
                    'total_paid'     => $spr->getTotalSudahDibayar(),
                    'sisa'           => $spr->getSisaPembayaran(),
                    'progress'       => $spr->getTotalPaymentProgress(),
                    'status'         => $spr->getPaymentStatus(),
                    'payment_scheme' => $spr->payment_scheme,
                    'kpr_payments'   => $spr->kprPayments,
                ];
            });

        return view('admin.dashboard', compact([
            'totalUsers',
            'totalRegistrations',
            'paidRegistrations',
            'pendingPayments',
            'totalUnits',
            'availableUnits',
            'totalSelections',
            'confirmedSelections',
            'pendingScreenings',
            'approvedScreenings',
            'pendingSprs',
            'issuedSprs',
            'recentRegistrations',
            'recentPayments',
            'recentSelections',
            'totalRevenue',
            'todayRevenue',
            'memberPaymentSummaries',
            'totalDevelopers',
            'approvedDevelopers',
            'pendingDevelopers',
            'recentDevelopers',
        ]));
    }

    /**
     * Marketing Admin Dashboard
     */
    public function marketing()
    {
        if (!Auth::user()->isOperator() && !Auth::user()->isSuperAdmin()) {
            abort(403);
        }

        $totalRegistrations = NupRegistration::count();
        $paidRegistrations = NupRegistration::where('payment_status', 'paid')->count();
        $totalSelections = UnitSelection::count();
        $availableUnits = HousingUnit::available()->count();

        $recentRegistrations = NupRegistration::with('user')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('admin.marketing', compact([
            'totalRegistrations',
            'paidRegistrations',
            'totalSelections',
            'availableUnits',
            'recentRegistrations',
        ]));
    }

    /**
     * Finance Admin Dashboard
     */
    public function finance()
    {
        if (!Auth::user()->isOperator() && !Auth::user()->isSuperAdmin()) {
            abort(403);
        }

        $totalRevenue = NupRegistration::where('payment_status', 'paid')->sum('nup_fee');
        $pendingPayments = NupRegistration::where('payment_status', 'pending')->count();
        $recentPayments = PaymentTransaction::with('user')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('admin.finance', compact([
            'totalRevenue',
            'pendingPayments',
            'recentPayments',
        ]));
    }

    /**
     * Screening Admin Dashboard
     */
    public function screening()
    {
        if (!Auth::user()->isPihakBank() && !Auth::user()->isSuperAdmin()) {
            abort(403);
        }

        $pendingScreenings = KprScreening::where('status', 'pending')->count();
        $processingScreenings = KprScreening::where('status', 'processing')->count();
        $approvedScreenings = KprScreening::where('status', 'approved')->count();
        $rejectedScreenings = KprScreening::where('status', 'rejected')->count();

        $recentScreenings = KprScreening::with('user')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('admin.screening', compact([
            'pendingScreenings',
            'processingScreenings',
            'approvedScreenings',
            'rejectedScreenings',
            'recentScreenings',
        ]));
    }
}