@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Klausul Surat Pemesanan Rumah (SPR)</h1>

            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <h3 class="font-semibold text-blue-800 mb-2">Perumahan</h3>
                <p class="text-blue-700">{{ $housingProject->name }} - {{ $housingProject->location }}</p>
            </div>

            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                <h3 class="font-semibold text-green-800 mb-2">Unit yang Dipilih</h3>
                <p class="text-green-700">{{ $selection->housingUnit->getFullIdentifierAttribute() }} - Tipe {{ $selection->housingUnit->type }}</p>
                <p class="text-green-700 font-bold mt-1">Harga: Rp {{ number_format($selection->housingUnit->price, 0, ',', '.') }}</p>
            </div>

            <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <h3 class="font-semibold text-yellow-800 mb-2">Uang Tanda Komitmen (UTK)</h3>
                <p class="text-yellow-700">Rp {{ number_format($housingProject->commitment_fee, 0, ',', '.') }}</p>
                <p class="text-sm text-yellow-600 mt-1">Batas waktu pembayaran: {{ $housingProject->commitment_deadline_hours }} jam (2x24 jam)</p>
            </div>

            <div class="mb-6">
                <h3 class="font-semibold text-gray-800 mb-4">Klausul SPR</h3>
                <div class="space-y-3">
                    @if($sprClauses->count() > 0)
                        @foreach($sprClauses as $index => $clause)
                            <div class="p-4 border rounded-lg bg-gray-50">
                                <div class="flex items-start">
                                    <span class="font-bold text-gray-600 mr-3">{{ $index + 1 }}.</span>
                                    <p class="text-gray-700">{{ $clause->clause_text }}</p>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="p-4 border rounded-lg bg-gray-50">
                            <p class="text-gray-600">Tidak ada klausul SPR yang tersedia untuk proyek ini.</p>
                        </div>
                    @endif
                </div>
            </div>

            <form action="{{ route('units.agree-spr-clauses', $selection->id) }}" method="POST">
                @csrf

                <div class="mb-6">
                    <label class="flex items-start p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition">
                        <input type="checkbox" name="agree_clauses" value="1" class="mt-1 mr-3" required>
                        <div>
                            <span class="font-semibold text-gray-800">Saya menyetujui seluruh klausul SPR di atas</span>
                            <p class="text-sm text-gray-600 mt-1">Dengan menyetujui klausul ini, Anda akan diarahkan ke halaman pembayaran uang tanda komitmen (UTK).</p>
                        </div>
                    </label>
                </div>

                <div class="flex gap-4">
                    <button type="submit" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition font-semibold">
                        Setuju & Lanjut ke Pembayaran UTK
                    </button>
                    <a href="{{ route('units.show', $selection->id) }}" class="bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400 transition font-semibold">
                        Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
