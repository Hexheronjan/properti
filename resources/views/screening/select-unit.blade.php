@extends('layouts.app')

@section('title', 'Pilih Unit')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-2"><svg class="w-4 h-4 inline-block text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg> Pilih Unit</h1>
        <p class="text-gray-600">Perumahan: {{ $housingProject->name }} - {{ $housingProject->location }}</p>
    </div>

    @if($availableUnits->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($availableUnits as $unit)
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-4">
                        <div class="flex justify-between items-center">
                            <h3 class="font-bold text-white text-lg">{{ $unit->unit_code ?? $unit->full_identifier }}</h3>
                            <span class="bg-green-500 text-white text-xs px-2 py-1 rounded-full">Tersedia</span>
                        </div>
                    </div>
                    
                    <div class="p-4">
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Tipe:</span>
                                <span class="font-semibold">{{ $unit->type }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Luas:</span>
                                <span class="font-semibold">{{ $unit->land_area }} m²</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Luas Bangunan:</span>
                                <span class="font-semibold">{{ $unit->building_area }} m²</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Kamar Tidur:</span>
                                <span class="font-semibold">{{ $unit->bedrooms }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Kamar Mandi:</span>
                                <span class="font-semibold">{{ $unit->bathrooms }}</span>
                            </div>
                        </div>
                        
                        <div class="border-t pt-3 mb-4">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 text-sm">Harga Unit:</span>
                                <span class="font-bold text-xl text-blue-600">Rp {{ number_format($unit->price, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        
                        <a href="{{ route('screening.spr-clauses', [$screening->id, $unit->id]) }}" 
                           class="block w-full text-center bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">
                            Pilih Unit Ini
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
            <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
            <h3 class="font-semibold text-yellow-800 mb-2">Tidak Ada Unit Tersedia</h3>
            <p class="text-sm text-yellow-700">Semua unit di perumahan ini sudah terjual atau tidak tersedia saat ini.</p>
        </div>
    @endif

    <div class="mt-6 flex justify-between">
        <a href="{{ route('screening.select-unit', $screening->id) }}" class="text-blue-600 hover:text-blue-800 text-sm">
            ← Kembali ke Pilih Perumahan
        </a>
        <a href="{{ route('screening.decision', $screening->id) }}" class="text-blue-600 hover:text-blue-800 text-sm">
            Kembali ke Keputusan Screening →
        </a>
    </div>
</div>
@endsection
