@extends('layouts.admin')

@section('title', 'Detail Pembayaran')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.kpr-payments.index') }}" class="text-blue-600 hover:text-blue-800 text-sm flex items-center gap-1">
            ← Kembali ke Kelola Pembayaran KPR
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        {{-- Header --}}
        @php
            $headerColor = match($payment->status) {
                'success' => 'from-green-500 to-emerald-600',
                'failed'  => 'from-red-500 to-red-600',
                default   => 'from-yellow-500 to-orange-500',
            };
            $statusLabel = match($payment->status) {
                'success' => 'Lunas',
                'failed'  => 'Gagal',
                'pending' => 'Menunggu Verifikasi',
                default   => ucfirst($payment->status),
            };
        @endphp
        <div class="bg-gradient-to-r {{ $headerColor }} px-6 py-5 flex justify-between items-center">
            <div class="text-white">
                <p class="text-xs opacity-70 uppercase tracking-wide">Nomor Pembayaran</p>
                <p class="font-bold text-xl">{{ $payment->payment_number }}</p>
                <p class="text-sm opacity-80 mt-0.5">
                    {{ $payment->payment_type === 'installment' ? 'Cicilan ke-' . $payment->installment_number : 'Pelunasan' }}
                    &middot; {{ ucfirst($payment->payment_method) }}
                </p>
            </div>
            <span class="px-4 py-2 rounded-full text-sm font-bold bg-white bg-opacity-20 text-white border border-white border-opacity-40">
                {{ $statusLabel }}
            </span>
        </div>

        <div class="p-6">
            {{-- Flash messages --}}
            @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 rounded-xl p-4 text-green-800 text-sm font-medium">
                    <svg class="w-4 h-4 inline-block text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 rounded-xl p-4 text-red-800 text-sm font-medium">
                    <svg class="w-4 h-4 inline-block text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg> {{ session('error') }}
                </div>
            @endif

            {{-- Detail grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                <div class="bg-gray-50 rounded-xl p-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-3">Informasi Pembayaran</p>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">No. Pembayaran</span>
                            <span class="font-semibold text-gray-800">{{ $payment->payment_number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Tanggal</span>
                            <span class="font-semibold text-gray-800">
                                {{ $payment->payment_date ? $payment->payment_date->format('d M Y H:i') : '-' }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Jenis</span>
                            <span class="font-semibold text-gray-800">
                                {{ $payment->payment_type === 'installment' ? 'Cicilan ke-' . $payment->installment_number : 'Pelunasan' }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Metode</span>
                            <span class="font-semibold text-gray-800">{{ ucfirst($payment->payment_method) }}</span>
                        </div>
                        @if($payment->transaction_id)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Transaction ID</span>
                            <span class="font-mono text-xs text-gray-700">{{ $payment->transaction_id }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="bg-gray-50 rounded-xl p-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-3">Rincian Jumlah</p>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Jumlah Dibayar</span>
                            <span class="font-bold text-gray-800 text-base">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                        </div>
                        @if($payment->principal_amount)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Pokok</span>
                            <span class="font-semibold text-gray-700">Rp {{ number_format($payment->principal_amount, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        @if($payment->interest_amount)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Bunga</span>
                            <span class="font-semibold text-gray-700">Rp {{ number_format($payment->interest_amount, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        @if($payment->remaining_principal)
                        <div class="flex justify-between border-t border-gray-200 pt-2 mt-2">
                            <span class="text-gray-500">Sisa Pokok</span>
                            <span class="font-bold text-red-600">Rp {{ number_format($payment->remaining_principal, 0, ',', '.') }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            @if($payment->notes)
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-6">
                <p class="text-xs text-yellow-600 uppercase font-semibold mb-1">Catatan</p>
                <p class="text-sm text-yellow-800">{{ $payment->notes }}</p>
            </div>
            @endif

            {{-- Status info --}}
            @if($payment->status === 'pending')
            <div class="bg-orange-50 border border-orange-200 rounded-xl p-4 mb-6">
                <h3 class="font-semibold text-orange-800 mb-1"><svg class="w-4 h-4 inline-block text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Menunggu Verifikasi</h3>
                @if($payment->payment_method === 'transfer')
                    <p class="text-sm text-orange-700">Silakan transfer ke rekening developer dan hubungi admin untuk konfirmasi.</p>
                @elseif($payment->payment_method === 'cash')
                    <p class="text-sm text-orange-700">Silakan datang ke kantor developer untuk melakukan pembayaran tunai.</p>
                @else
                    <p class="text-sm text-orange-700">Pembayaran sedang diproses. Harap tunggu konfirmasi dari admin.</p>
                @endif
            </div>
            @endif

            @if($payment->status === 'failed')
            <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
                <h3 class="font-semibold text-red-800 mb-1"><svg class="w-4 h-4 inline-block text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg> Pembayaran Gagal</h3>
                <p class="text-sm text-red-700">Pembayaran gagal diproses. Silakan coba lagi atau hubungi admin.</p>
            </div>
            @endif

            {{-- Midtrans Snap popup (jika ada snap_token dari session) --}}
            @if(session('snap_token'))
            <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4 mb-6">
                <h3 class="font-semibold text-indigo-800 mb-2"><svg class="w-4 h-4 inline-block text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg> Selesaikan Pembayaran</h3>
                <p class="text-sm text-indigo-700 mb-3">Klik tombol di bawah untuk membuka halaman pembayaran Midtrans.</p>
                <button id="pay-button"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition font-semibold">
                    <svg class="w-4 h-4 inline-block text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg> Bayar Sekarang via Midtrans
                </button>
            </div>
            @endif

            {{-- Action buttons --}}
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.kpr-payments.index') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition font-semibold text-sm">
                    ← Kembali ke Kelola Pembayaran KPR
                </a>
                @if($payment->status === 'pending' && Auth::user()->isAdmin() && $payment->payment_method !== 'midtrans')
                <form action="{{ route('admin.kpr-payments.approve', $payment->id) }}" method="POST" class="inline-flex">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 text-white rounded-xl hover:bg-green-700 transition font-semibold text-sm">
                        <svg class="w-4 h-4 inline-block text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Approve Pembayaran
                    </button>
                </form>
                <form action="{{ route('admin.kpr-payments.reject', $payment->id) }}" method="POST" class="inline-flex">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700 transition font-semibold text-sm">
                        <svg class="w-4 h-4 inline-block text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg> Reject Pembayaran
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Midtrans Snap JS --}}
@if(session('snap_token'))
<script src="https://app.{{ config('services.midtrans.environment') === 'production' ? '' : 'sandbox.' }}midtrans.com/snap/snap.js"
        data-client-key="{{ session('client_key', config('services.midtrans.client_key')) }}"></script>
<script>
    document.getElementById('pay-button').addEventListener('click', function() {
        snap.pay('{{ session('snap_token') }}', {
            onSuccess: function(result) {
                window.location.href = '{{ route('kpr-payment.list') }}';
            },
            onPending: function(result) {
                alert('Pembayaran pending. Silakan selesaikan pembayaran Anda.');
            },
            onError: function(result) {
                alert('Pembayaran gagal: ' + result.status_message);
            },
            onClose: function() {
                // User menutup popup tanpa bayar
            }
        });
    });

    // Auto-open Snap popup
    document.getElementById('pay-button').click();
</script>
@endif
@endsection
