@extends('layouts.app')

@section('title', 'Hasil Keputusan')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-2"><svg class="w-4 h-4 inline-block text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg> Hasil Keputusan Screening</h1>
        <p class="text-gray-600">Berikut adalah hasil keputusan dan status proses Anda.</p>
    </div>

    @if($screening->screening_decision === 'rejected')
        <!-- Rejected Decision -->
        <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
            <div class="text-center">
                <span class="text-6xl mb-4 block"><svg class="w-4 h-4 inline-block text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></span>
                <h3 class="font-bold text-xl text-red-800 mb-2">Keputusan: Tidak Melanjutkan</h3>
                <p class="text-sm text-red-700 mb-4">Anda memilih untuk tidak melanjutkan proses pemesanan unit.</p>
                
                @if($screening->decision_notes)
                    <div class="bg-white rounded-lg p-3 text-left">
                        <span class="text-xs text-gray-500">Catatan:</span>
                        <p class="text-sm text-gray-700">{{ $screening->decision_notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <div class="flex items-start">
                <span class="text-2xl mr-3"><svg class="w-4 h-4 inline-block text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></span>
                <div>
                    <h4 class="font-semibold text-yellow-800">Informasi Penting</h4>
                    <p class="text-sm text-yellow-700">NUP Anda tidak dapat direfund sesuai dengan kebijakan yang berlaku.</p>
                </div>
            </div>
        </div>

    @elseif($screening->screening_decision === 'approved')
        <!-- Approved Decision - Check if payment completed -->
        @if($screening->kprPayments()->where('status', 'success')->exists())
            <!-- Payment Successful -->
            <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-6">
                <div class="text-center">
                    <svg class="w-14 h-14 text-emerald-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                    <h3 class="font-bold text-xl text-green-800 mb-2">Selamat! Proses Berhasil</h3>
                    <p class="text-sm text-green-700 mb-4">Pembayaran uang tanda komitmen berhasil dan unit telah diamankan untuk Anda.</p>
                </div>
            </div>

            <!-- SPR Document Info -->
            @if($screening->sprDocuments()->exists())
                @php
                    $spr = $screening->sprDocuments()->first();
                @endphp
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2"><svg class="w-4 h-4 inline-block text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg> Dokumen SPR</h3>
                    
                    <div class="bg-gray-50 rounded-lg p-4 mb-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600">Nomor SPR:</span>
                                <span class="font-semibold ml-2">{{ $spr->spr_number }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Tanggal:</span>
                                <span class="font-semibold ml-2">{{ $spr->spr_date }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Status:</span>
                                <span class="font-semibold ml-2 text-green-600">{{ ucfirst($spr->status) }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">E-Materai:</span>
                                <span class="font-semibold ml-2 @if($spr->ematerai_status === 'stamped') text-green-600 @else text-yellow-600 @endif">
                                    {{ ucfirst($spr->ematerai_status) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    @if($spr->document_file)
                        <a href="{{ asset('storage/' . $spr->document_file) }}" target="_blank" 
                           class="inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            <svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg> Download SPR
                        </a>
                    @endif
                </div>
            @endif

            <!-- Unit Selection Info -->
            @if($screening->unitSelection)
                @php
                    $unitSelection = $screening->unitSelection;
                @endphp
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h3 class="font-semibold text-gray-800 mb-4"><svg class="w-4 h-4 inline-block text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg> Unit yang Dipilih</h3>
                    
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600">Nomor Unit:</span>
                                <span class="font-semibold ml-2">{{ $unitSelection->housingUnit->unit_code ?? $unitSelection->housingUnit->full_identifier }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Tipe:</span>
                                <span class="font-semibold ml-2">{{ $unitSelection->housingUnit->type }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Harga:</span>
                                <span class="font-semibold ml-2">Rp {{ number_format($unitSelection->housingUnit->price, 0, ',', '.') }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Status Unit:</span>
                                <span class="font-semibold ml-2 text-green-600">Diamankan</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        @else
            <!-- Payment Pending -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-6">
                <div class="text-center">
                    <span class="text-6xl mb-4 block"><svg class="w-4 h-4 inline-block text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></span>
                    <h3 class="font-bold text-xl text-yellow-800 mb-2">Menunggu Pembayaran</h3>
                    <p class="text-sm text-yellow-700 mb-4">Anda telah menyetujui klausul SPR. Silakan selesaikan pembayaran uang tanda komitmen.</p>
                    
                    <a href="{{ route('screening.commitment-payment', $screening->id) }}" 
                       class="inline-block px-6 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition">
                        Lanjut Pembayaran
                    </a>
                </div>
            </div>
        @endif

    @else
        <!-- No Decision Yet -->
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 mb-6">
            <div class="text-center">
                <svg class="w-16 h-16 text-amber-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <h3 class="font-bold text-xl text-gray-800 mb-2">Belum Ada Keputusan</h3>
                <p class="text-sm text-gray-700 mb-4">Anda belum membuat keputusan terkait hasil screening.</p>
                
                <a href="{{ route('screening.decision', $screening->id) }}" 
                   class="inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    Buat Keputusan
                </a>
            </div>
        </div>
    @endif

    <!-- Back to Dashboard -->
    <div class="mt-6 text-center">
        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800 text-sm">
            ← Kembali ke Dashboard
        </a>
    </div>
</div>
@endsection
