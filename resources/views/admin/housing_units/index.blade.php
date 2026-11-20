@extends(Auth::user()->isAdmin() ? 'layouts.admin' : 'layouts.app')

@section('title', 'Manajemen Unit Perumahan')

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
            <a href="{{ route('admin.housing-units.create') }}{{ request('project_id') ? '?project_id=' . request('project_id') : '' }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs sm:text-sm font-bold shadow transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>Posting Unit Baru</span>
            </a>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-gray-200 mb-6">
        <a href="{{ route('admin.housing-projects.index') }}" class="px-5 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-800 hover:border-gray-300 transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <span>Kawasan Perumahan</span>
        </a>
        <a href="{{ route('admin.housing-units.index') }}" class="px-5 py-3 text-sm font-bold border-b-2 border-blue-600 text-blue-600 bg-blue-50/50 rounded-t-xl transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span>Semua Unit Rumah (Kavling) ({{ $units->total() }})</span>
        </a>
    </div>

    @if($projects->isNotEmpty())
        <div class="mb-6 flex items-center gap-3 bg-white p-3.5 rounded-xl border border-gray-200">
            <span class="text-xs font-bold text-gray-700">Filter Perumahan:</span>
            <form action="{{ route('admin.housing-units.index') }}" method="GET" class="flex items-center gap-2">
                <select name="project_id" onchange="this.form.submit()" class="border border-gray-300 rounded-lg p-2 text-xs focus:outline-none focus:border-blue-600">
                    <option value="">Semua Perumahan</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->name }}
                        </option>
                    @endforeach
                </select>
                @if(request('project_id'))
                    <a href="{{ route('admin.housing-units.index') }}" class="text-xs text-blue-600 hover:underline">Reset Filter</a>
                @endif
            </form>
        </div>
    @endif

    @session('success')
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
            <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
        </div>
    @endsession

    @session('error')
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <p class="text-sm text-red-700 font-medium">{{ session('error') }}</p>
        </div>
    @endsession

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Gambar</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Perumahan & Kode Unit</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Blok / No</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tipe</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Harga</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Spesifikasi</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($units as $unit)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($unit->image && \Illuminate\Support\Facades\Storage::disk('public')->exists($unit->image))
                                    <img src="{{ asset('storage/' . $unit->image) }}" alt="{{ $unit->unit_code }}" class="w-12 h-12 object-cover rounded-lg border shadow-sm">
                                @else
                                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center border border-blue-100 shadow-sm">
                                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="font-bold text-gray-900 text-sm">{{ $unit->unit_code }}</p>
                                @if($unit->project)
                                    <p class="text-xs text-blue-600 font-semibold">{{ $unit->project->name }}</p>
                                @endif
                                @if(Auth::user()->isSuperAdmin() && ($unit->company || $unit->project?->company))
                                    <p class="text-[11px] text-gray-500 inline-flex items-center gap-1 mt-0.5">
                                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                        <span>{{ $unit->company->company_name ?? $unit->project->company->company_name }}</span>
                                    </p>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                Blok {{ $unit->block }} No. {{ $unit->number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ config('nup.unit_types.' . $unit->type, $unit->type) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-blue-600">
                                Rp {{ number_format($unit->price, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-600">
                                LB: {{ $unit->building_area }}m² | LT: {{ $unit->land_area }}m²<br>
                                <span class="text-slate-500 font-medium">{{ $unit->bedrooms }} KT | {{ $unit->bathrooms }} KM</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($unit->status === 'available')
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-green-100 text-green-800">Tersedia</span>
                                @elseif($unit->status === 'booked')
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-yellow-100 text-yellow-800">Dipesan</span>
                                @elseif($unit->status === 'sold')
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-red-100 text-red-800">Terjual</span>
                                @else
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-gray-100 text-gray-800">Tidak Tersedia</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('admin.housing-units.edit', $unit->id) }}" class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 p-1.5 rounded-lg transition" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form action="{{ route('admin.housing-units.destroy', $unit->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus unit {{ $unit->unit_code }} ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-1.5 rounded-lg transition" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                Belum ada unit perumahan yang diposting.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($units->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $units->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
