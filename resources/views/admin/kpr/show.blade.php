@extends('layouts.admin')

@section('title', 'Tinjau Dokumen & Skor Pefindo KPR')

@section('content')
<div class="max-w-6xl mx-auto py-6 px-4">
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('admin.kpr.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-blue-600 hover:text-blue-800 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            Kembali ke Manajemen Screening KPR
        </a>
        <span class="text-xs text-gray-500 font-mono">No: {{ $screening->screening_number }}</span>
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

    <!-- Card 1: Data Pemohon & Pinjaman -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-gray-100">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Verifikasi Dokumen & Skor Kredit Pefindo</h1>
                <p class="text-xs text-gray-500 mt-1">
                    Pemohon: <span class="font-bold text-gray-800">{{ $screening->user->name }}</span> ({{ $screening->user->email }} | {{ $screening->user->phone ?? '-' }})
                </p>
            </div>
            @php
                $isCancelledByUser = ($screening->participant_decision === 'not_agree' || $screening->rejection_reason === 'User membatalkan pengajuan KPR');
                $isSwitchedToInhouse = ($screening->participant_decision === 'switch_to_inhouse' || ($screening->nupRegistration?->kpr_switched && in_array($screening->nupRegistration?->payment_method, ['inhouse', 'installment'], true)));
                $isSwitchedToCash = ($screening->participant_decision === 'switch_to_cash' || ($screening->nupRegistration?->kpr_switched && in_array($screening->nupRegistration?->payment_method, ['cash', 'tunai'], true)));
                $isContinueKpr = in_array($screening->participant_decision, ['select_unit', 'agree_utk'], true);
                $hasDecision = ($isCancelledByUser || $isSwitchedToInhouse || $isSwitchedToCash || $isContinueKpr);
            @endphp
            <div>
                <span class="inline-flex items-center px-3.5 py-1.5 rounded-full text-xs font-bold
                    @if($isSwitchedToInhouse) bg-indigo-100 text-indigo-800 border border-indigo-300
                    @elseif($isSwitchedToCash) bg-emerald-100 text-emerald-800 border border-emerald-300
                    @elseif($isCancelledByUser) bg-slate-100 text-slate-700 border border-slate-300
                    @elseif($screening->status === 'approved') bg-emerald-100 text-emerald-800 border border-emerald-300
                    @elseif($screening->status === 'layak_bersyarat') bg-amber-100 text-amber-800 border border-amber-300
                    @elseif($screening->status === 'rejected') bg-rose-100 text-rose-800 border border-rose-300
                    @elseif($screening->status === 'processing') bg-blue-100 text-blue-800 border border-blue-300
                    @else bg-gray-100 text-gray-700 border border-gray-300 @endif
                ">
                    @if($isSwitchedToInhouse) BERALIH KE IN-HOUSE
                    @elseif($isSwitchedToCash) BERALIH KE TUNAI
                    @elseif($isCancelledByUser) DIBATALKAN OLEH PEMOHON
                    @elseif($screening->status === 'approved' && $isContinueKpr) LANJUT KPR (APPROVED)
                    @elseif($screening->status === 'approved') APPROVED (MENUNGGU PEMOHON)
                    @elseif($screening->status === 'layak_bersyarat') LAYAK BERSYARAT
                    @else Status: {{ strtoupper(str_replace('_', ' ', $screening->status)) }} @endif
                </span>
            </div>
        </div>

        {{-- Banner Status Keputusan Pemohon untuk Operator --}}
        @if($isSwitchedToInhouse)
            <div class="mt-4 p-4 rounded-xl border border-indigo-200 bg-indigo-50/70 text-indigo-950 flex items-start gap-3 text-xs shadow-xs">
                <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                </div>
                <div class="flex-1">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                        <strong class="font-black text-indigo-950 text-sm">Pemohon Beralih ke Skema In-House Developer (Cicilan Pengembang)</strong>
                        <span class="px-2.5 py-0.5 rounded-full bg-indigo-200 text-indigo-900 font-extrabold text-[11px] self-start sm:self-auto">
                            Beralih dari KPR
                        </span>
                    </div>
                    <p class="mt-1 text-indigo-800 leading-relaxed">
                        Pemohon memilih <strong>tidak melanjutkan pengajuan KPR Bank</strong> dan telah mengalihkan tiket antreannya ke skema <strong>Cicilan Langsung ke Pengembang (In-House)</strong>{{ $screening->decision_made_at ? ' pada ' . $screening->decision_made_at->translatedFormat('d F Y, H:i') : '' }}. 
                        Pengurusan berkas ke bank rekanan <strong>tidak perlu diteruskan</strong>. Pembeli saat ini diarahkan untuk memilih unit dan membuat draft SPR In-House.
                    </p>
                </div>
            </div>
        @elseif($isSwitchedToCash)
            <div class="mt-4 p-4 rounded-xl border border-emerald-200 bg-emerald-50/70 text-emerald-950 flex items-start gap-3 text-xs shadow-xs">
                <div class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                </div>
                <div class="flex-1">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                        <strong class="font-black text-emerald-950 text-sm">Pemohon Beralih ke Skema Pembayaran Tunai (Lunas / Bertahap)</strong>
                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-200 text-emerald-900 font-extrabold text-[11px] self-start sm:self-auto">
                            Beralih dari KPR
                        </span>
                    </div>
                    <p class="mt-1 text-emerald-800 leading-relaxed">
                        Pemohon memilih <strong>tidak melanjutkan KPR Bank</strong> dan beralih ke skema <strong>Pembayaran Tunai</strong>{{ $screening->decision_made_at ? ' pada ' . $screening->decision_made_at->translatedFormat('d F Y, H:i') : '' }}. 
                        Pengurusan berkas ke bank rekanan <strong>tidak perlu diteruskan</strong>.
                    </p>
                </div>
            </div>
        @elseif($isCancelledByUser)
            <div class="mt-4 p-4 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 flex items-start gap-3 text-xs shadow-xs">
                <div class="w-8 h-8 rounded-lg bg-slate-400 text-white flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <div class="flex-1">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                        <strong class="font-black text-slate-900 text-sm">Pengajuan Dibatalkan oleh Pemohon</strong>
                        <span class="px-2.5 py-0.5 rounded-full bg-slate-200 text-slate-800 font-extrabold text-[11px] self-start sm:self-auto">
                            Tiket NUP Hangus
                        </span>
                    </div>
                    <p class="mt-1 text-slate-600 leading-relaxed">
                        Pemohon memilih <strong>membatalkan pengajuan KPR secara mandiri</strong>{{ $screening->decision_made_at ? ' pada ' . $screening->decision_made_at->translatedFormat('d F Y, H:i') : '' }}. 
                        Tiket antrean NUP terkait telah dinyatakan <strong>hangus/batal</strong> dan berkas pengajuan ini telah ditutup.
                    </p>
                </div>
            </div>
        @elseif($isContinueKpr)
            <div class="mt-4 p-4 rounded-xl border border-blue-200 bg-blue-50/70 text-blue-950 flex items-start gap-3 text-xs shadow-xs">
                <div class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div class="flex-1">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                        <strong class="font-black text-blue-950 text-sm">Pemohon Mengonfirmasi Lanjut Proses KPR Bank</strong>
                        <span class="px-2.5 py-0.5 rounded-full bg-blue-200 text-blue-900 font-extrabold text-[11px] self-start sm:self-auto">
                            Lanjut KPR
                        </span>
                    </div>
                    <p class="mt-1 text-blue-800 leading-relaxed">
                        Pemohon telah menyetujui hasil skor Pefindo dan <strong>melanjutkan proses pengajuan KPR</strong> ke tahap pemilihan unit kavling dan penerbitan SPR.
                    </p>
                </div>
            </div>
        @elseif(in_array($screening->status, ['approved', 'layak_bersyarat']))
            <div class="mt-4 p-4 rounded-xl border border-amber-200 bg-amber-50/70 text-amber-950 flex items-start gap-3 text-xs shadow-xs">
                <div class="w-8 h-8 rounded-lg bg-amber-500 text-white flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="flex-1">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                        <strong class="font-black text-amber-950 text-sm">Menunggu Keputusan Lanjutan dari Pemohon</strong>
                        <span class="px-2.5 py-0.5 rounded-full bg-amber-200 text-amber-900 font-extrabold text-[11px] self-start sm:self-auto">
                            Belum Memilih Langkah
                        </span>
                    </div>
                    <p class="mt-1 text-amber-800 leading-relaxed">
                        Hasil skor Pefindo telah disetujui. Saat ini sistem sedang <strong>menunggu pemohon</strong> membuka aplikasi untuk memilih: <em>Lanjut KPR</em>, <em>Alih ke In-House</em>, <em>Alih ke Tunai</em>, atau <em>Batal</em>.
                    </p>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-4 text-xs text-gray-600 bg-gray-50 p-4 rounded-xl">
            <div>
                <span class="block text-gray-400">Pilihan Perumahan</span>
                <span class="font-bold text-sm text-gray-900">{{ $screening->nupRegistration?->housingPreferences?->first()?->housing_name ?? $screening->getHousingProject()?->name ?? '-' }}</span>
            </div>
            <div>
                <span class="block text-gray-400">Pilihan Unit</span>
                <span class="font-bold text-sm text-gray-900">{{ $screening->unitSelection?->housingUnit?->unit_number ?? 'Tahap Setelah Screening' }}</span>
            </div>
            <div>
                <span class="block text-gray-400">Rencana Pembayaran</span>
                @if($isSwitchedToInhouse)
                    <div class="flex items-center gap-1.5 flex-wrap mt-0.5">
                        <span class="font-bold text-sm text-indigo-700">In-House Developer</span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-black bg-indigo-100 text-indigo-800">Beralih dari KPR</span>
                    </div>
                @elseif($isSwitchedToCash)
                    <div class="flex items-center gap-1.5 flex-wrap mt-0.5">
                        <span class="font-bold text-sm text-emerald-700">Tunai (Lunas / Bertahap)</span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-black bg-emerald-100 text-emerald-800">Beralih dari KPR</span>
                    </div>
                @elseif($isCancelledByUser)
                    <div class="flex items-center gap-1.5 flex-wrap mt-0.5">
                        <span class="font-bold text-sm text-slate-700 line-through">KPR Bank</span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-black bg-slate-200 text-slate-700">Batal Pemohon</span>
                    </div>
                @else
                    <span class="font-bold text-sm text-gray-900">{{ config('nup.payment_methods.' . ($screening->nupRegistration->payment_method ?? ''), 'KPR Bank / FLPP') }}</span>
                @endif
            </div>
            <div>
                <span class="block text-gray-400">Profesi / Pekerjaan</span>
                <span class="font-bold text-sm text-gray-900">{{ \App\Models\KprScreening::getOccupationLabel($screening->user->occupation ?? $screening->employment_status, $screening->nupRegistration?->occupation_other ?? null) }}</span>
            </div>
        </div>

        @if($screening->bank)
            <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
                <span class="text-gray-500">Rekomendasi Bank KPR (Ditentukan Developer):</span>
                <span class="px-2.5 py-1 bg-blue-100 text-blue-800 font-bold rounded-lg">
                    {{ $screening->bank === 'other' ? ($screening->bank_other ?: 'Bank Lainnya') : ($kprBanks[$screening->bank] ?? $screening->bank) }}
                </span>
            </div>
        @endif
    </div>

    <!-- Card 2: Dokumen Identitas NUP Pemohon -->
    @php
        $nup = $screening->nupRegistration;
    @endphp
    @if($nup)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-sm font-bold text-gray-900 mb-1 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.364a4.125 4.125 0 00-6.338 0 .75.75 0 00.564 1.261h5.21a.75.75 0 00.564-1.261z"/></svg>
                <span>Dokumen Identitas Pemohon (Data NUP Tersimpan)</span>
            </h2>
            <p class="text-xs text-gray-500 mb-4 pb-2 border-b border-gray-100">
                Dokumen identitas telah tersimpan dari pendaftaran NUP.
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 text-xs">
                    <span class="font-bold text-gray-800 block mb-1">E-KTP</span>
                    @if($nup->ktp_file)
                        <a href="{{ asset('storage/' . $nup->ktp_file) }}" target="_blank" class="text-blue-600 font-bold hover:underline inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            <span>Lihat Berkas KTP</span>
                        </a>
                    @else
                        <span class="text-gray-400">Tercatat NIK: {{ $screening->user->nik ?? '-' }}</span>
                    @endif
                </div>

                <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 text-xs">
                    <span class="font-bold text-gray-800 block mb-1">Kartu Keluarga (KK)</span>
                    @if($nup->kk_file)
                        <a href="{{ asset('storage/' . $nup->kk_file) }}" target="_blank" class="text-blue-600 font-bold hover:underline inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            <span>Lihat Berkas KK</span>
                        </a>
                    @else
                        <span class="text-gray-400">Belum diunggah</span>
                    @endif
                </div>

                <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 text-xs">
                    <span class="font-bold text-gray-800 block mb-1">NPWP</span>
                    @if($nup->npwp_file)
                        <a href="{{ asset('storage/' . $nup->npwp_file) }}" target="_blank" class="text-blue-600 font-bold hover:underline inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            <span>Lihat Berkas NPWP</span>
                        </a>
                    @else
                        <span class="text-gray-400">Tidak ada</span>
                    @endif
                </div>

                <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 text-xs">
                    <span class="font-bold text-gray-800 block mb-1">Buku Nikah / Akta Cerai</span>
                    @if($nup->marriage_certificate_file)
                        <a href="{{ asset('storage/' . $nup->marriage_certificate_file) }}" target="_blank" class="text-blue-600 font-bold hover:underline inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            <span>Lihat Buku Nikah</span>
                        </a>
                    @elseif($nup->divorce_death_certificate_file)
                        <a href="{{ asset('storage/' . $nup->divorce_death_certificate_file) }}" target="_blank" class="text-blue-600 font-bold hover:underline inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            <span>Lihat Akta Cerai/Kematian</span>
                        </a>
                    @else
                        <span class="text-gray-400">Status: {{ ucfirst(str_replace('_', ' ', $nup->marital_status ?? 'single')) }}</span>
                    @endif
                    @if($nup->spouse_ktp_file)
                        <div class="mt-1.5 pt-1.5 border-t border-gray-200">
                            <a href="{{ asset('storage/' . $nup->spouse_ktp_file) }}" target="_blank" class="text-blue-600 font-medium hover:underline inline-flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                <span>Lihat KTP Pasangan</span>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Card 3: Verifikasi Dokumen Finansial & Pekerjaan (Karyawan vs Usaha) -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-sm font-bold text-gray-900 mb-1 flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
            <span>Dokumen Persyaratan KPR Pemohon</span>
            <span class="text-xs font-normal text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full">
                Sesuai Profesi: {{ \App\Models\KprScreening::getOccupationLabel($screening->nupRegistration->occupation ?? $screening->user->occupation ?? $screening->employment_status, $screening->nupRegistration->occupation_other ?? null) }}
            </span>
        </h2>
        <p class="text-xs text-gray-500 mb-4 pb-2 border-b border-gray-100">
            Periksa kelengkapan & kejelasan berkas berikut. Anda dapat menyetujui dokumen atau meminta revisi jika buram/kurang.
        </p>

        <div class="space-y-4">
            @foreach($requiredDocs as $docType => $docTitle)
                @php
                    $uploadedDoc = $screening->userDocuments->where('document_type', $docType)->sortByDesc('created_at')->first();
                @endphp
                <div class="p-4 rounded-xl border {{ $uploadedDoc && $uploadedDoc->status === 'approved' ? 'bg-emerald-50/30 border-emerald-300' : ($uploadedDoc && $uploadedDoc->status === 'rejected' ? 'bg-rose-50/40 border-rose-300' : ($uploadedDoc ? 'bg-amber-50/30 border-amber-300' : 'bg-gray-50 border-gray-200')) }}">
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
                                    File: <span class="font-mono text-gray-700">{{ $uploadedDoc->document_name }}</span> ({{ number_format($uploadedDoc->file_size / 1024, 1) }} KB) • Diunggah: {{ $uploadedDoc->uploaded_at->format('d/m/Y H:i') }}
                                </p>
                            @else
                                <p class="text-xs text-gray-400 mt-1">Pemohon belum mengunggah dokumen ini.</p>
                            @endif
                        </div>

                        @if($uploadedDoc)
                            <div class="flex items-center gap-2 flex-wrap">
                                <a href="{{ route('documents.download', $uploadedDoc->id) }}" target="_blank" class="px-3 py-1.5 bg-white border border-gray-300 hover:bg-gray-50 rounded-lg text-xs font-semibold text-gray-700 shadow-sm transition inline-flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                    <span>Unduh File</span>
                                </a>

                                <!-- Tombol Verifikasi Dokumen -->
                                <form action="{{ route('admin.kpr.verify-document', $uploadedDoc->id) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shadow-sm transition inline-flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                        <span>ACC Dokumen</span>
                                    </button>
                                </form>

                                <button type="button" onclick="document.getElementById('reject-box-{{ $uploadedDoc->id }}').classList.toggle('hidden')" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold shadow-sm transition inline-flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                    <span>Minta Revisi</span>
                                </button>
                            </div>
                        @endif
                    </div>

                    <!-- Kotak Form Revisi -->
                    @if($uploadedDoc)
                        <div id="reject-box-{{ $uploadedDoc->id }}" class="{{ $uploadedDoc->status === 'rejected' ? '' : 'hidden' }} mt-3 pt-3 border-t border-gray-200">
                            <form action="{{ route('admin.kpr.verify-document', $uploadedDoc->id) }}" method="POST" class="flex flex-col sm:flex-row gap-2">
                                @csrf
                                <input type="hidden" name="action" value="reject">
                                <input type="text" name="rejection_reason" value="{{ $uploadedDoc->rejection_reason }}" placeholder="Masukkan alasan revisi (misal: tulisan buram, belum legalisir, rekening koran kurang bulan)" class="border border-gray-300 rounded-lg p-2 text-xs flex-1 focus:outline-none focus:ring-2 focus:ring-rose-500" required>
                                <button type="submit" class="px-4 py-2 bg-rose-700 hover:bg-rose-800 text-white rounded-lg text-xs font-bold transition whitespace-nowrap">
                                    Simpan Revisi
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- Card 4: Status & Verifikasi Pembayaran Biaya Screening Manual Pefindo -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-6-10.5h16.5A2.25 2.25 0 0121.75 8v8a2.25 2.25 0 01-2.25 2.25H4.5A2.25 2.25 0 012.25 16V8A2.25 2.25 0 014.5 5.75z"/></svg>
                </span>
                <div>
                    <h2 class="text-base font-bold text-gray-900">Verifikasi Pembayaran Biaya Administrasi Screening</h2>
                    <p class="text-xs text-gray-500">Tagihan penarikan data biro kredit Pefindo IdScore & SLIK pemohon</p>
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
                        MENUNGGU KONFIRMASI OPERATOR
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300">
                        <span class="w-2 h-2 rounded-full bg-rose-600"></span>
                        BELUM DIBAYAR PEMOHON
                    </span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 my-5">
            <!-- Rincian Nilai Tagihan -->
            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 text-xs space-y-2">
                <span class="font-bold text-slate-800 uppercase tracking-wide block">Rincian Perhitungan Tagihan Screening:</span>
                <div class="flex justify-between text-slate-600">
                    <span>Biaya Pokok (diatur pengembang):</span>
                    <span class="font-bold text-slate-900">Rp {{ number_format($feeBreakdown['base'] ?? 150000, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>PPN 11%:</span>
                    <span class="font-bold text-slate-900">+ Rp {{ number_format($feeBreakdown['ppn'] ?? 16500, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Value Enhancement (VE 20%):</span>
                    <span class="font-bold text-slate-900">+ Rp {{ number_format($feeBreakdown['ve'] ?? 30000, 0, ',', '.') }}</span>
                </div>
                <div class="pt-2 border-t border-slate-200 flex justify-between items-center text-sm font-black text-slate-900">
                    <span>Total Tagihan Administrasi:</span>
                    <span class="text-blue-700 text-base">Rp {{ number_format($feeBreakdown['total'] ?? 196500, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Bukti Transfer & Konfirmasi Operator -->
            <div class="flex flex-col justify-between">
                @if($screening->screening_fee_proof)
                    <div class="p-4 bg-white border border-gray-200 rounded-xl text-xs space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-gray-800">Bukti Struk Transfer:</span>
                            <a href="{{ asset('storage/' . $screening->screening_fee_proof) }}" target="_blank" class="px-3 py-1 bg-blue-50 text-blue-700 rounded-lg font-bold hover:bg-blue-100 transition inline-flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                                <span>Buka Struk Asli</span>
                            </a>
                        </div>
                        <div class="text-gray-600">
                            <strong>Info Pengirim:</strong> {{ $screening->screening_fee_notes ?? '-' }}
                        </div>
                        <div class="text-gray-400 text-[11px]">
                            Diunggah pada: {{ $screening->screening_fee_paid_at?->format('d/m/Y H:i') ?? '-' }}
                        </div>

                        <!-- Form Persetujuan / Penolakan Bukti Transfer -->
                        @if(!$screening->isScreeningFeeVerified())
                            <div class="pt-3 border-t border-gray-100 flex items-center gap-2">
                                <form action="{{ route('admin.kpr.verify-screening-fee', $screening->id) }}" method="POST" class="flex-1">
                                    @csrf
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-sm">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                        <span>Konfirmasi Lunas</span>
                                    </button>
                                </form>
                                <form action="{{ route('admin.kpr.verify-screening-fee', $screening->id) }}" method="POST" class="flex-1" onsubmit="return confirm('Tolak bukti transfer biaya screening ini?');">
                                    @csrf
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="w-full py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-sm">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                        <span>Tolak Struk</span>
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="p-2 bg-emerald-50 rounded-lg text-emerald-800 text-[11px] font-semibold mt-2 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span>Telah diverifikasi oleh <strong>{{ $screening->screening_fee_verified_by }}</strong> pada {{ $screening->screening_fee_verified_at?->format('d/m/Y H:i') }}.</span>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="p-4 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-500 flex flex-col justify-center items-center text-center h-full">
                        <svg class="w-8 h-8 text-gray-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="font-bold text-gray-700">Belum Ada Bukti Pembayaran</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">Pemohon belum mengunggah struk transfer biaya administrasi screening.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Card 5: Input Hasil Skor Kredit Pefindo & Keputusan Status KPR -->
    @php
        $allDocsApproved = $screening->areAllRequiredDocumentsApproved();
        $feeVerified = $screening->isScreeningFeeVerified();
        $canInputPefindo = $allDocsApproved && $feeVerified;
        $unapprovedDocs = $screening->getUnapprovedRequiredDocuments();
    @endphp
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between gap-3 mb-1">
            <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                <span>Input Hasil Skor Kredit Pefindo & Keputusan Screening KPR</span>
            </h2>
            @if(!$canInputPefindo)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-300">
                    <svg class="w-3.5 h-3.5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                    <span>TERKUNCI</span>
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>SIAP DIINPUT</span>
                </span>
            @endif
        </div>
        <p class="text-xs text-gray-500 mb-4 pb-2 border-b border-gray-100">
            Setelah analis melakukan pengecekan SLIK OJK / Pefindo, masukkan skor kredit dan tetapkan kelayakan pengajuan KPR pemohon.
        </p>

        @if(!$allDocsApproved)
            <div class="mb-4 p-4 bg-amber-50 border border-amber-300 rounded-xl text-xs text-amber-900">
                <div class="flex items-center gap-2 font-bold text-amber-950 mb-1">
                    <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                    <span>Form Terkunci: Masih Ada Berkas Persyaratan yang Belum di-ACC</span>
                </div>
                <p class="text-amber-800 mb-2">
                    Skor Pefindo & Keputusan hanya dapat diisi jika <strong>seluruh berkas persyaratan pemohon di Card 3 telah disetujui (ACC)</strong> oleh developer.
                </p>
                <div class="bg-white/90 p-3 rounded-lg border border-amber-200 space-y-1.5">
                    <span class="font-bold text-gray-700 block text-[11px] uppercase tracking-wide">Berkas yang Perlu Ditindaklanjuti:</span>
                    @foreach($unapprovedDocs as $uDoc)
                        <div class="flex items-center justify-between text-xs py-0.5">
                            <span class="text-gray-800 font-semibold">• {{ $uDoc['title'] }}</span>
                            <span class="text-amber-700 font-medium text-[11px] bg-amber-100/70 px-2 py-0.5 rounded">{{ $uDoc['reason'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if(!$feeVerified)
            <div class="mb-4 p-3.5 bg-rose-50 border border-rose-300 rounded-xl text-xs text-rose-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                <span><strong>Pembayaran Screening Belum Lunas:</strong> Verifikasi bukti transfer biaya screening di Card 4 terlebih dahulu sebelum memproses skor Pefindo.</span>
            </div>
        @elseif($allDocsApproved)
            <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span><strong>Semua Syarat Terpenuhi:</strong> Semua berkas telah disetujui (ACC) dan biaya screening lunas. Anda dapat menginput skor Pefindo serta menetapkan keputusan.</span>
            </div>
        @endif

        <form action="{{ route('admin.kpr.pefindo', $screening->id) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <fieldset {{ $canInputPefindo ? '' : 'disabled' }} class="{{ $canInputPefindo ? '' : 'opacity-50 pointer-events-none select-none' }}">
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Skor Kredit Pefindo (Angka)</label>
                        <input type="text" name="pefindo_score" value="{{ old('pefindo_score', $screening->pefindo_score ?? '750') }}" placeholder="Contoh: 750 / 800" class="w-full border border-gray-300 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
                        <span class="text-[11px] text-gray-400 mt-1 block">Skala skor Pefindo (250 - 900)</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Kategori / Grade Pefindo</label>
                        <input type="text" name="pefindo_grade" value="{{ old('pefindo_grade', $screening->pefindo_grade ?? 'Grade 1 - Sangat Baik (Risiko Rendah)') }}" placeholder="Contoh: Grade 1 - Sangat Baik" class="w-full border border-gray-300 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
                        <span class="text-[11px] text-gray-400 mt-1 block">Kolektibilitas / tingkat risiko kredit</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Keputusan Kelayakan KPR</label>
                        <select name="status" class="w-full border border-gray-300 rounded-xl p-2.5 text-sm font-bold focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
                            <option value="approved" {{ old('status', $screening->status) === 'approved' ? 'selected' : '' }} class="text-emerald-700 font-bold">
                                LAYAK PROSES KPR (HIJAU)
                            </option>
                            <option value="layak_bersyarat" {{ old('status', $screening->status) === 'layak_bersyarat' ? 'selected' : '' }} class="text-amber-700 font-bold">
                                LAYAK BERSYARAT (KUNING)
                            </option>
                            <option value="rejected" {{ old('status', $screening->status) === 'rejected' ? 'selected' : '' }} class="text-rose-700 font-bold">
                                TIDAK LAYAK KPR (MERAH)
                            </option>
                        </select>
                        <span class="text-[11px] text-gray-400 mt-1 block">Status hasil untuk user</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Rekomendasi Bank KPR (Developer)</label>
                        <select name="bank" id="admin_bank_select" onchange="toggleAdminBankOther(this.value)" class="w-full border border-gray-300 rounded-xl p-2.5 text-sm font-semibold focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white">
                            <option value="">-- Tentukan Nanti / Pilih Bank --</option>
                            @foreach($kprBanks as $k => $b)
                                <option value="{{ $k }}" {{ old('bank', $screening->bank) === $k ? 'selected' : '' }}>{{ $b }}</option>
                            @endforeach
                        </select>
                        <span class="text-[11px] text-gray-400 mt-1 block">Rekomendasi bank oleh developer</span>

                        <div id="admin_bank_other_wrap" class="{{ old('bank', $screening->bank) === 'other' ? '' : 'hidden' }} mt-2">
                            <label class="block text-[11px] font-bold text-gray-700 mb-1">Nama Bank Lainnya (Ketik Manual):</label>
                            <input type="text" name="bank_other" id="admin_bank_other_input" value="{{ old('bank_other', $screening->bank_other) }}" placeholder="Contoh: Bank Danamon / Bank Jatim / dll." class="w-full border border-blue-300 rounded-xl p-2 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none bg-blue-50/20 font-semibold">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Upload Bukti Cetak Skor Pefindo (PDF/Gambar)</label>
                        <input type="file" name="pefindo_file" accept=".pdf,image/*" class="w-full border border-gray-300 rounded-xl p-2 text-xs bg-white focus:outline-none">
                        @if($screening->pefindo_file)
                            <a href="{{ asset('storage/' . $screening->pefindo_file) }}" target="_blank" class="text-blue-600 text-xs font-semibold hover:underline mt-1 inline-flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>Lihat file skor yang sudah diunggah</span>
                            </a>
                        @endif
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Catatan Analis / Catatan Bank</label>
                        <textarea name="pefindo_notes" rows="2" class="w-full border border-gray-300 rounded-xl p-2.5 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Catatan analisa kredit, batas DSR, atau syarat pelunasan hutang sebelumnya...">{{ old('pefindo_notes', $screening->pefindo_notes) }}</textarea>
                    </div>
                </div>
            </fieldset>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                @if($canInputPefindo)
                    <button type="submit" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold shadow-lg transition inline-flex items-center gap-2">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4.5A1.5 1.5 0 014.5 3h12.379a1.5 1.5 0 011.06.44l3.621 3.62a1.5 1.5 0 01.44 1.061V19.5a1.5 1.5 0 01-1.5 1.5H4.5A1.5 1.5 0 013 19.5V4.5zM6 3v4.5h9V3M6 21v-7.5h12V21"/></svg>
                        <span>Simpan Hasil Skor & Beri Notifikasi ke Pemohon</span>
                    </button>
                @else
                    <button type="button" disabled class="px-8 py-3 bg-gray-200 text-gray-500 rounded-xl text-sm font-bold cursor-not-allowed inline-flex items-center gap-2 border border-gray-300">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                        <span>Form Terkunci (ACC Semua Berkas Terlebih Dahulu)</span>
                    </button>
                @endif
            </div>
        </form>
    </div>
</div>

<script>
function toggleAdminBankOther(val) {
    const wrap = document.getElementById('admin_bank_other_wrap');
    const input = document.getElementById('admin_bank_other_input');
    if (wrap) {
        if (val === 'other') {
            wrap.classList.remove('hidden');
            if (input) input.focus();
        } else {
            wrap.classList.add('hidden');
        }
    }
}
</script>
@endsection
