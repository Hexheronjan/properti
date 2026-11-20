@extends('layouts.app')

@section('title', 'Pembayaran UTK')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('units.show', $unitSelection->id) }}" class="text-blue-600 hover:text-blue-800">
            ← Kembali ke Detail Unit
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Pembayaran UTK (Uang Tanda Jadi)</h1>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <h3 class="font-semibold text-blue-800 mb-2">Ringkasan Pembayaran</h3>
            <p class="text-sm text-blue-700">
                Unit Rumah: <strong>{{ $unitSelection->housingUnit->full_identifier }}</strong><br>
                Nomor Pembayaran: <strong>{{ $utkPayment->payment_number }}</strong><br>
                Biaya UTK (Uang Tanda Jadi): <strong>Rp {{ number_format($utkPayment->amount, 0, ',', '.') }}</strong>
            </p>
        </div>

        @session('error')
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <p class="text-sm text-red-700">{{ session('error') }}</p>
            </div>
        @endsession

        <div class="mb-6">
            <h3 class="font-semibold text-gray-800 mb-4">Metode Pembayaran</h3>
            <div id="payment-container">
                <button id="pay-button" class="w-full bg-blue-600 text-white font-semibold py-4 rounded-lg hover:bg-blue-700 transition">
                    Bayar Sekarang - Rp {{ number_format($utkPayment->amount, 0, ',', '.') }}
                </button>
            </div>
        </div>

        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <h3 class="font-semibold text-yellow-800 mb-2">Informasi Pembayaran</h3>
            <ul class="text-sm text-yellow-700 space-y-1">
                <li>• Pembayaran menggunakan Payment Gateway Midtrans</li>
                <li>• Tersedia berbagai metode pembayaran (Transfer Bank, QRIS, Kartu Kredit)</li>
                <li>• Pembayaran aman dan terenkripsi</li>
                <li>• Pilihan unit akan dikonfirmasi secara permanen setelah pembayaran sukses</li>
            </ul>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://app.{{ config('services.midtrans.environment') === 'production' ? '' : 'sandbox.' }}midtrans.com/snap/snap.js" data-client-key="{{ $client_key }}"></script>
<script>
    document.getElementById('pay-button').onclick = function() {
        snap.pay('{{ $snap_token }}', {
            onSuccess: function(result) {
                alert("Pembayaran UTK berhasil!");
                window.location.href = "{{ route('units.show', $unitSelection->id) }}?transaction_id=" + (result.transaction_id || '') + "&order_id=" + (result.order_id || '');
            },
            onPending: function(result) {
                alert("Pembayaran UTK pending!");
                window.location.href = "{{ route('units.show', $unitSelection->id) }}?transaction_id=" + (result.transaction_id || '') + "&order_id=" + (result.order_id || '');
            },
            onError: function(result) {
                alert("Pembayaran UTK gagal!");
                window.location.href = "{{ route('units.show', $unitSelection->id) }}?transaction_id=" + (result.transaction_id || '') + "&order_id=" + (result.order_id || '');
            }
        });
    };
</script>
@endpush
@endsection
