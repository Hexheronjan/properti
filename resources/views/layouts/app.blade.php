<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'APRO')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif; }
    </style>
</head>
<body class="bg-slate-50/80 text-slate-800 antialiased min-h-screen">
    @auth
        <div class="flex">
            <!-- Sidebar -->
            <aside id="member-sidebar" class="w-64 bg-white fixed inset-y-0 left-0 h-[100dvh] max-h-screen flex flex-col z-50 transition-transform duration-300 transform -translate-x-full md:translate-x-0 border-r border-slate-200/90 shadow-lg md:shadow-sm">
                <!-- Header -->
                <div class="p-5 border-b border-slate-100 flex-shrink-0">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/logo.png') }}" alt="APRO Logo" class="w-8 h-8 object-contain">
                        <div>
                            <h1 class="text-base font-extrabold text-slate-900 tracking-tight leading-none">APRO</h1>
                            <p class="text-[11px] font-medium text-slate-500 mt-1">
                                {{ auth()->user()->isAdmin() ? 'Panel Operator' : 'Portal Peserta' }}
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Navigation (Scrollable) -->
                <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-4">
                    <!-- SECTION 1: RINGKASAN & OVERVIEW (DASHBOARD) -->
                    <div>
                        <div class="px-3 pb-2 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider flex items-center justify-between">
                            <span>Beranda & Ringkasan</span>
                        </div>
                        
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white font-bold shadow-sm shadow-blue-500/25' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 font-medium' }}">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center transition {{ request()->routeIs('admin.dashboard') ? 'bg-white/20 text-white' : 'bg-blue-50 text-blue-600 group-hover:bg-blue-100' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                                    </svg>
                                </div>
                                <span>Dashboard Admin</span>
                            </a>
                        @else
                            <a href="{{ route('dashboard') }}" class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white font-bold shadow-sm shadow-blue-500/25' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 font-medium' }}">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center transition {{ request()->routeIs('dashboard') ? 'bg-white/20 text-white' : 'bg-blue-50 text-blue-600 group-hover:bg-blue-100' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25a2.25 2.25 0 0113.5 18v-2.25z" />
                                    </svg>
                                </div>
                                <span>Dashboard Saya</span>
                            </a>
                        @endif
                    </div>

                    <!-- SECTION 2: LAYANAN PEMESANAN (BAGIAN LAINNYA) -->
                    <div class="space-y-1">
                        <div class="px-3 pb-2 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                            Layanan Pemesanan
                        </div>

                        <!-- Daftar NUP (Amber Theme) -->
                        <a href="{{ route('nup.register') }}" class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition {{ request()->routeIs('nup.register') || request()->routeIs('nup.show') ? 'bg-amber-500 text-white font-bold shadow-sm shadow-amber-500/25' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 font-medium' }}">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center transition {{ request()->routeIs('nup.register') || request()->routeIs('nup.show') ? 'bg-white/20 text-white' : 'bg-amber-50 text-amber-600 group-hover:bg-amber-100' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                            </div>
                            <span>Daftar NUP</span>
                        </a>

                        <!-- Lihat & Pilih Unit (Indigo Theme) -->
                        <a href="{{ route('units.index') }}" class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition {{ request()->routeIs('units.index') || request()->routeIs('units.create') || request()->routeIs('units.show') ? 'bg-indigo-600 text-white font-bold shadow-sm shadow-indigo-500/25' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 font-medium' }}">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center transition {{ request()->routeIs('units.index') || request()->routeIs('units.create') || request()->routeIs('units.show') ? 'bg-white/20 text-white' : 'bg-indigo-50 text-indigo-600 group-hover:bg-indigo-100' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                                </svg>
                            </div>
                            <span>Lihat & Pilih Unit</span>
                        </a>

                        <!-- Antrian Unit (Purple Theme) -->
                        <a href="{{ route('units.queue') }}" class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition {{ request()->routeIs('units.queue') ? 'bg-purple-600 text-white font-bold shadow-sm shadow-purple-500/25' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 font-medium' }}">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center transition {{ request()->routeIs('units.queue') ? 'bg-white/20 text-white' : 'bg-purple-50 text-purple-600 group-hover:bg-purple-100' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                </svg>
                            </div>
                            <span>Antrian Unit</span>
                        </a>

                        @if(auth()->user()->isAdmin() || auth()->user()->kprScreenings()->exists())
                            <!-- Screening KPR (Teal Theme) -->
                            <a href="/screening" class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition {{ request()->is('screening*') ? 'bg-teal-600 text-white font-bold shadow-sm shadow-teal-500/25' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 font-medium' }}">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center transition {{ request()->is('screening*') ? 'bg-white/20 text-white' : 'bg-teal-50 text-teal-600 group-hover:bg-teal-100' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                    </svg>
                                </div>
                                <span>Screening KPR</span>
                            </a>
                        @endif
                    </div>

                    @if(auth()->user()->isAdmin())
                        <div class="space-y-1 pt-2 border-t border-slate-100">
                            <div class="px-3 pb-2 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Operator Developer</div>
                            <a href="{{ route('admin.dashboard') }}" class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition {{ request()->routeIs('admin.dashboard') ? 'bg-slate-800 text-white font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 font-medium' }}">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center transition bg-slate-100 text-slate-600 group-hover:bg-slate-200">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
                                    </svg>
                                </div>
                                <span>Admin Panel</span>
                            </a>
                            <a href="{{ route('admin.spr-clauses.index') }}" class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition {{ request()->routeIs('admin.spr-clauses.*') ? 'bg-slate-800 text-white font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 font-medium' }}">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center transition bg-slate-100 text-slate-600 group-hover:bg-slate-200">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.09 1.976 1.053 1.976 2.188V18.75a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 18.75V6.108c0-1.135.845-2.098 1.976-2.188.373-.03.748-.057 1.124-.08" />
                                    </svg>
                                </div>
                                <span>Kelola Klausul SPR</span>
                            </a>
                        </div>
                    @endif

                    <!-- SECTION 3: AKUN & KELUAR -->
                    <div class="space-y-1 pt-3 border-t border-slate-100">
                        <div class="px-3 pb-1 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                            Akun
                        </div>
                        <form action="{{ route('logout') }}" method="POST" class="w-full">
                            @csrf
                            <button type="submit" class="w-full group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-rose-600 hover:text-rose-700 hover:bg-rose-50 transition text-left">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center bg-rose-50 text-rose-600 group-hover:bg-rose-100 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                                    </svg>
                                </div>
                                <span>Keluar (Logout)</span>
                            </button>
                        </form>
                    </div>
                </nav>
                
                <!-- User Profile & Logout at Bottom -->
                <div class="p-3.5 border-t border-slate-100 flex-shrink-0 flex items-center justify-between bg-slate-50/70">
                    <div class="flex items-center gap-2.5 min-w-0 pr-2">
                        <div class="w-8 h-8 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center text-xs shrink-0 shadow-sm">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="font-bold text-slate-800 text-xs truncate">{{ auth()->user()->name }}</p>
                            <p class="text-slate-400 text-[10px] font-medium truncate">
                                @if(auth()->user()->role === 'super_admin') Super Admin
                                @elseif(auth()->user()->isAdmin()) Admin
                                @else Peserta
                                @endif
                            </p>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="shrink-0">
                        @csrf
                        <button type="submit" class="p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition" title="Logout">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                            </svg>
                        </button>
                    </form>
                </div>
            </aside>
            
            <!-- Sidebar Backdrop Overlay (Mobile) -->
            <div id="sidebar-overlay" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-40 hidden md:hidden"></div>

            <!-- Main Content -->
            <main class="flex-1 md:ml-64 min-w-0">
                <!-- Top Header -->
                <header class="bg-white border-b border-slate-200/80 sticky top-0 z-30 shadow-sm">
                    <div class="px-4 sm:px-6 py-3.5">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <!-- Toggle Button (Mobile) -->
                                <button id="sidebar-toggle" class="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg focus:outline-none md:hidden shrink-0" aria-label="Buka Menu">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                                    </svg>
                                </button>
                                <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight truncate">@yield('title')</h2>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span class="text-xs font-medium text-slate-500 hidden sm:inline-flex items-center gap-1.5 bg-slate-50 px-3 py-1 rounded-full border border-slate-200">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ now()->format('d F Y H:i') }}
                                </span>
                                <!-- Tombol Keluar Cepat (Mobile Header) -->
                                <form action="{{ route('logout') }}" method="POST" class="inline-block md:hidden">
                                    @csrf
                                    <button type="submit" class="flex items-center gap-1 px-2.5 py-1.5 text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-lg transition shadow-xs" title="Keluar">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                                        </svg>
                                        <span>Keluar</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="max-w-6xl mx-auto mt-4">
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <p class="text-sm text-green-700">{{ session('success') }}</p>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="max-w-6xl mx-auto mt-4">
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <p class="text-sm text-red-700">{{ session('error') }}</p>
                        </div>
                    </div>
                @endif

                <!-- Page Content -->
                <div class="p-6">
                    @yield('content')
                </div>
            </main>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const toggleBtn = document.getElementById('sidebar-toggle');
                const sidebar = document.getElementById('member-sidebar');
                const overlay = document.getElementById('sidebar-overlay');

                if (toggleBtn && sidebar && overlay) {
                    toggleBtn.addEventListener('click', () => {
                        sidebar.classList.toggle('-translate-x-full');
                        overlay.classList.toggle('hidden');
                    });

                    overlay.addEventListener('click', () => {
                        sidebar.classList.add('-translate-x-full');
                        overlay.classList.add('hidden');
                    });
                }
            });
        </script>
    @else
        <!-- Guest View (Fallback if ever accessed by guest) -->
        <nav class="bg-blue-600 text-white shadow-lg">
            <div class="container mx-auto px-4">
                <div class="flex flex-col sm:flex-row justify-between items-center py-4 gap-2">
                    <div class="flex items-center">
                        <a href="{{ route('home') }}" class="flex items-center gap-2 text-xl font-bold">
                            <img src="{{ asset('images/logo.png') }}" alt="APRO Logo" style="width: 32px; height: 32px;">
                        </a>
                    </div>
                    <div class="flex flex-wrap items-center justify-center gap-3 sm:gap-4">
                        <a href="{{ route('login') }}" class="text-sm font-semibold hover:text-blue-200">Login</a>
                        <a href="{{ route('auth.google') }}" class="text-sm font-semibold bg-white text-blue-600 px-3 py-1.5 rounded-lg hover:bg-blue-50 transition">Login dengan Google</a>
                        <a href="{{ route('register') }}" class="text-sm font-semibold hover:text-blue-200">Register</a>
                    </div>
                </div>
            </div>
        </nav>

        <main class="container mx-auto px-4 py-8">
            @yield('content')
        </main>

        <footer class="bg-gray-800 text-white py-4 mt-8">
            <div class="container mx-auto px-4 text-center">
                <p>&copy; {{ date('Y') }} APRO. Sistem Pemilihan Unit Perumahan.</p>
            </div>
        </footer>
    @endauth

    @stack('scripts')
</body>
</html>