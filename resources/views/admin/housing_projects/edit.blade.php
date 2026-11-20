@extends('layouts.admin')

@section('title', 'Edit Perumahan - ' . $project->name)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('admin.housing-projects.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-semibold flex items-center gap-1">
                ← Kembali ke Kelola Perumahan
            </a>
            <h1 class="text-2xl font-bold text-gray-800 mt-2">Edit Perumahan: {{ $project->name }}</h1>
        </div>
        <form action="{{ route('admin.housing-projects.destroy', $project->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus perumahan ini? Seluruh unit terkait akan terpengaruh.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-4 py-2 bg-red-50 hover:bg-red-100 text-red-600 rounded-xl text-xs font-bold transition inline-flex items-center gap-1.5">
                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                <span>Hapus Perumahan</span>
            </button>
        </form>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6 text-sm text-red-800 shadow-sm">
            <p class="font-bold mb-1">Terjadi kesalahan pengisian form:</p>
            <ul class="list-disc pl-5 space-y-1 text-xs">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.housing-projects.update', $project->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Card 1: Identitas Proyek -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sm:p-8">
            <h2 class="text-base font-bold text-gray-800 mb-4 pb-2 border-b border-gray-100 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span>Identitas Proyek Perumahan</span>
            </h2>

            <!-- Super Admin selects Company -->
            @if(Auth::user()->isSuperAdmin())
                <div class="mb-5 pb-5 border-b border-gray-100">
                    <label class="block text-xs font-bold text-gray-700 mb-1">Pengembang / Developer Pemilik Proyek *</label>
                    <select name="company_id" class="w-full border border-gray-300 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">-- Tanpa Pengembang / Developer Utama --</option>
                        @foreach($companies as $comp)
                            <option value="{{ $comp->id }}" {{ old('company_id', $project->company_id) == $comp->id ? 'selected' : '' }}>
                                {{ $comp->company_name }} ({{ $comp->pic_name }})
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Nama Proyek Perumahan *</label>
                    <input type="text" name="name" value="{{ old('name', $project->name) }}" class="w-full border border-gray-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Broker / Agensi Mitra Pemasaran Resmi</label>
                    <select name="broker_id" class="w-full border border-gray-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white">
                        <option value="">-- Tanpa Broker Khusus (Tim Internal Pengembang) --</option>
                        @foreach($brokers as $broker)
                            <option value="{{ $broker->id }}" {{ old('broker_id', $project->broker_id) == $broker->id ? 'selected' : '' }}>
                                {{ $broker->name }}{{ $broker->office_name ? ' (' . $broker->office_name . ')' : '' }}{{ $broker->contact ? ' - ' . $broker->contact : '' }}{{ is_null($broker->company_id) ? ' [Mitra Umum Platform]' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <span class="text-[11px] text-gray-500 block mt-1">Calon pembeli yang memilih perumahan ini saat daftar NUP akan otomatis terhubung ke broker ini.</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Deskripsi & Fasilitas Kawasan</label>
                    <textarea name="description" rows="3" class="w-full border border-gray-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">{{ old('description', $project->description) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Card 2: Lokasi & Alamat Terstruktur -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sm:p-8">
            <h2 class="text-base font-bold text-gray-800 mb-4 pb-2 border-b border-gray-100 flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Lokasi & Alamat Lengkap</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Provinsi *</label>
                    <input type="text" name="province" value="{{ old('province', $project->province ?? 'Jawa Timur') }}" class="w-full border border-gray-300 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Kabupaten / Kota *</label>
                    <input type="text" name="city" value="{{ old('city', $project->city) }}" class="w-full border border-gray-300 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Kecamatan (Opsional)</label>
                    <input type="text" name="district" value="{{ old('district', $project->district) }}" class="w-full border border-gray-300 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Contoh: Kebomas">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Desa / Kelurahan (Opsional)</label>
                    <input type="text" name="village" value="{{ old('village', $project->village) }}" class="w-full border border-gray-300 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Contoh: Kedanyang">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Kode Pos</label>
                    <input type="text" name="postal_code" value="{{ old('postal_code', $project->postal_code) }}" class="w-full border border-gray-300 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Alamat Jalan / Detail</label>
                    <input type="text" name="full_address" value="{{ old('full_address', $project->full_address) }}" class="w-full border border-gray-300 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>
        </div>

        <!-- Card 3: Site Plan & Dokumen Perizinan (KKPR / PKKPR) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sm:p-8">
            <h2 class="text-base font-bold text-gray-800 mb-4 pb-2 border-b border-gray-100 flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Site Plan & Legalitas Perizinan Proyek</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Nomor KKPR / PKKPR</label>
                    <input type="text" name="kkpr_number" value="{{ old('kkpr_number', $project->kkpr_number) }}" class="w-full border border-gray-300 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Dokumen KKPR / PKKPR</label>
                    @if($project->doc_kkpr)
                        <div class="mb-2 flex items-center gap-2">
                            <a href="{{ asset('storage/' . $project->doc_kkpr) }}" target="_blank" class="text-xs text-blue-600 font-bold hover:underline inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>Lihat Dokumen KKPR Tersimpan</span>
                            </a>
                        </div>
                    @endif
                    <input type="file" name="doc_kkpr" accept=".pdf,image/*" class="w-full border border-gray-300 rounded-xl p-2 text-xs bg-white focus:outline-none">
                    <span class="text-[11px] text-gray-400 mt-1 block">Upload file baru jika ingin mengganti</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Site Plan / Masterplan Kawasan</label>
                    @if($project->site_plan)
                        <div class="mb-2 flex items-center gap-2">
                            <a href="{{ asset('storage/' . $project->site_plan) }}" target="_blank" class="text-xs text-blue-600 font-bold hover:underline inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                                <span>Lihat Site Plan Tersimpan</span>
                            </a>
                        </div>
                    @endif
                    <input type="file" name="site_plan" accept=".pdf,image/*" class="w-full border border-gray-300 rounded-xl p-2 text-xs bg-white focus:outline-none">
                    <span class="text-[11px] text-gray-400 mt-1 block">Upload file baru jika ingin mengganti</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Foto Lingkungan Perumahan</label>
                    @if($project->image)
                        <div class="mb-2 w-28 h-16 rounded-lg overflow-hidden border border-gray-200">
                            <img src="{{ asset('storage/' . $project->image) }}" alt="Preview" class="w-full h-full object-cover">
                        </div>
                    @endif
                    <input type="file" name="image" accept="image/*" class="w-full border border-gray-300 rounded-xl p-2 text-xs bg-white focus:outline-none">
                    <span class="text-[11px] text-gray-400 mt-1 block">Foto lingkungan / kawasan perumahan (Maks 5 MB)</span>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 mb-1">Dokumen Perizinan Lainnya (Opsional)</label>
                    @if($project->doc_permit_other)
                        <div class="mb-2 flex items-center gap-2">
                            <a href="{{ asset('storage/' . $project->doc_permit_other) }}" target="_blank" class="text-xs text-blue-600 font-bold hover:underline inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>Lihat Dokumen Perizinan Lain</span>
                            </a>
                        </div>
                    @endif
                    <input type="file" name="doc_permit_other" accept=".pdf,image/*" class="w-full border border-gray-300 rounded-xl p-2 text-xs bg-white focus:outline-none">
                </div>
            </div>
        </div>

        <!-- Card 4: Skema Pembayaran yang Didukung (Checklist) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sm:p-8">
            <h2 class="text-base font-bold text-gray-800 mb-2 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                <span>Skema Pembayaran yang Didukung (Checklist)</span>
            </h2>
            <p class="text-xs text-gray-500 mb-4 pb-2 border-b border-gray-100">
                Centang metode pembayaran yang diterima untuk perumahan ini. Calon pembeli hanya bisa memilih skema yang Anda aktifkan di sini.
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @php
                    $activeSchemes = old('supported_payment_schemes', $project->supported_payment_schemes ?? ['cash', 'kpr_bank']);
                @endphp
                @foreach($availableSchemes as $key => $scheme)
                    <label class="flex items-start gap-3 p-4 border-2 rounded-xl cursor-pointer hover:bg-blue-50/50 transition border-gray-200 has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/40">
                        <input type="checkbox" name="supported_payment_schemes[]" value="{{ $key }}" 
                               {{ (in_array($key, $activeSchemes, true) || ($key === 'inhouse' && in_array('installment', $activeSchemes, true)) || ($key === 'cash' && in_array('tunai_bertahap', $activeSchemes, true))) ? 'checked' : '' }}
                               class="mt-1 rounded text-blue-600 focus:ring-blue-500 w-4 h-4">
                        <div>
                            <p class="font-bold text-sm text-gray-900 flex items-center gap-1.5">
                                {{ $scheme['label'] }}
                            </p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $scheme['desc'] }}</p>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- Card 5: Pengaturan Aturan Tunai & In-House (Cicilan Developer) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sm:p-8">
            <h2 class="text-base font-bold text-gray-800 mb-2 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                <span>Aturan Skema Tunai & In-House (Cicilan Pengembang)</span>
            </h2>
            <p class="text-xs text-gray-500 mb-4 pb-2 border-b border-gray-100">
                Atur batasan jatuh tempo dan denda/bunga keterlambatan untuk skema Tunai dan In-House.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Aturan Tunai -->
                <div class="p-4 bg-emerald-50/50 rounded-xl border border-emerald-200">
                    <h3 class="text-sm font-bold text-emerald-900 mb-3 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>Ketentuan Skema Tunai Keras (Cash Lunas)</span>
                    </h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Batas Jatuh Tempo Pelunasan Tunai (Hari)</label>
                            <input type="number" name="cash_due_days" value="{{ old('cash_due_days', $project->cash_due_days ?? 30) }}" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            <span class="text-[11px] text-gray-500 mt-0.5 block">Maksimal hari pelunasan sejak SPR terbit (misal: 30 hari)</span>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Denda Keterlambatan Pelunasan Tunai (% per hari)</label>
                            <input type="number" step="0.01" name="cash_penalty_percent" value="{{ old('cash_penalty_percent', $project->cash_penalty_percent ?? 0.10) }}" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            <span class="text-[11px] text-gray-500 mt-0.5 block">Persentase denda per hari jika melewati batas tempo pelunasan cash (contoh: 0.1% per hari)</span>
                        </div>
                        <div class="pt-2 border-t border-emerald-200/60">
                            <label class="flex items-start gap-2.5 cursor-pointer">
                                <input type="checkbox" name="cash_allow_installments" value="1" {{ old('cash_allow_installments', $project->cash_allow_installments ?? true) ? 'checked' : '' }} class="rounded text-emerald-600 focus:ring-emerald-500 w-4 h-4 mt-0.5">
                                <div>
                                    <span class="text-xs font-semibold text-gray-800 block">Izinkan Pembayaran Termin Bertahap (Tunai)</span>
                                    <span class="text-[11px] text-gray-500 block leading-relaxed">
                                        Jika diaktifkan, pembeli dapat membayar bertahap (termin) dalam batas waktu pelunasan tunai. Jika dinonaktifkan, pembeli wajib langsung lunas sekaligus (tanpa termin).
                                    </span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Aturan In-House -->
                <div class="p-4 bg-indigo-50/50 rounded-xl border border-indigo-200">
                    <h3 class="text-sm font-bold text-indigo-900 mb-3 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-indigo-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Ketentuan Skema In-House (Cicilan Pengembang)</span>
                    </h3>
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Maks Tenor (Bulan)</label>
                                <input type="number" name="inhouse_max_tenor_months" value="{{ old('inhouse_max_tenor_months', $project->inhouse_max_tenor_months ?? 24) }}" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                <span class="text-[11px] text-gray-500 mt-0.5 block">Contoh: 12 / 24 / 36 bln</span>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Bunga / Margin (% / thn)</label>
                                <input type="number" step="0.01" name="inhouse_interest_rate" value="{{ old('inhouse_interest_rate', $project->inhouse_interest_rate ?? 0.00) }}" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                <span class="text-[11px] text-gray-500 mt-0.5 block">0% untuk cicilan flat syariah</span>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Tgl Jatuh Tempo Bulanan</label>
                                <input type="number" name="inhouse_due_day" value="{{ old('inhouse_due_day', $project->inhouse_due_day ?? 10) }}" min="1" max="31" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                <span class="text-[11px] text-gray-500 mt-0.5 block">Tgl 1 - 31 setiap bulannya</span>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Denda Telat Bayar (% / bln)</label>
                                <input type="number" step="0.01" name="inhouse_penalty_percent" value="{{ old('inhouse_penalty_percent', $project->inhouse_penalty_percent ?? 1.00) }}" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                <span class="text-[11px] text-gray-500 mt-0.5 block">Denda per bulan jika telat</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 6: Ketentuan UTK & Status -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sm:p-8">
            <h2 class="text-base font-bold text-gray-800 mb-1 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Ketentuan UTK (Uang Tanda Komitmen)</span>
            </h2>
            <p class="text-xs text-gray-500 mb-4 pb-2 border-b border-gray-100">
                Operator mengisi nilai pokok UTK. Sistem otomatis menambahkan PPN 11% dan VE 20%. <strong>Khusus skema Tunai, biaya UTK otomatis dihapuskan (Rp 0).</strong>
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Nominal Biaya Pokok UTK (Rp)</label>
                    <input type="number" name="commitment_fee" id="commitment_fee_input" value="{{ old('commitment_fee', $project->commitment_fee ?? 5000000) }}" class="w-full border border-gray-300 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" oninput="calculateUtkBreakdown(this.value)">
                    <span class="text-[11px] text-gray-400 mt-1 block">Diisi oleh operator proyek</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Batas Waktu Bayar UTK (Jam)</label>
                    <input type="number" name="commitment_deadline_hours" value="{{ old('commitment_deadline_hours', $project->commitment_deadline_hours ?? 48) }}" class="w-full border border-gray-300 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <span class="text-[11px] text-gray-400 mt-1 block">Batas countdown komitmen pemilihan unit (Default: 48 jam)</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Batas Waktu Antrean NUP (Hari)</label>
                    <input type="number" name="nup_deadline_days" value="{{ old('nup_deadline_days', $project->nup_deadline_days ?? 7) }}" class="w-full border border-gray-300 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <span class="text-[11px] text-gray-400 mt-1 block">Masa berlaku antrean NUP pilih unit (Default: 7 hari)</span>
                </div>
            </div>

            <!-- Preview Kalkulasi UTK + 11% PPN + 20% VE -->
            <div class="p-4 bg-blue-50/70 border border-blue-200 rounded-xl mb-4 text-xs">
                <span class="font-bold text-blue-900 block mb-2">Simulasi Tagihan UTK Calon Pembeli (KPR / In-House):</span>
                <div class="space-y-1 text-gray-700">
                    <div class="flex justify-between">
                        <span>Pokok UTK:</span>
                        <span id="preview_utk_base" class="font-semibold text-gray-900">Rp {{ number_format($project->commitment_fee ?? 5000000, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>PPN 11%:</span>
                        <span id="preview_utk_ppn" class="font-semibold text-gray-900">+ Rp {{ number_format(round(($project->commitment_fee ?? 5000000) * 0.11), 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>VE (Value Enhancement 20%):</span>
                        <span id="preview_utk_ve" class="font-semibold text-gray-900">+ Rp {{ number_format(round(($project->commitment_fee ?? 5000000) * 0.20), 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between pt-1.5 border-t border-blue-200 font-bold text-blue-900 text-sm">
                        <span>Total UTK yang Ditagihkan:</span>
                        <span id="preview_utk_total" class="text-blue-600">Rp {{ number_format(round(($project->commitment_fee ?? 5000000) * 1.31), 0, ',', '.') }}</span>
                    </div>
                </div>
                <p class="text-[11px] text-emerald-700 mt-2 font-semibold flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Catatan: Jika calon pembeli memilih pembayaran Tunai, biaya UTK otomatis dihapuskan (Rp 0).</span>
                </p>
            </div>

            <!-- Pengaturan Biaya Pokok Screening KPR -->
            <div class="pt-4 border-t border-gray-100">
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-700 mb-1">Nominal Biaya Pokok Screening KPR (Rp)</label>
                    <input type="number" name="screening_fee" id="screening_fee_input" value="{{ old('screening_fee', $project->screening_fee ?? 150000) }}" class="w-full border border-gray-300 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" oninput="calculateScreeningBreakdown(this.value)">
                    <span class="text-[11px] text-gray-400 mt-1 block">Diisi oleh pengembang (Default / Rekomendasi: Rp 150.000. Dapat disetel 0 jika promo gratis).</span>
                </div>

                <!-- Preview Kalkulasi Screening + 11% PPN + 20% VE -->
                <div class="p-4 bg-emerald-50/70 border border-emerald-200 rounded-xl mb-4 text-xs">
                    <span class="font-bold text-emerald-950 block mb-2">Simulasi Tagihan Screening KPR Calon Pembeli:</span>
                    <div class="space-y-1 text-gray-700">
                        <div class="flex justify-between">
                            <span>Pokok Biaya Screening:</span>
                            <span id="preview_screening_base" class="font-semibold text-gray-900">Rp {{ number_format($project->screening_fee ?? 150000, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>PPN 11%:</span>
                            <span id="preview_screening_ppn" class="font-semibold text-gray-900">+ Rp {{ number_format(round(($project->screening_fee ?? 150000) * 0.11), 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>VE (Value Enhancement 20%):</span>
                            <span id="preview_screening_ve" class="font-semibold text-gray-900">+ Rp {{ number_format(round(($project->screening_fee ?? 150000) * 0.20), 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between pt-1.5 border-t border-emerald-200 font-bold text-emerald-950 text-sm">
                            <span>Total Biaya Screening Ditagihkan:</span>
                            <span id="preview_screening_total" class="text-emerald-700">Rp {{ number_format(round(($project->screening_fee ?? 150000) * 1.31), 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $project->is_active) ? 'checked' : '' }} class="rounded text-blue-600 focus:ring-blue-500 w-4 h-4">
                    <span class="text-sm font-semibold text-gray-800">Aktifkan Perumahan ini</span>
                </label>
            </div>
        </div>

        <script>
        function calculateUtkBreakdown(val) {
            const base = parseFloat(val) || 0;
            const ppn = Math.round(base * 0.11);
            const ve = Math.round(base * 0.20);
            const total = base + ppn + ve;

            document.getElementById('preview_utk_base').textContent = 'Rp ' + base.toLocaleString('id-ID');
            document.getElementById('preview_utk_ppn').textContent = '+ Rp ' + ppn.toLocaleString('id-ID');
            document.getElementById('preview_utk_ve').textContent = '+ Rp ' + ve.toLocaleString('id-ID');
            document.getElementById('preview_utk_total').textContent = 'Rp ' + total.toLocaleString('id-ID');
        }

        function calculateScreeningBreakdown(val) {
            const base = parseFloat(val) || 0;
            const ppn = Math.round(base * 0.11);
            const ve = Math.round(base * 0.20);
            const total = base + ppn + ve;

            document.getElementById('preview_screening_base').textContent = 'Rp ' + base.toLocaleString('id-ID');
            document.getElementById('preview_screening_ppn').textContent = '+ Rp ' + ppn.toLocaleString('id-ID');
            document.getElementById('preview_screening_ve').textContent = '+ Rp ' + ve.toLocaleString('id-ID');
            document.getElementById('preview_screening_total').textContent = 'Rp ' + total.toLocaleString('id-ID');
        }

        document.addEventListener('DOMContentLoaded', function() {
            const el = document.getElementById('commitment_fee_input');
            if (el && el.value) calculateUtkBreakdown(el.value);

            const scrEl = document.getElementById('screening_fee_input');
            if (scrEl && scrEl.value) calculateScreeningBreakdown(scrEl.value);
        });
        </script>

        <!-- Submit Bar -->
        <div class="flex items-center justify-end gap-3 pb-8">
            <a href="{{ route('admin.housing-projects.index') }}" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm font-semibold transition">
                Batal
            </a>
            <button type="submit" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold shadow-lg transition">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
