@extends('layouts.app')

@section('title', 'Unit Perumahan')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-extrabold bg-indigo-50 text-indigo-700 border border-indigo-200/80 mb-2 shadow-xs">
            <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span>
            LAYANAN PEMESANAN &middot; KATALOG UNIT
        </span>
        <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
            <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span>Unit Perumahan Tersedia</span>
        </h1>
        <p class="text-sm text-gray-500 mt-1">Pilih unit perumahan kavling yang sesuai dengan kebutuhan Anda.</p>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-semibold flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs font-semibold flex items-center gap-2">
            <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($availableUnits->isEmpty())
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-8 text-center text-amber-800">
            <div class="w-12 h-12 mx-auto mb-3 bg-amber-100 rounded-xl flex items-center justify-center text-amber-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <h3 class="font-bold text-base text-amber-950 mb-1">Tidak Ada Unit Tersedia</h3>
            <p class="text-xs text-amber-800">Mohon maaf, saat ini seluruh unit perumahan sedang dipesan atau telah terjual.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($availableUnits as $unit)
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden hover:shadow-md transition flex flex-col justify-between">
                    <div>
                        <div class="relative">
                            @if($unit->image)
                                <img src="{{ asset('storage/' . $unit->image) }}" alt="{{ $unit->unit_code }}" class="w-full h-48 object-cover">
                            @else
                                <div class="w-full h-48 bg-gray-100 flex items-center justify-center text-gray-300">
                                    <svg class="w-14 h-14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                </div>
                            @endif
                            <div class="absolute top-3 right-3">
                                <span class="px-2.5 py-1 bg-emerald-500 text-white text-[11px] font-bold rounded-full shadow-sm">
                                    Tersedia
                                </span>
                            </div>
                        </div>

                        <div class="p-5">
                            <div class="mb-2">
                                <h3 class="font-bold text-lg text-gray-900 leading-tight">{{ $unit->full_identifier }}</h3>
                                @if($unit->project)
                                    <div class="mt-1 space-y-0.5">
                                        <p class="text-xs text-gray-600">
                                            <span class="text-gray-400">Nama Perumahan:</span>
                                            <span class="font-bold text-blue-600">{{ $unit->project->name }}</span>
                                        </p>
                                        @if($unit->project->company)
                                            <p class="text-[11px] text-gray-500">
                                                <span class="text-gray-400">Pengembang:</span>
                                                <span class="font-medium text-gray-600">{{ $unit->project->company->company_name }}</span>
                                            </p>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <p class="text-xs font-medium text-gray-500 mb-3">{{ $unit->formatted_type }}</p>

                            <div class="grid grid-cols-2 gap-2 text-xs text-gray-600 bg-gray-50 p-3 rounded-xl mb-4 border border-gray-100">
                                <div class="font-medium">{{ $unit->bedrooms }} Kamar Tidur</div>
                                <div class="font-medium">{{ $unit->bathrooms }} Kamar Mandi</div>
                                <div class="font-medium">LB: {{ $unit->building_area }} m²</div>
                                <div class="font-medium">LT: {{ $unit->land_area }} m²</div>
                            </div>

                            @if($unit->description)
                                <p class="text-xs text-gray-500 line-clamp-2 mb-3">{{ $unit->description }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="p-5 pt-0">
                        <div class="border-t border-gray-100 pt-3">
                            <div class="text-[11px] text-gray-400">Harga Jual:</div>
                            <p class="text-lg font-extrabold text-blue-600 mb-4">
                                Rp {{ number_format($unit->price, 0, ',', '.') }}
                            </p>
                            
                            @if(auth()->check())
                                @if($hasUnpaidHouse)
                                    <div class="space-y-2">
                                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-center">
                                            <p class="text-xs text-amber-900 font-bold mb-0.5">Pemesanan Baru Dibatasi</p>
                                            <p class="text-[11px] text-amber-700">Anda masih memiliki unit rumah aktif yang belum lunas.</p>
                                        </div>
                                        <a href="{{ route('dashboard') }}" class="block w-full text-center bg-amber-600 text-white py-2 rounded-xl hover:bg-amber-700 transition text-xs font-semibold">
                                            Lihat Pembayaran Rumah Aktif
                                        </a>
                                    </div>
                                @elseif($availableNup)
                                    {{-- User memiliki NUP baru yang aktif dan belum digunakan memilih unit --}}
                                    <div class="space-y-1.5">
                                        <a href="{{ route('units.create', $availableNup->id) }}" class="inline-flex items-center justify-center gap-1.5 w-full text-center bg-emerald-600 text-white py-2.5 rounded-xl hover:bg-emerald-700 transition text-xs font-bold shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <span>Pilih Unit Ini</span>
                                        </a>
                                        <p class="text-[10px] text-center text-slate-400">Gunakan Antrean: <strong class="text-slate-700">{{ $availableNup->queue_number ?: $availableNup->registration_number }}</strong></p>
                                    </div>
                                @else
                                    {{-- User belum mendaftar NUP atau NUP lamanya sudah dipakai beli unit --}}
                                    <div class="space-y-1.5">
                                        <a href="{{ route('nup.register') }}" class="inline-flex items-center justify-center gap-1.5 w-full text-center bg-blue-600 text-white py-2.5 rounded-xl hover:bg-blue-700 transition text-xs font-bold shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                            <span>Daftar NUP untuk Memilih Unit</span>
                                        </a>
                                        <p class="text-[10px] text-center text-slate-400">1 Tiket NUP berlaku untuk 1 unit rumah</p>
                                    </div>
                                @endif
                            @else
                                <a href="{{ route('login') }}" class="block w-full text-center bg-slate-100 hover:bg-slate-200 text-slate-700 py-2.5 rounded-xl text-xs font-semibold transition">
                                    Login untuk Memilih Unit
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection