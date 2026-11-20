@extends('layouts.admin')

@section('title', 'Daftar Refund NUP')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:text-blue-800">
            ← Kembali ke Dashboard Admin
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Daftar Refund NUP</h1>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Refund</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alasan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Request</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($refunds as $refund)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $refund->refund_number }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $refund->user->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Rp {{ number_format($refund->refund_amount, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $refund->refund_reason }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold
                                    @if($refund->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($refund->status === 'approved') bg-green-100 text-green-800
                                    @elseif($refund->status === 'rejected') bg-red-100 text-red-800
                                    @elseif($refund->status === 'processed') bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-800 @endif
                                ">
                                    {{ strtoupper($refund->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $refund->requested_at->format('d-m-Y H:i') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                @if($refund->status === 'pending')
                                    <form action="{{ route('refunds.approve', $refund->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 text-xs">Setujui</button>
                                    </form>
                                    <form action="{{ route('refunds.reject', $refund->id) }}" method="POST" class="inline ml-2" onsubmit="return confirm('Tolak refund ini?')">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-xs">Tolak</button>
                                    </form>
                                @elseif($refund->status === 'approved')
                                    <form action="{{ route('refunds.process', $refund->id) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="text" name="payment_reference" placeholder="No. Referensi" class="px-2 py-1 border rounded text-xs mr-1" required>
                                        <button type="submit" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs">Proses</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $refunds->links() }}
    </div>
</div>
@endsection
