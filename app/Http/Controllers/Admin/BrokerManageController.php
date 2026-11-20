<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Broker;
use App\Models\DeveloperCompany;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class BrokerManageController extends Controller
{
    /**
     * Check authorization (Operator or Super Admin)
     */
    protected function checkAuthorization()
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && !$user->isOperator()) {
            abort(403, 'Anda tidak memiliki hak untuk mengelola data broker.');
        }
    }

    /**
     * Display a listing of the brokers.
     */
    public function index(Request $request)
    {
        $this->checkAuthorization();
        $user = Auth::user();

        $query = Broker::with(['company', 'user.brokers.company'])->latest();

        // Isolasi multi-tenant: Jika Operator Developer, HANYA tampilkan broker perusahaannya!
        if ($user->isOperator() && !$user->isSuperAdmin()) {
            $query->where('company_id', $user->company_id);
        } elseif ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        $brokers = $query->paginate(15);
        $companies = $user->isSuperAdmin() ? DeveloperCompany::where('status', 'approved')->get() : collect();

        return view('admin.brokers.index', compact('brokers', 'companies'));
    }

    /**
     * Store a newly created broker in storage.
     */
    public function store(Request $request)
    {
        $this->checkAuthorization();
        $user = Auth::user();

        $existingUser = User::where('email', strtolower($request->email))->first();

        $rules = [
            'name' => 'required|string|max:255',
            'office_name' => 'nullable|string|max:255',
            'contact' => 'nullable|string|max:50',
            'email' => 'required|email',
            'password' => $existingUser ? 'nullable|string' : 'required|string|min:6',
            'address' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:500',
            'signature_image' => 'nullable|image|mimes:png,webp|max:3072',
            'is_active' => 'nullable|boolean',
        ];

        if ($user->isSuperAdmin()) {
            $rules['company_id'] = 'nullable|exists:developer_companies,id';
        }

        $request->validate($rules, [
            'name.required' => 'Nama broker / agen wajib diisi.',
            'email.required' => 'Email login akun broker wajib diisi.',
            'password.required' => 'Password login broker wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'signature_image.mimes' => 'Format tanda tangan harus PNG transparan atau WebP.',
        ]);

        $companyId = $user->isOperator() && !$user->isSuperAdmin() 
            ? $user->company_id 
            : $request->company_id;

        $signaturePath = null;
        if ($request->hasFile('signature_image')) {
            $signaturePath = $request->file('signature_image')->store('brokers/signatures', 'public');
        }

        // KASUS 1: Akun broker dengan email ini SUDAH ADA (misal sudah didaftarkan developer lain)
        if ($existingUser) {
            if (!$existingUser->isBroker()) {
                return back()->with('error', "Email '{$request->email}' sudah terdaftar sebagai pengguna lain ({$existingUser->role}) dan tidak dapat didaftarkan sebagai broker.")->withInput();
            }

            // Cek apakah broker sudah terhubung ke developer ini
            $alreadyPartner = Broker::where('user_id', $existingUser->id)
                ->where('company_id', $companyId)
                ->exists();

            if ($alreadyPartner) {
                return back()->with('error', "Broker dengan email '{$request->email}' sudah terdaftar sebagai mitra perusahaan Anda.")->withInput();
            }

            // Jika tidak mengunggah TTD baru, pakai TTD yang sudah pernah diunggah broker sebelumnya
            if (!$signaturePath) {
                $existingBroker = Broker::where('user_id', $existingUser->id)->whereNotNull('signature_image')->first();
                $signaturePath = $existingBroker?->signature_image;
            }

            // Hubungkan broker yang sudah ada ke pengembang ini
            Broker::create([
                'company_id' => $companyId,
                'user_id' => $existingUser->id,
                'name' => $request->name ?: $existingUser->name,
                'office_name' => $request->office_name ?: ($existingBroker?->office_name ?? null),
                'contact' => $request->contact ?: $existingUser->phone,
                'address' => $request->address ?: ($existingBroker?->address ?? null),
                'description' => $request->description,
                'signature_image' => $signaturePath,
                'is_active' => $request->boolean('is_active', true),
            ]);

            return back()->with('success', "Broker '{$existingUser->name}' ({$request->email}) berhasil dihubungkan sebagai mitra resmi pengembang Anda! Karena broker sudah memiliki akun, broker dapat langsung login dengan password yang sudah dimilikinya.");
        }

        // KASUS 2: Pendaftaran Akun Broker Baru
        // 1. Buatkan User akun login untuk Broker
        $brokerUser = User::create([
            'name' => $request->name,
            'email' => strtolower($request->email),
            'password' => Hash::make($request->password),
            'phone' => $request->contact,
            'role' => 'broker',
            'company_id' => $companyId,
            'approval_status' => 'active',
        ]);

        // 2. Buatkan Master Data Broker
        Broker::create([
            'company_id' => $companyId,
            'user_id' => $brokerUser->id,
            'name' => $request->name,
            'office_name' => $request->office_name,
            'contact' => $request->contact,
            'address' => $request->address,
            'description' => $request->description,
            'signature_image' => $signaturePath,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', "Akun Broker '{$request->name}' berhasil dibuat! Broker kini dapat login menggunakan email '{$request->email}'.");
    }

    /**
     * Update the specified broker in storage.
     */
    public function update(Request $request, $id)
    {
        $this->checkAuthorization();
        $user = Auth::user();

        $broker = Broker::with('user')->findOrFail($id);

        if ($user->isOperator() && !$user->isSuperAdmin() && $broker->company_id !== $user->company_id) {
            abort(403, 'Anda tidak memiliki hak untuk mengubah data broker ini.');
        }

        $userId = $broker->user_id;

        $rules = [
            'name' => 'required|string|max:255',
            'office_name' => 'nullable|string|max:255',
            'contact' => 'nullable|string|max:50',
            'email' => 'nullable|email|unique:users,email,' . ($userId ?? 'NULL'),
            'password' => 'nullable|string|min:6',
            'address' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:500',
            'signature_image' => 'nullable|image|mimes:png,webp|max:3072',
            'is_active' => 'nullable|boolean',
        ];

        if ($user->isSuperAdmin()) {
            $rules['company_id'] = 'nullable|exists:developer_companies,id';
        }

        $request->validate($rules);

        $data = [
            'name' => $request->name,
            'office_name' => $request->office_name,
            'contact' => $request->contact,
            'address' => $request->address,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
        ];

        if ($user->isSuperAdmin()) {
            $data['company_id'] = $request->filled('company_id') ? $request->company_id : null;
        }

        if ($request->hasFile('signature_image')) {
            if ($broker->signature_image && Storage::disk('public')->exists($broker->signature_image)) {
                Storage::disk('public')->delete($broker->signature_image);
            }
            $data['signature_image'] = $request->file('signature_image')->store('brokers/signatures', 'public');
        }

        $broker->update($data);

        // Update akun user login jika ada
        if ($broker->user) {
            $userUpdate = [
                'name' => $request->name,
                'phone' => $request->contact,
            ];
            if ($request->filled('email')) {
                $userUpdate['email'] = strtolower($request->email);
            }
            if ($request->filled('password')) {
                $userUpdate['password'] = Hash::make($request->password);
            }
            if ($user->isSuperAdmin() && $request->filled('company_id')) {
                $userUpdate['company_id'] = $request->company_id;
            }
            $broker->user->update($userUpdate);
        } elseif ($request->filled('email') && $request->filled('password')) {
            $newUser = User::create([
                'name' => $request->name,
                'email' => strtolower($request->email),
                'password' => Hash::make($request->password),
                'phone' => $request->contact,
                'role' => 'broker',
                'company_id' => $broker->company_id,
                'approval_status' => 'active',
            ]);
            $broker->update(['user_id' => $newUser->id]);
        }

        return back()->with('success', 'Data Broker dan akun login berhasil diperbarui.');
    }

    /**
     * Remove the specified broker from storage.
     */
    public function destroy($id)
    {
        $this->checkAuthorization();
        $user = Auth::user();

        $broker = Broker::with('user')->findOrFail($id);

        if ($user->isOperator() && !$user->isSuperAdmin() && $broker->company_id !== $user->company_id) {
            abort(403, 'Anda tidak berhak menghapus data broker ini.');
        }

        if ($broker->signature_image && Storage::disk('public')->exists($broker->signature_image)) {
            Storage::disk('public')->delete($broker->signature_image);
        }

        if ($broker->user) {
            $broker->user->delete();
        }

        $broker->delete();

        return back()->with('success', 'Broker dan akun login berhasil dihapus.');
    }
}

