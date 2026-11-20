@extends('layouts.admin')

@section('title', 'Kelola Pembayaran Tunai & Termin')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Kelola Pembayaran Tunai & Termin</h1>
        <p class="text-xs text-gray-500 mt-1">
            Verifikasi bukti transfer pelunasan tunai keras dan cicilan termin bertahap ke rekening developer. 
            <span class="text-gray-400 italic">(Cicilan KPR Bank dibayarkan langsung oleh debitur ke Bank rekanan).</span>
        </p>
    </div>

    <!-- Statistik Ringkas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="text-sm font-semibold text-blue-600 mb-1">Total Tagihan</h3>
            <p class="text-2xl font-bold text-blue-800">Rp {{ number_format($stats['total_billed'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <h3 class="text-sm font-semibold text-green-600 mb-1">Sudah Dibayar</h3>
            <p class="text-2xl font-bold text-green-800">Rp {{ number_format($stats['total_paid'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
            <h3 class="text-sm font-semibold text-orange-600 mb-1">Sisa Tagihan</h3>
            <p class="text-2xl font-bold text-orange-800">Rp {{ number_format($stats['total_remaining'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
            <h3 class="text-sm font-semibold text-purple-600 mb-1">Progress Pembayaran</h3>
            <p class="text-2xl font-bold text-purple-800">{{ number_format($stats['payment_progress'], 1) }}%</p>
        </div>
    </div>

    <!-- Per SPR Summary -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <h2 class="text-lg font-bold text-gray-800 mb-1">Rekapitulasi Pembayaran per SPR (Tunai, Termin & Mitra Pintar)</h2>
        <p class="text-xs text-gray-500 mb-4">Daftar kewajiban pelunasan unit dengan skema Tunai, In-House, dan Pencairan Lembaga Mitra Pintar.</p>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-800">No. SPR</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-800">User</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-800">Unit</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-800">Skema</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-800">Total Harga</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-800">Sudah Bayar</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-800">Sisa</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-800">Progress</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-800">Status</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-800">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sprSummaries as $summary)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3 font-semibold">{{ $summary['spr_number'] }}</td>
                            <td class="px-4 py-3">{{ $summary['user_name'] }}</td>
                            <td class="px-4 py-3">{{ $summary['unit_identifier'] }}</td>
                            <td class="px-4 py-3">
                                @if(($summary['payment_scheme'] ?? '') === 'mitra_pintar')
                                    <span class="px-2 py-0.5 bg-amber-100 text-amber-800 rounded-full text-xs font-bold">Mitra Pintar</span>
                                @else
                                    <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded-full text-xs font-bold uppercase">{{ $summary['payment_scheme'] ?? '-' }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">Rp {{ number_format($summary['total_price'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-green-600 font-semibold">Rp {{ number_format($summary['total_paid'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-orange-600 font-semibold">Rp {{ number_format($summary['remaining'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-24 bg-gray-200 rounded-full h-2">
                                        <div class="bg-purple-600 h-2 rounded-full" style="width: {{ $summary['progress'] }}%"></div>
                                    </div>
                                    <span class="text-xs font-semibold">{{ number_format($summary['progress'], 1) }}%</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold
                                    @if($summary['is_fully_paid']) bg-green-100 text-green-800
                                    @else bg-yellow-100 text-yellow-800 @endif
                                ">
                                    {{ $summary['is_fully_paid'] ? 'LUNAS' : 'BELUM LUNAS' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if(($summary['payment_scheme'] ?? '') === 'mitra_pintar' && !$summary['is_fully_paid'])
                                    <a href="{{ route('spr.show', $summary['id']) }}#mitra-disbursement-panel" class="inline-flex items-center gap-1 px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-bold shadow-xs transition">
                                        <span><svg class="w-4 h-4 inline-block text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg> Konfirmasi Pencairan Mitra</span>
                                    </a>
                                @else
                                    <a href="{{ route('spr.show', $summary['id']) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition">
                                        <span>Kelola SPR</span>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-6 text-gray-400">
                                Belum ada data rekapitulasi SPR Tunai / Termin yang diterbitkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
            <p class="text-sm text-green-700">{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <p class="text-sm text-red-700">{{ session('error') }}</p>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-lg overflow-hidden p-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Daftar Riwayat Pembayaran Tunai & Termin</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-800">No. Pembayaran</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-800">User</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-800">SPR</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-800">Unit</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-800">Tanggal</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-800">Jenis</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-800">Cicilan Ke-</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-800">Jumlah</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-800">Metode</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-800">Status</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-800">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $payment->payment_number }}</td>
                            <td class="px-4 py-3">
                                <div>
                                    <p class="font-semibold">{{ $payment->user?->name ?? 'User' }}</p>
                                    <p class="text-xs text-gray-500">{{ $payment->user?->email ?? '-' }}</p>
                                </div>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs">
                                @if($payment->sprDocument)
                                    <span class="font-semibold text-gray-800">{{ $payment->sprDocument->spr_number }}</span>
                                @else
                                    <span class="text-gray-400 italic">No. SPR #{{ $payment->spr_document_id ?? '-' }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-medium text-xs">
                                {{ $payment->sprDocument?->unitSelection?->housingUnit?->full_identifier ?? '-' }}
                            </td>
                            <td class="px-4 py-3">{{ $payment->payment_date ? $payment->payment_date->format('d-m-Y H:i') : '-' }}</td>
                            <td class="px-4 py-3">{{ ucfirst($payment->payment_type) }}</td>
                            <td class="px-4 py-3">{{ $payment->installment_number ?? '-' }}</td>
                            <td class="px-4 py-3 text-right">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                @if($payment->proof_of_transfer || $payment->proof_file)
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 bg-blue-50 text-blue-700 rounded-md font-semibold text-xs border border-blue-200">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 003.75 19.5z" /></svg>
                                        Transfer Bank
                                    </span>
                                @else
                                    <span class="text-xs text-gray-700 font-medium">
                                        {{ ucfirst($payment->payment_method) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold
                                    @if($payment->status === 'success') bg-green-100 text-green-800
                                    @elseif($payment->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($payment->status === 'failed') bg-red-100 text-red-800
                                    @elseif($payment->status === 'cancelled') bg-gray-100 text-gray-600 border border-gray-200
                                    @else bg-gray-100 text-gray-800 @endif
                                ">
                                    @if($payment->status === 'cancelled')
                                        DIBATALKAN
                                    @else
                                        {{ strtoupper($payment->status) }}
                                    @endif
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1.5">
                                    <a href="{{ route('admin.kpr-payments.show', $payment->id) }}" class="px-2.5 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded text-xs font-bold transition">
                                        Detail
                                    </a>
                                    @if($payment->status === 'pending')
                                        <form action="{{ route('admin.kpr-payments.approve', $payment->id) }}" method="POST" onsubmit="return confirm('Setujui pembayaran ini?')">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 bg-green-600 hover:bg-green-700 text-white text-xs rounded transition font-bold">
                                                Approve
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.kpr-payments.reject', $payment->id) }}" method="POST" onsubmit="return confirm('Tolak pembayaran ini?')">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 bg-red-600 hover:bg-red-700 text-white text-xs rounded transition font-bold">
                                                Reject
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-6 text-gray-400">
                                Belum ada data pembayaran cicilan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
            <div class="px-4 py-3 bg-gray-50 border-t">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
