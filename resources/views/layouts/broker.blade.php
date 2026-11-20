<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Portal Mitra Broker') - {{ config('app.name', 'APRO') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col">

    <!-- Top Navbar -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand / Logo -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('broker.dashboard') }}" class="flex items-center gap-2.5">
                        <span class="w-9 h-9 bg-gradient-to-tr from-amber-600 to-amber-500 text-white font-extrabold rounded-xl flex items-center justify-center shadow-md">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </span>
                        <div>
                            <div class="font-extrabold text-slate-900 text-sm tracking-tight leading-tight">Portal Broker</div>
                            <div class="text-[10px] text-amber-700 font-bold uppercase tracking-wider">Mitra Penjualan</div>
                        </div>
                    </a>
                </div>

                <!-- Navigation Links (Desktop) -->
                <nav class="hidden md:flex items-center gap-1 text-xs font-semibold text-slate-600">
                    <a href="{{ route('broker.dashboard') }}" class="px-3 py-2 rounded-lg transition inline-flex items-center gap-1.5 {{ request()->routeIs('broker.dashboard') ? 'bg-amber-50 text-amber-800 font-bold' : 'hover:bg-slate-100' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('broker.units') }}" class="px-3 py-2 rounded-lg transition inline-flex items-center gap-1.5 {{ request()->routeIs('broker.units') ? 'bg-amber-50 text-amber-800 font-bold' : 'hover:bg-slate-100' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span>Stok & Katalog Unit</span>
                    </a>
                    <a href="{{ route('broker.leads') }}" class="px-3 py-2 rounded-lg transition inline-flex items-center gap-1.5 {{ request()->routeIs('broker.leads') ? 'bg-amber-50 text-amber-800 font-bold' : 'hover:bg-slate-100' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span>Calon Pembeli Saya</span>
                    </a>
                    <a href="{{ route('broker.profile') }}" class="px-3 py-2 rounded-lg transition inline-flex items-center gap-1.5 {{ request()->routeIs('broker.profile') ? 'bg-amber-50 text-amber-800 font-bold' : 'hover:bg-slate-100' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span>Profil & TTD</span>
                    </a>
                </nav>

                <!-- User Profile & Logout -->
                <div class="flex items-center gap-3">
                    <div class="hidden sm:block text-right">
                        <p class="font-bold text-xs text-slate-900 leading-tight">{{ auth()->user()->name }}</p>
                        <p class="text-[10px] text-slate-500 font-medium">
                            @if(isset($activeCompany) && $activeCompany)
                                {{ $activeCompany->company_name }}
                            @elseif(isset($partnerships) && $partnerships->count() > 1)
                                Mitra {{ $partnerships->count() }} Pengembang
                            @else
                                {{ auth()->user()->company?->company_name ?? 'Mitra Broker' }}
                            @endif
                        </p>
                    </div>

                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-xl transition shadow-xs" title="Keluar">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                            </svg>
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 mb-16 md:mb-0">
        @if(session('success'))
            <div class="mb-5 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs font-semibold flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-5 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs font-semibold flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-400 mb-16 md:mb-0">
        &copy; {{ date('Y') }} {{ config('app.name', 'APRO') }} &middot; Portal Resmi Mitra Broker & Marketing
    </footer>

    <!-- Mobile Bottom Navigation for Broker -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 z-30 flex items-center justify-around py-1.5 px-1 shadow-lg">
        <a href="{{ route('broker.dashboard') }}" class="flex flex-col items-center py-1 px-2 text-[10px] font-bold {{ request()->routeIs('broker.dashboard') ? 'text-amber-700' : 'text-slate-500 hover:text-slate-800' }}">
            <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            <span>Beranda</span>
        </a>
        <a href="{{ route('broker.units') }}" class="flex flex-col items-center py-1 px-2 text-[10px] font-bold {{ request()->routeIs('broker.units') ? 'text-amber-700' : 'text-slate-500 hover:text-slate-800' }}">
            <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <span>Unit</span>
        </a>
        <a href="{{ route('broker.leads') }}" class="flex flex-col items-center py-1 px-2 text-[10px] font-bold {{ request()->routeIs('broker.leads') ? 'text-amber-700' : 'text-slate-500 hover:text-slate-800' }}">
            <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            <span>Pembeli</span>
        </a>
        <a href="{{ route('broker.profile') }}" class="flex flex-col items-center py-1 px-2 text-[10px] font-bold {{ request()->routeIs('broker.profile') ? 'text-amber-700' : 'text-slate-500 hover:text-slate-800' }}">
            <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <span>Profil</span>
        </a>
        <form action="{{ route('logout') }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="flex flex-col items-center py-1 px-2 text-[10px] font-bold text-rose-600 hover:text-rose-800">
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                </svg>
                <span>Keluar</span>
            </button>
        </form>
    </nav>

</body>
</html>
