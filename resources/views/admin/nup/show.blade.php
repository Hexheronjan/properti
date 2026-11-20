@extends('layouts.admin')

@section('title', 'Admin - Detail Registrasi')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.nup.index') }}" class="text-blue-600 hover:text-blue-800">
            ← Kembali ke Daftar Registrasi
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Detail Registrasi #{{ $registration->registration_number }}</h1>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg p-4 mb-6">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 mb-6">{{ session('error') }}</div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <h3 class="font-semibold text-gray-800 mb-2">Informasi Pemesan Unit</h3>
                <p class="text-sm text-gray-600"><strong>Nama Pemesan:</strong> <span class="font-bold text-gray-900">{{ $registration->full_name ?: $registration->user->name }}</span></p>
                <p class="text-sm text-gray-600"><strong>Akun Login:</strong> {{ $registration->user->name }} ({{ $registration->user->email }})</p>
                <p class="text-sm text-gray-600"><strong>Telepon:</strong> {{ $registration->phone ?: $registration->user->phone }}</p>
                <p class="text-sm text-gray-600"><strong>NIK:</strong> {{ $registration->nik ?: $registration->user->nik }}</p>
                <p class="text-sm text-gray-600"><strong>Profesi / Pekerjaan:</strong> <span class="font-semibold text-blue-700">{{ $registration->occupation_display }}</span></p>
                <p class="text-sm text-gray-600"><strong>Penghasilan/Bulan:</strong> Rp {{ number_format($registration->monthly_income, 0, ',', '.') }}</p>
                <p class="text-sm text-gray-600"><strong>Alamat:</strong> {{ $registration->address ?: $registration->user->address }}</p>
            </div>

            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <h3 class="font-semibold text-gray-800 mb-2">Informasi Registrasi</h3>
                <p class="text-sm text-gray-600"><strong>Metode:</strong> {{ config('nup.payment_methods.' . $registration->payment_method) }}</p>
                <p class="text-sm text-gray-600"><strong>Biaya NUP:</strong> Rp {{ number_format($registration->nup_fee, 0, ',', '.') }}</p>
                <p class="text-sm text-gray-600"><strong>Status Bayar:</strong>
                    <span class="px-2 py-1 rounded-full text-xs font-semibold
                        @if($registration->payment_status === 'paid') bg-green-100 text-green-800
                        @elseif($registration->payment_status === 'pending') bg-yellow-100 text-yellow-800
                        @else bg-red-100 text-red-800 @endif
                    ">
                        {{ $registration->payment_status }}
                    </span>
                </p>
                @if($registration->payment_date)
                    <p class="text-sm text-gray-600"><strong>Tanggal Bayar:</strong> {{ $registration->payment_date->format('d-m-Y H:i') }}</p>
                @endif
            </div>
        </div>

        @if($registration->queue_number)
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-blue-800 mb-2">Nomor Antrian</h3>
                <p class="text-2xl font-bold text-blue-600">{{ $registration->queue_number }}</p>
                <p class="text-sm text-blue-700">Tanggal: {{ $registration->queue_issue_date->format('d-m-Y H:i') }}</p>
            </div>
        @endif

        @if($registration->unitSelections->isNotEmpty())
            <h3 class="font-semibold text-gray-800 mb-4">Unit yang Dipilih</h3>
            @foreach($registration->unitSelections as $selection)
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="font-semibold">{{ $selection->housingUnit->full_identifier }}</p>
                            <p class="text-sm text-gray-600">Tipe: {{ config('nup.unit_types.' . $selection->housingUnit->type) }}</p>
                            <p class="text-sm text-gray-600">Harga: Rp {{ number_format($selection->housingUnit->price, 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Status: {{ strtoupper($selection->status) }}</p>
                            <p class="text-sm text-gray-600">Tanggal: {{ $selection->selection_date->format('d-m-Y H:i') }}</p>
                            <p class="text-sm text-gray-600">Urutan: #{{ $selection->selection_order }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif

        @if($registration->paymentTransactions->isNotEmpty())
            <h3 class="font-semibold text-gray-800 mb-4">Transaksi Pembayaran</h3>
            @foreach($registration->paymentTransactions as $transaction)
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
                    <p class="text-sm"><strong>No. Transaksi:</strong> {{ $transaction->transaction_number }}</p>
                    <p class="text-sm"><strong>Metode:</strong> {{ $transaction->payment_method }}</p>
                    <p class="text-sm"><strong>Jumlah:</strong> Rp {{ number_format($transaction->amount, 0, ',', '.') }}</p>
                    <p class="text-sm"><strong>Status:</strong> {{ $transaction->status }}</p>
                    @if($transaction->payment_gateway === 'cash_office' && $transaction->status === 'pending' && $registration->payment_status !== 'paid')
                        <div class="mt-3 flex flex-wrap gap-3">
                            <form action="{{ route('admin.nup.confirm-cash', $transaction->id) }}" method="POST" onsubmit="return confirm('Konfirmasi bahwa uang tunai sudah diterima di kantor?')">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Setujui / konfirmasi diterima</button>
                            </form>
                            <form action="{{ route('admin.nup.reject-cash', $transaction->id) }}" method="POST" class="flex flex-wrap gap-2" onsubmit="return confirm('Tolak pembayaran tunai ini? Peserta dapat mengajukan pembayaran ulang.')">
                                @csrf
                                <input type="text" name="rejection_reason" maxlength="500" required placeholder="Alasan penolakan" class="border border-gray-300 rounded px-3 py-2">
                                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Tolak pembayaran</button>
                            </form>
                        </div>
                    @endif
                </div>
            @endforeach
        @endif

        <h3 class="font-semibold text-gray-800 mb-4">Dokumen Pendaftaran NUP</h3>
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @if($registration->ktp_file)
                    <div>
                        <p class="text-sm font-semibold">KTP</p>
                        <a href="{{ route('admin.nup.documents.download', [$registration, 'ktp']) }}" class="text-blue-600 text-sm hover:underline">Download</a>
                    </div>
                @endif
                @if($registration->kk_file)
                    <div>
                        <p class="text-sm font-semibold">Kartu Keluarga</p>
                        <a href="{{ route('admin.nup.documents.download', [$registration, 'kk']) }}" class="text-blue-600 text-sm hover:underline">Download</a>
                    </div>
                @endif
                @if($registration->npwp_file)
                    <div>
                        <p class="text-sm font-semibold">NPWP</p>
                        <a href="{{ route('admin.nup.documents.download', [$registration, 'npwp']) }}" class="text-blue-600 text-sm hover:underline">Download</a>
                    </div>
                @endif
                @if($registration->spouse_ktp_file)
                    <div>
                        <p class="text-sm font-semibold">KTP Pasangan</p>
                        <a href="{{ route('admin.nup.documents.download', [$registration, 'spouse_ktp']) }}" class="text-blue-600 text-sm hover:underline">Download</a>
                    </div>
                @endif
                @if($registration->spouse_kk_file)
                    <div>
                        <p class="text-sm font-semibold">KK Pasangan</p>
                        <a href="{{ route('admin.nup.documents.download', [$registration, 'spouse_kk']) }}" class="text-blue-600 text-sm hover:underline">Download</a>
                    </div>
                @endif
                @if($registration->marriage_certificate_file)
                    <div>
                        <p class="text-sm font-semibold">Buku Nikah</p>
                        <a href="{{ route('admin.nup.documents.download', [$registration, 'marriage_certificate']) }}" class="text-blue-600 text-sm hover:underline">Download</a>
                    </div>
                @endif
                @if($registration->divorce_death_certificate_file)
                    <div>
                        <p class="text-sm font-semibold">Akta Cerai/Kematian</p>
                        <a href="{{ route('admin.nup.documents.download', [$registration, 'divorce_death_certificate']) }}" class="text-blue-600 text-sm hover:underline">Download</a>
                    </div>
                @endif
                @if($registration->sk_karyawan_file)
                    <div>
                        <p class="text-sm font-semibold">SK Karyawan/Kontrak</p>
                        <a href="{{ route('admin.nup.documents.download', [$registration, 'sk_karyawan']) }}" class="text-blue-600 text-sm hover:underline">Download</a>
                    </div>
                @endif
                @if($registration->salary_slip_file)
                    <div>
                        <p class="text-sm font-semibold">Slip Gaji 6 Bulan</p>
                        <a href="{{ route('admin.nup.documents.download', [$registration, 'salary_slip']) }}" class="text-blue-600 text-sm hover:underline">Download</a>
                    </div>
                @endif
                @if($registration->bank_statement_file)
                    <div>
                        <p class="text-sm font-semibold">Rek. Koran 6 Bulan</p>
                        <a href="{{ route('admin.nup.documents.download', [$registration, 'bank_statement']) }}" class="text-blue-600 text-sm hover:underline">Download</a>
                    </div>
                @endif
                @if($registration->sku_file)
                    <div>
                        <p class="text-sm font-semibold">Surat Ket. Usaha (SKU)</p>
                        <a href="{{ route('admin.nup.documents.download', [$registration, 'sku']) }}" class="text-blue-600 text-sm hover:underline">Download</a>
                    </div>
                @endif
                @if($registration->financial_statement_file)
                    <div>
                        <p class="text-sm font-semibold">Lap. Keuangan 6 Bulan</p>
                        <a href="{{ route('admin.nup.documents.download', [$registration, 'financial_statement']) }}" class="text-blue-600 text-sm hover:underline">Download</a>
                    </div>
                @endif
                @if($registration->additional_income_file)
                    <div>
                        <p class="text-sm font-semibold">Bukti Penghasilan/Lainnya</p>
                        <a href="{{ route('admin.nup.documents.download', [$registration, 'additional_income']) }}" class="text-blue-600 text-sm hover:underline">Download</a>
                    </div>
                @endif
            </div>
        </div>

        @if($registration->kprScreening)
            <h3 class="font-semibold text-gray-800 mb-4">Screening KPR</h3>
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <p class="text-sm"><strong>No. Screening:</strong> {{ $registration->kprScreening->screening_number }}</p>
                <p class="text-sm"><strong>Bank:</strong> {{ config('nup.kpr_banks.' . $registration->kprScreening->bank) }}</p>
                <p class="text-sm"><strong>Status:</strong> {{ $registration->kprScreening->status }}</p>
            </div>
        @endif

        @if($registration->sprDocuments->isNotEmpty())
            <h3 class="font-semibold text-gray-800 mb-4">Dokumen SPR</h3>
            @foreach($registration->sprDocuments as $spr)
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <p class="text-sm"><strong>No. SPR:</strong> {{ $spr->spr_number }}</p>
                    <p class="text-sm"><strong>Status:</strong> {{ $spr->status }}</p>
                    @if($spr->document_file)
                        <a href="{{ asset('storage/' . $spr->document_file) }}" target="_blank" class="text-blue-600 text-sm">Download PDF</a>
                    @endif
                </div>
            @endforeach
        @endif
    </div>
</div>
@endsection
