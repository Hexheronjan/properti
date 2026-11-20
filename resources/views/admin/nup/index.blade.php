@extends('layouts.admin')

@section('title', 'Admin - Registrasi NUP')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                @if(Auth::user()->isSuperAdmin())
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                        Super Admin &bull; Seluruh Pengembang
                    </span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        {{ Auth::user()->company->company_name ?? 'Mitra Pengembang' }}
                    </span>
                @endif
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight mt-1">
                Manajemen Dokumen Pendaftar NUP
            </h1>
            <p class="text-xs sm:text-sm text-slate-500">
                @if(Auth::user()->isSuperAdmin())
                    Kelola dan pantau seluruh pendaftar NUP dari semua mitra developer.
                @else
                    Tinjau pendaftar NUP yang memilih unit perumahan Anda serta akses berkas dokumen konsumen.
                @endif
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg p-4 mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 mb-4">{{ session('error') }}</div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-col gap-2 border-b border-gray-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="font-semibold text-gray-900">Daftar Registrasi</h3>
                <p class="mt-1 text-sm text-gray-500">Tinjau status dan proses pembayaran tunai peserta.</p>
            </div>
            <span class="inline-flex w-fit items-center rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-gray-600">
                {{ $registrations->total() }} registrasi
            </span>
        </div>

        <div class="grid grid-cols-1 gap-4 p-4 lg:grid-cols-2">
            @forelse($registrations as $reg)
                @php($pendingCash = $reg->paymentTransactions->first())
                <article class="min-w-0 overflow-hidden rounded-xl border border-gray-200 bg-white transition hover:border-blue-200 hover:shadow-md">
                    <div class="flex flex-wrap items-start justify-between gap-3 border-b border-gray-100 bg-gray-50/70 px-4 py-4 sm:px-5">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor registrasi</p>
                            <p class="mt-1 break-all font-semibold text-gray-900">{{ $reg->registration_number }}</p>
                            <p class="mt-1 text-sm text-gray-600">{{ $reg->user->name }}</p>
                        </div>
                        <span class="shrink-0 rounded-full px-3 py-1 text-xs font-semibold uppercase
                            @if($reg->payment_status === 'paid') bg-green-100 text-green-800
                            @elseif($reg->payment_status === 'pending') bg-amber-100 text-amber-800
                            @else bg-red-100 text-red-800 @endif">
                            {{ $pendingCash && $reg->payment_status === 'pending' ? 'Menunggu konfirmasi' : $reg->payment_status }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 gap-4 px-4 py-4 sm:grid-cols-3 sm:px-5">
                        <div class="min-w-0">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Metode pembayaran</p>
                            <p class="mt-1 break-words text-sm text-gray-700">{{ config('nup.payment_methods.' . $reg->payment_method) }}</p>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Nomor antrean</p>
                            <p class="mt-1 break-all text-sm text-gray-700">{{ $reg->queue_number ?? 'Belum tersedia' }}</p>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Biaya NUP</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900">Rp {{ number_format($reg->nup_fee, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    @php($selectedUnit = $reg->unitSelections->first()?->housingUnit)
                    <div class="px-4 py-2.5 bg-slate-50 border-t border-gray-100 flex items-center justify-between text-xs">
                        <span class="text-gray-500 font-medium">Unit Kavling Terpilih:</span>
                        @if($selectedUnit)
                            <span class="font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-200">
                                {{ $selectedUnit->full_identifier }} &bull; {{ $selectedUnit->housingProject?->name ?? 'Perumahan' }}
                            </span>
                        @else
                            <span class="text-amber-600 font-medium bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                Belum Memilih Unit
                            </span>
                        @endif
                    </div>

                    <div class="border-t border-gray-100 px-4 py-4 sm:px-5">
                        @if($pendingCash && $reg->payment_status !== 'paid')
                            <div class="mb-3 flex items-start gap-2 rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-800">
                                <span class="mt-0.5" aria-hidden="true">!</span>
                                <p>Pastikan pembayaran tunai sudah diterima sebelum menyetujui.</p>
                            </div>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <form action="{{ route('admin.nup.confirm-cash', $pendingCash->id) }}" method="POST" onsubmit="return confirm('Konfirmasi uang tunai sudah diterima di kantor?')">
                                    @csrf
                                    <button type="submit" class="w-full rounded-lg bg-green-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">Setujui pembayaran</button>
                                </form>
                                <form action="{{ route('admin.nup.reject-cash', $pendingCash->id) }}" method="POST" class="flex min-w-0 flex-col gap-2 sm:flex-row" onsubmit="return confirm('Tolak pengajuan pembayaran ini? Peserta dapat membayar ulang.')">
                                    @csrf
                                    <input type="text" name="rejection_reason" maxlength="500" required aria-label="Alasan penolakan" placeholder="Alasan penolakan" class="min-w-0 flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm placeholder:text-gray-400 focus:border-red-400 focus:outline-none focus:ring-2 focus:ring-red-100">
                                    <button type="submit" class="shrink-0 rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-red-700 ring-1 ring-inset ring-red-200 transition hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-400">Tolak</button>
                                </form>
                            </div>
                        @else
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <p class="text-sm text-gray-500">Tidak ada pembayaran tunai yang menunggu konfirmasi.</p>
                                <a href="{{ route('admin.nup.show', $reg->id) }}" class="inline-flex w-fit items-center gap-1.5 justify-center rounded-lg border border-gray-300 px-3.5 py-1.5 text-xs font-semibold text-gray-700 transition hover:bg-blue-50 hover:text-blue-700 hover:border-blue-200 shadow-2xs">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Detail & Dokumen</span>
                                </a>
                            </div>
                        @endif
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-xl border border-dashed border-gray-300 px-6 py-12 text-center">
                    <p class="font-medium text-gray-700">Belum ada registrasi NUP</p>
                    <p class="mt-1 text-sm text-gray-500">Data registrasi akan tampil di sini.</p>
                </div>
            @endforelse
        </div>

        @if($registrations->hasPages())
            <div class="border-t border-gray-100 p-4">
                {{ $registrations->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
