@extends('layouts.admin')

@section('title', 'Kelola Klausul SPR')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800"><svg class="w-4 h-4 inline-block text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg> Kelola Klausul SPR</h1>
            <p class="text-gray-500 text-sm">Kelola pasal & klausul persetujuan SPR untuk setiap perumahan</p>
        </div>
        <a href="{{ route('admin.spr-clauses.create') }}" class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition font-semibold text-sm">
            + Tambah Klausul Baru
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 rounded-xl p-4 text-green-800 text-sm font-medium">
            <svg class="w-4 h-4 inline-block text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> {{ session('success') }}
        </div>
    @endif

    <div class="space-y-6">
        @foreach($projects as $project)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                {{-- Project Header --}}
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <div>
                        <h2 class="font-bold text-gray-800 text-lg">{{ $project->name }}</h2>
                        <p class="text-xs text-gray-500">{{ $project->location }} &middot; {{ $project->sprClauses->count() }} klausul aktif</p>
                    </div>
                    <a href="{{ route('admin.spr-clauses.create', ['project_id' => $project->id]) }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 hover:underline">
                        + Tambah Klausul ke Proyek ini
                    </a>
                </div>

                {{-- Clauses List --}}
                <div class="p-6">
                    @if($project->sprClauses->isEmpty())
                        <div class="text-center py-6 text-gray-400 bg-gray-50 rounded-xl">
                            <svg class="w-10 h-10 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                            <p class="text-sm mt-1">Belum ada klausul SPR untuk perumahan ini</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($project->sprClauses as $clause)
                                <div class="flex items-start justify-between border border-gray-150 rounded-xl p-4 hover:bg-gray-50 transition">
                                    <div class="flex items-start gap-4">
                                        <span class="bg-indigo-100 text-indigo-700 text-xs font-bold px-2.5 py-1 rounded-lg mt-0.5">
                                            {{ $clause->clause_number }}
                                        </span>
                                        <div>
                                            <h4 class="font-bold text-gray-800 text-sm flex items-center gap-2">
                                                {{ $clause->clause_title }}
                                                @if(!$clause->is_active)
                                                    <span class="px-2 py-0.5 text-[10px] font-semibold bg-gray-100 text-gray-600 rounded-full">Non-Aktif</span>
                                                @endif
                                            </h4>
                                            <p class="text-gray-600 text-sm mt-1 leading-relaxed">{{ $clause->clause_content }}</p>
                                            <span class="text-[10px] text-gray-400 mt-2 block">Urutan: {{ $clause->order }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 ml-4">
                                        <a href="{{ route('admin.spr-clauses.edit', $clause->id) }}" class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Edit">
                                            <svg class="w-4 h-4 inline-block text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        <form action="{{ route('admin.spr-clauses.destroy', $clause->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus klausul ini?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Hapus">
                                                <svg class="w-4 h-4 inline-block text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
