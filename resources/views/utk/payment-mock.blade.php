@extends('layouts.app')

@section('title', 'Pembayaran UTK - Menunggu Verifikasi')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('units.show', $unitSelection->id) }}" class="text-blue-600 hover:text-blue-800 text-sm flex items-center gap-1">
            ← Kembali ke Detail Unit
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-orange-500 to-yellow-500 px-6 py-5">
            <h1 class="text-xl font-bold text-white"><svg class="w-4 h-4 inline-block text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Pembayaran UTK Menunggu Verifikasi</h1>
            <p class="text-orange-100 text-sm mt-1">{{ $utkPayment->payment_number }}</p>
        </div>

        <div class="p-6">
            {{-- Info Transaksi --}}
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 mb-5">
                <h3 class="font-semibold text-blue-800 mb-3"><svg class="w-4 h-4 inline-block text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg> Informasi Transaksi</h3>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <p class="text-blue-500 text-xs uppercase">Nomor Pembayaran</p>
                        <p class="font-semibold text-blue-800">{{ $utkPayment->payment_number }}</p>
                    </div>
                    <div>
                        <p class="text-blue-500 text-xs uppercase">Jumlah</p>
                        <p class="font-bold text-blue-800 text-base">Rp {{ number_format($utkPayment->amount, 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-blue-500 text-xs uppercase">Status</p>
                        <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                            <svg class="w-4 h-4 inline-block text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> {{ strtoupper($utkPayment->status) }}
                        </span>
                    </div>
                    <div>
                        <p class="text-blue-500 text-xs uppercase">Unit</p>
                        <p class="font-semibold text-blue-800">{{ $unitSelection->housingUnit->full_identifier ?? '-' }}</p>
                    </div>
                </div>
            </div>

            {{-- Instruksi --}}
            <div class="bg-orange-50 border border-orange-200 rounded-xl p-5 mb-5">
                <h3 class="font-semibold text-orange-800 mb-2"><svg class="w-4 h-4 inline-block text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg> Langkah Selanjutnya</h3>
                <p class="text-sm text-orange-700 mb-3">{{ $message }}</p>
                <ol class="text-sm text-orange-700 space-y-2">
                    <li>1. Lakukan transfer sesuai jumlah yang tertera ke rekening developer</li>
                    <li>2. Simpan bukti transfer Anda</li>
                    <li>3. Hubungi admin atau marketing untuk konfirmasi pembayaran</li>
                    <li>4. Admin akan memverifikasi dan mengubah status pembayaran</li>
                </ol>
            </div>

            {{-- Action buttons --}}
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('dashboard') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition font-semibold text-sm">
                    <svg class="w-4 h-4 inline-block text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg> Ke Dashboard
                </a>
                <a href="{{ route('units.show', $unitSelection->id) }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition font-semibold text-sm">
                    ← Detail Unit
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
