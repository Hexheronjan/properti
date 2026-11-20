@extends('layouts.app')

@section('title', 'Detail Registrasi NUP')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div>
        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-blue-600 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Kembali ke Dashboard
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-6 border-b border-slate-100">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Detail Registrasi #{{ $registration->registration_number }}</h1>
                <p class="text-xs text-slate-400 mt-1">Dibuat pada {{ $registration->created_at->format('d M Y H:i') }}</p>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold self-start sm:self-auto
                @if($registration->payment_status === 'paid') bg-emerald-50 text-emerald-700 border border-emerald-200/80
                @elseif($registration->payment_status === 'pending') bg-amber-50 text-amber-700 border border-amber-200/80
                @else bg-rose-50 text-rose-700 border border-rose-200/80 @endif
            ">
                <span class="w-1.5 h-1.5 rounded-full {{ $registration->payment_status === 'paid' ? 'bg-emerald-500' : ($registration->payment_status === 'pending' ? 'bg-amber-500' : 'bg-rose-500') }}"></span>
                @if($registration->payment_status === 'pending' && $latestPaymentTransaction && $latestPaymentTransaction->payment_gateway === 'cash_office' && $latestPaymentTransaction->status === 'pending')
                    MENUNGGU KONFIRMASI KANTOR
                @elseif($registration->payment_status === 'paid')
                    LUNAS / BERHASIL
                @else
                    {{ strtoupper($registration->payment_status) }}
                @endif
            </span>
        </div>

        @session('success')
            <div class="bg-emerald-50 border border-emerald-200/80 rounded-xl p-4 my-4 flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-xs font-semibold text-emerald-800">{{ session('success') }}</p>
            </div>
        @endsession

        @session('error')
            <div class="bg-rose-50 border border-rose-200/80 rounded-xl p-4 my-4 flex items-center gap-3">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
                <p class="text-xs font-semibold text-rose-800">{{ session('error') }}</p>
            </div>
        @endsession

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 my-6">
            <div class="bg-slate-50/70 border border-slate-200/70 rounded-xl p-4">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Informasi Peserta</h3>
                <div class="space-y-1.5 text-xs">
                    <p class="text-slate-600"><span class="font-medium text-slate-400">Nama:</span> <span class="font-semibold text-slate-800">{{ $registration->user->name }}</span></p>
                    <p class="text-slate-600"><span class="font-medium text-slate-400">Email:</span> <span class="font-semibold text-slate-800">{{ $registration->user->email }}</span></p>
                    <p class="text-slate-600"><span class="font-medium text-slate-400">Telepon:</span> <span class="font-semibold text-slate-800">{{ $registration->user->phone }}</span></p>
                    <p class="text-slate-600"><span class="font-medium text-slate-400">NIK:</span> <span class="font-semibold text-slate-800">{{ $registration->user->nik }}</span></p>
                </div>
            </div>
            <div class="bg-slate-50/70 border border-slate-200/70 rounded-xl p-4">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Informasi Registrasi</h3>
                <div class="space-y-1.5 text-xs">
                    <p class="text-slate-600"><span class="font-medium text-slate-400">Metode:</span> <span class="font-semibold text-slate-800">{{ config('nup.payment_methods.' . $registration->payment_method) }}</span></p>
                    <p class="text-slate-600"><span class="font-medium text-slate-400">Biaya NUP:</span> <span class="font-bold text-slate-900">Rp {{ number_format($registration->nup_fee, 0, ',', '.') }}</span></p>
                    <p class="text-slate-600"><span class="font-medium text-slate-400">Status Pembayaran:</span> <span class="font-semibold text-slate-800">{{ strtoupper($registration->payment_status) }}</span></p>
                </div>
            </div>
        </div>

        @if($registration->queue_number)
            <div class="bg-blue-50/60 border border-blue-200/70 rounded-xl p-5 mb-6 flex items-center justify-between">
                <div>
                    <h3 class="text-[10px] font-bold text-blue-600 uppercase tracking-wider">Nomor Antrian Anda</h3>
                    <p class="text-3xl font-extrabold text-blue-950 font-mono tracking-tight mt-0.5">{{ $registration->queue_number }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-blue-700 font-medium">Diterbitkan: {{ $registration->queue_issue_date->format('d-m-Y H:i') }}</p>
                </div>
            </div>
        @endif

        @if($registration->payment_status === 'pending')
            <div class="bg-amber-50/70 border border-amber-200/80 rounded-xl p-5 mb-6">
                <h3 class="text-xs font-bold text-amber-900 uppercase tracking-wider mb-2">Pembayaran Belum Selesai</h3>
                @if($latestPaymentTransaction && $latestPaymentTransaction->payment_gateway === 'cash_office' && $latestPaymentTransaction->status === 'pending')
                    <p class="text-xs text-amber-800 mb-2 leading-relaxed">Pembayaran tunai sebesar <strong>Rp {{ number_format($latestPaymentTransaction->amount, 0, ',', '.') }}</strong> tercatat di sistem. Mohon serahkan dana ke kantor pemasaran agar petugas mengonfirmasi status antrean Anda.</p>
                @else
                    <p class="text-xs text-amber-800 mb-4 leading-relaxed">Silakan selesaikan pembayaran biaya NUP untuk mengaktifkan nomor antrean pemilihan unit Anda.</p>
                    <form action="{{ route('nup.payment', $registration->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="block text-slate-700 font-semibold text-xs mb-1.5">Metode Pembayaran</label>
                            <div class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl bg-slate-50">
                                <div class="w-9 h-9 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-800">Transfer Bank / Virtual Account (Online)</p>
                                    <p class="text-[11px] text-slate-500">Pembayaran online instan via Transfer Bank / VA / QRIS</p>
                                </div>
                            </div>
                            <input type="hidden" name="payment_method" value="transfer">
                        </div>
                        <div class="mb-4">
                            <label for="promo_code" class="block text-slate-700 font-semibold text-xs mb-1.5">Kode Promo (Opsional)</label>
                            <input type="text" id="promo_code" name="promo_code" value="{{ old('promo_code') }}" maxlength="50" autocomplete="off" placeholder="Masukkan kode promo jika ada" class="w-full border border-slate-300 rounded-xl p-2.5 text-xs uppercase focus:ring-1 focus:ring-blue-500 focus:outline-none">
                        </div>
                        <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-2.5 rounded-xl hover:bg-blue-700 transition text-xs shadow-sm">
                            Lanjutkan Proses Pembayaran
                        </button>
                    </form>
                @endif
            </div>
        @endif

        @if($registration->payment_status === 'paid' && $registration->unitSelections->isEmpty())
            <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                @if($registration->payment_method === 'kpr_bank_flpp')
                    @if(!$registration->kprScreening)
                        <div>
                            <h3 class="text-sm font-bold text-blue-950">Pembayaran NUP Berhasil!</h3>
                            <p class="text-xs text-blue-700 mt-0.5">Langkah berikutnya: Lengkapi berkas persyaratan screening KPR (Pefindo).</p>
                        </div>
                        <a href="{{ route('kpr.create-from-nup', $registration->id) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded-xl hover:bg-blue-700 transition shadow-sm shrink-0">
                            Isi Formulir Screening KPR &rarr;
                        </a>
                    @else
                        <div>
                            <h3 class="text-sm font-bold text-blue-950">Screening KPR Terkirim!</h3>
                            <p class="text-xs text-blue-700 mt-0.5">Status Screening: {{ strtoupper(str_replace('_', ' ', $registration->kprScreening->status)) }}. Menunggu evaluasi kelayakan dari pengembang/analis bank.</p>
                        </div>
                        <a href="{{ route('kpr.show', $registration->kprScreening->id) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded-xl hover:bg-blue-700 transition shadow-sm shrink-0">
                            Lihat Status Screening &rarr;
                        </a>
                    @endif
                @else
                    <div>
                        <h3 class="text-sm font-bold text-blue-950">Nomor Antrian Aktif!</h3>
                        <p class="text-xs text-blue-700 mt-0.5">Silakan jelajahi katalog dan tentukan unit rumah impian Anda.</p>
                    </div>
                    <a href="{{ route('units.create', $registration->id) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded-xl hover:bg-blue-700 transition shadow-sm shrink-0">
                        Pilih Unit Rumah &rarr;
                    </a>
                @endif
            </div>
        @endif

        @if($registration->unitSelections->isNotEmpty())
            <div class="mt-6 pt-6 border-t border-slate-100">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Unit yang Dipilih</h3>
                @foreach($registration->unitSelections as $selection)
                    <div class="bg-slate-50/70 border border-slate-200/80 rounded-xl p-4 mb-3">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <p class="font-bold text-slate-900 text-sm">{{ $selection->housingUnit->full_identifier }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Tipe: {{ config('nup.unit_types.' . $selection->housingUnit->type) }} &middot;
                                    Harga: Rp {{ number_format($selection->housingUnit->price, 0, ',', '.') }}
                                </p>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold self-start sm:self-auto
                                @if($selection->status === 'confirmed') bg-emerald-50 text-emerald-700 border border-emerald-200/80
                                @elseif($selection->status === 'pending') bg-amber-50 text-amber-700 border border-amber-200/80
                                @else bg-slate-100 text-slate-700 border border-slate-200 @endif
                            ">
                                {{ strtoupper($selection->status) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection

@if($registration->payment_status === 'pending' && $latestPaymentTransaction?->payment_gateway === 'doku')
    @push('scripts')
        <script>
            window.setTimeout(() => window.location.reload(), 5000);
        </script>
    @endpush
@endif
