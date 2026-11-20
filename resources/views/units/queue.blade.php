@extends('layouts.app')

@section('title', 'Antrian Pemilihan Unit')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800">
            ← Kembali ke Dashboard
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-6">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-extrabold bg-purple-50 text-purple-700 border border-purple-200/80 mb-3 shadow-xs">
            <span class="w-1.5 h-1.5 rounded-full bg-purple-600"></span>
            LAYANAN PEMESANAN &middot; ANTRIAN UNIT
        </span>
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Antrian Pemilihan Unit</h1>

        @if($queue->isEmpty())
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-6">
                <h3 class="font-semibold text-yellow-800 mb-2">Belum Ada Antrian</h3>
                <p class="text-sm text-yellow-700">Belum ada yang sudah melakukan pembayaran NUP.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Antrian</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Registrasi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Bayar</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Unit</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($queue as $index => $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold">
                                        #{{ $index + 1 }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $item->user->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $item->registration_number }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $item->payment_date ? $item->payment_date->format('d-m-Y H:i') : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($item->unitSelections->isNotEmpty())
                                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">
                                            Sudah Pilih
                                        </span>
                                    @else
                                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">
                                            Belum Pilih
                                        </span>
                                    @endif
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