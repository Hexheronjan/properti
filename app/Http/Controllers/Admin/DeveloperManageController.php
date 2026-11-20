<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeveloperCompany;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class DeveloperManageController extends Controller
{
    /**
     * Check authorization (Only Super Admin can manage developer approvals)
     */
    protected function checkSuperAdmin()
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Hanya Super Admin yang berhak menyetujui pendaftaran pengembang.');
        }
    }

    /**
     * Display a listing of developer applications
     */
    public function index(Request $request)
    {
        $this->checkSuperAdmin();

        $status = $request->get('status', 'all');

        $query = DeveloperCompany::with(['user', 'approvedBy', 'housingProjects']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $developers = $query->latest()->paginate(15);

        $counts = [
            'all' => DeveloperCompany::count(),
            'pending' => DeveloperCompany::where('status', 'pending')->count(),
            'approved' => DeveloperCompany::where('status', 'approved')->count(),
            'rejected' => DeveloperCompany::where('status', 'rejected')->count(),
        ];

        return view('admin.developers.index', compact('developers', 'status', 'counts'));
    }

    /**
     * Show form for Super Admin to create a developer / operator directly
     */
    public function create()
    {
        $this->checkSuperAdmin();

        return view('admin.developers.create');
    }

    /**
     * Store developer created directly by Super Admin (auto-approved & active)
     */
    public function store(Request $request)
    {
        $this->checkSuperAdmin();

        $validator = Validator::make($request->all(), [
            // Data Akun / PIC
            'pic_name' => 'required|string|max:255',
            'pic_nik' => 'required|string|digits:16',
            'pic_address' => 'required|string|max:500',
            'pic_phone' => 'required|string|max:30',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => 'required|string|min:6|confirmed',

            // Data Usaha
            'entity_type' => 'required|in:badan_usaha,perorangan',
            'business_type' => 'nullable|required_if:entity_type,badan_usaha|string|in:pt,cv,lainnya',
            'business_type_other' => 'nullable|required_if:business_type,lainnya|string|max:100',
            'company_name' => 'required|string|max:255',
            'nib' => 'nullable|string|max:100',
            'company_address' => 'required|string|max:500',
            'company_phone' => 'required|string|max:30',
            'company_email' => 'required|email|max:255',
            'website' => 'nullable|string|max:255',
            'company_initials' => 'nullable|string|max:20',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'signature_image' => 'nullable|image|mimes:png,webp|max:3072',
        ], [
            'pic_name.required' => 'Nama penanggung jawab wajib diisi.',
            'pic_nik.required' => 'NIK penanggung jawab wajib diisi.',
            'pic_nik.digits' => 'NIK penanggung jawab harus 16 digit angka.',
            'pic_address.required' => 'Alamat penanggung jawab wajib diisi.',
            'pic_phone.required' => 'Nomor WhatsApp wajib diisi.',
            'email.required' => 'Email login wajib diisi.',
            'email.unique' => 'Email ini sudah terdaftar di sistem.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'company_name.required' => 'Nama badan usaha / nama usaha wajib diisi.',
            'company_address.required' => 'Alamat kantor / usaha wajib diisi.',
            'company_phone.required' => 'Nomor telepon kantor wajib diisi.',
            'company_email.required' => 'Email perusahaan wajib diisi.',
            'business_type.required_if' => 'Jenis badan usaha wajib dipilih (PT / CV / Lainnya).',
            'business_type_other.required_if' => 'Mohon sebutkan jenis badan usaha jika memilih opsi Lainnya.',
            'logo.max' => 'Ukuran logo maksimal 3 MB.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $logoPath = null;
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('companies', 'public');
            }

            $signaturePath = null;
            if ($request->hasFile('signature_image')) {
                $signaturePath = $request->file('signature_image')->store('companies/signatures', 'public');
            }

            $businessType = null;
            if ($request->entity_type === 'badan_usaha') {
                if ($request->business_type === 'lainnya' && $request->filled('business_type_other')) {
                    $businessType = trim($request->business_type_other);
                } else {
                    $businessType = $request->business_type;
                }
            }

            // 1. Create Developer Company with status 'approved' directly
            $company = DeveloperCompany::create([
                'pic_name' => $request->pic_name,
                'pic_nik' => $request->pic_nik,
                'pic_address' => $request->pic_address,
                'pic_phone' => $request->pic_phone,
                'entity_type' => $request->entity_type,
                'business_type' => $businessType,
                'company_name' => $request->company_name,
                'company_initials' => $request->company_initials ? strtoupper(trim($request->company_initials)) : null,
                'nib' => $request->nib,
                'address' => $request->company_address,
                'phone' => $request->company_phone,
                'email' => $request->company_email,
                'website' => $request->website,
                'logo' => $logoPath,
                'signature_image' => $signaturePath,
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => Auth::id(),
            ]);

            // 2. Create User account with role 'operator' and approval_status 'active'
            $user = User::create([
                'name' => $request->pic_name,
                'nik' => $request->pic_nik,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'operator',
                'phone' => $request->pic_phone,
                'address' => $request->pic_address,
                'company_id' => $company->id,
                'approval_status' => 'active',
            ]);

            // Link user back to company
            $company->update(['user_id' => $user->id]);

            DB::commit();

            return redirect()->route('admin.developers.index')
                ->with('success', "🎉 Berhasil menambahkan pengembang '{$company->company_name}'! Akun Operator untuk {$user->name} ({$user->email}) langsung aktif dan siap digunakan login.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menambahkan pengembang: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified developer application
     */
    public function show($id)
    {
        $this->checkSuperAdmin();

        $developer = DeveloperCompany::with(['user', 'approvedBy', 'housingProjects.housingUnits'])->findOrFail($id);

        return view('admin.developers.show', compact('developer'));
    }

    /**
     * Approve developer application
     */
    public function approve($id)
    {
        $this->checkSuperAdmin();

        $developer = DeveloperCompany::with('user')->findOrFail($id);

        if ($developer->status === 'approved') {
            return back()->with('info', 'Pengembang ini sudah disetujui sebelumnya.');
        }

        try {
            DB::beginTransaction();

            $developer->update([
                'status' => 'approved',
                'rejection_reason' => null,
                'approved_at' => now(),
                'approved_by' => Auth::id(),
            ]);

            if ($developer->user) {
                $developer->user->update([
                    'role' => 'operator',
                    'approval_status' => 'active',
                    'company_id' => $developer->id,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.developers.index')
                ->with('success', "🎉 Pengembang '{$developer->company_name}' berhasil disetujui! Operator ({$developer->pic_name}) kini dapat login dan mengelola perumahan.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyetujui pengembang: ' . $e->getMessage());
        }
    }

    /**
     * Reject developer application
     */
    public function reject(Request $request, $id)
    {
        $this->checkSuperAdmin();

        $request->validate([
            'reason' => 'required|string|max:1000',
        ], [
            'reason.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $developer = DeveloperCompany::with('user')->findOrFail($id);

        try {
            DB::beginTransaction();

            $developer->update([
                'status' => 'rejected',
                'rejection_reason' => $request->reason,
            ]);

            if ($developer->user) {
                $developer->user->update([
                    'approval_status' => 'rejected',
                ]);
            }

            DB::commit();

            return redirect()->route('admin.developers.index')
                ->with('success', "Pendaftaran '{$developer->company_name}' telah ditolak.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menolak pengembang: ' . $e->getMessage());
        }
    }

    /**
     * Reset password of developer company operator account by Super Admin
     */
    public function resetPassword(Request $request, $id)
    {
        $this->checkSuperAdmin();

        $developer = DeveloperCompany::with('user')->findOrFail($id);

        if (!$developer->user) {
            return back()->with('error', 'Pengembang ini belum memiliki akun Operator yang terhubung.');
        }

        // Bersihkan spasi di awal dan akhir password jika diinputkan (spasi tengah diizinkan)
        if ($request->has('password') && is_string($request->password)) {
            $trimmedPassword = trim($request->password);
            $trimmedConfirm  = $request->has('password_confirmation') && is_string($request->password_confirmation) 
                ? trim($request->password_confirmation) 
                : $request->password_confirmation;

            $request->merge([
                'password' => $trimmedPassword === '' ? null : $trimmedPassword,
                'password_confirmation' => $trimmedConfirm === '' ? null : $trimmedConfirm,
            ]);
        }

        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ], [
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $newPassword = $request->password;
        $developer->user->update([
            'password' => Hash::make($newPassword),
        ]);

        // Buat format pesan WhatsApp siap kirim ke PIC
        $rawPhone = $developer->pic_phone ?: ($developer->user->phone ?? '');
        $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '62' . substr($cleanPhone, 1);
        }

        $waMessage = "Halo Bapak/Ibu {$developer->pic_name} ({$developer->company_name}),\n\n"
            . "Password akun Operator APRO Anda telah berhasil diperbarui oleh Super Admin:\n"
            . "• Email Login: {$developer->user->email}\n"
            . "• Password Baru: {$newPassword}\n\n"
            . "Silakan login di " . url('/login') . " dan Anda dapat mengganti password ini kapan saja.\n\n"
            . "Terima kasih,\nTim APRO";

        $waUrl = !empty($cleanPhone) ? "https://wa.me/{$cleanPhone}?text=" . rawurlencode($waMessage) : null;

        return back()->with([
            'success' => "🎉 Password akun Operator ({$developer->user->email}) berhasil diperbarui!",
            'reset_password_data' => [
                'company_name' => $developer->company_name,
                'pic_name' => $developer->pic_name,
                'email' => $developer->user->email,
                'password' => $newPassword,
                'phone' => $rawPhone,
                'wa_url' => $waUrl,
            ],
        ]);
    }
}
