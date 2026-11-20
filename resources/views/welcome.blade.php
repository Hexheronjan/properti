<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APRO - Sistem Pemilihan Unit Perumahan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', 'Inter', sans-serif; }
    </style>
</head>
<body class="relative min-h-screen flex flex-col items-center justify-between p-4 sm:p-6 text-slate-800 antialiased overflow-x-hidden bg-slate-100">

    <!-- BACKGROUND PREMIUM (Cerah, Segar, Berkelas, Arsitektural) -->
    <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden">
        <!-- Foto Lanskap Kawasan Perumahan Cerah Siang Hari -->
        <img src="https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=2000&q=80" 
             alt="Kawasan Perumahan Cerah" 
             class="w-full h-full object-cover opacity-20 filter blur-[3px] scale-105">

        <!-- Gradasi Latar Cerah & Mewah -->
        <div class="absolute inset-0 bg-gradient-to-tr from-slate-100/90 via-sky-50/70 to-blue-50/80"></div>

        <!-- Pola Blueprint Grid Arsitektural Halus -->
        <div class="absolute inset-0 bg-[radial-gradient(#94a3b8_1px,transparent_1px)] [background-size:20px_20px] opacity-25"></div>

        <!-- Ambient Glow Orbs Bernuansa Terang & Hangat -->
        <div class="absolute -top-24 -left-24 w-[480px] h-[480px] bg-sky-300/30 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-24 -right-24 w-[500px] h-[500px] bg-blue-300/25 rounded-full blur-3xl"></div>
        <div class="absolute top-1/3 right-1/4 w-[360px] h-[360px] bg-emerald-200/30 rounded-full blur-3xl"></div>
    </div>

    <!-- TOP BAR FLOATING BADGE (Bersih & Elegan) -->
    <div class="relative z-10 w-full max-w-4xl flex items-center justify-between py-2 sm:py-3 mb-2">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/80 backdrop-blur-md border border-slate-200/80 text-slate-700 text-xs font-bold shadow-sm">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>APRO Portal Properti & Perumahan Resmi</span>
        </div>
        <div class="hidden sm:flex items-center gap-2.5 text-xs font-semibold text-slate-500">
            <span>Kaveling Siap Huni</span>
            <span class="text-slate-300">•</span>
            <span>Antrean NUP Real-Time</span>
            <span class="text-slate-300">•</span>
            <span>Penerbitan SPR</span>
        </div>
    </div>

    <!-- MAIN HORIZONTAL SPLIT CARD (Landscape Mewah & Seimbang) -->
    <div class="relative z-10 max-w-4xl w-full bg-white/95 backdrop-blur-xl rounded-[2.25rem] shadow-[0_20px_60px_-15px_rgba(15,23,42,0.15)] border border-slate-200/90 overflow-hidden my-auto transition-all">
        <div class="grid grid-cols-1 md:grid-cols-12 min-h-[520px]">
            
            <!-- SISI KIRI: Foto Rumah Tropis Modern Cerah di Atas Tanah Hijau Asri -->
            <div class="md:col-span-5 relative overflow-hidden bg-slate-900 flex flex-col justify-between p-6 sm:p-7 min-h-[280px] md:min-h-full">
                <!-- Foto Rumah Cerah Siang Hari dengan Halaman Rumput Hijau -->
                <img src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=1200&q=80" 
                     alt="Rumah Tropis Modern APRO" 
                     class="absolute inset-0 w-full h-full object-cover scale-105 pointer-events-none">

                <!-- Soft Gradient Overlay (Tidak Gelap Pekat, Tetap Menjaga Kejernihan Foto) -->
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/85 via-slate-900/25 to-slate-900/20"></div>

                <!-- Lengkungan Transisi Halus ke Sisi Putih Form (Desktop: Kurva Kanan, Mobile: Kurva Bawah) -->
                <div class="hidden md:block absolute right-0 inset-y-0 w-10 overflow-hidden pointer-events-none text-white z-10">
                    <svg class="h-full w-full" viewBox="0 0 20 100" preserveAspectRatio="none">
                        <path d="M0,0 C12,30 12,70 0,100 L20,100 L20,0 Z" fill="currentColor"></path>
                    </svg>
                </div>
                <div class="md:hidden absolute bottom-0 inset-x-0 h-6 overflow-hidden pointer-events-none text-white z-10">
                    <svg class="w-full h-full" viewBox="0 0 100 20" preserveAspectRatio="none">
                        <path d="M0,20 C30,6 70,6 100,20 Z" fill="currentColor"></path>
                    </svg>
                </div>

                <!-- Floating Badge Atas -->
                <div class="relative z-20">
                    <div class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-white/95 backdrop-blur-md rounded-full shadow text-xs font-bold text-slate-800 border border-white/60">
                        <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>Kaveling & Unit Hunian Resmi</span>
                    </div>
                </div>

                <!-- Floating Glass Card Bawah -->
                <div class="relative z-20 mt-12 md:mt-0 p-4 bg-slate-950/65 backdrop-blur-md rounded-2xl border border-white/20 text-white shadow-lg">
                    <div class="flex items-center justify-between gap-2 mb-1.5">
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-sky-400">Cluster Asri & Modern</span>
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-400/40">Siap Huni & Indent</span>
                    </div>
                    <h3 class="text-sm font-bold text-white tracking-tight">Kawasan Hunian Asri & Bernilai Investasi</h3>
                    <p class="text-[11px] text-slate-300 mt-1 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-sky-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Akses Strategis, Jalan Paving & Bebas Banjir
                    </p>
                </div>
            </div>

            <!-- SISI KANAN: Konten, Aksi & Fitur Terstruktur -->
            <div class="md:col-span-7 p-6 sm:p-8 lg:p-9 flex flex-col justify-between relative bg-white">
                <div>
                    <!-- Header Logo & Nama Platform -->
                    <div class="flex items-center gap-3.5 mb-4">
                        <div class="w-12 h-12 bg-white rounded-2xl shadow-sm border border-slate-200/90 p-1.5 flex items-center justify-center shrink-0">
                            <img src="{{ asset('images/logo.png') }}" alt="APRO Logo" class="w-full h-full object-contain">
                        </div>
                        <div>
                            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight leading-none">
                                APRO System
                            </h1>
                            <p class="text-xs text-slate-500 font-semibold mt-1">
                                Sistem Pemilihan Unit & Administrasi Perumahan
                            </p>
                        </div>
                    </div>

                    <!-- Flash Alert (Logout / Success / Error) -->
                    @if(session('success'))
                        <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xl flex items-center gap-2 shadow-sm font-medium">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-4 p-3 bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-xl flex items-center gap-2 shadow-sm font-medium">
                            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            <span>{{ session('error') }}</span>
                        </div>
                    @endif

                    <!-- Kotak Sambutan Bergradien Lembut -->
                    <div class="p-4 bg-gradient-to-r from-blue-50/80 to-indigo-50/60 border border-blue-100 rounded-2xl mb-4">
                        <div class="flex items-center gap-2 mb-1">
                            <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <h2 class="text-xs font-bold text-blue-950 uppercase tracking-wide">Selamat Datang di Portal APRO</h2>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Sistem resmi pemilihan kaveling dan unit perumahan berbasis nomor antrian (NUP). Daftarkan diri Anda untuk mengunci unit dan melanjutkan pemesanan rumah impian secara transparan.
                        </p>
                    </div>

                    <!-- Tombol Aksi (Hierarki Jelas & Elegan) -->
                    <div class="mb-5">
                        @guest
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <!-- Tombol Utama (Primary Gradient) -->
                                <a href="{{ route('login') }}" class="py-3 px-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 active:from-blue-800 active:to-indigo-800 text-white text-xs sm:text-sm font-bold rounded-xl shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                                    <span>Masuk ke Akun</span>
                                </a>

                                <!-- Tombol Kedua (Secondary Clean) -->
                                <a href="{{ route('register') }}" class="py-3 px-4 bg-slate-50 hover:bg-slate-100 active:bg-slate-200 text-slate-800 text-xs sm:text-sm font-bold rounded-xl border border-slate-200/90 shadow-sm hover:shadow transition-all flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                    <span>Daftar Konsumen</span>
                                </a>
                            </div>
                        @endguest

                        @auth
                            <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl mb-3 flex items-center justify-between gap-2">
                                <div>
                                    <p class="text-[11px] text-slate-500">Masuk sebagai:</p>
                                    <p class="text-xs font-bold text-slate-900 truncate">{{ Auth::user()->name }} ({{ Auth::user()->email }})</p>
                                </div>
                                <form action="{{ route('logout') }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="py-1.5 px-3 bg-white hover:bg-rose-50 hover:text-rose-600 text-slate-600 text-xs font-bold rounded-lg border border-slate-200 transition">
                                        Logout
                                    </button>
                                </form>
                            </div>

                            @if(Auth::user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="w-full py-3 px-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-md transition flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span>Buka Panel Super Admin</span>
                                </a>
                            @else
                                <a href="{{ route('dashboard') }}" class="w-full py-3 px-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-md transition flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                    <span>Buka Dashboard Saya</span>
                                </a>
                            @endif
                        @endauth
                    </div>

                    <!-- Layanan & Fasilitas Terpadu (Penyajian Modern & Clean) -->
                    <div class="pt-3 border-t border-slate-100">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-2">
                            Layanan & Fasilitas Terpadu
                        </span>

                        <div class="space-y-2 text-xs">
                            <div class="flex items-start gap-2.5 p-1 rounded-lg hover:bg-slate-50 transition">
                                <div class="w-5 h-5 rounded-md bg-emerald-50 text-emerald-600 border border-emerald-200/80 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <div>
                                    <span class="font-bold text-slate-800 text-[11px] block">Katalog Kaveling & Blok Real-Time</span>
                                    <span class="text-[10px] text-slate-500">Pilih unit perumahan sesuai denah kavling yang tersedia secara transparan.</span>
                                </div>
                            </div>

                            <div class="flex items-start gap-2.5 p-1 rounded-lg hover:bg-slate-50 transition">
                                <div class="w-5 h-5 rounded-md bg-emerald-50 text-emerald-600 border border-emerald-200/80 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <div>
                                    <span class="font-bold text-slate-800 text-[11px] block">Penerbitan Dokumen SPR & BAST Otomatis</span>
                                    <span class="text-[10px] text-slate-500">Penerbitan berkas pemesanan rumah dan berita acara digital resmi.</span>
                                </div>
                            </div>

                            <div class="flex items-start gap-2.5 p-1 rounded-lg hover:bg-slate-50 transition">
                                <div class="w-5 h-5 rounded-md bg-emerald-50 text-emerald-600 border border-emerald-200/80 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <div>
                                    <span class="font-bold text-slate-800 text-[11px] block">Sistem Antrian NUP Terstruktur</span>
                                    <span class="text-[10px] text-slate-500">Alur pemilihan unit teratur dengan verifikasi berkas perbankan/tunai aman.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Card -->
                <div class="mt-5 pt-3 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-2 text-[11px] text-slate-500">
                    <div class="flex items-center gap-2">
                        <span>Mitra Pengembang?</span>
                        <a href="{{ route('developer.register') }}" class="font-bold text-blue-600 hover:text-blue-800 hover:underline">
                            Daftar Developer
                        </a>
                        <span>•</span>
                        <a href="{{ route('developer.status') }}" class="font-semibold text-slate-600 hover:text-slate-800 hover:underline">
                            Cek Status
                        </a>
                    </div>
                    <span class="text-slate-400">&copy; {{ date('Y') }} APRO System</span>
                </div>
            </div>

        </div>
    </div>

    <!-- SUBTLE COPYRIGHT FOOTER -->
    <div class="relative z-10 py-2 text-center text-[11px] text-slate-500 font-medium">
        &copy; {{ date('Y') }} APRO System. Seluruh hak cipta dilindungi undang-undang.
    </div>

</body>
</html>
