@extends('layouts.admin')

@section('title', 'SPR Pending - Admin')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:text-blue-800">
            ← Kembali ke Dashboard Admin
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">SPR Menunggu Persetujuan</h1>
            @if($pendingSprs->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">No. SPR</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Peserta</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Unit</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Tanggal</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">DP</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Skema</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingSprs as $spr)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm">{{ $spr->spr_number }}</td>
                                    <td class="px-4 py-3 text-sm">{{ $spr->user->name }}</td>
                                    <td class="px-4 py-3 text-sm">{{ $spr->unitSelection->housingUnit->block }} - {{ $spr->unitSelection->housingUnit->number }}</td>
                                    <td class="px-4 py-3 text-sm">{{ $spr->spr_date ? \Carbon\Carbon::parse($spr->spr_date)->format('d/m/Y') : '-' }}</td>
                                    <td class="px-4 py-3 text-sm">Rp {{ number_format($spr->down_payment, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-sm">{{ ucfirst($spr->payment_scheme) }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <a href="{{ route('spr.show', $spr->id) }}" class="text-blue-600 hover:text-blue-800 mr-2">Detail</a>
                                        <form action="{{ route('spr.issue', $spr->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-green-600 hover:text-green-800 mr-2">Terbitkan</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <p>Tidak ada SPR yang menunggu persetujuan.</p>
                </div>
            @endif
    </div>
</div>
@endsection
