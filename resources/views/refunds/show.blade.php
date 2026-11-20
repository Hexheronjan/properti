@extends('layouts.app')

@section('title', 'Detail Refund NUP')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.refunds.index') }}" class="text-blue-600 hover:text-blue-800">
                ← Kembali ke Daftar Refund
            </a>
        @else
            <a href="{{ route('nup.show', $refund->nupRegistration->id) }}" class="text-blue-600 hover:text-blue-800">
                ← Kembali ke Detail NUP
            </a>
        @endif
    </div>

    <div class="bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Detail Refund #{{ $refund->refund_number }}</h1>

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

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <h3 class="font-semibold text-gray-800 mb-2">Informasi Refund</h3>
                <p class="text-sm text-gray-600"><strong>Nomor Refund:</strong> {{ $refund->refund_number }}</p>
                <p class="text-sm text-gray-600"><strong>Jumlah:</strong> Rp {{ number_format($refund->refund_amount, 0, ',', '.') }}</p>
                <p class="text-sm text-gray-600"><strong>Alasan:</strong> {{ $refund->refund_reason }}</p>
                @if($refund->refund_notes)
                    <p class="text-sm text-gray-600"><strong>Catatan:</strong> {{ $refund->refund_notes }}</p>
                @endif
            </div>

            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <h3 class="font-semibold text-gray-800 mb-2">Status Refund</h3>
                <p class="text-sm text-gray-600"><strong>Status:</strong>
                    <span class="px-2 py-1 rounded-full text-xs font-semibold
                        @if($refund->status === 'pending') bg-yellow-100 text-yellow-800
                        @elseif($refund->status === 'approved') bg-green-100 text-green-800
                        @elseif($refund->status === 'rejected') bg-red-100 text-red-800
                        @elseif($refund->status === 'processed') bg-blue-100 text-blue-800
                        @else bg-gray-100 text-gray-800 @endif
                    ">
                        {{ strtoupper($refund->status) }}
                    </span>
                </p>
                <p class="text-sm text-gray-600"><strong>Tanggal Request:</strong> {{ $refund->requested_at->format('d-m-Y H:i') }}</p>
                @if($refund->approved_at)
                    <p class="text-sm text-gray-600"><strong>Tanggal Disetujui:</strong> {{ $refund->approved_at->format('d-m-Y H:i') }}</p>
                @endif
                @if($refund->processed_at)
                    <p class="text-sm text-gray-600"><strong>Tanggal Diproses:</strong> {{ $refund->processed_at->format('d-m-Y H:i') }}</p>
                @endif
                @if($refund->payment_reference)
                    <p class="text-sm text-gray-600"><strong>No. Referensi:</strong> {{ $refund->payment_reference }}</p>
                @endif
            </div>
        </div>

        @if(auth()->user()->isAdmin() && $refund->status === 'pending')
            <div class="flex gap-2">
                <form action="{{ route('refunds.approve', $refund->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                        Setujui Refund
                    </button>
                </form>
                <form action="{{ route('refunds.reject', $refund->id) }}" method="POST" onsubmit="return confirm('Tolak refund ini?')">
                    @csrf
                    <input type="text" name="rejection_reason" placeholder="Alasan penolakan..." class="px-4 py-3 border border-gray-300 rounded-lg mr-2" required>
                    <button type="submit" class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                        Tolak Refund
                    </button>
                </form>
            </div>
        @endif

        @if(auth()->user()->isAdmin() && $refund->status === 'approved')
            <form action="{{ route('refunds.process', $refund->id) }}" method="POST">
                @csrf
                <div class="flex gap-2 items-center">
                    <label class="text-gray-700 font-semibold">No. Referensi Pembayaran:</label>
                    <input type="text" name="payment_reference" placeholder="Masukkan nomor referensi" class="px-4 py-3 border border-gray-300 rounded-lg" required>
                    <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Proses Pembayaran
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection
