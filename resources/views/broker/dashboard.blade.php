@extends('layouts.broker')

@section('title', 'Dashboard Broker')

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
                    <span class="text-[11px] text-slate-500 block sm:inline sm:ml-1">Pilih perusahaan untuk fokus data atau tampilkan semua mitra</span>
                </div>
            </div>
            <div class="flex items-center gap-1.5 flex-wrap">
                <a href="{{ request()->fullUrlWithQuery(['company_id' => 'all']) }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ ($selectedCompanyId === 'all' || !$selectedCompanyId) ? 'bg-amber-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    <span>Semua Mitra ({{ $partnerships->count() }})</span>
                </a>
                @foreach($partnerships as $p)
                    @if($p->company)
                        <a href="{{ request()->fullUrlWithQuery(['company_id' => $p->company_id]) }}" 
                           class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ (string)$selectedCompanyId === (string)$p->company_id ? 'bg-amber-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span>{{ $p->company->company_name }}</span>
                        </a>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    <!-- Welcome Banner -->
    <div class="bg-gradient-to-r from-amber-600 via-amber-700 to-orange-700 rounded-3xl p-6 sm:p-8 text-white shadow-lg relative overflow-hidden">
        <div class="relative z-10 max-w-3xl">
            <span class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-bold uppercase tracking-wider mb-3 inline-flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                @if($selectedCompanyId === 'all')
                    <span>Mitra Resmi: Semua Pengembang ({{ $partnerships->count() }} Mitra)</span>
                @else
                    <span>Mitra Resmi: {{ $activeCompany->company_name ?? ($primaryBroker->company->company_name ?? 'Pengembang Properti') }}</span>
                @endif
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                Halo, {{ $primaryBroker->name ?? auth()->user()->name }}!
            </h1>
            <p class="text-amber-100 text-xs sm:text-sm mt-1.5 leading-relaxed">
                Selamat datang di Portal Mitra Broker. Pantau ketersediaan stok unit real-time dan pantau progres status transaksi calon pembeli Anda.
            </p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Total Leads -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-500">Total Pendaftar NUP</span>
                <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </span>
            </div>
            <div class="text-2xl font-extrabold text-slate-900">{{ $totalLeads }}</div>
            <p class="text-[11px] text-slate-400 mt-0.5">Calon pembeli binaan Anda</p>
        </div>

        <!-- 2. NUP Lunas -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-500">NUP Lunas (Aktif)</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </span>
            </div>
            <div class="text-2xl font-extrabold text-emerald-600">{{ $paidNupLeads }}</div>
            <p class="text-[11px] text-slate-400 mt-0.5">Sudah memegang nomor antrean</p>
        </div>

        <!-- 3. Closing / Terjual -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-500">Unit Closing (SPR)</span>
                <span class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="text-2xl font-extrabold text-purple-700">{{ $totalClosing }}</div>
            <p class="text-[11px] text-slate-400 mt-0.5">Pemesanan terkonfirmasi</p>
        </div>

        <!-- 4. Stok Tersedia -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-500">Stok Unit Tersedia</span>
                <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </span>
            </div>
            <div class="text-2xl font-extrabold text-amber-700">{{ $availableStock }}</div>
            <p class="text-[11px] text-slate-400 mt-0.5">Di proyek {{ $activeCompany->company_name ?? ($selectedCompanyId === 'all' ? 'semua mitra developer' : 'developer') }}</p>
        </div>
    </div>

    <!-- Quick Navigation / Shortcut -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <a href="{{ route('broker.units') }}" class="p-5 bg-white border border-slate-200 rounded-2xl hover:border-amber-400 hover:shadow-md transition flex items-center justify-between group">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-2xl bg-amber-100 text-amber-800 flex items-center justify-center shrink-0 group-hover:scale-110 transition">
                    <svg class="w-5 h-5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-slate-900 group-hover:text-amber-800 transition">Cek Stok Unit Real-Time</h3>
                    <p class="text-xs text-slate-500">Lihat nomor kavling, tipe, harga, dan ketersediaan unit terbaru.</p>
                </div>
            </div>
            <span class="text-slate-400 group-hover:translate-x-1 transition font-bold text-base">→</span>
        </a>

        <a href="{{ route('broker.leads') }}" class="p-5 bg-white border border-slate-200 rounded-2xl hover:border-blue-400 hover:shadow-md transition flex items-center justify-between group">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-2xl bg-blue-100 text-blue-800 flex items-center justify-center shrink-0 group-hover:scale-110 transition">
                    <svg class="w-5 h-5 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-slate-900 group-hover:text-blue-800 transition">Daftar Calon Pembeli</h3>
                    <p class="text-xs text-slate-500">Pantau progres NUP, screening KPR, dan pembayaran UTK pembeli Anda.</p>
                </div>
            </div>
            <span class="text-slate-400 group-hover:translate-x-1 transition font-bold text-base">→</span>
        </a>
    </div>

    <!-- Recent Leads Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
            <div>
                <h2 class="font-bold text-base text-slate-900">Calon Pembeli Terbaru</h2>
                <p class="text-xs text-slate-400">5 pendaftar NUP terakhir yang menggunakan kode referral Anda.</p>
            </div>
            <a href="{{ route('broker.leads') }}" class="text-xs font-bold text-amber-700 hover:text-amber-800">
                Lihat Semua ({{ $totalLeads }}) →
            </a>
        </div>

        @if($recentLeads->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px]">
                            <th class="px-4 py-2.5 text-left">Nama Pembeli</th>
                            <th class="px-4 py-2.5 text-left">Pengembang & Proyek</th>
                            <th class="px-4 py-2.5 text-left">No. NUP / Antrean</th>
                            <th class="px-4 py-2.5 text-left">Status NUP</th>
                            <th class="px-4 py-2.5 text-left">Unit Dipilih</th>
                            <th class="px-4 py-2.5 text-left">Tanggal Daftar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($recentLeads as $lead)
                            @php
                                $sel = $lead->unitSelections->first();
                                $proj = $sel?->housingUnit?->housingProject;
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-4 py-3">
                                    <div class="font-bold text-slate-900">{{ $lead->user?->name ?? 'Konsumen' }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $lead->user?->phone ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-bold text-slate-800">{{ $proj?->company?->company_name ?? '-' }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $proj?->name ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-mono font-bold text-slate-800">{{ $lead->queue_number ?: $lead->registration_number }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    @if($lead->payment_status === 'paid')
                                        <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded-full font-bold text-[10px] inline-flex items-center gap-1">
                                            <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            <span>NUP LUNAS</span>
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 bg-amber-100 text-amber-800 rounded-full font-bold text-[10px] inline-flex items-center gap-1">
                                            <svg class="w-3 h-3 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>Menunggu Bayar</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $sel = $lead->unitSelections->first();
                                    @endphp
                                    @if($sel && $sel->housingUnit)
                                        <span class="font-bold text-blue-700">{{ $sel->housingUnit->block }} - {{ $sel->housingUnit->number }}</span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-slate-500 text-[11px]">
                                    {{ $lead->created_at->format('d/m/Y H:i') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8 text-slate-400 text-xs">
                Belum ada calon pembeli yang mendaftar di bawah nama broker Anda.
            </div>
        @endif
    </div>

</div>
@endsection
