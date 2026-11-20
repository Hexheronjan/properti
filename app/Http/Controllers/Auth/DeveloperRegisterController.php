<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\DeveloperCompany;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DeveloperRegisterController extends Controller
{
    /**
     * Show developer / operator registration form
     */
    public function showRegistrationForm(Request $request)
    {
        $reapplyId = $request->get('reapply', old('reapply_company_id'));
        $reapplyDeveloper = null;

        if ($reapplyId) {
            $reapplyDeveloper = DeveloperCompany::with('user')
                ->where('id', $reapplyId)
                ->where('status', 'rejected')
                ->first();
        } elseif ($request->filled('email') || old('email')) {
            $checkEmail = $request->get('email', old('email'));
            $reapplyDeveloper = DeveloperCompany::with('user')
                ->where(function ($q) use ($checkEmail) {
                    $q->where('email', $checkEmail)
                      ->orWhereHas('user', fn($uq) => $uq->where('email', $checkEmail));
                })
                ->where('status', 'rejected')
                ->latest()
                ->first();
        }

        return view('auth.register-developer', compact('reapplyDeveloper'));
    }

    /**
     * Process developer registration
     */
    public function register(Request $request)
    {
        // Cek apakah ada reapply_company_id atau email / NIK milik pendaftaran operator yang sebelumnya DITOLAK (Pengajuan Ulang)
        $rejectedCompany = null;
        if ($request->filled('reapply_company_id')) {
            $rejectedCompany = DeveloperCompany::with('user')
                ->where('id', $request->reapply_company_id)
                ->where('status', 'rejected')
                ->first();
        }

        $existingUserByEmail = User::where('email', $request->email)->first();
        $existingUserByNik   = $request->filled('pic_nik') ? User::where('nik', $request->pic_nik)->first() : null;

        $rejectedUser = null;
        if ($rejectedCompany) {
            $rejectedUser = $rejectedCompany->user ?: User::find($rejectedCompany->user_id);
        }

        if (!$rejectedUser) {
            if ($existingUserByEmail && $existingUserByEmail->role === 'operator' && $existingUserByEmail->approval_status === 'rejected') {
                $rejectedUser = $existingUserByEmail;
            } elseif ($existingUserByNik && $existingUserByNik->role === 'operator' && $existingUserByNik->approval_status === 'rejected') {
                $rejectedUser = $existingUserByNik;
            }
        }

        if ($rejectedUser && !$rejectedCompany) {
            $rejectedCompany = $rejectedUser->company 
                ?: DeveloperCompany::where('user_id', $rejectedUser->id)->first()
                ?: DeveloperCompany::where('email', $request->company_email)->first();
        }

        // Bersihkan spasi di awal dan akhir password & konfirmasi jika ada (spasi tengah tetap diizinkan)
        if ($request->has('password') && is_string($request->password)) {
            $trimmedPassword = trim($request->password);
            $request->merge([
                'password' => $trimmedPassword === '' ? null : $trimmedPassword,
            ]);
        }
        if ($request->has('password_confirmation') && is_string($request->password_confirmation)) {
            $trimmedConfirm = trim($request->password_confirmation);
            $request->merge([
                'password_confirmation' => $trimmedConfirm === '' ? null : $trimmedConfirm,
            ]);
        }

        $emailRule    = ['required', 'email', 'max:255'];
        $nikRule      = ['required', 'string', 'digits:16'];
        $passwordRule = $rejectedUser ? ['nullable', 'string', 'min:6', 'confirmed'] : ['required', 'string', 'min:6', 'confirmed'];

        if ($rejectedUser) {
            $emailRule[] = Rule::unique('users', 'email')->ignore($rejectedUser->id);
            $nikRule[]   = Rule::unique('users', 'nik')->ignore($rejectedUser->id);
        } else {
            $emailRule[] = 'unique:users,email';
            $nikRule[]   = 'unique:users,nik';
        }

        $validator = Validator::make($request->all(), [
            // Data Akun / PIC
            'pic_name' => 'required|string|max:255',
            'pic_nik' => $nikRule,
            'pic_address' => 'required|string|max:500',
            'pic_phone' => 'required|string|max:30',
            'email' => $emailRule,
            'password' => $passwordRule,

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
            'pic_nik.unique' => 'NIK penanggung jawab sudah terdaftar di sistem.',
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

            // Tentukan jenis badan usaha (jika 'lainnya', gunakan input teks kustom)
            $businessType = null;
            if ($request->entity_type === 'badan_usaha') {
                if ($request->business_type === 'lainnya' && $request->filled('business_type_other')) {
                    $businessType = trim($request->business_type_other);
                } else {
                    $businessType = $request->business_type;
                }
            }

            if ($rejectedUser) {
                // ==========================================
                // ALUR PENGAJUAN ULANG (RE-APPLICATION)
                // ==========================================
                $company = $rejectedCompany
                    ?: ($rejectedUser->company 
                    ?: DeveloperCompany::where('user_id', $rejectedUser->id)->first()
                    ?: DeveloperCompany::where('email', $request->company_email)->first());

                $companyData = [
                    'pic_name'         => $request->pic_name,
                    'pic_nik'          => $request->pic_nik,
                    'pic_address'      => $request->pic_address,
                    'pic_phone'        => $request->pic_phone,
                    'entity_type'      => $request->entity_type,
                    'business_type'    => $businessType,
                    'company_name'     => $request->company_name,
                    'company_initials' => $request->company_initials ? strtoupper(trim($request->company_initials)) : null,
                    'nib'              => $request->nib,
                    'address'          => $request->company_address,
                    'phone'            => $request->company_phone,
                    'email'            => $request->company_email,
                    'website'          => $request->website,
                    'status'           => 'pending',
                    'rejection_reason' => null,
                ];

                if ($logoPath) {
                    $companyData['logo'] = $logoPath;
                }
                if ($signaturePath) {
                    $companyData['signature_image'] = $signaturePath;
                }

                if ($company) {
                    $company->update($companyData);
                } else {
                    $company = DeveloperCompany::create($companyData);
                }

                $userUpdateData = [
                    'name'            => $request->pic_name,
                    'nik'             => $request->pic_nik,
                    'email'           => $request->email,
                    'role'            => 'operator',
                    'phone'           => $request->pic_phone,
                    'address'         => $request->pic_address,
                    'company_id'      => $company->id,
                    'approval_status' => 'pending',
                ];

                if ($request->filled('password')) {
                    $userUpdateData['password'] = Hash::make($request->password);
                }

                $rejectedUser->update($userUpdateData);

                $company->update(['user_id' => $rejectedUser->id]);
                $user = $rejectedUser;

            } else {
                // ==========================================
                // ALUR PENDAFTARAN BARU
                // ==========================================
                // 1. Create Developer Company (status pending)
                $company = DeveloperCompany::create([
                    'pic_name'         => $request->pic_name,
                    'pic_nik'          => $request->pic_nik,
                    'pic_address'      => $request->pic_address,
                    'pic_phone'        => $request->pic_phone,
                    'entity_type'      => $request->entity_type,
                    'business_type'    => $businessType,
                    'company_name'     => $request->company_name,
                    'company_initials' => $request->company_initials ? strtoupper(trim($request->company_initials)) : null,
                    'nib'              => $request->nib,
                    'address'          => $request->company_address,
                    'phone'            => $request->company_phone,
                    'email'            => $request->company_email,
                    'website'          => $request->website,
                    'logo'             => $logoPath,
                    'signature_image'  => $signaturePath,
                    'status'           => 'pending',
                ]);

                // 2. Create User account with role 'operator' and approval_status 'pending'
                $user = User::create([
                    'name'            => $request->pic_name,
                    'nik'             => $request->pic_nik,
                    'email'           => $request->email,
                    'password'        => Hash::make($request->password),
                    'role'            => 'operator',
                    'phone'           => $request->pic_phone,
                    'address'         => $request->pic_address,
                    'company_id'      => $company->id,
                    'approval_status' => 'pending',
                ]);

                // Link user back to company
                $company->update(['user_id' => $user->id]);
            }

            DB::commit();

            return view('auth.register-developer-success', compact('company', 'user'));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat pendaftaran: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Check status of developer registration
     */
    public function checkStatus(Request $request)
    {
        $identifier = trim($request->get('identifier', $request->get('email', '')));
        $developer = null;
        $searched = false;

        if (!empty($identifier)) {
            $searched = true;
            $developer = DeveloperCompany::with(['user', 'approvedBy'])
                ->where(function ($query) use ($identifier) {
                    $query->where('email', $identifier)
                        ->orWhere('pic_nik', $identifier)
                        ->orWhere('nib', $identifier)
                        ->orWhereHas('user', function ($q) use ($identifier) {
                            $q->where('email', $identifier);
                        });
                })
                ->latest()
                ->first();
        }

        return view('auth.developer-status', compact('developer', 'identifier', 'searched'));
    }
}
