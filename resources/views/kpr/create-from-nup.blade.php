@extends('layouts.app')

@section('title', 'Screening KPR')

@section('content')
<div class="max-w-4xl mx-auto py-6 px-4">
    <div class="mb-6">
        <a href="{{ route('nup.show', $nupRegistration->id) }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-blue-600 hover:text-blue-800 transition">
            ← Kembali ke Detail NUP
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sm:p-8 mb-6">
        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">Formulir Pengajuan Screening KPR</h1>
        <p class="text-xs text-gray-500 mb-6 pb-4 border-b border-gray-100">
            Lengkapi rencana pengajuan pembiayaan KPR Anda. Data identitas dan pekerjaan Anda telah disinkronkan dari pendaftaran NUP.
        </p>

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl text-sm mb-6 flex items-center gap-2">
                <span><svg class="w-4 h-4 inline-block text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg></span> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl text-sm mb-6 flex items-center gap-2">
                <svg class="w-4 h-4 inline-block text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl text-xs mb-6">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-blue-50/70 border border-blue-200 rounded-xl p-4 mb-6 text-xs text-blue-900 grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <span class="text-blue-600 block">Nomor Registrasi NUP:</span>
                <span class="font-bold text-sm text-blue-950">{{ $nupRegistration->registration_number }}</span>
            </div>
            <div>
                <span class="text-blue-600 block">Nama Pemohon:</span>
                <span class="font-bold text-sm text-blue-950">{{ $nupRegistration->full_name }}</span>
            </div>
            <div>
                <span class="text-blue-600 block">Pilihan Perumahan:</span>
                <span class="font-bold text-sm text-blue-950">{{ $preferredProject->name ?? $nupRegistration->housingPreferences->first()?->housing_name ?? '-' }}</span>
            </div>
            <div>
                <span class="text-blue-600 block">Metode Pembayaran:</span>
                <span class="font-bold text-sm text-blue-950">{{ config('nup.payment_methods.' . $nupRegistration->payment_method, 'KPR Bank') }}</span>
            </div>
        </div>

        <form action="{{ route('kpr.store-from-nup', $nupRegistration->id) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- 1. KATEGORI PEKERJAAN PEMOHON (Untuk filter otomatis dokumen yang wajib diupload) -->
            <div class="mb-6">
                <label class="block text-xs font-bold text-gray-700 mb-1">Kategori Pekerjaan / Profesi Pemohon <span class="text-red-500">*</span></label>
                <select name="employment_status" id="employment_status" class="w-full border border-gray-300 rounded-xl p-3 text-sm font-semibold focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white" required onchange="toggleKprDocFields()">
                    <option value="employee" {{ old('employment_status', $autoEmploymentStatus) === 'employee' ? 'selected' : '' }}>
                        ASN / PPPK / Karyawan Tetap / Karyawan Kontrak
                    </option>
                    <option value="entrepreneur" {{ in_array(old('employment_status', $autoEmploymentStatus), ['entrepreneur', 'professional']) ? 'selected' : '' }}>
                        Bukan ASN / Wiraswasta / Pemilik Usaha / UMKM / Profesional
                    </option>
                    <option value="other" {{ old('employment_status', $autoEmploymentStatus) === 'other' ? 'selected' : '' }}>
                        Lainnya (Pekerjaan Bebas / Mandiri)
                    </option>
                </select>
                <p class="text-[11px] text-gray-500 mt-1 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
                    <span>Form upload berkas persyaratan KPR di bawah otomatis menyesuaikan dengan status pekerjaan yang Anda pilih.</span>
                </p>
            </div>

            <!-- Input Mandiri Jika Memilih 'Lainnya' -->
            <div id="kpr_other_container" class="mb-6 hidden bg-amber-50 border border-amber-200 rounded-xl p-3">
                <label class="block text-gray-800 font-bold mb-1 text-xs">Sebutkan Pekerjaan / Profesi Anda <span class="text-red-500">*</span></label>
                <input type="text" name="occupation_other" id="kpr_occupation_other" class="w-full border border-gray-300 rounded-lg p-2.5 text-xs bg-white focus:outline-none" placeholder="Contoh: Petani, Pengemudi Online, Seniman, dll." value="{{ old('occupation_other', $nupRegistration->occupation_other ?? '') }}">
            </div>

            <!-- 2. UPLOAD BERKAS PERSYARATAN KPR (DIPILIH DAN DIFILTER OTOMATIS) -->
            <h3 class="font-bold text-gray-900 text-sm mb-3">Upload Berkas Persyaratan Screening KPR</h3>

            <!-- 4A. Berkas Khusus ASN / Karyawan Kontrak -->
            <div id="kpr_docs_employee" class="border-l-4 border-indigo-500 bg-indigo-50/50 p-4 rounded-r-xl mb-6 shadow-sm">
                <div class="flex items-center gap-2 mb-3 pb-2 border-b border-indigo-200/70">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-indigo-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v1.894m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800 text-sm">Berkas Pemohon ASN / Karyawan Kontrak</h4>
                        <p class="text-[11px] text-gray-500">Unggah berkas kelayakan penghasilan resmi Anda:</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-gray-700 font-bold mb-1 text-xs">1. Upload SK Karyawan / SK Pengangkatan / Kontrak Kerja <span class="text-red-500">*</span></label>
                        <input type="file" name="sk_karyawan_file" class="w-full border border-gray-300 rounded-lg p-2 text-xs bg-white" accept=".pdf,.jpg,.jpeg,.png">
                        <p class="text-[11px] text-gray-500 mt-0.5">Surat Keputusan (SK) Pengangkatan ASN/Karyawan atau Surat Kontrak Kerja (PDF/Gambar, Maks. 10MB).</p>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-bold mb-1 text-xs">2. Upload Rekening Koran 6 Bulan Terakhir <span class="text-red-500">*</span></label>
                        <input type="file" name="bank_statement_employee" class="w-full border border-gray-300 rounded-lg p-2 text-xs bg-white" accept=".pdf,.jpg,.jpeg,.png">
                        <p class="text-[11px] text-gray-500 mt-0.5">Mutasi rekening koran tabungan gaji / payroll 6 bulan terakhir (PDF/Gambar, Maks. 10MB).</p>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-bold mb-1 text-xs">3. Upload Slip Gaji 6 Bulan Terakhir <span class="text-red-500">*</span></label>
                        <input type="file" name="salary_slip_file" class="w-full border border-gray-300 rounded-lg p-2 text-xs bg-white" accept=".pdf,.jpg,.jpeg,.png">
                        <p class="text-[11px] text-gray-500 mt-0.5">Slip gaji 6 bulan terakhir (bisa digabung jadi 1 file PDF, Maks. 10MB).</p>
                    </div>
                </div>
            </div>

            <!-- 4B. Berkas Khusus Wiraswasta / Pemilik Usaha / Non-ASN -->
            <div id="kpr_docs_entrepreneur" class="hidden border-l-4 border-amber-500 bg-amber-50/50 p-4 rounded-r-xl mb-6 shadow-sm">
                <div class="flex items-center gap-2 mb-3 pb-2 border-b border-amber-200/70">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.614A2.993 2.993 0 009 9.35c.66 0 1.28-.213 1.785-.576a3.004 3.004 0 004.43 0c.505.363 1.125.576 1.785.576a2.993 2.993 0 002.465-1.285 3 3 0 003.535.684V9.35z"/></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800 text-sm">Berkas Pemohon Wiraswasta / Usaha (Bukan ASN)</h4>
                        <p class="text-[11px] text-gray-500">Unggah berkas pembuktian legalitas & finansial usaha Anda:</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-gray-700 font-bold mb-1 text-xs">1. Upload Surat Keterangan Usaha (SKU) / NIB OSS <span class="text-red-500">*</span></label>
                        <input type="file" name="sku_file" class="w-full border border-gray-300 rounded-lg p-2 text-xs bg-white" accept=".pdf,.jpg,.jpeg,.png">
                        <p class="text-[11px] text-gray-500 mt-0.5">Surat Keterangan Usaha dari Kelurahan / Desa atau Nomor Induk Berusaha OSS (PDF/Gambar, Maks. 10MB).</p>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-bold mb-1 text-xs">2. Upload Laporan Keuangan 6 Bulan Terakhir <span class="text-red-500">*</span></label>
                        <input type="file" name="financial_statement_file" class="w-full border border-gray-300 rounded-lg p-2 text-xs bg-white" accept=".pdf,.jpg,.jpeg,.png">
                        <p class="text-[11px] text-gray-500 mt-0.5">Laporan pembukuan ringkas, catatan laba rugi, atau rekap omzet 6 bulan terakhir (PDF/Gambar, Maks. 10MB).</p>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-bold mb-1 text-xs">3. Upload Rekening Koran 6 Bulan Terakhir <span class="text-red-500">*</span></label>
                        <input type="file" name="bank_statement_entrepreneur" class="w-full border border-gray-300 rounded-lg p-2 text-xs bg-white" accept=".pdf,.jpg,.jpeg,.png">
                        <p class="text-[11px] text-gray-500 mt-0.5">Mutasi rekening operasional usaha / rekening tabungan 6 bulan terakhir (PDF/Gambar, Maks. 10MB).</p>
                    </div>
                </div>
            </div>

            <!-- 4C. Berkas Khusus Profesi Lainnya -->
            <div id="kpr_docs_other" class="hidden border-l-4 border-purple-500 bg-purple-50/50 p-4 rounded-r-xl mb-6 shadow-sm">
                <div class="flex items-center gap-2 mb-3 pb-2 border-b border-purple-200/70">
                    <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800 text-sm">Berkas Pembuktian Penghasilan</h4>
                        <p class="text-[11px] text-gray-500">Unggah berkas mutasi dan bukti pendapatan Anda:</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-gray-700 font-bold mb-1 text-xs">1. Upload Rekening Koran 6 Bulan Terakhir <span class="text-red-500">*</span></label>
                        <input type="file" name="bank_statement_other" class="w-full border border-gray-300 rounded-lg p-2 text-xs bg-white" accept=".pdf,.jpg,.jpeg,.png">
                        <p class="text-[11px] text-gray-500 mt-0.5">Rekening koran tabungan aktif 6 bulan terakhir (PDF/Gambar, Maks. 10MB).</p>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-bold mb-1 text-xs">2. Upload Bukti Penghasilan / Dokumen Pendukung Lainnya <span class="text-red-500">*</span></label>
                        <input type="file" name="additional_income_file" class="w-full border border-gray-300 rounded-lg p-2 text-xs bg-white" accept=".pdf,.jpg,.jpeg,.png">
                        <p class="text-[11px] text-gray-500 mt-0.5">Slip pendapatan, bukti penerimaan honorarium, surat keterangan penghasilan, atau bukti legalitas lainnya (PDF/Gambar, Maks. 10MB).</p>
                    </div>
                </div>
            </div>

            <!-- Catatan Otomatis Dokumen Identitas -->
            <div class="p-3.5 mb-6 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700 flex items-center gap-2.5">
                <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
                <span>Dokumen identitas Anda (<strong>E-KTP, Kartu Keluarga, NPWP & Buku Nikah</strong>) otomatis disinkronkan dari pendaftaran NUP, sehingga Anda tidak perlu mengunggahnya lagi.</span>
            </div>

            <!-- Box Biaya Administrasi Screening Manual (Pefindo IdScore) -->
            <div class="p-4 mb-6 rounded-xl border bg-slate-50 border-slate-200 text-xs text-slate-800">
                <div class="flex items-start gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-6-10.5h16.5A2.25 2.25 0 0121.75 8v8a2.25 2.25 0 01-2.25 2.25H4.5A2.25 2.25 0 012.25 16V8A2.25 2.25 0 014.5 5.75z"/></svg>
                    </div>
                    <div>
                        <p class="font-bold text-slate-900 mb-1">
                            Ketentuan Biaya Administrasi Screening Manual (Pefindo / BI Checking)
                        </p>
                        <p class="text-slate-600 leading-relaxed">
                            Pengajuan KPR Bank melalui evaluasi riwayat kredit biro kredit (Pefindo IdScore & SLIK OJK) dengan estimasi biaya administrasi Rp 150.000 (+ PPN 11% & VE 20% = Total Rp 196.500). Rincian nomor rekening pengembang dan form upload bukti transfer akan ditampilkan di halaman berikutnya.
                        </p>
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3.5 rounded-xl hover:bg-blue-700 shadow-md transition inline-flex items-center justify-center gap-2">
                <span>Simpan & Ajukan Screening KPR</span>
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
            </button>
        </form>
    </div>
</div>

<script>
function toggleKprDocFields() {
    const statusSelect = document.getElementById('employment_status');
    if (!statusSelect) return;
    const status = statusSelect.value;

    const docEmployee = document.getElementById('kpr_docs_employee');
    const docEntrepreneur = document.getElementById('kpr_docs_entrepreneur');
    const docOther = document.getElementById('kpr_docs_other');
    const otherContainer = document.getElementById('kpr_other_container');
    const otherInput = document.getElementById('kpr_occupation_other');

    // 1. Tampilkan input teks nama profesi jika 'other'
    if (status === 'other') {
        if (otherContainer) otherContainer.classList.remove('hidden');
        if (otherInput) {
            otherInput.required = true;
            otherInput.disabled = false;
        }
    } else {
        if (otherContainer) otherContainer.classList.add('hidden');
        if (otherInput) {
            otherInput.required = false;
            otherInput.disabled = true;
        }
    }

    // 2. Sembunyikan semua blok berkas, disable input dan lepas required
    [docEmployee, docEntrepreneur, docOther].forEach(el => {
        if (el) {
            el.classList.add('hidden');
            el.querySelectorAll('input').forEach(input => {
                input.required = false;
                input.disabled = true;
            });
        }
    });

    // 3. Tampilkan blok berkas yang sesuai dengan status pekerjaan dan aktifkan
    if (status === 'employee') {
        if (docEmployee) {
            docEmployee.classList.remove('hidden');
            docEmployee.querySelectorAll('input').forEach(input => {
                input.required = true;
                input.disabled = false;
            });
        }
    } else if (status === 'entrepreneur' || status === 'professional') {
        if (docEntrepreneur) {
            docEntrepreneur.classList.remove('hidden');
            docEntrepreneur.querySelectorAll('input').forEach(input => {
                input.required = true;
                input.disabled = false;
            });
        }
    } else if (status === 'other') {
        if (docOther) {
            docOther.classList.remove('hidden');
            docOther.querySelectorAll('input').forEach(input => {
                input.required = true;
                input.disabled = false;
            });
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    toggleKprDocFields();
});
</script>
    </div>
</div>
@endsection
