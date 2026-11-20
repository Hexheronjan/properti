@extends('layouts.app')

@section('title', 'Buat SPR - Surat Pemesanan Rumah')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('units.show', $unitSelection->id) }}" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 font-medium">
            ← Kembali ke Detail Unit
        </a>
    </div>

    {{-- Header --}}
    <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-2"><svg class="w-4 h-4 inline-block text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg> Buat Surat Pemesanan Rumah (SPR)</h1>
        <p class="text-sm text-gray-500">Isi data di bawah untuk membuat draft SPR. Draft akan direview oleh admin sebelum diterbitkan.</p>
    </div>

    {{-- Info Unit --}}
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-2xl p-6 mb-6">
        <h3 class="font-bold text-blue-800 mb-3"><svg class="w-4 h-4 inline-block text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg> Informasi Unit</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
            <div>
                <span class="text-blue-600 font-medium">Unit</span>
                <p class="font-bold text-gray-800 mt-1">{{ $unitSelection->housingUnit->full_identifier }}</p>
            </div>
            <div>
                <span class="text-blue-600 font-medium">Tipe</span>
                <p class="font-bold text-gray-800 mt-1">{{ config('nup.unit_types.' . $unitSelection->housingUnit->type, $unitSelection->housingUnit->type) }}</p>
            </div>
            <div>
                <span class="text-blue-600 font-medium">Harga Unit</span>
                <p class="font-bold text-green-700 text-lg mt-1">Rp {{ number_format($unitSelection->housingUnit->price, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    {{-- Error / Success --}}
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-2xl p-4 mb-6">
            <p class="font-semibold text-red-800 mb-2"><svg class="w-4 h-4 inline-block text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> Terjadi Kesalahan</p>
            <ul class="text-sm text-red-700 space-y-1">
                @foreach($errors->all() as $error)
                    <li>• {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @session('error')
        <div class="bg-red-50 border border-red-200 rounded-2xl p-4 mb-6">
            <p class="text-sm text-red-700"><svg class="w-4 h-4 inline-block text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> {{ session('error') }}</p>
        </div>
    @endsession

    {{-- Form --}}
    <form action="{{ route('spr.store', $unitSelection->id) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl shadow-lg p-6">
        @csrf

        @php
            $selScheme = $unitSelection->payment_scheme;
            $reqScheme = request('scheme');

            // Cek apakah user memilih skema tunai murni saat pemilihan unit
            $isTunaiUser = in_array($selScheme, ['cash', 'tunai', 'tunai_keras', 'tunai_bertahap'], true)
                           && !in_array($reqScheme, ['kpr', 'inhouse'], true);

            $project = $unitSelection->housingUnit->project ?? $unitSelection->housingUnit->housingProject;
            // Ketentuan Tunai Bertahap (Termin Hari)
            $dueDays = $project->cash_due_days ?? 30;
            $penaltyPct = (float) ($project->cash_penalty_percent ?? 1);
            $dueDate = now()->addDays($dueDays);

            // Ketentuan In-House Developer (Cicilan Bulanan)
            $inhouseMaxTenor = (int) ($project->inhouse_max_tenor_months ?? 24);
            $inhouseInterest = (float) ($project->inhouse_interest_rate ?? 0);
            $inhouseDueDay = (int) ($project->inhouse_due_day ?? 10);
            $inhousePenaltyPct = (float) ($project->inhouse_penalty_percent ?? 1);

            // Tentukan pilihan skema aktif default sesuai skema yang dipilih pertama kali saat memilih unit
            $activeScheme = old('payment_scheme') 
                            ?: ($reqScheme 
                            ?: (in_array($selScheme, ['kpr', 'kpr_bank'], true) ? 'kpr'
                                : ($selScheme === 'cash' ? 'cash' 
                                : ($selScheme === 'tunai_bertahap' ? 'tunai_bertahap'
                                : (in_array($selScheme, ['inhouse', 'installment'], true) ? 'inhouse' 
                                : ($isTunaiUser ? 'cash' : 'kpr'))))));
        @endphp

        {{-- Pilih Skema Pembayaran --}}
        <h3 class="font-bold text-gray-800 text-lg mb-2">Skema Pembayaran Pemesanan Unit</h3>
        @if($activeScheme === 'kpr')
            {{-- Khusus Skema KPR Bank: Skema Terkunci Tanpa Pilihan Lain --}}
            <input type="hidden" name="payment_scheme" value="kpr">
            <div class="mb-6 p-5 rounded-2xl border-2 border-blue-500 bg-blue-50/70 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-blue-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-blue-200 text-blue-900 tracking-wider">Skema Terpilih</span>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-emerald-100 text-emerald-800 font-bold">UTK LUNAS</span>
                        </div>
                        <h4 class="text-base font-bold text-slate-900 mt-1">KPR Bank (FLPP / Komersial)</h4>
                        <p class="text-xs text-slate-600">Dokumen SPR ini diajukan untuk proses pengawalan berkas ke Bank Rekanan penyedia KPR.</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-blue-600 text-white text-xs font-bold rounded-xl shadow-sm self-start sm:self-auto shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>KPR TERKUNCI</span>
                </span>
            </div>
        @elseif(in_array($activeScheme, ['inhouse', 'installment'], true))
            {{-- Khusus Skema In-House Developer: Skema Terkunci Sesuai Pilihan di Awal --}}
            <input type="hidden" name="payment_scheme" value="inhouse">
            <div class="mb-6 p-5 rounded-2xl border-2 border-amber-500 bg-amber-50/70 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-amber-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-amber-200 text-amber-900 tracking-wider">Skema Terpilih</span>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-emerald-100 text-emerald-800 font-bold">UTK LUNAS</span>
                        </div>
                        <h4 class="text-base font-bold text-slate-900 mt-1">In-House Developer (Cicilan Bulanan)</h4>
                        <p class="text-xs text-slate-600">Dokumen SPR ini diajukan untuk cicilan bertahap langsung ke pengembang (maksimal hingga {{ $inhouseMaxTenor }} bulan).</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-amber-600 text-white text-xs font-bold rounded-xl shadow-sm self-start sm:self-auto shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>IN-HOUSE TERKUNCI</span>
                </span>
            </div>
        @elseif($activeScheme === 'mitra_pintar')
            {{-- Khusus Skema Mitra Pintar: Skema Terkunci Sesuai Pilihan di Awal --}}
            <input type="hidden" name="payment_scheme" value="mitra_pintar">
            <div class="mb-6 p-5 rounded-2xl border-2 border-indigo-500 bg-indigo-50/70 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4 0 2.25 2.25 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-indigo-200 text-indigo-900 tracking-wider">Skema Terpilih</span>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-emerald-100 text-emerald-800 font-bold">UTK LUNAS</span>
                        </div>
                        <h4 class="text-base font-bold text-slate-900 mt-1">Kredit Mitra Pintar</h4>
                        <p class="text-xs text-slate-600">Dokumen SPR ini diajukan untuk pembiayaan program khusus kerja sama instansi / perusahaan mitra.</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-indigo-600 text-white text-xs font-bold rounded-xl shadow-sm self-start sm:self-auto shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>MITRA TERKUNCI</span>
                </span>
            </div>
        @else
            {{-- Khusus Skema Tunai: Hanya Menampilkan 2 Opsi Tunai (Tunai Keras vs Tunai Bertahap) --}}
            <p class="text-xs text-gray-500 mb-4">
                Pilih metode pembayaran tunai yang sesuai: <strong>Tunai Keras</strong> (Lunas Sekaligus) atau <strong>Tunai Bertahap</strong> (Pelunasan per Termin {{ $dueDays }} Hari).
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6" id="scheme-cards">
                <label class="scheme-card cursor-pointer" data-scheme="cash" data-min-pct="0" data-label="Tunai Keras">
                    <input type="radio" name="payment_scheme" value="cash" class="hidden scheme-radio"
                        {{ $activeScheme === 'cash' ? 'checked' : '' }}>
                    <div class="flex flex-col items-center p-5 rounded-2xl border-2 transition scheme-box
                        {{ $activeScheme === 'cash' ? 'border-green-500 bg-green-50/70 shadow-sm' : 'border-gray-200 hover:border-green-300' }}">
                        <svg class="w-8 h-8 text-emerald-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg>
                        <span class="font-bold text-gray-800 text-sm">Tunai Keras</span>
                        <span class="text-xs text-gray-500 mt-1">Lunas Sekaligus</span>
                        <span class="text-[11px] text-green-700 mt-2 font-bold px-2.5 py-0.5 bg-green-100 rounded-full">100% Lunas</span>
                    </div>
                </label>

                <label class="scheme-card cursor-pointer" data-scheme="tunai_bertahap" data-min-pct="10" data-label="Tunai Bertahap">
                    <input type="radio" name="payment_scheme" value="tunai_bertahap" class="hidden scheme-radio"
                        {{ $activeScheme === 'tunai_bertahap' ? 'checked' : '' }}>
                    <div class="flex flex-col items-center p-5 rounded-2xl border-2 transition scheme-box
                        {{ $activeScheme === 'tunai_bertahap' ? 'border-indigo-500 bg-indigo-50/70 shadow-sm' : 'border-gray-200 hover:border-indigo-300' }}">
                        <svg class="w-8 h-8 text-indigo-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="font-bold text-gray-800 text-sm">Tunai Bertahap</span>
                        <span class="text-xs text-gray-500 mt-1">Termin {{ $dueDays }} Hari</span>
                        <span class="text-[11px] text-indigo-700 mt-2 font-bold px-2.5 py-0.5 bg-indigo-100 rounded-full">Batas {{ $dueDays }} Hari</span>
                    </div>
                </label>
            </div>
        @endif

        {{-- Detail Pembayaran --}}
        @if($activeScheme === 'kpr')
            {{-- Khusus KPR: Bebas DP, Tanda Jadi Menggunakan UTK --}}
            <input type="hidden" name="down_payment" id="down_payment" value="0">
            <h3 class="font-bold text-gray-800 text-lg mb-4">Detail Perhitungan Pembiayaan KPR</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide block mb-1">Harga Unit Rumah</span>
                    <span class="text-lg font-black text-slate-900 block">Rp {{ number_format($unitSelection->housingUnit->price, 0, ',', '.') }}</span>
                    <span class="text-[11px] text-slate-400 mt-0.5 block">Harga resmi pengembang</span>
                </div>
                <div class="bg-emerald-50 p-4 rounded-xl border border-emerald-200">
                    <span class="text-xs font-semibold text-emerald-800 uppercase tracking-wide block mb-1">Uang Tanda Jadi (UTK)</span>
                    <span class="text-lg font-black text-emerald-700 block">Rp {{ number_format($unitSelection->utk_amount, 0, ',', '.') }}</span>
                    <span class="text-[11px] text-emerald-600 font-bold mt-0.5 block"><svg class="w-4 h-4 inline-block text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> SUDAH LUNAS</span>
                </div>
                <div class="bg-blue-50 p-4 rounded-xl border border-blue-200">
                    <span class="text-xs font-semibold text-blue-800 uppercase tracking-wide block mb-1">Plafon Pengajuan KPR Bank</span>
                    <span class="text-lg font-black text-blue-700 block">Rp {{ number_format($unitSelection->housingUnit->price, 0, ',', '.') }}</span>
                    <span class="text-[11px] text-blue-600 font-medium mt-0.5 block">100% Pembiayaan KPR Bank</span>
                </div>
            </div>

            {{-- Banner Khusus KPR: Tanpa DP --}}
            <div class="mb-6 p-5 bg-gradient-to-br from-indigo-50/90 to-blue-50/70 border border-indigo-200 rounded-2xl shadow-sm">
                <div class="flex items-start gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-sm mt-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="text-xs text-indigo-950 space-y-2 leading-relaxed">
                        <h4 class="font-bold text-sm text-indigo-900">Ketentuan Alur Pengurusan KPR Bank:</h4>
                        <p>1. <strong class="text-indigo-950">Tanpa Uang Muka (Bebas DP):</strong> Skema KPR Bank tidak membebankan Down Payment (DP). Komitmen tanda jadi pemesanan unit telah terpenuhi melalui pembayaran <strong class="text-emerald-700">Uang Tanda Jadi (UTK)</strong> yang telah berstatus lunas.</p>
                        <p>2. <strong class="text-indigo-950">Argo 7 Hari Kerja:</strong> Begitu SPR diterbitkan, tim operator pengembang akan menyerahkan berkas Anda ke Bank Rekanan dan <strong>Argo 7 Hari Pengurusan Bank</strong> akan langsung aktif di sistem.</p>
                        <p>3. <strong class="text-indigo-950">Penerbitan SP3K & Akad:</strong> Pihak bank memproses analisis kredit pemohon. Setelah SP3K dari bank terbit dan KPR disetujui (ACC), Anda dapat langsung melanjutkan ke penandatanganan Akad Kredit di hadapan bank/notaris.</p>
                    </div>
                </div>
            </div>
        @else
            {{-- Detail Pembayaran Skema Tunai / Inhouse --}}
            <h3 class="font-bold text-gray-800 text-lg mb-4">Detail Perhitungan Pembiayaan</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-6">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2 text-sm">Harga Unit Rumah</label>
                    <input type="text" value="Rp {{ number_format($unitSelection->housingUnit->price, 0, ',', '.') }}"
                           class="w-full border border-gray-200 rounded-xl p-3 bg-gray-50 text-gray-700 font-semibold" readonly>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2 text-sm">
                        Down Payment / Uang Muka
                        <span id="dp-min-label" class="text-xs text-gray-400 font-normal ml-1"></span>
                    </label>
                    <input type="number" name="down_payment" id="down_payment"
                           class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-blue-300 focus:border-blue-400 transition"
                           value="{{ old('down_payment') }}"
                           min="0" max="{{ $unitSelection->housingUnit->price }}"
                           placeholder="Masukkan jumlah DP">
                    <p id="dp-hint" class="text-xs text-gray-400 mt-1"></p>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2 text-sm">Sisa / Pokok Pinjaman</label>
                    <input type="text" id="remaining_display"
                           class="w-full border border-gray-200 rounded-xl p-3 bg-gray-50 text-gray-700 font-semibold" readonly>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2 text-sm">Porsi Down Payment (DP)</label>
                    <div class="w-full bg-gray-100 rounded-full h-3 mt-3">
                        <div id="dp-progress-bar" class="bg-blue-600 h-3 rounded-full transition-all duration-300" style="width:0%"></div>
                    </div>
                    <p id="dp-pct-label" class="text-xs text-gray-500 mt-1">0%</p>
                </div>
            </div>

            {{-- Info Rekening Transfer Pengembang untuk Pembayaran Uang Muka (DP) Khusus Tunai/Inhouse --}}
            <div class="mb-6 p-5 bg-gradient-to-br from-slate-50 to-blue-50/40 border border-blue-200 rounded-2xl shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-blue-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-900 text-sm">Rekening Tujuan Transfer Pengembang</h4>
                            <p class="text-xs text-slate-500">Gunakan rekening resmi pengembang di bawah ini untuk transfer Uang Muka (DP)</p>
                        </div>
                    </div>
                    <div class="text-left sm:text-right bg-white sm:bg-transparent px-3 py-1.5 sm:p-0 rounded-lg border sm:border-0 border-blue-100">
                        <span class="text-[11px] text-slate-500 block">Nominal DP Disepakati:</span>
                        <span class="text-sm font-black text-blue-700" id="dp-transfer-preview">Rp 0</span>
                    </div>
                </div>

                <div class="mt-4">
                    @if(isset($bankAccounts) && $bankAccounts->count() > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($bankAccounts as $acc)
                                <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs hover:border-blue-300 transition">
                                    <div class="flex items-center justify-between gap-2 mb-1.5">
                                        <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded font-bold text-xs">
                                            {{ $acc->bank_name }}
                                        </span>
                                        <button type="button" onclick="navigator.clipboard.writeText('{{ $acc->account_number }}'); alert('Nomor rekening {{ $acc->account_number }} berhasil disalin!');"
                                                class="text-[11px] font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                            Salin No. Rek
                                        </button>
                                    </div>
                                    <div class="font-mono font-black text-slate-900 text-base tracking-wider">
                                        {{ $acc->account_number }}
                                    </div>
                                    <div class="text-xs text-slate-600 mt-1">
                                        a.n. <strong class="text-slate-800">{{ $acc->account_holder_name }}</strong>
                                    </div>
                                    @if($acc->branch)
                                        <div class="text-[11px] text-slate-400 mt-0.5">Cabang: {{ $acc->branch }}</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-white p-4 rounded-xl border border-slate-200 text-xs text-slate-600">
                            Rekening bank resmi pengembang ({{ $company?->company_name ?? 'Pengembang' }}) akan ditampilkan saat Anda membuat draft SPR dan mengonfirmasi pembayaran.
                        </div>
                    @endif
                </div>

                {{-- Form Upload Bukti Transfer Uang Muka (DP) --}}
                <div class="mt-5 pt-4 border-t border-blue-100 bg-white p-5 rounded-2xl border border-blue-100 shadow-xs">
                    <div class="flex items-center gap-2 mb-3">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        <div>
                            <h5 class="font-bold text-slate-900 text-sm">Upload Bukti Transfer Uang Muka (DP)</h5>
                            <p class="text-xs text-slate-500">Jika Anda sudah melakukan transfer ke rekening di atas, unggah struk pembayarannya di bawah ini</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs mb-4">
                        <div>
                            <label class="block text-slate-700 font-semibold mb-1">Rekening Tujuan Transfer</label>
                            <select name="developer_bank_account_id" class="w-full border border-slate-300 rounded-xl p-2.5 text-xs focus:ring-1 focus:ring-blue-500">
                                @if(isset($bankAccounts) && $bankAccounts->count() > 0)
                                    @foreach($bankAccounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->bank_name }} - {{ $acc->account_number }} (a.n. {{ $acc->account_holder_name }})</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <div>
                            <label class="block text-slate-700 font-semibold mb-1">Foto / File Bukti Transfer (Struk ATM / M-Banking)</label>
                            <input type="file" name="proof_of_transfer" accept=".jpg,.jpeg,.png,.pdf"
                                   class="w-full border border-slate-300 rounded-xl p-2 text-xs focus:ring-1 focus:ring-blue-500 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <span class="text-[10px] text-slate-400 mt-1 block">Format: JPG, PNG, atau PDF (Maks. 10MB)</span>
                        </div>

                        <div>
                            <label class="block text-slate-700 font-semibold mb-1">Bank Pengirim Anda</label>
                            <input type="text" name="sender_bank" value="{{ old('sender_bank') }}" placeholder="Contoh: BCA / Mandiri / BRI / BNI"
                                   class="w-full border border-slate-300 rounded-xl p-2.5 text-xs focus:ring-1 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-slate-700 font-semibold mb-1">Nama Pemilik Rekening Pengirim</label>
                            <input type="text" name="sender_name" value="{{ old('sender_name', auth()->user()->name ?? '') }}" placeholder="Nama sesuai buku tabungan / mutasi"
                                   class="w-full border border-slate-300 rounded-xl p-2.5 text-xs focus:ring-1 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-slate-700 font-semibold mb-1">Tanggal Transfer</label>
                            <input type="date" name="transfer_date" value="{{ old('transfer_date', date('Y-m-d')) }}"
                                   class="w-full border border-slate-300 rounded-xl p-2.5 text-xs focus:ring-1 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-slate-700 font-semibold mb-1">Nomor Rekening Pengirim (Opsional)</label>
                            <input type="text" name="sender_account_number" value="{{ old('sender_account_number') }}" placeholder="Nomor rekening pengirim"
                                   class="w-full border border-slate-300 rounded-xl p-2.5 text-xs focus:ring-1 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="p-2.5 bg-blue-50/70 border border-blue-200/80 rounded-xl text-[11px] text-blue-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span><strong>Catatan:</strong> Jika Anda belum transfer saat ini, Anda tetap bisa klik tombol <strong>"Buat Draft SPR"</strong> di bawah dan mengunggah bukti transfer nanti di halaman detail SPR.</span>
                    </div>
                </div>
            </div>
        @endif

        {{-- 1. Ketentuan Skema Tunai Bertahap (Pelunasan Termin per Hari) --}}
        <div id="tunai-bertahap-section" class="hidden mb-6 p-5 bg-indigo-50/80 border border-indigo-200 rounded-2xl">
            <h4 class="font-bold text-indigo-950 text-sm mb-1 flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Ketentuan Tunai Bertahap (Pelunasan Termin Hari)</span>
            </h4>
            <p class="text-xs text-indigo-800 mb-4">
                Sisa pelunasan rumah diselesaikan bertahap dalam jangka hari kalender sesuai batas maksimal yang ditentukan oleh pengembang (<strong>{{ $project->name ?? 'Perumahan' }}</strong>):
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs mb-3">
                <div class="bg-white p-3.5 rounded-xl border border-indigo-100 shadow-sm">
                    <label for="tunai_bertahap_days_select" class="text-gray-500 block text-[11px] uppercase font-bold mb-1">Target Waktu Pelunasan</label>
                    @php
                        $maxD = max(1, (int) $dueDays);
                        if ($maxD <= 30) {
                            $dayPreset = [7, 14, 21, 30];
                        } else {
                            $dayPreset = [7, 14, 30, 45, 60, 90, 120, 180];
                        }
                        $dayOptions = array_filter($dayPreset, fn($d) => $d < $maxD);
                        $dayOptions[] = $maxD;
                        sort($dayOptions);
                        $dayOptions = array_values(array_unique($dayOptions));
                    @endphp
                    <select id="tunai_bertahap_days_select" name="tunai_bertahap_days" class="w-full border border-indigo-300 rounded-lg p-2 font-bold text-indigo-900 bg-indigo-50/50 text-sm focus:ring-2 focus:ring-indigo-500">
                        @foreach($dayOptions as $d)
                            <option value="{{ $d }}" {{ $d == $maxD ? 'selected' : '' }}>
                                {{ $d }} Hari @if($d == $maxD) (Maksimal Pengembang) @endif
                            </option>
                        @endforeach
                    </select>
                    <span class="text-[10px] text-indigo-700 font-semibold mt-1 block">Maksimal {{ $maxD }} hari dari developer</span>
                </div>

                <div class="bg-white p-3.5 rounded-xl border border-indigo-100 shadow-sm">
                    <span class="text-gray-500 block text-[11px] uppercase font-bold">Estimasi Jatuh Tempo</span>
                    <span class="font-extrabold text-gray-900 text-sm mt-1.5 block" id="tunai_bertahap_due_date_text">{{ $dueDate->translatedFormat('d F Y') }}</span>
                    <span class="text-[11px] text-gray-400">Dihitung sejak tanggal terbit SPR</span>
                </div>

                <div class="bg-white p-3.5 rounded-xl border border-indigo-100 shadow-sm">
                    <span class="text-gray-500 block text-[11px] uppercase font-bold">Denda Keterlambatan</span>
                    <span class="font-bold text-rose-700 text-sm mt-1.5 block">{{ $penaltyPct }}% / bulan</span>
                    <span class="text-[11px] text-gray-400">Jika melewati batas waktu</span>
                </div>
            </div>

            <div class="p-3 bg-white/80 border border-indigo-200 rounded-xl text-xs text-indigo-900">
                <span>Sisa pelunasan sebesar <strong class="text-gray-900 font-bold" id="tunai_bertahap_remaining_text">Rp 0</strong> wajib diselesaikan selambat-lambatnya dalam waktu yang dipilih (maksimal <strong>{{ $dueDays }} hari</strong> kalender sesuai ketentuan developer <strong>{{ $project->name ?? 'Pengembang' }}</strong>).</span>
            </div>
        </div>

        {{-- 2. Ketentuan Skema In-House Developer (Cicilan Angsuran per Bulan) --}}
        <div id="inhouse-section" class="hidden mb-6 p-5 bg-amber-50/80 border border-amber-300 rounded-2xl">
            <h4 class="font-bold text-amber-950 text-sm mb-1 flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5"/></svg>
                <span>Ketentuan In-House Developer (Cicilan Bulanan Langsung ke Pengembang)</span>
            </h4>
            <p class="text-xs text-amber-800 mb-4">
                Sisa harga rumah dicicil setiap bulan ke rekening pengembang (<strong>{{ $project->name ?? 'Perumahan' }}</strong>) dengan pilihan tenor yang dibatasi oleh ketentuan developer:
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs mb-4">
                <div class="bg-white p-3.5 rounded-xl border border-amber-200 shadow-sm">
                    <label for="inhouse_tenor_select" class="text-gray-500 block text-[11px] uppercase font-bold mb-1">Pilihan Tenor Cicilan</label>
                    @php
                        $maxT = max(1, (int) $inhouseMaxTenor);
                        if ($maxT <= 12) {
                            // Jika maksimal tenor dari pengembang <= 12 bulan (misal 2, 3, 6 bulan), buat pilihan berurutan 1 sampai maxT
                            $tenorOptions = range(1, $maxT);
                        } else {
                            // Jika lebih dari 12 bulan, berikan pilihan umum sampai batas maxT
                            $preset = [1, 2, 3, 6, 9, 12, 18, 24, 30, 36, 48, 60];
                            $tenorOptions = array_filter($preset, fn($t) => $t < $maxT);
                            $tenorOptions[] = $maxT;
                            sort($tenorOptions);
                            $tenorOptions = array_values(array_unique($tenorOptions));
                        }
                    @endphp
                    <select id="inhouse_tenor_select" name="inhouse_tenor" class="w-full border border-amber-300 rounded-lg p-2 font-bold text-amber-900 bg-amber-50/50 text-sm focus:ring-2 focus:ring-amber-500">
                        @foreach($tenorOptions as $t)
                            <option value="{{ $t }}" {{ $t == $maxT ? 'selected' : '' }}>
                                {{ $t }} Bulan ({{ $t }}x Angsuran) @if($t == $maxT) — Maksimal dari Developer @endif
                            </option>
                        @endforeach
                    </select>
                    <span class="text-[10px] text-amber-700 font-semibold mt-1 block">
                        Dibatasi maksimal {{ $maxT }} bulan sesuai ketentuan developer ({{ $project->name ?? 'Pengembang' }})
                    </span>
                </div>

                <div class="bg-white p-3.5 rounded-xl border border-amber-200 shadow-sm">
                    <span class="text-gray-500 block text-[11px] uppercase font-bold">Jatuh Tempo Bulanan</span>
                    <span class="font-extrabold text-gray-900 text-sm mt-1.5 block">Tiap Tanggal {{ $inhouseDueDay }}</span>
                    <span class="text-[11px] text-gray-400">Pembayaran per bulan</span>
                </div>

                <div class="bg-white p-3.5 rounded-xl border border-amber-200 shadow-sm">
                    <span class="text-gray-500 block text-[11px] uppercase font-bold">Bunga & Denda</span>
                    <span class="font-bold text-gray-900 text-sm mt-1.5 block">Bunga: {{ $inhouseInterest > 0 ? $inhouseInterest . '%' : '0% (Bebas)' }}</span>
                    <span class="text-[11px] text-rose-600 block mt-0.5">Denda: {{ $inhousePenaltyPct }}% / bln</span>
                </div>
            </div>

            <!-- Kotak Estimasi Angsuran Bulanan -->
            <div class="p-4 bg-white rounded-xl border-2 border-amber-400 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <span class="text-xs uppercase font-extrabold text-amber-900 tracking-wider block">Estimasi Angsuran In-House</span>
                    <span class="text-[11px] text-gray-500">Dihitung otomatis: Sisa Pokok dibagi tenor bulan yang Anda pilih</span>
                </div>
                <div class="text-left sm:text-right">
                    <div class="text-2xl font-black text-amber-900" id="inhouse_monthly_display">Rp 0</div>
                    <span class="text-xs font-semibold text-amber-700" id="inhouse_tenor_display">/ bulan</span>
                </div>
            </div>
        </div>

        <input type="hidden" name="payment_schedule" id="payment_schedule" value="">

        {{-- Syarat & Ketentuan --}}
        <div class="mb-6">
            <label class="block text-gray-700 font-semibold mb-2 text-sm">Syarat & Ketentuan (Opsional)</label>
            <textarea name="terms_conditions" class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-blue-300 transition"
                      rows="3" placeholder="Tambahkan catatan atau syarat khusus...">{{ old('terms_conditions') }}</textarea>
        </div>

        {{-- Info Box --}}
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
            <h4 class="font-semibold text-amber-800 mb-2"><svg class="w-4 h-4 inline-block text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Informasi Penting</h4>
            <ul class="text-sm text-amber-700 space-y-1">
                @if($activeScheme === 'kpr')
                    <li>• Dokumen SPR ini diterbitkan sebagai bukti pemesanan resmi unit rumah untuk pengajuan berkas KPR ke Bank Rekanan.</li>
                    <li>• Tanda jadi pemesanan unit telah dipenuhi lewat <strong>Uang Tanda Komitmen (UTK)</strong> yang sudah lunas (Bebas DP).</li>
                    <li>• Setelah SPR diterbitkan, <strong>Argo 7 Hari Pengurusan Bank</strong> langsung berjalan aktif di sistem bagi operator pengembang.</li>
                    <li>• Penandatanganan Akad Kredit dilakukan setelah Surat Keputusan Kredit (SP3K) resmi disetujui (ACC) oleh pihak bank.</li>
                @elseif($isTunaiUser)
                    <li>• Draft SPR akan dibuat dan perlu <strong>disetujui admin pengembang</strong> sebelum diterbitkan</li>
                    <li>• Pelunasan sisa tagihan rumah mengikuti ketentuan batas waktu <strong>{{ $dueDays }} hari</strong> yang ditetapkan oleh pengembang</li>
                @else
                    <li>• Draft SPR akan dibuat dan diverifikasi oleh pihak pengembang</li>
                @endif
            </ul>
        </div>

        <button type="submit" id="submit-btn"
                class="w-full bg-blue-600 text-white font-bold py-4 rounded-xl hover:bg-blue-700 active:scale-95 transition text-base flex items-center justify-center gap-2 shadow-lg shadow-blue-500/25">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>{{ $activeScheme === 'kpr' ? 'Terbitkan Dokumen SPR KPR & Jalankan Argo Bank' : 'Buat Draft SPR' }}</span>
        </button>
    </form>
</div>

@push('scripts')
<script>
    const unitPrice    = {{ $unitSelection->housingUnit->price }};
    const dpInput      = document.getElementById('down_payment');
    const remaining    = document.getElementById('remaining_display');
    const progressBar  = document.getElementById('dp-progress-bar');
    const pctLabel     = document.getElementById('dp-pct-label');
    const dpHint       = document.getElementById('dp-hint');
    const dpMinLabel   = document.getElementById('dp-min-label');
    const submitBtn    = document.getElementById('submit-btn');
    const scheduleInput = document.getElementById('payment_schedule');
    const tenorSelect  = document.getElementById('inhouse_tenor_select');
    const daysSelect   = document.getElementById('tunai_bertahap_days_select');

    const schemeCards  = document.querySelectorAll('.scheme-card');
    const schemeRadios = document.querySelectorAll('.scheme-radio');
    const schemeBoxes  = document.querySelectorAll('.scheme-box');

    let currentMinPct  = 0;
    const dueDays      = {{ $dueDays }};
    const inhouseDueDay = {{ $inhouseDueDay }};

    // Format number rupiah
    function formatRp(n) {
        return 'Rp ' + Math.max(0, Math.round(n)).toLocaleString('id-ID');
    }

    // Hitung dan update jadwal pembayaran & simulasi
    function updateSchedule() {
        let scheme = '{{ $activeScheme }}';
        const checkedRadio = document.querySelector('.scheme-radio:checked');
        if (checkedRadio) scheme = checkedRadio.value;
        const dp = parseFloat(dpInput.value) || 0;
        const rem = Math.max(0, unitPrice - dp);

        // Update teks sisa pembayaran di kotak per hari
        const tunaiBertahapText = document.getElementById('tunai_bertahap_remaining_text');
        if (tunaiBertahapText) {
            tunaiBertahapText.textContent = formatRp(rem);
        }

        // Update simulasi cicilan bulanan in-house
        const monthlyDisplay = document.getElementById('inhouse_monthly_display');
        const tenorDisplay = document.getElementById('inhouse_tenor_display');
        const tenor = tenorSelect ? parseInt(tenorSelect.value) || 12 : 12;
        const monthly = tenor > 0 ? Math.round(rem / tenor) : 0;

        if (monthlyDisplay) monthlyDisplay.textContent = formatRp(monthly);
        if (tenorDisplay) tenorDisplay.textContent = `/ bulan (${tenor}x angsuran)`;

        // Set nilai input hidden payment_schedule
        if (scheme === 'cash') {
            if (scheduleInput) scheduleInput.value = 'Tunai Keras (Lunas Sekaligus 100%)';
        } else if (scheme === 'tunai_bertahap') {
            const selectedDays = daysSelect ? parseInt(daysSelect.value) || dueDays : dueDays;
            const targetDate = new Date();
            targetDate.setDate(targetDate.getDate() + selectedDays);
            const dateFormatted = targetDate.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
            const dueDateText = document.getElementById('tunai_bertahap_due_date_text');
            if (dueDateText) dueDateText.textContent = dateFormatted;
            if (scheduleInput) scheduleInput.value = `Termin ${selectedDays} Hari (Jatuh Tempo: ${dateFormatted})`;
        } else if (scheme === 'inhouse' || scheme === 'installment') {
            if (scheduleInput) scheduleInput.value = `Cicilan In-House ${tenor} Bulan (Angsuran ${formatRp(monthly)}/bln, Tiap Tgl ${inhouseDueDay})`;
        } else if (scheme === 'kpr') {
            if (scheduleInput) scheduleInput.value = `KPR Bank (Plafon Kredit ${formatRp(rem)})`;
        }
    }

    // Update UI ketika DP berubah
    function updateDP() {
        const dp  = dpInput ? (parseFloat(dpInput.value) || 0) : 0;
        const rem = Math.max(0, unitPrice - dp);
        const pct = unitPrice > 0 ? Math.min(100, (dp / unitPrice) * 100) : 0;

        if (remaining) remaining.value = formatRp(rem);
        if (progressBar) progressBar.style.width = pct + '%';
        if (pctLabel) pctLabel.textContent = pct.toFixed(1) + '%';

        const dpTransferPreview = document.getElementById('dp-transfer-preview');
        if (dpTransferPreview) {
            dpTransferPreview.textContent = formatRp(dp);
        }

        // Warna progress bar
        if (progressBar) {
            if (pct >= 100) progressBar.className = 'bg-green-500 h-3 rounded-full transition-all duration-300';
            else if (pct >= 50) progressBar.className = 'bg-blue-500 h-3 rounded-full transition-all duration-300';
            else progressBar.className = 'bg-amber-500 h-3 rounded-full transition-all duration-300';
        }

        updateSchedule();
    }

    // Update hint text per skema
    function updateSchemeHint(card) {
        const scheme   = card.dataset.scheme;
        const minPct   = parseFloat(card.dataset.minPct) || 0;
        currentMinPct  = minPct;

        const tunaiBertahapSec = document.getElementById('tunai-bertahap-section');
        const inhouseSec       = document.getElementById('inhouse-section');

        if (scheme === 'cash') {
            if (tunaiBertahapSec) tunaiBertahapSec.classList.add('hidden');
            if (inhouseSec) inhouseSec.classList.add('hidden');
            dpHint.textContent     = 'Skema Tunai Keras: Pelunasan 100% lunas sekaligus.';
            dpMinLabel.textContent = '';
            dpInput.value          = unitPrice;
            dpInput.setAttribute('readonly', true);
            dpInput.classList.add('bg-gray-100');
            updateDP();
        } else if (scheme === 'tunai_bertahap') {
            if (tunaiBertahapSec) tunaiBertahapSec.classList.remove('hidden');
            if (inhouseSec) inhouseSec.classList.add('hidden');
            dpHint.textContent     = 'Isi nominal DP / tanda jadi awal (sisa pelunasan dalam batas ' + dueDays + ' hari kalender pengembang).';
            dpMinLabel.textContent = '';
            dpInput.removeAttribute('readonly');
            dpInput.classList.remove('bg-gray-100');
            dpInput.placeholder    = 'Masukkan jumlah DP awal';
            if (parseFloat(dpInput.value) >= unitPrice || !dpInput.value) {
                dpInput.value = Math.round(unitPrice * 0.2); // Default 20% DP
            }
            updateDP();
        } else if (scheme === 'inhouse' || scheme === 'installment') {
            if (tunaiBertahapSec) tunaiBertahapSec.classList.add('hidden');
            if (inhouseSec) inhouseSec.classList.remove('hidden');
            dpHint.textContent     = 'Isi nominal DP uang muka (sisa harga rumah dicicil setiap bulan langsung ke developer).';
            dpMinLabel.textContent = '';
            dpInput.removeAttribute('readonly');
            dpInput.classList.remove('bg-gray-100');
            dpInput.placeholder    = 'Masukkan jumlah DP awal';
            if (parseFloat(dpInput.value) >= unitPrice || !dpInput.value) {
                dpInput.value = Math.round(unitPrice * 0.2); // Default 20% DP
            }
            updateDP();
        } else if (scheme === 'kpr') {
            if (tunaiBertahapSec) tunaiBertahapSec.classList.add('hidden');
            if (inhouseSec) inhouseSec.classList.add('hidden');
            if (dpHint) dpHint.textContent = 'Skema KPR Bank: Tanpa Uang Muka (Bebas DP). Komitmen tanda jadi telah menggunakan UTK lunas.';
            if (dpMinLabel) dpMinLabel.textContent = '';
            if (dpInput) {
                dpInput.value = 0;
            }
            updateDP();
        }
    }

    // Warna card aktif
    function activateCard(activeCard) {
        schemeCards.forEach(c => {
            const box = c.querySelector('.scheme-box');
            const scheme = c.dataset.scheme;
            if (c === activeCard) {
                if (scheme === 'cash') {
                    box.className = 'flex flex-col items-center p-5 rounded-2xl border-2 transition scheme-box border-green-500 bg-green-50/70 shadow-sm';
                } else if (scheme === 'tunai_bertahap') {
                    box.className = 'flex flex-col items-center p-5 rounded-2xl border-2 transition scheme-box border-indigo-500 bg-indigo-50/70 shadow-sm';
                } else if (scheme === 'inhouse' || scheme === 'installment') {
                    box.className = 'flex flex-col items-center p-5 rounded-2xl border-2 transition scheme-box border-amber-500 bg-amber-50/70 shadow-sm';
                } else if (scheme === 'kpr') {
                    box.className = 'flex flex-col items-center p-5 rounded-2xl border-2 transition scheme-box border-blue-500 bg-blue-50/70 shadow-sm';
                }
            } else {
                box.className = 'flex flex-col items-center p-5 rounded-2xl border-2 border-gray-200 hover:border-gray-300 transition scheme-box';
            }
        });
    }

    // Event: pilih kartu skema
    schemeCards.forEach(card => {
        card.addEventListener('click', () => {
            const radio = card.querySelector('.scheme-radio');
            radio.checked = true;
            activateCard(card);
            updateSchemeHint(card);
        });
    });

    // Event: DP input
    if (dpInput) {
        dpInput.addEventListener('input', updateDP);
    }

    // Event: tenor inhouse select
    if (tenorSelect) {
        tenorSelect.addEventListener('change', updateSchedule);
    }

    // Event: target hari tunai bertahap select
    if (daysSelect) {
        daysSelect.addEventListener('change', updateSchedule);
    }

    // Inisialisasi
    const activeSchemeVal = '{{ $activeScheme }}';
    if (activeSchemeVal === 'kpr') {
        if (dpInput) dpInput.value = 0;
        if (scheduleInput) scheduleInput.value = `KPR Bank (Plafon Pengajuan: ${formatRp(unitPrice)})`;
    } else if (activeSchemeVal === 'inhouse' || activeSchemeVal === 'installment') {
        const inhouseSec = document.getElementById('inhouse-section');
        const tunaiBertahapSec = document.getElementById('tunai-bertahap-section');
        if (inhouseSec) inhouseSec.classList.remove('hidden');
        if (tunaiBertahapSec) tunaiBertahapSec.classList.add('hidden');
        if (dpHint) dpHint.textContent = 'Isi nominal DP uang muka (sisa harga rumah dicicil setiap bulan langsung ke developer).';
        if (dpMinLabel) dpMinLabel.textContent = '';
        if (dpInput) {
            dpInput.removeAttribute('readonly');
            dpInput.classList.remove('bg-gray-100');
            dpInput.placeholder = 'Masukkan jumlah DP awal';
            if (parseFloat(dpInput.value) >= unitPrice || !dpInput.value) {
                dpInput.value = Math.round(unitPrice * 0.2);
            }
        }
        updateSchedule();
    }

    const checkedRadio = document.querySelector('.scheme-radio:checked');
    if (checkedRadio) {
        const activeCard = checkedRadio.closest('.scheme-card');
        if (activeCard) {
            activateCard(activeCard);
            updateSchemeHint(activeCard);
        }
    }
    updateDP();
</script>
@endpush
@endsection