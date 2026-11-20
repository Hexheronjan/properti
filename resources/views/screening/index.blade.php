@extends('layouts.app')

@section('title', 'Screening KPR')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-1 flex items-center gap-2">
            <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
            <span>Daftar Screening KPR</span>
        </h1>
        <p class="text-sm text-gray-500">Pantau status verifikasi berkas dan hasil skor Pefindo pengajuan KPR Anda.</p>
    </div>

    @if($screenings->count() > 0)
        <div class="space-y-4">
            @foreach($screenings as $screening)
                @php
                    $rejectedDocs = $screening->userDocuments->where('status', 'rejected');
                    $hasRejection = $rejectedDocs->isNotEmpty();
                    $kprBanks = config('nup.kpr_banks');
                    $bankName = $screening->bank === 'other' ? ($screening->bank_other ?: 'Bank Lainnya') : ($kprBanks[$screening->bank] ?? ($screening->bank ? strtoupper($screening->bank) : 'Ditentukan Developer'));

                    $isCancelledByUser = ($screening->participant_decision === 'not_agree' || $screening->rejection_reason === 'User membatalkan pengajuan KPR');
                    $isSwitchedToInhouse = ($screening->participant_decision === 'switch_to_inhouse' || ($screening->nupRegistration?->kpr_switched && in_array($screening->nupRegistration?->payment_method, ['inhouse', 'installment'], true)));
                    $isSwitchedToCash = ($screening->participant_decision === 'switch_to_cash' || ($screening->nupRegistration?->kpr_switched && in_array($screening->nupRegistration?->payment_method, ['cash', 'tunai'], true)));
                    $isContinueKpr = in_array($screening->participant_decision, ['select_unit', 'agree_utk'], true);

                    $activeSel = $screening->unitSelection ?? $screening->nupRegistration?->unitSelections()->latest()->first();
                    $isSelUtkPaid = $activeSel ? ($activeSel->isCash() || $activeSel->isUtkWaived() || $activeSel->isUtkPaid()) : false;
                @endphp
                <div class="bg-white rounded-2xl shadow-sm border {{ $hasRejection ? 'border-rose-300 ring-2 ring-rose-100' : 'border-gray-200' }} p-6">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 pb-4 border-b border-gray-100">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-lg text-gray-900">{{ $screening->screening_number }}</h3>
                                @if($hasRejection)
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300 animate-pulse">
                                        PERLU REVISI BERKAS
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Tanggal Pengajuan: {{ $screening->created_at->format('d F Y H:i') }}
                            </p>
                        </div>
                        <div class="mt-2 md:mt-0">
                            @if($isSwitchedToInhouse)
                                <span class="bg-indigo-100 text-indigo-800 text-xs font-bold px-3 py-1 rounded-full border border-indigo-300">
                                    Beralih ke In-House
                                </span>
                            @elseif($isSwitchedToCash)
                                <span class="bg-emerald-100 text-emerald-800 text-xs font-bold px-3 py-1 rounded-full border border-emerald-300">
                                    Beralih ke Tunai
                                </span>
                            @elseif($isCancelledByUser)
                                <span class="bg-slate-100 text-slate-700 text-xs font-bold px-3 py-1 rounded-full border border-slate-300">
                                    Dibatalkan Pemohon
                                </span>
                            @elseif($hasRejection)
                                <span class="bg-rose-100 text-rose-800 text-xs font-bold px-3 py-1 rounded-full border border-rose-300">
                                    Menunggu Revisi Pemohon
                                </span>
                            @elseif($screening->isLayakProses())
                                <span class="bg-emerald-100 text-emerald-800 text-xs font-bold px-3 py-1 rounded-full border border-emerald-300">
                                    Layak Proses
                                </span>
                            @elseif($screening->isLayakBersyarat())
                                <span class="bg-amber-100 text-amber-800 text-xs font-bold px-3 py-1 rounded-full border border-amber-300">
                                    Layak Bersyarat
                                </span>
                            @elseif($screening->isTidakLayak())
                                <span class="bg-rose-100 text-rose-800 text-xs font-bold px-3 py-1 rounded-full border border-rose-300">
                                    Tidak Layak
                                </span>
                            @elseif($screening->status === 'submitted')
                                <span class="bg-blue-100 text-blue-800 text-xs font-bold px-3 py-1 rounded-full border border-blue-300">
                                    Berkas Dikirim (Sedang Ditinjau)
                                </span>
                            @else
                                <span class="bg-gray-100 text-gray-800 text-xs font-bold px-3 py-1 rounded-full border border-gray-300">
                                    {{ ucfirst(str_replace('_', ' ', $screening->status)) }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Notifikasi Jika Ada Berkas yang Ditolak / Perlu Revisi --}}
                    @if($hasRejection)
                        <div class="mb-4 p-4 bg-rose-50 border border-rose-300 rounded-xl text-xs text-rose-900">
                            <div class="flex items-center gap-2 font-bold mb-1.5 text-rose-950">
                                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                                <span>Petugas Meminta Revisi Dokumen:</span>
                            </div>
                            <div class="space-y-1.5 bg-white/90 p-3 rounded-lg border border-rose-200">
                                @foreach($rejectedDocs as $rDoc)
                                    <div>
                                        <p class="font-bold text-rose-900">• {{ \App\Models\KprScreening::documentLabels()[$rDoc->document_type] ?? $rDoc->document_type }}</p>
                                        <p class="text-rose-700 ml-3 mt-0.5">Catatan: "{{ $rDoc->rejection_reason ?? 'Berkas kurang jelas / tidak sesuai. Silakan upload ulang.' }}"</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs mb-4 bg-gray-50 p-3.5 rounded-xl border border-gray-100">
                        <div>
                            <span class="text-gray-400 block">Bank Rekanan:</span>
                            @if($isSwitchedToInhouse)
                                <span class="font-bold text-indigo-700">In-House (Cicilan Pengembang)</span>
                            @elseif($isSwitchedToCash)
                                <span class="font-bold text-emerald-700">Tunai (Tanpa Bank)</span>
                            @elseif($isCancelledByUser)
                                <span class="font-bold text-slate-500 line-through">Dibatalkan</span>
                            @else
                                <span class="font-bold text-gray-800">{{ $bankName }}</span>
                            @endif
                        </div>
                        <div>
                            <span class="text-gray-400 block">Plafon Pinjaman:</span>
                            @if($isSwitchedToInhouse || $isSwitchedToCash)
                                <span class="font-bold text-gray-700">Bukan KPR (Skema Mandiri)</span>
                            @elseif($isCancelledByUser)
                                <span class="font-bold text-slate-400">-</span>
                            @else
                                <span class="font-bold text-gray-800">{{ $screening->loan_amount > 0 ? 'Rp ' . number_format($screening->loan_amount, 0, ',', '.') : 'Tahap Setelah Screening' }}</span>
                            @endif
                        </div>
                        <div>
                            <span class="text-gray-400 block">Pilihan Unit:</span>
                            <span class="font-bold text-gray-800">{{ $activeSel?->housingUnit?->unit_number ?? $screening->unitSelection?->housingUnit?->unit_number ?? 'Tahap Setelah Screening' }}</span>
                        </div>
                    </div>

                    @php
                        $existingSpr = $activeSel?->sprDocuments()?->whereNotIn('status', ['cancelled'])->first();
                    @endphp
                    <div class="border-t border-gray-100 pt-4 flex flex-col sm:flex-row items-center justify-between gap-3">
                        <div class="text-xs text-gray-500">
                            @if($isSwitchedToInhouse)
                                <div class="flex items-center gap-1.5 text-indigo-900 font-semibold">
                                    <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                                    <span>
                                        Anda telah beralih ke skema <strong>In-House Developer</strong> (Cicilan Pengembang).
                                        @if($existingSpr)
                                            Dokumen SPR telah resmi diterbitkan oleh pengembang.
                                        @elseif($activeSel && $isSelUtkPaid)
                                            Menunggu Operator Pengembang menerbitkan dokumen SPR resmi.
                                        @else
                                            Pengajuan KPR Bank tidak dilanjutkan.
                                        @endif
                                    </span>
                                </div>
                            @elseif($isSwitchedToCash)
                                <div class="flex items-center gap-1.5 text-emerald-900 font-semibold">
                                    <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                    <span>
                                        Anda telah beralih ke skema <strong>Pembayaran Tunai</strong>.
                                        @if($existingSpr)
                                            Dokumen SPR telah resmi diterbitkan oleh pengembang.
                                        @elseif($activeSel && $isSelUtkPaid)
                                            Menunggu Operator Pengembang menerbitkan dokumen SPR resmi.
                                        @else
                                            Pengajuan KPR Bank tidak dilanjutkan.
                                        @endif
                                    </span>
                                </div>
                            @elseif($isCancelledByUser)
                                <div class="flex items-center gap-1.5 text-slate-700 font-semibold">
                                    <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                                    <span>Pengajuan KPR telah <strong>dibatalkan secara mandiri</strong>. Tiket antrean NUP terkait telah hangus.</span>
                                </div>
                            @elseif($hasRejection)
                                <span class="text-rose-700 font-bold">Silakan klik tombol di samping untuk mengunggah ulang dokumen revisi.</span>
                            @elseif($screening->hasParticipantDecision())
                                <span>
                                    Keputusan Anda telah tercatat: <strong>Lanjut Proses KPR Bank</strong>.
                                    @if($existingSpr)
                                        Dokumen SPR telah diterbitkan untuk bank.
                                    @elseif($activeSel && $isSelUtkPaid)
                                        Menunggu pengembang menerbitkan dokumen SPR resmi.
                                    @endif
                                </span>
                            @elseif($screening->isApproved() || $screening->isLayakBersyarat())
                                <span class="text-emerald-700 font-bold">Hasil screening telah keluar! Silakan tentukan keputusan Anda.</span>
                            @else
                                <span class="text-gray-600">Berkas Anda sedang dalam proses peninjauan oleh petugas/analis.</span>
                            @endif
                        </div>

                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            @if($hasRejection)
                                <a href="{{ route('kpr.show', $screening->id) }}" class="w-full sm:w-auto px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition shadow-sm inline-flex items-center justify-center gap-1.5">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                                    <span>Upload Ulang Berkas Revisi Sekarang &rarr;</span>
                                </a>
                            @elseif($isCancelledByUser)
                                <a href="{{ route('kpr.show', $screening->id) }}" class="w-full sm:w-auto px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition border border-slate-300 inline-flex items-center justify-center gap-1.5">
                                    <span>Lihat Rincian &rarr;</span>
                                </a>
                            @elseif($isSwitchedToInhouse || $isSwitchedToCash)
                                @if(!$activeSel)
                                    <a href="{{ route('units.create', $screening->nup_registration_id) }}" class="w-full sm:w-auto px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition shadow-sm inline-flex items-center justify-center gap-1.5">
                                        <span>Pilih Unit Kavling &rarr;</span>
                                    </a>
                                @elseif(!$isSelUtkPaid)
                                    <a href="{{ route('units.show', $activeSel->id) }}" class="w-full sm:w-auto px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition shadow-sm inline-flex items-center justify-center gap-1.5">
                                        <span>Bayar UTK Unit Terpilih &rarr;</span>
                                    </a>
                                @elseif($existingSpr)
                                    <a href="{{ route('spr.show', $existingSpr->id) }}" class="w-full sm:w-auto px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-sm inline-flex items-center justify-center gap-1.5">
                                        <span>Tinjau Dokumen SPR &rarr;</span>
                                    </a>
                                @else
                                    <a href="{{ route('units.show', $activeSel->id) }}" class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-sm inline-flex items-center justify-center gap-1.5">
                                        <span>Lihat Detail Unit &rarr;</span>
                                    </a>
                                @endif
                                <a href="{{ route('kpr.show', $screening->id) }}" class="w-full sm:w-auto px-3.5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-bold transition border border-gray-200 inline-flex items-center justify-center">
                                    <span>Detail Skor</span>
                                </a>
                            @elseif($screening->hasParticipantDecision())
                                @if(!$activeSel)
                                    <a href="{{ route('units.create', $screening->nup_registration_id) }}" class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-sm inline-flex items-center justify-center gap-1.5">
                                        <span>Pilih Unit Kavling &rarr;</span>
                                    </a>
                                @elseif(!$isSelUtkPaid)
                                    <a href="{{ route('units.show', $activeSel->id) }}" class="w-full sm:w-auto px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition shadow-sm inline-flex items-center justify-center gap-1.5">
                                        <span>Bayar UTK Unit Terpilih &rarr;</span>
                                    </a>
                                @elseif($existingSpr)
                                    <a href="{{ route('spr.show', $existingSpr->id) }}" class="w-full sm:w-auto px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-sm inline-flex items-center justify-center gap-1.5">
                                        <span>Tinjau Dokumen SPR &rarr;</span>
                                    </a>
                                @else
                                    <a href="{{ route('units.show', $activeSel->id) }}" class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-sm inline-flex items-center justify-center gap-1.5">
                                        <span>Lihat Detail Unit &rarr;</span>
                                    </a>
                                @endif
                                <a href="{{ route('kpr.show', $screening->id) }}" class="w-full sm:w-auto px-3.5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-bold transition border border-gray-200 inline-flex items-center justify-center">
                                    <span>Detail Skor</span>
                                </a>
                            @elseif($screening->isApproved() || $screening->isLayakBersyarat())
                                <a href="{{ route('kpr.outcome', $screening->id) }}" class="w-full sm:w-auto px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-sm inline-flex items-center justify-center gap-1.5">
                                    <span>Lihat Hasil Skor & Buat Keputusan &rarr;</span>
                                </a>
                            @else
                                <a href="{{ route('kpr.show', $screening->id) }}" class="w-full sm:w-auto px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-sm inline-flex items-center justify-center gap-1.5">
                                    <span>Buka Detail & Berkas Screening &rarr;</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-2xl shadow-sm border border-gray-150 p-8 text-center max-w-4xl mx-auto">
            <svg class="w-14 h-14 text-blue-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Belum Ada Screening KPR</h3>
            <p class="text-sm text-gray-500 mb-8 max-w-lg mx-auto">Untuk memulai proses screening KPR Bank, Anda perlu melengkapi seluruh tahapan pembelian unit rumah terlebih dahulu.</p>
            
            {{-- Stepper Progress Bar --}}
            @php
                $hasNup = auth()->user()->nupRegistrations()->where('payment_status', 'paid')->exists();
                $hasUnit = (bool)$unitSelection;
            @endphp
            
            <div class="flex items-center justify-between mb-10 max-w-2xl mx-auto relative">
                <!-- Step 1: NUP -->
                <div class="flex flex-col items-center z-10 flex-1">
                    <div class="w-10 h-10 {{ $hasNup ? 'bg-emerald-500 text-white' : 'bg-blue-600 text-white' }} rounded-full flex items-center justify-center font-bold text-sm shadow-sm transition-colors">
                        @if($hasNup) <svg class="w-4 h-4 inline-block text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> @else 1 @endif
                    </div>
                    <span class="text-xs font-semibold mt-2 {{ $hasNup ? 'text-emerald-600' : 'text-blue-600' }}">Daftar NUP</span>
                </div>
                
                <div class="h-1 flex-1 -mx-8 -mt-5 {{ $hasNup ? 'bg-emerald-500' : 'bg-gray-200' }}"></div>

                <!-- Step 2: Pilih Unit -->
                <div class="flex flex-col items-center z-10 flex-1">
                    <div class="w-10 h-10 {{ $hasUnit ? 'bg-emerald-500 text-white' : ($hasNup ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-500') }} rounded-full flex items-center justify-center font-bold text-sm shadow-sm transition-colors">
                        @if($hasUnit) <svg class="w-4 h-4 inline-block text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> @else 2 @endif
                    </div>
                    <span class="text-xs font-semibold mt-2 {{ $hasUnit ? 'text-emerald-600' : ($hasNup ? 'text-blue-600' : 'text-gray-400') }}">Pilih Unit</span>
                </div>
                
                <div class="h-1 flex-1 -mx-8 -mt-5 {{ $hasUnit ? 'bg-emerald-500' : 'bg-gray-200' }}"></div>

                <!-- Step 3: Bayar UTK -->
                <div class="flex flex-col items-center z-10 flex-1">
                    <div class="w-10 h-10 {{ $hasUnit ? 'bg-amber-500 text-white animate-pulse' : 'bg-gray-200 text-gray-500' }} rounded-full flex items-center justify-center font-bold text-sm shadow-sm transition-colors">
                        3
                    </div>
                    <span class="text-xs font-semibold mt-2 {{ $hasUnit ? 'text-amber-600 font-bold' : 'text-gray-400' }}">Bayar UTK</span>
                </div>
                
                <div class="h-1 flex-1 -mx-8 -mt-5 bg-gray-200"></div>

                <!-- Step 4: Screening KPR -->
                <div class="flex flex-col items-center z-10 flex-1">
                    <div class="w-10 h-10 bg-gray-200 text-gray-500 rounded-full flex items-center justify-center font-bold text-sm shadow-sm">
                        4
                    </div>
                    <span class="text-xs font-semibold mt-2 text-gray-400">Screening KPR</span>
                </div>
            </div>

            {{-- Conditional Action Card --}}
            @if($unitSelection)
                @if($unitSelection->isUtkPaid() || $unitSelection->isConfirmed())
                    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-6 text-left max-w-xl mx-auto shadow-sm">
                        <div class="flex items-start gap-3">
                            <span class="text-2xl mt-0.5"><svg class="w-6 h-6 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></span>
                            <div class="flex-1">
                                <h4 class="font-bold text-blue-900 text-base mb-1">Unit Terkunci — Lengkapi Berkas Screening KPR</h4>
                                <p class="text-sm text-blue-800 leading-relaxed mb-4">Unit <strong class="text-blue-950 font-bold">"{{ $unitSelection->housingUnit?->full_identifier }}"</strong> telah berhasil dikunci dan UTK lunas. Silakan lengkapi data pekerjaan dan unggah berkas persyaratan KPR Anda sekarang agar dapat segera diverifikasi.</p>
                                <a href="{{ route('kpr.create', $unitSelection->id) }}" class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition shadow-sm text-sm">
                                    <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    Lengkapi Berkas KPR Sekarang &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 text-left max-w-xl mx-auto shadow-sm">
                        <div class="flex items-start gap-3">
                            <span class="text-2xl mt-0.5"><svg class="w-4 h-4 inline-block text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></span>
                            <div class="flex-1">
                                <h4 class="font-bold text-amber-800 text-base mb-1">Unit Terpilih Belum Lunas UTK</h4>
                                <p class="text-sm text-amber-700 leading-relaxed mb-4"> Anda sudah memilih unit <strong class="text-amber-900 font-bold">"{{ $unitSelection->housingUnit?->full_identifier }}"</strong>. Silakan selesaikan pembayaran UTK (Uang Tanda Jadi) Anda terlebih dahulu agar proses verifikasi berkas KPR dapat dimulai.</p>
                                <a href="{{ route('units.show', $unitSelection->id) }}" class="inline-flex items-center gap-2 px-5 py-3 bg-amber-600 text-white font-bold rounded-xl hover:bg-amber-700 transition shadow-sm text-sm">
                                    <svg class="w-4 h-4 inline-block text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg> Bayar UTK Sekarang
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            @else
                <div class="bg-gray-50 rounded-2xl p-6 max-w-md mx-auto text-left border border-gray-150">
                    <h4 class="font-bold text-gray-800 mb-3 text-sm">Langkah selanjutnya untuk Anda:</h4>
                    <div class="space-y-3">
                        @if(!$hasNup)
                            <div class="flex items-start gap-2 text-sm text-gray-600">
                                <span class="text-blue-600 font-bold">1.</span>
                                <span>Lakukan pendaftaran NUP Anda.</span>
                            </div>
                        @elseif(!$hasUnit)
                            <div class="flex items-start gap-2 text-sm text-gray-600">
                                <span class="text-blue-600 font-bold">2.</span>
                                <span>Masuk ke menu unit untuk memilih unit rumah perumahan pilihan Anda.</span>
                            </div>
                        @endif
                        <div class="flex items-start gap-2 text-sm text-gray-400">
                            <span class="font-bold">3.</span>
                            <span>Bayar UTK (Uang Tanda Komitmen) dari unit terpilih.</span>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex gap-3">
                        @if(!$hasNup)
                            <a href="{{ route('nup.register') }}" class="flex-1 text-center py-3 bg-green-600 text-white rounded-xl hover:bg-green-700 transition font-bold text-sm shadow-sm">
                                Daftar NUP
                            </a>
                        @elseif(!$hasUnit)
                            <a href="{{ route('units.index') }}" class="flex-1 text-center py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-bold text-sm shadow-sm">
                                Pilih Unit Rumah
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    @endif

</div>
@endsection
