@extends('layouts.app')

@section('title', 'Detail & Berkas Screening KPR')

@section('content')
<div class="max-w-5xl mx-auto py-6 px-4 sm:px-6">
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-blue-600 hover:text-blue-800 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            Kembali ke Dashboard
        </a>
        <span class="text-xs text-gray-500 font-mono">No. Screening: {{ $screening->screening_number }}</span>
    </div>

    <!-- Alert Notifikasi -->
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl text-sm mb-6 flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl text-sm mb-6 flex items-center gap-2">
            <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @php
        $isCancelledByUser = ($screening->participant_decision === 'not_agree' || $screening->rejection_reason === 'User membatalkan pengajuan KPR');
        $isSwitchedToInhouse = ($screening->participant_decision === 'switch_to_inhouse' || ($screening->nupRegistration?->kpr_switched && in_array($screening->nupRegistration?->payment_method, ['inhouse', 'installment'], true)));
        $isSwitchedToCash = ($screening->participant_decision === 'switch_to_cash' || ($screening->nupRegistration?->kpr_switched && in_array($screening->nupRegistration?->payment_method, ['cash', 'tunai'], true)));
    @endphp

    <!-- Card 1: Ringkasan Pengajuan & Status Pefindo -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-gray-100">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Screening & Verifikasi Berkas KPR</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    @if($screening->bank)
                        Bank Rekanan: <span class="font-bold text-gray-800">{{ $screening->bank === 'other' ? ($screening->bank_other ?: 'Bank Lainnya') : ($kprBanks[$screening->bank] ?? $screening->bank) }}</span> |
                    @endif
                    Kategori Pemohon: <span class="font-bold text-gray-800">{{ \App\Models\KprScreening::getOccupationLabel($screening->nupRegistration->occupation ?? $screening->user->occupation ?? $screening->employment_status, $screening->nupRegistration->occupation_other ?? null) }}</span>
                </p>
            </div>
            <div>
                <span class="inline-flex items-center px-3.5 py-1.5 rounded-full text-xs font-bold
                    @if($isSwitchedToInhouse) bg-indigo-100 text-indigo-800 border border-indigo-300
                    @elseif($isSwitchedToCash) bg-emerald-100 text-emerald-800 border border-emerald-300
                    @elseif($isCancelledByUser) bg-slate-100 text-slate-700 border border-slate-300
                    @elseif($screening->status === 'approved') bg-emerald-100 text-emerald-800 border border-emerald-300
                    @elseif($screening->status === 'layak_bersyarat') bg-amber-100 text-amber-800 border border-amber-300
                    @elseif($screening->status === 'rejected') bg-rose-100 text-rose-800 border border-rose-300
                    @elseif($screening->status === 'processing') bg-blue-100 text-blue-800 border border-blue-300
                    @elseif($screening->status === 'submitted') bg-indigo-100 text-indigo-800 border border-indigo-300
                    @else bg-gray-100 text-gray-700 border border-gray-300 @endif
                ">
                    @if($isSwitchedToInhouse) BERALIH KE IN-HOUSE
                    @elseif($isSwitchedToCash) BERALIH KE TUNAI
                    @elseif($isCancelledByUser) DIBATALKAN OLEH PEMOHON
                    @elseif($screening->status === 'approved') LAYAK KPR (HIJAU)
                    @elseif($screening->status === 'layak_bersyarat') LAYAK BERSYARAT (KUNING)
                    @elseif($screening->status === 'rejected') TIDAK LAYAK KPR (MERAH)
                    @elseif($screening->status === 'processing') PROSES VERIFIKASI BANK
                    @elseif($screening->status === 'submitted') BERKAS TELAH DIKIRIM (MENUNGGU VERIFIKASI)
                    @else MENUNGGU BERKAS LENGKAP @endif
                </span>
            </div>
        </div>

        @if($isSwitchedToInhouse)
            <!-- Banner Penjelasan Khusus jika Beralih ke In-House -->
            <div class="mt-6 p-4 rounded-xl border border-indigo-200 bg-indigo-50/70 text-indigo-950 flex items-start gap-3">
                <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                </div>
                <div>
                    <h4 class="font-bold text-indigo-950 text-sm">Anda Telah Beralih ke Skema Cicilan In-House (Pengembang)</h4>
                    <p class="text-xs text-indigo-800 mt-1 leading-relaxed">
                        Anda telah memilih untuk melanjutkan pemesanan dengan skema cicilan langsung ke pengembang tanpa KPR Bank. Tiket NUP Anda tetap aktif. Silakan lanjutkan ke menu pemilihan unit kavling Anda.
                    </p>
                </div>
            </div>
        @elseif($isSwitchedToCash)
            <!-- Banner Penjelasan Khusus jika Beralih ke Tunai -->
            <div class="mt-6 p-4 rounded-xl border border-emerald-200 bg-emerald-50/70 text-emerald-950 flex items-start gap-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                </div>
                <div>
                    <h4 class="font-bold text-emerald-950 text-sm">Anda Telah Beralih ke Skema Pembayaran Tunai</h4>
                    <p class="text-xs text-emerald-800 mt-1 leading-relaxed">
                        Anda telah memilih untuk melanjutkan pemesanan secara Tunai tanpa melalui bank. Tiket NUP Anda tetap aktif. Silakan lanjutkan ke menu pemilihan unit kavling Anda.
                    </p>
                </div>
            </div>
        @elseif($isCancelledByUser)
            <!-- Banner Penjelasan Khusus jika Dibatalkan Mandiri oleh Pemohon -->
            <div class="mt-6 p-4 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 flex items-start gap-3">
                <div class="w-9 h-9 rounded-xl bg-slate-200 text-slate-700 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 text-sm">Pengajuan KPR Dibatalkan Secara Mandiri</h4>
                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                        Anda telah memilih untuk membatalkan pengajuan KPR ini. Tiket antrean NUP terkait (<strong>{{ $screening->nupRegistration?->queue_number ?: 'NUP' }}</strong>) telah dinyatakan hangus. Jika Anda ingin melakukan pemesanan unit di lain waktu, silakan mendaftar tiket NUP baru dari awal.
                    </p>
                </div>
            </div>
        @endif

        <!-- Banner Skor Kredit Pefindo jika ada -->
        @if($screening->pefindo_score)
            <div class="mt-6 p-4 rounded-xl border {{ $isCancelledByUser ? 'bg-slate-50 border-slate-200 text-slate-800' : ($screening->status === 'approved' ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : ($screening->status === 'layak_bersyarat' ? 'bg-amber-50 border-amber-200 text-amber-900' : 'bg-rose-50 border-rose-200 text-rose-900')) }}">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                        </div>
                        <div>
                            <p class="font-bold text-sm">Hasil Skor Kredit Pefindo: <span class="text-base font-extrabold">{{ $screening->pefindo_score }}</span></p>
                            <p class="text-xs opacity-90">Kategori: <strong>{{ $screening->pefindo_grade ?? '-' }}</strong></p>
                        </div>
                    </div>
                    @if($screening->pefindo_file)
                        <a href="{{ asset('storage/' . $screening->pefindo_file) }}" target="_blank" class="px-3 py-1.5 bg-white border rounded-lg text-xs font-bold shadow-sm hover:bg-gray-50 transition inline-flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                            <span>Bukti Skor Pefindo</span>
                        </a>
                    @endif
                </div>
                @if($screening->pefindo_notes)
                    <p class="text-xs mt-2 pt-2 border-t border-black/10"><strong>Catatan Analis:</strong> {{ $screening->pefindo_notes }}</p>
                @endif
            </div>
        @endif

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6 text-xs text-gray-600 bg-gray-50 p-4 rounded-xl">
            @if($screening->loan_amount && $screening->loan_amount > 0)
                <div>
                    <span class="block text-gray-400">Plafon Pengajuan</span>
                    <span class="font-bold text-sm text-gray-900">Rp {{ number_format($screening->loan_amount, 0, ',', '.') }}</span>
                </div>
                <div>
                    <span class="block text-gray-400">Jangka Waktu (Tenor)</span>
                    <span class="font-bold text-sm text-gray-900">{{ $screening->tenor_years }} Tahun</span>
                </div>
            @else
                <div>
                    <span class="block text-gray-400">Pilihan Unit</span>
                    <span class="font-bold text-sm text-amber-700">Tahap Berikutnya (Setelah Screening)</span>
                </div>
                <div>
                    <span class="block text-gray-400">Pilihan Perumahan</span>
                    <span class="font-bold text-sm text-gray-900">{{ $screening->nupRegistration->housingPreferences->first()?->housing_name ?? '-' }}</span>
                </div>
            @endif
            <div>
                <span class="block text-gray-400">Penghasilan Pemohon</span>
                <span class="font-bold text-sm text-gray-900">{{ $screening->monthly_income > 0 ? 'Rp ' . number_format($screening->monthly_income, 0, ',', '.') : 'Tersinkronisasi NUP' }}</span>
            </div>
            <div>
                <span class="block text-gray-400">Status Berkas</span>
                <span class="font-bold text-sm text-gray-900">{{ $screening->userDocuments->count() }} Berkas Diunggah</span>
            </div>
        </div>
    </div>

    <!-- Card 2: Biaya Administrasi Screening Manual (Pefindo IdScore & SLIK) -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-6-10.5h16.5A2.25 2.25 0 0121.75 8v8a2.25 2.25 0 01-2.25 2.25H4.5A2.25 2.25 0 012.25 16V8A2.25 2.25 0 014.5 5.75z"/></svg>
                </span>
                <div>
                    <h2 class="text-base font-bold text-gray-900">Biaya Administrasi Screening Manual (Pefindo / BI Checking)</h2>
                    <p class="text-xs text-gray-500">Biaya resmi pengecekan riwayat kredit pemohon ke biro kredit berlisensi OJK</p>
                </div>
            </div>
            <div>
                @if($screening->isScreeningFeeVerified())
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                        <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                        LUNAS &middot; TERVERIFIKASI
                    </span>
                @elseif($screening->isScreeningFeePending())
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-300">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        MENUNGGU VERIFIKASI OPERATOR
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300">
                        <span class="w-2 h-2 rounded-full bg-rose-600"></span>
                        BELUM DIBAYAR
                    </span>
                @endif
            </div>
        </div>

        <!-- Rincian Biaya -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 my-4">
            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 text-xs space-y-2">
                <span class="font-bold text-slate-800 uppercase tracking-wide block">Rincian Komponen Biaya:</span>
                <div class="flex justify-between text-slate-600">
                    <span>Biaya Pokok Analisis Screening:</span>
                    <span class="font-bold text-slate-900">Rp {{ number_format($feeBreakdown['base'] ?? 150000, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Pajak Pertambahan Nilai (PPN 11%):</span>
                    <span class="font-bold text-slate-900">+ Rp {{ number_format($feeBreakdown['ppn'] ?? 16500, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Value Enhancement (VE 20%):</span>
                    <span class="font-bold text-slate-900">+ Rp {{ number_format($feeBreakdown['ve'] ?? 30000, 0, ',', '.') }}</span>
                </div>
                <div class="pt-2 border-t border-slate-200 flex justify-between items-center text-sm font-black text-slate-900">
                    <span>Total Biaya Administrasi:</span>
                    <span class="text-blue-700 text-base">Rp {{ number_format($feeBreakdown['total'] ?? 196500, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Status & Keterangan Pembayaran -->
            <div class="flex flex-col justify-center">
                @if($screening->isScreeningFeeVerified())
                    <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-900">
                        <p class="font-bold flex items-center gap-1.5 mb-1 text-sm text-emerald-800">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Pembayaran Terverifikasi</span>
                        </p>
                        <p class="leading-relaxed">
                            Biaya screening telah diverifikasi oleh <strong>{{ $screening->screening_fee_verified_by ?? 'Operator' }}</strong> pada {{ $screening->screening_fee_verified_at?->format('d/m/Y H:i') }}. Operator sedang/telah memproses penarikan skor kredit Pefindo Anda.
                        </p>
                        @if($screening->screening_fee_proof)
                            <a href="{{ asset('storage/' . $screening->screening_fee_proof) }}" target="_blank" class="mt-2.5 inline-flex items-center gap-1 font-bold text-blue-600 hover:underline">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>Lihat Bukti Transfer yang Diverifikasi</span>
                            </a>
                        @endif
                    </div>
                @elseif($screening->isScreeningFeePending())
                    <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-900">
                        <p class="font-bold flex items-center gap-1.5 mb-1 text-sm text-amber-800">
                            <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Bukti Transfer Sedang Ditinjau</span>
                        </p>
                        <p class="leading-relaxed">
                            Bukti pembayaran Anda berhasil diunggah pada {{ $screening->screening_fee_paid_at?->format('d/m/Y H:i') }}. Operator sedang mencocokkan dana masuk. Begitu diverifikasi, penarikan skor kredit Pefindo akan langsung dijalankan.
                        </p>
                        @if($screening->screening_fee_proof)
                            <div class="mt-3 flex items-center gap-3">
                                <a href="{{ asset('storage/' . $screening->screening_fee_proof) }}" target="_blank" class="px-3 py-1.5 bg-white border border-amber-300 rounded-lg font-bold text-amber-900 hover:bg-amber-100 transition inline-flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                                    <span>Lihat Struk yang Diunggah</span>
                                </a>
                                <button type="button" onclick="document.getElementById('reupload_fee_form').classList.toggle('hidden')" class="text-blue-600 font-semibold hover:underline">
                                    Unggah Ulang Bukti Lain?
                                </button>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-900">
                        <p class="font-bold flex items-center gap-1.5 mb-1 text-sm text-blue-800">
                            <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
                            <span>Petunjuk Pembayaran</span>
                        </p>
                        <p class="leading-relaxed">
                            Silakan transfer nominal <strong>Rp {{ number_format($feeBreakdown['total'] ?? 196500, 0, ',', '.') }}</strong> ke salah satu rekening resmi pengembang di bawah, lalu unggah struk bukti transfer.
                        </p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Form Unggah Bukti & Rekening Transfer (Tampil jika belum bayar atau pending ingin unggah ulang) --}}
        @if(!$screening->isScreeningFeeVerified())
            <div id="reupload_fee_form" class="{{ $screening->isScreeningFeePending() ? 'hidden' : '' }} pt-4 border-t border-gray-100">
                <!-- Info Rekening Bank Pengembang -->
                @if(isset($bankAccounts) && $bankAccounts->count() > 0)
                    <div class="mb-4">
                        <span class="text-xs font-bold text-gray-700 uppercase tracking-wide block mb-2">Rekening Resmi Pengembang Tujuan Transfer:</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                            @foreach($bankAccounts as $acc)
                                <div class="bg-gray-50 p-3 rounded-xl border border-gray-200 text-xs">
                                    <div class="flex items-center justify-between gap-1 mb-1">
                                        <span class="px-2 py-0.5 bg-blue-100 text-blue-800 font-bold rounded text-[11px]">{{ $acc->bank_name }}</span>
                                        <button type="button" onclick="navigator.clipboard.writeText('{{ $acc->account_number }}'); alert('Nomor rekening disalin: {{ $acc->account_number }}');" class="text-blue-600 font-semibold text-[11px] hover:underline">Salin</button>
                                    </div>
                                    <div class="font-mono font-black text-gray-900 text-sm tracking-wider">{{ $acc->account_number }}</div>
                                    <div class="text-[11px] text-gray-500 truncate mt-0.5">a.n. <strong class="text-gray-700">{{ $acc->account_holder_name }}</strong></div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Form Upload Struk Transfer Biaya Screening -->
                <form action="{{ route('kpr.upload-screening-fee', $screening->id) }}" method="POST" enctype="multipart/form-data" class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                    @csrf
                    <span class="text-xs font-bold text-gray-800 block mb-2">Unggah Bukti Transfer Biaya Administrasi Screening:</span>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs mb-3">
                        <div>
                            <label class="block text-gray-600 mb-1 font-semibold">Rekening Bank Tujuan</label>
                            <select name="developer_bank_account_id" class="w-full border border-gray-300 rounded-lg p-2 text-xs bg-white">
                                @if(isset($bankAccounts))
                                    @foreach($bankAccounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->bank_name }} - {{ $acc->account_number }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-600 mb-1 font-semibold">Bank Pengirim Anda</label>
                            <input type="text" name="sender_bank" placeholder="Contoh: BCA / Mandiri / BRI" class="w-full border border-gray-300 rounded-lg p-2 text-xs bg-white" required>
                        </div>
                        <div>
                            <label class="block text-gray-600 mb-1 font-semibold">Nama Pemilik Rekening</label>
                            <input type="text" name="sender_name" value="{{ old('sender_name', auth()->user()->name ?? '') }}" class="w-full border border-gray-300 rounded-lg p-2 text-xs bg-white" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="block text-gray-600 mb-1 font-semibold">Pilih File Foto / PDF Bukti Transfer <span class="text-red-500">*</span></label>
                        <input type="file" name="screening_fee_proof" accept=".pdf,image/*" class="w-full border border-gray-300 rounded-lg p-2 text-xs bg-white focus:outline-none" required>
                        <span class="text-[10px] text-gray-400 mt-0.5 block">Format: JPG, PNG, atau PDF (Maks. 10MB)</span>
                    </div>
                    <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold shadow-md flex items-center justify-center gap-2 transition">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                        <span>Kirim Bukti Transfer & Ajukan Berkas Screening</span>
                    </button>
                </form>
            </div>
        @endif
    </div>

    <!-- Card 3: Dokumen Identitas (Otomatis dari Pendaftaran NUP) -->
    @php
        $nup = $screening->nupRegistration;
    @endphp
    @if($nup)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-base font-bold text-gray-900 mb-1 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.364a4.125 4.125 0 00-6.338 0 .75.75 0 00.564 1.261h5.21a.75.75 0 00.564-1.261z"/></svg>
                <span>Dokumen Identitas & Sipil (Otomatis dari Pendaftaran NUP)</span>
            </h2>
            <p class="text-xs text-gray-500 mb-4 pb-2 border-b border-gray-100">
                Dokumen ini otomatis diambil dari registrasi NUP Anda sebelumnya, sehingga Anda <strong>tidak perlu mengunggahnya kembali</strong>.
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                <!-- KTP -->
                <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 text-xs">
                    <div class="flex items-center justify-between mb-1">
                        <span class="font-bold text-gray-800">E-KTP Pemohon</span>
                        <span class="inline-flex items-center gap-1 text-emerald-600 font-bold">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            Tersedia
                        </span>
                    </div>
                    @if($nup->ktp_file)
                        <a href="{{ asset('storage/' . $nup->ktp_file) }}" target="_blank" class="text-blue-600 hover:underline inline-flex items-center gap-1 mt-1 font-semibold">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            <span>Lihat Berkas KTP</span>
                        </a>
                    @else
                        <span class="text-gray-400">Data NIK User</span>
                    @endif
                </div>

                <!-- KK -->
                <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 text-xs">
                    <div class="flex items-center justify-between mb-1">
                        <span class="font-bold text-gray-800">Kartu Keluarga (KK)</span>
                        <span class="inline-flex items-center gap-1 text-emerald-600 font-bold">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            Tersedia
                        </span>
                    </div>
                    @if($nup->kk_file)
                        <a href="{{ asset('storage/' . $nup->kk_file) }}" target="_blank" class="text-blue-600 hover:underline inline-flex items-center gap-1 mt-1 font-semibold">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            <span>Lihat Berkas KK</span>
                        </a>
                    @else
                        <span class="text-gray-400">Tercatat NUP</span>
                    @endif
                </div>

                <!-- NPWP -->
                <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 text-xs">
                    <div class="flex items-center justify-between mb-1">
                        <span class="font-bold text-gray-800">NPWP Pribadi</span>
                        @if($nup->npwp_file)
                            <span class="inline-flex items-center gap-1 text-emerald-600 font-bold">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                Tersedia
                            </span>
                        @else
                            <span class="text-gray-400">Opsional</span>
                        @endif
                    </div>
                    @if($nup->npwp_file)
                        <a href="{{ asset('storage/' . $nup->npwp_file) }}" target="_blank" class="text-blue-600 hover:underline inline-flex items-center gap-1 mt-1 font-semibold">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            <span>Lihat Berkas NPWP</span>
                        </a>
                    @else
                        <span class="text-gray-400">Tidak dilampirkan</span>
                    @endif
                </div>

                <!-- Buku Nikah / Akta Cerai -->
                <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 text-xs">
                    <div class="flex items-center justify-between mb-1">
                        <span class="font-bold text-gray-800">Buku Nikah / Akta</span>
                        @if($nup->marriage_certificate_file || $nup->divorce_death_certificate_file || $nup->spouse_ktp_file)
                            <span class="inline-flex items-center gap-1 text-emerald-600 font-bold">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                Tersedia
                            </span>
                        @else
                            <span class="text-gray-400">{{ ucfirst(str_replace('_', ' ', $nup->marital_status ?? 'Lajang')) }}</span>
                        @endif
                    </div>
                    @if($nup->marriage_certificate_file)
                        <a href="{{ asset('storage/' . $nup->marriage_certificate_file) }}" target="_blank" class="text-blue-600 hover:underline inline-flex items-center gap-1 mt-1 font-semibold">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            <span>Lihat Buku Nikah</span>
                        </a>
                    @elseif($nup->divorce_death_certificate_file)
                        <a href="{{ asset('storage/' . $nup->divorce_death_certificate_file) }}" target="_blank" class="text-blue-600 hover:underline inline-flex items-center gap-1 mt-1 font-semibold">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            <span>Lihat Akta Cerai/Kematian</span>
                        </a>
                    @else
                        <span class="text-gray-400">Status: {{ ucfirst(str_replace('_', ' ', $nup->marital_status ?? 'single')) }}</span>
                    @endif
                    @if($nup->spouse_ktp_file)
                        <div class="mt-1 pt-1 border-t border-gray-200">
                            <a href="{{ asset('storage/' . $nup->spouse_ktp_file) }}" target="_blank" class="text-blue-600 hover:underline inline-flex items-center gap-1 font-semibold">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                <span>Lihat KTP Pasangan</span>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Card 3: Dokumen Pendukung KPR Sesuai Status Pekerjaan -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-base font-bold text-gray-900 mb-1 flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
            <span>Dokumen Persyaratan KPR</span>
            <span class="text-xs font-normal text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full">
                Khusus: {{ \App\Models\KprScreening::getOccupationLabel($screening->nupRegistration->occupation ?? $screening->user->occupation ?? $screening->employment_status, $screening->nupRegistration->occupation_other ?? null) }}
            </span>
        </h2>
        <p class="text-xs text-gray-500 mb-4 pb-2 border-b border-gray-100">
            Berikut adalah daftar berkas wajib sesuai profesi Anda. Pastikan berkas jelas, terbaca, dan mutakhir (6 bulan terakhir).
        </p>

        <!-- Daftar Dokumen yang Dibutuhkan -->
        <div class="space-y-3 mb-6">
            @foreach($requiredDocs as $docType => $docTitle)
                @php
                    $uploadedDoc = $screening->userDocuments->where('document_type', $docType)->sortByDesc('created_at')->first();
                @endphp
                <div class="p-4 rounded-xl border transition
                    @if($uploadedDoc && $uploadedDoc->status === 'approved') bg-emerald-50/40 border-emerald-300
                    @elseif($uploadedDoc && $uploadedDoc->status === 'rejected') bg-rose-50/50 border-rose-300
                    @elseif($uploadedDoc && $uploadedDoc->status === 'pending') bg-amber-50/40 border-amber-300
                    @else bg-gray-50 border-gray-200 @endif
                ">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-sm text-gray-900">{{ $docTitle }}</span>
                                <span class="text-[11px] px-2 py-0.5 rounded font-bold uppercase
                                    @if($uploadedDoc && $uploadedDoc->status === 'approved') bg-emerald-100 text-emerald-800
                                    @elseif($uploadedDoc && $uploadedDoc->status === 'rejected') bg-rose-100 text-rose-800
                                    @elseif($uploadedDoc && $uploadedDoc->status === 'pending') bg-amber-100 text-amber-800
                                    @else bg-gray-200 text-gray-600 @endif
                                ">
                                    {{ $uploadedDoc ? ($uploadedDoc->status === 'approved' ? 'Disetujui' : ($uploadedDoc->status === 'rejected' ? 'Perlu Revisi' : 'Menunggu Verifikasi')) : 'Belum Diunggah' }}
                                </span>
                            </div>
                            @if($uploadedDoc)
                                <p class="text-xs text-gray-500 mt-1">
                                    File: <span class="font-mono text-gray-700">{{ $uploadedDoc->document_name }}</span> 
                                    ({{ number_format($uploadedDoc->file_size / 1024, 1) }} KB) • Diunggah: {{ $uploadedDoc->uploaded_at->format('d/m/Y H:i') }}
                                </p>
                            @else
                                <p class="text-xs text-gray-400 mt-1">Wajib dilampirkan untuk verifikasi analis KPR bank.</p>
                            @endif
                        </div>

                        <div class="flex items-center gap-2 flex-wrap">
                            @if($uploadedDoc)
                                <a href="{{ route('documents.download', $uploadedDoc->id) }}" target="_blank" class="px-3 py-1.5 bg-white border border-gray-300 hover:bg-gray-50 rounded-lg text-xs font-semibold text-gray-700 shadow-sm transition inline-flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                    <span>Unduh Berkas</span>
                                </a>
                            @endif

                            @if(!$isCancelledByUser && (!$uploadedDoc || $uploadedDoc->status === 'rejected'))
                                <button type="button" onclick="openUploadModal('{{ $docType }}', '{{ $docTitle }}')" class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold shadow-sm transition inline-flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                                    <span>{{ $uploadedDoc ? 'Upload Ulang Revisi' : 'Upload File' }}</span>
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Kotak Alasan Revisi jika ditolak -->
                    @if($uploadedDoc && $uploadedDoc->status === 'rejected')
                        <div class="mt-3 p-3 bg-white border border-rose-300 rounded-lg text-xs text-rose-800">
                            <p class="font-bold flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                                <span>Catatan Revisi dari Petugas / Bank:</span>
                            </p>
                            <p class="mt-0.5 text-gray-700">{{ $uploadedDoc->rejection_reason ?? 'Dokumen kurang jelas atau tidak lengkap. Silakan unggah ulang versi yang lebih jelas.' }}</p>

                            <!-- Form Unggah Ulang Dokumen Langsung di Tempat -->
                            <form action="{{ route('kpr.upload', $screening->id) }}" method="POST" enctype="multipart/form-data" class="mt-3 pt-3 border-t border-rose-100 flex flex-col sm:flex-row gap-2">
                                @csrf
                                <input type="hidden" name="document_type" value="{{ $docType }}">
                                <input type="file" name="document" accept=".pdf,image/*" class="border border-rose-200 rounded-lg p-1.5 text-xs bg-rose-50/30 focus:outline-none flex-1" required>
                                <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold shadow-sm transition inline-flex items-center justify-center gap-1.5 whitespace-nowrap">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                                    <span>Unggah File Revisi</span>
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        @if(!$isCancelledByUser)
            <!-- Form Upload Cepat Dokumen Tambahan -->
            <div class="pt-4 border-t border-gray-100">
                <h3 class="text-xs font-bold text-gray-700 mb-2 uppercase tracking-wider">Unggah / Perbarui Dokumen</h3>
                <form action="{{ route('kpr.upload', $screening->id) }}" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-3">
                    @csrf
                    <select name="document_type" id="upload_doc_type" class="border border-gray-300 rounded-xl p-2.5 text-xs sm:w-1/3 focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
                        <option value="">-- Pilih Jenis Dokumen --</option>
                        @foreach($requiredDocs as $k => $lbl)
                            <option value="{{ $k }}">{{ $lbl }}</option>
                        @endforeach
                        <option value="other">Dokumen Pendukung Lainnya</option>
                    </select>
                    <input type="file" name="document" accept=".pdf,image/*" class="border border-gray-300 rounded-xl p-2 text-xs flex-1 bg-white focus:outline-none" required>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition whitespace-nowrap inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                        <span>Unggah Dokumen</span>
                    </button>
                </form>
                <span class="text-[11px] text-gray-400 mt-1.5 block">Format yang didukung: PDF, JPG, PNG (Maksimal 10 MB per berkas).</span>
            </div>
        @endif
    </div>

    <!-- Card 4: Tindakan & Langkah Berikutnya -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            <h3 class="text-sm font-bold text-gray-900">Langkah Pengajuan KPR</h3>
            <p class="text-xs text-gray-500 mt-0.5">
                @if($isCancelledByUser)
                    Pengajuan KPR telah dibatalkan secara mandiri oleh pemohon. Tiket antrean NUP terkait telah hangus.
                @elseif($screening->status === 'approved' || $screening->status === 'layak_bersyarat')
                    Skor Pefindo Anda telah keluar dan disetujui. Silakan lanjut memilih unit atau skema pembayaran.
                @elseif($screening->status === 'rejected')
                    Pengajuan KPR Anda dinyatakan belum layak oleh analis bank. Anda dapat beralih ke skema In-House atau Tunai.
                @elseif($screening->status === 'submitted')
                    Berkas telah dikirim dan sedang dianalisis oleh analis perbankan rekanan.
                @else
                    Pastikan seluruh berkas wajib telah diunggah sebelum submit ke analis bank.
                @endif
            </p>
        </div>

        <div class="flex items-center gap-3 w-full sm:w-auto">
            @if($isCancelledByUser)
                <a href="{{ route('nup.register') }}" class="w-full sm:w-auto text-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold shadow-md transition inline-flex items-center justify-center gap-2">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Daftar NUP Baru</span>
                </a>
                <a href="{{ route('dashboard') }}" class="w-full sm:w-auto text-center px-5 py-3 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-xl text-sm font-semibold transition">
                    Dashboard
                </a>
            @elseif($screening->status === 'pending' || $screening->status === 'requires_document')
                @if(!$screening->isScreeningFeeVerified() && !$screening->isScreeningFeePending())
                    <button type="button" onclick="const f = document.getElementById('reupload_fee_form'); if(f){ f.classList.remove('hidden'); f.scrollIntoView({ behavior: 'smooth', block: 'center' }); }" class="w-full sm:w-auto px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-sm font-bold shadow-md transition inline-flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-6-10.5h16.5A2.25 2.25 0 0121.75 8v8a2.25 2.25 0 01-2.25 2.25H4.5A2.25 2.25 0 012.25 16V8A2.25 2.25 0 014.5 5.75z"/></svg>
                        <span>Bayar Biaya Screening Terlebih Dahulu</span>
                    </button>
                @else
                    <form action="{{ route('kpr.submit', $screening->id) }}" method="POST" class="w-full sm:w-auto">
                        @csrf
                        <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-bold shadow-md transition inline-flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                            <span>Kirim Berkas untuk Dianalisis</span>
                        </button>
                    </form>
                @endif
            @elseif($screening->status === 'submitted' || $screening->status === 'processing')
                <div class="px-5 py-2.5 bg-blue-50 text-blue-800 border border-blue-200 rounded-xl text-xs font-bold flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-600 shrink-0 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Bukti Transfer & Berkas Sedang Ditinjau Petugas</span>
                </div>
            @endif

            @if(in_array($screening->status, ['approved', 'layak_bersyarat']))
                @php
                    $showUnitSel = $screening->unitSelection ?? $screening->nupRegistration?->unitSelections()->latest()->first();
                @endphp
                @if($showUnitSel)
                    @php
                        $showSpr = $showUnitSel->sprDocuments()->whereNotIn('status', ['cancelled'])->first();
                    @endphp
                    @if($showSpr)
                        <a href="{{ route('spr.show', $showSpr->id) }}" class="w-full sm:w-auto text-center px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-bold shadow-md transition inline-flex items-center justify-center gap-2">
                            <span>Tinjau Dokumen SPR</span>
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                        </a>
                    @else
                        <a href="{{ route('units.show', $showUnitSel->id) }}" class="w-full sm:w-auto text-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold shadow-md transition inline-flex items-center justify-center gap-2">
                            <span>Lihat Detail Unit & Pembayaran UTK</span>
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                        </a>
                    @endif
                @elseif($screening->hasParticipantDecision())
                    <a href="{{ route('units.create', $screening->nup_registration_id) }}" class="w-full sm:w-auto text-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold shadow-md transition inline-flex items-center justify-center gap-2">
                        <span>Pilih Unit Rumah</span>
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </a>
                @else
                    <a href="{{ route('kpr.outcome', $screening->id) }}" class="w-full sm:w-auto text-center px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-bold shadow-md transition inline-flex items-center justify-center gap-2">
                        <span>Buka Hasil Skor & Langkah KPR</span>
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </a>
                @endif
            @elseif($screening->status === 'rejected')
                <a href="{{ route('kpr.outcome', $screening->id) }}" class="w-full sm:w-auto text-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-md transition inline-flex items-center justify-center gap-2">
                    <span>Alihkan ke Skema In-House / Tunai</span>
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                </a>
            @endif
        </div>
    </div>
</div>

<script>
function openUploadModal(docType, docTitle) {
    const select = document.getElementById('upload_doc_type');
    if (select) {
        select.value = docType;
        select.focus();
        select.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}
</script>
@endsection
