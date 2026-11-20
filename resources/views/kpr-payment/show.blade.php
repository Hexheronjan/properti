@extends('layouts.app')

@section('title', 'Detail Pembayaran')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Banner khusus Admin: arahkan ke halaman admin untuk approve/reject --}}
    @if(auth()->user()->isAdmin())
    <div class="bg-blue-600 rounded-2xl p-4 flex items-center justify-between gap-4 text-white shadow-xs">
        <div>
            <p class="font-bold text-sm">Mode Administrator</p>
            <p class="text-blue-100 text-xs mt-0.5">Gunakan halaman Admin Panel untuk memverifikasi atau menyetujui bukti transfer ini.</p>
        </div>
        <a href="{{ route('admin.kpr-payments.show', $payment->id) }}"
           class="flex-shrink-0 inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-white text-blue-700 font-semibold rounded-lg hover:bg-blue-50 transition text-xs shadow-2xs">
            Buka di Admin Panel &rarr;
        </a>
    </div>
    @endif

    <div>
        <a href="{{ route('kpr-payment.list') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-blue-600 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Kembali ke Pembayaran Rumah
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        {{-- Header --}}
        @php
            $statusBadgeClass = match($payment->status) {
                'success' => 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
                'failed'  => 'bg-rose-50 text-rose-700 border-rose-200/80',
                default   => 'bg-amber-50 text-amber-700 border-amber-200/80',
            };
            $statusLabel = match($payment->status) {
                'success' => 'Lunas',
                'failed'  => 'Ditolak / Gagal',
                'pending' => 'Menunggu Verifikasi',
                default   => ucfirst($payment->status),
            };
        @endphp
        <div class="px-6 py-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white">
            <div>
                <span class="text-xs text-slate-400 font-mono">No. Pembayaran: {{ $payment->payment_number }}</span>
                <h1 class="font-bold text-lg text-slate-900 mt-0.5">
                    {{ $payment->payment_type === 'installment' ? 'Cicilan Termin ke-' . $payment->installment_number : 'Pelunasan Unit' }}
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Metode: {{ ucfirst($payment->payment_method) }}</p>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border {{ $statusBadgeClass }} self-start sm:self-auto">
                <span class="w-1.5 h-1.5 rounded-full {{ $payment->status === 'success' ? 'bg-emerald-500' : ($payment->status === 'failed' ? 'bg-rose-500' : 'bg-amber-500') }}"></span>
                {{ $statusLabel }}
            </span>
        </div>

        <div class="p-6">
            {{-- Flash messages --}}
            @if(session('success'))
                <div class="mb-4 bg-emerald-50 border border-emerald-200/80 rounded-xl p-4 text-emerald-800 text-xs font-semibold flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-rose-50 border border-rose-200/80 rounded-xl p-4 text-rose-800 text-xs font-semibold flex items-center gap-2">
                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                    {{ session('error') }}
                </div>
            @endif

            {{-- Detail grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                <div class="bg-slate-50/70 border border-slate-200/80 rounded-xl p-4">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-3">Informasi Pembayaran</p>
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-500">No. Pembayaran</span>
                            <span class="font-semibold text-slate-800 font-mono">{{ $payment->payment_number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Tanggal Transaksi</span>
                            <span class="font-semibold text-slate-800">
                                {{ $payment->payment_date ? $payment->payment_date->format('d M Y H:i') : ($payment->created_at->format('d M Y H:i')) }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Jenis Pembayaran</span>
                            <span class="font-semibold text-slate-800">
                                {{ $payment->payment_type === 'installment' ? 'Cicilan Termin ke-' . $payment->installment_number : 'Pelunasan Unit' }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Metode</span>
                            <span class="font-semibold text-slate-800">{{ ucfirst($payment->payment_method) }}</span>
                        </div>
                        @if($payment->proof_of_transfer)
                        <div class="flex justify-between items-center pt-2 border-t border-slate-200">
                            <span class="text-slate-500">Bukti Transfer</span>
                            <a href="{{ asset('storage/' . $payment->proof_of_transfer) }}" target="_blank" class="text-blue-600 hover:text-blue-800 font-semibold underline">
                                Lihat Berkas &rarr;
                            </a>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="bg-slate-50/70 border border-slate-200/80 rounded-xl p-4">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-3">Rincian Finansial</p>
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500">Jumlah Dibayar</span>
                            <span class="font-bold text-slate-900 text-sm">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                        </div>
                        @if($payment->principal_amount)
                        <div class="flex justify-between">
                            <span class="text-slate-500">Alokasi Pokok</span>
                            <span class="font-medium text-slate-700">Rp {{ number_format($payment->principal_amount, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        @if($payment->interest_amount)
                        <div class="flex justify-between">
                            <span class="text-slate-500">Alokasi Bunga / Lainnya</span>
                            <span class="font-medium text-slate-700">Rp {{ number_format($payment->interest_amount, 0, ',', '.') }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            @if($payment->notes)
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 mb-6">
                <p class="text-[10px] text-slate-400 uppercase font-bold mb-1">Catatan</p>
                <p class="text-xs text-slate-700">{{ $payment->notes }}</p>
            </div>
            @endif

            {{-- Status info --}}
            @if($payment->status === 'pending')
            <div class="bg-amber-50/70 border border-amber-200/80 rounded-xl p-4 mb-6 flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div>
                    <h3 class="font-bold text-amber-900 text-xs uppercase tracking-wider mb-0.5">Menunggu Verifikasi Keuangan</h3>
                    <p class="text-xs text-amber-800 leading-relaxed">Bukti transfer Anda telah tersimpan dan sedang dalam antrean verifikasi oleh tim keuangan pengembang.</p>
                </div>
            </div>
            @endif

            @if($payment->status === 'failed')
            <div class="bg-rose-50 border border-rose-200/80 rounded-xl p-4 mb-6 flex items-start gap-3">
                <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                <div>
                    <h3 class="font-bold text-rose-900 text-xs uppercase tracking-wider mb-0.5">Pembayaran Ditolak</h3>
                    <p class="text-xs text-rose-800 leading-relaxed">Pembayaran ditolak atau gagal diverifikasi. Silakan hubungi bagian administrasi keuangan pengembang untuk informasi lebih lanjut.</p>
                </div>
            </div>
            @endif

            {{-- Action buttons --}}
            <div class="flex items-center gap-2.5 pt-2 border-t border-slate-100">
                <a href="{{ route('kpr-payment.list') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-semibold text-xs shadow-xs">
                    Kembali ke Daftar Pembayaran
                </a>
                @if($payment->status === 'pending')
                <a href="{{ route('kpr-payment.create', $payment->spr_document_id) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-xl hover:bg-slate-50 transition font-semibold text-xs shadow-2xs">
                    + Buat Pembayaran Baru
                </a>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
