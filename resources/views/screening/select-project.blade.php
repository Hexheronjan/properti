@extends('layouts.app')

@section('title', 'Pilih Perumahan')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-2 flex items-center gap-2"><svg class="w-5 h-5 inline-block text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg> Pilih Perumahan</h1>
        <p class="text-gray-600">Silakan pilih perumahan yang ingin Anda pilih unitnya.</p>
    </div>

    @if($housingProjects->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($housingProjects as $project)
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                    @if($project->image)
                        <img src="{{ asset('storage/' . $project->image) }}" alt="{{ $project->name }}" class="w-full h-48 object-cover">
                    @else
                        <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                            <svg class="w-16 h-16 text-slate-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        </div>
                    @endif
                    
                    <div class="p-4">
                        <h3 class="font-bold text-lg text-gray-800 mb-2">{{ $project->name }}</h3>
                        <p class="text-gray-600 text-sm mb-3">{{ $project->location }}</p>
                        
                        @if($project->description)
                            <p class="text-gray-500 text-sm mb-3 line-clamp-2">{{ Str::limit($project->description, 100) }}</p>
                        @endif
                        
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <span class="text-xs text-gray-500">Uang Komitmen:</span>
                                <p class="font-semibold text-blue-600">Rp {{ number_format($project->commitment_fee, 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500">Batas Waktu:</span>
                                <p class="font-semibold text-orange-600">{{ $project->commitment_deadline_hours }} jam</p>
                            </div>
                        </div>
                        
                        <a href="{{ route('screening.select-project', [$screening->id, $project->id]) }}" 
                           class="block w-full text-center bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">
                            Lihat Unit
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
            <svg class="w-12 h-12 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <h3 class="font-semibold text-yellow-800 mb-2">Belum Ada Perumahan Tersedia</h3>
            <p class="text-sm text-yellow-700">Mohon hubungi admin untuk informasi lebih lanjut.</p>
        </div>
    @endif

    <div class="mt-6">
        <a href="{{ route('screening.decision', $screening->id) }}" class="text-blue-600 hover:text-blue-800 text-sm">
            ← Kembali ke Keputusan Screening
        </a>
    </div>
</div>
@endsection
