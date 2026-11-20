@extends('layouts.app')

@section('title', 'Klausul SPR')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-2"><svg class="w-4 h-4 inline-block text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg> Klausul Surat Pemesanan Rumah (SPR)</h1>
        <p class="text-gray-600">Unit: {{ $unit->unit_number }} - {{ $housingProject->name }}</p>
    </div>

    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
        <div class="flex items-start">
            <span class="text-2xl mr-3"><svg class="w-4 h-4 inline-block text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></span>
            <div>
                <h3 class="font-semibold text-yellow-800">Penting: Baca dengan Teliti</h3>
                <p class="text-sm text-yellow-700">Anda wajib membaca dan menyetujui seluruh klausul SPR di bawah ini sebelum melanjutkan ke pembayaran uang tanda komitmen.</p>
            </div>
        </div>
    </div>

    <form action="{{ route('screening.process-spr-agreement', [$screening->id, $unit->id]) }}" method="POST">
        @csrf
        
        <!-- SPR Clauses -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h3 class="font-semibold text-gray-800 mb-4">Klausul SPR</h3>
            
            @if($sprClauses->count() > 0)
                <div class="space-y-4">
                    @foreach($sprClauses as $index => $clause)
                        <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                            <div class="flex items-start mb-2">
                                <span class="bg-blue-600 text-white text-xs font-bold px-2 py-1 rounded mr-3">
                                    {{ $clause->clause_number }}
                                </span>
                                <h4 class="font-semibold text-gray-800">{{ $clause->clause_title }}</h4>
                            </div>
                            <p class="text-gray-600 text-sm pl-10">{{ $clause->clause_content }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-gray-100 rounded-lg p-4 text-center">
                    <p class="text-gray-600 text-sm">Belum ada klausul SPR yang ditambahkan untuk perumahan ini.</p>
                    <p class="text-gray-500 text-xs mt-1">Silakan hubungi admin untuk informasi lebih lanjut.</p>
                </div>
            @endif
        </div>

        <!-- Unit Summary -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <h3 class="font-semibold text-blue-800 mb-3">Ringkasan Pemesanan</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-600">Unit:</span>
                    <span class="font-semibold ml-2">{{ $unit->unit_code ?? $unit->full_identifier }}</span>
                </div>
                <div>
                    <span class="text-gray-600">Tipe:</span>
                    <span class="font-semibold ml-2">{{ $unit->type }}</span>
                </div>
                <div>
                    <span class="text-gray-600">Harga Unit:</span>
                    <span class="font-semibold ml-2">Rp {{ number_format($unit->price, 0, ',', '.') }}</span>
                </div>
                <div>
                    <span class="text-gray-600">Uang Komitmen:</span>
                    <span class="font-semibold ml-2 text-blue-600">Rp {{ number_format($housingProject->commitment_fee, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Agreement Checkbox -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h3 class="font-semibold text-gray-800 mb-4">Persetujuan SPR</h3>
            
            <div class="space-y-4">
                <!-- Agree Option -->
                <label class="flex items-start p-4 border-2 rounded-lg cursor-pointer hover:border-green-500 transition {{ old('spr_agreement') == 'agree' ? 'border-green-500 bg-green-50' : 'border-gray-200' }}">
                    <input type="radio" name="spr_agreement" value="agree" class="mt-1 mr-3" @if(old('spr_agreement') == 'agree') checked @endif required>
                    <div>
                        <div class="font-semibold text-gray-800">Setuju</div>
                        <div class="text-sm text-gray-600 mt-1">
                            Saya menyetujui seluruh klausul SPR di atas dan melanjutkan proses pembayaran.
                        </div>
                    </div>
                </label>

                <!-- Disagree Option -->
                <label class="flex items-start p-4 border-2 rounded-lg cursor-pointer hover:border-red-500 transition {{ old('spr_agreement') == 'disagree' ? 'border-red-500 bg-red-50' : 'border-gray-200' }}">
                    <input type="radio" name="spr_agreement" value="disagree" class="mt-1 mr-3" @if(old('spr_agreement') == 'disagree') checked @endif required>
                    <div>
                        <div class="font-semibold text-gray-800">Tidak Setuju</div>
                        <div class="text-sm text-gray-600 mt-1">
                            Saya tidak menyetujui klausul SPR.
                            <br><span class="text-red-600 font-semibold">Proses pembelian akan dibatalkan.</span>
                        </div>
                    </div>
                </label>
            </div>

            @error('spr_agreement')
                <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
            @enderror
        </div>

        <!-- Not Refund NUP Agreement -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h3 class="font-semibold text-gray-800 mb-4">Persetujuan Not Refund NUP</h3>
            
            <div class="space-y-4">
                <!-- Agree Option -->
                <label class="flex items-start p-4 border-2 rounded-lg cursor-pointer hover:border-green-500 transition {{ old('nup_agreement') == 'agree' ? 'border-green-500 bg-green-50' : 'border-gray-200' }}">
                    <input type="radio" name="nup_agreement" value="agree" class="mt-1 mr-3" @if(old('nup_agreement') == 'agree') checked @endif required>
                    <div>
                        <div class="font-semibold text-gray-800">Setuju (Not Refund NUP)</div>
                        <div class="text-sm text-gray-600 mt-1">
                            Saya menyetujui bahwa NUP tidak dapat direfund jika saya membatalkan pembelian.
                        </div>
                    </div>
                </label>

                <!-- Disagree Option -->
                <label class="flex items-start p-4 border-2 rounded-lg cursor-pointer hover:border-red-500 transition {{ old('nup_agreement') == 'disagree' ? 'border-red-500 bg-red-50' : 'border-gray-200' }}">
                    <input type="radio" name="nup_agreement" value="disagree" class="mt-1 mr-3" @if(old('nup_agreement') == 'disagree') checked @endif required>
                    <div>
                        <div class="font-semibold text-gray-800">Tidak Setuju</div>
                        <div class="text-sm text-gray-600 mt-1">
                            Saya tidak menyetujui ketentuan ini.
                            <br><span class="text-red-600 font-semibold">Proses tidak dapat dilanjutkan.</span>
                        </div>
                    </div>
                </label>
            </div>

            @error('nup_agreement')
                <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
            @enderror
        </div>

        <!-- Submit Button -->
        <div class="flex justify-between items-center">
            <a href="{{ route('screening.select-project', [$screening->id, $housingProject->id]) }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                ← Kembali
            </a>
            <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                Lanjut ke Pembayaran →
            </button>
        </div>
    </form>
</div>
@endsection
