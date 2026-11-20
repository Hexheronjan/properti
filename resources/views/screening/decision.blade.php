@extends('layouts.app')

@section('title', 'Keputusan Screening KPR')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2"><svg class="w-4 h-4 inline-block text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg> Keputusan Screening KPR</h1>
            <p class="text-gray-600">Screening Anda telah selesai. Silakan lihat hasil dan buat keputusan Anda.</p>
        </div>

        @if($screening->isLayakProses())
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <span class="text-3xl mr-3"><svg class="w-4 h-4 inline-block text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg></span>
                    <div>
                        <h3 class="font-semibold text-green-800">Selamat! Anda Layak Proses KPR</h3>
                        <p class="text-sm text-green-700">Berdasarkan hasil screening, Anda memenuhi syarat untuk melanjutkan proses KPR.</p>
                    </div>
                </div>
            </div>
        @elseif($screening->isLayakBersyarat())
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <span class="text-3xl mr-3"><svg class="w-4 h-4 inline-block text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></span>
                    <div>
                        <h3 class="font-semibold text-yellow-800">Layak Bersyarat</h3>
                        <p class="text-sm text-yellow-700">Anda layak dengan beberapa syarat tambahan. Silakan hubungi admin untuk detail lebih lanjut.</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Screening Details -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <h3 class="font-semibold text-gray-800 mb-3">Detail Screening</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-600">Nomor Screening:</span>
                    <span class="font-semibold ml-2">{{ $screening->screening_number }}</span>
                </div>
                <div>
                    <span class="text-gray-600">Status:</span>
                    <span class="font-semibold ml-2 uppercase">{{ $screening->status }}</span>
                </div>
                <div>
                    <span class="text-gray-600">Bank:</span>
                    <span class="font-semibold ml-2">{{ strtoupper($screening->bank) }}</span>
                </div>
                <div>
                    <span class="text-gray-600">Jumlah Pinjaman:</span>
                    <span class="font-semibold ml-2">Rp {{ number_format($screening->loan_amount, 0, ',', '.') }}</span>
                </div>
                <div>
                    <span class="text-gray-600">Tenor:</span>
                    <span class="font-semibold ml-2">{{ $screening->tenor_years }} Tahun</span>
                </div>
                <div>
                    <span class="text-gray-600">Penghasilan Bulanan:</span>
                    <span class="font-semibold ml-2">Rp {{ number_format($screening->monthly_income, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Decision Form -->
        <form action="{{ route('screening.process-decision', $screening->id) }}" method="POST">
            @csrf
            <div class="mb-6">
                <h3 class="font-semibold text-gray-800 mb-4">Keputusan Anda</h3>
                
                <div class="space-y-4">
                    <!-- Agree Option -->
                    <label class="flex items-start p-4 border-2 rounded-lg cursor-pointer hover:border-blue-500 transition {{ old('decision') == 'agree' ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }}">
                        <input type="radio" name="decision" value="agree" class="mt-1 mr-3" @if(old('decision') == 'agree') checked @endif required>
                        <div>
                            <div class="font-semibold text-gray-800">Setuju Lanjut Proses</div>
                            <div class="text-sm text-gray-600 mt-1">
                                Saya setuju untuk melanjutkan proses pemilihan unit dan pembayaran uang tanda komitmen.
                                <br><span class="text-blue-600 font-semibold">Batas waktu: 48 jam (2×24 jam)</span>
                            </div>
                        </div>
                    </label>

                    <!-- Disagree Option -->
                    <label class="flex items-start p-4 border-2 rounded-lg cursor-pointer hover:border-red-500 transition {{ old('decision') == 'disagree' ? 'border-red-500 bg-red-50' : 'border-gray-200' }}">
                        <input type="radio" name="decision" value="disagree" class="mt-1 mr-3" @if(old('decision') == 'disagree') checked @endif required>
                        <div>
                            <div class="font-semibold text-gray-800">Tidak Setuju</div>
                            <div class="text-sm text-gray-600 mt-1">
                                Saya tidak ingin melanjutkan proses ini.
                                <br><span class="text-red-600 font-semibold"><svg class="w-4 h-4 inline-block text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> NUP tidak dapat direfund</span>
                            </div>
                        </div>
                    </label>
                </div>

                @error('decision')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Notes -->
            <div class="mb-6">
                <label class="block text-gray-700 font-semibold mb-2">Catatan (Opsional)</label>
                <textarea name="notes" rows="3" class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes') }}</textarea>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('dashboard') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                    Kembali ke Dashboard
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    Simpan Keputusan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
