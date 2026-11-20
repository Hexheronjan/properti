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

        <div class="bg-blue-50/70 border border-blue-200 rounded-2xl p-6 mb-6">
            <h3 class="font-bold text-blue-900 mb-3 text-base">Rincian Unit & Tagihan UTK</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs text-blue-900 mb-4 pb-4 border-b border-blue-200">
                <div>
                    <span class="text-blue-600 block">Unit Dipilih:</span>
                    <span class="font-bold text-sm text-blue-950">{{ $unitSelection->housingUnit->full_identifier }}</span>
                    <span class="text-gray-500 block">{{ config('nup.unit_types.' . $unitSelection->housingUnit->type) }}</span>
                </div>
                <div>
                    <span class="text-blue-600 block">Harga Jual Unit:</span>
                    <span class="font-bold text-sm text-blue-950">Rp {{ number_format($unitSelection->housingUnit->price, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Breakdown Perhitungan UTK: Pokok + PPN 11% + VE 20% -->
            <div class="bg-white rounded-xl p-4 border border-blue-200 text-xs text-gray-700 space-y-2">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Biaya Pokok UTK (Ditetapkan Pengembang):</span>
                    <span class="font-semibold text-gray-900">Rp {{ number_format($baseUtk ?? 5000000, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center text-gray-600">
                    <span>PPN (11%):</span>
                    <span class="font-semibold text-gray-900">+ Rp {{ number_format($ppnUtk ?? (($baseUtk ?? 5000000) * 0.11), 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center text-gray-600">
                    <span>VE (Value Enhancement 20%):</span>
                    <span class="font-semibold text-gray-900">+ Rp {{ number_format($veUtk ?? (($baseUtk ?? 5000000) * 0.20), 0, ',', '.') }}</span>
                </div>
                <div class="pt-2 border-t border-gray-200 flex justify-between items-center text-sm font-bold text-blue-900">
                    <span>Total Tagihan UTK (Uang Tanda Komitmen):</span>
                    <span class="text-base text-blue-600">Rp {{ number_format($totalUtk ?? $utkAmount, 0, ',', '.') }}</span>
                </div>
            </div>

            @if($unitSelection->utk_payment_deadline)
                <div class="text-xs text-blue-900 mt-3 flex items-center justify-between flex-wrap gap-2 font-medium bg-white p-3 rounded-xl border border-blue-200">
                    <span class="flex items-center gap-1.5 font-semibold">
                        <span>⏱️</span> Batas Waktu Pembayaran UTK: 
                        <strong class="{{ $unitSelection->isUtkPaymentDeadlinePassed() ? 'text-red-600' : 'text-blue-900' }}">
                            {{ $unitSelection->utk_payment_deadline->format('d-m-Y H:i') }} WIB
                        </strong>
                    </span>
                    <span class="text-[11px] font-bold px-2.5 py-1 rounded-full {{ $unitSelection->isUtkPaymentDeadlinePassed() ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-800' }}">
                        {{ $unitSelection->isUtkPaymentDeadlinePassed() ? 'Telah Lewat Batas Waktu' : 'Tersisa ' . now()->diffForHumans($unitSelection->utk_payment_deadline, true) }}
                    </span>
                </div>
            @endif
        </div>

        @if($unitSelection->isUtkPaid())
            <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                <h3 class="font-semibold text-green-800 mb-2">Pembayaran Selesai</h3>
                <p class="text-sm text-green-700">UTK sudah dibayar pada {{ $unitSelection->utk_payment_date ? $unitSelection->utk_payment_date->format('d-m-Y H:i') : '-' }}</p>
            </div>
        @elseif($unitSelection->isUtkPaymentDeadlinePassed())
            <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                <h3 class="font-semibold text-red-800 mb-2">Batas Waktu Berakhir</h3>
                <p class="text-sm text-red-700">Batas waktu pembayaran UTK telah berakhir. Silakan hubungi admin.</p>
            </div>
        @else
            <form action="{{ route('utk.process', $unitSelection->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="payment_method" value="manual_transfer">

                {{-- Transfer Rekening Pengembang --}}
                <div class="mb-6 p-5 bg-gradient-to-br from-blue-50/60 to-indigo-50/40 border border-blue-200 rounded-2xl">
                    <h4 class="font-bold text-blue-950 text-sm mb-1 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        <span>Rekening Resmi Pengembang: {{ $company->company_name ?? 'Pengembang' }}</span>
                    </h4>
                    <p class="text-xs text-blue-800 mb-4">
                        Silakan lakukan transfer sebesar <strong class="text-blue-900 text-sm">Rp {{ number_format($totalUtk ?? $utkAmount, 0, ',', '.') }}</strong> ke salah satu rekening bank resmi berikut:
                    </p>

                    @if($bankAccounts && $bankAccounts->isNotEmpty())
                        <div class="space-y-3 mb-5">
                            @foreach($bankAccounts as $idx => $bank)
                                <label class="flex items-start justify-between p-3.5 bg-white border-2 rounded-xl cursor-pointer hover:border-blue-400 transition {{ $idx === 0 ? 'border-blue-500 ring-1 ring-blue-400' : 'border-gray-200' }}">
                                    <div class="flex items-start gap-3">
                                        <input type="radio" name="developer_bank_account_id" value="{{ $bank->id }}" class="mt-1 text-blue-600 focus:ring-blue-500" {{ $idx === 0 ? 'checked' : '' }}>
                                        <div>
                                            <span class="px-2 py-0.5 bg-blue-100 text-blue-800 font-extrabold text-xs rounded uppercase">
                                                {{ $bank->bank_name }}
                                            </span>
                                            <p class="font-mono font-bold text-gray-900 text-base mt-1 tracking-wide" id="acc_num_{{ $bank->id }}">{{ $bank->account_number }}</p>
                                            <p class="text-xs text-gray-600">a.n. <strong>{{ $bank->account_holder_name }}</strong></p>
                                            @if($bank->branch)
                                                <p class="text-[11px] text-gray-400">{{ $bank->branch }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <button type="button" onclick="navigator.clipboard.writeText('{{ $bank->account_number }}'); alert('Nomor rekening berhasil disalin!');" class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold rounded-lg transition shrink-0 inline-flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                        <span>Salin No. Rek</span>
                                    </button>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl text-amber-900 text-xs mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>Belum ada rekening bank yang didaftarkan oleh pengembang ini. Silakan hubungi admin pengembang untuk informasi nomor rekening transfer.</span>
                        </div>
                    @endif

                    <h5 class="font-bold text-gray-800 text-xs uppercase tracking-wide mb-3 pt-3 border-t border-blue-200">
                        Form Unggah Bukti Transfer
                    </h5>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-1 text-xs">Foto / Struk Bukti Transfer <span class="text-rose-500">*</span></label>
                            <input type="file" name="proof_of_transfer" accept="image/*,.pdf" required class="w-full border border-gray-300 rounded-xl p-2.5 text-xs bg-white focus:outline-none">
                            <span class="text-[11px] text-gray-500 mt-0.5 block">Format: JPG, PNG, atau PDF (Maks. 10MB)</span>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-semibold mb-1 text-xs">Tanggal Transfer <span class="text-rose-500">*</span></label>
                            <input type="date" name="transfer_date" value="{{ date('Y-m-d') }}" required class="w-full border border-gray-300 rounded-xl p-2.5 text-xs bg-white focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-semibold mb-1 text-xs">Nama Bank Pengirim <span class="text-rose-500">*</span></label>
                            <input type="text" name="sender_bank" placeholder="Contoh: BCA / Mandiri / BRI" required class="w-full border border-gray-300 rounded-xl p-2.5 text-xs bg-white focus:ring-1 focus:ring-blue-500 uppercase">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-semibold mb-1 text-xs">Nama Pemilik Rekening Pengirim <span class="text-rose-500">*</span></label>
                            <input type="text" name="sender_name" value="{{ Auth::user()->name }}" placeholder="Nama sesuai rekening bank" required class="w-full border border-gray-300 rounded-xl p-2.5 text-xs bg-white focus:ring-1 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3.5 rounded-xl hover:bg-blue-700 active:scale-95 transition text-sm shadow-md inline-flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    <span>Kirim Bukti Transfer UTK</span>
                </button>
            </form>
        @endif
    </div>
</div>
@endsection
