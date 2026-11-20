@extends('layouts.app')

@section('title', 'Upload Berkas Persyaratan KPR')

@section('content')
@php
    $actionUrl = isset($unitSelection) ? route('kpr.store', $unitSelection->id) : route('kpr.store-from-nup', $nupRegistration->id);
    $backUrl = isset($unitSelection) ? route('units.show', $unitSelection->id) : route('nup.show', $nupRegistration->id);
    $activeStatus = $autoEmploymentStatus ?? 'employee';
@endphp

<div class="max-w-3xl mx-auto">
    <div class="mb-5">
        <a href="{{ $backUrl }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-blue-600 hover:text-blue-800 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Kembali ke {{ isset($unitSelection) ? 'Detail Pemilihan Unit' : 'Detail NUP' }}</span>
        </a>
    </div>

    <!-- Header Box -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div class="flex-1">
                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200 mb-1">
                    Tahap Persyaratan KPR Bank
                </span>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900">Upload Berkas Persyaratan KPR</h1>
                <p class="text-xs text-slate-500 mt-1">
                    Unggah berkas kelayakan penghasilan Anda untuk proses verifikasi skor kredit (Pefindo / Bank). Pembayaran UTK baru dibuka setelah hasil evaluasi dinyatakan <strong>LAYAK</strong>.
                </p>
            </div>
        </div>

        <!-- Info Unit & Profil Pekerjaan dari NUP -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-5 pt-5 border-t border-slate-100 text-xs">
            @if(isset($unitSelection))
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80">
                    <span class="text-slate-400 block text-[11px]">Unit Terpilih:</span>
                    <strong class="text-slate-800 text-sm block">{{ $unitSelection->housingUnit->full_identifier }}</strong>
                    <span class="text-blue-600 font-bold block mt-0.5">Rp {{ number_format($unitSelection->housingUnit->price, 0, ',', '.') }}</span>
                </div>
            @elseif($preferredProject)
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80">
                    <span class="text-slate-400 block text-[11px]">Perumahan yang Diminati:</span>
                    <strong class="text-slate-800 text-sm block">{{ $preferredProject->name }}</strong>
                    <span class="text-slate-500 block mt-0.5">{{ $preferredProject->location ?? 'Kawasan Perumahan' }}</span>
                </div>
            @endif

            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80">
                <span class="text-slate-400 block text-[11px]">Pekerjaan Anda (Sesuai Data NUP):</span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-800 border border-indigo-200 mt-1">
                    <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>{{ $autoOccupationLabel }}</span>
                </span>
                @if(isset($nupRegistration) && $nupRegistration->monthly_income > 0)
                    <span class="text-slate-500 block text-[11px] mt-1.5">Penghasilan: <strong>Rp {{ number_format($nupRegistration->monthly_income, 0, ',', '.') }} / bulan</strong></span>
                @endif
            </div>
        </div>

        @if(isset($errors) && $errors->any())
            <div class="bg-red-50 border border-red-200 rounded-xl p-4 mt-4">
                <p class="text-xs font-bold text-red-800 mb-1">Mohon perbaiki data berikut:</p>
                <ul class="text-xs text-red-700 list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @session('success')
            <div class="bg-green-50 border border-green-200 rounded-xl p-4 mt-4">
                <p class="text-xs text-green-700 font-semibold">{{ session('success') }}</p>
            </div>
        @endsession

        @session('error')
            <div class="bg-red-50 border border-red-200 rounded-xl p-4 mt-4">
                <p class="text-xs text-red-700 font-semibold">{{ session('error') }}</p>
            </div>
        @endsession
    </div>

    <!-- Form Upload Berkas -->
    <form action="{{ $actionUrl }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
        @csrf

        {{-- Hidden fields agar tersimpan otomatis dari data NUP & Unit tanpa membebani pembeli --}}
        <input type="hidden" name="employment_status" value="{{ $activeStatus }}">
        <input type="hidden" name="bank" value="btn">
        <input type="hidden" name="loan_amount" value="{{ $defaultPlafon ?? 0 }}">
        <input type="hidden" name="tenor_years" value="15">
        <input type="hidden" name="monthly_income" value="{{ $nupRegistration->monthly_income ?? 0 }}">
        <input type="hidden" name="monthly_expense" value="{{ $nupRegistration->active_installment ?? 0 }}">
        <input type="hidden" name="employer_name" value="{{ $nupRegistration->full_name ?? Auth::user()->name }}">
        <input type="hidden" name="employment_duration_years" value="3">
        @if(!empty($autoOccupationOther))
            <input type="hidden" name="occupation_other" value="{{ $autoOccupationOther }}">
        @endif

        <h3 class="font-bold text-slate-900 text-base mb-1">Berkas Persyaratan yang Wajib Diunggah</h3>
        <p class="text-xs text-slate-500 mb-5">
            Daftar dokumen di bawah telah disesuaikan secara otomatis dengan kategori pekerjaan Anda:
        </p>

        @if($activeStatus === 'employee')
            <!-- BERKAS KHUSUS ASN / KARYAWAN TETAP / KARYAWAN KONTRAK -->
            <div class="border-2 border-indigo-200 bg-indigo-50/30 rounded-2xl p-5 mb-6 space-y-4 shadow-xs">
                <div class="flex items-center gap-2 pb-3 border-b border-indigo-100">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-900 text-sm">Persyaratan Berkas ASN / Karyawan</h4>
                        <p class="text-[11px] text-slate-500">Unggah berkas pembuktian masa kerja dan penghasilan:</p>
                    </div>
                </div>

                <div>
                    <label class="block text-slate-700 font-bold mb-1.5 text-xs">
                        1. Upload SK Karyawan / SK Pengangkatan / Kontrak Kerja <span class="text-red-500">*</span>
                    </label>
                    <input type="file" name="sk_karyawan_file" class="w-full border border-slate-300 rounded-xl p-2.5 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-blue-500" accept=".pdf,.jpg,.jpeg,.png" required>
                    <p class="text-[11px] text-slate-400 mt-1">Surat Keputusan (SK) Pengangkatan atau Surat Kontrak Kerja resmi (PDF/JPG/PNG, Maks. 10MB).</p>
                </div>

                <div>
                    <label class="block text-slate-700 font-bold mb-1.5 text-xs">
                        2. Upload Slip Gaji 3 - 6 Bulan Terakhir <span class="text-red-500">*</span>
                    </label>
                    <input type="file" name="salary_slip_file" class="w-full border border-slate-300 rounded-xl p-2.5 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-blue-500" accept=".pdf,.jpg,.jpeg,.png" required>
                    <p class="text-[11px] text-slate-400 mt-1">Slip gaji resmi dari tempat Anda bekerja (bisa digabung jadi 1 file PDF, Maks. 10MB).</p>
                </div>

                <div>
                    <label class="block text-slate-700 font-bold mb-1.5 text-xs">
                        3. Upload Rekening Koran / Buku Tabungan Gaji (3 - 6 Bulan Terakhir) <span class="text-red-500">*</span>
                    </label>
                    <input type="file" name="bank_statement_file" class="w-full border border-slate-300 rounded-xl p-2.5 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-blue-500" accept=".pdf,.jpg,.jpeg,.png" required>
                    <p class="text-[11px] text-slate-400 mt-1">Rekening koran tabungan gaji / payroll yang mencerminkan mutasi pendapatan bulanan (Maks. 10MB).</p>
                </div>
            </div>

        @elseif(in_array($activeStatus, ['entrepreneur', 'professional'], true))
            <!-- BERKAS KHUSUS WIRASWASTA / USAHA / UMKM / PROFESIONAL -->
            <div class="border-2 border-amber-200 bg-amber-50/30 rounded-2xl p-5 mb-6 space-y-4 shadow-xs">
                <div class="flex items-center gap-2 pb-3 border-b border-amber-100">
                    <div class="w-8 h-8 rounded-lg bg-amber-600 text-white flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-900 text-sm">Persyaratan Berkas Wiraswasta / Usaha / UMKM</h4>
                        <p class="text-[11px] text-slate-500">Unggah berkas pembuktian legalitas & finansial usaha Anda:</p>
                    </div>
                </div>

                <div>
                    <label class="block text-slate-700 font-bold mb-1.5 text-xs">
                        1. Upload Surat Keterangan Usaha (SKU) / NIB OSS <span class="text-red-500">*</span>
                    </label>
                    <input type="file" name="sku_file" class="w-full border border-slate-300 rounded-xl p-2.5 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-blue-500" accept=".pdf,.jpg,.jpeg,.png" required>
                    <p class="text-[11px] text-slate-400 mt-1">Surat Keterangan Usaha dari Kelurahan / Desa atau Nomor Induk Berusaha (NIB) OSS (Maks. 10MB).</p>
                </div>

                <div>
                    <label class="block text-slate-700 font-bold mb-1.5 text-xs">
                        2. Upload Laporan Keuangan / Pembukuan Usaha (3 - 6 Bulan Terakhir) <span class="text-red-500">*</span>
                    </label>
                    <input type="file" name="financial_statement_file" class="w-full border border-slate-300 rounded-xl p-2.5 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-blue-500" accept=".pdf,.jpg,.jpeg,.png" required>
                    <p class="text-[11px] text-slate-400 mt-1">Catatan pembukuan sederhana, rekap omzet, atau laporan laba rugi usaha (Maks. 10MB).</p>
                </div>

                <div>
                    <label class="block text-slate-700 font-bold mb-1.5 text-xs">
                        3. Upload Rekening Koran Operasional Usaha (3 - 6 Bulan Terakhir) <span class="text-red-500">*</span>
                    </label>
                    <input type="file" name="bank_statement_file" class="w-full border border-slate-300 rounded-xl p-2.5 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-blue-500" accept=".pdf,.jpg,.jpeg,.png" required>
                    <p class="text-[11px] text-slate-400 mt-1">Mutasi rekening operasional usaha / rekening tabungan transaksi 3 - 6 bulan terakhir (Maks. 10MB).</p>
                </div>
            </div>

        @else
            <!-- BERKAS KHUSUS LAINNYA / PEKERJAAN MANDIRI -->
            <div class="border-2 border-purple-200 bg-purple-50/30 rounded-2xl p-5 mb-6 space-y-4 shadow-xs">
                <div class="flex items-center gap-2 pb-3 border-b border-purple-100">
                    <div class="w-8 h-8 rounded-lg bg-purple-600 text-white flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-900 text-sm">Persyaratan Berkas Pekerjaan Bebas / Mandiri</h4>
                        <p class="text-[11px] text-slate-500">Unggah berkas bukti aktivitas kerja dan mutasi pendapatan:</p>
                    </div>
                </div>

                <div>
                    <label class="block text-slate-700 font-bold mb-1.5 text-xs">
                        1. Upload Rekening Koran Tabungan (3 - 6 Bulan Terakhir) <span class="text-red-500">*</span>
                    </label>
                    <input type="file" name="bank_statement_file" class="w-full border border-slate-300 rounded-xl p-2.5 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-blue-500" accept=".pdf,.jpg,.jpeg,.png" required>
                    <p class="text-[11px] text-slate-400 mt-1">Rekening koran tabungan aktif yang mencerminkan arus pemasukan Anda (Maks. 10MB).</p>
                </div>

                <div>
                    <label class="block text-slate-700 font-bold mb-1.5 text-xs">
                        2. Upload Bukti Pekerjaan / Portofolio / Bukti Penghasilan <span class="text-red-500">*</span>
                    </label>
                    <input type="file" name="additional_income_file" class="w-full border border-slate-300 rounded-xl p-2.5 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-blue-500" accept=".pdf,.jpg,.jpeg,.png" required>
                    <p class="text-[11px] text-slate-400 mt-1">Surat keterangan kerja, kontrak kerja freelance, atau rekap invoice penghasilan (Maks. 10MB).</p>
                </div>
            </div>
        @endif

        <!-- Catatan Dokumen Identitas Sudah Tersinkronisasi -->
        <div class="p-3.5 mb-6 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700 flex items-center gap-2.5">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>Dokumen identitas Anda (<strong>E-KTP, Kartu Keluarga, NPWP & Dokumen Pernikahan</strong>) sudah otomatis disinkronkan dari pendaftaran NUP, Anda tidak perlu mengunggahnya lagi.</span>
        </div>

        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 px-6 rounded-xl shadow-md transition text-sm">
            <span>Kirim Berkas Persyaratan KPR &rarr;</span>
        </button>
    </form>
</div>
@endsection
