<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin - APRO')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif; margin: 0; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="bg-slate-50/80 text-slate-800 antialiased min-h-screen">
    <div class="flex">
        <!-- Sidebar -->
        <aside id="admin-sidebar" class="w-64 bg-white fixed inset-y-0 left-0 h-[100dvh] max-h-screen flex flex-col z-50 transition-transform duration-300 transform -translate-x-full md:translate-x-0 border-r border-slate-200/90 shadow-lg md:shadow-sm">
            <!-- Header -->
            <div class="p-5 border-b border-slate-100 flex-shrink-0">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logo.png') }}" alt="APRO Logo" class="w-8 h-8 object-contain">
                    <div>
                        <h1 class="text-base font-extrabold text-slate-900 tracking-tight leading-none">APRO</h1>
                        <p class="text-[11px] font-medium text-slate-500 mt-1">Admin Panel</p>
                    </div>
                </div>
            </div>
            
            <!-- Navigation (Scrollable) -->
            <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1 custom-scrollbar">
                <div class="px-3 pb-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Menu Utama</div>
                
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium' }}">
                    <svg class="w-4 h-4 {{ request()->routeIs('admin.dashboard') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25a2.25 2.25 0 01-13.5 18v-2.25z" />
                    </svg>
                    <span>Dashboard</span>
                </a>
                
                <a href="{{ route('admin.nup.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition {{ request()->routeIs('admin.nup.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium' }}">
                    <svg class="w-4 h-4 {{ request()->routeIs('admin.nup.*') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                    <span>Manajemen NUP</span>
                </a>

                @if(auth()->user()->isSuperAdmin())
                    <a href="{{ route('admin.promo-codes.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition {{ request()->routeIs('admin.promo-codes.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('admin.promo-codes.*') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 6h.008v.008H6V6z" />
                        </svg>
                        <span>Kode Promo</span>
                    </a>
                @endif
                
                @if(auth()->user()->isSuperAdmin())
                    @php
                        $pendingDevCount = \App\Models\DeveloperCompany::where('status', 'pending')->count();
                    @endphp
                    <a href="{{ route('admin.developers.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm transition {{ request()->routeIs('admin.developers.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.developers.*') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
                            </svg>
                            <span>Mitra Developer</span>
                        </div>
                        @if($pendingDevCount > 0)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500 text-white animate-pulse">
                                {{ $pendingDevCount }}
                            </span>
                        @endif
                    </a>
                @endif

                <a href="{{ route('admin.housing-projects.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition {{ request()->routeIs('admin.housing-projects.*') || request()->routeIs('admin.housing-units.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium' }}">
                    <svg class="w-4 h-4 {{ request()->routeIs('admin.housing-projects.*') || request()->routeIs('admin.housing-units.*') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205l3 1m1.5.5l-1.5-.5" />
                    </svg>
                    <span>Perumahan & Unit</span>
                </a>

                <a href="{{ route('admin.units.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition {{ request()->routeIs('admin.units.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium' }}">
                    <svg class="w-4 h-4 {{ request()->routeIs('admin.units.*') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.042 21.672L13.684 16.6m0 0l-2.51 2.225.569-9.47 5.227 7.917-3.286-.672zm-7.518-.267A8.25 8.25 0 1120.25 10.5M8.288 14.212A5.25 5.25 0 1117.25 10.5" />
                    </svg>
                    <span>Pemilihan Unit</span>
                </a>

                <a href="{{ route('admin.utk-payments.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition {{ request()->routeIs('admin.utk-payments.*') || request()->routeIs('utk.admin') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium' }}">
                    <svg class="w-4 h-4 {{ request()->routeIs('admin.utk-payments.*') || request()->routeIs('utk.admin') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Pembayaran UTK</span>
                </a>

                <a href="{{ route('admin.kpr-payments.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition {{ request()->routeIs('admin.kpr-payments.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium' }}">
                    <svg class="w-4 h-4 {{ request()->routeIs('admin.kpr-payments.*') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                    </svg>
                    <span>Pembayaran Tunai & Termin</span>
                </a>

                <a href="{{ route('admin.bank-accounts.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition {{ request()->routeIs('admin.bank-accounts.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium' }}">
                    <svg class="w-4 h-4 {{ request()->routeIs('admin.bank-accounts.*') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.5m-15 10.5V10.5m0-1.5h18" />
                    </svg>
                    <span>Rekening Bank Developer</span>
                </a>
                
                <a href="{{ route('admin.kpr.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition {{ request()->routeIs('admin.kpr.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium' }}">
                    <svg class="w-4 h-4 {{ request()->routeIs('admin.kpr.*') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                    </svg>
                    <span>Screening KPR</span>
                </a>
                
                <a href="{{ route('admin.spr.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition {{ request()->routeIs('admin.spr.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium' }}">
                    <svg class="w-4 h-4 {{ request()->routeIs('admin.spr.*') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.09 1.976 1.053 1.976 2.188V18.75a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 18.75V6.108c0-1.135.845-2.098 1.976-2.188.373-.03.748-.057 1.124-.08" />
                    </svg>
                    <span>Manajemen SPR</span>
                </a>

                <a href="{{ route('admin.brokers.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition {{ request()->routeIs('admin.brokers.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium' }}">
                    <svg class="w-4 h-4 {{ request()->routeIs('admin.brokers.*') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                    </svg>
                    <span>Broker & Marketing (Master TTD)</span>
                </a>

                <!-- Keluar (Logout) Menu Link -->
                <div class="pt-3 border-t border-slate-100">
                    <form action="{{ route('logout') }}" method="POST" class="w-full">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-rose-600 hover:text-rose-700 hover:bg-rose-50 transition text-left">
                            <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                            </svg>
                            <span>Keluar (Logout)</span>
                        </button>
                    </form>
                </div>
            </nav>
            
            <!-- User Profile & Logout at Bottom -->
            <div class="p-3.5 border-t border-slate-100 flex-shrink-0 flex items-center justify-between bg-slate-50/50">
                <div class="flex items-center gap-2.5 min-w-0 pr-2">
                    <div class="w-8 h-8 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center text-xs shrink-0 shadow-sm">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="font-bold text-slate-800 text-xs truncate">{{ auth()->user()->name }}</p>
                        @if(auth()->user()->company)
                            <p class="text-blue-700 text-[10px] font-semibold truncate">{{ auth()->user()->company->company_name }}</p>
                        @else
                            <p class="text-slate-400 text-[10px] font-medium truncate">
                                {{ auth()->user()->role === 'super_admin' ? 'Super Admin' : (auth()->user()->role === 'operator' ? 'Operator' : 'Admin') }}
                            </p>
                        @endif
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="shrink-0">
                    @csrf
                    <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Logout">
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
            <header class="bg-white border-b border-slate-200/80 sticky top-0 z-30 shadow-xs">
                <div class="px-6 py-3.5">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <!-- Toggle Button (Mobile) -->
                            <button id="sidebar-toggle" class="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg focus:outline-none md:hidden">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                                </svg>
                            </button>
                            <h2 class="text-lg font-bold text-slate-900 tracking-tight">@yield('title')</h2>
                        </div>
                        <div class="flex items-center space-x-3">
                            <span class="text-xs font-medium text-slate-500 hidden sm:inline-flex items-center gap-1.5 bg-slate-50 px-3 py-1 rounded-full border border-slate-200">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ now()->format('d F Y H:i') }}
                            </span>

                            <!-- Lonceng Notifikasi Transaksi Masuk -->
                            @php
                                $adminNotifCount = \App\Services\AdminNotificationService::getPendingCount();
                                $adminNotifications = \App\Services\AdminNotificationService::getPendingNotifications(8);
                                $adminRecentNotifications = \App\Services\AdminNotificationService::getRecentCompletedNotifications(4);
                            @endphp
                            <div class="relative" id="admin-notif-container">
                                <button id="admin-notif-btn" type="button" 
                                        class="relative p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition focus:outline-none"
                                        aria-label="Notifikasi Pembayaran">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0M3.124 7.5A8.969 8.969 0 015.292 3m13.416 0a8.969 8.969 0 012.168 4.5" />
                                    </svg>
                                    <span id="admin-notif-badge" class="{{ $adminNotifCount > 0 ? '' : 'hidden' }} absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 bg-rose-600 text-white text-[10px] font-extrabold rounded-full flex items-center justify-center ring-2 ring-white animate-pulse">
                                        {{ $adminNotifCount > 99 ? '99+' : $adminNotifCount }}
                                    </span>
                                </button>

                                <!-- Dropdown Panel Notifikasi -->
                                <div id="admin-notif-dropdown" class="hidden absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-2xl shadow-xl border border-slate-200/90 z-50 overflow-hidden transform origin-top-right transition-all">
                                    <!-- Header -->
                                    <div class="px-4 py-3 bg-slate-50/80 border-b border-slate-100 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full {{ $adminNotifCount > 0 ? 'bg-amber-500 animate-pulse' : 'bg-emerald-500' }}"></span>
                                            <h4 class="font-bold text-xs text-slate-800">Notifikasi Transaksi Masuk</h4>
                                        </div>
                                        <span id="admin-notif-counter-text" class="text-[10px] px-2 py-0.5 rounded-full font-bold {{ $adminNotifCount > 0 ? 'bg-amber-100 text-amber-800 border border-amber-200/80' : 'bg-slate-100 text-slate-500' }}">
                                            {{ $adminNotifCount }} menunggu
                                        </span>
                                    </div>

                                    <!-- List Container -->
                                    <div id="admin-notif-list" class="max-h-96 overflow-y-auto divide-y divide-slate-100 custom-scrollbar">
                                        @if($adminNotifications->isNotEmpty())
                                            <div class="px-3.5 py-1.5 bg-amber-50/80 border-b border-amber-100/60 text-[10px] font-bold text-amber-800 uppercase tracking-wider flex items-center justify-between">
                                                <span>Menunggu Tindakan ({{ $adminNotifications->count() }})</span>
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
                                            </div>
                                            @foreach($adminNotifications as $notif)
                                                <a href="{{ $notif['url'] }}" class="block p-3.5 hover:bg-slate-50 transition group">
                                                    <div class="flex items-start gap-3">
                                                        <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 mt-0.5
                                                            @if($notif['category'] === 'kpr') bg-blue-50 text-blue-600 border border-blue-200/60
                                                            @elseif($notif['category'] === 'utk') bg-emerald-50 text-emerald-600 border border-emerald-200/60
                                                            @elseif($notif['category'] === 'nup') bg-amber-50 text-amber-600 border border-amber-200/60
                                                            @elseif($notif['category'] === 'mitra') bg-amber-50 text-amber-700 border border-amber-200/60
                                                            @elseif($notif['category'] === 'screening') bg-indigo-50 text-indigo-600 border border-indigo-200/60
                                                            @else bg-purple-50 text-purple-600 border border-purple-200/60 @endif">
                                                            @if($notif['category'] === 'kpr')
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 003.75 19.5z"/></svg>
                                                            @elseif($notif['category'] === 'utk')
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                            @elseif($notif['category'] === 'nup')
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                                            @elseif($notif['category'] === 'mitra')
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                                                            @elseif($notif['category'] === 'screening')
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                                                            @else
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                            @endif
                                                        </div>
                                                        <div class="flex-1 min-w-0">
                                                            <div class="flex items-center justify-between gap-1 mb-0.5">
                                                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded {{ $notif['badge_color'] }}">{{ $notif['badge'] }}</span>
                                                                <span class="text-[10px] text-slate-400">{{ $notif['time_human'] }}</span>
                                                            </div>
                                                            <p class="text-xs font-bold text-slate-800 truncate group-hover:text-blue-600 transition">{{ $notif['title'] }}</p>
                                                            <p class="text-[11px] text-slate-500 truncate">
                                                                <strong class="text-slate-700">{{ $notif['user_name'] }}</strong> &middot; {!! $notif['description'] !!}
                                                            </p>
                                                        </div>
                                                        <svg class="w-4 h-4 text-slate-300 group-hover:text-blue-600 transition shrink-0 self-center" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                                                    </div>
                                                </a>
                                            @endforeach
                                        @endif

                                        @if($adminRecentNotifications->isNotEmpty())
                                            <div class="px-3.5 py-1.5 bg-slate-100/70 border-b border-slate-200/50 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                                                Transaksi Terakhir Masuk
                                            </div>
                                            @foreach($adminRecentNotifications as $notif)
                                                <a href="{{ $notif['url'] }}" class="block p-3.5 hover:bg-slate-50 transition group opacity-90 hover:opacity-100">
                                                    <div class="flex items-start gap-3">
                                                        <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 mt-0.5
                                                            {{ $notif['category'] === 'kpr' ? 'bg-blue-50 text-blue-600 border border-blue-200/60' : 'bg-emerald-50 text-emerald-600 border border-emerald-200/60' }}">
                                                            @if($notif['category'] === 'kpr')
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 003.75 19.5z"/></svg>
                                                            @else
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                            @endif
                                                        </div>
                                                        <div class="flex-1 min-w-0">
                                                            <div class="flex items-center justify-between gap-1 mb-0.5">
                                                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded {{ $notif['badge_color'] }}">{{ $notif['badge'] }}</span>
                                                                <span class="text-[10px] text-slate-400">{{ $notif['time_human'] }}</span>
                                                            </div>
                                                            <p class="text-xs font-bold text-slate-800 truncate group-hover:text-blue-600 transition">{{ $notif['title'] }}</p>
                                                            <p class="text-[11px] text-slate-500 truncate">
                                                                <strong class="text-slate-700">{{ $notif['user_name'] }}</strong> &middot; {!! $notif['description'] !!}
                                                            </p>
                                                        </div>
                                                        <svg class="w-4 h-4 text-slate-300 group-hover:text-blue-600 transition shrink-0 self-center" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                                                    </div>
                                                </a>
                                            @endforeach
                                        @endif

                                        @if($adminNotifications->isEmpty() && $adminRecentNotifications->isEmpty())
                                            <div class="py-8 px-4 text-center text-slate-400">
                                                <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                </div>
                                                <p class="text-xs font-bold text-slate-700">Semua Beres!</p>
                                                <p class="text-[11px] text-slate-400 mt-0.5">Tidak ada pembayaran atau aktivitas transaksi baru saat ini.</p>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Footer Shortcuts -->
                                    <div class="p-2.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-[11px]">
                                        <a href="{{ route('admin.kpr-payments.index') }}" class="font-bold text-blue-600 hover:text-blue-800 transition">
                                            Semua Pembayaran &rarr;
                                        </a>
                                        <a href="{{ route('admin.utk-payments.index') }}" class="font-semibold text-slate-500 hover:text-slate-800 transition">
                                            Verifikasi UTK &rarr;
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Tombol Keluar Cepat (Mobile Admin Header) -->
                            <form action="{{ route('logout') }}" method="POST" class="inline-block md:hidden">
                                @csrf
                                <button type="submit" class="flex items-center gap-1 px-2.5 py-1.5 text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-lg transition" title="Keluar">
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
            
            <!-- Page Content -->
            <div class="p-4 sm:p-6">
                @yield('content')
            </div>
        </main>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn = document.getElementById('sidebar-toggle');
            const sidebar = document.getElementById('admin-sidebar');
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

            // Admin Notification Dropdown & Polling
            const notifBtn = document.getElementById('admin-notif-btn');
            const notifDropdown = document.getElementById('admin-notif-dropdown');
            const notifContainer = document.getElementById('admin-notif-container');

            if (notifBtn && notifDropdown) {
                notifBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    notifDropdown.classList.toggle('hidden');
                });

                document.addEventListener('click', (e) => {
                    if (notifContainer && !notifContainer.contains(e.target)) {
                        notifDropdown.classList.add('hidden');
                    }
                });

                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') {
                        notifDropdown.classList.add('hidden');
                    }
                });

                // Helper to render notification item dynamically
                function renderNotifItem(item) {
                    let iconSvg = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                    let iconColor = 'bg-purple-50 text-purple-600 border border-purple-200/60';

                    if (item.category === 'kpr') {
                        iconSvg = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 003.75 19.5z"/></svg>';
                        iconColor = 'bg-blue-50 text-blue-600 border border-blue-200/60';
                    } else if (item.category === 'utk') {
                        iconSvg = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                        iconColor = 'bg-emerald-50 text-emerald-600 border border-emerald-200/60';
                    } else if (item.category === 'nup') {
                        iconSvg = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>';
                        iconColor = 'bg-amber-50 text-amber-600 border border-amber-200/60';
                    } else if (item.category === 'mitra') {
                        iconSvg = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>';
                        iconColor = 'bg-amber-50 text-amber-700 border border-amber-200/60';
                    } else if (item.category === 'screening') {
                        iconSvg = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>';
                        iconColor = 'bg-indigo-50 text-indigo-600 border border-indigo-200/60';
                    }

                    return `
                        <a href="${item.url}" class="block p-3.5 hover:bg-slate-50 transition group">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 mt-0.5 ${iconColor}">
                                    ${iconSvg}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-1 mb-0.5">
                                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded ${item.badge_color}">${item.badge}</span>
                                        <span class="text-[10px] text-slate-400">${item.time_human}</span>
                                    </div>
                                    <p class="text-xs font-bold text-slate-800 truncate group-hover:text-blue-600 transition">${item.title}</p>
                                    <p class="text-[11px] text-slate-500 truncate">
                                        <strong class="text-slate-700">${item.user_name}</strong> &middot; ${item.description}
                                    </p>
                                </div>
                                <svg class="w-4 h-4 text-slate-300 group-hover:text-blue-600 transition shrink-0 self-center" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                            </div>
                        </a>
                    `;
                }

                // Realtime Polling every 20 seconds
                setInterval(async () => {
                    try {
                        const res = await fetch('{{ route('admin.notifications.feed') }}', {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        if (!res.ok) return;
                        const data = await res.json();
                        
                        const badge = document.getElementById('admin-notif-badge');
                        const counterText = document.getElementById('admin-notif-counter-text');
                        const list = document.getElementById('admin-notif-list');
                        
                        if (badge) {
                            if (data.count > 0) {
                                badge.textContent = data.count > 99 ? '99+' : data.count;
                                badge.classList.remove('hidden');
                            } else {
                                badge.classList.add('hidden');
                            }
                        }
                        if (counterText) {
                            counterText.textContent = data.count + ' menunggu';
                            if (data.count > 0) {
                                counterText.className = 'text-[10px] px-2 py-0.5 rounded-full font-bold bg-amber-100 text-amber-800 border border-amber-200/80';
                            } else {
                                counterText.className = 'text-[10px] px-2 py-0.5 rounded-full font-bold bg-slate-100 text-slate-500';
                            }
                        }
                        if (list) {
                            let html = '';
                            if (data.notifications && data.notifications.length > 0) {
                                html += `
                                    <div class="px-3.5 py-1.5 bg-amber-50/80 border-b border-amber-100/60 text-[10px] font-bold text-amber-800 uppercase tracking-wider flex items-center justify-between">
                                        <span>Menunggu Tindakan (${data.notifications.length})</span>
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
                                    </div>
                                `;
                                html += data.notifications.map(renderNotifItem).join('');
                            }

                            if (data.recent && data.recent.length > 0) {
                                html += `
                                    <div class="px-3.5 py-1.5 bg-slate-100/70 border-b border-slate-200/50 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                                        Transaksi Terakhir Masuk
                                    </div>
                                `;
                                html += data.recent.map(renderNotifItem).join('');
                            }

                            if (!html) {
                                html = `
                                    <div class="py-8 px-4 text-center text-slate-400">
                                        <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </div>
                                        <p class="text-xs font-bold text-slate-700">Semua Beres!</p>
                                        <p class="text-[11px] text-slate-400 mt-0.5">Tidak ada pembayaran atau aktivitas transaksi baru saat ini.</p>
                                    </div>
                                `;
                            }

                            list.innerHTML = html;
                        }
                    } catch (err) {
                        // silent fail on network glitch
                    }
                }, 20000);
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>
