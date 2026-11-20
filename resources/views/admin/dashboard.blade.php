@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Executive Greeting & Action Bar (Menghilangkan Duplikasi Judul) -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-1">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Sistem Operasional Real-Time
                </span>
                <span class="text-xs text-slate-300">&bull;</span>
                <span class="text-xs font-medium text-slate-500">{{ now()->translatedFormat('l, d F Y') }}</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight mt-1.5">
                Selamat Datang, {{ Auth::user()->name }}
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Ringkasan performa penjualan unit kavling, verifikasi antrean NUP, dan arus kas pembayaran properti.
            </p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('admin.nup.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:border-slate-300 shadow-2xs transition">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span>Kelola NUP</span>
            </a>
            <a href="{{ route('admin.housing-units.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-blue-600 text-xs font-bold text-white hover:bg-blue-700 shadow-sm transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>Katalog Unit</span>
            </a>
        </div>
    </div>

    @if(Auth::user()->isSuperAdmin())
        <!-- Monitoring Mitra Pengembang (PT / CV) untuk Super Admin: Redesain Elegan & Harmonis -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-5 sm:p-6 transition hover:border-slate-300">
            <!-- Header Kartu -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-sm sm:text-base font-bold text-slate-900">Monitoring Mitra Pengembang Properti</h2>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 uppercase tracking-wider">Super Admin</span>
                        </div>
                        <p class="text-xs text-slate-500">Legalitas badan usaha developer (PT/CV) dan persetujuan kemitraan platform.</p>
                    </div>
                </div>
                <div class="shrink-0">
                    <a href="{{ route('admin.developers.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold shadow-xs transition">
                        <span>Kelola Semua Mitra</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>

            <!-- Body Grid: Metrik & Pengembang Terbaru -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 mt-4">
                <!-- Metrik Ringkas Developer (5 Kolom) -->
                <div class="lg:col-span-5 grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-1 gap-3">
                    <!-- Total PT/CV -->
                    <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-200/80 flex items-center justify-between">
                        <div>
                            <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Total Terdaftar</span>
                            <div class="flex items-baseline gap-1 mt-0.5">
                                <span class="text-xl font-black text-slate-900">{{ $totalDevelopers }}</span>
                                <span class="text-xs text-slate-500 font-medium">Perusahaan</span>
                            </div>
                        </div>
                        <div class="w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-600 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                    </div>

                    <!-- Disetujui (Aktif) -->
                    <div class="bg-emerald-50/60 rounded-xl p-3.5 border border-emerald-200/70 flex items-center justify-between">
                        <div>
                            <span class="text-[11px] font-semibold text-emerald-800 uppercase tracking-wider block">Mitra Aktif</span>
                            <div class="flex items-baseline gap-1 mt-0.5">
                                <span class="text-xl font-black text-emerald-700">{{ $approvedDevelopers }}</span>
                                <span class="text-xs text-emerald-600 font-medium">PT/CV Aktif</span>
                            </div>
                        </div>
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </div>
                    </div>

                    <!-- Menunggu Persetujuan -->
                    <div class="bg-amber-50/70 rounded-xl p-3.5 border border-amber-200/80 flex items-center justify-between">
                        <div>
                            <span class="text-[11px] font-semibold text-amber-900 uppercase tracking-wider block">Menunggu Approval</span>
                            <div class="flex items-baseline gap-1 mt-0.5">
                                <span class="text-xl font-black text-amber-800">{{ $pendingDevelopers }}</span>
                                <span class="text-xs text-amber-700 font-medium">Permohonan</span>
                            </div>
                        </div>
                        @if($pendingDevelopers > 0)
                            <a href="{{ route('admin.developers.index', ['status' => 'pending']) }}" class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-bold transition shrink-0 shadow-xs flex items-center gap-1">
                                <span>Verifikasi</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        @else
                            <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Pengembang Terakhir Terdaftar (7 Kolom) -->
                <div class="lg:col-span-7 bg-slate-50/70 rounded-xl p-4 border border-slate-200/70 flex flex-col">
                    <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-200/60">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-700">Mitra Pengembang Terkini</span>
                        <span class="text-[11px] text-slate-400 font-medium">Real-Time Database</span>
                    </div>

                    @if($recentDevelopers->isNotEmpty())
                        <div class="space-y-2">
                            @foreach($recentDevelopers->take(3) as $dev)
                                <div class="flex items-center justify-between p-2.5 bg-white rounded-xl border border-slate-200/70 shadow-2xs hover:border-blue-200 hover:bg-blue-50/20 transition">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-8 h-8 rounded-lg bg-slate-100 border border-slate-200 text-slate-600 flex items-center justify-center shrink-0 font-bold text-xs uppercase">
                                            {{ substr($dev->company_name, 0, 2) }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-bold text-slate-900 text-xs truncate">{{ $dev->company_name }}</span>
                                                <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                                    {{ $dev->entity_type === 'badan_usaha' ? strtoupper($dev->business_type ?? 'PT/CV') : 'Perorangan' }}
                                                </span>
                                            </div>
                                            <p class="text-[11px] text-slate-500 truncate mt-0.5">PIC: {{ $dev->pic_name }} &bull; {{ $dev->phone }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        @if($dev->status === 'approved')
                                            <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full text-[10px] font-bold">
                                                AKTIF
                                            </span>
                                        @elseif($dev->status === 'pending')
                                            <span class="px-2 py-0.5 bg-amber-50 text-amber-700 border border-amber-200 rounded-full text-[10px] font-bold">
                                                MENUNGGU
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 bg-rose-50 text-rose-700 border border-rose-200 rounded-full text-[10px] font-bold">
                                                DITOLAK
                                            </span>
                                        @endif
                                        <a href="{{ route('admin.developers.show', $dev->id) }}" class="p-1 text-slate-400 hover:text-slate-800 transition" title="Lihat Profil Developer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6 text-slate-400">
                            <p class="text-xs">Belum ada pengembang terdaftar di database.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Primary KPI Metric Cards (Desain Modern, Rapi, Teks Tidak Terpotong) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Total Peserta -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Member</span>
                <div class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
            </div>
            <div class="flex items-baseline gap-1.5">
                <span class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ number_format($totalUsers) }}</span>
                <span class="text-xs font-semibold text-slate-500">Konsumen</span>
            </div>
            <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500">Akun terdaftar</span>
                <span class="font-bold text-blue-600">Aktif</span>
            </div>
        </div>

        <!-- 2. Registrasi NUP -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Registrasi NUP</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
            </div>
            <div class="flex items-baseline gap-1.5">
                <span class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ number_format($totalRegistrations) }}</span>
                <span class="text-xs font-semibold text-slate-500">Nomor</span>
            </div>
            <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500">Terverifikasi bayar:</span>
                <span class="font-bold text-emerald-600">{{ number_format($paidRegistrations) }} Paid</span>
            </div>
        </div>

        <!-- 3. Unit Tersedia -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Ketersediaan Unit</span>
                <div class="w-9 h-9 rounded-xl bg-amber-50 border border-amber-100 text-amber-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                </div>
            </div>
            <div class="flex items-baseline gap-1.5">
                <span class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ $availableUnits }}</span>
                <span class="text-xs font-semibold text-slate-400">/ {{ $totalUnits }} Tersedia</span>
            </div>
            <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500">Terpilih / Proses:</span>
                <span class="font-bold text-amber-600">{{ max(0, $totalUnits - $availableUnits) }} Unit</span>
            </div>
        </div>

        <!-- 4. Total Revenue (Dipastikan Nilai Lengkap & Tidak Pernah Terpotong) -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Akumulasi Revenue</span>
                <div class="w-9 h-9 rounded-xl bg-violet-50 border border-violet-100 text-violet-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="min-w-0" title="Rp {{ number_format($totalRevenue, 0, ',', '.') }}">
                <div class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight truncate">
                    <span class="text-xs sm:text-sm font-extrabold text-violet-600 mr-0.5">Rp</span>{{ number_format($totalRevenue, 0, ',', '.') }}
                </div>
            </div>
            <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500">Hari ini:</span>
                <span class="font-bold text-slate-800">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Secondary Pipeline Cards (Proses Bisnis Kavling) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Pemilihan Unit -->
        <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-2xs">
            <div class="flex items-center justify-between text-xs text-slate-500 mb-1.5">
                <span class="font-medium">Pemilihan Unit</span>
                <span class="font-bold text-blue-600">{{ $confirmedSelections }} Terkonfirmasi</span>
            </div>
            <div class="text-lg font-bold text-slate-800">
                {{ $confirmedSelections }} <span class="text-xs text-slate-400 font-normal">/ {{ $totalSelections }} diajukan</span>
            </div>
        </div>

        <!-- Screening KPR -->
        <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-2xs">
            <div class="flex items-center justify-between text-xs text-slate-500 mb-1.5">
                <span class="font-medium">Screening Berkas KPR</span>
                <span class="font-bold text-emerald-600">{{ $approvedScreenings }} Approved</span>
            </div>
            <div class="text-lg font-bold text-slate-800">
                {{ $approvedScreenings }} <span class="text-xs text-amber-600 font-medium">({{ $pendingScreenings }} Pending)</span>
            </div>
        </div>

        <!-- SPR Diterbitkan -->
        <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-2xs">
            <div class="flex items-center justify-between text-xs text-slate-500 mb-1.5">
                <span class="font-medium">Surat SPR Diterbitkan</span>
                <span class="font-bold text-indigo-600">{{ $issuedSprs }} Terbit</span>
            </div>
            <div class="text-lg font-bold text-slate-800">
                {{ $issuedSprs }} <span class="text-xs text-slate-400 font-normal">({{ $pendingSprs }} Draft)</span>
            </div>
        </div>

        <!-- Pending Payments -->
        <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-2xs">
            <div class="flex items-center justify-between text-xs text-slate-500 mb-1.5">
                <span class="font-medium">Pembayaran Tertunda</span>
                <span class="font-bold {{ $pendingPayments > 0 ? 'text-rose-600' : 'text-slate-500' }}">{{ $pendingPayments }} Menunggu</span>
            </div>
            <div class="text-lg font-bold {{ $pendingPayments > 0 ? 'text-rose-600' : 'text-slate-800' }}">
                {{ $pendingPayments }} <span class="text-xs text-slate-400 font-normal">Transaksi Verifikasi</span>
            </div>
        </div>
    </div>

    <!-- Recent Activity Feeds (Desain Bersih & Elegan, 3 Kolom) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- 1. Registrasi NUP Terbaru -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                        <h3 class="font-bold text-sm text-slate-800">Registrasi NUP Terbaru</h3>
                    </div>
                    <a href="{{ route('admin.nup.index') }}" class="text-[11px] font-bold text-blue-600 hover:text-blue-700">Lihat Semua &rarr;</a>
                </div>

                @if($recentRegistrations->isEmpty())
                    <div class="py-10 text-center text-slate-400 text-xs">
                        <svg class="w-8 h-8 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <p>Belum ada aktivitas registrasi</p>
                    </div>
                @else
                    <div class="space-y-2.5">
                        @foreach($recentRegistrations as $reg)
                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/70 hover:bg-white hover:border-slate-300 transition flex items-center justify-between">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 font-bold text-xs flex items-center justify-center shrink-0">
                                        {{ substr($reg->user->name ?? 'U', 0, 1) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-xs text-slate-900 truncate">{{ $reg->user->name ?? '-' }}</p>
                                        <p class="text-[11px] font-mono text-slate-500 truncate">{{ $reg->registration_number }}</p>
                                    </div>
                                </div>
                                <div class="shrink-0 text-right">
                                    @if($reg->payment_status === 'paid')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            LUNAS
                                        </span>
                                    @elseif($reg->payment_status === 'pending')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                            PENDING
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                            BATAL
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- 2. Pembayaran Masuk Terbaru -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                        <h3 class="font-bold text-sm text-slate-800">Pembayaran Terbaru</h3>
                    </div>
                    <span class="text-[11px] font-medium text-slate-400">Verifikasi Sistem</span>
                </div>

                @if($recentPayments->isEmpty())
                    <div class="py-10 text-center text-slate-400 text-xs">
                        <svg class="w-8 h-8 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p>Belum ada mutasi transaksi</p>
                    </div>
                @else
                    <div class="space-y-2.5">
                        @foreach($recentPayments as $payment)
                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/70 hover:bg-white hover:border-slate-300 transition flex items-center justify-between">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 font-bold text-xs flex items-center justify-center shrink-0">
                                        {{ substr($payment->user->name ?? 'P', 0, 1) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-xs text-slate-900 truncate">{{ $payment->user->name ?? '-' }}</p>
                                        <p class="text-[11px] font-mono text-slate-500 truncate">{{ $payment->transaction_number }}</p>
                                    </div>
                                </div>
                                <div class="shrink-0 text-right">
                                    @if($payment->status === 'success')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            SUKSES
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                            PENDING
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- 3. Pemilihan Unit Terbaru -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-amber-500"></div>
                        <h3 class="font-bold text-sm text-slate-800">Pemilihan Unit Terbaru</h3>
                    </div>
                    <a href="{{ route('admin.units.index') }}" class="text-[11px] font-bold text-amber-600 hover:text-amber-700">Lihat Semua &rarr;</a>
                </div>

                @if($recentSelections->isEmpty())
                    <div class="py-10 text-center text-slate-400 text-xs">
                        <svg class="w-8 h-8 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <p>Belum ada data unit dipilih</p>
                    </div>
                @else
                    <div class="space-y-2.5">
                        @foreach($recentSelections as $selection)
                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/70 hover:bg-white hover:border-slate-300 transition flex items-center justify-between">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 font-bold text-xs flex items-center justify-center shrink-0">
                                        {{ substr($selection->user->name ?? 'K', 0, 1) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-xs text-slate-900 truncate">{{ $selection->user->name ?? '-' }}</p>
                                        <p class="text-[11px] font-semibold text-slate-600 truncate">{{ $selection->housingUnit->full_identifier ?? 'Unit' }}</p>
                                    </div>
                                </div>
                                <div class="shrink-0 text-right">
                                    @if($selection->status === 'confirmed')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            KUNCI UNIT
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                            REVIEW
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- ================================================================ -->
    <!-- REKAP PEMBAYARAN RUMAH SELURUH MEMBER (ENTERPRISE DATA CARD) -->
    <!-- ================================================================ -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/40">
            <div>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Rekap Pembayaran Rumah Member</h2>
                        <p class="text-xs text-slate-500">Monitoring angsuran pelunasan, sisa tagihan, dan progres seluruh SPR aktif.</p>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    {{ $memberPaymentSummaries->count() }} Dokumen SPR Aktif
                </span>
            </div>
        </div>

        @if($memberPaymentSummaries->isEmpty())
            <div class="p-12 text-center text-slate-400">
                <div class="w-12 h-12 mx-auto mb-3 bg-slate-100 rounded-2xl flex items-center justify-center text-slate-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                </div>
                <p class="font-bold text-slate-700 text-sm">Belum Ada Transaksi Pembayaran Rumah</p>
                <p class="text-xs text-slate-400 mt-0.5">Seluruh pembayaran pelunasan dan angsuran SPR member akan muncul otomatis di sini.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                            <th class="px-5 py-3.5 text-left">Nama Member</th>
                            <th class="px-4 py-3.5 text-left">Unit Kavling</th>
                            <th class="px-4 py-3.5 text-right">Total Harga</th>
                            <th class="px-4 py-3.5 text-right">Sudah Dibayar</th>
                            <th class="px-4 py-3.5 text-right">Sisa Tagihan</th>
                            <th class="px-4 py-3.5 text-center w-36">Progres</th>
                            <th class="px-4 py-3.5 text-center">Status</th>
                            <th class="px-5 py-3.5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($memberPaymentSummaries as $summary)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-700 font-bold text-xs flex items-center justify-center shrink-0">
                                            {{ substr($summary['user_name'], 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900 text-xs">{{ $summary['user_name'] }}</p>
                                            <span class="inline-block px-1.5 py-0.2 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 mt-0.5">
                                                {{ strtoupper($summary['payment_scheme'] ?? 'KPR') }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="font-semibold text-slate-800 text-xs">{{ $summary['unit_identifier'] }}</span>
                                </td>
                                <td class="px-4 py-3.5 text-right font-medium text-slate-900 text-xs">
                                    Rp {{ number_format($summary['total_price'], 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3.5 text-right font-bold text-emerald-600 text-xs">
                                    Rp {{ number_format($summary['total_paid'], 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3.5 text-right font-bold text-xs {{ $summary['sisa'] > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                    Rp {{ number_format($summary['sisa'], 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-slate-100 rounded-full h-2 overflow-hidden border border-slate-200/60">
                                            <div class="h-2 rounded-full {{ $summary['progress'] >= 100 ? 'bg-emerald-500' : 'bg-indigo-600' }}"
                                                 style="width: {{ min($summary['progress'], 100) }}%"></div>
                                        </div>
                                        <span class="text-xs font-bold {{ $summary['progress'] >= 100 ? 'text-emerald-600' : 'text-indigo-600' }} w-9 text-right">
                                            {{ number_format($summary['progress'], 0) }}%
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    @if($summary['status'] === 'Lunas')
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 inline-flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            <span>Lunas</span>
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 inline-flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                                            <span>Belum Lunas</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <a href="{{ route('kpr-payment.index', $summary['spr']->id) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 bg-slate-100 text-slate-700 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition text-xs font-semibold">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <span>Rincian</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection
