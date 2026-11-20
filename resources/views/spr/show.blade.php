@extends(auth()->check() && auth()->user()->isAdmin() ? 'layouts.admin' : 'layouts.app')

@section('title', 'Detail Surat Pemesanan Rumah (SPR)')

@section('content')
<div class="max-w-5xl mx-auto py-6 px-4 sm:px-6">
    <div class="mb-6 flex items-center justify-between">
        @if(auth()->check() && auth()->user()->isAdmin())
            <a href="{{ route('admin.spr.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-blue-600 hover:text-blue-800 transition">
                ← Kembali ke Manajemen SPR
            </a>
        @else
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-blue-600 hover:text-blue-800 transition">
                ← Kembali ke Dashboard
            </a>
        @endif
        <span class="text-xs text-gray-500 font-mono">No. SPR: {{ $spr->spr_number }}</span>
    </div>

    <!-- Alert Notifikasi -->
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl text-sm mb-6 flex items-center gap-2.5">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl text-sm mb-6 flex items-center gap-2.5">
            <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif
    @if(session('info'))
        <div class="bg-blue-50 border border-blue-200 text-blue-800 p-4 rounded-xl text-sm mb-6 flex items-center gap-2.5">
            <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('info') }}</span>
        </div>
    @endif

    @php
        $unit = $spr->unitSelection?->housingUnit;
        $project = $unit?->housingProject;
        $company = $project?->company;
        $broker = $spr->getBroker();
        $brokerName = $broker?->name ?? ($spr->nupRegistration?->agent_name ?? 'Tim Marketing Penjualan');
        $brokerSignature = $broker?->signature_image;
        $utkAmount = (float) ($spr->unitSelection?->utk_amount ?? 0);
        $isCash = $spr->unitSelection?->isCash() || $spr->unitSelection?->isUtkWaived() || $spr->payment_scheme === 'cash';
        $schemeLabel = $spr->payment_scheme_label;
        $city = $project?->city ?? 'Indonesia';
        $issueDate = $spr->issued_date ?? ($spr->spr_date ? \Carbon\Carbon::parse($spr->spr_date) : now());
        $isPaid = $spr->status === 'paid' || $spr->isFullyPaid();
        $paidDate = $spr->paid_date ?? ($spr->issued_date ?? ($spr->spr_date ? \Carbon\Carbon::parse($spr->spr_date) : now()));
        $totalSudahDibayar = $spr->getTotalSudahDibayar();
        $sisaPembayaran = $spr->getSisaPembayaran();
        $activeBankAccounts = $company ? $company->activeBankAccounts()->get() : collect();
        $overdue = $spr->getOverduePenaltyDetails();
        $latestPayment = $spr->kprPayments()->latest()->first();
        $pendingPayment = $spr->kprPayments()->where('status', 'pending')->latest()->first();
        $isLatestFailed = $latestPayment && $latestPayment->status === 'failed';
        $isDpPaid = ($spr->down_payment <= 0) || ($totalSudahDibayar >= $spr->down_payment && $spr->status !== 'draft');
    @endphp

    {{-- Banner Status Pembayaran & Verifikasi Admin --}}
    @if($spr->payment_scheme === 'kpr')
        @if($spr->is_akad_completed || $spr->status === 'paid')
            <div class="bg-gradient-to-r from-emerald-800 via-emerald-900 to-teal-950 rounded-2xl p-6 sm:p-7 text-white shadow-lg mb-8 border border-emerald-600/50">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-5">
                    <div class="space-y-2">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/25 text-emerald-200 text-xs font-bold border border-emerald-400/40">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                            Proses KPR Selesai &middot; Akad Kredit Terlaksana (Lunas oleh Bank)
                        </div>
                        <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight">
                            Selamat! Pembiayaan Unit Rumah Selesai
                        </h2>
                        <p class="text-sm text-emerald-100 max-w-2xl leading-relaxed">
                            Proses akad kredit dan penandatanganan notaris telah selesai dilaksanakan. Pelunasan unit kavling telah dicairkan oleh pihak Bank rekanan kepada Developer, sehingga kewajiban Anda pada tahap pembelian rumah ini telah tuntas.
                        </p>
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 shrink-0">
                        <a href="{{ route('spr.pdf', $spr->id) }}"
                           class="inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-white/15 hover:bg-white/25 active:scale-95 text-white border border-white/30 rounded-xl text-xs sm:text-sm font-bold shadow-md transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Unduh PDF SPR</span>
                        </a>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-gradient-to-r from-blue-900 to-indigo-900 rounded-2xl p-6 sm:p-7 text-white shadow-lg mb-8 border border-blue-700">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-5">
                    <div class="space-y-2">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-800/80 text-blue-200 text-xs font-bold border border-blue-600">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            Skema KPR Bank Rekanan &middot; Bebas DP (Uang Tanda Jadi UTK Lunas)
                        </div>
                        <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight">
                            Dokumen SPR Resmi Terbit & Pengurusan Bank Berjalan
                        </h2>
                        <p class="text-sm text-blue-100 max-w-2xl leading-relaxed">
                            Surat Pemesanan Rumah (SPR) telah resmi diterbitkan untuk pengajuan berkas ke Bank Rekanan. Pemesanan unit rumah telah dikunci dengan Uang Tanda Komitmen (UTK) lunas tanpa Down Payment (Bebas DP). Plafon KPR diajukan 100% sebesar <strong class="text-white font-extrabold bg-blue-800 px-2 py-0.5 rounded border border-blue-600">Rp {{ number_format($spr->unit_price, 0, ',', '.') }}</strong>.
                            @if($spr->argo_deadline)
                                &middot; Sisa Argo Waktu Pengurusan: <strong class="text-amber-300 font-bold">{{ $spr->getArgoRemainingText() }}</strong>.
                            @endif
                        </p>
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 shrink-0">
                        <a href="{{ route('spr.pdf', $spr->id) }}"
                           class="inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white rounded-xl text-xs sm:text-sm font-bold shadow-md hover:shadow-lg transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Unduh PDF SPR</span>
                        </a>
                    </div>
                </div>
            </div>
        @endif
    @elseif($spr->payment_scheme === 'mitra_pintar')
        @if($spr->status === 'paid' || $isPaid)
            {{-- KONDISI MITRA PINTAR LUNAS (100%): TAMPILAN EMERALD LUNAS PREMIUM --}}
            <div class="bg-gradient-to-r from-emerald-800 via-emerald-900 to-teal-950 rounded-2xl p-6 sm:p-7 text-white shadow-lg mb-8 border border-emerald-600/50">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-5">
                    <div class="space-y-2">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/25 text-emerald-200 text-xs font-bold border border-emerald-400/40">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                            Pelunasan Mitra Pintar Diterima &middot; LUNAS (100%)
                        </div>
                        <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight">
                            Selamat! Pelunasan Unit dari Lembaga Mitra Telah Masuk
                        </h2>
                        <p class="text-sm text-emerald-100 max-w-2xl leading-relaxed">
                            Dana pelunasan unit rumah telah dicairkan oleh Lembaga Mitra Pintar ke rekening pengembang. Status pemesanan resmi lunas 100% dan siap dilanjutkan ke Serah Terima Kunci & Penandatanganan BAST.
                        </p>
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 shrink-0">
                        @if(Auth::check() && Auth::user()->isAdmin())
                            @if(!$spr->bast_file)
                                <a href="#bast-panel"
                                   class="inline-flex items-center justify-center gap-2 px-5 py-3.5 bg-amber-400 hover:bg-amber-300 text-slate-950 rounded-xl text-xs sm:text-sm font-black shadow-md transition">
                                    <svg class="w-4 h-4 text-slate-950 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                    <span>Jadwalkan Serah Terima (BAST) &darr;</span>
                                </a>
                            @endif
                        @endif
                        <a href="{{ route('spr.pdf', $spr->id) }}"
                           class="inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-white/15 hover:bg-white/25 active:scale-95 text-white border border-white/30 rounded-xl text-xs sm:text-sm font-bold shadow-md transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Unduh PDF SPR</span>
                        </a>
                    </div>
                </div>
            </div>
        @else
            {{-- KONDISI MITRA PINTAR BELUM PENCAIRAN: SLATE / INDIGO DENGAN AKSEN EMAS --}}
            <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-2xl p-6 sm:p-7 text-white shadow-lg mb-8 border border-indigo-700/60">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-5">
                    <div class="space-y-2">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 text-amber-300 text-xs font-bold border border-amber-400/40">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse"></span>
                            Skema Kredit Mitra Pintar &middot; Bebas DP (Uang Tanda Komitmen UTK Lunas)
                        </div>
                        <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight">
                            Dokumen SPR Resmi Terbit & Menunggu Pencairan Lembaga Mitra
                        </h2>
                        <p class="text-sm text-slate-200 max-w-2xl leading-relaxed">
                            Surat Pemesanan Rumah (SPR) telah resmi diterbitkan. Pemesanan unit rumah telah dikunci dengan Uang Tanda Komitmen (UTK) lunas tanpa Down Payment (Bebas DP). Pelunasan sisa harga unit sebesar <strong class="text-amber-200 font-extrabold bg-white/10 px-2 py-0.5 rounded border border-amber-400/40">Rp {{ number_format($spr->unit_price, 0, ',', '.') }}</strong> akan dicairkan langsung oleh Lembaga Mitra Pintar ke rekening pengembang.
                        </p>
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 shrink-0">
                        @if(Auth::check() && Auth::user()->isAdmin())
                            <a href="#mitra-disbursement-panel"
                               class="inline-flex items-center justify-center gap-2 px-5 py-3.5 bg-amber-400 hover:bg-amber-300 text-slate-950 rounded-xl text-xs sm:text-sm font-black shadow-md transition">
                                <svg class="w-4 h-4 text-slate-950 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                <span>Konfirmasi Pencairan Mitra &darr;</span>
                            </a>
                        @endif
                        <a href="{{ route('spr.pdf', $spr->id) }}"
                           class="inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white rounded-xl text-xs sm:text-sm font-bold shadow-md hover:shadow-lg transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Unduh PDF SPR</span>
                        </a>
                    </div>
                </div>
            </div>
        @endif
    @elseif(!$isPaid)
        @if($pendingPayment)
            {{-- KONDISI 1: Pembeli Sudah Transfer & Sedang Diverifikasi Admin --}}
            <div class="bg-white rounded-2xl p-6 sm:p-7 text-slate-800 shadow-sm mb-8 border-2 border-amber-300">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-5">
                    <div class="space-y-2">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-900 text-xs font-bold border border-amber-300">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                            Status: Bukti Pembayaran Sedang Diverifikasi Admin
                        </div>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                            Bukti Transfer {{ $pendingPayment->payment_type === 'dp' ? 'Uang Muka (DP)' : 'Pembayaran' }} Sedang Ditinjau
                        </h2>
                        <p class="text-sm text-slate-600 max-w-2xl leading-relaxed">
                            Pembayaran sejumlah <strong class="text-emerald-700 font-extrabold bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">Rp {{ number_format($pendingPayment->amount, 0, ',', '.') }}</strong> ({{ $pendingPayment->sender_bank }} a.n. {{ $pendingPayment->sender_name }}) telah berhasil Anda unggah. Tim keuangan pengembang sedang memvalidasi dana yang masuk. Surat Pemesanan Rumah (SPR) resmi berstempel akan otomatis diterbitkan setelah pembayaran disetujui.
                        </p>
                    </div>
                    <div class="flex flex-col items-stretch sm:items-end gap-2.5 shrink-0">
                        <span class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-amber-50 text-amber-900 border border-amber-300 text-xs font-bold shadow-xs">
                            <svg class="w-4 h-4 animate-spin text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Menunggu Persetujuan Admin
                        </span>
                        @if($pendingPayment->proof_of_transfer)
                            <a href="{{ asset('storage/' . $pendingPayment->proof_of_transfer) }}" target="_blank"
                               class="inline-flex items-center justify-center gap-1.5 text-xs text-blue-600 hover:text-blue-800 font-bold hover:underline py-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>Lihat Bukti Transfer yang Diunggah</span>
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Rekening Developer Info --}}
                @if($activeBankAccounts->count() > 0)
                    <div class="mt-6 pt-5 border-t border-slate-200">
                        <span class="text-xs font-extrabold uppercase tracking-wider text-slate-500 block mb-3">Rekening Pengembang Tujuan Transfer:</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                            @foreach($activeBankAccounts as $bacc)
                                <div class="bg-slate-50 hover:bg-slate-100 transition p-4 rounded-xl border border-slate-200 text-xs">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="px-2.5 py-1 bg-blue-100 text-blue-800 rounded-md font-bold text-xs uppercase tracking-wide">{{ $bacc->bank_name }}</span>
                                        <button type="button" onclick="navigator.clipboard.writeText('{{ $bacc->account_number }}'); alert('Nomor rekening disalin: {{ $bacc->account_number }}');"
                                                class="text-xs text-blue-600 hover:text-blue-800 font-bold underline">Salin</button>
                                    </div>
                                    <div class="font-mono text-slate-900 font-black text-base tracking-wider">{{ $bacc->account_number }}</div>
                                    <div class="text-xs text-slate-600 mt-1 truncate">a.n. <strong class="text-slate-800">{{ $bacc->account_holder_name }}</strong></div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

        @elseif($isDpPaid)
            {{-- KONDISI 2: Uang Muka (DP) Telah Berhasil Disetujui Admin -> Banner Hijau Sukses & Jadwal Cicilan --}}
            <div class="bg-white rounded-2xl p-6 sm:p-7 text-slate-800 shadow-sm mb-8 border-2 border-emerald-300">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-5">
                    <div class="space-y-2">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-900 text-xs font-bold border border-emerald-300">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                            Status: Pembayaran Uang Muka (DP) Disetujui Pengembang
                        </div>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                            Uang Muka (DP) Terverifikasi & Dokumen SPR Resmi Diterbitkan
                        </h2>
                        <p class="text-sm text-slate-600 max-w-2xl leading-relaxed">
                            Pembayaran Uang Muka sebesar <strong class="text-emerald-700 font-extrabold bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">Rp {{ number_format($spr->down_payment, 0, ',', '.') }}</strong> telah diverifikasi dan disetujui. Surat Pemesanan Rumah (SPR) fisik di bawah telah resmi terbit. Sisa tagihan pembayaran unit: <strong class="text-slate-900 font-extrabold">Rp {{ number_format($sisaPembayaran, 0, ',', '.') }}</strong>.
                        </p>
                    </div>
                    @if(!Auth::user()->isAdmin())
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 shrink-0">
                        <a href="{{ route('kpr-payment.create', $spr->id) }}"
                           class="inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white rounded-xl text-xs font-bold shadow-md hover:shadow-lg transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            <span>Bayar Cicilan / Pelunasan</span>
                        </a>
                    </div>
                    @endif
                </div>
            </div>

        @elseif($isLatestFailed && !$pendingPayment)
            {{-- KONDISI 3: Pembayaran Terakhir Ditolak Admin -> Muncul Tombol Bayar Ulang --}}
            <div class="bg-white rounded-2xl p-6 sm:p-7 text-slate-800 shadow-sm mb-8 border-2 border-rose-300">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-5">
                    <div class="space-y-2">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-100 text-rose-900 text-xs font-bold border border-rose-300">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-600"></span>
                            Status: Pembayaran Ditolak oleh Pengembang
                        </div>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                            Pembayaran Belum Berhasil Divalidasi
                        </h2>
                        <p class="text-sm text-slate-600 max-w-2xl leading-relaxed">
                            Bukti transfer sebelumnya ditolak oleh admin pengembang. Silakan lakukan pembayaran ulang dan unggah bukti transfer yang valid.
                        </p>
                        <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-900 font-medium">
                            <strong class="font-bold">Catatan Pengembang:</strong> {{ $latestPayment?->notes ?: 'Bukti transfer tidak valid atau dana belum masuk ke rekening pengembang.' }}
                        </div>
                    </div>
                    @if(!Auth::user()->isAdmin())
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 shrink-0">
                        <a href="{{ route('kpr-payment.create', $spr->id) }}"
                           class="inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-rose-600 hover:bg-rose-700 active:scale-95 text-white rounded-xl text-xs font-bold shadow-md hover:shadow-lg transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Bayar Ulang / Upload Bukti Baru</span>
                        </a>
                    </div>
                    @endif
                </div>

                {{-- Rekening Developer Preview --}}
                @if($activeBankAccounts->count() > 0)
                    <div class="mt-6 pt-5 border-t border-slate-200">
                        <span class="text-xs font-extrabold uppercase tracking-wider text-slate-500 block mb-3">Rekening Resmi Pengembang ({{ $company?->company_name ?? 'Developer' }}):</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                            @foreach($activeBankAccounts as $bacc)
                                <div class="bg-slate-50 hover:bg-slate-100 transition p-4 rounded-xl border border-slate-200 text-xs">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="px-2.5 py-1 bg-blue-100 text-blue-800 rounded-md font-bold text-xs uppercase">{{ $bacc->bank_name }}</span>
                                        <button type="button" onclick="navigator.clipboard.writeText('{{ $bacc->account_number }}'); alert('Nomor rekening disalin: {{ $bacc->account_number }}');"
                                                class="text-xs text-blue-600 hover:text-blue-800 font-bold underline">Salin</button>
                                    </div>
                                    <div class="font-mono text-slate-900 font-black text-base tracking-wider">{{ $bacc->account_number }}</div>
                                    <div class="text-xs text-slate-600 mt-1 truncate">a.n. <strong class="text-slate-800">{{ $bacc->account_holder_name }}</strong></div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

        @else
            {{-- KONDISI 3: Belum Bayar Sama Sekali -> Tampilkan Tombol Bayar --}}
            <div class="bg-white rounded-2xl p-6 sm:p-7 text-slate-800 shadow-sm mb-8 border-2 border-slate-200">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-5">
                    <div class="space-y-2">
                        @if($overdue['is_overdue'] && $overdue['penalty_amount'] > 0)
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-100 text-rose-900 text-xs font-bold border border-rose-300">
                                <span class="w-2.5 h-2.5 rounded-full bg-rose-600 animate-pulse"></span>
                                Melewati Jatuh Tempo (Telat {{ $overdue['days_overdue'] }} Hari &middot; Denda Otomatis +Rp {{ number_format($overdue['penalty_amount'], 0, ',', '.') }})
                            </div>
                        @else
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 text-blue-900 text-xs font-bold border border-blue-200">
                                <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                                Status Pembayaran: {{ $spr->getPaymentStatus() }}
                                @if($overdue['due_date'])
                                    &middot; Jatuh Tempo: {{ $overdue['due_date']->translatedFormat('d F Y') }}
                                @endif
                            </div>
                        @endif

                        <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                            @if($spr->down_payment > 0 && $totalSudahDibayar < $spr->down_payment)
                                Pembayaran Uang Muka (DP) Rumah
                            @else
                                Pembayaran Tagihan / Cicilan Rumah
                            @endif
                        </h2>
                        <p class="text-sm text-slate-600 max-w-2xl leading-relaxed">
                            @if($overdue['is_overdue'] && $overdue['penalty_amount'] > 0)
                                Pembayaran melewati tanggal jatuh tempo ({{ $overdue['due_date']?->translatedFormat('d F Y') }}). Dikenakan denda otomatis <strong class="text-rose-700 font-bold">{{ $overdue['penalty_percent'] }}% / bln</strong> (+Rp {{ number_format($overdue['penalty_amount'], 0, ',', '.') }}). Total wajib bayar: <strong class="text-slate-900 font-extrabold bg-rose-50 px-2 py-0.5 rounded border border-rose-200">Rp {{ number_format($overdue['total_amount'], 0, ',', '.') }}</strong>.
                            @elseif($spr->down_payment > 0 && $totalSudahDibayar < $spr->down_payment)
                                Uang Muka (DP) yang wajib dibayar: <strong class="text-emerald-700 font-extrabold bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">Rp {{ number_format($spr->down_payment, 0, ',', '.') }}</strong>. Silakan lakukan transfer ke rekening pengembang di bawah, lalu unggah struk bukti transfer.
                            @else
                                Sisa tagihan yang harus dibayar: <strong class="text-amber-800 font-extrabold bg-amber-50 px-2 py-0.5 rounded border border-amber-200">Rp {{ number_format($sisaPembayaran, 0, ',', '.') }}</strong>. Total sudah terbayar: <strong class="text-emerald-700 font-bold">Rp {{ number_format($totalSudahDibayar, 0, ',', '.') }}</strong>.
                            @endif
                        </p>
                    </div>
                    @if(!Auth::user()->isAdmin())
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 shrink-0">
                        <a href="{{ route('kpr-payment.create', $spr->id) }}"
                           class="inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white rounded-xl text-xs sm:text-sm font-bold shadow-md hover:shadow-lg transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            <span>Bayar Uang Muka / Upload Bukti Transfer</span>
                        </a>
                    </div>
                    @endif
                </div>

                {{-- Rekening Developer Preview --}}
                @if($activeBankAccounts->count() > 0)
                    <div class="mt-6 pt-5 border-t border-slate-200">
                        <span class="text-xs font-extrabold uppercase tracking-wider text-slate-500 block mb-3">Rekening Resmi Pengembang ({{ $company?->company_name ?? 'Developer' }}):</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                            @foreach($activeBankAccounts as $bacc)
                                <div class="bg-slate-50 hover:bg-slate-100 transition p-4 rounded-xl border border-slate-200 text-xs">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="px-2.5 py-1 bg-blue-100 text-blue-800 rounded-md font-bold text-xs uppercase">{{ $bacc->bank_name }}</span>
                                        <button type="button" onclick="navigator.clipboard.writeText('{{ $bacc->account_number }}'); alert('Nomor rekening disalin: {{ $bacc->account_number }}');"
                                                class="text-xs text-blue-600 hover:text-blue-800 font-bold underline">Salin</button>
                                    </div>
                                    <div class="font-mono text-slate-900 font-black text-base tracking-wider">{{ $bacc->account_number }}</div>
                                    <div class="text-xs text-slate-600 mt-1 truncate">a.n. <strong class="text-slate-800">{{ $bacc->account_holder_name }}</strong></div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif
    @else
        {{-- KONDISI 4: CASH / IN-HOUSE TELAH LUNAS 100% --}}
        <div class="bg-gradient-to-r from-emerald-800 via-emerald-900 to-teal-950 rounded-2xl p-6 sm:p-7 text-white shadow-lg mb-8 border border-emerald-600/50">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-5">
                <div class="space-y-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/25 text-emerald-200 text-xs font-bold border border-emerald-400/40">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                        Status: Seluruh Pembayaran LUNAS (100%)
                    </div>
                    <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight">
                        Selamat! Pembayaran Unit Rumah Telah Lunas 100%
                    </h2>
                    <p class="text-sm text-emerald-100 max-w-2xl leading-relaxed">
                        Seluruh kewajiban pembayaran unit rumah telah lunas dan diverifikasi oleh pengembang. Dokumen Surat Pemesanan Rumah (SPR) resmi bertandatangan dan berstempel lunas telah terbit.
                    </p>
                </div>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 shrink-0">
                    <a href="{{ route('spr.pdf', $spr->id) }}"
                       class="inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-white/15 hover:bg-white/25 active:scale-95 text-white border border-white/30 rounded-xl text-xs sm:text-sm font-bold shadow-md transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span>Unduh PDF SPR</span>
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- JIKA STATUS SPR MASIH DRAFT DAN USER ADALAH ADMIN: TAMPILKAN PANEL INFO ADMIN --}}
    @if($spr->status === 'draft' && Auth::check() && Auth::user()->isAdmin())
        <div class="bg-amber-50 border-2 border-amber-300 rounded-2xl p-5 mb-8 text-slate-800 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 font-bold border border-amber-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </span>
                <div>
                    <h3 class="text-sm font-black text-slate-900">SPR Masih Berstatus Draft (Menunggu Validasi Pembayaran / Persetujuan Admin)</h3>
                    <p class="text-xs text-slate-600 mt-0.5">Sebagai Admin/Operator, Anda melihat pratinjau lembar SPR di bawah. Untuk pembeli (user), dokumen fisik SPR otomatis disembunyikan sampai disetujui.</p>
                </div>
            </div>
            <form action="{{ route('spr.issue', $spr->id) }}" method="POST" class="shrink-0">
                @csrf
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow transition">
                    Terbitkan Dokumen SPR
                </button>
            </form>
        </div>
    @endif

    {{-- JIKA STATUS SPR MASIH DRAFT DAN USER BUKAN ADMIN: TAMPILKAN RINCIAN PENGAJUAN (BUKAN SURAT FISIK) --}}
    @if($spr->status === 'draft' && !(Auth::check() && Auth::user()->isAdmin()))
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8 mb-8 text-slate-800">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-5 border-b border-slate-200">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-50 text-amber-800 border border-amber-300 text-xs font-bold mb-2">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        Status Pengajuan: Menunggu Persetujuan Admin
                    </div>
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900">
                        Rincian Pengajuan Pemesanan Unit
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        Dokumen fisik resmi Surat Pemesanan Rumah (SPR) akan otomatis tampil dan dapat diunduh di sini setelah bukti pembayaran DP diverifikasi dan disetujui admin.
                    </p>
                </div>
                <div class="sm:text-right shrink-0">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block">Nomor Pengajuan</span>
                    <span class="text-base sm:text-lg font-mono font-black text-slate-900 block">No. {{ $spr->spr_number }}</span>
                    <span class="text-xs text-slate-500 block mt-0.5">Tanggal: {{ $issueDate->translatedFormat('d F Y') }}</span>
                </div>
            </div>

            <!-- Detail Pemesan & Unit Kavling -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 my-6 text-xs">
                <!-- Data Pemesan -->
                <div class="bg-slate-50 rounded-xl p-5 border border-slate-200">
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-500 mb-3 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Identitas Pemesan
                    </h3>
                    <table class="w-full">
                        <tr class="border-b border-slate-200">
                            <td class="py-2 text-slate-500 w-1/3">Nama Lengkap</td>
                            <td class="py-2 font-bold text-slate-900">{{ $spr->buyer_name }}</td>
                        </tr>
                        <tr class="border-b border-slate-200">
                            <td class="py-2 text-slate-500">NIK (KTP)</td>
                            <td class="py-2 font-mono font-bold text-slate-900">{{ $spr->buyer_nik }}</td>
                        </tr>
                        <tr class="border-b border-slate-200">
                            <td class="py-2 text-slate-500">No. WhatsApp</td>
                            <td class="py-2 font-bold text-slate-900">{{ $spr->buyer_phone }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 text-slate-500">Email</td>
                            <td class="py-2 font-bold text-slate-900">{{ $spr->user->email ?? '-' }}</td>
                        </tr>
                    </table>
                </div>

                <!-- Data Unit Rumah -->
                <div class="bg-slate-50 rounded-xl p-5 border border-slate-200">
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-500 mb-3 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Unit Kavling yang Dipesan
                    </h3>
                    <table class="w-full">
                        <tr class="border-b border-slate-200">
                            <td class="py-2 text-slate-500 w-1/3">Kawasan Proyek</td>
                            <td class="py-2 font-bold text-slate-900">{{ $project?->name ?? 'Kawasan Perumahan' }} ({{ $company?->company_name ?? 'Developer' }})</td>
                        </tr>
                        <tr class="border-b border-slate-200">
                            <td class="py-2 text-slate-500">Blok / Nomor Unit</td>
                            <td class="py-2 font-bold text-slate-900">Blok {{ $unit?->block ?? '-' }} No. {{ $unit?->unit_number ?? '-' }}</td>
                        </tr>
                        <tr class="border-b border-slate-200">
                            <td class="py-2 text-slate-500">Tipe Rumah</td>
                            <td class="py-2 font-bold text-slate-900">{{ $unit?->houseType?->type_name ?? '-' }} (LB: {{ $unit?->houseType?->building_area ?? '-' }} m² / LT: {{ $unit?->houseType?->surface_area ?? '-' }} m²)</td>
                        </tr>
                        <tr>
                            <td class="py-2 text-slate-500">Kota / Lokasi</td>
                            <td class="py-2 font-bold text-slate-900">{{ $city }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Rincian Finansial & Skema -->
            <div class="bg-slate-50 rounded-xl p-5 border border-slate-200 text-xs mb-6">
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-500 mb-3 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Ketentuan Pembayaran & Skema Transaksi
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 pt-2">
                    <div class="p-3 bg-white rounded-lg border border-slate-200">
                        <span class="text-slate-500 block text-[11px]">Harga Rumah Kesepakatan</span>
                        <span class="text-slate-900 font-extrabold text-sm block mt-0.5">Rp {{ number_format($spr->house_price, 0, ',', '.') }}</span>
                    </div>
                    <div class="p-3 bg-white rounded-lg border border-slate-200">
                        <span class="text-slate-500 block text-[11px]">Skema Pembayaran</span>
                        <span class="text-slate-900 font-extrabold text-sm block mt-0.5">{{ $schemeLabel }}</span>
                    </div>
                    <div class="p-3 bg-white rounded-lg border border-slate-200">
                        <span class="text-slate-500 block text-[11px]">Uang Muka (DP) Wajib</span>
                        <span class="text-emerald-700 font-extrabold text-sm block mt-0.5">Rp {{ number_format($spr->down_payment, 0, ',', '.') }}</span>
                    </div>
                    <div class="p-3 bg-white rounded-lg border border-slate-200">
                        <span class="text-slate-500 block text-[11px]">Tenor / Jatuh Tempo</span>
                        <span class="text-slate-900 font-extrabold text-sm block mt-0.5">
                            {{ $spr->tenor_months ? $spr->tenor_months . ' Bulan' : ($spr->payment_scheme === 'kpr' ? 'Plafon KPR' : 'Tunai') }}
                            @if($overdue['due_date'])
                                <span class="text-[11px] font-normal text-slate-500 block">Jatuh Tempo: {{ $overdue['due_date']->translatedFormat('d M Y') }}</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <!-- Tahapan Alur Pemesanan -->
            <div class="pt-4 border-t border-slate-200">
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-500 block mb-3">Alur Penerbitan Dokumen Resmi:</span>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                    <div class="bg-emerald-50 p-4 rounded-xl border border-emerald-200">
                        <span class="text-emerald-800 font-bold flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            1. Formulir Pemesanan
                        </span>
                        <span class="text-[11px] text-emerald-700 mt-1 block">Telah berhasil dibuat & tercatat di sistem</span>
                    </div>
                    @if($pendingPayment)
                        <div class="bg-amber-50 p-4 rounded-xl border border-amber-300">
                            <span class="text-amber-900 font-bold flex items-center gap-1.5">
                                <svg class="w-4 h-4 animate-spin text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                2. Verifikasi Uang Muka (DP)
                            </span>
                            <span class="text-[11px] text-amber-800 mt-1 block">Bukti transfer diunggah, sedang diverifikasi tim admin</span>
                        </div>
                    @elseif($lastFailedPayment && !$pendingPayment)
                        <div class="bg-rose-50 p-4 rounded-xl border border-rose-300">
                            <span class="text-rose-900 font-bold flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                2. Pembayaran DP Ditolak
                            </span>
                            <span class="text-[11px] text-rose-800 mt-1 block">Bukti ditolak admin. Silakan bayar / unggah ulang.</span>
                        </div>
                    @else
                        <div class="bg-amber-50 p-4 rounded-xl border border-amber-300">
                            <span class="text-amber-900 font-bold flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                2. Pembayaran Uang Muka (DP)
                            </span>
                            <span class="text-[11px] text-amber-800 mt-1 block">Menunggu pembayaran & unggah bukti transfer</span>
                        </div>
                    @endif
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 opacity-75">
                        <span class="text-slate-500 font-bold flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            3. Penerbitan SPR Resmi
                        </span>
                        <span class="text-[11px] text-slate-500 mt-1 block">Otomatis terbit setelah pembayaran disetujui</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- TAMPILKAN DOKUMEN FISIK SPR HANYA JIKA SUDAH DISETUJUI (STATUS ISSUED / PAID), ATAU JIKA VIEW DI AKSES OLEH ADMIN --}}
    @if($spr->isIssued() || $spr->isFullyPaid() || (Auth::check() && Auth::user()->isAdmin()))
    <!-- Card Dokumen Fisik SPR Resmi -->
    <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-900 p-8 sm:p-14 mb-8 text-black font-sans">
        @if($spr->status === 'draft')
            <div class="py-2.5 px-4 bg-amber-100 border-2 border-dashed border-amber-400 text-amber-900 rounded-xl font-black text-xs uppercase tracking-wider mb-6 text-center">
                DRAFT PRATINJAU — DOKUMEN RESMI BELUM DITERBITKAN (MENUNGGU PERSETUJUAN ADMIN)
            </div>
        @endif
        <!-- HEADER DOKUMEN & NOMOR SPR (SISI KANAN) -->
        <div class="flex flex-col sm:flex-row items-start justify-between gap-4 pb-6 border-b-2 border-gray-900 mb-6">
            <div>
                <h1 class="text-xl sm:text-2xl font-black uppercase tracking-wider text-black">
                    Surat Pemesanan Rumah (SPR)
                </h1>
                <p class="text-xs font-bold text-black mt-1">
                    {{ $company?->company_name ?? 'Pengembang Proyek Properti' }}
                </p>
                @if($project?->name)
                    <p class="text-xs text-gray-700">Kawasan Perumahan: {{ $project->name }}</p>
                @endif
            </div>

            <!-- SISI KANAN: NOMOR SPR CONTOH (001/SPR/PT/IV/2026) -->
            <div class="sm:text-right">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-600 block">Nomor Dokumen</span>
                <span class="text-base sm:text-lg font-black text-black font-mono block">
                    No. {{ $spr->spr_number }}
                </span>
                <span class="text-xs text-black block mt-0.5">
                    Tanggal Terbit: <strong>{{ $issueDate->translatedFormat('d F Y') }}</strong>
                </span>
                <div class="mt-2">
                    @if($isPaid)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 border-2 border-black text-xs font-black uppercase tracking-widest text-white bg-black rounded shadow-sm">
                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                            LUNAS (100%)
                        </span>
                    @elseif($spr->status === 'issued')
                        <span class="inline-flex items-center px-3 py-0.5 border border-black text-[11px] font-black uppercase tracking-widest text-black bg-white">
                            RESMI DITERBITKAN
                        </span>
                    @elseif($spr->status === 'draft')
                        <span class="inline-flex items-center px-3 py-0.5 border border-dashed border-amber-600 text-[11px] font-black uppercase tracking-widest text-amber-800 bg-amber-50">
                            DRAFT (BELUM RESMI TERBIT)
                        </span>
                    @elseif($spr->status === 'cancelled')
                        <span class="inline-flex items-center px-3 py-0.5 border border-black text-[11px] font-black uppercase tracking-widest text-white bg-black">
                            DIBATALKAN
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-0.5 border border-black text-[11px] font-black uppercase tracking-widest text-black bg-white">
                            {{ strtoupper($spr->status) }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- 1. IDENTITAS PEMESAN (SISI KIRI) -->
        <div class="mb-6">
            <h2 class="text-xs font-black uppercase text-black tracking-wider mb-2">
                Yang bertanda tangan di bawah ini :
            </h2>
            <div class="bg-white rounded-xl p-4 border border-gray-400 text-xs">
                <table class="w-full">
                    <tr class="border-b border-gray-200">
                        <td class="py-1.5 w-1/4 text-gray-700 font-semibold">Nama</td>
                        <td class="py-1.5 w-4 text-center font-bold text-black">:</td>
                        <td class="py-1.5 font-bold text-black">{{ $spr->buyer_name }}</td>
                    </tr>
                    <tr class="border-b border-gray-200">
                        <td class="py-1.5 text-gray-700 font-semibold">NIK</td>
                        <td class="py-1.5 text-center font-bold text-black">:</td>
                        <td class="py-1.5 font-bold text-black font-mono">{{ $spr->buyer_nik }}</td>
                    </tr>
                    <tr class="border-b border-gray-200">
                        <td class="py-1.5 text-gray-700 font-semibold">No. Telp / WhatsApp</td>
                        <td class="py-1.5 text-center font-bold text-black">:</td>
                        <td class="py-1.5 font-bold text-black">{{ $spr->buyer_phone }}</td>
                    </tr>
                    <tr>
                        <td class="py-1.5 text-gray-700 font-semibold">Alamat Domisili</td>
                        <td class="py-1.5 text-center font-bold text-black">:</td>
                        <td class="py-1.5 font-bold text-black">{{ $spr->buyer_address }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- 2. DATA UNIT YANG DIPESAN -->
        <div class="mb-6">
            <h2 class="text-xs font-black uppercase text-black tracking-wider mb-2">
                Dengan ini menyatakan melakukan pemesanan unit rumah sebagai berikut :
            </h2>
            <div class="bg-white rounded-xl p-4 border border-gray-400 text-xs">
                <table class="w-full">
                    <tr class="border-b border-gray-200">
                        <td class="py-1.5 w-1/4 text-gray-700 font-semibold">Nama Perumahan</td>
                        <td class="py-1.5 w-4 text-center font-bold text-black">:</td>
                        <td class="py-1.5 font-bold text-black">{{ $project?->name ?? 'Perumahan' }}</td>
                    </tr>
                    <tr class="border-b border-gray-200">
                        <td class="py-1.5 text-gray-700 font-semibold">Nama Developer</td>
                        <td class="py-1.5 text-center font-bold text-black">:</td>
                        <td class="py-1.5 font-bold text-black">{{ $company?->company_name ?? 'Developer' }}</td>
                    </tr>
                    <tr class="border-b border-gray-200">
                        <td class="py-1.5 text-gray-700 font-semibold">Tipe</td>
                        <td class="py-1.5 text-center font-bold text-black">:</td>
                        <td class="py-1.5 font-bold text-black">{{ config('nup.unit_types.' . ($unit?->type ?? ''), $unit?->type ?? '-') }}</td>
                    </tr>
                    <tr class="border-b border-gray-200">
                        <td class="py-1.5 text-gray-700 font-semibold">Blok / No</td>
                        <td class="py-1.5 text-center font-bold text-black">:</td>
                        <td class="py-1.5 font-bold text-black">Blok {{ $unit?->block ?? '-' }} / No. {{ $unit?->number ?? '-' }}</td>
                    </tr>
                    <tr class="border-b border-gray-200">
                        <td class="py-1.5 text-gray-700 font-semibold">Harga Unit</td>
                        <td class="py-1.5 text-center font-bold text-black">:</td>
                        <td class="py-1.5 font-black text-black text-sm">Rp {{ number_format($spr->unit_price, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="border-b border-gray-200">
                        <td class="py-1.5 text-gray-700 font-semibold">UTK (Uang Tanda Komitmen)</td>
                        <td class="py-1.5 text-center font-bold text-black">:</td>
                        <td class="py-1.5 font-bold text-black">
                            @if($isCash)
                                <span>Rp 0 (Bebas UTK - Skema Pembayaran Tunai)</span>
                            @else
                                <span>Rp {{ number_format($utkAmount, 0, ',', '.') }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr class="border-b border-gray-200">
                        <td class="py-1.5 text-gray-700 font-semibold">Cara Bayar / Pembiayaan</td>
                        <td class="py-1.5 text-center font-bold text-black">:</td>
                        <td class="py-1.5 font-black text-black uppercase">{{ $schemeLabel }}</td>
                    </tr>
                    @if($spr->payment_schedule)
                        <tr class="border-b border-gray-200">
                            <td class="py-1.5 text-gray-700 font-semibold">Jadwal / Rencana Termin</td>
                            <td class="py-1.5 text-center font-bold text-black">:</td>
                            <td class="py-1.5 font-bold text-black">{{ $spr->payment_schedule }}</td>
                        </tr>
                    @endif
                    <tr class="border-b border-gray-200 bg-gray-50/70">
                        <td class="py-2.5 text-gray-900 font-bold">Status Pembayaran</td>
                        <td class="py-2.5 text-center font-bold text-black">:</td>
                        <td class="py-2.5">
                            @if($isPaid)
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-black text-white text-xs font-black uppercase rounded tracking-wider">
                                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        LUNAS (100%)
                                    </span>
                                    <span class="text-xs text-gray-700 font-semibold">
                                        — Tanggal Lunas: <strong class="text-black">{{ $paidDate->translatedFormat('d F Y') }}</strong>
                                    </span>
                                </div>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 border border-black text-xs font-bold uppercase text-black bg-white rounded">
                                    BELUM LUNAS
                                </span>
                            @endif
                        </td>
                    </tr>
                    <tr class="border-b border-gray-200">
                        <td class="py-1.5 text-gray-700 font-semibold">Total Nilai Terbayar</td>
                        <td class="py-1.5 text-center font-bold text-black">:</td>
                        <td class="py-1.5 font-bold text-black">
                            Rp {{ number_format($totalSudahDibayar, 0, ',', '.') }}
                            @if($isPaid)
                                <span class="text-[11px] text-gray-600 font-normal ml-1">(Lunas Penuh)</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="py-1.5 text-gray-700 font-semibold">{{ $spr->payment_scheme === 'kpr' ? 'Plafon Pembiayaan KPR Bank' : ($spr->payment_scheme === 'mitra_pintar' ? 'Pelunasan Lembaga Mitra Pintar' : 'Sisa Tagihan / Kewajiban') }}</td>
                        <td class="py-1.5 text-center font-bold text-black">:</td>
                        <td class="py-1.5 font-black text-black">
                            @if($spr->payment_scheme === 'kpr')
                                Rp {{ number_format($spr->unit_price, 0, ',', '.') }} <span class="text-xs font-semibold text-blue-700 ml-1">(100% Plafon KPR Bank)</span>
                            @elseif($isPaid)
                                Rp 0 <span class="text-xs font-bold text-black ml-1">(Bebas Kewajiban Pembayaran / Lunas 100%)</span>
                            @elseif($spr->payment_scheme === 'mitra_pintar')
                                Rp {{ number_format($sisaPembayaran, 0, ',', '.') }} <span class="text-xs font-semibold text-amber-800 ml-1">(Dicairkan oleh Lembaga Mitra Pintar - Bebas DP)</span>
                            @else
                                Rp {{ number_format($sisaPembayaran, 0, ',', '.') }}
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
            @if($isPaid)
                <!-- OFFICIAL MONOCHROME STAMP LUNAS -->
                <div class="mt-3 p-3.5 bg-gray-50 border-2 border-dashed border-black rounded-xl flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full border-2 border-black flex items-center justify-center bg-white text-black shrink-0">
                            <svg class="w-5 h-5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <div class="text-xs font-black uppercase tracking-wider text-black">STATUS DOKUMEN: LUNAS 100%</div>
                            <div class="text-[11px] text-gray-600">Unit pemesanan ini telah lunas dan diverifikasi resmi oleh pengembang pada tanggal <strong>{{ $paidDate->translatedFormat('d F Y') }}</strong>.</div>
                        </div>
                    </div>
                    <div class="border-2 border-black px-4 py-1.5 rounded text-center bg-white shrink-0">
                        <div class="text-[8px] font-black uppercase tracking-widest text-black">SURAT PEMESANAN RUMAH</div>
                        <div class="text-sm font-black uppercase tracking-widest text-black border-y border-black my-0.5 py-0.5">RESMI LUNAS</div>
                        <div class="text-[8px] font-mono font-bold text-gray-600">{{ $paidDate->translatedFormat('d/m/Y') }} • TERVERIFIKASI</div>
                    </div>
                </div>
            @endif
        </div>

        <!-- 3. ATURAN & KETENTUAN -->
        <div class="mb-8">
            <h2 class="text-xs font-black uppercase text-black tracking-wider mb-2">
                Dan bersedia mengikuti aturan sebagai berikut :
            </h2>
            <div class="p-4 bg-white rounded-xl border border-gray-400 text-xs text-black space-y-2 leading-relaxed">
                <ol class="list-decimal list-inside space-y-1.5 pl-1">
                    <li>
                        <strong>Komitmen Pemesanan Unit:</strong> Pembayaran Uang Tanda Komitmen (UTK) / pemilihan unit merupakan bukti sah penguncian unit dan bersifat mengikat kedua belah pihak.
                    </li>
                    <li>
                        <strong>Kelengkapan Berkas & Administrasi:</strong> Pemesan bersedia melengkapi dan menandatangani seluruh kelengkapan berkas administrasi pembiayaan selambat-lambatnya 7 (tujuh) hari kerja sejak SPR ini diterbitkan.
                    </li>
                    @if($spr->payment_scheme === 'kpr' || str_contains(strtolower($spr->payment_scheme), 'kpr'))
                        <li>
                            <strong>Ketentuan Skema KPR Bank:</strong> Realisasi pembiayaan tunduk pada keputusan persetujuan kredit (SP3K) bank rekanan. Apabila terjadi penurunan plafon kredit oleh bank, Pemesan bersedia menambah pembayaran uang muka atau beralih ke skema In-House / Tunai.
                        </li>
                    @elseif($spr->payment_scheme === 'tunai_bertahap')
                        <li>
                            <strong>Ketentuan Skema Tunai Bertahap:</strong> Pelunasan pembayaran sisa harga unit diselesaikan bertahap dalam jangka hari (termin hari) sesuai kesepakatan pengembang.
                        </li>
                    @elseif($spr->payment_scheme === 'installment' || str_contains(strtolower($spr->payment_scheme), 'inhouse'))
                        <li>
                            <strong>Ketentuan Skema In-House:</strong> Pembayaran angsuran cicilan bulanan wajib disetorkan paling lambat sesuai tanggal jatuh tempo bulanan yang telah disepakati pengembang.
                        </li>
                    @elseif($spr->payment_scheme === 'mitra_pintar')
                        <li>
                            <strong>Ketentuan Kredit Mitra Pintar:</strong> Pemesan wajib mematuhi seluruh klausul perjanjian kerja sama (PKS) instansi mitra yang disepakati bersama pengembang.
                        </li>
                    @else
                        <li>
                            <strong>Ketentuan Skema Tunai:</strong> Pelunasan pembayaran unit diselesaikan sesuai jadwal termin yang disepakati tanpa keterlambatan.
                        </li>
                    @endif
                    <li>
                        <strong>Pembatalan Sepihak:</strong> Apabila pemesan membatalkan pemesanan secara sepihak di luar ketentuan yang disepakati, maka tata cara pengembalian dan potongan biaya administrasi mengacu pada kebijakan resmi pengembang.
                    </li>
                    <li>
                        <strong>Keabsahan Dokumen Elektronik:</strong> Surat Pemesanan Rumah (SPR) ini sah dan mengikat para pihak secara elektronik sejak disetujui dan ditandatangani oleh para pihak.
                    </li>
                </ol>
            </div>
        </div>

        <!-- 4. BAGIAN TANDA TANGAN (KIRI: BROKER/MARKETING, KANAN: TGL & TTD DEVELOPER) -->
        <div class="pt-6 border-t-2 border-gray-900 relative">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 items-start">
                <!-- SEBELAH KIRI: NAMA BROKER / MARKETING -->
                <div class="text-center p-4 bg-white rounded-2xl border border-gray-400">
                    <p class="text-xs font-bold text-black mb-1">Pihak Pemasar / Broker :</p>
                    <div class="h-28 flex items-center justify-center">
                        @if($brokerSignature)
                            <img src="{{ asset('storage/' . $brokerSignature) }}" alt="Tanda Tangan Broker" class="max-h-24 max-w-full object-contain filter drop-shadow">
                        @else
                            <div class="text-center">
                                <svg class="w-8 h-8 text-gray-400 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                                <span class="text-[10px] text-gray-500 font-mono">[ Tanda Tangan Broker ]</span>
                            </div>
                        @endif
                    </div>
                    <p class="text-xs font-bold text-black underline">{{ $brokerName }}</p>
                    <p class="text-[11px] text-gray-600">{{ $broker?->office_name ?? 'Broker / Marketing Penjualan' }}</p>
                </div>

                <!-- SEBELAH KANAN: TGL TERBIT SPR & TTD DEVELOPER -->
                <div class="text-center p-4 bg-white rounded-2xl border border-gray-400 relative">
                    @if($isPaid)
                        <!-- WATERMARK CAP LUNAS DEVELOPER -->
                        <div class="absolute -top-3 right-3 transform rotate-6 pointer-events-none select-none z-10">
                            <span class="inline-block border-2 border-dashed border-black px-2 py-0.5 text-[10px] font-black uppercase tracking-widest text-black bg-white shadow-xs">
                                RESMI LUNAS
                            </span>
                        </div>
                    @endif
                    <p class="text-xs text-black mb-0.5">{{ $city }}, {{ $issueDate->translatedFormat('d F Y') }}</p>
                    <p class="text-xs font-bold text-black mb-1">Pihak Pengembang (Developer) :</p>

                    <div class="h-28 flex items-center justify-center">
                        @if($company && $company->signature_image)
                            <img src="{{ asset('storage/' . $company->signature_image) }}" alt="Tanda Tangan Developer" class="max-h-24 max-w-full object-contain filter drop-shadow">
                        @else
                            <div class="text-center">
                                <svg class="w-8 h-8 text-gray-400 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                                <span class="text-[10px] text-gray-500 font-mono">[ Tanda Tangan Developer ]</span>
                            </div>
                        @endif
                    </div>

                    <p class="text-xs font-bold text-black underline mt-1">
                        {{ $company?->pic_name ?? ($company?->company_name ?? 'Direktur Pengembang') }}
                    </p>
                    <p class="text-[11px] text-gray-600">{{ $company?->company_name ?? 'Developer Properti' }}</p>
                </div>
            </div>

            <!-- TANDA TANGAN PEMESAN / KONSUMEN -->
            <div class="mt-6 pt-4 border-t border-gray-300 flex items-center justify-between text-xs text-gray-600">
                <div>
                    Dokumen ini sah dan mengikat para pihak secara elektronik.
                </div>
                <div class="text-right">
                    <span>Pemesan / Konsumen: </span>
                    <strong class="text-black">{{ $spr->buyer_name }}</strong>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- ==================== WORKFLOW KPR: ARGO, SP3K & AKAD KREDIT ==================== -->
    @if($spr->payment_scheme === 'kpr')
        <!-- SECTION 1: ARGO COUNTDOWN 7 HARI PENGURUSAN BANK -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sm:p-8 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">⏱️</span>
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-gray-900">Argo Pengurusan KPR Bank (7 Hari)</h2>
                        <p class="text-xs text-gray-500">Proses pengawalan berkas oleh tim developer ke bank rekanan hingga keputusan SP3K.</p>
                    </div>
                </div>
                <div>
                    @if($spr->isSp3kApproved())
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-emerald-100 text-emerald-800 rounded-full text-xs font-bold">
                            <svg class="w-3.5 h-3.5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            <span>SELESAI - SP3K DISETUJUI</span>
                        </span>
                    @elseif($spr->isSp3kRejected())
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-rose-100 text-rose-800 rounded-full text-xs font-bold">
                            <svg class="w-3.5 h-3.5 text-rose-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                            <span>KPR TIDAK DISETUJUI BANK</span>
                        </span>
                    @elseif($spr->argo_deadline)
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 {{ $spr->isArgoExpired() ? 'bg-rose-100 text-rose-800' : 'bg-indigo-100 text-indigo-800' }} rounded-full text-xs font-bold font-mono">
                            <svg class="w-3.5 h-3.5 {{ $spr->isArgoExpired() ? 'text-rose-700' : 'text-indigo-700' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Sisa Argo: {{ $spr->getArgoRemainingText() }}</span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-gray-100 text-gray-700 rounded-full text-xs font-bold">
                            <svg class="w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Menunggu Pengajuan Bank</span>
                        </span>
                    @endif
                </div>
            </div>

            <div class="mt-4 text-xs text-gray-600 bg-gray-50 p-4 rounded-xl leading-relaxed">
                <p>
                    <strong>Ketentuan Argo 7 Hari:</strong> Setelah penandatanganan SPR dan pembayaran komitmen, operator pengembang menyerahkan berkas KPR Anda ke Bank Rekanan. Bank memproses analisis kredit dalam kurun waktu 7 hari kerja untuk menerbitkan Surat Penegasan Persetujuan Penyediaan Kredit (SP3K).
                </p>
                @if($spr->argo_deadline)
                    <p class="mt-2 text-indigo-700 font-semibold">
                        Batas Waktu Pengurusan (Deadline Argo): {{ $spr->argo_deadline->format('l, d F Y H:i') }}
                    </p>
                @endif
            </div>
        </div>

        <!-- SECTION 2: JIKA KPR ACC (SP3K APPROVED) & JADWAL AKAD KREDIT -->
        @if($spr->isSp3kApproved())
            <!-- Banner SP3K Terbit -->
            <div class="bg-emerald-50 border border-emerald-300 rounded-2xl p-6 sm:p-8 mb-6">
                <div class="flex items-start gap-4">
                    <svg class="w-14 h-14 text-emerald-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                    <div class="flex-1">
                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Keputusan Bank: DISETUJUI (ACC)</span>
                        <h2 class="text-lg sm:text-xl font-extrabold text-emerald-950 mt-0.5">Selamat! KPR Anda Telah Disetujui Bank (SP3K Terbit)</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4 bg-white/80 p-4 rounded-xl text-xs border border-emerald-200">
                            <div>
                                <span class="block text-gray-500">Nomor Dokumen SP3K</span>
                                <span class="font-bold text-sm text-gray-900">{{ $spr->sp3k_number ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="block text-gray-500">Plafon Kredit yang Disetujui (ACC)</span>
                                <span class="font-extrabold text-sm text-emerald-700">Rp {{ number_format($spr->sp3k_approved_amount, 0, ',', '.') }}</span>
                            </div>
                            <div>
                                <span class="block text-gray-500">Bukti Dokumen SP3K</span>
                                @if($spr->sp3k_file)
                                    <a href="{{ asset('storage/' . $spr->sp3k_file) }}" target="_blank" class="inline-flex items-center gap-1 font-bold text-blue-600 hover:underline mt-0.5">
                                        <svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg> Unduh File SP3K (PDF)
                                    </a>
                                @else
                                    <span class="text-gray-400">Tercatat di sistem</span>
                                @endif
                            </div>
                        </div>
                        @if($spr->sp3k_notes)
                            <p class="text-xs text-emerald-900 mt-3 pt-2 border-t border-emerald-200">
                                <strong>Catatan dari Bank:</strong> {{ $spr->sp3k_notes }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Card Jadwal Akad Kredit & Checklist Dokumen Asli (HANYA TAMPIL JIKA SUDAH DIISI OPERATOR/DEVELOPER) -->
            @if($spr->akad_date)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sm:p-8 mb-6">
                    <div class="pb-4 border-b border-gray-100 flex items-center justify-between flex-wrap gap-2">
                        <div>
                            <h2 class="text-base sm:text-lg font-bold text-gray-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>Jadwal Akad Kredit & Penandatanganan Notaris</span>
                            </h2>
                            <p class="text-xs text-gray-500 mt-0.5">Berikut rincian waktu, lokasi akad, dan berkas fisik asli yang ditetapkan oleh pengembang & bank.</p>
                        </div>
                        <div>
                            @if($spr->is_akad_completed)
                                <span class="px-3.5 py-1.5 bg-emerald-100 text-emerald-800 rounded-full text-xs font-bold inline-flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span>AKAD SELESAI DILAKSANAKAN</span>
                                </span>
                            @else
                                <span class="px-3.5 py-1.5 bg-indigo-100 text-indigo-800 rounded-full text-xs font-bold inline-flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span>JADWAL AKAD DITETAPKAN</span>
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Detail Jadwal Akad -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 my-6 p-4 bg-indigo-50/50 rounded-xl border border-indigo-200 text-xs">
                        <div>
                            <span class="block text-indigo-700/70 font-semibold">Hari & Tanggal Akad</span>
                            <span class="font-extrabold text-sm text-indigo-950">{{ \Carbon\Carbon::parse($spr->akad_date)->translatedFormat('l, d F Y') }}</span>
                            <span class="block text-indigo-800 text-[11px] mt-0.5">Pukul: {{ \Carbon\Carbon::parse($spr->akad_date)->format('H:i') }} WIB</span>
                        </div>
                        <div>
                            <span class="block text-indigo-700/70 font-semibold">Tempat / Kantor Cabang Bank</span>
                            <span class="font-extrabold text-sm text-indigo-950">{{ $spr->akad_location }}</span>
                        </div>
                        <div>
                            <span class="block text-indigo-700/70 font-semibold">Notaris Rekanan</span>
                            <span class="font-extrabold text-sm text-indigo-950">{{ $spr->akad_notary ?? 'Ditentukan Bank' }}</span>
                        </div>
                    </div>

                    @if($spr->akad_notes)
                        <div class="mb-4 p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700">
                            <span class="font-bold text-slate-900 block mb-1">Catatan Tambahan dari Developer:</span>
                            <p class="whitespace-pre-line">{{ $spr->akad_notes }}</p>
                        </div>
                    @endif

                    <!-- CHECKLIST DOKUMEN ASLI YANG DIISI SENDIRI OLEH OPERATOR -->
                    @if(!empty($spr->akad_checklist) && count($spr->akad_checklist) > 0)
                        <div class="p-5 bg-amber-50/60 rounded-xl border border-amber-200">
                            <h3 class="text-sm font-bold text-amber-950 mb-1 flex items-center gap-2">
                                <svg class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>Checklist Dokumen Fisik Asli yang Wajib Dibawa saat Akad</span>
                            </h3>
                            <p class="text-xs text-amber-800 mb-4">
                                Saat hadir di bank untuk akad kredit, Anda wajib membawa dokumen fisik asli berikut sesuai instruksi developer & bank:
                            </p>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                @foreach($spr->akad_checklist as $item)
                                    <div class="flex items-start gap-2.5 p-3 bg-white rounded-lg border border-amber-300 font-semibold text-gray-800">
                                        <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        <span class="font-bold text-gray-900">{{ $item }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        @endif

        <!-- SECTION 3: JIKA KPR DITOLAK (RESCUE OPTIONS: IN-HOUSE, TUNAI, ATAU BATAL) -->
        @if($spr->isSp3kRejected())
            <div class="bg-rose-50 border border-rose-300 rounded-2xl p-6 sm:p-8 mb-6">
                <div class="flex items-start gap-4">
                    <span class="text-4xl"><svg class="w-4 h-4 inline-block text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></span>
                    <div class="flex-1">
                        <span class="text-xs font-bold uppercase tracking-wider text-rose-700">Hasil Pengajuan KPR</span>
                        <h2 class="text-lg sm:text-xl font-bold text-rose-950 mt-0.5">Pengajuan KPR Anda Belum Disetujui Bank</h2>
                        <p class="text-xs text-rose-900 mt-1 leading-relaxed">
                            Alasan / Catatan Bank: <strong>{{ $spr->sp3k_notes ?? 'Tidak memenuhi kriteria rasio angsuran bank (DSR) atau batas kolektibilitas kredit.' }}</strong>
                        </p>

                        <!-- Pilihan Penyelamatan Unit -->
                        <div class="mt-6 pt-4 border-t border-rose-200">
                            <h3 class="text-sm font-bold text-gray-900 mb-2">Pilihan Langkah Lanjutan untuk Anda:</h3>
                            <p class="text-xs text-gray-600 mb-4">Anda tetap dapat memiliki unit rumah ini dengan mengalihkan ke skema cicilan mandiri developer (In-House) atau pelunasan tunai.</p>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <!-- Opsi 1: Beralih ke In-House Developer -->
                                <form action="{{ route('spr.rescue-switch', $spr->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin beralih ke skema In-House Developer?')">
                                    @csrf
                                    <input type="hidden" name="action" value="switch_inhouse">
                                    <button type="submit" class="w-full p-4 bg-white hover:bg-indigo-50 border-2 border-indigo-500 rounded-xl text-left transition shadow-sm">
                                        <svg class="w-4 h-4 inline-block text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span class="font-bold text-sm text-indigo-900 block mt-1">Alih ke In-House</span>
                                        <span class="text-[11px] text-gray-500 block mt-0.5">Cicilan bertahap langsung ke pengembang tanpa bank</span>
                                    </button>
                                </form>

                                <!-- Opsi 2: Beralih ke Tunai -->
                                <form action="{{ route('spr.rescue-switch', $spr->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin beralih ke skema Tunai?')">
                                    @csrf
                                    <input type="hidden" name="action" value="switch_cash">
                                    <button type="submit" class="w-full p-4 bg-white hover:bg-emerald-50 border-2 border-emerald-500 rounded-xl text-left transition shadow-sm">
                                        <svg class="w-4 h-4 inline-block text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        <span class="font-bold text-sm text-emerald-900 block mt-1">Alih ke Tunai</span>
                                        <span class="text-[11px] text-gray-500 block mt-0.5">Pelunasan sekaligus atau termin bertahap</span>
                                    </button>
                                </form>

                                <!-- Opsi 3: Batalkan Pemesanan -->
                                <div>
                                    <button type="button" onclick="document.getElementById('cancelSprModal').classList.remove('hidden')" class="w-full p-4 bg-white hover:bg-rose-50/50 border border-slate-200 hover:border-rose-300 rounded-xl text-left transition group">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center shrink-0 group-hover:bg-rose-100 transition">
                                                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </div>
                                            <div>
                                                <span class="font-bold text-sm text-slate-800 group-hover:text-rose-900 block">Batalkan Pemesanan Unit</span>
                                                <span class="text-[11px] text-slate-400 block mt-0.5">Biaya NUP & UTK hangus, unit dilepas kembali</span>
                                            </div>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif

    <!-- ==================== OPERATOR / ADMIN ACTION CONTROLS ==================== -->
    @if(Auth::user()->isAdmin() && $spr->payment_scheme === 'kpr')
        <div class="bg-gray-900 text-white rounded-2xl shadow-xl p-6 sm:p-8 mb-6">
            <h2 class="text-base font-bold text-white mb-1 flex items-center gap-2">
                <svg class="w-4 h-4 inline-block text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg> Panel Operator Developer: Kelola Alur KPR & Jadwal Akad
            </h2>
            <p class="text-xs text-gray-400 mb-6 pb-4 border-b border-gray-800">
                Gunakan formulir berikut untuk memperbarui status pengajuan bank, mengunggah SP3K, serta menetapkan jadwal akad kredit untuk pemohon.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- FORM 1: Status Pengajuan Bank & Perpanjang Argo -->
                <div class="bg-gray-800/80 p-5 rounded-xl border border-gray-700 text-xs flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-sm text-blue-400 mb-3 flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full bg-blue-500/20 text-blue-400 font-bold inline-flex items-center justify-center text-[11px]">1</span>
                            <span>Pengajuan Berkas ke Bank & Durasi Argo</span>
                        </h3>

                        <!-- Info Status Argo Aktif -->
                        <div class="p-3.5 bg-blue-950/60 border border-blue-800/50 rounded-xl space-y-2 mb-4">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400 text-[11px] font-medium">Status Berkas:</span>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-500/20 text-blue-300 border border-blue-500/30">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-pulse"></span>
                                    Sedang Diproses Bank Rekanan
                                </span>
                            </div>
                            <div class="flex items-center justify-between pt-1 border-t border-blue-900/40">
                                <span class="text-gray-400 text-[11px] font-medium">Sisa Waktu Argo:</span>
                                <span class="font-bold {{ $spr->isArgoExpired() ? 'text-rose-400' : 'text-emerald-300' }}">
                                    {{ $spr->getArgoRemainingText() }}
                                </span>
                            </div>
                            @if($spr->argo_deadline)
                                <div class="flex items-center justify-between text-[10px] text-gray-400">
                                    <span>Tenggat Target:</span>
                                    <span>{{ $spr->argo_deadline->format('d M Y, H:i') }} WIB</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Form Perpanjang Durasi Argo -->
                    <form action="{{ route('spr.bank-submission', $spr->id) }}" method="POST" class="space-y-3 pt-2 border-t border-gray-700/60">
                        @csrf
                        <input type="hidden" name="bank_submission_status" value="submitted_to_bank">
                        <div>
                            <label class="block text-gray-300 font-semibold mb-1">Perpanjang / Reset Durasi Argo (Hari)</label>
                            <div class="flex items-center gap-2">
                                <input type="number" name="argo_days" value="7" min="1" max="60" class="w-full bg-gray-900 border border-gray-700 rounded-lg p-2.5 text-xs text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <button type="submit" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold transition whitespace-nowrap">
                                    Perpanjang Argo
                                </button>
                            </div>
                            <span class="text-[11px] text-gray-400 mt-1 block">Gunakan jika bank rekanan memerlukan tambahan waktu verifikasi berkas.</span>
                        </div>
                    </form>
                </div>

                <!-- FORM 2: Input Hasil SP3K dari Bank -->
                <div class="bg-gray-800/80 p-5 rounded-xl border border-gray-700 text-xs">
                    <h3 class="font-bold text-sm text-emerald-400 mb-3 flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 font-bold inline-flex items-center justify-center text-[11px]">2</span>
                        <span>Unggah Hasil Keputusan Bank (SP3K)</span>
                    </h3>
                    <form action="{{ route('spr.sp3k', $spr->id) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-gray-300 font-semibold mb-1">Keputusan Bank</label>
                            <select name="bank_submission_status" class="w-full bg-gray-900 border border-gray-700 rounded-lg p-2.5 text-xs text-white focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                                <option value="sp3k_approved" {{ $spr->bank_submission_status === 'sp3k_approved' ? 'selected' : '' }}>
                                    <svg class="w-4 h-4 inline-block text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> SP3K Disetujui (KPR ACC)
                                </option>
                                <option value="sp3k_rejected" {{ $spr->bank_submission_status === 'sp3k_rejected' ? 'selected' : '' }}>
                                    <svg class="w-4 h-4 inline-block text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg> Ditolak oleh Bank
                                </option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-gray-300 font-semibold mb-1">Nomor SP3K</label>
                                <input type="text" name="sp3k_number" value="{{ old('sp3k_number', $spr->sp3k_number) }}" placeholder="No. SP3K" class="w-full bg-gray-900 border border-gray-700 rounded-lg p-2 text-xs text-white">
                            </div>
                            <div>
                                <label class="block text-gray-300 font-semibold mb-1">Plafon ACC (Rp)</label>
                                <input type="number" name="sp3k_approved_amount" value="{{ old('sp3k_approved_amount', $spr->sp3k_approved_amount ?? $spr->remaining_amount) }}" placeholder="Nominal ACC" class="w-full bg-gray-900 border border-gray-700 rounded-lg p-2 text-xs text-white">
                            </div>
                        </div>
                        <div>
                            <label class="block text-gray-300 font-semibold mb-1">Upload File SP3K (PDF/Gambar)</label>
                            <input type="file" name="sp3k_file" accept=".pdf,image/*" class="w-full bg-gray-900 border border-gray-700 rounded-lg p-1.5 text-xs text-white">
                        </div>
                        <div>
                            <label class="block text-gray-300 font-semibold mb-1">Catatan / Syarat Khusus Bank</label>
                            <input type="text" name="sp3k_notes" value="{{ old('sp3k_notes', $spr->sp3k_notes) }}" placeholder="Contoh: Buka tabungan di bank rekanan min saldo Rp 2jt" class="w-full bg-gray-900 border border-gray-700 rounded-lg p-2 text-xs text-white">
                        </div>
                        <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold transition">
                            Simpan Hasil SP3K & Beri Notifikasi WA
                        </button>
                    </form>
                </div>

                <!-- FORM 3: Input Jadwal Akad Kredit (Muncul bila SP3K ACC) -->
                @if($spr->isSp3kApproved())
                    <div class="bg-gray-800/80 p-5 rounded-xl border border-gray-700 text-xs md:col-span-2">
                        <h3 class="font-bold text-sm text-indigo-400 mb-3 flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full bg-indigo-500/20 text-indigo-400 font-bold inline-flex items-center justify-center text-[11px]">3</span>
                            <span>Jadwal Akad Kredit & Penandatanganan Notaris</span>
                        </h3>
                        <form action="{{ route('spr.akad', $spr->id) }}" method="POST" class="space-y-3">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-gray-300 font-semibold mb-1">Tanggal & Waktu Akad</label>
                                    <input type="datetime-local" name="akad_date" value="{{ old('akad_date', $spr->akad_date ? $spr->akad_date->format('Y-m-d\TH:i') : '') }}" class="w-full bg-gray-900 border border-gray-700 rounded-lg p-2 text-xs text-white" required>
                                </div>
                                <div>
                                    <label class="block text-gray-300 font-semibold mb-1">Lokasi Kantor Cabang Bank</label>
                                    <input type="text" name="akad_location" value="{{ old('akad_location', $spr->akad_location) }}" placeholder="Contoh: Bank BTN KC Surabaya Pemuda" class="w-full bg-gray-900 border border-gray-700 rounded-lg p-2 text-xs text-white" required>
                                </div>
                                <div>
                                    <label class="block text-gray-300 font-semibold mb-1">Nama Notaris Rekanan</label>
                                    <input type="text" name="akad_notary" value="{{ old('akad_notary', $spr->akad_notary) }}" placeholder="Contoh: Notaris Ahmad, SH., M.Kn." class="w-full bg-gray-900 border border-gray-700 rounded-lg p-2 text-xs text-white">
                                </div>
                            </div>
                            <div>
                                <label class="block text-gray-300 font-semibold mb-1">Checklist Dokumen Fisik Asli yang Wajib Dibawa Pemohon (Pilih yang Diperlukan)</label>
                                @php
                                    $savedChecklist = (array) ($spr->akad_checklist ?? []);
                                @endphp
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 p-3 bg-gray-900 rounded-lg border border-gray-700 text-xs text-gray-300 mb-2">
                                    @foreach([
                                        'E-KTP Asli Pemohon & Pasangan',
                                        'Kartu Keluarga (KK) Asli',
                                        'Buku Nikah / Akta Cerai Asli',
                                        'Kartu NPWP Asli',
                                        'Materai 10.000 Fisik (10 - 15 Lembar)',
                                        'Slip Gaji Asli 3 Bulan Terakhir',
                                        'Rekening Koran 3 Bulan Terakhir (Cap Basah)',
                                        'Pas Foto 3x4 Suami & Istri',
                                    ] as $doc)
                                        <label class="flex items-center gap-2 cursor-pointer hover:text-white">
                                            <input type="checkbox" name="akad_checklist[]" value="{{ $doc }}" {{ in_array($doc, $savedChecklist, true) ? 'checked' : '' }} class="rounded text-indigo-600 bg-gray-800 border-gray-600">
                                            <span>{{ $doc }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                <div>
                                    <label class="block text-gray-400 text-[11px] mb-0.5">Dokumen Fisik Tambahan Lainnya (Opsional, pisahkan baris baru untuk setiap dokumen):</label>
                                    <textarea name="akad_custom_docs" rows="2" class="w-full bg-gray-900 border border-gray-700 rounded-lg p-2 text-xs text-white" placeholder="Contoh:&#10;Surat Keterangan Belum Memiliki Rumah&#10;SK Pengangkatan Karyawan Asli"></textarea>
                                </div>
                            </div>
                            <div>
                                <label class="block text-gray-300 font-semibold mb-1">Catatan Tambahan untuk Pemohon</label>
                                <textarea name="akad_notes" rows="2" class="w-full bg-gray-900 border border-gray-700 rounded-lg p-2 text-xs text-white" placeholder="Instruksi kedatangan, dresscode, dan petunjuk khusus lainnya...">{{ old('akad_notes', $spr->akad_notes) }}</textarea>
                            </div>
                            <div class="flex items-center justify-end pt-2">
                                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold transition flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span>Simpan / Perbarui Jadwal Akad</span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- FORM 4: Konfirmasi Pelaksanaan Akad Kredit (Tersendiri / Sekali Klik) -->
                    <div class="bg-gray-800/90 p-5 rounded-xl border border-gray-700 text-xs md:col-span-2">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-start gap-3.5">
                                <div class="w-10 h-10 rounded-xl {{ $spr->is_akad_completed ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-indigo-500/20 text-indigo-400 border border-indigo-500/30' }} flex items-center justify-center shrink-0">
                                    <span class="w-5 h-5 rounded-full {{ $spr->is_akad_completed ? 'bg-emerald-500 text-white' : 'bg-indigo-500/30 text-indigo-300' }} font-bold inline-flex items-center justify-center text-[11px]">
                                        @if($spr->is_akad_completed)
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        @else
                                            4
                                        @endif
                                    </span>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h3 class="font-bold text-sm text-white">Konfirmasi Pelaksanaan Akad Kredit</h3>
                                        @if($spr->is_akad_completed)
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                                SUDAH SELESAI DILAKSANAKAN
                                            </span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-500/20 text-amber-400 border border-amber-500/30 flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                                                MENUNGGU PELAKSANAAN AKAD
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-gray-300 mt-1 leading-relaxed">
                                        @if($spr->is_akad_completed)
                                            Proses penandatanganan akad kredit di hadapan notaris telah selesai dilaksanakan. Status SPR resmi <strong class="text-emerald-400">LUNAS 100%</strong> dan siap dilanjutkan ke Serah Terima Kunci (BAST).
                                        @else
                                            Bila proses penandatanganan akad kredit pemohon telah selesai dilaksanakan bersama pihak bank dan notaris, klik tombol di bawah untuk menandai tahap akad selesai secara langsung.
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div class="shrink-0 flex items-center gap-2 self-end sm:self-auto">
                                @if(!$spr->is_akad_completed)
                                    <form action="{{ route('spr.complete-akad', $spr->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin proses penandatanganan Akad Kredit telah selesai dilaksanakan?')">
                                        @csrf
                                        <input type="hidden" name="is_completed" value="1">
                                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold shadow-md hover:shadow-emerald-600/30 transition text-xs">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            <span>Tandai Akad Kredit Selesai</span>
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('spr.complete-akad', $spr->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan status selesai akad kredit?')">
                                        @csrf
                                        <input type="hidden" name="is_completed" value="0">
                                        <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-gray-700 hover:bg-gray-600 text-gray-300 rounded-lg text-xs font-semibold transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            <span>Batal Tandai Selesai</span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- ==================== WORKFLOW MITRA PINTAR (LANGKAH 13 & 14) ==================== -->
    @if($spr->payment_scheme === 'mitra_pintar')
        <div class="bg-white rounded-2xl shadow-sm border border-amber-200 p-6 sm:p-8 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <svg class="w-12 h-12 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-gray-900">Pencairan Pelunasan Lembaga Mitra Pintar (Bebas DP)</h2>
                        <p class="text-xs text-gray-500">Pelunasan sisa harga unit rumah dicairkan langsung oleh instansi mitra ke rekening pengembang.</p>
                    </div>
                </div>
                <div>
                    @if($isPaid)
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-emerald-100 text-emerald-800 rounded-full text-xs font-bold">
                            <svg class="w-3.5 h-3.5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            <span>PENCAIRAN DITERIMA (LUNAS 100%)</span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-amber-100 text-amber-800 rounded-full text-xs font-bold">
                            <svg class="w-3.5 h-3.5 text-amber-700 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>MENUNGGU PENCAIRAN MITRA</span>
                        </span>
                    @endif
                </div>
            </div>

            <div class="mt-4 text-xs text-gray-700 bg-amber-50/70 border border-amber-200 p-4 rounded-xl leading-relaxed space-y-2">
                @if($isPaid)
                    <p class="font-bold text-emerald-800 text-sm">
                        <svg class="w-4 h-4 inline-block text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Dana Pelunasan Unit Sebesar Rp {{ number_format($spr->unit_price, 0, ',', '.') }} Telah Diterima Pengembang
                    </p>
                    <p class="text-gray-600">
                        Pembayaran pelunasan dari Lembaga Mitra Pintar telah dikonfirmasi masuk ke rekening developer pada tanggal <strong>{{ $paidDate->translatedFormat('d F Y') }}</strong>. Kewajiban pembayaran unit kepada pengembang telah dinyatakan <strong>Lunas 100%</strong>.
                    </p>
                @else
                    <p>
                        <strong>Ketentuan Skema Kredit Mitra Pintar (Bebas DP):</strong> Pemesan telah melunasi Uang Tanda Komitmen (UTK) untuk mengunci unit rumah tanpa dikenakan kewajiban Uang Muka (DP Rp 0).
                    </p>
                    <p>
                        Pelunasan penuh harga rumah sebesar <strong class="text-amber-950 font-extrabold">Rp {{ number_format($spr->unit_price, 0, ',', '.') }}</strong> diselesaikan oleh <strong>Lembaga Mitra Pintar {{ $spr->unitSelection?->mitra_institution_name ? '(' . $spr->unitSelection->mitra_institution_name . ')' : '' }}</strong> ke rekening pengembang di luar sistem aplikasi. Pembeli tidak perlu melakukan transfer cicilan bulanan di website ini.
                    </p>
                @endif
            </div>
        </div>

        @if(Auth::check() && Auth::user()->isAdmin() && !$isPaid)
            <div id="mitra-disbursement-panel" class="bg-gray-900 text-white rounded-2xl shadow-xl p-6 sm:p-8 mb-6 border border-gray-700 scroll-mt-6">
                <h2 class="text-base font-bold text-amber-400 mb-1 flex items-center gap-2">
                    <svg class="w-4 h-4 inline-block text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg> Panel Operator Developer: Konfirmasi Pencairan Pelunasan dari Mitra Pintar (Langkah 14)
                </h2>
                <p class="text-xs text-gray-400 mb-6 pb-4 border-b border-gray-800">
                    Apabila dana pelunasan dari Lembaga Mitra Pintar telah masuk ke rekening bank pengembang, silakan konfirmasi di bawah ini agar status SPR menjadi <strong>Lunas 100%</strong> dan berlanjut ke tahap Penjadwalan Serah Terima (BAST).
                </p>

                <form action="{{ route('spr.confirm-mitra-disbursement', $spr->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-gray-300 font-semibold mb-1">Nominal Pencairan Masuk (Rp) <span class="text-rose-400">*</span></label>
                            <input type="number" name="disbursement_amount" value="{{ old('disbursement_amount', (int) ($sisaPembayaran > 0 ? $sisaPembayaran : $spr->unit_price)) }}" class="w-full bg-gray-800 border border-gray-700 rounded-lg p-2.5 text-xs text-white font-bold focus:outline-none focus:ring-2 focus:ring-amber-500" required>
                        </div>
                        <div>
                            <label class="block text-gray-300 font-semibold mb-1">Tanggal Dana Masuk <span class="text-rose-400">*</span></label>
                            <input type="date" name="transfer_date" value="{{ old('transfer_date', now()->toDateString()) }}" class="w-full bg-gray-800 border border-gray-700 rounded-lg p-2.5 text-xs text-white focus:outline-none focus:ring-2 focus:ring-amber-500" required>
                        </div>
                        <div>
                            <label class="block text-gray-300 font-semibold mb-1">Rekening Pengembang Penerima</label>
                            <select name="developer_bank_account_id" class="w-full bg-gray-800 border border-gray-700 rounded-lg p-2.5 text-xs text-white focus:outline-none focus:ring-2 focus:ring-amber-500">
                                <option value="">-- Pilih Rekening Developer --</option>
                                @foreach($activeBankAccounts as $bacc)
                                    <option value="{{ $bacc->id }}">{{ $bacc->bank_name }} - {{ $bacc->account_number }} (a.n. {{ $bacc->account_holder_name }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-gray-300 font-semibold mb-1">Nama Instansi / Mitra Pengirim</label>
                            <input type="text" name="sender_name" value="{{ old('sender_name', $spr->unitSelection?->mitra_institution_name ?: 'Lembaga Mitra Pintar') }}" placeholder="Contoh: PT Mitra Pintar / Koperasi Instansi" class="w-full bg-gray-800 border border-gray-700 rounded-lg p-2.5 text-xs text-white">
                        </div>
                        <div>
                            <label class="block text-gray-300 font-semibold mb-1">No. Referensi / Bukti Transfer Bank</label>
                            <input type="text" name="reference_number" value="{{ old('reference_number') }}" placeholder="Contoh: TRF-MITRA-001" class="w-full bg-gray-800 border border-gray-700 rounded-lg p-2.5 text-xs text-white">
                        </div>
                        <div>
                            <label class="block text-gray-300 font-semibold mb-1">Upload Bukti Pencairan (Opsional)</label>
                            <input type="file" name="proof_file" accept=".pdf,image/*" class="w-full bg-gray-800 border border-gray-700 rounded-lg p-2 text-xs text-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-300 font-semibold mb-1">Catatan Verifikasi Pencairan</label>
                        <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Contoh: Dana pelunasan 100% telah diterima di rekening perusahaan" class="w-full bg-gray-800 border border-gray-700 rounded-lg p-2.5 text-xs text-white">
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit" onclick="return confirm('Konfirmasi bahwa dana pelunasan dari Lembaga Mitra Pintar telah diterima dan tandai SPR ini LUNAS 100%?')" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-lg transition inline-flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>Konfirmasi Pencairan Masuk & Tandai SPR Lunas (100%)</span>
                        </button>
                    </div>
                </form>
            </div>
        @endif
    @endif

    <!-- ==================== TAHAP 15 & 16: PENJADWALAN SERAH TERIMA & BAST (MITRA PINTAR) ==================== -->
    @if($spr->payment_scheme === 'mitra_pintar' && $isPaid)
        <div id="bast-panel" class="bg-white rounded-2xl shadow-sm border-2 {{ $spr->bast_file ? 'border-emerald-400' : 'border-blue-200' }} p-6 sm:p-8 mb-6 scroll-mt-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <svg class="w-10 h-10 text-amber-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-gray-900">Penjadwalan Serah Terima Kunci & BAST (Tahap Akhir)</h2>
                        <p class="text-xs text-gray-500">Pengecekan fisik unit rumah, penyerahan kunci, dan penandatanganan Berita Acara Serah Terima (BAST).</p>
                    </div>
                </div>
                <div>
                    @if($spr->bast_file)
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-emerald-100 text-emerald-800 rounded-full text-xs font-bold border border-emerald-300">
                            <svg class="w-3.5 h-3.5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>SELESAI &middot; KUNCI & BAST DISERAHKAN</span>
                        </span>
                    @elseif($spr->bast_date)
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-blue-100 text-blue-800 rounded-full text-xs font-bold border border-blue-200">
                            <span><svg class="w-4 h-4 inline-block text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg> JADWAL SERAH TERIMA TERSEDIA</span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-amber-100 text-amber-800 rounded-full text-xs font-bold border border-amber-200">
                            <span><svg class="w-4 h-4 inline-block text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> MENUNGGU JADWAL SERAH TERIMA</span>
                        </span>
                    @endif
                </div>
            </div>

            @php
                $defaultBastLocation = 'Kantor Developer ' . ($company?->company_name ?? 'Pengembang') . ' / Unit Blok ' . ($unit?->block ?? '-') . ' No. ' . ($unit?->number ?? '-') . ' (' . ($project?->name ?? 'Kawasan Perumahan') . ')';
            @endphp

            @if($spr->bast_date)
                <div class="my-5 p-4 bg-blue-50/60 rounded-xl border border-blue-200 text-xs space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <span class="block text-blue-700/70 font-semibold">Hari & Tanggal Serah Terima</span>
                            <span class="font-extrabold text-sm text-blue-950">{{ \Carbon\Carbon::parse($spr->bast_date)->translatedFormat('l, d F Y') }}</span>
                            <span class="block text-blue-800 text-[11px] mt-0.5">Pukul: {{ \Carbon\Carbon::parse($spr->bast_date)->format('H:i') }} WIB</span>
                        </div>
                        <div>
                            <span class="block text-blue-700/70 font-semibold">Lokasi Penyerahan Kunci</span>
                            <span class="font-extrabold text-sm text-blue-950">{{ $spr->bast_location ?: $defaultBastLocation }}</span>
                            <span class="block text-blue-800 text-[11px] mt-0.5">{{ $company?->company_name ?? 'Developer' }} &middot; {{ $project?->name ?? 'Kawasan Perumahan' }}</span>
                        </div>
                        <div>
                            <span class="block text-blue-700/70 font-semibold">Dokumen BAST Resmi</span>
                            @if($spr->bast_file)
                                <a href="{{ asset('storage/' . $spr->bast_file) }}" target="_blank" class="inline-flex items-center gap-1.5 font-bold text-emerald-700 hover:underline mt-1">
                                    <svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg> Unduh Dokumen BAST Bertandatangan
                                </a>
                            @else
                                <span class="text-gray-500 block mt-1">Ditandatangani saat cek fisik di lokasi</span>
                            @endif
                        </div>
                    </div>
                    @if($spr->bast_notes)
                        <div class="pt-3 border-t border-blue-200/80 text-blue-950">
                            <span class="font-bold text-blue-800">Catatan Serah Terima / Pengecekan Fisik dari Developer:</span>
                            <p class="mt-0.5 text-slate-700">{{ $spr->bast_notes }}</p>
                        </div>
                    @endif
                </div>
            @else
                <div class="my-5 p-4 bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-600">
                    <svg class="w-4 h-4 inline-block text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Pembayaran unit rumah telah <strong>Lunas 100%</strong>. Tim teknik & pengembang sedang melakukan persiapan akhir unit sebelum menetapkan jadwal pengecekan fisik bersama dan penyerahan kunci.
                </div>
            @endif

            @if(Auth::check() && Auth::user()->isAdmin())
                <div class="mt-5 pt-5 border-t border-gray-200 bg-gray-900 text-white rounded-xl p-5 text-xs">
                    <h3 class="font-bold text-sm text-blue-400 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 inline-block text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg> Panel Operator: Jadwalkan Serah Terima & Upload BAST (Langkah 15 & 16)
                    </h3>
                    <form action="{{ route('spr.bast', $spr->id) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-gray-300 font-semibold mb-1">Tanggal & Waktu Serah Terima Kunci <span class="text-rose-400">*</span></label>
                                <input type="datetime-local" name="bast_date" value="{{ old('bast_date', $spr->bast_date ? $spr->bast_date->format('Y-m-d\TH:i') : now()->addDays(3)->format('Y-m-d\T09:00')) }}" class="w-full bg-gray-800 border border-gray-700 rounded-lg p-2.5 text-xs text-white" required>
                            </div>
                            <div>
                                <label class="block text-gray-300 font-semibold mb-1">Lokasi Penyerahan Kunci <span class="text-rose-400">*</span></label>
                                <input type="text" name="bast_location" value="{{ old('bast_location', $spr->bast_location ?: $defaultBastLocation) }}" placeholder="Contoh: Kantor Pemasaran Developer / Lokasi Unit" class="w-full bg-gray-800 border border-gray-700 rounded-lg p-2.5 text-xs text-white" required>
                            </div>
                            <div>
                                <label class="block text-gray-300 font-semibold mb-1">Upload File BAST Bertandatangan (Jika Selesai)</label>
                                <input type="file" name="bast_file" accept=".pdf,image/*" class="w-full bg-gray-800 border border-gray-700 rounded-lg p-2 text-xs text-white">
                            </div>
                        </div>
                        <div>
                            <label class="block text-gray-300 font-semibold mb-1">Catatan Serah Terima / Pengecekan Fisik</label>
                            <input type="text" name="bast_notes" value="{{ old('bast_notes', $spr->bast_notes) }}" placeholder="Contoh: Kunci utama, kunci cadangan, dan meteran listrik/air telah diserahkan lengkap" class="w-full bg-gray-800 border border-gray-700 rounded-lg p-2.5 text-xs text-white">
                        </div>
                        <div class="flex justify-end pt-1">
                            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold transition">
                                Simpan Jadwal / Selesaikan BAST
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    @endif

    <!-- Download & Actions Bar -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-2 flex-wrap">
            @if(!Auth::user()->isAdmin())
            @if($spr->payment_scheme === 'kpr')
                {{-- KHUSUS SKEMA KPR --}}
                @if($spr->down_payment > 0 && $totalSudahDibayar < $spr->down_payment && !$pendingPayment)
                    {{-- Ada kewajiban DP yang belum lunas --}}
                    <a href="{{ route('kpr-payment.create', $spr->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        <span>Bayar Uang Muka (DP) Rp {{ number_format($spr->down_payment, 0, ',', '.') }}</span>
                    </a>
                @elseif($pendingPayment)
                    <span class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-50 text-amber-800 rounded-xl text-xs font-bold border border-amber-300">
                        <svg class="w-4 h-4 animate-spin text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Bukti Pembayaran DP Sedang Diverifikasi Admin</span>
                    </span>
                @elseif($spr->isSp3kApproved())
                    <span class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-50 text-emerald-800 rounded-xl text-xs font-bold border border-emerald-300">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>KPR Disetujui (SP3K ACC) &middot; Siap Akad Kredit</span>
                    </span>
                @else
                    {{-- DP Rp 0 (Bebas DP) atau DP sudah lunas, dalam proses Argo Pengurusan Bank --}}
                    <span class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-50 text-blue-800 rounded-xl text-xs font-bold border border-blue-200">
                        <svg class="w-4 h-4 text-blue-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Pengurusan KPR Bank Berjalan (Bebas DP)</span>
                    </span>
                @endif
            @elseif($spr->payment_scheme === 'mitra_pintar')
                {{-- KHUSUS SKEMA MITRA PINTAR (BEBAS DP) --}}
                @if($isPaid)
                    <span class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-50 text-emerald-800 rounded-xl text-xs font-bold border border-emerald-300">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Pelunasan Mitra Pintar Diterima (Lunas 100%)</span>
                    </span>
                @else
                    <span class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-50 text-amber-800 rounded-xl text-xs font-bold border border-amber-300">
                        <svg class="w-4 h-4 text-amber-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Menunggu Pencairan Dana dari Lembaga Mitra Pintar</span>
                    </span>
                @endif
            @else
                {{-- SKEMA NON-KPR (CASH / IN-HOUSE / INSTALLMENT) --}}
                @if(!$isPaid && !$pendingPayment)
                    <a href="{{ route('kpr-payment.create', $spr->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        <span>Bayar Uang Muka / Angsuran</span>
                    </a>
                @elseif($pendingPayment)
                    <span class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-50 text-amber-800 rounded-xl text-xs font-bold border border-amber-300">
                        <svg class="w-4 h-4 animate-spin text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Bukti Pembayaran Sedang Diverifikasi Admin</span>
                    </span>
                @endif
            @endif
            @else
                {{-- MODE OPERATOR / PENGEMBANG --}}
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 text-slate-700 rounded-xl text-xs font-semibold">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Pemohon: <strong>{{ $spr->user->name ?? ($spr->buyer_name ?? 'Konsumen') }}</strong></span>
                </span>
                @if($spr->down_payment > 0 && !$spr->isDpPaid())
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 text-amber-800 rounded-xl text-xs font-semibold border border-amber-200">
                        <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Menunggu Pembayaran DP Konsumen (Rp {{ number_format($spr->down_payment, 0, ',', '.') }})</span>
                    </span>
                @endif
                @if($pendingPayment)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-800 rounded-xl text-xs font-semibold border border-blue-200">
                        <svg class="w-3.5 h-3.5 text-blue-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Ada Bukti Pembayaran Konsumen Menunggu Verifikasi</span>
                    </span>
                @endif
            @endif
            @if($spr->status === 'issued' && $spr->document_file)
                <a href="{{ asset('storage/' . $spr->document_file) }}" target="_blank" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Download PDF SPR Resmi
                </a>
            @endif
            @if($spr->isIssued() || $spr->isFullyPaid() || (Auth::check() && Auth::user()->isAdmin()))
                <a href="{{ route('spr.pdf', $spr->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Cetak / Preview PDF
                </a>
            @else
                <span class="inline-flex items-center gap-2 px-4 py-2 bg-slate-50 text-slate-500 rounded-xl text-xs font-medium border border-slate-200">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    PDF SPR Resmi dapat diunduh setelah disetujui Admin
                </span>
            @endif
        </div>

        @if(Auth::user()->isAdmin() && $spr->status === 'draft')
            <form action="{{ route('spr.issue', $spr->id) }}" method="POST">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Terbitkan Dokumen SPR
                </button>
            </form>
        @endif
    </div>
</div>

<!-- Modal Konfirmasi Pembatalan Pemesanan Unit & Konsekuensi Dana Hangus -->
<div id="cancelSprModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-200 overflow-hidden transform transition-all animate-in fade-in zoom-in-95 duration-200">
        <!-- Header Modal -->
        <div class="p-6 bg-gradient-to-r from-rose-50 to-orange-50 border-b border-rose-100 flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 border border-rose-200 shadow-xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-extrabold text-slate-900 tracking-tight">Konfirmasi Pembatalan Pemesanan</h3>
                <p class="text-xs text-rose-700 font-medium mt-0.5">Tindakan ini bersifat permanen dan tidak dapat dibatalkan kembali.</p>
            </div>
        </div>

        <!-- Body Modal: Konsekuensi & Peringatan -->
        <div class="p-6 space-y-4">
            <div class="p-4 bg-rose-50/70 border border-rose-200 rounded-xl space-y-3">
                <p class="text-xs font-bold text-rose-950 uppercase tracking-wider flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-rose-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    Konsekuensi Pembatalan:
                </p>
                <div class="space-y-2 text-xs text-rose-900">
                    <div class="flex items-start gap-2">
                        <span class="font-bold text-rose-600 shrink-0">1.</span>
                        <p><strong class="text-rose-950">Seluruh Biaya Dinyatakan HANGUS (Non-Refundable):</strong> Baik uang pendaftaran NUP maupun Uang Tanda Jadi (UTK) yang telah dibayarkan TIDAK dapat dikembalikan dengan alasan apapun.</p>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="font-bold text-rose-600 shrink-0">2.</span>
                        <p><strong class="text-rose-950">Pelepasan Unit Rumah:</strong> Hak atas unit kavling <strong>{{ $spr->unitSelection?->housingUnit?->full_identifier ?? 'Unit' }}</strong> akan segera dicabut dan dilepas kembali ke publik/antrian berikutnya.</p>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="font-bold text-rose-600 shrink-0">3.</span>
                        <p><strong class="text-rose-950">Tiket NUP Ditutup:</strong> Tiket antrean NUP Anda untuk transaksi ini dinyatakan selesai dan tidak dapat digunakan lagi untuk memilih unit lain.</p>
                    </div>
                </div>
            </div>

            <!-- Form Pembatalan -->
            <form id="formCancelSpr" action="{{ route('spr.rescue-switch', $spr->id) }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="action" value="cancel">

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Alasan Pembatalan (Opsional):</label>
                    <textarea name="reason" rows="2" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-rose-500 focus:border-rose-500" placeholder="Contoh: KPR belum disetujui dan tidak ingin melanjutkan ke skema in-house/tunai."></textarea>
                </div>

                <!-- Checkbox Persetujuan Konsekuensi -->
                <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl">
                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="checkbox" id="agreeConsequencesCheckbox" onchange="document.getElementById('btnSubmitCancelSpr').disabled = !this.checked; if(this.checked){ document.getElementById('btnSubmitCancelSpr').classList.remove('opacity-50','cursor-not-allowed'); } else { document.getElementById('btnSubmitCancelSpr').classList.add('opacity-50','cursor-not-allowed'); }" class="mt-0.5 w-4 h-4 text-rose-600 rounded border-slate-300 focus:ring-rose-500">
                        <span class="text-xs text-slate-700 font-medium leading-relaxed">
                            Saya telah membaca, memahami, dan <strong>menyetujui seluruh konsekuensi</strong> di atas, termasuk bahwa uang pendaftaran NUP dan UTK yang telah dibayarkan dinyatakan <strong>HANGUS</strong>.
                        </span>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('cancelSprModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition">
                        Batal / Kembali
                    </button>
                    <button type="submit" id="btnSubmitCancelSpr" disabled class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-md transition opacity-50 cursor-not-allowed flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Ya, Batalkan Pemesanan Unit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
