@extends('layouts.admin')

@section('title', 'Tambah Klausul SPR Baru')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.spr-clauses.index') }}" class="text-blue-600 hover:text-blue-800 text-sm flex items-center gap-1">
            ← Kembali ke Kelola Klausul
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100">
        <div class="bg-gradient-to-r from-indigo-600 to-blue-700 px-6 py-5">
            <h1 class="text-xl font-bold text-white"><svg class="w-4 h-4 inline-block text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg> Tambah Klausul SPR Baru</h1>
            <p class="text-indigo-200 text-sm mt-1">Buat pasal/ketentuan klausul baru untuk dokumen SPR perumahan</p>
        </div>

        <form action="{{ route('admin.spr-clauses.store') }}" method="POST" class="p-6 space-y-5">
            @csrf

            <div>
                <label class="block text-gray-700 font-semibold mb-2 text-sm">Pilih Perumahan</label>
                <select name="housing_project_id" class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-indigo-500 focus:outline-none" required>
                    <option value="" disabled selected>-- Pilih Perumahan --</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" {{ old('housing_project_id', $projectId) == $project->id ? 'selected' : '' }}>
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>
                @error('housing_project_id')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2 text-sm">Nomor Pasal (contoh: Pasal 1)</label>
                    <input type="text" name="clause_number" value="{{ old('clause_number') }}" class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-indigo-500 focus:outline-none" placeholder="Pasal 1" required>
                    @error('clause_number')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2 text-sm">Urutan Tampil (Order)</label>
                    <input type="number" name="order" value="{{ old('order', 0) }}" class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-indigo-500 focus:outline-none" required min="0">
                    @error('order')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-2 text-sm">Judul Klausul</label>
                <input type="text" name="clause_title" value="{{ old('clause_title') }}" class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-indigo-500 focus:outline-none" placeholder="Ketentuan Pembayaran" required>
                @error('clause_title')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-2 text-sm">Isi Konten Klausul</label>
                <textarea name="clause_content" rows="6" class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-indigo-500 focus:outline-none leading-relaxed" placeholder="Tuliskan isi pasal/ketentuan di sini..." required>{{ old('clause_content') }}</textarea>
                @error('clause_content')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3 bg-gray-50 p-4 rounded-xl">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <label for="is_active" class="text-sm font-semibold text-gray-700 select-none cursor-pointer">
                    Aktifkan klausul ini agar langsung tampil ke calon pembeli
                </label>
            </div>

            <div class="flex gap-4 pt-3">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-3 rounded-xl hover:bg-indigo-700 transition font-semibold text-sm">
                    <svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg> Simpan Klausul
                </button>
                <a href="{{ route('admin.spr-clauses.index') }}" class="bg-gray-100 text-gray-700 px-6 py-3 rounded-xl hover:bg-gray-200 transition font-semibold text-sm">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
