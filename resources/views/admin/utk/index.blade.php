@extends('layouts.admin')

@section('title', 'Admin - Pembayaran UTK')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Admin - Pembayaran UTK</h1>
            <p class="text-sm text-slate-500 mt-0.5">Kelola dan verifikasi pembayaran Uang Tanda Jadi (UTK) pemesanan unit perumahan.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 border border-slate-200/80 text-xs font-semibold text-slate-700 shadow-sm">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Total: {{ $utkPayments->total() }} Transaksi
            </span>
        </div>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm rounded-2xl p-4 flex items-center gap-2.5 shadow-sm">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-800 text-sm rounded-2xl p-4 flex items-center gap-2.5 shadow-sm">
            <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Container Utama -->
    <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <!-- Subheader Box -->
        <div class="px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 bg-slate-50/50">
            <div>
                <h2 class="font-bold text-slate-800 text-base">Daftar Tagihan & Pembayaran UTK</h2>
                <p class="text-xs text-slate-500">Tinjau status transfer bank, pembayaran tunai kantor, atau gerbang pembayaran online.</p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 bg-white border border-slate-200 rounded-lg text-slate-600 self-start sm:self-auto">
                Halaman {{ $utkPayments->currentPage() }} dari {{ $utkPayments->lastPage() }}
            </span>
        </div>

        <!-- Grid Transaksi -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 p-5">
            @forelse($utkPayments as $utkPayment)
                <article class="bg-white rounded-2xl border border-slate-200 hover:border-blue-300 hover:shadow-md transition-all duration-200 flex flex-col justify-between overflow-hidden">
                    
                    {{-- 1. Header Card --}}
                    <div class="p-4 sm:p-5 bg-gradient-to-r from-slate-50 to-white border-b border-slate-100 flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-0.5">Nomor Pembayaran</span>
                            <p class="font-mono font-bold text-slate-900 text-base sm:text-lg tracking-tight">{{ $utkPayment->payment_number }}</p>
                            <div class="flex items-center gap-1.5 mt-1 text-xs text-slate-600">
                                <span class="w-5 h-5 rounded-full bg-slate-200/80 text-slate-600 flex items-center justify-center font-bold text-[10px] shrink-0">
                                    {{ strtoupper(substr($utkPayment->user->name ?? 'U', 0, 1)) }}
                                </span>
                                <span class="font-medium text-slate-800 truncate">{{ $utkPayment->user->name ?? 'User' }}</span>
                            </div>
                        </div>

                        {{-- Badge Status --}}
                        <div class="shrink-0">
                            @if($utkPayment->status === 'paid')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-sm">
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    LUNAS
                                </span>
                            @elseif($utkPayment->unitSelection?->status === 'cancelled' || $utkPayment->status === 'cancelled')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    DIBATALKAN
                                </span>
                            @elseif($utkPayment->status === 'pending' && $utkPayment->payment_method === 'cash')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    KONFIRMASI TUNAI
                                </span>
                            @elseif($utkPayment->status === 'pending')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    MENUNGGU TRANSFER
                                </span>
                            @elseif($utkPayment->status === 'failed')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    GAGAL
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ strtoupper($utkPayment->status) }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- 2. Detail Transaksi (Grid Terstruktur) --}}
                    <div class="p-4 sm:p-5 space-y-3.5 flex-1">
                        <!-- Baris 1: Unit & Nominal -->
                        <div class="grid grid-cols-2 gap-3">
                            <!-- Unit Pilihan -->
                            <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-100">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Unit Pilihan</span>
                                <div class="flex items-start gap-1.5">
                                    <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                    <div class="min-w-0">
                                        <p class="text-xs sm:text-sm font-bold text-slate-900 truncate">
                                            {{ $utkPayment->unitSelection->housingUnit->full_identifier ?? '-' }}
                                        </p>
                                        <p class="text-[11px] text-slate-500 truncate">
                                            {{ $utkPayment->unitSelection->housingUnit->housingProject->name ?? 'Perumahan' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Biaya UTK -->
                            <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-100">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Biaya UTK</span>
                                <p class="text-xs sm:text-base font-extrabold text-slate-900">
                                    Rp {{ number_format($utkPayment->amount, 0, ',', '.') }}
                                </p>
                                @if(isset($utkPayment->metadata['promo_code']))
                                    <p class="text-[11px] text-emerald-700 font-semibold flex items-center gap-1 mt-0.5 truncate">
                                        <svg class="w-3 h-3 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                                        <span>{{ $utkPayment->metadata['promo_code'] }}</span>
                                        <span class="text-slate-400 font-normal line-through text-[10px]">(Rp {{ number_format($utkPayment->metadata['original_amount'], 0, ',', '.') }})</span>
                                    </p>
                                @endif
                            </div>
                        </div>

                        <!-- Baris 2: Metode Pembayaran -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 p-2.5 bg-slate-50/50 rounded-xl border border-slate-100 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="text-[11px] font-semibold text-slate-400">Metode:</span>
                                @if($utkPayment->payment_method === 'cash')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                        <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                        Tunai Kantor
                                    </span>
                                @elseif($utkPayment->proof_of_transfer || $utkPayment->payment_method === 'bank_transfer')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-semibold bg-blue-50 text-blue-800 border border-blue-200">
                                        <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M3 10h18M5 6l7-3 7 3M4 10v11M20 10v11M8 14v4M12 14v4M16 14v4"/></svg>
                                        Transfer Rekening PT
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-semibold bg-indigo-50 text-indigo-800 border border-indigo-200">
                                        <svg class="w-3.5 h-3.5 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                                        DOKU Gateway
                                    </span>
                                @endif
                            </div>

                            @if($utkPayment->developerBankAccount)
                                <div class="text-[11px] text-slate-500 sm:text-right">
                                    Tujuan: <strong class="text-slate-700">{{ $utkPayment->developerBankAccount->bank_name }}</strong> ({{ $utkPayment->developerBankAccount->account_number }})
                                </div>
                            @endif
                        </div>

                        <!-- Bukti Transfer Box (Jika Ada) -->
                        @if($utkPayment->proof_of_transfer)
                            <div class="p-3 bg-blue-50/70 border border-blue-200/80 rounded-xl flex items-center justify-between gap-3 text-xs">
                                <div class="min-w-0">
                                    <span class="font-bold text-blue-900 flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        Bukti Transfer Pengirim:
                                    </span>
                                    <p class="text-slate-700 mt-0.5 truncate">
                                        Bank {{ $utkPayment->sender_bank ?? '-' }} a.n. <strong>{{ $utkPayment->sender_name ?? '-' }}</strong>
                                    </p>
                                    @if($utkPayment->transfer_date)
                                        <span class="text-slate-500 block text-[11px] mt-0.5">
                                            Tgl Transfer: {{ \Carbon\Carbon::parse($utkPayment->transfer_date)->format('d-m-Y') }}
                                        </span>
                                    @endif
                                </div>
                                <a href="{{ asset('storage/' . $utkPayment->proof_of_transfer) }}" target="_blank" class="px-3 py-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white rounded-xl text-xs font-semibold transition flex items-center gap-1.5 shrink-0 shadow-sm">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    <span>Lihat Bukti Struk</span>
                                </a>
                            </div>
                        @endif

                        <!-- Timestamps Info -->
                        <div class="text-[11px] text-slate-400 flex items-center gap-1 pt-1">
                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Pengajuan: {{ $utkPayment->created_at->format('d M Y, H:i') }}</span>
                            @if($utkPayment->payment_date)
                                <span class="mx-1">•</span>
                                <span>Selesai: {{ $utkPayment->payment_date->format('d M Y, H:i') }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- 3. Action Footer --}}
                    <div class="border-t border-slate-100 bg-slate-50/40 p-4 sm:px-5">
                        @if($utkPayment->unitSelection?->status === 'cancelled' || $utkPayment->status === 'cancelled')
                            <div class="flex items-center text-xs text-rose-700 font-semibold gap-2 bg-rose-50 border border-rose-200 p-3 rounded-xl">
                                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                <div>
                                    <p class="font-bold">Pemilihan unit ini telah dibatalkan oleh pembeli.</p>
                                    <p class="text-[11px] font-normal text-rose-600 mt-0.5">Tagihan UTK otomatis dibatalkan / hangus.</p>
                                </div>
                            </div>
                        @elseif($utkPayment->status === 'pending' && $utkPayment->proof_of_transfer)
                            {{-- Pending Transfer dengan Bukti --}}
                            <div class="mb-3 flex items-start gap-2 rounded-xl bg-blue-50/80 border border-blue-200/80 p-3 text-xs text-blue-900">
                                <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <p>Pastikan dana transfer sebesar <strong>Rp {{ number_format($utkPayment->amount, 0, ',', '.') }}</strong> sudah masuk di mutasi rekening bank sebelum menyetujui.</p>
                            </div>
                            <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2">
                                <form action="{{ route('utk.confirm-transfer', $utkPayment->id) }}" method="POST" onsubmit="return confirm('Konfirmasi bahwa dana transfer UTK sebesar Rp {{ number_format($utkPayment->amount, 0, ',', '.') }} dari {{ $utkPayment->sender_name }} SUDAH MASUK di rekening bank?')">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center justify-center gap-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        <span>Setujui Transfer</span>
                                    </button>
                                </form>
                                <form action="{{ route('utk.reject-transfer', $utkPayment->id) }}" method="POST" class="flex min-w-0 gap-2" onsubmit="return confirm('Tolak bukti transfer ini?')">
                                    @csrf
                                    <input type="text" name="reason" maxlength="255" placeholder="Alasan penolakan..." class="min-w-0 flex-1 rounded-xl border border-slate-300 px-3 py-2 text-xs placeholder:text-slate-400 focus:border-rose-500 focus:ring-1 focus:ring-rose-500 focus:outline-none">
                                    <button type="submit" class="shrink-0 rounded-xl bg-rose-600 hover:bg-rose-700 active:bg-rose-800 px-4 py-2 text-xs font-bold text-white transition shadow-sm">
                                        Tolak
                                    </button>
                                </form>
                            </div>
                        @elseif($utkPayment->payment_method === 'cash' && $utkPayment->status === 'pending')
                            <div class="mb-3 flex items-start gap-2 rounded-xl bg-amber-50 border border-amber-200/80 p-3 text-xs text-amber-900">
                                <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <p>Pastikan uang tunai sebesar <strong>Rp {{ number_format($utkPayment->amount, 0, ',', '.') }}</strong> sudah diterima di kantor sebelum menyetujui.</p>
                            </div>
                            <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2">
                                <form action="{{ route('utk.confirm-cash', $utkPayment->id) }}" method="POST" onsubmit="return confirm('Konfirmasi bahwa uang tunai sebesar Rp {{ number_format($utkPayment->amount, 0, ',', '.') }} dari {{ $utkPayment->user->name }} SUDAH DITERIMA di kantor?')">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center justify-center gap-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        <span>Setujui Pembayaran</span>
                                    </button>
                                </form>
                                <form action="{{ route('utk.reject-cash', $utkPayment->id) }}" method="POST" class="flex min-w-0 gap-2" onsubmit="return confirm('Tolak pembayaran tunai ini?')">
                                    @csrf
                                    <input type="text" name="reason" maxlength="255" placeholder="Alasan penolakan..." class="min-w-0 flex-1 rounded-xl border border-slate-300 px-3 py-2 text-xs placeholder:text-slate-400 focus:border-rose-500 focus:ring-1 focus:ring-rose-500 focus:outline-none">
                                    <button type="submit" class="shrink-0 rounded-xl bg-rose-600 hover:bg-rose-700 active:bg-rose-800 px-4 py-2 text-xs font-bold text-white transition shadow-sm">
                                        Tolak
                                    </button>
                                </form>
                            </div>
                        @elseif($utkPayment->status === 'paid')
                            <div class="flex items-center gap-2 text-xs font-semibold text-emerald-700 bg-emerald-50/70 border border-emerald-200/80 px-3 py-2.5 rounded-xl">
                                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span>Pembayaran telah terkonfirmasi lunas.</span>
                            </div>
                        @else
                            <div class="text-xs text-slate-500 flex items-center gap-2 px-1">
                                @if($utkPayment->status === 'failed')
                                    <span class="text-rose-600 font-semibold flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        Pembayaran gagal atau dibatalkan.
                                    </span>
                                @else
                                    <span class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Menunggu pembayaran otomatis melalui gateway.
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-slate-300 p-12 text-center bg-slate-50/50">
                    <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                    <p class="font-bold text-slate-700 text-sm">Belum ada transaksi UTK</p>
                    <p class="mt-1 text-xs text-slate-400">Data pembayaran UTK dari peserta/konsumen akan tampil di sini.</p>
                </div>
            @endforelse
        </div>

        @if($utkPayments->hasPages())
            <div class="border-t border-slate-100 p-4 bg-slate-50/50">
                {{ $utkPayments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
