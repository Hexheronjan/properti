@extends('layouts.admin')

@section('title', 'Finance Dashboard')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Dashboard Keuangan</h1>
        <p class="text-gray-600">Selamat datang kembali! Berikut statistik finansial dan pembayaran NUP.</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-gray-600 text-sm font-semibold">Total Revenue (NUP Terbayar)</h3>
            <p class="text-3xl font-bold text-green-600">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-gray-600 text-sm font-semibold">Pembayaran Pending</h3>
            <p class="text-3xl font-bold text-yellow-600">{{ $pendingPayments }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-gray-600 text-sm font-semibold">Metode Pembayaran</h3>
            <p class="text-sm text-gray-500 mt-2">Terintegrasi otomatis dengan Midtrans Payment Gateway (Mock + Real Mode)</p>
        </div>
    </div>

    <!-- Recent Payments Table -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Transaksi Pembayaran Terbaru</h3>
        @if($recentPayments->isEmpty())
            <p class="text-gray-500 text-sm">Tidak ada transaksi pembayaran terbaru.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Peserta</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor Transaksi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah (Nominal)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($recentPayments as $payment)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $payment->user->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $payment->transaction_number }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-950 font-semibold">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ ucfirst($payment->type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($payment->status === 'success') bg-green-100 text-green-800
                                        @elseif($payment->status === 'pending') bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $payment->created_at ? $payment->created_at->format('d-m-Y H:i') : '-' }}
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
