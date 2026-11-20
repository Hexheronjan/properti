@extends('layouts.admin')

@section('title', 'SPR Documents - Admin')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:text-blue-800">
            ← Kembali ke Dashboard Admin
        </a>
    </div>

    <!-- Alert Notifikasi -->
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl text-sm mb-6 flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl text-sm mb-6 flex items-center gap-2">
            <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Antrean Unit yang Menunggu Penerbitan Dokumen SPR --}}
    @if(isset($readySelections) && $readySelections->isNotEmpty())
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-2xl p-6 mb-8 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900">Unit Siap Diterbitkan Dokumen SPR</h2>
                        <p class="text-xs text-gray-600">Unit berikut telah lunas UTK / skema Tunai dan menunggu Operator menerbitkan SPR resmi.</p>
                    </div>
                </div>
                <span class="px-3 py-1 bg-blue-600 text-white text-xs font-bold rounded-full self-start sm:self-auto">
                    {{ $readySelections->count() }} Antrean Siap Terbit
                </span>
            </div>

            <div class="overflow-x-auto bg-white rounded-xl border border-blue-100 shadow-xs">
                <table class="min-w-full text-xs">
                    <thead>
                        <tr class="bg-blue-50/70 border-b border-blue-100 text-left text-gray-600 font-bold uppercase tracking-wider text-[11px]">
                            <th class="px-4 py-3">Pemohon</th>
                            <th class="px-4 py-3">Unit Kavling</th>
                            <th class="px-4 py-3">Harga Unit</th>
                            <th class="px-4 py-3">Rencana Skema</th>
                            <th class="px-4 py-3">Status UTK</th>
                            <th class="px-4 py-3 text-right">Aksi Operator</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($readySelections as $ready)
                            @php
                                $rScheme = $ready->payment_scheme ?: ($ready->nupRegistration?->payment_method ?: 'inhouse');
                            @endphp
                            <tr class="hover:bg-blue-50/30 transition">
                                <td class="px-4 py-3">
                                    <strong class="text-gray-900 block text-xs">{{ $ready->nupRegistration?->full_name ?: $ready->user->name }}</strong>
                                    <span class="text-gray-400 font-mono text-[11px]">{{ $ready->nupRegistration?->queue_number ?: 'NUP-'.$ready->nup_registration_id }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-bold text-gray-900 block">{{ $ready->housingUnit?->full_identifier }}</span>
                                    <span class="text-gray-500 text-[11px]">{{ $ready->housingUnit?->housingProject?->name }}</span>
                                </td>
                                <td class="px-4 py-3 font-bold text-gray-900">
                                    Rp {{ number_format($ready->housingUnit?->price ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3">
                                    @if(in_array($rScheme, ['inhouse', 'installment'], true))
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-indigo-100 text-indigo-800">In-House Developer</span>
                                    @elseif(in_array($rScheme, ['cash', 'tunai'], true))
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">Tunai (Cash)</span>
                                    @elseif(in_array($rScheme, ['kpr', 'kpr_bank_flpp'], true))
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800">KPR Bank</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-gray-100 text-gray-800">{{ strtoupper($rScheme) }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($ready->isCash())
                                        <span class="text-emerald-700 font-bold">Bebas UTK</span>
                                    @else
                                        <span class="text-emerald-700 font-bold">Lunas UTK</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.spr.create', $ready->id) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-sm transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        <span>Terbitkan SPR</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
        <h1 class="text-xl font-bold text-gray-800 mb-6 flex items-center justify-between">
            <span>Daftar Dokumen Surat Pemesanan Rumah (SPR) Resmi</span>
            <span class="text-xs font-normal text-gray-500">Total: {{ $sprDocuments->total() }} Dokumen</span>
        </h1>
            @if($sprDocuments->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">No. SPR</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Peserta</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Unit</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Skema</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Status Pengajuan / Pencairan</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Status SPR</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sprDocuments as $spr)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm font-bold text-gray-900">{{ $spr->spr_number }}</td>
                                    <td class="px-4 py-3 text-sm">{{ $spr->user->name }}</td>
                                    <td class="px-4 py-3 text-sm">{{ $spr->unitSelection?->housingUnit?->full_identifier ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm uppercase font-bold text-xs">
                                        @if($spr->payment_scheme === 'mitra_pintar')
                                            <span class="px-2 py-0.5 bg-amber-100 text-amber-800 rounded-full text-xs font-bold">Mitra Pintar</span>
                                        @else
                                            {{ $spr->payment_scheme }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        @if($spr->payment_scheme === 'kpr')
                                            @if($spr->isSp3kApproved())
                                                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded-full text-xs font-bold"><svg class="w-4 h-4 inline-block text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> SP3K ACC</span>
                                            @elseif($spr->isSp3kRejected())
                                                <span class="px-2 py-0.5 bg-rose-100 text-rose-800 rounded-full text-xs font-bold"><svg class="w-4 h-4 inline-block text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg> Ditolak Bank</span>
                                            @elseif($spr->argo_deadline)
                                                <span class="px-2 py-0.5 bg-indigo-100 text-indigo-800 rounded-full text-xs font-bold"><svg class="w-4 h-4 inline-block text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Argo: {{ $spr->getArgoRemainingText() }}</span>
                                            @else
                                                <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full text-xs">Menunggu Pengajuan</span>
                                            @endif
                                        @elseif($spr->payment_scheme === 'mitra_pintar')
                                            @if($spr->status === 'paid' || $spr->isFullyPaid())
                                                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded-full text-xs font-bold"><svg class="w-4 h-4 inline-block text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Pencairan Mitra Diterima</span>
                                            @else
                                                <span class="px-2 py-0.5 bg-amber-100 text-amber-800 rounded-full text-xs font-bold"><svg class="w-4 h-4 inline-block text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Menunggu Pencairan Mitra</span>
                                            @endif
                                        @else
                                            @if($spr->isFullyPaid())
                                                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded-full text-xs font-bold"><svg class="w-4 h-4 inline-block text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Lunas</span>
                                            @else
                                                <span class="text-xs text-gray-500">Progres: {{ number_format($spr->getTotalPaymentProgress(), 0) }}%</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        @if($spr->status === 'paid' || $spr->isFullyPaid())
                                            @if($spr->bast_file)
                                                <span class="px-2 py-1 bg-emerald-600 text-white rounded-full text-xs font-bold"><svg class="w-4 h-4 inline-block text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg> BAST Selesai</span>
                                            @else
                                                <span class="px-2 py-1 bg-emerald-100 text-emerald-800 rounded-full text-xs font-bold"><svg class="w-4 h-4 inline-block text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Lunas (100%)</span>
                                            @endif
                                        @elseif($spr->status === 'issued')
                                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold">Diterbitkan</span>
                                        @elseif($spr->status === 'draft')
                                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-bold">Draft</span>
                                        @elseif($spr->status === 'cancelled')
                                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-bold">Dibatalkan</span>
                                        @else
                                            <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs">{{ $spr->status }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            @if($spr->payment_scheme === 'mitra_pintar' && !$spr->isFullyPaid())
                                                <a href="{{ route('spr.show', $spr->id) }}#mitra-disbursement-panel" class="px-3 py-1 bg-amber-600 hover:bg-amber-700 text-white rounded text-xs font-bold transition shadow-xs">
                                                    <svg class="w-4 h-4 inline-block text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg> Konfirmasi Pencairan
                                                </a>
                                            @elseif($spr->payment_scheme === 'mitra_pintar' && $spr->isFullyPaid() && !$spr->bast_file)
                                                <a href="{{ route('spr.show', $spr->id) }}#bast-panel" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-bold transition shadow-xs">
                                                    <svg class="w-4 h-4 inline-block text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg> Jadwalkan BAST
                                                </a>
                                            @endif
                                            <a href="{{ route('spr.show', $spr->id) }}" class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-bold transition">
                                                Kelola SPR
                                            </a>
                                            @if($spr->status === 'draft')
                                                <form action="{{ route('spr.issue', $spr->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-green-600 hover:text-green-800 text-xs font-bold">Terbitkan</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($sprDocuments->hasPages())
                    <div class="mt-4">
                        {{ $sprDocuments->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-8 text-gray-500">
                    <p>Belum ada dokumen SPR.</p>
                </div>
            @endif
    </div>
</div>
@endsection
