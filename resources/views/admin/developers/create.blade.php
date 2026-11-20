@extends('layouts.admin')

@section('title', 'Tambah Pengembang Baru')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('admin.developers.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-blue-600 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Kembali ke Daftar Pengembang
        </a>
    </div>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <svg class="w-6 h-6 text-blue-600 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                Tambah Pengembang Baru (Manual)
            </h1>
        <p class="text-sm text-gray-600 mt-1">Daftarkan mitra pengembang & buatkan akun operator secara langsung. Akun ini akan <strong>langsung aktif (approved)</strong>.</p>
    </div>

    @if(isset($errors) && $errors->any())
        <div class="bg-red-50 border border-red-200 rounded-2xl p-4 mb-6 shadow-sm">
            <div class="flex items-center gap-2 text-red-800 font-semibold mb-1 text-sm">
                <svg class="w-4 h-4 text-red-500 inline-block shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> Mohon periksa kembali formulir berikut:
            </div>
            <ul class="list-disc pl-5 text-xs text-red-700 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @session('error')
        <div class="bg-red-50 border border-red-200 text-red-800 text-sm rounded-2xl p-4 mb-6 shadow-sm">
            {{ session('error') }}
        </div>
    @endsession

    <form action="{{ route('admin.developers.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- Informasi Banner -->
        <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 flex items-start gap-3 text-blue-900 text-xs">
            <svg class="w-4 h-4 inline-block text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            <div>
                <p class="font-bold">Pembuatan Langsung oleh Super Admin</p>
                <p class="text-blue-700 mt-0.5">Pengembang yang dibuat melalui menu ini tidak perlu menunggu verifikasi lagi. Akun operator akan langsung berstatus <strong>Aktif</strong> dan bisa langsung login.</p>
            </div>
        </div>

        <!-- Bagian 1: Data Perusahaan / Pengembang -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-blue-600 text-white font-bold flex items-center justify-center text-sm">1</span>
                <div>
                    <h2 class="text-base font-bold text-gray-800">Data Badan Usaha / Pengembang</h2>
                    <p class="text-xs text-gray-500">Legalitas dan identitas usaha pengembang perumahan.</p>
                </div>
            </div>

            <div class="p-6 sm:p-8 space-y-5">
                <!-- Bentuk Entitas -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Bentuk Usaha <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-3 p-3.5 border rounded-xl cursor-pointer hover:bg-gray-50 transition border-gray-200">
                            <input type="radio" name="entity_type" value="badan_usaha" class="text-blue-600" {{ old('entity_type', 'badan_usaha') === 'badan_usaha' ? 'checked' : '' }} onchange="toggleBusinessType(true)">
                            <div>
                                <span class="text-sm font-bold text-gray-800 block">Badan Usaha</span>
                                <span class="text-[11px] text-gray-500">PT, CV, Koperasi, dll.</span>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-3.5 border rounded-xl cursor-pointer hover:bg-gray-50 transition border-gray-200">
                            <input type="radio" name="entity_type" value="perorangan" class="text-blue-600" {{ old('entity_type') === 'perorangan' ? 'checked' : '' }} onchange="toggleBusinessType(false)">
                            <div>
                                <span class="text-sm font-bold text-gray-800 block">Perorangan</span>
                                <span class="text-[11px] text-gray-500">Usaha atas nama pribadi</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Jenis Badan Usaha (PT/CV/Lainnya) -->
                <div id="business_type_container" class="{{ old('entity_type', 'badan_usaha') === 'perorangan' ? 'hidden' : '' }}">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Jenis Badan Usaha <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-3 gap-3">
                        <label class="flex items-center gap-2 p-2.5 border rounded-lg cursor-pointer hover:bg-gray-50 text-xs border-gray-200">
                            <input type="radio" name="business_type" value="pt" class="text-blue-600" {{ old('business_type', 'pt') === 'pt' ? 'checked' : '' }} onchange="toggleOtherBusiness(false)">
                            <span class="font-bold text-gray-800">PT (Perseroan Terbatas)</span>
                        </label>
                        <label class="flex items-center gap-2 p-2.5 border rounded-lg cursor-pointer hover:bg-gray-50 text-xs border-gray-200">
                            <input type="radio" name="business_type" value="cv" class="text-blue-600" {{ old('business_type') === 'cv' ? 'checked' : '' }} onchange="toggleOtherBusiness(false)">
                            <span class="font-bold text-gray-800">CV (Persekutuan Komanditer)</span>
                        </label>
                        <label class="flex items-center gap-2 p-2.5 border rounded-lg cursor-pointer hover:bg-gray-50 text-xs border-gray-200">
                            <input type="radio" name="business_type" value="lainnya" class="text-blue-600" {{ old('business_type') === 'lainnya' ? 'checked' : '' }} onchange="toggleOtherBusiness(true)">
                            <span class="font-bold text-gray-800">Lainnya</span>
                        </label>
                    </div>
                    <div id="business_type_other_container" class="mt-2 {{ old('business_type') === 'lainnya' ? '' : 'hidden' }}">
                        <input type="text" name="business_type_other" value="{{ old('business_type_other') }}" placeholder="Sebutkan jenis badan usaha (misal: UD / Koperasi)" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Nama Perusahaan / Usaha <span class="text-red-500">*</span></label>
                        <input type="text" name="company_name" value="{{ old('company_name') }}" required placeholder="Contoh: PT Graha Pratama Sejahtera" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Inisial / Singkatan Perusahaan</label>
                        <input type="text" name="company_initials" value="{{ old('company_initials') }}" maxlength="20" placeholder="Contoh: GPS atau PTPR (opsional)" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 text-sm uppercase">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">NIB (Nomor Induk Berusaha)</label>
                        <input type="text" name="nib" value="{{ old('nib') }}" placeholder="13 digit NIB OSS (opsional)" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Email Kantor <span class="text-red-500">*</span></label>
                        <input type="email" name="company_email" value="{{ old('company_email') }}" required placeholder="info@perusahaan.com" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">No. Telepon Kantor <span class="text-red-500">*</span></label>
                        <input type="text" name="company_phone" value="{{ old('company_phone') }}" required placeholder="031-1234567 atau 081234..." class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Alamat Kantor / Domisili Usaha <span class="text-red-500">*</span></label>
                    <textarea name="company_address" rows="2" required placeholder="Jl. Raya Utama No. 123, Kelurahan, Kecamatan, Kota/Kabupaten" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 text-sm">{{ old('company_address') }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Website (Opsional)</label>
                        <input type="url" name="website" value="{{ old('website') }}" placeholder="https://perusahaan.com" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Logo Perusahaan (Opsional)</label>
                        <input type="file" name="logo" accept="image/*" class="w-full text-xs text-gray-500 file:mr-2 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Stempel / TTD Digital (Opsional)</label>
                        <input type="file" name="signature_image" accept="image/png,image/webp" class="w-full text-xs text-gray-500 file:mr-2 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                </div>
            </div>
        </div>

        <!-- Bagian 2: Data PIC / Akun Login Operator -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-emerald-600 text-white font-bold flex items-center justify-center text-sm">2</span>
                <div>
                    <h2 class="text-base font-bold text-gray-800">Data Penanggung Jawab & Akun Login Operator</h2>
                    <p class="text-xs text-gray-500">Akun ini yang akan dipakai login oleh operator mitra developer.</p>
                </div>
            </div>

            <div class="p-6 sm:p-8 space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Nama Lengkap Penanggung Jawab (PIC) <span class="text-red-500">*</span></label>
                        <input type="text" name="pic_name" value="{{ old('pic_name') }}" required placeholder="Contoh: Budi Santoso" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">NIK Penanggung Jawab (16 Digit) <span class="text-red-500">*</span></label>
                        <input type="text" name="pic_nik" value="{{ old('pic_nik') }}" maxlength="16" pattern="\d{16}" required placeholder="Contoh: 3578012345670001" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 text-sm font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Nomor WhatsApp Aktif <span class="text-red-500">*</span></label>
                        <input type="text" name="pic_phone" value="{{ old('pic_phone') }}" required placeholder="Contoh: 081234567890" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 text-sm font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Alamat Domisili PIC <span class="text-red-500">*</span></label>
                        <input type="text" name="pic_address" value="{{ old('pic_address') }}" required placeholder="Alamat KTP / tempat tinggal PIC" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 text-sm">
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-4">
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Kredensial Login Akun Operator</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Email Login Operator <span class="text-red-500">*</span></label>
                            <input type="email" name="email" value="{{ old('email') }}" required placeholder="operator@perusahaan.com" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 text-sm font-mono">
                            <p class="text-[11px] text-gray-400 mt-1">Email ini akan digunakan oleh operator untuk login ke panel admin.</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Password Awal <span class="text-red-500">*</span></label>
                                <input type="password" name="password" required minlength="6" placeholder="Minimal 6 karakter" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Konfirmasi Password <span class="text-red-500">*</span></label>
                                <input type="password" name="password_confirmation" required minlength="6" placeholder="Ulangi password di atas" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 text-sm">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="flex items-center justify-end gap-3 pt-2 pb-12">
            <a href="{{ route('admin.developers.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl text-sm transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold rounded-xl shadow-md hover:shadow transition text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
                Simpan & Aktifkan Pengembang
            </button>
        </div>
    </form>
</div>

<script>
function toggleBusinessType(isCompany) {
    const container = document.getElementById('business_type_container');
    if (isCompany) {
        container.classList.remove('hidden');
    } else {
        container.classList.add('hidden');
    }
}

function toggleOtherBusiness(isOther) {
    const container = document.getElementById('business_type_other_container');
    if (isOther) {
        container.classList.remove('hidden');
    } else {
        container.classList.add('hidden');
    }
}
</script>
@endsection
