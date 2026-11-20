@extends('layouts.admin')

@section('title', 'Screening Dashboard')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Dashboard Screening Kelayakan KPR</h1>
        <p class="text-gray-600">Selamat datang kembali! Layanan peninjauan dan kelayakan KPR oleh Pihak Bank.</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
            <h3 class="text-gray-600 text-sm font-semibold">Screening Pending</h3>
            <p class="text-3xl font-bold text-yellow-600">{{ $pendingScreenings }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
            <h3 class="text-gray-600 text-sm font-semibold">Dalam Proses (Verifikasi)</h3>
            <p class="text-3xl font-bold text-blue-600">{{ $processingScreenings }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
            <h3 class="text-gray-600 text-sm font-semibold">Layak Proses (Disetujui)</h3>
            <p class="text-3xl font-bold text-green-600">{{ $approvedScreenings }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
            <h3 class="text-gray-600 text-sm font-semibold">Tidak Layak (Ditolak)</h3>
            <p class="text-3xl font-bold text-red-600">{{ $rejectedScreenings }}</p>
        </div>
    </div>

    <!-- Recent Screenings Table -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Pengajuan KPR Terbaru</h3>
            <a href="{{ route('admin.kpr.index') }}" class="text-blue-600 hover:text-blue-700 text-sm font-semibold">Lihat Semua KPR →</a>
        </div>
        @if($recentScreenings->isEmpty())
            <p class="text-gray-500 text-sm">Tidak ada pengajuan screening KPR terbaru.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Peserta</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor Screening</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bank Mitra</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengajuan Pinjaman</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($recentScreenings as $screening)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $screening->user->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $screening->screening_number }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ strtoupper($screening->bank) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-semibold">Rp {{ number_format($screening->loan_amount, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($screening->status === 'layak_proses' || $screening->status === 'approved') bg-green-100 text-green-800
                                        @elseif($screening->status === 'layak_bersyarat') bg-yellow-100 text-yellow-800
                                        @elseif($screening->status === 'tidak_layak' || $screening->status === 'rejected') bg-red-100 text-red-800
                                        @else bg-blue-100 text-blue-800 @endif">
                                        {{ str_replace('_', ' ', ucfirst($screening->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <a href="{{ route('kpr.show', $screening->id) }}" class="text-blue-600 hover:text-blue-900 font-semibold">Tinjau Dokumen</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
