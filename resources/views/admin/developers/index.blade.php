@extends('layouts.admin')

@section('title', 'Mitra Pengembang (Developer)')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800"><svg class="w-4 h-4 inline-block text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg> Mitra Pengembang Perumahan</h1>
            <p class="text-sm text-gray-600">Verifikasi dan kelola pendaftaran perusahaan pengembang (developer) & operator.</p>
        </div>
        <div>
            <a href="{{ route('admin.developers.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold rounded-xl shadow-sm hover:shadow transition text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Pengembang Baru
            </a>
        </div>
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

    @session('info')
        <div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-xl p-4 mb-6 text-sm">
            {{ session('info') }}
        </div>
    @endsession

    <!-- Tabs Filter -->
    <div class="flex flex-wrap gap-2 mb-6 border-b border-gray-200 pb-2">
        <a href="{{ route('admin.developers.index') }}" 
           class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $status === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            Semua ({{ $counts['all'] }})
        </a>
        <a href="{{ route('admin.developers.index', ['status' => 'pending']) }}" 
           class="px-4 py-2 rounded-lg text-sm font-semibold transition flex items-center gap-1.5 {{ $status === 'pending' ? 'bg-amber-500 text-white' : 'bg-amber-50 text-amber-800 hover:bg-amber-100' }}">
            <svg class="w-4 h-4 inline-block text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Menunggu Approval
            <span class="px-2 py-0.5 rounded-full text-xs {{ $status === 'pending' ? 'bg-amber-700 text-white' : 'bg-amber-200 text-amber-900' }}">{{ $counts['pending'] }}</span>
        </a>
        <a href="{{ route('admin.developers.index', ['status' => 'approved']) }}" 
           class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $status === 'approved' ? 'bg-green-600 text-white' : 'bg-green-50 text-green-800 hover:bg-green-100' }}">
            <svg class="w-4 h-4 inline-block text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Disetujui ({{ $counts['approved'] }})
        </a>
        <a href="{{ route('admin.developers.index', ['status' => 'rejected']) }}" 
           class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $status === 'rejected' ? 'bg-red-600 text-white' : 'bg-red-50 text-red-800 hover:bg-red-100' }}">
            <svg class="w-4 h-4 inline-block text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg> Ditolak ({{ $counts['rejected'] }})
        </a>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-700">
                <thead class="bg-gray-50 border-b border-gray-200 text-xs font-semibold text-gray-600 uppercase">
                    <tr>
                        <th class="px-6 py-4">Perusahaan / Brand</th>
                        <th class="px-6 py-4">PIC / Operator</th>
                        <th class="px-6 py-4">Bentuk & NIB</th>
                        <th class="px-6 py-4">Kontak</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Proyek</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($developers as $dev)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if($dev->logo)
                                        <img src="{{ asset('storage/' . $dev->logo) }}" alt="Logo" class="w-10 h-10 rounded-lg object-contain border border-gray-200 p-0.5">
                                    @else
                                        <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-700 font-bold flex items-center justify-center text-sm">
                                            {{ substr($dev->company_name, 0, 2) }}
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-bold text-gray-900">{{ $dev->company_name }}</p>
                                        <p class="text-xs text-gray-500">{{ Str::limit($dev->address, 35) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-semibold text-gray-900">{{ $dev->pic_name }}</p>
                                <p class="text-xs text-gray-500">{{ $dev->user->email ?? $dev->email }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $dev->entity_type === 'badan_usaha' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $dev->entity_type === 'badan_usaha' ? strtoupper($dev->business_type ?? 'PT/CV') : 'Perorangan' }}
                                </span>
                                @if($dev->nib)
                                    <p class="text-xs text-gray-500 mt-1">NIB: {{ $dev->nib }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs">
                                <p><svg class="w-4 h-4 inline-block text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg> {{ $dev->pic_phone }}</p>
                                <p><svg class="w-4 h-4 inline-block text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg> {{ $dev->phone }}</p>
                            </td>
                            <td class="px-6 py-4">
                                @if($dev->status === 'approved')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                        <svg class="w-4 h-4 inline-block text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Disetujui
                                    </span>
                                @elseif($dev->status === 'pending')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                        <svg class="w-4 h-4 inline-block text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Menunggu
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                        <svg class="w-4 h-4 inline-block text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg> Ditolak
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs font-semibold">
                                {{ $dev->housingProjects->count() }} Perumahan
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('admin.developers.show', $dev->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg font-semibold text-xs transition">
                                    <svg class="w-4 h-4 inline-block text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg> Detail & Kelola
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <svg class="w-12 h-12 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <p class="font-semibold">Belum ada data mitra pengembang pada kategori ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($developers->hasPages())
            <div class="p-4 border-t border-gray-200">
                {{ $developers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
