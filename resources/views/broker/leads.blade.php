@extends('layouts.broker')

@section('title', 'Calon Pembeli Saya')

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
                    <span class="text-[11px] text-slate-500 block sm:inline sm:ml-1">Tampilkan calon pembeli per perusahaan atau semua mitra</span>
                </div>
            </div>
            <div class="flex items-center gap-1.5 flex-wrap">
                <a href="{{ route('broker.leads', array_merge(request()->query(), ['company_id' => 'all'])) }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ ($selectedCompanyId === 'all' || !$selectedCompanyId) ? 'bg-amber-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    <span>Semua Mitra ({{ $partnerships->count() }})</span>
                </a>
                @foreach($partnerships as $p)
                    @if($p->company)
                        <a href="{{ route('broker.leads', array_merge(request()->query(), ['company_id' => $p->company_id])) }}" 
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
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <span>Calon Pembeli Saya (Leads)</span>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Daftar calon pembeli yang memilih nama broker Anda saat melakukan pendaftaran NUP.
            </p>
        </div>

        <a href="{{ route('broker.dashboard') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 inline-flex items-center gap-1">
            ← Kembali ke Dashboard
        </a>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-2">
            <a href="{{ route('broker.leads') }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ !request('status') ? 'bg-amber-100 text-amber-900' : 'text-slate-600 hover:bg-slate-100' }}">
                Semua Pembeli ({{ $leads->total() }})
            </a>
            <a href="{{ route('broker.leads', ['status' => 'paid']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition inline-flex items-center gap-1 {{ request('status') === 'paid' ? 'bg-emerald-100 text-emerald-900' : 'text-slate-600 hover:bg-slate-100' }}">
                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>NUP Lunas</span>
            </a>
            <a href="{{ route('broker.leads', ['status' => 'pending']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition inline-flex items-center gap-1 {{ request('status') === 'pending' ? 'bg-amber-100 text-amber-900' : 'text-slate-600 hover:bg-slate-100' }}">
                <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Menunggu Bayar</span>
            </a>
        </div>
    </div>

    <!-- Table of Leads -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        @if($leads->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-600 font-bold uppercase text-[10px]">
                            <th class="px-4 py-3 text-left">Nama Pembeli & Kontak</th>
                            <th class="px-4 py-3 text-left">Nomor NUP</th>
                            <th class="px-4 py-3 text-left">Status NUP</th>
                            <th class="px-4 py-3 text-left">Screening KPR</th>
                            <th class="px-4 py-3 text-left">Unit Rumah</th>
                            <th class="px-4 py-3 text-left">Status UTK</th>
                            <th class="px-4 py-3 text-left">Dokumen SPR</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($leads as $lead)
                            @php
                                $unitSel = $lead->unitSelections->first();
                                $spr = $lead->sprDocuments->first();
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-4 py-3.5">
                                    <div class="font-bold text-slate-900 text-sm">{{ $lead->user?->name ?? 'Konsumen' }}</div>
                                    <div class="text-[11px] text-slate-500 flex items-center gap-1 mt-0.5">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                        <span>{{ $lead->user?->phone ?? '-' }}</span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">
                                        Daftar: {{ $lead->created_at->format('d/m/Y H:i') }}
                                    </div>
                                </td>

                                <td class="px-4 py-3.5">
                                    <span class="font-mono font-bold text-xs text-slate-800 bg-slate-100 px-2 py-1 rounded">
                                        {{ $lead->queue_number ?: $lead->registration_number }}
                                    </span>
                                </td>

                                <td class="px-4 py-3.5">
                                    @if($lead->payment_status === 'paid')
                                        <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded-full font-bold text-[10px] inline-flex items-center gap-1">
                                            <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            <span>NUP Lunas</span>
                                        </span>
                                    @elseif($lead->payment_status === 'pending')
                                        <span class="px-2 py-0.5 bg-amber-100 text-amber-800 rounded-full font-bold text-[10px] inline-flex items-center gap-1">
                                            <svg class="w-3 h-3 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>Menunggu Bayar</span>
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 bg-rose-100 text-rose-800 rounded-full font-bold text-[10px] inline-flex items-center gap-1">
                                            <svg class="w-3 h-3 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            <span>{{ strtoupper($lead->payment_status) }}</span>
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3.5">
                                    @if($lead->kprScreening)
                                        @php
                                            $scStatus = $lead->kprScreening->status;
                                        @endphp
                                        @if($scStatus === 'approved')
                                            <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded-full font-bold text-[10px] inline-flex items-center gap-1">
                                                <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                <span>Lolos Screening</span>
                                            </span>
                                        @elseif($scStatus === 'layak_bersyarat')
                                            <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded-full font-bold text-[10px] inline-flex items-center gap-1">
                                                <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <span>Layak Bersyarat</span>
                                            </span>
                                        @elseif($scStatus === 'rejected')
                                            <span class="px-2 py-0.5 bg-rose-100 text-rose-800 rounded-full font-bold text-[10px] inline-flex items-center gap-1">
                                                <svg class="w-3 h-3 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                <span>Ditolak</span>
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded-full font-bold text-[10px] inline-flex items-center gap-1">
                                                <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <span>Sedang Diverifikasi</span>
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-slate-400 text-[11px]">-</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3.5">
                                    @if($unitSel && $unitSel->housingUnit)
                                        <div class="font-bold text-slate-900 text-xs">
                                            {{ $unitSel->housingUnit->block }} - {{ $unitSel->housingUnit->number }}
                                        </div>
                                        <div class="text-[10px] text-slate-500">
                                            <span class="font-bold text-slate-700">{{ $unitSel->housingUnit->housingProject?->company?->company_name }}</span> &middot; {{ $unitSel->housingUnit->housingProject?->name }}
                                        </div>
                                        <div class="text-[10px] font-bold text-amber-800">
                                            Rp {{ number_format($unitSel->housingUnit->price, 0, ',', '.') }}
                                        </div>
                                    @else
                                        <span class="text-slate-400 text-[11px]">Belum memilih unit</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3.5">
                                    @if($unitSel)
                                        @if($unitSel->isUtkWaived())
                                            <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded-full font-bold text-[10px] inline-flex items-center gap-1">
                                                <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                                <span>Bebas UTK (Tunai)</span>
                                            </span>
                                        @elseif($unitSel->isUtkPaid())
                                            <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded-full font-bold text-[10px] inline-flex items-center gap-1">
                                                <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                <span>UTK LUNAS</span>
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 bg-amber-100 text-amber-800 rounded-full font-bold text-[10px] inline-flex items-center gap-1">
                                                <svg class="w-3 h-3 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <span>Belum Bayar UTK</span>
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-slate-400 text-[11px]">-</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3.5">
                                    @if($spr && $spr->status === 'paid')
                                        <span class="px-2 py-0.5 bg-purple-100 text-purple-800 rounded-full font-bold text-[10px] inline-flex items-center gap-1">
                                            <svg class="w-3 h-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <span>SPR TERBIT</span>
                                        </span>
                                    @elseif($spr)
                                        <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded-full font-bold text-[10px]">
                                            Proses SPR
                                        </span>
                                    @else
                                        <span class="text-slate-400 text-[11px]">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($leads->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $leads->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-12 text-slate-400 text-xs">
                <div class="w-12 h-12 mx-auto mb-3 bg-slate-100 text-slate-400 rounded-2xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <p class="font-bold text-slate-700 text-sm">Belum ada calon pembeli terdaftar.</p>
                <p class="mt-1">Calon pembeli yang memilih nama Anda saat mendaftar NUP akan otomatis muncul di halaman ini.</p>
            </div>
        @endif
    </div>

</div>
@endsection
