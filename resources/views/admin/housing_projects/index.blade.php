@extends('layouts.admin')

@section('title', 'Kelola Perumahan')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span>Kelola Perumahan & Unit</span>
            </h1>
            <p class="text-sm text-gray-600">
                @if(Auth::user()->isOperator() && !Auth::user()->isSuperAdmin())
                    Kawasan perumahan dan unit rumah milik <strong>{{ Auth::user()->company->company_name ?? 'Perusahaan Anda' }}</strong>.
                @else
                    Kelola kawasan proyek perumahan dan stok unit kavling rumah.
                @endif
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.housing-projects.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs sm:text-sm font-bold shadow transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Perumahan</span>
            </a>
            <a href="{{ route('admin.housing-units.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs sm:text-sm font-bold shadow transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>Posting Unit Baru</span>
            </a>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-gray-200 mb-6">
        <a href="{{ route('admin.housing-projects.index') }}" class="px-5 py-3 text-sm font-bold border-b-2 border-blue-600 text-blue-600 bg-blue-50/50 rounded-t-xl transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <span>Kawasan Perumahan ({{ $projects->total() }})</span>
        </a>
        <a href="{{ route('admin.housing-units.index') }}" class="px-5 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-800 hover:border-gray-300 transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span>Semua Unit Rumah (Kavling)</span>
        </a>
    </div>

    @session('success')
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl p-4 mb-6 text-sm">
            {{ session('success') }}
        </div>
    @endsession

    @session('error')
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl p-4 mb-6 text-sm">
            {{ session('error') }}
        </div>
    @endsession

    <!-- Filter Company for Super Admin -->
    @if(Auth::user()->isSuperAdmin() && $companies->isNotEmpty())
        <div class="bg-white p-4 rounded-xl border border-gray-200 mb-6 flex items-center gap-3">
            <span class="text-xs font-bold text-gray-700">Filter Pengembang:</span>
            <form action="{{ route('admin.housing-projects.index') }}" method="GET" class="flex items-center gap-2 flex-1">
                <select name="company_id" onchange="this.form.submit()" class="border border-gray-300 rounded-lg p-2 text-xs focus:outline-none focus:border-blue-600 max-w-xs">
                    <option value="">Semua Pengembang</option>
                    @foreach($companies as $comp)
                        <option value="{{ $comp->id }}" {{ request('company_id') == $comp->id ? 'selected' : '' }}>
                            {{ $comp->company_name }}
                        </option>
                    @endforeach
                </select>
                @if(request('company_id'))
                    <a href="{{ route('admin.housing-projects.index') }}" class="text-xs text-blue-600 hover:underline">Reset</a>
                @endif
            </form>
        </div>
    @endif

    <!-- Grid Projects -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($projects as $proj)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition flex flex-col">
                <div class="h-44 bg-gray-100 relative overflow-hidden">
                    @if($proj->image)
                        <img src="{{ asset('storage/' . $proj->image) }}" alt="{{ $proj->name }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-300 bg-gray-100">
                            <svg class="w-14 h-14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                    @endif
                    <div class="absolute top-3 right-3">
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold shadow-sm {{ $proj->is_active ? 'bg-green-500 text-white' : 'bg-gray-500 text-white' }}">
                            {{ $proj->is_active ? 'Aktif' : 'Non-aktif' }}
                        </span>
                    </div>
                </div>

                <div class="p-5 flex-1 flex flex-col justify-between">
                    <div>
                        @if($proj->company)
                            <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded bg-blue-50 text-blue-800 text-xs font-semibold mb-2">
                                <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <span>{{ $proj->company->company_name }}</span>
                            </div>
                        @endif
                        <h3 class="text-lg font-bold text-gray-900 mb-1">{{ $proj->name }}</h3>
                        <p class="text-xs text-gray-500 mb-3 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>{{ $proj->location }}</span>
                        </p>
                        @if($proj->description)
                            <p class="text-xs text-gray-600 line-clamp-2 mb-3">{{ $proj->description }}</p>
                        @endif
                    </div>

                    <div class="pt-4 border-t border-gray-100 mt-2">
                        <div class="flex items-center justify-between text-xs text-gray-600 mb-3">
                            <span>Total Unit: <strong>{{ $proj->housingUnits->count() }} unit</strong></span>
                            @if($proj->commitment_fee)
                                <span>UTK: <strong>Rp {{ number_format($proj->commitment_fee, 0, ',', '.') }}</strong></span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.housing-units.index', ['project_id' => $proj->id]) }}" class="flex-1 text-center py-2 px-3 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-lg text-xs transition flex items-center justify-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                <span>Unit ({{ $proj->housingUnits->count() }})</span>
                            </a>
                            <a href="{{ route('admin.housing-units.create', ['project_id' => $proj->id]) }}" class="py-2 px-3 bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold rounded-lg text-xs transition inline-flex items-center gap-1" title="Tambah Unit Baru ke Perumahan Ini">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                <span>Unit</span>
                            </a>
                            <a href="{{ route('admin.housing-projects.edit', $proj->id) }}" class="py-2 px-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg text-xs transition inline-flex items-center" title="Edit Perumahan">
                                <svg class="w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center text-gray-500 bg-white rounded-2xl border border-gray-200">
                <div class="w-12 h-12 mx-auto mb-3 bg-gray-100 text-gray-400 rounded-2xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <p class="font-bold text-gray-800 text-base">Belum Ada Perumahan</p>
                <p class="text-xs text-gray-500 mt-1 mb-4">Silakan tambahkan data perumahan pertama Anda.</p>
                <a href="{{ route('admin.housing-projects.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah Perumahan</span>
                </a>
            </div>
        @endforelse
    </div>

    @if($projects->hasPages())
        <div class="mt-6">
            {{ $projects->links() }}
        </div>
    @endif
</div>
@endsection
