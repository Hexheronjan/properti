@extends('layouts.broker')

@section('title', 'Katalog & Stok Unit')

@section('content')
<div class="space-y-6">

    <!-- Company Selector / Mitra Developer Filter -->
    @if(isset($partnerships) && $partnerships->count() > 1)
        <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-slate-200 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </span>
                <div>
                    <span class="text-xs font-bold text-slate-800">Filter Mitra Pengembang:</span>
                    <span class="text-[11px] text-slate-500 block sm:inline sm:ml-1">Tampilkan stok unit per perusahaan atau semua mitra</span>
                </div>
            </div>
            <div class="flex items-center gap-1.5 flex-wrap">
                <a href="{{ route('broker.units', ['company_id' => 'all']) }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ ($selectedCompanyId === 'all' || !$selectedCompanyId) ? 'bg-amber-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    <span>Semua Mitra ({{ $partnerships->count() }})</span>
                </a>
                @foreach($partnerships as $p)
                    @if($p->company)
                        <a href="{{ route('broker.units', ['company_id' => $p->company_id]) }}" 
                           class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ (string)$selectedCompanyId === (string)$p->company_id ? 'bg-amber-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span>{{ $p->company->company_name }}</span>
                        </a>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    <!-- Header & Info -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900 flex items-center gap-2">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span>Katalog & Stok Unit Real-Time</span>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Proyek Pengembang: <strong class="text-slate-800">{{ $activeCompany->company_name ?? ($selectedCompanyId === 'all' ? 'Semua Mitra Pengembang' : 'Developer') }}</strong>. Stok unit diperbarui secara otomatis dan langsung dari sistem.
            </p>
        </div>

        <a href="{{ route('broker.dashboard') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 inline-flex items-center gap-1">
            ← Kembali ke Dashboard
        </a>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('broker.units') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
            <div>
                <label class="block font-bold text-slate-700 mb-1">Perumahan</label>
                <select name="project_id" class="w-full border border-slate-300 rounded-xl p-2.5 text-xs bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    <option value="">-- Semua Proyek --</option>
                    @foreach($projects as $proj)
                        <option value="{{ $proj->id }}" {{ request('project_id') == $proj->id ? 'selected' : '' }}>
                            {{ $proj->company ? '[' . $proj->company->company_name . '] ' : '' }}{{ $proj->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Tipe Rumah</label>
                <select name="type" class="w-full border border-slate-300 rounded-xl p-2.5 text-xs bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    <option value="">-- Semua Tipe --</option>
                    @foreach($unitTypes as $key => $label)
                        <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Status Ketersediaan</label>
                <select name="status" class="w-full border border-slate-300 rounded-xl p-2.5 text-xs bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    <option value="">-- Semua Status --</option>
                    <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Tersedia (Available)</option>
                    <option value="booked" {{ request('status') == 'booked' ? 'selected' : '' }}>Dipesan (Booked)</option>
                    <option value="sold" {{ request('status') == 'sold' ? 'selected' : '' }}>Terjual (Sold)</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-bold text-xs shadow-sm transition">
                    Terapkan Filter
                </button>
                @if(request()->hasAny(['project_id', 'type', 'status']))
                    <a href="{{ route('broker.units') }}" class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold text-xs transition inline-flex items-center justify-center" title="Reset Filter">
                        <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Units Grid -->
    @if($units->count() > 0)
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3.5">
            @foreach($units as $unit)
                <div class="bg-white rounded-2xl border p-3.5 flex flex-col justify-between transition hover:shadow-md relative overflow-hidden
                    @if($unit->status === 'available') border-emerald-200 hover:border-emerald-400
                    @elseif($unit->status === 'booked') border-amber-200 hover:border-amber-400
                    @else border-slate-200 opacity-75 @endif
                ">
                    <!-- Status Top Pill -->
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase
                            @if($unit->status === 'available') bg-emerald-100 text-emerald-800
                            @elseif($unit->status === 'booked') bg-amber-100 text-amber-800
                            @else bg-rose-100 text-rose-800 @endif
                        ">
                            {{ $unit->status === 'available' ? 'Tersedia' : ($unit->status === 'booked' ? 'Dipesan' : 'Terjual') }}
                        </span>
                        <span class="text-[10px] font-mono font-bold text-slate-400">
                            #{{ $unit->id }}
                        </span>
                    </div>

                    <!-- Unit Block & Number -->
                    <div class="my-1.5">
                        <div class="text-base font-extrabold text-slate-900 leading-tight">
                            {{ $unit->block }} - {{ $unit->number }}
                        </div>
                        <div class="text-[11px] text-slate-500 truncate" title="{{ $unit->housingProject?->name }}">
                            <span class="font-semibold text-slate-700">{{ $unit->housingProject?->company?->company_name }}</span> &middot; {{ $unit->housingProject?->name }}
                        </div>
                        <div class="text-[10px] text-slate-400 mt-0.5">
                            {{ config('nup.unit_types.' . $unit->type, $unit->type) }}
                        </div>
                    </div>

                    <!-- Price -->
                    <div class="pt-2 border-t border-slate-100 mt-2">
                        <div class="text-[10px] text-slate-400">Harga Jual:</div>
                        <div class="font-extrabold text-xs text-amber-800">
                            Rp {{ number_format($unit->price, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($units->hasPages())
            <div class="mt-4">
                {{ $units->links() }}
            </div>
        @endif
    @else
        <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-400 text-xs">
            <div class="w-12 h-12 mx-auto mb-3 bg-slate-100 text-slate-400 rounded-2xl flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <p class="font-bold text-slate-700 text-sm">Tidak ada unit yang sesuai dengan filter.</p>
            <p class="mt-1 text-slate-400">Silakan ubah pilihan perumahan atau status ketersediaan di atas.</p>
        </div>
    @endif

</div>
@endsection
