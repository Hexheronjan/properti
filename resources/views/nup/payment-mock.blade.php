@extends('layouts.app')

@section('title', 'Pembayaran Mock')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('nup.show', $registration->id) }}" class="text-blue-600 hover:text-blue-800">
            ← Kembali ke Detail Registrasi
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Pembayaran Mock (Testing Mode)</h1>

        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <h3 class="font-semibold text-yellow-800 mb-2"><svg class="w-4 h-4 inline-block text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> Mode Mock Aktif</h3>
            <p class="text-sm text-yellow-700">{{ $message ?? 'Payment gateway belum dikonfigurasi. Menggunakan mode mock untuk testing.' }}</p>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <h3 class="font-semibold text-blue-800 mb-2">Ringkasan Pembayaran</h3>
            <p class="text-sm text-blue-700">
                Nomor Registrasi: <strong>{{ $registration->registration_number }}</strong><br>
                Biaya NUP: <strong>Rp {{ number_format($registration->nup_fee, 0, ',', '.') }}</strong><br>
                No. Transaksi: <strong>{{ $transaction->transaction_number }}</strong>
            </p>
        </div>

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

        @if($registration->payment_status === 'paid')
            <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                <h3 class="font-semibold text-green-800 mb-2"><svg class="w-4 h-4 inline-block text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Pembayaran Selesai</h3>
                <p class="text-sm text-green-700 mb-4">
                    Pembayaran Anda telah berhasil. Nomor antrian Anda: <strong>{{ $registration->queue_number }}</strong>
                </p>
                <a href="{{ route('units.create', $registration->id) }}" class="inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                    Pilih Unit Sekarang
                </a>
            </div>
        @else
            <form action="{{ route('nup.mock-payment-success', $registration->id) }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="transaction_number" value="{{ $transaction->transaction_number }}">

                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-800 mb-2">Simulasi Pembayaran</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Klik tombol di bawah untuk mensimulasikan pembayaran berhasil. 
                        Dalam mode production, ini akan diganti dengan payment gateway yang sebenarnya (Midtrans).
                    </p>
                    <button type="submit" class="w-full bg-green-600 text-white font-semibold py-3 rounded-lg hover:bg-green-700 transition">
                        <svg class="w-4 h-4 inline-block text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Simulasi Pembayaran Berhasil
                    </button>
                </div>

                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-800 mb-2">Catatan</h3>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• Ini adalah mode mock untuk testing tanpa payment gateway credentials</li>
                        <li>• Dalam production, sistem akan menggunakan Midtrans untuk pembayaran nyata</li>
                        <li>• Mock payment akan menerbitkan nomor antrian seperti payment nyata</li>
                        <li>• Notifikasi WhatsApp akan tetap dikirim jika dikonfigurasi</li>
                    </ul>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection