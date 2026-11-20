@extends('layouts.admin')

@section('title', 'Marketing Dashboard')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Dashboard Marketing</h1>
        <p class="text-gray-600">Selamat datang kembali! Berikut statistik pemasaran perumahan Anda.</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-gray-600 text-sm font-semibold">Total Registrasi</h3>
            <p class="text-3xl font-bold text-blue-600">{{ $totalRegistrations }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-gray-600 text-sm font-semibold">NUP Terbayar (Paid)</h3>
            <p class="text-3xl font-bold text-green-600">{{ $paidRegistrations }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-gray-600 text-sm font-semibold">Unit Terpilih</h3>
            <p class="text-3xl font-bold text-orange-600">{{ $totalSelections }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-gray-600 text-sm font-semibold">Unit Tersedia</h3>
            <p class="text-3xl font-bold text-purple-600">{{ $availableUnits }}</p>
        </div>
    </div>

    <!-- Recent Registrations Table -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Registrasi Terbaru</h3>
        @if($recentRegistrations->isEmpty())
            <p class="text-gray-500 text-sm">Tidak ada data registrasi terbaru.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Peserta</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor Registrasi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Skema Pembayaran</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Pembayaran</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($recentRegistrations as $reg)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $reg->user->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $reg->registration_number }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $reg->payment_method === 'kpr_bank_flpp' ? 'KPR Bank / FLPP' : 'Tunai / Mitra Pintar' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($reg->payment_status === 'paid') bg-green-100 text-green-800
                                        @elseif($reg->payment_status === 'pending') bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ ucfirst($reg->payment_status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $reg->created_at->format('d-m-Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
