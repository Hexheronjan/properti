@extends('layouts.app')

@section('title', 'Pembayaran Rumah')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-1">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Pembayaran Rumah</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Kelola riwayat dan kewajiban pembayaran unit rumah Anda</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200/80 rounded-xl p-4 flex items-center gap-2.5">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <p class="text-xs font-semibold text-emerald-800">{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-rose-50 border border-rose-200/80 rounded-xl p-4 flex items-center gap-2.5">
            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
            <p class="text-xs font-semibold text-rose-800">{{ session('error') }}</p>
        </div>
    @endif

    @if($sprs->isEmpty())
        <div class="bg-white border border-slate-200/80 rounded-2xl p-8 text-center max-w-xl mx-auto shadow-sm">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-3 border border-blue-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 003.75 19.5z" />
                </svg>
            </div>
            <h3 class="font-bold text-slate-900 text-base mb-1">Belum Ada Tagihan Pembayaran</h3>
            <p class="text-xs text-slate-500 mb-6 leading-relaxed">Anda belum memiliki dokumen SPR aktif yang membutuhkan pembayaran cicilan atau pelunasan.</p>

            <div class="flex items-center justify-center gap-3">
                <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition text-xs font-semibold shadow-xs">
                    Dashboard Saya
                </a>
                <a href="{{ route('units.index') }}" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-xl hover:bg-slate-50 transition text-xs font-semibold">
                    Katalog Unit
                </a>
            </div>
        </div>
    @else
        @foreach($sprs as $spr)
        @php
            $totalHarga     = $spr->unit_price;
            $totalDibayar   = $spr->getTotalSudahDibayar();
            $sisa           = $spr->getSisaPembayaran();
            $persentase     = $spr->getTotalPaymentProgress();
            $isFullyPaid    = $spr->isFullyPaid();
            $status         = $spr->getPaymentStatus();
            $unitLabel      = $spr->unitSelection?->housingUnit?->full_identifier ?? '-';
            $schemeLabel    = match($spr->payment_scheme) {
                'kpr'               => 'KPR Bank (Bebas DP)',
                'mitra_pintar'      => 'Kredit Mitra Pintar (Bebas DP)',
                'tunai_mitra_pintar'=> 'Tunai / Mitra Pintar',
                'cash'              => 'Tunai Keras',
                'tunai_bertahap'    => 'Tunai Bertahap',
                'installment', 'inhouse' => 'In-House Developer',
                default             => ucfirst($spr->payment_scheme),
            };
        @endphp

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden mb-6">
            {{-- Clean Header --}}
            <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white">
                <div>
                    <span class="text-xs text-slate-400 font-mono">No. SPR: {{ $spr->spr_number }}</span>
                    <h3 class="font-bold text-base text-slate-900 mt-0.5">{{ $unitLabel }}</h3>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $schemeLabel }} &middot; Terbit: {{ $spr->spr_date->format('d M Y') }}</p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold self-start sm:self-auto
                    {{ $isFullyPaid ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/80' : 'bg-amber-50 text-amber-700 border border-amber-200/80' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $isFullyPaid ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                    {{ $isFullyPaid ? 'Lunas 100%' : 'Belum Lunas' }}
                </span>
            </div>

            <div class="p-6">
                {{-- Kartu statistik finansial --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5">
                    <div class="bg-slate-50 border border-slate-200/70 rounded-xl p-3.5">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Harga Rumah</p>
                        <p class="text-base font-bold text-slate-900 mt-1">Rp {{ number_format($totalHarga, 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-3.5">
                        <p class="text-[10px] font-bold text-blue-600 uppercase tracking-wider">Total Terbayar</p>
                        <p class="text-base font-bold text-blue-950 mt-1">Rp {{ number_format($totalDibayar, 0, ',', '.') }}</p>
                        <p class="text-[11px] text-blue-600 mt-0.5">DP: Rp {{ number_format($spr->down_payment, 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-slate-50 border border-slate-200/70 rounded-xl p-3.5">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Sisa Kewajiban</p>
                        <p class="text-base font-bold text-slate-900 mt-1">Rp {{ number_format($sisa, 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-slate-50 border border-slate-200/70 rounded-xl p-3.5">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Progres Pelunasan</p>
                        <p class="text-base font-bold text-slate-900 mt-1">{{ number_format($persentase, 1) }}%</p>
                    </div>
                </div>

                {{-- Progress Bar --}}
                <div class="mb-5">
                    <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                        <div class="h-2 rounded-full transition-all duration-500 {{ $persentase >= 100 ? 'bg-emerald-500' : 'bg-blue-600' }}"
                             style="width: {{ min($persentase, 100) }}%"></div>
                    </div>
                </div>

                {{-- Riwayat Pembayaran --}}
                <div class="mb-5">
                    <h4 class="font-bold text-slate-700 mb-3 text-xs uppercase tracking-wider">Riwayat Pembayaran</h4>

                    {{-- Down Payment entry (Hanya muncul jika belum ada transaksi pengajuan DP di tabel pembayaran) --}}
                    @php
                        $isDpPaid = $spr->isDpPaid();
                        $hasPendingDp = $spr->hasPendingDpPayment();
                        $hasDpPaymentRecord = $spr->kprPayments->whereIn('payment_type', ['dp', 'down_payment'])->isNotEmpty();
                    @endphp
                    @if(!$hasDpPaymentRecord && $spr->down_payment > 0)
                    <div class="flex items-center justify-between bg-slate-50/70 border border-slate-200/80 rounded-xl px-4 py-3 mb-2">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg {{ $isDpPaid ? 'bg-emerald-50 text-emerald-600 border border-emerald-200/60' : 'bg-rose-50 text-rose-600 border border-rose-200/60' }} flex items-center justify-center shrink-0">
                                @if($isDpPaid)
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                @else
                                    <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                @endif
                            </div>
                            <div>
                                <p class="font-bold text-slate-800 text-xs">Kewajiban Uang Muka (DP)</p>
                                <p class="text-[11px] text-slate-400">{{ $spr->due_date ? 'Jatuh Tempo: ' . \Carbon\Carbon::parse($spr->due_date)->format('d M Y') : $spr->spr_date->format('d M Y') }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-slate-900 text-xs">Rp {{ number_format($spr->down_payment, 0, ',', '.') }}</p>
                            <span class="text-[10px] px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 border border-rose-200/60 font-semibold">Belum Dibayar</span>
                        </div>
                    </div>
                    @endif

                    @if($spr->kprPayments->isNotEmpty())
                        <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                            @foreach($spr->kprPayments as $payment)
                            @php
                                $badgeColor = match($payment->status) {
                                    'success' => 'bg-emerald-50 text-emerald-700 border border-emerald-200/80',
                                    'pending' => 'bg-amber-50 text-amber-700 border border-amber-200/80',
                                    'failed'  => 'bg-rose-50 text-rose-700 border border-rose-200/80',
                                    default   => 'bg-slate-100 text-slate-700 border border-slate-200',
                                };
                                $badgeLabel = match($payment->status) {
                                    'success' => 'Lunas',
                                    'pending' => 'Verifikasi',
                                    'failed'  => 'Ditolak',
                                    default   => ucfirst($payment->status),
                                };
                            @endphp
                            <a href="{{ auth()->user()->isAdmin() ? route('admin.kpr-payments.show', $payment->id) : route('kpr-payment.show', $payment->id) }}"
                               class="flex items-center justify-between bg-slate-50/70 border border-slate-200/80 rounded-xl px-4 py-3 hover:bg-slate-100/70 transition group">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 border border-blue-200/60 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800 text-xs">
                                            {{ in_array($payment->payment_type, ['dp', 'down_payment'], true) ? 'Uang Muka (DP) Rumah' : ($payment->payment_type === 'installment' ? 'Cicilan Termin ke-' . $payment->installment_number : 'Pelunasan Unit') }}
                                            <span class="text-slate-400 font-normal">#{{ $payment->payment_number }}</span>
                                        </p>
                                        <p class="text-[11px] text-slate-400">
                                            {{ $payment->payment_date ? $payment->payment_date->format('d M Y H:i') : $payment->created_at->format('d M Y H:i') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="text-right">
                                        <p class="font-bold text-slate-900 text-xs">Rp {{ number_format($payment->amount, 0, ',', '.') }}</p>
                                        <span class="text-[10px] px-2 py-0.5 rounded-md font-semibold {{ $badgeColor }}">{{ $badgeLabel }}</span>
                                    </div>
                                    <svg class="w-4 h-4 text-slate-300 group-hover:text-blue-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5 bg-slate-50 border border-slate-200/60 rounded-xl text-slate-500">
                            @if($spr->payment_scheme === 'mitra_pintar')
                                <p class="text-xs font-semibold text-amber-800">Menunggu pencairan dana pelunasan dari Lembaga Mitra Pintar ke rekening pengembang.</p>
                                <p class="text-[11px] text-slate-500 mt-1">Pembayaran angsuran rumah Anda dikelola langsung melalui kerja sama instansi mitra (Bebas DP).</p>
                            @elseif($spr->payment_scheme === 'kpr')
                                <p class="text-xs font-semibold text-blue-800">Menunggu proses akad kredit dan pencairan dana dari Bank Rekanan.</p>
                            @else
                                <p class="text-xs font-medium">Belum ada riwayat pembayaran termin / pelunasan</p>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Tombol Aksi --}}
                @if(!$isFullyPaid)
                <div class="flex flex-wrap gap-2.5 pt-2 border-t border-slate-100">
                    @if($spr->payment_scheme === 'mitra_pintar')
                        <span class="inline-flex items-center gap-2 px-4 py-2 bg-amber-50 text-amber-800 border border-amber-300 rounded-xl font-bold text-xs shadow-2xs">
                            <svg class="w-4 h-4 text-amber-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Menunggu Pencairan Pelunasan dari Lembaga Mitra Pintar
                        </span>
                    @elseif($spr->payment_scheme === 'kpr')
                        <span class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-800 border border-blue-200 rounded-xl font-bold text-xs shadow-2xs">
                            <svg class="w-4 h-4 text-blue-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Pengurusan KPR Bank Rekanan Berjalan (Bebas DP)
                        </span>
                    @elseif(!$isDpPaid && $spr->down_payment > 0)
                        @if($hasPendingDp)
                            <span class="inline-flex items-center gap-2 px-4 py-2 bg-amber-50 text-amber-800 border border-amber-300 rounded-xl font-bold text-xs shadow-2xs">
                                <svg class="w-4 h-4 animate-spin text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                Bukti DP Sedang Diverifikasi Pengembang
                            </span>
                        @else
                            <a href="{{ route('kpr-payment.create', $spr->id) }}"
                               class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 transition font-semibold text-xs shadow-xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                Bayar Uang Muka (DP)
                            </a>
                        @endif
                    @else
                        <a href="{{ route('kpr-payment.create', $spr->id) }}"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-semibold text-xs shadow-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 003.75 19.5z"/></svg>
                            Bayar Cicilan / Lunasi
                        </a>
                    @endif
                    @if($isDpPaid || $spr->down_payment <= 0)
                        <a href="{{ route('spr.show', $spr->id) }}"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-xl hover:bg-slate-50 transition font-semibold text-xs shadow-2xs">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                            Lihat SPR
                        </a>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-50 border border-slate-200 text-slate-400 rounded-xl font-medium text-xs">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                            SPR Terbit Setelah DP Lunas
                        </span>
                    @endif
                </div>
                @else
                <div class="bg-emerald-50 border border-emerald-200/80 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <p class="text-emerald-700 font-semibold text-xs"><svg class="w-4 h-4 inline-block text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Seluruh kewajiban pembayaran rumah ini telah lunas 100%.</p>
                    <a href="{{ route('spr.show', $spr->id) }}"
                       class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 transition font-semibold text-xs shadow-xs shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                        <span>{{ in_array($spr->payment_scheme, ['cash', 'tunai_bertahap', 'inhouse', 'installment']) ? 'Lihat SPR' : 'Lihat SPR & Jadwal Serah Terima (BAST)' }} &rarr;</span>
                    </a>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    @endif
</div>
@endsection
