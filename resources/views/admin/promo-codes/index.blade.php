@extends('layouts.admin')

@section('title', 'Kode Promo')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Kode Promo</h1>
        <p class="mt-1 text-sm text-gray-600">Atur kode promo khusus untuk pembayaran NUP dan UTK melalui DOKU.</p>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800"><svg class="w-4 h-4 inline-block text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> {{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            <p class="font-semibold">Periksa kembali data promo:</p>
            <ul class="mt-2 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
        <h2 class="font-semibold text-gray-900">Buat promo baru</h2>
        <p class="mt-1 text-sm text-gray-500">Buat kode diskon untuk pembayaran NUP (registrasi), UTK (tanda jadi), atau keduanya.</p>
        <form action="{{ route('admin.promo-codes.store') }}" method="POST" class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @csrf
            <label class="block text-sm font-medium text-gray-700">Kode promo
                <input type="text" name="code" value="{{ old('code') }}" required maxlength="50" placeholder="CONTOH: PROMO50" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 uppercase focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
            </label>
            <label class="block text-sm font-medium text-gray-700">Nominal akhir yang dibayar (Rp)
                <input type="number" name="payable_amount" value="{{ old('payable_amount', 0) }}" min="0" max="100000000" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
            </label>
            <label class="block text-sm font-medium text-gray-700">Berlaku untuk
                <select name="applicable_to" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    <option value="all" @selected(old('applicable_to', 'all') === 'all')>Semua (NUP & UTK)</option>
                    <option value="nup" @selected(old('applicable_to') === 'nup')>Hanya NUP</option>
                    <option value="utk" @selected(old('applicable_to') === 'utk')>Hanya UTK</option>
                </select>
            </label>
            <label class="block text-sm font-medium text-gray-700">Batas pemakaian
                <input type="number" name="usage_limit" value="{{ old('usage_limit', 1) }}" min="1" max="100000" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
            </label>
            <label class="block text-sm font-medium text-gray-700">Batasi untuk email (opsional)
                <input type="email" name="allowed_email" value="{{ old('allowed_email') }}" maxlength="255" placeholder="email akun peserta Anda" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
            </label>
            <label class="block text-sm font-medium text-gray-700">Berlaku sampai (opsional)
                <input type="datetime-local" name="expires_at" value="{{ old('expires_at') }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
            </label>
            <div class="col-span-full flex flex-col justify-end gap-3 sm:flex-row sm:items-center">
                <label class="inline-flex items-center gap-2 text-sm text-gray-700 font-medium">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"> Aktif
                </label>
                <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">Buat kode promo</button>
            </div>
        </form>
        <div class="mt-5 rounded-lg bg-amber-50 p-3 text-sm text-amber-800 flex items-start gap-2.5">
            <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <p>Nominal <strong>0</strong> membuat pembayaran langsung berstatus <strong>Lunas</strong> (bebas biaya 100%, sangat cocok untuk keperluan pengujian / testing simulasi).</p>
                <p class="text-xs text-amber-700 mt-1">Nominal berbayar di bawah Rp10.000 hanya menampilkan Virtual Account BRI dan BSI di DOKU.</p>
            </div>
        </div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
            <h2 class="font-semibold text-gray-900">Promo yang tersedia</h2>
            <span class="rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-600">{{ $promoCodes->total() }} kode</span>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($promoCodes as $promo)
                <form action="{{ route('admin.promo-codes.update', $promo) }}" method="POST" class="grid grid-cols-1 items-end gap-3 p-4 sm:p-5 lg:grid-cols-8">
                    @csrf
                    @method('PUT')
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Kode
                        <input type="text" name="code" value="{{ $promo->code }}" required maxlength="50" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm uppercase">
                    </label>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Bayar (Rp)
                        <input type="number" name="payable_amount" value="{{ $promo->payable_amount }}" min="0" max="100000000" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </label>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Berlaku untuk
                        <select name="applicable_to" class="mt-1 w-full rounded-lg border border-gray-300 px-2 py-2 text-sm font-medium">
                            <option value="all" @selected(($promo->applicable_to ?? 'all') === 'all')>Semua</option>
                            <option value="nup" @selected(($promo->applicable_to ?? '') === 'nup')>NUP Saja</option>
                            <option value="utk" @selected(($promo->applicable_to ?? '') === 'utk')>UTK Saja</option>
                        </select>
                    </label>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Kuota
                        <input type="number" name="usage_limit" value="{{ $promo->usage_limit }}" min="1" max="100000" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </label>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Khusus email
                        <input type="email" name="allowed_email" value="{{ $promo->allowed_email }}" maxlength="255" placeholder="Semua akun" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </label>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Berlaku sampai
                        <input type="datetime-local" name="expires_at" value="{{ $promo->expires_at?->format('Y-m-d\\TH:i') }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </label>
                    <div class="flex flex-col gap-1">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700 font-medium">
                            <input type="checkbox" name="is_active" value="1" @checked($promo->is_active) class="rounded border-gray-300 text-blue-600"> Aktif
                        </label>
                        <span class="text-xs text-gray-500">Terpakai {{ $promo->used_count }} / {{ $promo->usage_limit }}</span>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="submit" class="flex-1 rounded-lg bg-gray-900 px-3 py-2 text-sm font-semibold text-white hover:bg-gray-700">Simpan</button>
                        <button type="submit" form="delete-promo-{{ $promo->id }}" class="rounded-lg border border-red-200 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50" onclick="return confirm('Hapus kode promo {{ $promo->code }}?')">Hapus</button>
                    </div>
                </form>
                <form id="delete-promo-{{ $promo->id }}" action="{{ route('admin.promo-codes.destroy', $promo) }}" method="POST" class="hidden">@csrf @method('DELETE')</form>
            @empty
                <div class="px-5 py-10 text-center text-sm text-gray-500">Belum ada kode promo.</div>
            @endforelse
        </div>
        @if($promoCodes->hasPages())
            <div class="border-t border-gray-100 p-4">{{ $promoCodes->links() }}</div>
        @endif
    </section>
</div>
@endsection
