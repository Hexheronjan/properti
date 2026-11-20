<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PromoCodeController extends Controller
{
    protected function checkSuperAdmin(): void
    {
        if (!auth()->check() || !auth()->user()->isSuperAdmin()) {
            abort(403, 'Hanya Super Admin yang berhak mengelola kode promo.');
        }
    }

    public function index()
    {
        $this->checkSuperAdmin();

        $promoCodes = PromoCode::latest()->paginate(20);

        return view('admin.promo-codes.index', compact('promoCodes'));
    }

    public function store(Request $request)
    {
        $this->checkSuperAdmin();

        $data = $this->validatedData($request);
        $data['code'] = strtoupper(trim($data['code']));
        $data['used_count'] = 0;
        $data['is_active'] = $request->boolean('is_active');

        PromoCode::create($data);

        return redirect()->route('admin.promo-codes.index')->with('success', 'Kode promo berhasil dibuat.');
    }

    public function update(Request $request, PromoCode $promoCode)
    {
        $this->checkSuperAdmin();

        $data = $this->validatedData($request, $promoCode);
        $data['code'] = strtoupper(trim($data['code']));
        $data['is_active'] = $request->boolean('is_active');
        $promoCode->update($data);

        return redirect()->route('admin.promo-codes.index')->with('success', 'Kode promo berhasil diperbarui.');
    }

    public function destroy(PromoCode $promoCode)
    {
        $this->checkSuperAdmin();

        $promoCode->delete();

        return redirect()->route('admin.promo-codes.index')->with('success', 'Kode promo berhasil dihapus.');
    }

    private function validatedData(Request $request, ?PromoCode $promoCode = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('promo_codes', 'code')->ignore($promoCode?->id)],
            'payable_amount' => 'required|integer|min:0|max:100000000',
            'applicable_to' => ['required', 'string', 'in:all,nup,utk'],
            'usage_limit' => 'required|integer|min:1|max:100000',
            'allowed_email' => 'nullable|email|max:255',
            'expires_at' => 'nullable|date|after:now',
        ]);
    }
}
