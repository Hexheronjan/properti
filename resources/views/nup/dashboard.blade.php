@extends('layouts.app')

@section('title', 'Dashboard Saya')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header Dashboard dengan Banner Identitas Utama -->
    <div class="relative overflow-hidden bg-gradient-to-r from-blue-700 via-indigo-700 to-blue-800 rounded-2xl p-6 sm:p-7 text-white shadow-sm">
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-extrabold bg-white/15 text-white backdrop-blur-xs mb-2 border border-white/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    BERANDA UTAMA PORTAL PESERTA
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Selamat Datang, {{ $user->name }}!</h1>
                <p class="text-xs sm:text-sm text-blue-100/90 mt-1 max-w-2xl">
                    Pantau nomor registrasi NUP, status pemilihan unit, antrian rumah, serta progres pembayaran Anda secara terpadu di satu tempat.
                </p>
            </div>
            <div class="flex items-center gap-2 self-start sm:self-auto shrink-0">
                <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl text-xs font-bold bg-white text-blue-800 shadow-sm">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Akun Peserta Aktif</span>
                </span>
            </div>
        </div>
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
    </div>
    {{-- BANNER: Peringatan rumah belum lunas --}}
    @if($hasUnpaidHouse)
    <div class="bg-amber-50/70 border border-amber-200/80 rounded-2xl p-4 sm:p-5 flex items-start gap-4 shadow-sm">
        <div class="w-10 h-10 rounded-xl bg-amber-100/80 flex items-center justify-center shrink-0 text-amber-700 mt-0.5">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
        </div>
        <div class="flex-1">
            <h3 class="font-bold text-amber-950 text-sm mb-0.5">Pemesanan Unit Baru Dibatasi</h3>
            <p class="text-amber-800 text-xs leading-relaxed">
                Anda masih memiliki kewajiban pembayaran unit rumah yang belum lunas. Silakan selesaikan pelunasan pembayaran unit rumah aktif Anda sebelum mengajukan pemesanan atau pengajuan unit berikutnya.
            </p>
        </div>
    </div>
    @endif

    @if($registrations->isEmpty())
        <!-- Empty State -->
        <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm p-8 sm:p-12 text-center max-w-2xl mx-auto my-6">
            <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-4 border border-blue-100">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
            </div>
            <h3 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight mb-2">Mulai Perjalanan Memiliki Rumah Impian</h3>
            <p class="text-slate-500 text-xs sm:text-sm max-w-md mx-auto mb-6 leading-relaxed">
                Daftarkan Nomor Urut Pemesanan (NUP) Anda secara online untuk mengunci antrian dan memilih unit rumah idaman di perumahan pilihan Anda.
            </p>
            <a href="{{ route('nup.register') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs sm:text-sm rounded-xl transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Daftar NUP Sekarang
            </a>
        </div>
    @else
        @php
            $sprIssuedCount = $sprDocuments->count();
            $sprLunasCount  = $sprDocuments->filter(fn($s) => $s->isFullyPaid())->count();
            $sprBelumLunas  = $sprIssuedCount - $sprLunasCount;
            $totalNupFee    = $registrations->sum('nup_fee');
        @endphp

        <!-- Unified Statistics Cards (Warna Khusus per Metrik) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Card 1: Registrasi NUP (Blue Theme) -->
            <div class="bg-white rounded-2xl p-5 border-t-4 border-t-blue-500 border border-slate-200/80 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-bold text-blue-600 uppercase tracking-wider">Registrasi NUP</span>
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-black text-slate-900 tracking-tight">{{ $registrations->count() }}</p>
                <p class="text-[11px] text-slate-500 font-medium mt-1">Nomor registrasi terdaftar</p>
            </div>

            <!-- Card 2: Unit Dipilih (Indigo Theme) -->
            <div class="bg-white rounded-2xl p-5 border-t-4 border-t-indigo-500 border border-slate-200/80 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-bold text-indigo-600 uppercase tracking-wider">Unit Dipilih</span>
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-black text-slate-900 tracking-tight">{{ $registrations->sum(function($r) { return $r->unitSelections->where('status', '!=', 'cancelled')->count(); }) }}</p>
                <p class="text-[11px] text-slate-500 font-medium mt-1">Unit rumah terkunci</p>
            </div>

            <!-- Card 3: Status Pembayaran (Emerald/Green Theme) -->
            <div class="bg-white rounded-2xl p-5 border-t-4 border-t-emerald-500 border border-slate-200/80 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider">Status Pembayaran</span>
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                @if($sprIssuedCount === 0)
                    <p class="text-lg font-bold text-slate-400 mt-1">Belum Ada SPR</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Pilih unit terlebih dahulu</p>
                @elseif($sprBelumLunas > 0)
                    <div class="flex items-center gap-1.5 mt-1">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        <p class="text-lg font-black text-amber-700">Belum Lunas</p>
                    </div>
                    <p class="text-[11px] text-slate-500 font-medium mt-0.5">{{ $sprBelumLunas }} dari {{ $sprIssuedCount }} SPR aktif</p>
                @else
                    <div class="flex items-center gap-1.5 mt-1">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <p class="text-lg font-black text-emerald-700">Semua Lunas</p>
                    </div>
                    <p class="text-[11px] text-slate-500 font-medium mt-0.5">{{ $sprIssuedCount }} SPR lunas 100%</p>
                @endif
            </div>

            <!-- Card 4: NUP Fee (Amber Theme) -->
            <div class="bg-white rounded-2xl p-5 border-t-4 border-t-amber-500 border border-slate-200/80 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-bold text-amber-700 uppercase tracking-wider">Total Biaya NUP</span>
                    <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                        </svg>
                    </div>
                </div>
                <p class="text-xl font-black text-slate-900 tracking-tight">Rp {{ number_format($totalNupFee, 0, ',', '.') }}</p>
                <p class="text-[11px] text-slate-500 font-medium mt-1">Biaya pendaftaran NUP</p>
            </div>
        </div>

        <!-- Quick Actions Panel (Warna Berbeda Tiap Aksi) -->
        <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-sm">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-1.5 h-4 bg-blue-600 rounded-full"></div>
                <h2 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider">Aksi Cepat & Akses Layanan</h2>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <!-- Aksi 1: Daftar NUP Baru (Amber Accent) -->
                <a href="{{ route('nup.register') }}" class="flex items-center justify-between p-4 bg-gradient-to-br from-amber-50/50 to-white hover:from-amber-50 hover:to-amber-100/60 border border-amber-200/90 hover:border-amber-400 rounded-xl transition group shadow-sm">
                    <div class="flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 border border-amber-200/80 group-hover:scale-105 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-sm font-bold text-slate-900 group-hover:text-amber-800 transition block">Daftar NUP Baru</span>
                            <span class="text-[11px] text-amber-800/80 block">Ambil nomor urut pemesanan</span>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-amber-400 group-hover:text-amber-700 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </a>

                <!-- Aksi 2: Katalog & Pilih Unit (Indigo Accent) -->
                <a href="{{ route('units.index') }}" class="flex items-center justify-between p-4 bg-gradient-to-br from-indigo-50/50 to-white hover:from-indigo-50 hover:to-indigo-100/60 border border-indigo-200/90 hover:border-indigo-400 rounded-xl transition group shadow-sm">
                    <div class="flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0 border border-indigo-200/80 group-hover:scale-105 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-sm font-bold text-slate-900 group-hover:text-indigo-800 transition block">Katalog & Pilih Unit</span>
                            <span class="text-[11px] text-indigo-800/80 block">Jelajahi denah dan ketersediaan</span>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-indigo-400 group-hover:text-indigo-700 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </a>

                <!-- Aksi 3: Status Antrian Unit (Purple Accent) -->
                <a href="{{ route('units.queue') }}" class="flex items-center justify-between p-4 bg-gradient-to-br from-purple-50/50 to-white hover:from-purple-50 hover:to-purple-100/60 border border-purple-200/90 hover:border-purple-400 rounded-xl transition group shadow-sm">
                    <div class="flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center shrink-0 border border-purple-200/80 group-hover:scale-105 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-sm font-bold text-slate-900 group-hover:text-purple-800 transition block">Status Antrian Unit</span>
                            <span class="text-[11px] text-purple-800/80 block">Pantau giliran pemilihan rumah</span>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-purple-400 group-hover:text-purple-700 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </a>
            </div>
        </div>

        <!-- Section Title: Status & Tahapan Pemesanan Unit -->
        <div class="flex items-center justify-between pt-2">
            <div class="flex items-center gap-2">
                <div class="w-1.5 h-4 bg-emerald-600 rounded-full"></div>
                <h2 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider">Status Pemesanan & Alur Tahapan Unit</h2>
            </div>
            <span class="text-xs text-slate-500 font-medium">{{ $registrations->count() }} Dokumen Registrasi</span>
        </div>
                <!-- Registrations List -->
        <div class="space-y-6">
            @foreach($registrations as $registration)
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                    <!-- Card Header dengan Aksen Jelas -->
                    <div class="px-6 py-4 border-b border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-gradient-to-r from-slate-50 via-blue-50/30 to-slate-50">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-sm shadow-blue-500/20 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
                                </svg>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-base font-extrabold text-slate-900 tracking-tight">Registrasi #{{ $registration->registration_number }}</h3>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200/60">NUP RESMI</span>
                                </div>
                                <p class="text-slate-500 text-xs mt-0.5">Metode Pembayaran: <span class="font-semibold text-slate-700">{{ config('nup.payment_methods.' . $registration->payment_method) }}</span></p>
                            </div>
                        </div>

                        @if($registration->is_forfeited)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold shadow-xs bg-rose-100 text-rose-800 border border-rose-300">
                                <span class="w-2 h-2 rounded-full bg-rose-600"></span>
                                DIBATALKAN / HANGUS
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold shadow-xs
                                @if($registration->payment_status === 'paid')
                                    bg-emerald-100 text-emerald-800 border border-emerald-300
                                @elseif($registration->payment_status === 'pending')
                                    bg-amber-100 text-amber-800 border border-amber-300
                                @else
                                    bg-rose-100 text-rose-800 border border-rose-300
                                @endif
                            ">
                                <span class="w-2 h-2 rounded-full {{ $registration->payment_status === 'paid' ? 'bg-emerald-600' : ($registration->payment_status === 'pending' ? 'bg-amber-600 animate-pulse' : 'bg-rose-600') }}"></span>
                                {{ strtoupper($registration->payment_status) }}
                            </span>
                        @endif
                    </div>
                    <!-- Card Body -->
                    <div class="p-6">
                        @if($registration->is_forfeited)
                            <div class="p-5 bg-gradient-to-br from-rose-50/80 to-white border border-rose-200/90 rounded-2xl">
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center shrink-0 border border-rose-200 shadow-xs">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h4 class="text-base font-extrabold text-slate-900">Pemesanan Unit Telah Dibatalkan</h4>
                                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200">Dana Hangus</span>
                                        </div>
                                        <p class="text-xs text-rose-900/90 mt-1 leading-relaxed">
                                            {{ $registration->forfeit_reason ?? 'Pemesanan unit telah dibatalkan secara mandiri. Sesuai ketentuan resmi, seluruh biaya pendaftaran NUP & Uang Tanda Jadi (UTK) dinyatakan hangus (non-refundable).' }}
                                        </p>
                                        <div class="mt-3.5 p-3.5 bg-white/90 rounded-xl border border-rose-200/80 text-xs text-rose-900 space-y-1.5">
                                            <p class="font-bold text-rose-950 uppercase tracking-wider text-[10px]">Ringkasan Konsekuensi Pembatalan:</p>
                                            <p class="flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                <span>Seluruh uang pendaftaran NUP dan UTK yang telah dibayarkan <strong>HANGUS</strong> (tidak dapat dikembalikan).</span>
                                            </p>
                                            <p class="flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                <span>Hak atas unit kavling telah dilepas kembali ke stok publik.</span>
                                            </p>
                                            <p class="flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                <span>Nomor tiket antrean NUP ini telah ditutup / tidak aktif.</span>
                                            </p>
                                        </div>
                                        <div class="mt-4 flex items-center gap-3">
                                            <a href="{{ route('nup.register') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-sm">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                Daftar Tiket NUP Baru
                                            </a>
                                            <a href="{{ route('nup.show', $registration->id) }}" class="px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 rounded-xl transition text-xs font-semibold">
                                                Detail Riwayat NUP
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                        <!-- Progress Stepper Timeline -->
                        <div class="mb-6 p-4 bg-slate-50/70 border border-slate-200/70 rounded-xl">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-3">Tahapan Proses Pemesanan</span>
                            <div class="flex items-center justify-between max-w-2xl mx-auto">
                                <!-- Step 1: NUP -->
                                <div class="flex flex-col items-center">
                                    <div class="w-7 h-7 rounded-full {{ $registration->payment_status === 'paid' ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-500' }} flex items-center justify-center text-xs font-bold shadow-sm">
                                        @if($registration->payment_status === 'paid')
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                        @else
                                            1
                                        @endif
                                    </div>
                                    <span class="text-[11px] font-medium mt-1.5 {{ $registration->payment_status === 'paid' ? 'text-slate-800' : 'text-slate-400' }}">NUP</span>
                                </div>
                                <div class="flex-1 h-0.5 {{ $registration->payment_status === 'paid' ? 'bg-blue-600' : 'bg-slate-200' }} mx-2"></div>

                                <!-- Step 2: Unit -->
                                <div class="flex flex-col items-center">
                                    <div class="w-7 h-7 rounded-full {{ $registration->unitSelections->isNotEmpty() ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-500' }} flex items-center justify-center text-xs font-bold shadow-sm">
                                        @if($registration->unitSelections->isNotEmpty())
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                        @else
                                            2
                                        @endif
                                    </div>
                                    <span class="text-[11px] font-medium mt-1.5 {{ $registration->unitSelections->isNotEmpty() ? 'text-slate-800' : 'text-slate-400' }}">Pilih Unit</span>
                                </div>
                                <div class="flex-1 h-0.5 {{ $registration->unitSelections->isNotEmpty() ? 'bg-blue-600' : 'bg-slate-200' }} mx-2"></div>

                                <!-- Step 3: Screening / KPR -->
                                <div class="flex flex-col items-center">
                                    @php
                                        $kprDone = ($registration->kprScreening && $registration->kprScreening->status === 'approved') || $registration->payment_method === 'tunai_mitra_pintar' || $registration->unitSelections->whereIn('payment_scheme', ['cash', 'tunai', 'installment'])->isNotEmpty();
                                    @endphp
                                    <div class="w-7 h-7 rounded-full {{ $kprDone ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-500' }} flex items-center justify-center text-xs font-bold shadow-sm">
                                        @if($kprDone)
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                        @else
                                            3
                                        @endif
                                    </div>
                                    <span class="text-[11px] font-medium mt-1.5 {{ $kprDone ? 'text-slate-800' : 'text-slate-400' }}">Verifikasi</span>
                                </div>
                                <div class="flex-1 h-0.5 {{ $registration->sprDocuments()->whereIn('status', ['issued', 'paid', 'active'])->count() > 0 ? 'bg-blue-600' : 'bg-slate-200' }} mx-2"></div>

                                <!-- Step 4: SPR -->
                                <div class="flex flex-col items-center">
                                    @php
                                        $sprDone = $registration->sprDocuments()->whereIn('status', ['issued', 'paid', 'active'])->count() > 0;
                                    @endphp
                                    <div class="w-7 h-7 rounded-full {{ $sprDone ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-500' }} flex items-center justify-center text-xs font-bold shadow-sm">
                                        @if($sprDone)
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                        @else
                                            4
                                        @endif
                                    </div>
                                    <span class="text-[11px] font-medium mt-1.5 {{ $sprDone ? 'text-slate-800' : 'text-slate-400' }}">SPR Resmi</span>
                                </div>
                            </div>
                        </div>

                        <!-- Queue Number Box (Amber Theme) -->
                        @if($registration->queue_number)
                            <div class="bg-gradient-to-r from-amber-50/80 via-orange-50/40 to-amber-50/80 border border-amber-200/90 rounded-xl p-4 mb-4 flex items-center justify-between shadow-xs">
                                <div>
                                    <span class="text-[10px] font-bold text-amber-800 uppercase tracking-wider block">Nomor Antrian Pemilihan Unit</span>
                                    <p class="text-2xl font-black text-amber-950 font-mono tracking-tight mt-0.5">{{ $registration->queue_number }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs text-slate-600 font-medium block">Terbit: {{ $registration->queue_issue_date->format('d-m-Y H:i') }}</span>
                                    @if($registration->unit_selection_deadline)
                                        <span class="text-[11px] text-amber-800 block mt-0.5 font-bold">Batas Waktu: {{ $registration->unit_selection_deadline->format('d-m-Y H:i') }}</span>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- KPR Screening Status Box -->
                        @if($registration->kprScreening)
                            <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-4 mb-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Status Screening KPR</span>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold
                                        @if($registration->kprScreening->isLayakProses()) bg-emerald-50 text-emerald-700 border border-emerald-200/80
                                        @elseif($registration->kprScreening->isLayakBersyarat()) bg-amber-50 text-amber-700 border border-amber-200/80
                                        @elseif($registration->kprScreening->isTidakLayak()) bg-rose-50 text-rose-700 border border-rose-200/80
                                        @elseif($registration->kprScreening->status === 'approved') bg-emerald-50 text-emerald-700 border border-emerald-200/80
                                        @elseif($registration->kprScreening->status === 'rejected') bg-rose-50 text-rose-700 border border-rose-200/80
                                        @else bg-slate-100 text-slate-700 border border-slate-200 @endif
                                    ">
                                        {{ config('nup.screening_statuses.' . $registration->kprScreening->status, strtoupper(str_replace('_', ' ', $registration->kprScreening->status))) }}
                                    </span>
                                </div>
                                
                                @if($registration->kprScreening->unit_selection_deadline)
                                    <p class="text-xs text-slate-600">
                                        Batas Waktu Pilih Unit: <span class="font-semibold text-slate-800">{{ $registration->kprScreening->unit_selection_deadline->format('d-m-Y H:i') }}</span>
                                        @if($registration->kprScreening->isUnitSelectionDeadlinePassed())
                                            <span class="text-rose-600 font-bold ml-1">(LEWAT DEADLINE)</span>
                                        @endif
                                    </p>
                                @endif
                                
                                @if($registration->kprScreening->participant_decision && $registration->kprScreening->participant_decision !== 'pending')
                                    <p class="text-xs text-slate-600 mt-1">
                                        Keputusan Peserta: <span class="font-semibold text-slate-800">{{ config('nup.participant_decisions.' . $registration->kprScreening->participant_decision) }}</span>
                                    </p>
                                @endif

                                @if($registration->kprScreening->pefindo_score)
                                    <div class="mt-2.5 p-3.5 bg-emerald-50/70 border border-emerald-200/80 rounded-xl">
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                                            <div>
                                                <span class="text-[11px] font-semibold text-emerald-800 uppercase tracking-wider block">Skor Kredit Pefindo:</span>
                                                <div class="flex items-baseline gap-2 mt-0.5">
                                                    <span class="text-xl font-black text-emerald-950">{{ $registration->kprScreening->pefindo_score }}</span>
                                                    <span class="text-xs font-bold text-emerald-700">({{ $registration->kprScreening->pefindo_grade ?? 'Sangat Baik' }})</span>
                                                </div>
                                                @if($registration->kprScreening->bank)
                                                    <p class="text-xs text-slate-600 mt-1">
                                                        Rekomendasi Bank: <strong class="text-slate-800">{{ $registration->kprScreening->bank === 'other' ? $registration->kprScreening->bank_other : strtoupper($registration->kprScreening->bank) }}</strong>
                                                    </p>
                                                @endif
                                            </div>
                                            @if(!$registration->kprScreening->hasParticipantDecision() && $registration->unitSelections->isEmpty())
                                                <a href="{{ route('kpr.outcome', $registration->kprScreening->id) }}" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition shadow-xs shrink-0">
                                                    <span>Buka Hasil Skor & Langkah KPR &rarr;</span>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                @php
                                    $rejectedDocs = $registration->kprScreening->userDocuments->where('status', 'rejected');
                                @endphp
                                @if($rejectedDocs->isNotEmpty())
                                    <div class="mt-2.5 p-3 bg-rose-50 border border-rose-200 rounded-lg text-xs text-rose-800">
                                        <div class="flex items-center gap-1.5 font-bold mb-1 text-rose-950">
                                            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                                            <span>Perlu Revisi Dokumen:</span>
                                        </div>
                                        @foreach($rejectedDocs as $rDoc)
                                            <p class="text-rose-900">• <strong>{{ \App\Models\KprScreening::documentLabels()[$rDoc->document_type] ?? $rDoc->document_type }}</strong>: "{{ $rDoc->rejection_reason ?? 'Mohon unggah ulang versi jelas.' }}"</p>
                                        @endforeach
                                    </div>
                                    <div class="mt-3 flex justify-end">
                                        <a href="{{ route('kpr.show', $registration->kprScreening->id) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold transition shadow-sm">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                                            <span>Unggah Berkas Revisi Sekarang &rarr;</span>
                                        </a>
                                    </div>
                                @else
                                    <div class="mt-3 flex justify-end">
                                        <a href="{{ route('kpr.show', $registration->kprScreening->id) }}" class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 hover:text-blue-800 transition">
                                            <span>Lihat Rincian Berkas Screening &rarr;</span>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <!-- Unit Selection Details -->
                        @if($registration->unitSelections->where('status', '!=', 'cancelled')->isNotEmpty())
                            <div class="mb-5">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Unit Rumah yang Dipilih</span>
                                @foreach($registration->unitSelections->where('status', '!=', 'cancelled') as $selection)
                                    @php
                                        $proj = $selection->housingUnit->project ?? $selection->housingUnit->housingProject;
                                        $isCashSel = in_array($selection->payment_scheme, ['cash', 'tunai', 'tunai_keras'], true);
                                        $utkIsPaid = $isCashSel || $selection->isUtkPaid();
                                        $unitSpr = $selection->sprDocuments()->latest()->first();
                                    @endphp
                                    <div class="bg-gradient-to-r from-blue-50/40 via-indigo-50/20 to-white border border-blue-200/90 rounded-xl p-4.5 mb-3 shadow-xs">
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                            <div>
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <p class="font-bold text-slate-900 text-base">{{ $selection->housingUnit->full_identifier }}</p>
                                                    @if($proj)
                                                        <span class="text-xs px-2.5 py-0.5 bg-blue-50 text-blue-700 border border-blue-200/60 rounded-md font-semibold">{{ $proj->name }}</span>
                                                    @endif
                                                </div>
                                                <p class="text-xs text-slate-600 mt-1">
                                                    <span class="font-semibold text-slate-700">Tipe:</span> {{ config('nup.unit_types.' . $selection->housingUnit->type, $selection->housingUnit->type) }} &middot;
                                                    <span class="font-semibold text-slate-700">Harga:</span> Rp {{ number_format($selection->housingUnit->price, 0, ',', '.') }}
                                                </p>
                                                <div class="flex items-center gap-2 mt-2 flex-wrap text-xs">
                                                    <span class="text-slate-400 font-medium">Status:</span>
                                                    <span class="px-2 py-0.5 rounded-md font-semibold text-[11px]
                                                        @if($selection->status === 'confirmed') bg-emerald-50 text-emerald-700 border border-emerald-200/80
                                                        @elseif($selection->status === 'pending') bg-amber-50 text-amber-700 border border-amber-200/80
                                                        @else bg-slate-100 text-slate-700 border border-slate-200 @endif
                                                    ">
                                                        {{ strtoupper($selection->status) }}
                                                    </span>

                                                    <span class="text-slate-300">|</span>
                                                    <span class="text-slate-400 font-medium">UTK:</span>
                                                    @if($isCashSel)
                                                        <span class="px-2 py-0.5 rounded-md font-semibold text-[11px] bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                                                            BEBAS UTK (TUNAI)
                                                        </span>
                                                    @elseif($selection->utk_payment_status)
                                                        <span class="px-2 py-0.5 rounded-md font-semibold text-[11px]
                                                            @if($selection->utk_payment_status === 'paid') bg-emerald-50 text-emerald-700 border border-emerald-200/80
                                                            @elseif($selection->utk_payment_status === 'pending') bg-amber-50 text-amber-700 border border-amber-200/80
                                                            @else bg-rose-50 text-rose-700 border border-rose-200/80 @endif
                                                        ">
                                                            {{ strtoupper($selection->utk_payment_status) }}
                                                        </span>
                                                    @endif

                                                    @if($unitSpr)
                                                        <span class="text-slate-300">|</span>
                                                        <span class="text-slate-400 font-medium">SPR:</span>
                                                        <span class="px-2 py-0.5 rounded-md font-semibold text-[11px] bg-blue-50 text-blue-700 border border-blue-200/80">
                                                            {{ strtoupper($unitSpr->status) }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('units.show', $selection->id) }}" class="px-3.5 py-1.5 bg-white border border-slate-300 hover:border-blue-600 text-slate-700 hover:text-blue-600 rounded-lg text-xs font-semibold shadow-sm transition">
                                                    Detail Unit &rarr;
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Action Buttons Container -->
                        <div class="flex gap-2.5 flex-wrap items-center pt-3 border-t border-slate-100">
                            @if($registration->payment_status === 'pending')
                                <a href="{{ route('nup.show', $registration->id) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition text-xs font-semibold shadow-sm flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 003.75 19.5z" /></svg>
                                    Proses Pembayaran NUP
                                </a>
                            @elseif($registration->payment_status === 'paid')
                                @php
                                    $activeSelection = $registration->unitSelections->where('status', '!=', 'cancelled')->last();
                                    $isCash = $activeSelection ? in_array($activeSelection->payment_scheme, ['cash', 'tunai', 'tunai_keras'], true) : false;
                                    $isTunaiBertahap = $activeSelection ? ($activeSelection->payment_scheme === 'tunai_bertahap') : false;
                                    $isInHouse = $activeSelection ? in_array($activeSelection->payment_scheme, ['installment', 'inhouse'], true) : false;
                                    $isKpr = $activeSelection ? in_array($activeSelection->payment_scheme, ['kpr', 'kpr_bank'], true) : false;
                                    $utkPaid = $activeSelection ? ($isCash || $activeSelection->isUtkPaid()) : false;
                                    $hasSpr = $activeSelection && $activeSelection->sprDocuments && $activeSelection->sprDocuments->isNotEmpty();
                                    $activeSpr = $hasSpr ? $activeSelection->sprDocuments->first() : null;
                                @endphp

                                @if($activeSelection)
                                    @if(!$hasSpr)
                                        @if($utkPaid)
                                            <span class="px-3.5 py-1.5 bg-blue-50 text-blue-700 border border-blue-200 rounded-xl text-xs font-semibold flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 text-blue-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                Menunggu Penerbitan Dokumen SPR oleh Pengembang
                                            </span>
                                            <a href="{{ route('units.show', $activeSelection->id) }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-xl transition text-xs font-semibold shadow-sm flex items-center gap-1.5">
                                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                Detail Pemesanan Unit
                                            </a>
                                            @if(Auth::check() && Auth::user()->isAdmin())
                                                <a href="{{ route('admin.spr.create', $activeSelection->id) }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition text-xs font-semibold shadow-sm flex items-center gap-1.5">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                                    Operator: Terbitkan SPR
                                                </a>
                                            @endif
                                        @elseif(!$activeSelection->isUtkPaymentDeadlinePassed())
                                            <a href="{{ route('units.show', $activeSelection->id) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition text-xs font-semibold shadow-sm flex items-center gap-1.5">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 003.75 19.5z" /></svg>
                                                Bayar UTK Sekarang
                                            </a>
                                        @endif
                                    @else
                                        @if($activeSpr->isDpPaid() || $activeSpr->down_payment <= 0)
                                            <a href="{{ route('spr.show', $activeSpr->id) }}" class="px-4 py-2 bg-white border border-slate-300 hover:border-slate-400 text-slate-800 rounded-xl transition text-xs font-semibold shadow-sm flex items-center gap-1.5">
                                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                                                Lihat Dokumen SPR
                                            </a>
                                            @if($activeSpr->payment_scheme === 'kpr')
                                                <span class="px-3.5 py-1.5 bg-blue-50 text-blue-700 border border-blue-200 rounded-xl text-xs font-semibold flex items-center gap-1.5">
                                                    <svg class="w-3.5 h-3.5 text-blue-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    Pengawalan KPR (Argo 7 Hari)
                                                </span>
                                            @elseif($activeSpr->payment_scheme === 'mitra_pintar')
                                                @if($activeSpr->isFullyPaid())
                                                    <span class="px-3.5 py-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl text-xs font-semibold flex items-center gap-1.5">
                                                        <svg class="w-4 h-4 inline-block text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Pelunasan Mitra Diterima (Lunas 100%)
                                                    </span>
                                                @else
                                                    <span class="px-3.5 py-1.5 bg-amber-50 text-amber-800 border border-amber-300 rounded-xl text-xs font-semibold flex items-center gap-1.5">
                                                        <svg class="w-3.5 h-3.5 text-amber-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                        Menunggu Pencairan Pelunasan Lembaga Mitra
                                                    </span>
                                                @endif
                                            @else
                                                @if(!$activeSpr->isFullyPaid())
                                                    <a href="{{ route('kpr-payment.create', $activeSpr->id) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition text-xs font-semibold shadow-sm flex items-center gap-1.5">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 003.75 19.5z" /></svg>
                                                        Bayar Termin / Pelunasan
                                                    </a>
                                                @endif
                                            @endif
                                        @else
                                            <a href="{{ route('kpr-payment.create', $activeSpr->id) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition text-xs font-semibold shadow-sm flex items-center gap-1.5">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                                Bayar Uang Muka (DP)
                                            </a>
                                            <span class="px-3.5 py-1.5 bg-slate-50 text-slate-500 border border-slate-200 rounded-xl text-xs font-semibold flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                                                SPR Terbuka Setelah DP Lunas
                                            </span>
                                        @endif
                                    @endif
                                @else
                                    {{-- Belum Pilih Unit --}}
                                    @if($registration->payment_method === 'kpr_bank_flpp' && !$registration->kprScreening)
                                        @if($hasUnpaidHouse)
                                            <button disabled class="px-4 py-2 bg-slate-100 text-slate-400 rounded-xl cursor-not-allowed text-xs font-semibold">
                                                Screening KPR (Terkunci)
                                            </button>
                                        @else
                                            <a href="{{ route('kpr.create-from-nup', $registration->id) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition text-xs font-semibold shadow-sm">
                                                Mulai Screening KPR
                                            </a>
                                        @endif
                                    @elseif($registration->payment_method === 'kpr_bank_flpp' && $registration->kprScreening)
                                        @php
                                            $scr = $registration->kprScreening;
                                            $hasRejectedDocs = $scr->userDocuments->where('status', 'rejected')->isNotEmpty();
                                        @endphp
                                        @if($hasRejectedDocs)
                                            <a href="{{ route('kpr.show', $scr->id) }}" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl transition text-xs font-semibold shadow-sm flex items-center gap-1.5">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                                                Upload Ulang Berkas Revisi
                                            </a>
                                        @elseif(in_array($scr->status, ['submitted', 'processing']))
                                            <a href="{{ route('kpr.show', $scr->id) }}" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl transition text-xs font-semibold shadow-sm flex items-center gap-1.5">
                                                <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                Screening Sedang Ditinjau
                                            </a>
                                        @elseif(in_array($scr->status, ['approved', 'layak_bersyarat']))
                                            @if(!$scr->hasParticipantDecision())
                                                <a href="{{ route('kpr.outcome', $scr->id) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition text-xs font-bold shadow-sm flex items-center gap-1.5">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    Hasil Screening: Buat Keputusan &rarr;
                                                </a>
                                            @else
                                                @if($hasUnpaidHouse)
                                                    <button disabled class="px-4 py-2 bg-slate-100 text-slate-400 rounded-xl cursor-not-allowed text-xs font-semibold">
                                                        Pilih Unit (Terkunci)
                                                    </button>
                                                @else
                                                    <a href="{{ route('units.create', $registration->id) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition text-xs font-semibold shadow-sm flex items-center gap-1.5">
                                                        Pilih Unit Rumah &rarr;
                                                    </a>
                                                @endif
                                            @endif
                                        @elseif($scr->status === 'rejected')
                                            <a href="{{ route('kpr.show', $scr->id) }}" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl transition text-xs font-semibold shadow-sm">
                                                Screening Ditolak
                                            </a>
                                        @else
                                            <a href="{{ route('kpr.show', $scr->id) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition text-xs font-semibold shadow-sm">
                                                Detail Berkas Screening
                                            </a>
                                        @endif
                                    @else
                                        @if($hasUnpaidHouse)
                                            <button disabled class="px-4 py-2 bg-slate-100 text-slate-400 rounded-xl cursor-not-allowed text-xs font-semibold">
                                                Pilih Unit (Terkunci)
                                            </button>
                                        @else
                                            <a href="{{ route('units.create', $registration->id) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition text-xs font-semibold shadow-sm">
                                                Pilih Unit Rumah
                                            </a>
                                        @endif
                                    @endif
                                @endif
                            @endif

                            <a href="{{ route('nup.show', $registration->id) }}" class="px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 rounded-xl transition text-xs font-semibold">
                                Detail Registrasi
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- ================================================================ --}}
    {{-- SECTION: RINGKASAN PEMBAYARAN RUMAH --}}
    {{-- ================================================================ --}}
    @if($sprDocuments->isNotEmpty())
    <div class="mt-8 space-y-4">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 003.75 19.5z" />
            </svg>
            <h2 class="text-lg font-bold text-slate-900 tracking-tight">Ringkasan Pembayaran Rumah</h2>
        </div>

        @foreach($sprDocuments as $spr)
        @php
            $totalHarga      = $spr->unit_price;
            $totalDibayar    = $spr->getTotalSudahDibayar();
            $sisaPembayaran  = $spr->getSisaPembayaran();
            $persentase      = $spr->getTotalPaymentProgress();
            $statusPembayaran = $spr->getPaymentStatus();
            $unitLabel       = $spr->unitSelection?->housingUnit?->full_identifier ?? '-';
        @endphp

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-6">
            {{-- Clean White Card Header --}}
            <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white">
                <div>
                    <span class="text-xs text-slate-400 font-mono">No. SPR: {{ $spr->spr_number }}</span>
                    <h3 class="font-bold text-base text-slate-900 mt-0.5">{{ $unitLabel }}</h3>
                    <p class="text-xs text-slate-400 mt-0.5">{{ ucfirst($spr->payment_scheme) }} &middot; Terbit: {{ $spr->spr_date->format('d M Y') }}</p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold self-start sm:self-auto
                    {{ $statusPembayaran === 'Lunas' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/80' : 'bg-amber-50 text-amber-700 border border-amber-200/80' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $statusPembayaran === 'Lunas' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                    {{ $statusPembayaran === 'Lunas' ? 'Lunas 100%' : 'Belum Lunas' }}
                </span>
            </div>

            <div class="p-6">
                {{-- Status Khusus Alur KPR --}}
                @if($spr->payment_scheme === 'kpr')
                    @if($spr->isSp3kApproved())
                        <div class="mb-6 p-4 bg-emerald-50/70 border border-emerald-200/80 rounded-xl flex items-start justify-between flex-wrap gap-3">
                            <div>
                                <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 text-[11px] font-bold rounded-full">KPR DISETUJUI (SP3K ACC)</span>
                                <h4 class="font-bold text-emerald-950 text-sm mt-1">SP3K No: {{ $spr->sp3k_number }} &middot; Plafon ACC: Rp {{ number_format($spr->sp3k_approved_amount, 0, ',', '.') }}</h4>
                                @if($spr->akad_date)
                                    <p class="text-xs text-emerald-900 mt-1">
                                        <strong>Jadwal Akad:</strong> {{ \Carbon\Carbon::parse($spr->akad_date)->translatedFormat('l, d F Y H:i') }} di {{ $spr->akad_location }}
                                    </p>
                                    <p class="text-[11px] text-emerald-700 mt-0.5">
                                        Wajib membawa berkas fisik asli (KTP, KK, Buku Nikah, NPWP Asli & Materai).
                                    </p>
                                @else
                                    <p class="text-xs text-emerald-800 mt-1">Jadwal akad sedang disusun oleh pihak bank rekanan.</p>
                                @endif
                            </div>
                            <a href="{{ route('spr.show', $spr->id) }}" class="px-3.5 py-1.5 bg-white border border-emerald-300 hover:border-emerald-500 text-emerald-800 rounded-lg text-xs font-semibold shadow-sm transition">
                                Rincian Akad &rarr;
                            </a>
                        </div>
                    @elseif($spr->argo_deadline)
                        <div class="mb-6 p-4 bg-slate-50 border border-slate-200 rounded-xl flex items-start justify-between flex-wrap gap-3">
                            <div>
                                <span class="px-2.5 py-0.5 bg-blue-50 text-blue-700 border border-blue-200/60 text-[11px] font-semibold rounded-full">Pengurusan KPR Bank</span>
                                <h4 class="font-bold text-slate-900 text-sm mt-1">Sisa Waktu Argo: {{ $spr->getArgoRemainingText() }}</h4>
                                <p class="text-xs text-slate-500 mt-0.5">Operator pengembang sedang mengawal proses berkas KPR Anda di bank.</p>
                            </div>
                            <a href="{{ route('spr.show', $spr->id) }}" class="px-3.5 py-1.5 bg-white border border-slate-300 hover:border-blue-600 text-slate-700 hover:text-blue-600 rounded-lg text-xs font-semibold shadow-sm transition">
                                Cek Status &rarr;
                            </a>
                        </div>
                    @endif
                @elseif($spr->payment_scheme === 'mitra_pintar')
                    <div class="mb-6 p-4 {{ $statusPembayaran === 'Lunas' ? 'bg-emerald-50/70 border-emerald-200/80' : 'bg-amber-50/70 border-amber-200/80' }} border rounded-xl flex items-start justify-between flex-wrap gap-3">
                        <div>
                            <span class="px-2.5 py-0.5 {{ $statusPembayaran === 'Lunas' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }} text-[11px] font-bold rounded-full">
                                {{ $statusPembayaran === 'Lunas' ? 'PELUNASAN MITRA DITERIMA (LUNAS 100%)' : 'SKEMA KREDIT MITRA PINTAR (BEBAS DP)' }}
                            </span>
                            <h4 class="font-bold text-slate-900 text-sm mt-1">
                                {{ $statusPembayaran === 'Lunas' ? 'Dana Pelunasan Unit Telah Dicairkan oleh Lembaga Mitra Pintar' : 'Menunggu Pencairan Dana Pelunasan dari Lembaga Mitra Pintar' }}
                            </h4>
                            <p class="text-xs text-slate-600 mt-0.5">
                                {{ $statusPembayaran === 'Lunas' ? 'Kewajiban pembayaran rumah Anda telah tuntas 100%. Silakan cek jadwal Serah Terima Kunci & BAST.' : 'Pelunasan sisa harga unit diselesaikan langsung oleh Lembaga Mitra ke rekening pengembang (Bebas DP).' }}
                            </p>
                        </div>
                        <a href="{{ route('spr.show', $spr->id) }}" class="px-3.5 py-1.5 bg-white border border-amber-300 hover:border-amber-500 text-amber-900 rounded-lg text-xs font-semibold shadow-sm transition">
                            Detail SPR &rarr;
                        </a>
                    </div>
                @endif

                {{-- Kartu Statistik Finansial --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                    <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-4">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Harga Unit</span>
                        <p class="text-xl font-bold text-slate-900 mt-1">Rp {{ number_format($totalHarga, 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-4">
                        <span class="text-[10px] font-bold text-blue-600 uppercase tracking-wider block">Sudah Dibayar</span>
                        <p class="text-xl font-bold text-blue-950 mt-1">Rp {{ number_format($totalDibayar, 0, ',', '.') }}</p>
                        <p class="text-[11px] text-blue-600 mt-1">DP: Rp {{ number_format($spr->down_payment, 0, ',', '.') }} {{ $spr->down_payment <= 0 ? '(Bebas DP)' : '' }}</p>
                    </div>
                    <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-4">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Sisa Kewajiban</span>
                        <p class="text-xl font-bold text-slate-900 mt-1">Rp {{ number_format($sisaPembayaran, 0, ',', '.') }}</p>
                    </div>
                </div>

                {{-- Progress Bar --}}
                <div class="mb-6">
                    <div class="flex justify-between text-xs mb-1.5 font-semibold">
                        <span class="text-slate-600">Progres Pelunasan Rumah</span>
                        <span class="{{ $persentase >= 100 ? 'text-emerald-600' : 'text-blue-600' }}">{{ number_format($persentase, 1) }}%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                        <div class="h-2.5 rounded-full transition-all duration-500 {{ $persentase >= 100 ? 'bg-emerald-500' : 'bg-blue-600' }}"
                             style="width: {{ min($persentase, 100) }}%"></div>
                    </div>
                </div>

                {{-- Riwayat Pembayaran --}}
                <div>
                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wider block mb-3">Riwayat Transaksi</span>

                    {{-- Down Payment --}}
                    @if($spr->down_payment > 0)
                    <div class="mb-2">
                        <div class="flex items-center justify-between bg-slate-50/70 border border-slate-200/80 rounded-xl px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-200/60 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                </div>
                                <div>
                                    <p class="font-bold text-slate-800 text-xs">Uang Muka / Down Payment (DP)</p>
                                    <p class="text-[11px] text-slate-400">{{ $spr->spr_date->format('d M Y') }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-slate-900 text-xs">Rp {{ number_format($spr->down_payment, 0, ',', '.') }}</p>
                                <span class="text-[10px] px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200/60 font-semibold">Lunas</span>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Riwayat Angsuran --}}
                    @if($spr->kprPayments->isNotEmpty())
                        <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
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
                            <div class="flex items-center justify-between bg-slate-50/70 border border-slate-200/80 rounded-xl px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 border border-blue-200/60 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800 text-xs">
                                            {{ $payment->payment_type === 'installment' ? 'Termin ke-' . $payment->installment_number : 'Pelunasan Unit' }}
                                            <span class="text-slate-400 font-normal">#{{ $payment->payment_number }}</span>
                                        </p>
                                        <p class="text-[11px] text-slate-400">
                                            {{ $payment->payment_date ? $payment->payment_date->format('d M Y H:i') : ($payment->created_at->format('d M Y H:i')) }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-slate-900 text-xs">Rp {{ number_format($payment->amount, 0, ',', '.') }}</p>
                                    <span class="text-[10px] px-2 py-0.5 rounded-md font-semibold {{ $badgeColor }}">{{ $badgeLabel }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6 text-slate-500 bg-slate-50 border border-slate-200/60 rounded-xl">
                            @if($spr->payment_scheme === 'mitra_pintar')
                                <p class="text-xs font-semibold text-amber-800">Menunggu pencairan dana pelunasan dari Lembaga Mitra Pintar ke rekening pengembang.</p>
                            @elseif($spr->payment_scheme === 'kpr')
                                <p class="text-xs font-semibold text-blue-800">Menunggu proses akad kredit dan pencairan dana dari Bank Rekanan.</p>
                            @else
                                <p class="text-xs font-medium text-slate-400">Belum ada riwayat pembayaran termin / pelunasan</p>
                            @endif
                        </div>
                    @endif

                    {{-- Tombol bayar jika belum lunas --}}
                    @if($statusPembayaran !== 'Lunas')
                        <div class="mt-4 flex items-center justify-between flex-wrap gap-3 pt-3 border-t border-slate-100">
                            @if($spr->payment_scheme === 'kpr')
                                <span class="text-xs text-slate-500 italic">
                                    Catatan: Pembayaran angsuran KPR dibayarkan langsung oleh debitur ke Bank rekanan penyedia KPR.
                                </span>
                            @elseif($spr->payment_scheme === 'mitra_pintar')
                                <span class="text-xs text-amber-800 font-medium">
                                    <svg class="w-4 h-4 inline-block text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Catatan: Pelunasan unit dicairkan oleh Lembaga Mitra Pintar ke rekening pengembang. Pembayaran angsuran dilakukan langsung melalui instansi mitra (payroll/potong gaji).
                                </span>
                            @else
                                <a href="{{ route('kpr-payment.create', $spr->id) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold shadow-sm transition flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 003.75 19.5z" /></svg>
                                    Bayar Termin / Pelunasan Sekarang
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

<!-- Decision Modal for Tidak Layak -->
<div id="decisionModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-xl border border-slate-200">
        <h3 class="text-base font-bold text-slate-900 mb-1">Pilihan Solusi Pembiayaan</h3>
        <p class="text-xs text-slate-500 mb-4">Hasil verifikasi awal belum memenuhi skema KPR Bank reguler. Anda dapat memilih opsi alternatif berikut untuk melanjutkan kepemilikan unit:</p>
        <form id="decisionForm" action="{{ route('kpr.make-decision', 0) }}" method="POST">
            @csrf
            <input type="hidden" id="screeningId" name="screening_id" value="">
            <div class="space-y-2.5 mb-4 text-xs">
                <label class="flex items-center p-3 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 transition">
                    <input type="radio" name="decision" value="switch_to_cash" class="mr-3 text-blue-600" checked>
                    <div>
                        <span class="font-bold text-slate-800 block">Alih ke Pembayaran Tunai (Cash / Termin)</span>
                        <span class="text-slate-400">Pelunasan mandiri tanpa pengajuan bank</span>
                    </div>
                </label>
                <label class="flex items-center p-3 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 transition">
                    <input type="radio" name="decision" value="switch_to_mitra_pintar" class="mr-3 text-blue-600">
                    <div>
                        <span class="font-bold text-slate-800 block">Alih ke Skema Mitra Pintar</span>
                        <span class="text-slate-400">Kerja sama instansi / lembaga mitra pengembang</span>
                    </div>
                </label>
                <label class="flex items-center p-3 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 transition">
                    <input type="radio" name="decision" value="request_refund" class="mr-3 text-rose-600">
                    <div>
                        <span class="font-bold text-rose-800 block">Pengajuan Pengembalian NUP 100%</span>
                        <span class="text-slate-400">Refund dana jaminan NUP sesuai kebijakan resmi</span>
                    </div>
                </label>
            </div>
            <textarea name="notes" placeholder="Catatan tambahan (opsional)..." class="w-full border border-slate-300 rounded-xl p-2.5 text-xs mb-4 focus:ring-1 focus:ring-blue-500 focus:outline-none" rows="2"></textarea>
            <div class="flex gap-2.5">
                <button type="button" onclick="closeDecisionModal()" class="flex-1 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">Batal</button>
                <button type="submit" class="flex-1 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl transition shadow-sm">Konfirmasi Pilihan</button>
            </div>
        </form>
    </div>
</div>

<!-- Refund Modal -->
<div id="refundModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-xl border border-slate-200">
        <h3 class="text-base font-bold text-slate-900 mb-1">Pengajuan Refund NUP 100%</h3>
        <p class="text-xs text-slate-500 mb-4">Pengajuan pengembalian dana jaminan NUP Anda akan diverifikasi oleh tim keuangan pengembang.</p>
        <form id="refundForm" action="#" method="POST">
            @csrf
            <input type="hidden" id="refundId" name="id" value="">
            <input type="hidden" id="refundType" name="type" value="">
            <textarea name="notes" placeholder="Tuliskan nomor rekening tujuan transfer dan alasan pengajuan refund..." class="w-full border border-slate-300 rounded-xl p-2.5 text-xs mb-4 focus:ring-1 focus:ring-blue-500 focus:outline-none" rows="3" required></textarea>
            <div class="flex gap-2.5">
                <button type="button" onclick="closeRefundModal()" class="flex-1 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">Batal</button>
                <button type="submit" class="flex-1 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl transition shadow-sm">Kirim Pengajuan Refund</button>
            </div>
        </form>
    </div>
</div>

<script>
function showDecisionModal(screeningId) {
    document.getElementById('screeningId').value = screeningId;
    document.getElementById('decisionForm').action = '/kpr/' + screeningId + '/make-decision';
    document.getElementById('decisionModal').classList.remove('hidden');
    document.getElementById('decisionModal').classList.add('flex');
}

function closeDecisionModal() {
    document.getElementById('decisionModal').classList.add('hidden');
    document.getElementById('decisionModal').classList.remove('flex');
}

function showRefundModal(id, type) {
    document.getElementById('refundId').value = id;
    document.getElementById('refundType').value = type;
    if (type === 'nup') {
        document.getElementById('refundForm').action = '/nup/' + id + '/request-refund';
    } else {
        document.getElementById('refundForm').action = '/kpr/' + id + '/request-refund';
    }
    document.getElementById('refundModal').classList.remove('hidden');
    document.getElementById('refundModal').classList.add('flex');
}

function closeRefundModal() {
    document.getElementById('refundModal').classList.add('hidden');
    document.getElementById('refundModal').classList.remove('flex');
}
</script>
@endsection
