<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($reapplyDeveloper) && $reapplyDeveloper ? 'Pengajuan Ulang Pendaftaran Mitra Pengembang - APRO' : 'Pendaftaran Mitra Pengembang (Operator) - APRO' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen py-8 sm:py-12 px-4 sm:px-6 text-slate-800">
    @php
        $isReapply = isset($reapplyDeveloper) && $reapplyDeveloper;

        $picName = old('pic_name', $reapplyDeveloper?->pic_name ?? $reapplyDeveloper?->user?->name ?? '');
        $picNik = old('pic_nik', $reapplyDeveloper?->pic_nik ?? $reapplyDeveloper?->user?->nik ?? '');
        $picAddress = old('pic_address', $reapplyDeveloper?->pic_address ?? $reapplyDeveloper?->user?->address ?? '');
        $picPhone = old('pic_phone', $reapplyDeveloper?->pic_phone ?? $reapplyDeveloper?->user?->phone ?? '');
        $accountEmail = old('email', $reapplyDeveloper?->user?->email ?? $reapplyDeveloper?->email ?? '');

        $entityType = old('entity_type', $reapplyDeveloper?->entity_type ?? 'badan_usaha');
        
        $savedBt = $reapplyDeveloper?->business_type;
        $isOtherBt = $savedBt && !in_array($savedBt, ['pt', 'cv']);
        $businessType = old('business_type', $isOtherBt ? 'lainnya' : ($savedBt ?? 'pt'));
        $businessTypeOther = old('business_type_other', $isOtherBt ? $savedBt : '');

        $companyName = old('company_name', $reapplyDeveloper?->company_name ?? '');
        $nib = old('nib', $reapplyDeveloper?->nib ?? '');
        $companyAddress = old('company_address', $reapplyDeveloper?->address ?? '');
        $companyPhone = old('company_phone', $reapplyDeveloper?->phone ?? '');
        $companyEmail = old('company_email', $reapplyDeveloper?->email ?? '');
        $website = old('website', $reapplyDeveloper?->website ?? '');
        $companyInitials = old('company_initials', $reapplyDeveloper?->company_initials ?? '');
    @endphp

    <div class="max-w-3xl mx-auto">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-white rounded-2xl shadow-sm border border-slate-200/80 p-2 mx-auto mb-3 flex items-center justify-center">
                <img src="{{ asset('images/logo.png') }}" alt="APRO Logo" class="w-full h-full object-contain">
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">
                {{ $isReapply ? 'Pengajuan Ulang Pendaftaran Pengembang' : 'Pendaftaran Pengembang Perumahan' }}
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-xl mx-auto">
                {{ $isReapply 
                    ? 'Perbaiki data atau berkas Anda yang belum sesuai dan kirimkan kembali permohonan pendaftaran.' 
                    : 'Daftarkan perusahaan Anda untuk menjadi mitra developer dan mengelola perumahan secara mandiri.' }}
            </p>
            <div class="mt-3">
                <a href="{{ route('developer.status') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 border border-blue-200/60 px-3.5 py-1.5 rounded-full transition shadow-sm">
                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Sudah pernah mendaftar? Cek Status Pendaftaran di Sini</span>
                </a>
            </div>
        </div>

        @if($isReapply)
            <!-- Banner Khusus Pengajuan Ulang -->
            <div class="mb-6 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-2xl p-5 shadow-sm">
                <div class="flex items-start gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800 border border-blue-200">Mode Pengajuan Ulang</span>
                            <span class="text-xs text-slate-500 font-medium">ID Pengembang: #{{ $reapplyDeveloper->id }}</span>
                        </div>
                        <h3 class="text-base font-bold text-slate-900">Perbaiki & Ajukan Kembali Pendaftaran</h3>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                            Seluruh data yang Anda daftarkan sebelumnya telah kami muat secara otomatis di bawah. Anda cukup mengoreksi bagian yang keliru atau melengkapi data sesuai catatan penolakan dari Super Admin berikut:
                        </p>

                        <div class="mt-3 p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-xs">
                            <div class="flex items-center gap-1.5 font-bold text-rose-800 mb-1">
                                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <span>Catatan Alasan Penolakan dari Super Admin:</span>
                            </div>
                            <div class="font-mono text-xs bg-white p-2.5 rounded-lg border border-rose-200 text-rose-900 whitespace-pre-wrap font-medium">
                                {{ $reapplyDeveloper->rejection_reason ?? 'Data atau berkas legalitas belum memenuhi ketentuan.' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 mb-6 shadow-sm">
                <div class="flex items-center gap-2 text-rose-800 font-semibold mb-1 text-sm">
                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Mohon periksa kembali formulir pendaftaran:</span>
                </div>
                <ul class="list-disc pl-5 text-xs text-rose-700 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-rose-50 border border-rose-200 text-rose-800 text-sm rounded-2xl p-4 mb-6 shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Formulir Pendaftaran Satu Kartu Terpadu -->
        <form action="{{ route('developer.register.post') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl sm:rounded-3xl shadow-xl border border-slate-200/80 overflow-hidden">
            @csrf

            @if($isReapply)
                <input type="hidden" name="reapply_company_id" value="{{ $reapplyDeveloper->id }}">
            @endif

            <!-- ================= BAGIAN 1: DATA PIC / AKUN OPERATOR ================= -->
            <div class="p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-6">
                    <span class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 border border-blue-200/60 font-bold flex items-center justify-center text-sm shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </span>
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900">1. Data Penanggung Jawab (Akun Operator)</h2>
                        <p class="text-xs text-slate-500">Informasi personal PIC yang akan mengoperasikan akun pengembang.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap Penanggung Jawab *</label>
                        <input type="text" name="pic_name" value="{{ $picName }}" class="w-full border border-slate-300 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition" required placeholder="Contoh: Budi Santoso (Sesuai KTP)">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">NIK Penanggung Jawab (16 Digit) *</label>
                        <input type="text" name="pic_nik" value="{{ $picNik }}" maxlength="16" pattern="[0-9]{16}" class="w-full border border-slate-300 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition" required placeholder="16 digit NIK KTP PIC" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Penanggung Jawab *</label>
                        <textarea name="pic_address" rows="2" class="w-full border border-slate-300 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition" required placeholder="Alamat domisili lengkap PIC...">{{ $picAddress }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">No. WhatsApp *</label>
                        <input type="text" name="pic_phone" value="{{ $picPhone }}" class="w-full border border-slate-300 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition" required placeholder="Contoh: 081234567890">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Email (Untuk Login Akun) *</label>
                        <input type="email" name="email" value="{{ $accountEmail }}" class="w-full border border-slate-300 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition" required placeholder="operator@perusahaan.com">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Password Akun
                            @if($isReapply)
                                <span class="text-slate-400 font-normal">(Kosongkan jika tidak diubah)</span>
                            @else
                                *
                            @endif
                        </label>
                        <div class="relative">
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   class="w-full border border-slate-300 rounded-xl p-2.5 pr-10 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition" 
                                   {{ $isReapply ? '' : 'required' }} 
                                   placeholder="{{ $isReapply ? 'Kosongkan jika password tidak diubah' : 'Minimal 6 karakter' }}">
                            <button type="button" id="togglePassword" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 focus:outline-none p-1 rounded transition" title="Lihat/Sembunyikan Password">
                                <svg class="w-4 h-4 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg class="w-4 h-4 eye-closed hidden text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                        <span class="text-[11px] text-slate-500 block mt-1">Minimal 6 karakter. Boleh spasi antar kata (spasi di awal/akhir otomatis dibersihkan).</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Konfirmasi Password
                            @if($isReapply)
                                <span class="text-slate-400 font-normal">(Kosongkan jika tidak diubah)</span>
                            @else
                                *
                            @endif
                        </label>
                        <div class="relative">
                            <input type="password" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   class="w-full border border-slate-300 rounded-xl p-2.5 pr-10 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition" 
                                   {{ $isReapply ? '' : 'required' }} 
                                   placeholder="{{ $isReapply ? 'Kosongkan jika password tidak diubah' : 'Ulangi password' }}">
                            <button type="button" id="togglePasswordConfirm" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 focus:outline-none p-1 rounded transition" title="Lihat/Sembunyikan Password">
                                <svg class="w-4 h-4 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg class="w-4 h-4 eye-closed hidden text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Divider Antar Sesi -->
            <div class="border-t border-slate-200"></div>

            <!-- ================= BAGIAN 2: PROFIL & LEGALITAS PERUSAHAAN ================= -->
            <div class="p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-6">
                    <span class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 border border-indigo-200/60 font-bold flex items-center justify-center text-sm shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </span>
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900">2. Profil & Legalitas Perusahaan</h2>
                        <p class="text-xs text-slate-500">Informasi badan usaha atau badan pengembang perumahan.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Bentuk Usaha (Badan Usaha / Perorangan) -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-2">Bentuk Usaha *</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="flex items-center gap-3 p-3.5 border-2 rounded-xl cursor-pointer hover:bg-slate-50 transition border-slate-200 has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/50">
                                <input type="radio" name="entity_type" value="badan_usaha" {{ $entityType === 'badan_usaha' ? 'checked' : '' }} onchange="toggleBusinessType(this.value)" class="w-4 h-4 text-blue-600 focus:ring-blue-500">
                                <div>
                                    <p class="font-semibold text-sm text-slate-800">Badan Usaha</p>
                                    <p class="text-xs text-slate-500">PT, CV, Koperasi, Firma, dll.</p>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-3.5 border-2 rounded-xl cursor-pointer hover:bg-slate-50 transition border-slate-200 has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/50">
                                <input type="radio" name="entity_type" value="perorangan" {{ $entityType === 'perorangan' ? 'checked' : '' }} onchange="toggleBusinessType(this.value)" class="w-4 h-4 text-blue-600 focus:ring-blue-500">
                                <div>
                                    <p class="font-semibold text-sm text-slate-800">Perorangan</p>
                                    <p class="text-xs text-slate-500">Developer Mandiri / Individu</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Jenis Badan Usaha (PT / CV / Lainnya) -->
                    <div id="businessTypeWrapper">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis Badan Usaha *</label>
                        <select name="business_type" id="business_type" onchange="checkBusinessTypeOther()" class="w-full border border-slate-300 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                            <option value="pt" {{ $businessType === 'pt' ? 'selected' : '' }}>PT (Perseroan Terbatas)</option>
                            <option value="cv" {{ $businessType === 'cv' ? 'selected' : '' }}>CV (Persekutuan Komanditer)</option>
                            <option value="lainnya" {{ $businessType === 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>

                        <!-- Input Spesifik jika memilih Lainnya -->
                        <div id="businessTypeOtherWrapper" class="{{ $businessType === 'lainnya' ? '' : 'hidden' }} mt-2">
                            <label class="block text-xs font-semibold text-blue-700 mb-1">Sebutkan Jenis Badan Usaha Lainnya *</label>
                            <input type="text" name="business_type_other" id="business_type_other" value="{{ $businessTypeOther }}" class="w-full border border-blue-300 bg-blue-50/50 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Contoh: Koperasi, Yayasan, BUMDes">
                        </div>
                    </div>

                    <!-- Nama Badan Usaha / Brand -->
                    <div id="companyNameWrapper">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Badan Usaha / Nama Usaha *</label>
                        <input type="text" name="company_name" value="{{ $companyName }}" class="w-full border border-slate-300 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition" required placeholder="Contoh: PT Praja Propertindo">
                    </div>

                    <!-- NIB -->
                    <div class="sm:col-span-2" id="nibWrapper">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Induk Berusaha (NIB) <span class="text-slate-400 font-normal">(Opsional)</span></label>
                        <input type="text" name="nib" value="{{ $nib }}" class="w-full border border-slate-300 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition" placeholder="Nomor NIB perusahaan jika ada">
                    </div>

                    <!-- Alamat Kantor -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Kantor / Operasional Usaha *</label>
                        <textarea name="company_address" rows="2" class="w-full border border-slate-300 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition" required placeholder="Alamat lengkap kantor pusat atau kantor pemasaran...">{{ $companyAddress }}</textarea>
                    </div>

                    <!-- Telepon & Email Kantor -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Telepon Kantor / Usaha *</label>
                        <input type="text" name="company_phone" value="{{ $companyPhone }}" class="w-full border border-slate-300 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition" required placeholder="Contoh: 031-1234567 atau 08123456789">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Email Perusahaan / Usaha *</label>
                        <input type="email" name="company_email" value="{{ $companyEmail }}" class="w-full border border-slate-300 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition" required placeholder="info@perusahaan.com">
                    </div>

                    <!-- Website & Logo -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Website Perusahaan <span class="text-slate-400 font-normal">(Opsional)</span></label>
                        <input type="text" name="website" value="{{ $website }}" class="w-full border border-slate-300 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition" placeholder="https://perusahaan.com">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Logo Perusahaan <span class="text-slate-400 font-normal">(Opsional, maks 3MB)</span></label>
                        <input type="file" name="logo" accept="image/*" class="w-full border border-slate-300 rounded-xl p-2 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none bg-slate-50/50">
                        @if($isReapply && $reapplyDeveloper?->logo)
                            <div class="mt-2 flex items-center gap-2 p-2 bg-emerald-50 border border-emerald-200 rounded-xl">
                                <img src="{{ asset('storage/' . $reapplyDeveloper->logo) }}" alt="Logo Saat Ini" class="w-8 h-8 object-contain rounded-lg border bg-white p-0.5">
                                <div class="text-[11px] text-slate-600">
                                    <span class="font-semibold text-emerald-700 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        Logo sebelumnya telah tersimpan
                                    </span>
                                    <p class="text-[10px] text-slate-400">Unggah file baru hanya jika ingin menggantinya.</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Inisial PT & Tanda Tangan Remove BG -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Inisial / Kode Singkat Perusahaan <span class="text-slate-400 font-normal">(Untuk No. Dokumen SPR)</span></label>
                        <input type="text" name="company_initials" value="{{ $companyInitials }}" maxlength="10" class="w-full border border-slate-300 rounded-xl p-2.5 text-sm uppercase focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition" placeholder="Contoh: PT, APL, KGP">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanda Tangan Developer (PNG Transparan) <span class="text-slate-400 font-normal">(Opsional)</span></label>
                        <input type="file" name="signature_image" accept="image/png,image/webp" class="w-full border border-slate-300 rounded-xl p-2 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none bg-slate-50/50">
                        <span class="text-[11px] text-slate-500 block mt-1">Format PNG transparan untuk dibubuhkan berdampingan dengan e-Materai pada SPR.</span>
                        @if($isReapply && $reapplyDeveloper?->signature_image)
                            <div class="mt-2 flex items-center gap-2 p-2 bg-emerald-50 border border-emerald-200 rounded-xl">
                                <img src="{{ asset('storage/' . $reapplyDeveloper->signature_image) }}" alt="Tanda Tangan Saat Ini" class="w-8 h-8 object-contain rounded-lg border bg-white p-0.5">
                                <div class="text-[11px] text-slate-600">
                                    <span class="font-semibold text-emerald-700 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        Tanda tangan sebelumnya telah tersimpan
                                    </span>
                                    <p class="text-[10px] text-slate-400">Unggah file baru hanya jika ingin menggantinya.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Info Box Alur Verifikasi -->
                <div class="mt-6 p-4 bg-blue-50/70 border border-blue-200/80 rounded-2xl text-blue-900 text-xs flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <p class="font-bold text-blue-950 mb-0.5">Alur Persetujuan & Verifikasi Akun:</p>
                        <p class="text-blue-800 leading-relaxed">Setelah data dikirim, Super Admin akan meninjau dan memverifikasi data perusahaan Anda. Anda akan dapat masuk (login) menginput dan mengelola perumahan secara penuh setelah permohonan disetujui.</p>
                    </div>
                </div>

                <!-- Tombol Submit & Navigasi -->
                <div class="mt-8 flex flex-col sm:flex-row items-center gap-3">
                    <button type="submit" class="w-full sm:flex-1 py-3.5 px-6 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transition text-sm flex items-center justify-center gap-2">
                        @if($isReapply)
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Simpan Perubahan & Kirim Ulang Permohonan</span>
                        @else
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            <span>Kirim Pendaftaran Pengembang</span>
                        @endif
                    </button>
                    <a href="{{ route('login') }}" class="w-full sm:w-auto py-3.5 px-6 text-center text-slate-600 hover:text-slate-900 text-sm font-semibold hover:bg-slate-50 rounded-xl transition">
                        Sudah punya akun? Masuk
                    </a>
                </div>
            </div>
        </form>

        <p class="text-center text-xs text-slate-400 mt-6">
            &copy; {{ date('Y') }} APRO System. Seluruh hak cipta dilindungi.
        </p>
    </div>

    <script>
        function checkBusinessTypeOther() {
            const select = document.getElementById('business_type');
            const otherWrapper = document.getElementById('businessTypeOtherWrapper');
            const otherInput = document.getElementById('business_type_other');
            if (select && otherWrapper && otherInput) {
                if (select.value === 'lainnya' && !select.disabled) {
                    otherWrapper.classList.remove('hidden');
                    otherInput.required = true;
                    otherInput.focus();
                } else {
                    otherWrapper.classList.add('hidden');
                    otherInput.required = false;
                }
            }
        }

        function toggleBusinessType(val) {
            const wrapper = document.getElementById('businessTypeWrapper');
            const otherWrapper = document.getElementById('businessTypeOtherWrapper');
            const select = document.getElementById('business_type');
            const otherInput = document.getElementById('business_type_other');

            if (val === 'perorangan') {
                wrapper.classList.add('opacity-50');
                if (otherWrapper) otherWrapper.classList.add('hidden');
                select.disabled = true;
                if (otherInput) {
                    otherInput.disabled = true;
                    otherInput.required = false;
                }
            } else {
                wrapper.classList.remove('opacity-50');
                select.disabled = false;
                if (otherInput) otherInput.disabled = false;
                checkBusinessTypeOther();
            }
        }

        // Aturan Spasi Password:
        // 1. Spasi di awal dilarang / dicegat
        // 2. Spasi di tengah diizinkan
        // 3. Spasi di akhir otomatis dipotong (.trim()) saat blur atau submit
        function setupPasswordSanitizer(inputId) {
            const input = document.getElementById(inputId);
            if (!input) return;

            input.addEventListener('keydown', function (e) {
                if ((e.key === ' ' || e.code === 'Space' || e.keyCode === 32) && (this.selectionStart === 0 || this.value.length === 0)) {
                    e.preventDefault();
                }
            });

            input.addEventListener('input', function () {
                if (this.value.startsWith(' ')) {
                    this.value = this.value.replace(/^\s+/, '');
                }
            });

            input.addEventListener('blur', function () {
                this.value = this.value.trim();
            });
        }

        // Toggle Show / Hide Password
        function setupPasswordToggle(buttonId, inputId) {
            const btn = document.getElementById(buttonId);
            const input = document.getElementById(inputId);
            if (btn && input) {
                btn.addEventListener('click', function () {
                    const isPassword = input.getAttribute('type') === 'password';
                    input.setAttribute('type', isPassword ? 'text' : 'password');
                    const eyeOpen = btn.querySelector('.eye-open');
                    const eyeClosed = btn.querySelector('.eye-closed');
                    if (eyeOpen && eyeClosed) {
                        eyeOpen.classList.toggle('hidden', isPassword);
                        eyeClosed.classList.toggle('hidden', !isPassword);
                    }
                });
            }
        }

        // Initialize on load
        document.addEventListener('DOMContentLoaded', () => {
            const checked = document.querySelector('input[name="entity_type"]:checked');
            if (checked) {
                toggleBusinessType(checked.value);
            }
            checkBusinessTypeOther();

            setupPasswordToggle('togglePassword', 'password');
            setupPasswordToggle('togglePasswordConfirm', 'password_confirmation');
            setupPasswordSanitizer('password');
            setupPasswordSanitizer('password_confirmation');

            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', () => {
                    const pwd = document.getElementById('password');
                    const pwdConf = document.getElementById('password_confirmation');
                    if (pwd) pwd.value = pwd.value.trim();
                    if (pwdConf) pwdConf.value = pwdConf.value.trim();
                });
            }
        });
    </script>
</body>
</html>
