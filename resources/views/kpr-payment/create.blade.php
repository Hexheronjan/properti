@extends('layouts.app')

@push('styles')
<style>
    /* Hilangkan panah spinner up/down bawaan browser */
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { 
        -webkit-appearance: none; 
        margin: 0; 
    }
    input[type=number] {
        -moz-appearance: textfield;
    }
</style>
@endpush

@section('title', 'Bayar Cicilan / Lunasi')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div>
        <a href="{{ route('kpr-payment.list') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-blue-600 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Kembali ke Pembayaran Rumah
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        {{-- Header --}}
        <div class="px-6 py-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100/80 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 003.75 19.5z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-slate-900 tracking-tight">
                        @if(!$isDpPaid)
                            Pembayaran Uang Muka (DP) Rumah
                        @elseif($spr->payment_scheme === 'cash')
                            Pembayaran Pelunasan Rumah (Tunai Keras)
                        @elseif($spr->payment_scheme === 'inhouse')
                            Pembayaran Cicilan In-House & Pelunasan
                        @elseif($spr->payment_scheme === 'tunai_bertahap')
                            Pembayaran Tunai Bertahap & Pelunasan
                        @else
                            Pembayaran Tagihan Rumah
                        @endif
                    </h1>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $spr->spr_number }} &middot; {{ $spr->unitSelection->housingUnit->full_identifier ?? '-' }}</p>
                </div>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 text-blue-700 rounded-full font-semibold text-xs border border-blue-200/80 self-start sm:self-auto">
                <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                Transfer Bank Manual
            </span>
        </div>

        <div class="p-6">
            {{-- Informasi SPR --}}
            <div class="bg-slate-50/70 border border-slate-200/80 rounded-xl p-4 mb-6">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                    <h3 class="font-bold text-slate-700 text-xs uppercase tracking-wider">Informasi Dokumen SPR</h3>
                </div>
                @php
                    $tenorMonths = (int) ($spr->tenor_months ?: ($spr->getProject()?->inhouse_max_tenor_months ?? 12));
                @endphp
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    <div>
                        <p class="text-slate-400 font-medium">No. SPR</p>
                        <p class="font-bold text-slate-800 font-mono mt-0.5">{{ $spr->spr_number }}</p>
                    </div>
                    <div>
                        <p class="text-slate-400 font-medium">Unit Terpilih</p>
                        <p class="font-bold text-slate-800 mt-0.5">{{ $spr->unitSelection->housingUnit->full_identifier ?? '-' }}</p>
                    </div>
                    <div>
                        @if($spr->payment_scheme === 'cash')
                            <p class="text-slate-400 font-medium">Batas Waktu Pelunasan</p>
                            <p class="font-bold text-emerald-700 mt-0.5">
                                {{ $spr->due_date ? $spr->due_date->format('d/m/Y') : (($spr->getProject()?->cash_due_days ?? 30) . ' Hari') }}
                                <span class="text-[10px] text-slate-500 font-normal block">(Tunai Keras / Lunas)</span>
                            </p>
                        @else
                            <p class="text-slate-400 font-medium">Tenor Angsuran</p>
                            <p class="font-bold text-blue-700 mt-0.5">
                                {{ $tenorMonths }} Bulan
                                @if(in_array($spr->payment_scheme, ['inhouse', 'installment']))
                                    <span class="text-[10px] text-slate-500 font-normal block">(Angsuran ke-{{ $lastInstallment + 1 }} dari {{ $tenorMonths }})</span>
                                @endif
                            </p>
                        @endif
                    </div>
                    <div>
                        <p class="text-slate-400 font-medium">{{ !$isDpPaid ? 'Kewajiban Uang Muka (DP)' : 'Sisa Tagihan Kewajiban' }}</p>
                        <p class="font-bold text-slate-900 text-sm mt-0.5">
                            Rp {{ number_format(!$isDpPaid ? $spr->down_payment : $remainingAmount, 0, ',', '.') }}
                        </p>
                        @if(!$isDpPaid)
                            <p class="text-[10px] text-slate-400 mt-0.5">Sisa pokok unit: Rp {{ number_format($remainingAmount, 0, ',', '.') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            @if(isset($overdueDetails) && $overdueDetails['is_overdue'])
                <div class="bg-rose-50 border-2 border-rose-300 rounded-2xl p-5 mb-6 text-slate-900 shadow-sm">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0 shadow-sm mt-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div class="flex-1">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <h4 class="font-black text-rose-950 text-sm">Pemberitahuan: Pembayaran Melewati Batas Jatuh Tempo</h4>
                                <span class="px-2.5 py-0.5 rounded-full bg-rose-200 text-rose-800 font-extrabold text-[11px] self-start sm:self-auto">
                                    @if($spr->payment_scheme === 'cash')
                                        Telat {{ $overdueDetails['days_overdue'] }} Hari
                                    @else
                                        Telat {{ $overdueDetails['days_overdue'] }} Hari ({{ $overdueDetails['months_overdue'] }} bln kalender)
                                    @endif
                                </span>
                            </div>
                            <p class="text-xs text-rose-800 mt-1">
                                Tagihan ini melewati tanggal jatuh tempo (<strong>{{ $overdueDetails['due_date']?->translatedFormat('d F Y') }}</strong>). 
                                Sesuai ketentuan operator pengembang, dikenakan denda otomatis sebesar <strong>{{ $overdueDetails['penalty_percent'] }}% / {{ $overdueDetails['penalty_unit'] ?? ($spr->payment_scheme === 'cash' ? 'hari' : 'bulan') }}</strong>.
                            </p>

                            <div class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs">
                                <div class="bg-white/90 p-2.5 rounded-xl border border-rose-200">
                                    <span class="text-slate-500 block text-[10px] uppercase font-bold">Tagihan Pokok</span>
                                    <span class="font-extrabold text-slate-800 text-sm" id="box-pokok-display">Rp {{ number_format($overdueDetails['base_amount'], 0, ',', '.') }}</span>
                                </div>
                                <div class="bg-white/90 p-2.5 rounded-xl border border-rose-200">
                                    <span class="text-rose-600 block text-[10px] uppercase font-bold">Denda Keterlambatan</span>
                                    <span class="font-extrabold text-rose-700 text-sm" id="box-denda-display">+ Rp {{ number_format($overdueDetails['penalty_amount'], 0, ',', '.') }}</span>
                                </div>
                                <div class="bg-rose-100/90 p-2.5 rounded-xl border border-rose-300">
                                    <span class="text-rose-900 block text-[10px] uppercase font-bold">Total Wajib Bayar</span>
                                    <span class="font-black text-rose-950 text-sm" id="box-total-display">Rp {{ number_format($overdueDetails['total_amount'], 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if(isset($errors) && $errors->any())
                <div class="bg-rose-50 border border-rose-200/80 rounded-xl p-4 mb-6">
                    <ul class="text-xs font-medium text-rose-700 space-y-1">
                        @foreach($errors->all() as $error)
                            <li class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                                {{ $error }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @session('success')
                <div class="bg-emerald-50 border border-emerald-200/80 rounded-xl p-4 mb-6 flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <p class="text-xs font-semibold text-emerald-800">{{ session('success') }}</p>
                </div>
            @endsession

            @session('error')
                <div class="bg-rose-50 border border-rose-200/80 rounded-xl p-4 mb-6 flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                    <p class="text-xs font-semibold text-rose-800">{{ session('error') }}</p>
                </div>
            @endsession

            <form action="{{ route('kpr-payment.store', $spr->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div>
                    <label class="block text-slate-700 font-bold text-xs uppercase tracking-wider mb-2">Jenis Pembayaran</label>
                    @if(!$isDpPaid)
                        {{-- HANYA PILIHAN UANG MUKA (DP) KARENA DP BELUM LUNAS / BELUM DISETUJUI ADMIN --}}
                        <input type="hidden" name="payment_type" value="dp">
                        <div class="p-4 border-2 border-blue-600 rounded-xl bg-blue-50/40">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div>
                                    <span class="font-bold text-slate-900 text-sm flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        Uang Muka (DP) Rumah
                                    </span>
                                    <p class="text-xs text-slate-600 mt-1">
                                        Kewajiban Uang Muka yang harus dibayar: <strong class="text-blue-700 font-extrabold text-sm">Rp {{ number_format($spr->down_payment, 0, ',', '.') }}</strong>
                                    </p>
                                    <p class="text-[11px] text-slate-500 mt-0.5">
                                        Pilihan cicilan bulanan dan pelunasan akan otomatis aktif setelah pembayaran Uang Muka (DP) disetujui oleh admin pengembang.
                                    </p>
                                </div>
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-lg font-bold text-xs shrink-0 self-start sm:self-auto border border-blue-200">
                                    Tahap: Pembayaran DP
                                </span>
                            </div>
                        </div>
                    @else
                        {{-- JIKA DP SUDAH LUNAS / DI-ACC: TAMPILKAN PILIHAN SESUAI SKEMA --}}
                        @if($spr->payment_scheme === 'cash')
                            {{-- SKEMA TUNAI (TERMIN MAKSIMAL X HARI ATAU PELUNASAN SEKALIGUS) --}}
                            @php
                                $project = $spr->getProject();
                                $cashDueDays = $project?->cash_due_days ?? 15;
                                $allowCashTermin = (bool) ($project?->cash_allow_installments ?? true);
                            @endphp

                            @if($allowCashTermin)
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <label class="flex items-start p-4 border rounded-xl cursor-pointer transition border-slate-200 hover:bg-slate-50" id="type-opt-inst" onclick="selectCashTermin()">
                                        <input type="radio" name="payment_type" value="installment" class="mt-1 mr-3 text-emerald-600 focus:ring-emerald-500" required>
                                        <div>
                                            <span class="font-bold text-slate-900 text-sm flex items-center gap-1.5">
                                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                                Termin Bertahap (Tunai)
                                            </span>
                                            <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                                                Bayar bertahap (termin ke-{{ $lastInstallment + 1 }}). Bebas tentukan nominal pembayaran saat ini.
                                            </p>
                                            <span class="inline-block mt-1.5 px-2 py-0.5 bg-amber-50 text-amber-800 border border-amber-200 font-bold rounded text-[10px]">
                                                Maks. {{ $cashDueDays }} Hari sejak SPR terbit
                                            </span>
                                        </div>
                                    </label>

                                    <label class="flex items-start p-4 border rounded-xl cursor-pointer transition border-emerald-600 ring-1 ring-emerald-500 bg-emerald-50/30" id="type-opt-full" onclick="selectFullPayment()">
                                        <input type="radio" name="payment_type" value="full_payment" class="mt-1 mr-3 text-emerald-600 focus:ring-emerald-500" required checked>
                                        <div>
                                            <span class="font-bold text-slate-900 text-sm flex items-center gap-1.5">
                                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                Pelunasan Tunai Sekaligus
                                            </span>
                                            <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                                                Langsung lunasi sisa tagihan: <strong class="text-emerald-700">Rp {{ number_format($remainingAmount, 0, ',', '.') }}</strong>
                                            </p>
                                            <span class="inline-block mt-1.5 px-2 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded text-[10px]">
                                                Lunas Sekaligus
                                            </span>
                                        </div>
                                    </label>
                                </div>
                            @else
                                {{-- KEBIJAKAN DEVELOPER: WAJIB LANGSUNG LUNAS SEKALIGUS (TERMIN NONAKTIF) --}}
                                <input type="hidden" name="payment_type" value="full_payment">
                                <div class="p-4 border-2 border-emerald-600 rounded-xl bg-emerald-50/40">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                        <div>
                                            <span class="font-bold text-slate-900 text-sm flex items-center gap-1.5">
                                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                Pelunasan Rumah (Tunai Keras Sekaligus)
                                            </span>
                                            <p class="text-xs text-slate-600 mt-1">
                                                Sisa total tagihan unit yang harus dilunasi: <strong class="text-emerald-700 font-extrabold text-sm">Rp {{ number_format($remainingAmount, 0, ',', '.') }}</strong>
                                            </p>
                                            <p class="text-[11px] text-slate-500 mt-0.5">
                                                Sesuai kebijakan pengembang, skema Tunai Keras wajib dilunasi sekaligus (pembayaran termin bertahap dinonaktifkan). Batas waktu pelunasan maks. <strong>{{ $cashDueDays }} Hari</strong> sejak SPR terbit.
                                            </p>
                                        </div>
                                        <span class="px-3 py-1 bg-emerald-100 text-emerald-800 rounded-lg font-bold text-xs shrink-0 self-start sm:self-auto border border-emerald-200">
                                            Wajib Lunas Sekaligus
                                        </span>
                                    </div>
                                </div>
                            @endif
                        @else
                            {{-- SKEMA IN-HOUSE / CICILAN BULANAN: TAMPILKAN CICILAN BULANAN ATAU PELUNASAN --}}
                            @php
                                $monthlyInstallment = (int) $spr->getMonthlyInstallmentAmount();
                            @endphp
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <label class="flex items-center p-4 border rounded-xl cursor-pointer transition border-blue-600 ring-1 ring-blue-500 bg-blue-50/30" id="type-opt-inst" onclick="selectInstallmentAmount({{ $monthlyInstallment }})">
                                    <input type="radio" name="payment_type" value="installment" class="mr-3 text-blue-600 focus:ring-blue-500" required checked>
                                    <div>
                                        <span class="font-bold text-slate-900 text-sm flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5" /></svg>
                                            {{ $spr->payment_scheme === 'inhouse' ? 'Cicilan Bulanan' : 'Termin Bertahap' }}
                                        </span>
                                        <p class="text-[11px] text-slate-500 mt-0.5">
                                            Angsuran ke-{{ $lastInstallment + 1 }} dari {{ $tenorMonths }} Bulan
                                            @if($monthlyInstallment > 0)
                                                &middot; <strong class="text-blue-700">Rp {{ number_format($monthlyInstallment, 0, ',', '.') }} / bln</strong>
                                            @endif
                                            <span class="inline-block ml-1 px-1.5 py-0.5 bg-blue-100 text-blue-800 font-bold rounded text-[10px]">
                                                Tenor {{ $tenorMonths }} Bulan
                                            </span>
                                        </p>
                                    </div>
                                </label>

                                <label class="flex items-center p-4 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 transition" id="type-opt-full" onclick="selectFullPayment()">
                                    <input type="radio" name="payment_type" value="full_payment" class="mr-3 text-blue-600 focus:ring-blue-500" required>
                                    <div>
                                        <span class="font-bold text-slate-900 text-sm flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            Pelunasan Rumah
                                        </span>
                                        <p class="text-[11px] text-slate-500 mt-0.5">Sisa tagihan lunas: <strong>Rp {{ number_format($remainingAmount, 0, ',', '.') }}</strong></p>
                                    </div>
                                </label>
                            </div>
                        @endif
                    @endif
                </div>

                <div>
                    <label class="block text-slate-700 font-bold text-xs uppercase tracking-wider mb-2">Jumlah Pembayaran (Rp)</label>
                    @php
                        $isCash = in_array($spr->payment_scheme, ['cash', 'tunai_bertahap']);
                        $monthlyInstallment = (int) $spr->getMonthlyInstallmentAmount();
                        $maxAmount = !$isDpPaid ? (float) $spr->down_payment : ($remainingAmount + (isset($overdueDetails) && $overdueDetails['is_overdue'] ? $overdueDetails['penalty_amount'] : 0));
                        $defaultAmount = !$isDpPaid 
                            ? (int) $spr->down_payment 
                            : ((isset($overdueDetails) && $overdueDetails['is_overdue'] && $overdueDetails['penalty_amount'] > 0) 
                                ? (int) $overdueDetails['total_amount'] 
                                : ($isCash ? (int) $remainingAmount : ($monthlyInstallment > 0 ? $monthlyInstallment : (int) $remainingAmount)));
                    @endphp
                    <div class="relative">
                        <input type="number" name="amount" id="amount" class="w-full border border-slate-300 rounded-xl p-3 pr-10 text-lg font-bold text-slate-900 bg-slate-100 cursor-not-allowed focus:outline-none" required min="100000" placeholder="Nominal pelunasan otomatis" max="{{ $maxAmount }}" value="{{ $defaultAmount }}" readonly onkeydown="return false;" onwheel="return false;">
                        <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400" title="Nominal dikunci otomatis oleh sistem">
                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                        </div>
                    </div>
                    <div class="flex flex-wrap justify-between items-center gap-2 mt-2">
                        <p class="text-[11px] text-slate-500 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Nominal dikunci otomatis oleh sistem agar tidak bisa salah ketik / terhapus angka 0.</span>
                        </p>
                        <div class="flex items-center gap-2 flex-wrap">
                            @if(!$isDpPaid)
                                <button type="button" onclick="selectDpPayment()" class="text-xs bg-blue-50 text-blue-700 hover:bg-blue-100 font-semibold px-2.5 py-1 rounded-lg border border-blue-200 cursor-pointer transition">
                                    Set Nominal DP (Rp {{ number_format($spr->down_payment, 0, ',', '.') }})
                                </button>
                            @elseif($spr->payment_scheme === 'cash')
                                @if(isset($overdueDetails) && $overdueDetails['is_overdue'] && $overdueDetails['penalty_amount'] > 0)
                                    <button type="button" onclick="selectWithPenalty()" class="text-xs bg-rose-100 text-rose-800 hover:bg-rose-200 font-bold px-2.5 py-1 rounded-lg border border-rose-300 cursor-pointer transition">
                                        Isi Tagihan + Denda (Rp {{ number_format($overdueDetails['total_amount'], 0, ',', '.') }})
                                    </button>
                                @endif
                                @if($allowCashTermin)
                                    <button type="button" onclick="selectCashTerminPartial({{ round($remainingAmount / 2) }})" class="text-xs bg-amber-50 text-amber-800 hover:bg-amber-100 font-bold px-2.5 py-1 rounded-lg border border-amber-200 cursor-pointer transition">
                                        Termin 50% (Rp {{ number_format(round($remainingAmount / 2), 0, ',', '.') }})
                                    </button>
                                    <button type="button" onclick="selectFullPayment()" class="text-xs bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-3 py-1.5 rounded-lg shadow-xs cursor-pointer transition">
                                        Set Lunas 100% (Rp {{ number_format($remainingAmount, 0, ',', '.') }})
                                    </button>
                                @else
                                    <button type="button" onclick="selectFullPayment()" class="text-xs bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-3 py-1.5 rounded-lg shadow-xs cursor-pointer transition">
                                        Set Pelunasan Tunai (Rp {{ number_format($remainingAmount, 0, ',', '.') }})
                                    </button>
                                @endif
                            @else
                                @if(isset($overdueDetails) && $overdueDetails['is_overdue'] && $overdueDetails['penalty_amount'] > 0)
                                    <button type="button" onclick="selectWithPenalty()" class="text-xs bg-rose-100 text-rose-800 hover:bg-rose-200 font-bold px-2.5 py-1 rounded-lg border border-rose-300 cursor-pointer transition">
                                        Isi Tagihan + Denda (Rp {{ number_format($overdueDetails['total_amount'], 0, ',', '.') }})
                                    </button>
                                @endif
                                @if($monthlyInstallment > 0)
                                    <button type="button" onclick="selectInstallmentAmount({{ $monthlyInstallment }})" class="text-xs bg-blue-600 hover:bg-blue-700 text-white font-bold px-3 py-1.5 rounded-lg shadow-xs cursor-pointer transition">
                                        Set Angsuran ke-{{ $lastInstallment + 1 }} dari {{ $tenorMonths }} Bulan (Rp {{ number_format($monthlyInstallment, 0, ',', '.') }})
                                    </button>
                                @endif
                                <button type="button" onclick="selectFullPayment()" class="text-xs bg-slate-100 text-slate-700 hover:bg-slate-200 font-semibold px-2.5 py-1 rounded-lg border border-slate-300 cursor-pointer transition">
                                    Isi Sisa Pokok (Rp {{ number_format($remainingAmount, 0, ',', '.') }})
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- METODE PEMBAYARAN TRANSFER MANUAL KE REKENING PENGEMBANG --}}
                <input type="hidden" name="payment_method" value="manual_transfer">

                <div>
                    <div class="p-5 bg-slate-50/70 border border-slate-200/90 rounded-2xl shadow-xs">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 mb-4 border-b border-slate-200 gap-2">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600 block">Metode Pembayaran Resmi</span>
                                <h4 class="font-bold text-slate-900 text-sm flex items-center gap-2 mt-0.5">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" /></svg>
                                    Rekening Pengembang: {{ $company->company_name ?? 'Pengembang' }}
                                </h4>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-white text-slate-700 rounded-full font-medium text-xs border border-slate-200 self-start sm:self-auto shadow-2xs">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" /></svg>
                                Rekening Khusus Unit Ini
                            </span>
                        </div>

                        <p class="text-xs text-slate-600 mb-4 leading-relaxed">
                            Unit rumah ini dinaungi langsung oleh <strong>{{ $company->company_name ?? 'Pengembang Properti' }}</strong>. 
                            Silakan transfer pembayaran ke salah satu rekening bank resmi perusahaan di bawah ini:
                        </p>

                        @if($bankAccounts && $bankAccounts->isNotEmpty())
                            <div class="space-y-3 mb-5">
                                @foreach($bankAccounts as $idx => $bank)
                                    <label class="flex items-start justify-between p-3.5 bg-white border rounded-xl cursor-pointer hover:border-blue-400 transition {{ $idx === 0 ? 'border-blue-500 ring-1 ring-blue-400' : 'border-slate-200' }}">
                                        <div class="flex items-start gap-3">
                                            <input type="radio" name="developer_bank_account_id" value="{{ $bank->id }}" class="mt-1 text-blue-600 focus:ring-blue-500" {{ $idx === 0 ? 'checked' : '' }}>
                                            <div>
                                                <span class="px-2 py-0.5 bg-blue-50 text-blue-700 font-bold text-xs rounded uppercase border border-blue-200/60">
                                                    {{ $bank->bank_name }}
                                                </span>
                                                <p class="font-mono font-bold text-slate-900 text-base mt-1 tracking-wide">{{ $bank->account_number }}</p>
                                                <p class="text-xs text-slate-500">a.n. <strong class="text-slate-800">{{ $bank->account_holder_name }}</strong></p>
                                                @if($bank->branch)
                                                    <p class="text-[11px] text-slate-400">{{ $bank->branch }}</p>
                                                @endif
                                            </div>
                                        </div>
                                        <button type="button" onclick="navigator.clipboard.writeText('{{ $bank->account_number }}'); alert('Nomor rekening berhasil disalin!');" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 hover:bg-blue-50 hover:text-blue-700 text-slate-600 text-xs font-semibold rounded-lg border border-slate-200 transition shrink-0">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184" /></svg>
                                            Salin
                                        </button>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <div class="p-4 bg-amber-50 border border-amber-200/80 rounded-xl text-amber-900 text-xs mb-4 flex items-center gap-2">
                                <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                                <span>Belum ada rekening bank yang didaftarkan oleh pengembang <strong>{{ $company->company_name ?? 'ini' }}</strong>. Silakan hubungi admin pengembang.</span>
                            </div>
                        @endif

                        <h5 class="font-bold text-slate-700 text-xs uppercase tracking-wide mb-3 pt-3 border-t border-slate-200">
                            Unggah Bukti Transfer
                        </h5>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-slate-700 font-semibold mb-1 text-xs">Foto / Struk Bukti Transfer <span class="text-rose-500">*</span></label>
                                <input type="file" name="proof_of_transfer" accept="image/*,.pdf" class="w-full border border-slate-300 rounded-xl p-2 text-xs bg-white focus:outline-none" required>
                                <span class="text-[10px] text-slate-400 mt-1 block">Format: JPG, PNG, atau PDF (Maks. 10MB)</span>
                            </div>
                            <div>
                                <label class="block text-slate-700 font-semibold mb-1 text-xs">Tanggal Transfer <span class="text-rose-500">*</span></label>
                                <input type="date" name="transfer_date" value="{{ date('Y-m-d') }}" class="w-full border border-slate-300 rounded-xl p-2.5 text-xs bg-white focus:ring-1 focus:ring-blue-500 focus:outline-none" required>
                            </div>
                            <div>
                                <label class="block text-slate-700 font-semibold mb-1 text-xs">Nama Bank Pengirim <span class="text-rose-500">*</span></label>
                                <input type="text" name="sender_bank" placeholder="Contoh: BCA / Mandiri / BRI" class="w-full border border-slate-300 rounded-xl p-2.5 text-xs bg-white focus:ring-1 focus:ring-blue-500 focus:outline-none uppercase" required>
                            </div>
                            <div>
                                <label class="block text-slate-700 font-semibold mb-1 text-xs">Nama Pemilik Rekening Pengirim <span class="text-rose-500">*</span></label>
                                <input type="text" name="sender_name" value="{{ Auth::user()->name }}" placeholder="Nama sesuai rekening bank" class="w-full border border-slate-300 rounded-xl p-2.5 text-xs bg-white focus:ring-1 focus:ring-blue-500 focus:outline-none" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-slate-700 font-semibold mb-1.5 text-xs">Catatan Pembayaran (Opsional)</label>
                    <textarea name="notes" class="w-full border border-slate-300 rounded-xl p-3 text-xs focus:ring-1 focus:ring-blue-500 focus:outline-none" rows="2" placeholder="Tambahkan catatan khusus untuk verifikator keuangan..."></textarea>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl transition font-semibold text-xs shadow-xs flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                        </svg>
                        Kirim Pembayaran
                    </button>
                    <a href="{{ route('kpr-payment.list') }}" class="bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 px-5 py-2.5 rounded-xl transition font-semibold text-xs shadow-2xs">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateCardStyles() {
    const dpOpt   = document.getElementById('type-opt-dp');
    const instOpt = document.getElementById('type-opt-inst');
    const fullOpt = document.getElementById('type-opt-full');
    const selectedRadio = document.querySelector('input[name="payment_type"]:checked');

    const isCash = '{{ $spr->payment_scheme }}' === 'cash';
    const activeClass   = isCash 
        ? 'flex items-start p-4 border rounded-xl cursor-pointer transition border-emerald-600 ring-1 ring-emerald-500 bg-emerald-50/30'
        : 'flex items-center p-4 border rounded-xl cursor-pointer transition border-blue-600 ring-1 ring-blue-500 bg-blue-50/30';
    const inactiveClass = isCash
        ? 'flex items-start p-4 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 transition bg-white'
        : 'flex items-center p-4 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 transition bg-white';

    if (dpOpt)   dpOpt.className = inactiveClass;
    if (instOpt) instOpt.className = inactiveClass;
    if (fullOpt) fullOpt.className = inactiveClass;

    if (selectedRadio) {
        if (selectedRadio.value === 'dp' && dpOpt) {
            dpOpt.className = activeClass;
        } else if (selectedRadio.value === 'installment' && instOpt) {
            instOpt.className = activeClass;
        } else if (selectedRadio.value === 'full_payment' && fullOpt) {
            fullOpt.className = activeClass;
        }
    }
}

function selectDpPayment() {
    const dpRadio = document.querySelector('input[name="payment_type"][value="dp"]');
    if (dpRadio) {
        dpRadio.checked = true;
    }
    document.getElementById('amount').value = {{ (float) ($spr->down_payment ?? 0) }};
    updateCardStyles();
}

function selectWithPenalty() {
    const instRadio = document.querySelector('input[name="payment_type"][value="installment"]') || document.querySelector('input[name="payment_type"][value="full_payment"]');
    if (instRadio) {
        instRadio.checked = true;
    }
    document.getElementById('amount').value = {{ (float) ($overdueDetails['total_amount'] ?? 0) }};
    updateCardStyles();
}

function selectInstallmentAmount(amount) {
    const instRadio = document.querySelector('input[name="payment_type"][value="installment"]');
    if (instRadio) {
        instRadio.checked = true;
    }
    document.getElementById('amount').value = amount;
    updateCardStyles();
}

function selectCashTermin() {
    const instRadio = document.querySelector('input[name="payment_type"][value="installment"]');
    if (instRadio) {
        instRadio.checked = true;
    }
    const amountInput = document.getElementById('amount');
    amountInput.removeAttribute('readonly');
    amountInput.classList.remove('bg-slate-100', 'cursor-not-allowed');
    amountInput.classList.add('bg-white');
    if (parseFloat(amountInput.value) >= {{ (float) $remainingAmount }}) {
        amountInput.value = {{ (float) round($remainingAmount / 2) }};
    }
    amountInput.focus();
    updateCardStyles();
}

function selectCashTerminPartial(amount) {
    selectCashTermin();
    document.getElementById('amount').value = amount;
}

function selectFullPayment() {
    const fullRadio = document.querySelector('input[name="payment_type"][value="full_payment"]');
    if (fullRadio) {
        fullRadio.checked = true;
    }
    const amountInput = document.getElementById('amount');
    amountInput.value = {{ (float) $remainingAmount }};
    amountInput.setAttribute('readonly', true);
    amountInput.classList.remove('bg-white');
    amountInput.classList.add('bg-slate-100', 'cursor-not-allowed');
    updateCardStyles();
}

document.addEventListener('DOMContentLoaded', function() {
    updateCardStyles();
});

document.querySelectorAll('input[name="payment_type"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        if (this.value === 'full_payment') {
            document.getElementById('amount').value = {{ (float) $remainingAmount }};
        } else if (this.value === 'dp') {
            document.getElementById('amount').value = {{ (float) ($spr->down_payment ?? 0) }};
        } else if (this.value === 'installment') {
            const monthly = {{ (float) ($spr->getMonthlyInstallmentAmount()) }};
            document.getElementById('amount').value = monthly > 0 ? monthly : {{ (float) $remainingAmount }};
        }
        updateCardStyles();
    });
});
</script>
@endsection
