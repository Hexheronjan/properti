@extends('layouts.admin')

@section('title', 'Unit Selections - Admin')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:text-blue-800 text-sm font-semibold flex items-center gap-1">
            ← Kembali ke Dashboard Admin
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm font-semibold flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm font-semibold flex items-center gap-2">
            <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6 pb-3 border-b border-gray-100 flex-wrap gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Daftar Pemilihan Unit</h1>
                <p class="text-xs text-gray-500 mt-0.5">Kelola data peserta yang telah memilih unit perumahan dan verifikasi dokumen Mitra Pintar.</p>
            </div>
        </div>

        @if($unitSelections->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-xs">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 font-bold uppercase text-[11px]">
                            <th class="px-4 py-3 text-left">ID & Peserta</th>
                            <th class="px-4 py-3 text-left">Unit & Tipe</th>
                            <th class="px-4 py-3 text-left">Skema Pembayaran</th>
                            <th class="px-4 py-3 text-left">Dokumen Mitra Pintar</th>
                            <th class="px-4 py-3 text-left">Status UTK & Unit</th>
                            <th class="px-4 py-3 text-center">Aksi Operator</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($unitSelections as $selection)
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="px-4 py-3">
                                    <div class="font-bold text-gray-900">#{{ $selection->id }} - {{ $selection->user->name }}</div>
                                    <div class="text-[11px] text-gray-500">{{ $selection->user->phone ?? '-' }}</div>
                                    <div class="text-[10px] text-gray-400 mt-0.5">Pilih: {{ $selection->selection_date ? $selection->selection_date->format('d/m/Y H:i') : '-' }}</div>
                                </td>

                                <td class="px-4 py-3">
                                    <div class="font-bold text-blue-700">
                                        {{ $selection->housingUnit->block }} - {{ $selection->housingUnit->number }}
                                    </div>
                                    <div class="text-gray-600">{{ config('nup.unit_types.' . $selection->housingUnit->type) }}</div>
                                    <div class="text-[11px] text-gray-500">Rp {{ number_format($selection->housingUnit->price, 0, ',', '.') }}</div>
                                </td>

                                <td class="px-4 py-3">
                                    @php
                                        $scheme = $selection->sprDocuments->first()?->payment_scheme ?: $selection->payment_scheme;
                                    @endphp
                                    @if($selection->isCash() || in_array($scheme, ['cash', 'tunai', 'tunai_keras'], true))
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-100 text-emerald-800 rounded-lg font-bold text-[11px]">
                                            <svg class="w-3.5 h-3.5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                            <span>Tunai (Bebas UTK)</span>
                                        </span>
                                    @elseif($selection->isTunaiBertahap() || $scheme === 'tunai_bertahap')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-teal-100 text-teal-800 rounded-lg font-bold text-[11px]">
                                            <svg class="w-3.5 h-3.5 text-teal-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>Tunai Bertahap</span>
                                        </span>
                                    @elseif($scheme === 'mitra_pintar')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-amber-100 text-amber-900 rounded-lg font-bold text-[11px]">
                                            <svg class="w-3.5 h-3.5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                            <span>Mitra Pintar</span>
                                        </span>
                                    @elseif($selection->isInHouse() || in_array($scheme, ['inhouse', 'installment'], true))
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-indigo-100 text-indigo-800 rounded-lg font-bold text-[11px]">
                                            <svg class="w-3.5 h-3.5 text-indigo-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <span>In-House Developer</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-blue-100 text-blue-800 rounded-lg font-bold text-[11px]">
                                            <svg class="w-3.5 h-3.5 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                            <span>KPR Bank</span>
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3">
                                    @if($selection->isMitraPintar())
                                        <div class="space-y-1">
                                            <div class="font-semibold text-gray-900 inline-flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                                <span>{{ $selection->mitra_institution_name ?? '-' }}</span>
                                            </div>
                                            @if($selection->mitra_document)
                                                <div>
                                                    <a href="{{ asset('storage/' . $selection->mitra_document) }}" target="_blank" class="inline-flex items-center gap-1 text-blue-600 hover:underline font-bold text-[11px]">
                                                        <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                        <span>Lihat Berkas Kerja Sama</span>
                                                    </a>
                                                </div>
                                            @else
                                                <span class="text-gray-400 text-[10px]">Belum ada berkas</span>
                                            @endif

                                            <div>
                                                @if($selection->isMitraApproved())
                                                    <span class="px-2 py-0.5 bg-green-100 text-green-800 rounded font-semibold text-[10px] inline-flex items-center gap-1">
                                                        <svg class="w-3 h-3 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                        <span>ACC (Disetujui)</span>
                                                    </span>
                                                @elseif($selection->isMitraRejected())
                                                    <span class="px-2 py-0.5 bg-red-100 text-red-800 rounded font-semibold text-[10px] inline-flex items-center gap-1" title="{{ $selection->mitra_rejection_reason }}">
                                                        <svg class="w-3 h-3 text-red-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                        <span>Ditolak</span>
                                                    </span>
                                                    <div class="text-[10px] text-red-600 truncate max-w-xs">{{ $selection->mitra_rejection_reason }}</div>
                                                @else
                                                    <span class="px-2 py-0.5 bg-yellow-100 text-yellow-800 rounded font-semibold text-[10px] inline-flex items-center gap-1">
                                                        <svg class="w-3 h-3 text-yellow-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                        <span>Menunggu Verifikasi</span>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3">
                                    <div class="space-y-1">
                                        <div>
                                            <span class="px-2 py-0.5 rounded-full font-bold text-[10px]
                                                @if($selection->status === 'confirmed') bg-green-100 text-green-800
                                                @elseif($selection->status === 'cancelled') bg-red-100 text-red-800
                                                @else bg-blue-100 text-blue-800 @endif
                                            ">
                                                Unit: {{ strtoupper($selection->status) }}
                                            </span>
                                        </div>
                                        <div class="text-[11px] text-gray-500">
                                            UTK: 
                                            @if($selection->isUtkWaived())
                                                <span class="font-bold text-emerald-600">Rp 0 (Bebas)</span>
                                            @elseif($selection->isUtkPaid())
                                                <span class="font-bold text-green-600">Lunas</span>
                                            @else
                                                <span class="font-bold text-orange-600">Belum Bayar</span>
                                            @endif
                                        </div>
                                        @if($selection->utk_payment_deadline && !$selection->isUtkPaid() && !$selection->isUtkWaived() && $selection->status !== 'cancelled')
                                            <div class="text-[10px] {{ $selection->isUtkPaymentDeadlinePassed() ? 'text-red-600 font-bold' : 'text-gray-500' }}">
                                                ⏱️ Batas: {{ $selection->utk_payment_deadline->format('d/m/Y H:i') }}
                                                @if($selection->isUtkPaymentDeadlinePassed())
                                                    <span class="block text-red-600 font-bold">(Lewat Waktu)</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                        <a href="{{ route('units.show', $selection->id) }}" class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-semibold text-xs transition">
                                            Detail
                                        </a>

                                        <!-- Menu "Tidak Jadi Batal" (Restore) hanya jika unit masih AVAILABLE (Belum diambil orang lain) -->
                                        @if($selection->status === 'cancelled')
                                            @if($selection->housingUnit && $selection->housingUnit->status === 'available')
                                                <form action="{{ route('admin.units.restore', $selection->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin memulihkan pemesanan unit ini (Tidak Jadi Batal)? Status unit akan kembali dipesan (Booked).')">
                                                    @csrf
                                                    <button type="submit" class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-white rounded-lg font-bold text-xs shadow-sm transition inline-flex items-center gap-1.5" title="Pulihkan / Aktifkan kembali pemesanan">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                        <span>Tidak Jadi Batal</span>
                                                    </button>
                                                </form>
                                            @else
                                                <span class="px-2 py-1 bg-slate-100 text-slate-400 border border-slate-200 rounded-lg text-[11px] font-medium inline-flex items-center gap-1 cursor-not-allowed" title="Unit ini sudah tidak tersedia (telah dipesan atau dibeli oleh konsumen lain)">
                                                    <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                    Unit Terisi
                                                </span>
                                            @endif
                                        @elseif($selection->isConfirmed() || $selection->isUtkPaid() || $selection->isCash())
                                            <!-- Jika Sudah Lunas (CONFIRMED): Tombol ke Tahap SPR -->
                                            @php
                                                $hasSpr = $selection->sprDocuments && $selection->sprDocuments->isNotEmpty();
                                            @endphp
                                            @if(!$hasSpr)
                                                <a href="{{ route('admin.spr.create', $selection->id) }}" class="px-2 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-lg font-semibold text-xs transition inline-flex items-center gap-1" title="Buat & Terbitkan Dokumen SPR">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                    <span>+ SPR</span>
                                                </a>
                                            @else
                                                <a href="{{ route('admin.spr.index') }}" class="px-2 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-lg font-semibold text-xs transition inline-flex items-center gap-1" title="Lihat Daftar SPR">
                                                    <svg class="w-4 h-4 inline-block text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg> SPR Terbit
                                                </a>
                                            @endif
                                        @else
                                            <!-- Tombol Batalkan Pemesanan (Hanya untuk unit yang Belum Lunas / Masih Selected) -->
                                            <button type="button" onclick="openCancelModal({{ $selection->id }}, '{{ addslashes($selection->user->name) }}', '{{ addslashes($selection->housingUnit->unit_code ?? ($selection->housingUnit->block . ' - ' . $selection->housingUnit->number)) }}')" class="px-2 py-1 bg-gray-100 hover:bg-red-50 text-red-600 hover:text-red-700 border border-gray-200 hover:border-red-300 rounded-lg font-semibold text-xs transition inline-flex items-center gap-1" title="Batalkan Pemesanan Unit">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                <span>Batalkan</span>
                                            </button>
                                        @endif

                                        <!-- Aksi Khusus Verifikasi Mitra Pintar -->
                                        @if($selection->isMitraPintar() && $selection->isMitraPending())
                                            <form action="{{ route('admin.units.mitra.approve', $selection->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin menyetujui dokumen kerja sama Mitra Pintar calon pembeli ini?')">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1 bg-green-600 hover:bg-green-700 text-white rounded-lg font-bold text-xs shadow-sm transition inline-flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                    <span>Setuju</span>
                                                </button>
                                            </form>

                                            <button type="button" onclick="openMitraRejectModal({{ $selection->id }}, '{{ addslashes($selection->user->name) }}')" class="px-2.5 py-1 bg-rose-600 hover:bg-rose-700 text-white rounded-lg font-bold text-xs shadow-sm transition inline-flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                <span>Tolak</span>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($unitSelections->hasPages())
                <div class="mt-4">
                    {{ $unitSelections->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-12 text-gray-500">
                <p class="text-sm">Belum ada pemilihan unit.</p>
            </div>
        @endif
    </div>
</div>

<!-- Modal Tolak Mitra Pintar -->
<div id="mitraRejectModal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100">
        <h3 class="text-base font-bold text-gray-900 mb-1 flex items-center gap-2">
            <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Tolak Dokumen Mitra Pintar</span>
        </h3>
        <p class="text-xs text-gray-500 mb-4" id="mitraRejectModalSubtitle">
            Masukkan catatan atau alasan penolakan dokumen kerja sama mitra pintar.
        </p>

        <form id="mitraRejectForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-xs font-bold text-gray-700 mb-1">Alasan Penolakan <span class="text-red-500">*</span></label>
                <textarea name="rejection_reason" required rows="3" class="w-full border border-gray-300 rounded-xl p-2.5 text-xs focus:ring-2 focus:ring-rose-500 focus:outline-none" placeholder="Contoh: Dokumen kerja sama tidak valid / Perusahaan belum terdaftar dalam MoU / Surat keterangan buram"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2">
                <button type="button" onclick="closeMitraRejectModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-lg shadow-sm transition">
                    Kirim Penolakan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Batalkan Pemesanan Unit oleh Operator -->
<div id="cancelSelectionModal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100">
        <h3 class="text-base font-bold text-gray-900 mb-1 flex items-center gap-2">
            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span>Batalkan Pemesanan Unit</span>
        </h3>
        <p class="text-xs text-gray-500 mb-4" id="cancelModalSubtitle">
            Unit rumah akan otomatis dikembalikan ke status TERSEDIA (Available) untuk pembeli lain.
        </p>

        <form id="cancelSelectionForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-xs font-bold text-gray-700 mb-1">Alasan Pembatalan (Opsional)</label>
                <textarea name="reason" rows="3" class="w-full border border-gray-300 rounded-xl p-2.5 text-xs focus:ring-2 focus:ring-red-500 focus:outline-none" placeholder="Contoh: Pembeli mengajukan pembatalan sepihak / Gagal bayar UTK lewat batas waktu"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2">
                <button type="button" onclick="closeCancelModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition">
                    Tutup
                </button>
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-lg shadow-sm transition">
                    Ya, Batalkan Pemesanan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openMitraRejectModal(selectionId, userName) {
    const modal = document.getElementById('mitraRejectModal');
    const form = document.getElementById('mitraRejectForm');
    const subtitle = document.getElementById('mitraRejectModalSubtitle');

    form.action = '/admin/units/' + selectionId + '/mitra/reject';
    subtitle.textContent = 'Calon Pembeli: ' + userName + '. Masukkan alasan penolakan dokumen kerja sama:';
    modal.classList.remove('hidden');
}

function closeMitraRejectModal() {
    document.getElementById('mitraRejectModal').classList.add('hidden');
}

function openCancelModal(selectionId, userName, unitCode) {
    const modal = document.getElementById('cancelSelectionModal');
    const form = document.getElementById('cancelSelectionForm');
    const subtitle = document.getElementById('cancelModalSubtitle');

    form.action = '/admin/units/' + selectionId + '/cancel';
    subtitle.innerHTML = 'Pembeli: <strong>' + userName + '</strong> &middot; Unit: <strong>' + unitCode + '</strong>.<br>Unit rumah akan otomatis dikembalikan ke status <strong>TERSEDIA (Available)</strong>.';
    modal.classList.remove('hidden');
}

function closeCancelModal() {
    document.getElementById('cancelSelectionModal').classList.add('hidden');
}
</script>
@endsection
