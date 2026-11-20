@extends('layouts.admin')

@section('title', 'Kelola Pembayaran KPR')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Kelola Pembayaran KPR</h1>
    </div>

    <!-- Statistik Ringkas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="text-sm font-semibold text-blue-600 mb-1">Total Tagihan</h3>
            <p class="text-2xl font-bold text-blue-800">Rp {{ number_format($stats['total_billed'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <h3 class="text-sm font-semibold text-green-600 mb-1">Sudah Dibayar</h3>
            <p class="text-2xl font-bold text-green-800">Rp {{ number_format($stats['total_paid'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
            <h3 class="text-sm font-semibold text-orange-600 mb-1">Sisa Tagihan</h3>
            <p class="text-2xl font-bold text-orange-800">Rp {{ number_format($stats['total_remaining'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
            <h3 class="text-sm font-semibold text-purple-600 mb-1">Progress Pembayaran</h3>
            <p class="text-2xl font-bold text-purple-800">{{ number_format($stats['payment_progress'], 1) }}%</p>
        </div>
    </div>

    <!-- Per SPR Summary -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Rekapitulasi per SPR</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-800">No. SPR</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-800">User</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-800">Unit</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-800">Total Harga</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-800">Sudah Bayar</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-800">Sisa</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-800">Progress</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-800">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sprSummaries as $summary)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3 font-semibold">{{ $summary['spr_number'] }}</td>
                            <td class="px-4 py-3">{{ $summary['user_name'] }}</td>
                            <td class="px-4 py-3">{{ $summary['unit_identifier'] }}</td>
                            <td class="px-4 py-3 text-right">Rp {{ number_format($summary['total_price'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-green-600 font-semibold">Rp {{ number_format($summary['total_paid'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-orange-600 font-semibold">Rp {{ number_format($summary['remaining'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-24 bg-gray-200 rounded-full h-2">
                                        <div class="bg-purple-600 h-2 rounded-full" style="width: {{ $summary['progress'] }}%"></div>
                                    </div>
                                    <span class="text-xs font-semibold">{{ number_format($summary['progress'], 1) }}%</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold
                                    @if($summary['is_fully_paid']) bg-green-100 text-green-800
                                    @else bg-yellow-100 text-yellow-800 @endif
                                ">
                                    {{ $summary['is_fully_paid'] ? 'LUNAS' : 'BELUM LUNAS' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
            <p class="text-sm text-green-700">{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <p class="text-sm text-red-700">{{ session('error') }}</p>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-800">No. Pembayaran</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-800">User</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-800">SPR</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-800">Unit</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-800">Tanggal</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-800">Jenis</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-800">Cicilan Ke-</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-800">Jumlah</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-800">Metode</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-800">Status</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-800">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $payment->payment_number }}</td>
                            <td class="px-4 py-3">
                                <div>
                                    <p class="font-semibold">{{ $payment->user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $payment->user->email }}</p>
                                </div>
                            </td>
                            <td class="px-4 py-3">{{ $payment->sprDocument->spr_number }}</td>
                            <td class="px-4 py-3">{{ $payment->sprDocument->unitSelection->housingUnit->full_identifier }}</td>
                            <td class="px-4 py-3">{{ $payment->payment_date ? $payment->payment_date->format('d-m-Y H:i') : '-' }}</td>
                            <td class="px-4 py-3">{{ ucfirst($payment->payment_type) }}</td>
                            <td class="px-4 py-3">{{ $payment->installment_number ?? '-' }}</td>
                            <td class="px-4 py-3 text-right">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">{{ ucfirst($payment->payment_method) }}</td>
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
                                @if($payment->status === 'pending')
                                <div class="flex gap-2">
                                    <form action="{{ route('admin.kpr-payments.approve', $payment->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700 transition">
                                            Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.kpr-payments.reject', $payment->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700 transition">
                                            Reject
                                        </button>
                                    </form>
                                </div>
                                @else
                                <a href="{{ route('admin.kpr-payments.show', $payment->id) }}" class="text-blue-600 text-xs hover:underline">
                                    Detail
                                </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
            <div class="px-4 py-3 bg-gray-50 border-t">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
