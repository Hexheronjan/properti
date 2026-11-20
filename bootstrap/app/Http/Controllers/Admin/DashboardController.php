<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

        // Get statistics
        $totalUsers = User::where('role', 'member')->count();
        $totalRegistrations = NupRegistration::count();
        $paidRegistrations = NupRegistration::where('payment_status', 'paid')->count();
        $pendingPayments = NupRegistration::where('payment_status', 'pending')->count();
        $totalUnits = HousingUnit::count();
        $availableUnits = HousingUnit::available()->count();
        $totalSelections = UnitSelection::count();
        $confirmedSelections = UnitSelection::where('status', 'confirmed')->count();
        $pendingScreenings = KprScreening::where('status', 'pending')->count();
        $approvedScreenings = KprScreening::where('status', 'approved')->count();
        $pendingSprs = SprDocument::where('status', 'draft')->count();
        $issuedSprs = SprDocument::where('status', 'issued')->count();

        // Recent activities
        $recentRegistrations = NupRegistration::with('user')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $recentPayments = PaymentTransaction::with('user')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $recentSelections = UnitSelection::with(['user', 'housingUnit'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Revenue data
        $totalRevenue = NupRegistration::where('payment_status', 'paid')->sum('nup_fee');
        $todayRevenue = NupRegistration::where('payment_status', 'paid')
            ->whereDate('payment_date', today())
            ->sum('nup_fee');

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