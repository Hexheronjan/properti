@extends(Auth::user()->isAdmin() ? 'layouts.admin' : 'layouts.app')

@section('title', 'Edit Unit Perumahan')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.housing-units.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-semibold">
            ← Kembali ke Manajemen Unit
        </a>
        <h1 class="text-2xl font-bold text-gray-800 mt-2">Edit Unit #{{ $unit->unit_code }}</h1>
        <p class="text-sm text-gray-600">Perbarui informasi data unit perumahan di bawah ini.</p>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <h3 class="text-sm font-bold text-red-800 mb-1">Terjadi kesalahan pengisian data:</h3>
            <ul class="list-disc pl-5 text-xs text-red-700 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('admin.housing-units.update', $unit->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-6 pb-6 border-b border-gray-100">
                <label class="block text-sm font-bold text-gray-700 mb-2">Pilih Perumahan (Proyek) *</label>
                <select name="project_id" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-600">
                    <option value="">-- Pilih Perumahan --</option>
                    @foreach($projects as $proj)
                        <option value="{{ $proj->id }}" {{ old('project_id', $unit->project_id) == $proj->id ? 'selected' : '' }}>
                            {{ $proj->name }} ({{ $proj->location }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Kode Unit *</label>
                    <input type="text" name="unit_code" value="{{ old('unit_code', $unit->unit_code) }}" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-600" required>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Blok *</label>
                    <input type="text" name="block" value="{{ old('block', $unit->block) }}" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-600" required>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Nomor *</label>
                    <input type="text" name="number" value="{{ old('number', $unit->number) }}" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-600" required>
                </div>
            </div>

            <!-- Bagian: Tipe Unit (Ditentukan dari Luas Bangunan & Luas Tanah) -->
            <div class="mb-6 p-4 bg-blue-50/50 rounded-xl border border-blue-200">
                <div class="flex items-center justify-between mb-3">
                    <label class="block text-sm font-bold text-gray-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <span>Tipe Unit</span>
                    </label>
                    <span class="text-xs font-bold text-blue-800 bg-white border border-blue-200 px-3 py-1 rounded-full shadow-sm">
                        Otomatis: <span id="preview_type_display" class="font-extrabold text-blue-600">{{ old('type', $unit->type) }}</span>
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Luas Bangunan (m²) *</label>
                        <input type="number" name="building_area" id="building_area" value="{{ old('building_area', $unit->building_area) }}" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-600 bg-white" required oninput="syncUnitType()">
                        <span class="text-[11px] text-gray-500 mt-0.5 block">Diisi oleh operator (misal: 36, 45, 54, dll.)</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Luas Tanah (m²) *</label>
                        <input type="number" name="land_area" id="land_area" value="{{ old('land_area', $unit->land_area) }}" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-600 bg-white" required oninput="syncUnitType()">
                        <span class="text-[11px] text-gray-500 mt-0.5 block">Diisi oleh operator (misal: 60, 72, 84, dll.)</span>
                    </div>
                </div>

                <!-- Nilai Tipe Unit otomatis terisi dari Luas Bangunan / Luas Tanah -->
                <input type="hidden" name="type" id="type_field" value="{{ old('type', $unit->type) }}">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="lg:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Harga (Rp) *</label>
                    <input type="number" name="price" value="{{ old('price', (int)$unit->price) }}" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-600" required>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Kamar Tidur *</label>
                    <input type="number" name="bedrooms" value="{{ old('bedrooms', $unit->bedrooms) }}" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-600" required>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Kamar Mandi *</label>
                    <input type="number" name="bathrooms" value="{{ old('bathrooms', $unit->bathrooms) }}" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-600" required>
                </div>

                <div class="lg:col-span-4">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Status Unit *</label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-600" required>
                        <option value="available" {{ old('status', $unit->status) == 'available' ? 'selected' : '' }}>Tersedia (Available)</option>
                        <option value="booked" {{ old('status', $unit->status) == 'booked' ? 'selected' : '' }}>Dipesan (Booked)</option>
                        <option value="sold" {{ old('status', $unit->status) == 'sold' ? 'selected' : '' }}>Terjual (Sold)</option>
                        <option value="unavailable" {{ old('status', $unit->status) == 'unavailable' ? 'selected' : '' }}>Tidak Tersedia</option>
                    </select>
                </div>
            </div>

            <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Ganti Foto Unit</label>
                    <input type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp" class="block w-full max-w-full border border-gray-300 rounded-lg p-2 text-sm focus:outline-none focus:border-blue-600">
                    <span class="text-xs text-gray-500 mt-1 block">Format file: JPG, JPEG, PNG, GIF, atau WEBP. Maks: 5MB. Kosongkan jika tidak ingin mengubahnya.</span>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Foto Saat Ini</label>
                    @if($unit->image)
                        <img src="{{ asset('storage/' . $unit->image) }}" alt="{{ $unit->unit_code }}" class="w-24 h-24 object-cover rounded shadow border">
                    @else
                        <div class="w-24 h-24 bg-gray-100 rounded flex items-center justify-center border text-gray-400">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        </div>
                    @endif
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-2">Deskripsi Detail</label>
                <textarea name="description" rows="4" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:outline-none focus:border-blue-600">{{ old('description', $unit->description) }}</textarea>
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 border-t pt-4">
                <a href="{{ route('admin.housing-units.index') }}" class="px-5 py-2.5 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-bold shadow transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
    <script>
    function syncUnitType() {
        const lb = document.getElementById('building_area').value;
        const lt = document.getElementById('land_area').value;
        let label = '-';
        if (lb && lt) {
            label = 'Tipe ' + lb + '/' + lt;
        } else if (lb) {
            label = 'Tipe ' + lb;
        } else if (lt) {
            label = 'Tipe Tanah ' + lt;
        }
        document.getElementById('preview_type_display').textContent = label;
        document.getElementById('type_field').value = label;
    }
    document.addEventListener('DOMContentLoaded', function() {
        syncUnitType();
    });
    </script>
</div>
@endsection
