<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeveloperBankAccount;
use App\Models\DeveloperCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeveloperBankAccountController extends Controller
{
    /**
     * Display a listing of the developer bank accounts.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = $user->isSuperAdmin();
        $company = $user->company ?? $user->developerCompany;

        if ($isSuperAdmin) {
            $companies = DeveloperCompany::where('status', 'approved')->orderBy('company_name')->get();
            $query = DeveloperBankAccount::with('developerCompany');
            if ($request->filled('company_id')) {
                $query->where('developer_company_id', $request->company_id);
            }
            $bankAccounts = $query->orderBy('developer_company_id')->latest()->paginate(15);
        } else {
            if (!$company) {
                return redirect()->route('admin.dashboard')
                    ->with('error', 'Akun Anda belum terhubung dengan data Perusahaan Developer.');
            }
            $companies = collect([$company]);
            $bankAccounts = DeveloperBankAccount::where('developer_company_id', $company->id)
                ->latest()
                ->paginate(15);
        }

        return view('admin.bank-accounts.index', compact('bankAccounts', 'companies', 'isSuperAdmin', 'company'));
    }

    /**
     * Store a newly created bank account.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = $user->isSuperAdmin();
        $company = $user->company ?? $user->developerCompany;

        $rules = [
            'bank_name'           => 'required|string|max:50',
            'account_number'      => 'required|string|max:50',
            'account_holder_name' => 'required|string|max:100',
            'branch'              => 'nullable|string|max:100',
        ];

        if ($isSuperAdmin) {
            $rules['developer_company_id'] = 'required|exists:developer_companies,id';
        }

        $validated = $request->validate($rules);

        $companyId = $isSuperAdmin ? $validated['developer_company_id'] : $company->id;

        DeveloperBankAccount::create([
            'developer_company_id' => $companyId,
            'bank_name'           => strtoupper(trim($validated['bank_name'])),
            'account_number'      => trim($validated['account_number']),
            'account_holder_name' => trim($validated['account_holder_name']),
            'branch'              => $validated['branch'] ?? null,
            'is_active'           => true,
        ]);

        return redirect()->route('admin.bank-accounts.index')
            ->with('success', 'Rekening bank pengembang berhasil ditambahkan.');
    }

    /**
     * Update the specified bank account.
     */
    public function update(Request $request, $id)
    {
        $bankAccount = DeveloperBankAccount::findOrFail($id);
        $user = Auth::user();
        $company = $user->company ?? $user->developerCompany;

        if (!$user->isSuperAdmin() && (!$company || $bankAccount->developer_company_id !== $company->id)) {
            abort(403);
        }

        $validated = $request->validate([
            'bank_name'           => 'required|string|max:50',
            'account_number'      => 'required|string|max:50',
            'account_holder_name' => 'required|string|max:100',
            'branch'              => 'nullable|string|max:100',
            'is_active'           => 'nullable|boolean',
        ]);

        $bankAccount->update([
            'bank_name'           => strtoupper(trim($validated['bank_name'])),
            'account_number'      => trim($validated['account_number']),
            'account_holder_name' => trim($validated['account_holder_name']),
            'branch'              => $validated['branch'] ?? null,
            'is_active'           => $request->has('is_active') ? (bool) $request->is_active : $bankAccount->is_active,
        ]);

        return redirect()->route('admin.bank-accounts.index')
            ->with('success', 'Rekening bank berhasil diperbarui.');
    }

    /**
     * Remove the specified bank account.
     */
    public function destroy($id)
    {
        $bankAccount = DeveloperBankAccount::findOrFail($id);
        $user = Auth::user();
        $company = $user->company ?? $user->developerCompany;

        if (!$user->isSuperAdmin() && (!$company || $bankAccount->developer_company_id !== $company->id)) {
            abort(403);
        }

        $bankAccount->delete();

        return redirect()->route('admin.bank-accounts.index')
            ->with('success', 'Rekening bank berhasil dihapus.');
    }
}
