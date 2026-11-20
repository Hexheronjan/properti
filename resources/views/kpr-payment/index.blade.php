@extends('layouts.app')

@section('title', 'Pembayaran KPR')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800">
            ← Kembali ke Dashboard
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Pembayaran KPR</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="font-semibold text-blue-800 mb-2">Informasi SPR</h3>
                <p class="text-sm text-blue-700"><strong>No. SPR:</strong> {{ $spr->spr_number }}</p>
                <p class="text-sm text-blue-700"><strong>Tanggal SPR:</strong> {{ $spr->spr_date }}</p>
                <p class="text-sm text-blue-700"><strong>Skema:</strong> {{ ucfirst($spr->payment_scheme) }}</p>
            </div>

            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <h3 class="font-semibold text-green-800 mb-2">Status Pembayaran</h3>
                <p class="text-sm text-green-700"><strong>Total Dibayar:</strong> Rp {{ number_format($totalPaid, 0, ',', '.') }}</p>
                <p class="text-sm text-green-700"><strong>Sisa Pembayaran:</strong> Rp {{ number_format($remainingAmount, 0, ',', '.') }}</p>
                <p class="text-sm text-green-700"><strong>Progress:</strong> {{ number_format($paymentProgress, 1) }}%</p>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="mb-6">
            <div class="flex justify-between text-sm text-gray-600 mb-1">
                <span>Progress Pembayaran</span>
                <span>{{ number_format($paymentProgress, 1) }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-4">
                <div class="bg-green-600 h-4 rounded-full transition-all duration-300" style="width: {{ $paymentProgress }}%"></div>
            </div>
        </div>

        @if($isFullyPaid)
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-green-800 mb-2 flex items-center gap-1.5"><svg class="w-4 h-4 inline-block text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> KPR Sudah Lunas</h3>
                <p class="text-sm text-green-700">Selamat! KPR Anda sudah lunas sepenuhnya.</p>
            </div>
        @else
            <div class="mb-6">
                <a href="{{ route('kpr-payment.create', $spr->id) }}" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition font-semibold">
                    + Bayar Cicilan
                </a>
            </div>
        @endif

        <h3 class="font-semibold text-gray-800 mb-4">Riwayat Pembayaran</h3>

        @if($spr->kprPayments->isEmpty())
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <p class="text-sm text-yellow-700">Belum ada pembayaran KPR.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-800">No. Pembayaran</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-800">Tanggal</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-800">Jenis</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-800">Cicilan Ke-</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-800">Jumlah</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-800">Status</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-800">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($spr->kprPayments as $payment)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-3">{{ $payment->payment_number }}</td>
                                <td class="px-4 py-3">{{ $payment->payment_date ? $payment->payment_date->format('d-m-Y H:i') : '-' }}</td>
                                <td class="px-4 py-3">{{ ucfirst($payment->payment_type) }}</td>
                                <td class="px-4 py-3">{{ $payment->installment_number ?? '-' }}</td>
                                <td class="px-4 py-3 text-right">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold
                                        @if($payment->status === 'success') bg-green-100 text-green-800
                                        @elseif($payment->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($payment->status === 'failed') bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800 @endif
                                    ">
                                        {{ strtoupper($payment->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('kpr-payment.show', $payment->id) }}" class="text-blue-600 hover:text-blue-800">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
