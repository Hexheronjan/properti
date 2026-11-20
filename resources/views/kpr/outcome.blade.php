@extends('layouts.app')

@section('title', 'Hasil Skor Pefindo & Pilihan Langkah KPR')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4">
    <div class="mb-6">
        <a href="{{ route('kpr.show', $screening->id) }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-blue-600 hover:text-blue-800 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            Kembali ke Berkas Screening
        </a>
    </div>

    <!-- Banner Hasil Skor Pefindo -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sm:p-8 mb-6">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <div class="flex-1">
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Hasil Pemeriksaan Skor Kredit</span>
                <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 mt-0.5">Hasil Skor Pefindo & Kelayakan KPR</h1>
                <p class="text-xs text-gray-500 mt-1">
                    Berdasarkan pengecekan riwayat kredit, berikut hasil evaluasi profil finansial Anda:
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6 bg-emerald-50/60 p-5 rounded-2xl border border-emerald-200">
                    <div>
                        <span class="block text-xs text-emerald-800 font-semibold">Skor Kredit Pefindo</span>
                        <span class="text-3xl font-black text-emerald-950 mt-1 block">{{ $screening->pefindo_score ?? '750' }}</span>
                        <span class="text-xs text-emerald-700 font-medium mt-0.5 block">Kategori: <strong>{{ $screening->pefindo_grade ?? 'Grade 1 - Sangat Baik' }}</strong></span>
                        @if($screening->bank)
                            <div class="mt-2 text-xs text-slate-700">
                                Rekomendasi Bank: <strong class="text-slate-900">{{ $screening->bank === 'other' ? $screening->bank_other : strtoupper($screening->bank) }}</strong>
                            </div>
                        @endif
                    </div>

                    <div>
                        @php
                            $isOutcomeCancelledByUser = ($screening->participant_decision === 'not_agree' || $screening->rejection_reason === 'User membatalkan pengajuan KPR');
                        @endphp
                        <span class="block text-xs text-emerald-800 font-semibold">Status Kelayakan</span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold mt-2
                            @if($isOutcomeCancelledByUser) bg-slate-200 text-slate-800
                            @elseif($screening->status === 'approved') bg-emerald-200 text-emerald-900
                            @elseif($screening->status === 'layak_bersyarat') bg-amber-200 text-amber-900
                            @else bg-rose-200 text-rose-900 @endif
                        ">
                            @if($isOutcomeCancelledByUser)
                                <svg class="w-3.5 h-3.5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                <span>DIBATALKAN OLEH PEMOHON</span>
                            @elseif($screening->status === 'approved')
                                <svg class="w-3.5 h-3.5 text-emerald-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span>LAYAK PROSES KPR (HIJAU)</span>
                            @elseif($screening->status === 'layak_bersyarat')
                                <svg class="w-3.5 h-3.5 text-amber-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                                <span>LAYAK BERSYARAT (KUNING)</span>
                            @else
                                <svg class="w-3.5 h-3.5 text-rose-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                <span>TIDAK LAYAK KPR (MERAH)</span>
                            @endif
                        </span>
                        @if($screening->pefindo_file)
                            <a href="{{ asset('storage/' . $screening->pefindo_file) }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-blue-600 font-bold hover:underline mt-2.5 block">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 9.75m-6 0h12m-12 0a2.25 2.25 0 01-2.25-2.25V6.75A2.25 2.25 0 014.5 4.5h8.25m6 6.75v6a2.25 2.25 0 01-2.25 2.25h-3.75"/></svg>
                                <span>Unduh Lembar Skor Pefindo</span>
                            </a>
                        @endif
                    </div>
                </div>

                @if($screening->pefindo_notes)
                    <div class="mt-4 p-3 bg-gray-50 rounded-xl border border-gray-200 text-xs text-gray-700">
                        <strong>Catatan Analis:</strong> {{ $screening->pefindo_notes }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @php
        $activeUnitSelection = $screening->unitSelection ?? $screening->nupRegistration?->unitSelections()->latest()->first();
        $hasConfirmedDecision = $screening->hasParticipantDecision();
    @endphp

    @if($hasConfirmedDecision)
        <!-- Keputusan Sudah Tercatat -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sm:p-8 mb-6">
            <div class="flex items-start gap-3.5 mb-4">
                <div class="w-10 h-10 rounded-xl {{ $screening->participant_decision === 'not_agree' ? 'bg-slate-100 text-slate-600 border-slate-200' : 'bg-emerald-50 text-emerald-600 border-emerald-100' }} flex items-center justify-center shrink-0 border">
                    @if($screening->participant_decision === 'not_agree')
                        <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    @else
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    @endif
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900">
                        {{ $screening->participant_decision === 'not_agree' ? 'Pengajuan KPR Telah Dibatalkan' : 'Keputusan Anda Telah Dikonfirmasi' }}
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Anda telah menentukan langkah lanjutan untuk pengajuan KPR ini{{ $screening->decision_made_at ? ' pada ' . $screening->decision_made_at->format('d M Y, H:i') : '' }}.
                    </p>
                </div>
            </div>

            <div class="p-4 {{ $screening->participant_decision === 'not_agree' ? 'bg-rose-50/50 border-rose-200' : 'bg-slate-50 border-slate-200' }} border rounded-xl mb-6">
                <span class="text-[11px] font-bold {{ $screening->participant_decision === 'not_agree' ? 'text-rose-700' : 'text-slate-500' }} uppercase tracking-wider block">Pilihan yang Telah Anda Ambil:</span>
                <span class="text-base font-extrabold {{ $screening->participant_decision === 'not_agree' ? 'text-rose-950' : 'text-slate-800' }} mt-1 block">
                    {{ $screening->participant_decision === 'not_agree' ? 'Batalkan Pengajuan KPR (Tiket NUP Hangus)' : config('nup.participant_decisions.' . $screening->participant_decision, ucwords(str_replace('_', ' ', $screening->participant_decision ?: 'Lanjut KPR & Pilih Unit'))) }}
                </span>
                @if($screening->participant_decision === 'not_agree')
                    <p class="text-xs text-rose-800 mt-2 leading-relaxed">
                        Pengajuan KPR dibatalkan secara mandiri oleh pemohon. Tiket antrean NUP terkait telah dinyatakan hangus. Silakan daftar NUP baru jika ingin memesan unit kembali.
                    </p>
                @elseif($activeUnitSelection)
                    <p class="text-xs text-slate-600 mt-2">
                        Unit Terpilih: <strong class="text-slate-900">{{ $activeUnitSelection->housingUnit?->full_identifier }}</strong> (Status: <span class="uppercase font-semibold text-emerald-700">{{ $activeUnitSelection->status }}</span>)
                    </p>
                @endif
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                @if($screening->participant_decision === 'not_agree')
                    <a href="{{ route('nup.register') }}" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-sm inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Daftar NUP Baru &rarr;</span>
                    </a>
                @elseif($activeUnitSelection)
                    @php
                        $actSpr = $activeUnitSelection->sprDocuments()->whereNotIn('status', ['cancelled'])->first();
                    @endphp
                    @if($actSpr)
                        <a href="{{ route('spr.show', $actSpr->id) }}" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-sm inline-flex items-center gap-1.5">
                            <span>Tinjau Dokumen SPR &rarr;</span>
                        </a>
                    @else
                        <a href="{{ route('units.show', $activeUnitSelection->id) }}" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-sm inline-flex items-center gap-1.5">
                            <span>Lihat Detail Unit & Pembayaran UTK &rarr;</span>
                        </a>
                    @endif
                @elseif($screening->nupRegistration)
                    <a href="{{ route('units.create', $screening->nupRegistration->id) }}" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-sm inline-flex items-center gap-1.5">
                        <span>Lanjut ke Pemilihan Unit Rumah &rarr;</span>
                    </a>
                @endif
                <a href="{{ route('dashboard') }}" class="px-4 py-2.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-xl text-xs font-semibold transition">
                    Kembali ke Dashboard
                </a>
            </div>
        </div>
    @else
        <!-- Opsi Langkah Selanjutnya -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sm:p-8 mb-6">
            <h2 class="text-base font-bold text-gray-900 mb-2">Pilih Langkah Lanjutan Anda:</h2>
            <p class="text-xs text-gray-500 mb-6 pb-2 border-b border-gray-100">
                @if($screening->status === 'rejected')
                    Pengajuan KPR Anda dinyatakan belum memenuhi kriteria bank rekanan. Anda dapat beralih ke skema <strong>In-House Developer (Cicilan Pengembang)</strong> atau <strong>Tunai</strong> di bawah ini agar pemesanan unit kavling Anda dapat dilanjutkan.
                @else
                    Silakan tentukan skema yang paling sesuai dengan rencana Anda. Anda dapat melanjutkan pengajuan KPR Bank, atau beralih ke skema In-House / Tunai.
                @endif
            </p>

            <form action="{{ route('kpr.handle-outcome', $screening->id) }}" method="POST" id="outcomeForm" onsubmit="return handleOutcomeSubmit(event)">
                @csrf

                <div class="grid grid-cols-1 gap-4 mb-6">
                    @if($screening->status !== 'rejected')
                        <!-- Opsi 1: Lanjut KPR (Hanya tampil jika Disetujui/Layak) -->
                        <label class="flex items-start gap-4 p-5 rounded-2xl border-2 cursor-pointer hover:bg-blue-50/40 transition border-blue-500 bg-blue-50/20 has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50">
                            <input type="radio" name="outcome" value="select_unit" checked class="mt-1 text-blue-600 focus:ring-blue-500 w-5 h-5">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.333A12.004 12.004 0 0012 9c-2.485 0-4.77.75-6.5 2.033V21m15 0H3"/></svg>
                                    <span class="font-bold text-sm text-gray-950">Lanjut KPR & Pilih Unit Rumah</span>
                                    <span class="text-[11px] px-2 py-0.5 bg-blue-600 text-white font-bold rounded-full">Rekomendasi</span>
                                </div>
                                <p class="text-xs text-gray-600 mt-1.5 leading-relaxed">
                                    Lanjutkan proses dengan memilih unit perumahan yang tersedia, membayar Uang Tanda Komitmen (UTK), menandatangani Surat Pemesanan Rumah (SPR), dan sistem akan menjalankan Argo 7 Hari untuk pengurusan SP3K ke bank rekanan.
                                </p>
                            </div>
                        </label>
                    @endif

                    <!-- Opsi 2: Beralih ke In-House -->
                    <label class="flex items-start gap-4 p-5 rounded-2xl border-2 cursor-pointer hover:bg-indigo-50/40 transition border-gray-200 has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/30">
                        <input type="radio" name="outcome" value="switch_to_inhouse" {{ $screening->status === 'rejected' ? 'checked' : '' }} class="mt-1 text-indigo-600 focus:ring-indigo-500 w-5 h-5">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                                <span class="font-bold text-sm text-gray-950">Alih ke In-House Developer (Cicilan Pengembang)</span>
                                @if($screening->status === 'rejected')
                                    <span class="text-[11px] px-2 py-0.5 bg-indigo-600 text-white font-bold rounded-full">Rekomendasi</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-600 mt-1.5 leading-relaxed">
                                Tidak ingin melalui BI Checking bank rekanan? Anda bisa mencicil langsung ke developer secara syariah/flat bulanan sesuai tenor yang tersedia.
                            </p>
                        </div>
                    </label>

                    <!-- Opsi 3: Beralih ke Tunai -->
                    <label class="flex items-start gap-4 p-5 rounded-2xl border-2 cursor-pointer hover:bg-emerald-50/40 transition border-gray-200 has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50/30">
                        <input type="radio" name="outcome" value="switch_to_cash" class="mt-1 text-emerald-600 focus:ring-emerald-500 w-5 h-5">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6H2.25m0 0v1.5c0 .414.336.75.75.75H3m12-2.25H9.75M12 9.75h.008v.008H12v-.008zm0 3h.008v.008H12v-.008zm-6 3h.008v.008H6v-.008zM18 9.75h.008v.008H18v-.008zm0 3h.008v.008H18v-.008zm0 3h.008v.008H18v-.008z"/></svg>
                                <span class="font-bold text-sm text-gray-950">Alih ke Pembayaran Tunai (Lunas / Bertahap)</span>
                            </div>
                            <p class="text-xs text-gray-600 mt-1.5 leading-relaxed">
                                Bayar lunas sekaligus atau bertahap sesuai batas jatuh tempo termin proyek (Bebas UTK).
                            </p>
                        </div>
                    </label>

                    <!-- Opsi 4: Batalkan Pengajuan -->
                    <label class="flex items-start gap-4 p-5 rounded-2xl border-2 cursor-pointer hover:bg-rose-50/40 transition border-gray-200 has-[:checked]:border-rose-600 has-[:checked]:bg-rose-50/30">
                        <input type="radio" name="outcome" value="not_agree" class="mt-1 text-rose-600 focus:ring-rose-500 w-5 h-5">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span class="font-bold text-sm text-gray-950">Batalkan Pengajuan KPR</span>
                                <span class="text-[10px] px-2 py-0.5 bg-rose-100 text-rose-700 font-bold rounded-full">Tiket NUP Hangus</span>
                            </div>
                            <p class="text-xs text-rose-700 mt-1.5 leading-relaxed">
                                Pengajuan KPR dibatalkan dan nomor antrean NUP Anda otomatis dinyatakan hangus/selesai.
                            </p>
                        </div>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="submit" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold shadow-lg transition inline-flex items-center gap-2">
                        <span>Lanjutkan Pilihan Saya</span>
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>

<!-- Modal Peringatan Konfirmasi Pembatalan -->
<div id="cancelWarningModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-rose-100 transform transition-all animate-in fade-in zoom-in-95">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 border border-rose-200">
                <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-base font-black text-rose-950">Peringatan: Konfirmasi Pembatalan KPR</h3>
                <p class="text-xs text-rose-700 mt-0.5">Apakah Anda benar-benar yakin ingin membatalkan pengajuan KPR?</p>
            </div>
        </div>

        <div class="mt-4 p-4 bg-rose-50/70 rounded-2xl border border-rose-200 text-xs text-slate-800 space-y-2.5 leading-relaxed">
            <p class="font-bold text-rose-950">Harap perhatikan konsekuensi berikut:</p>
            <ul class="space-y-1.5 list-disc list-inside text-rose-900">
                <li>Tiket antrean <strong>{{ $screening->nupRegistration?->queue_number ?: 'NUP' }}</strong> akan otomatis dinyatakan <strong>HANGUS</strong> dan tidak dapat digunakan kembali.</li>
                <li>Biaya pendaftaran NUP yang sudah dibayarkan tidak dapat dikembalikan (<em>non-refundable</em>).</li>
                <li>Jika di kemudian hari Anda ingin memesan rumah kembali, Anda diwajibkan mendaftar tiket NUP baru dari awal.</li>
            </ul>
            <div class="mt-3 pt-3 border-t border-rose-200 text-[11px] text-slate-600">
                <strong class="text-indigo-800">Saran Pengembang:</strong> Jika Anda masih berminat memiliki rumah tanpa repot melalui BI Checking Bank, Anda disarankan memilih opsi <strong>"Alih ke In-House Developer"</strong> atau <strong>"Alih ke Tunai"</strong> agar tiket NUP Anda tetap dapat digunakan.
            </div>
        </div>

        <div class="mt-6 flex flex-col-reverse sm:flex-row items-center justify-end gap-2.5">
            <button type="button" onclick="closeCancelModal()" class="w-full sm:w-auto px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                Kembali / Pikir-pikir Dulu
            </button>
            <button type="button" onclick="confirmCancelAndSubmit()" class="w-full sm:w-auto px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-md transition inline-flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                <span>Ya, Saya Yakin Batalkan KPR & NUP</span>
            </button>
        </div>
    </div>
</div>

<script>
function handleOutcomeSubmit(e) {
    const selected = document.querySelector('input[name="outcome"]:checked');
    if (selected && selected.value === 'not_agree') {
        e.preventDefault();
        openCancelModal();
        return false;
    }
    return true;
}

function openCancelModal() {
    const modal = document.getElementById('cancelWarningModal');
    if (modal) {
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }
}

function closeCancelModal() {
    const modal = document.getElementById('cancelWarningModal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
}

function confirmCancelAndSubmit() {
    const form = document.getElementById('outcomeForm');
    if (form) {
        form.onsubmit = null;
        form.submit();
    }
}
</script>
</div>
@endsection
