@extends('layouts.app')

@section('title', 'Pembayaran Uang Tanda Komitmen')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-2"><svg class="w-4 h-4 inline-block text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg> Pembayaran Uang Tanda Komitmen</h1>
        <p class="text-gray-600">Lengkapi pembayaran untuk mengamankan unit yang Anda pilih.</p>
    </div>

    <!-- Warning Banner -->
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
        <div class="flex items-start">
            <span class="text-2xl mr-3">⏰</span>
            <div>
                <h3 class="font-semibold text-red-800">Batas Waktu Pembayaran: 48 Jam (2×24 Jam)</h3>
                <p class="text-sm text-red-700">Pastikan pembayaran selesai dalam batas waktu yang ditentukan agar unit tetap tersedia untuk Anda.</p>
            </div>
        </div>
    </div>

    <!-- Payment Summary -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h3 class="font-semibold text-gray-800 mb-4">Ringkasan Pembayaran</h3>
        
        <div class="bg-gray-50 rounded-lg p-4 mb-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-600">Unit:</span>
                    <span class="font-semibold ml-2">{{ $unit->unit_code ?? $unit->full_identifier }}</span>
                </div>
                <div>
                    <span class="text-gray-600">Perumahan:</span>
                    <span class="font-semibold ml-2">{{ $housingProject->name }}</span>
                </div>
                <div>
                    <span class="text-gray-600">Tipe:</span>
                    <span class="font-semibold ml-2">{{ $unit->type }}</span>
                </div>
                <div>
                    <span class="text-gray-600">Harga Unit:</span>
                    <span class="font-semibold ml-2">Rp {{ number_format($unit->price, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <div class="border-t pt-4">
            <div class="flex justify-between items-center">
                <div>
                    <span class="text-gray-600">Uang Tanda Komitmen:</span>
                    <p class="text-xs text-gray-500 mt-1">Pembayaran komitmen untuk mengamankan unit</p>
                </div>
                <div class="text-right">
                    <span class="font-bold text-2xl text-blue-600">Rp {{ number_format($housingProject->commitment_fee, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    @if($existingPayment && $existingPayment->isSuccessful())
        <!-- Payment Already Successful -->
        <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
            <span class="text-4xl mb-4 block"><svg class="w-4 h-4 inline-block text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg></span>
            <h3 class="font-semibold text-green-800 mb-2">Pembayaran Berhasil!</h3>
            <p class="text-sm text-green-700 mb-4">Pembayaran uang tanda komitmen Anda telah berhasil diproses.</p>
            <a href="{{ route('screening.result', $screening->id) }}" class="inline-block px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                Lihat Hasil
            </a>
        </div>
    @else
        <!-- Payment Form -->
        <form action="{{ route('screening.process-commitment-payment', $screening->id) }}" method="POST">
            @csrf
            
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="font-semibold text-gray-800 mb-4">Metode Pembayaran</h3>
                
                <div class="space-y-3">
                    <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer hover:border-blue-500 transition {{ old('payment_method') == 'transfer' ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }}">
                        <input type="radio" name="payment_method" value="transfer" class="mr-3" @if(old('payment_method') == 'transfer') checked @endif required>
                        <div class="flex items-center">
                            <span class="text-2xl mr-3 inline-flex items-center justify-center"><svg class="w-4 h-4 inline-block text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg></span>
                            <div>
                                <div class="font-semibold text-gray-800">Transfer bank via DOKU</div>
                                <div class="text-sm text-gray-600">Pilih Virtual Account bank pada halaman DOKU.</div>
                            </div>
                        </div>
                    </label>
                    <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer hover:border-blue-500 transition {{ old('payment_method') == 'cash' ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }}">
                        <input type="radio" name="payment_method" value="cash" class="mr-3" @if(old('payment_method') == 'cash') checked @endif required>
                        <div class="font-semibold text-gray-800">Tunai di Alfamart/Indomaret via DOKU</div>
                    </label>
                </div>

                @error('payment_method')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Terms and Conditions -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <h4 class="font-semibold text-yellow-800 mb-2">Syarat dan Ketentuan</h4>
                <ul class="text-sm text-yellow-700 space-y-1">
                    <li>• Pembayaran harus diselesaikan dalam 48 jam setelah ini</li>
                    <li>• Unit akan diamankan setelah pembayaran berhasil</li>
                    <li>• SPR akan dicetak dengan integrasi E-Materai</li>
                    <li>• Pastikan data yang diinput sudah benar</li>
                </ul>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-between items-center">
                <a href="{{ route('screening.spr-clauses', [$screening->id, $unit->id]) }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                    ← Kembali
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    Lanjut Pembayaran →
                </button>
            </div>
        </form>
    @endif
</div>
@endsection
