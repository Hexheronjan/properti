@extends('layouts.app')

@section('title', 'Pilih Unit')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800">
            ← Kembali ke Dashboard
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-4">Pilih Unit Perumahan</h1>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
            <h3 class="font-semibold text-blue-800 mb-2">Informasi Pemilihan</h3>
            <p class="text-sm text-blue-700">
                Nomor Registrasi: <strong>{{ $registration->registration_number }}</strong><br>
                Nomor Antrian: <strong>{{ $registration->queue_number ?? 'Belum tersedia' }}</strong><br>
                Metode Pembayaran: <strong>{{ config('nup.payment_methods.' . $registration->payment_method) }}</strong>
            </p>
        </div>

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <ul class="text-sm text-red-700">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @session('success')
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <p class="text-sm text-green-700">{{ session('success') }}</p>
            </div>
        @endsession

        @session('error')
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <p class="text-sm text-red-700">{{ session('error') }}</p>
            </div>
        @endsession
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($availableUnits as $unit)
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                @if($unit->image)
                    <img src="{{ asset('storage/' . $unit->image) }}" alt="{{ $unit->unit_code }}" class="w-full h-48 object-cover">
                @else
                    <div class="w-full h-48 bg-slate-100 flex items-center justify-center">
                        <svg class="w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    </div>
                @endif

                <div class="p-4">
                    <h3 class="font-bold text-lg text-gray-800 mb-1">{{ $unit->full_identifier }}</h3>
                    @php
                        $proj = $unit->project ?? $unit->housingProject;
                    @endphp
                    @if($proj)
                        <div class="mb-2 text-xs">
                            <span class="text-gray-400">Nama Perumahan:</span>
                            <span class="font-bold text-blue-600">{{ $proj->name }}</span>
                        </div>
                    @endif
                    <p class="text-sm text-gray-600 mb-2">{{ $unit->formatted_type }}</p>

                    <div class="grid grid-cols-2 gap-2 text-xs text-slate-600 mb-4">
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 4h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>{{ $unit->bedrooms }} KT</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>{{ $unit->bathrooms }} KM</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                            <span>LB: {{ $unit->building_area }}m²</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                            <span>LT: {{ $unit->land_area }}m²</span>
                        </div>
                    </div>

                    <div class="border-t pt-3">
                        <p class="text-lg font-bold text-blue-600 mb-3">
                            Rp {{ number_format($unit->price, 0, ',', '.') }}
                        </p>

                        <form action="{{ route('units.store', $registration->id) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                            @csrf
                            <input type="hidden" name="housing_unit_id" value="{{ $unit->id }}">

                            <div>
                                <label class="block text-gray-700 font-semibold mb-1 text-xs">Pilih Skema Pembayaran</label>
                                <select name="payment_scheme" class="payment-scheme-select w-full border border-gray-300 rounded-lg p-2 text-xs font-semibold focus:ring-2 focus:ring-blue-500" data-unit-id="{{ $unit->id }}" required>
                                    @php
                                        $proj = $unit->project ?? $unit->housingProject;
                                        $schemes = $proj ? ($proj->supported_payment_schemes ?? ['cash', 'kpr_bank']) : ['cash', 'kpr_bank'];

                                        // Ambil rencana pembelian dari pendaftaran NUP peserta
                                        $userPref = $registration->housingPreferences->firstWhere('housing_name', $proj?->name) 
                                                   ?? $registration->housingPreferences->first();
                                        $plan = $userPref?->purchase_plan ?? $registration->payment_method;

                                        // Normalisasi skema pilihan user saat pendaftaran NUP
                                        $userSelectedScheme = match($plan) {
                                             'inhouse', 'installment' => 'inhouse',
                                             'tunai', 'cash', 'tunai_keras' => 'cash',
                                             'kredit_mitra_pintar', 'mitra_pintar', 'tunai_mitra_pintar' => 'mitra_pintar',
                                             'kpr_bank_flpp', 'kpr', 'kpr_bank' => 'kpr_bank',
                                             default => 'kpr_bank',
                                        };

                                        // Daftar skema standar (Tunai disatukan menjadi 1 opsi saja)
                                        $masterOptions = [
                                            'inhouse' => 'In-House Developer (Cicilan Bulanan)',
                                            'cash' => 'Tunai (Bebas UTK)',
                                            'kpr_bank' => 'KPR Bank',
                                            'mitra_pintar' => 'Kredit Mitra Pintar',
                                        ];

                                        // Filter hanya skema yang didukung oleh proyek perumahan
                                        $availableOptions = [];
                                        foreach ($masterOptions as $code => $label) {
                                            $isSupported = $proj ? $proj->supportsScheme($code) : in_array($code, $schemes, true);
                                            if ($isSupported) {
                                                $availableOptions[$code] = $label;
                                            }
                                        }

                                        // Tampilkan pilihan dari NUP di URUTAN PERTAMA SENDIRI dan otomatis terpilih
                                        if (isset($availableOptions[$userSelectedScheme])) {
                                            $sortedOptions = [$userSelectedScheme => $availableOptions[$userSelectedScheme]];
                                            foreach ($availableOptions as $code => $label) {
                                                if ($code !== $userSelectedScheme) {
                                                    $sortedOptions[$code] = $label;
                                                }
                                            }
                                            $availableOptions = $sortedOptions;
                                            $defaultScheme = $userSelectedScheme;
                                        } else {
                                            $defaultScheme = array_key_first($availableOptions) ?? 'cash';
                                        }
                                    @endphp
                                    @foreach($availableOptions as $code => $label)
                                        <option value="{{ $code }}" {{ $defaultScheme === $code ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @if($proj && $proj->commitment_fee)
                                    <span class="text-[11px] text-gray-500 block mt-1">
                                        Biaya UTK KPR/In-House/Mitra: <strong>Rp {{ number_format($proj->getTotalUtkAmount(), 0, ',', '.') }}</strong> (Termasuk PPN 11% + VE 20%). Skema Tunai Bebas UTK.
                                    </span>
                                @endif
                            </div>

                            <!-- Bidang Khusus Kredit Mitra Pintar -->
                            <div id="mitra_fields_{{ $unit->id }}" class="hidden p-3 bg-amber-50 rounded-lg border border-amber-200 text-xs space-y-2">
                                <p class="font-bold text-amber-900 flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    <span>Persyaratan Kredit Mitra Pintar</span>
                                </p>
                                <div>
                                    <label class="block font-semibold text-gray-700 mb-0.5">Nama Instansi / Perusahaan Mitra <span class="text-red-500">*</span></label>
                                    <input type="text" name="mitra_institution_name" class="w-full border border-gray-300 rounded p-1.5 text-xs bg-white focus:outline-none focus:ring-1 focus:ring-amber-500" placeholder="Contoh: PT Telkom / RS Sardjito / Pemprov">
                                </div>
                                <div>
                                    <label class="block font-semibold text-gray-700 mb-0.5">Upload Dokumen Kerja Sama / SK Mitra <span class="text-red-500">*</span></label>
                                    <input type="file" name="mitra_document" accept=".pdf,image/*" class="w-full border border-gray-300 rounded p-1.5 text-xs bg-white focus:outline-none">
                                    <span class="text-[10px] text-gray-500 block mt-0.5">Format: PDF / Foto SK Karyawan Mitra, Surat Rekomendasi Instansi, atau Bukti MoU.</span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-gray-700 font-semibold mb-2 text-xs">Catatan (Opsional)</label>
                                <textarea name="notes" class="w-full border border-gray-300 rounded-lg p-2 text-xs" rows="2" placeholder="Tambahkan catatan..."></textarea>
                            </div>

                            <button type="submit" class="w-full bg-emerald-600 text-white font-semibold py-2 rounded-lg hover:bg-emerald-700 transition">
                                Pilih Unit Ini
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function toggleMitraFields(select) {
        const unitId = select.getAttribute('data-unit-id');
        const fields = document.getElementById('mitra_fields_' + unitId);
        if (fields) {
            if (select.value === 'mitra_pintar') {
                fields.classList.remove('hidden');
                fields.querySelectorAll('input').forEach(inp => inp.required = true);
            } else {
                fields.classList.add('hidden');
                fields.querySelectorAll('input').forEach(inp => inp.required = false);
            }
        }
    }

    document.querySelectorAll('.payment-scheme-select').forEach(function(select) {
        toggleMitraFields(select);
        select.addEventListener('change', function() {
            toggleMitraFields(this);
        });
    });
});
</script>
@endsection