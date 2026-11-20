@extends(auth()->check() && auth()->user()->isAdmin() ? 'layouts.admin' : 'layouts.app')

@section('title', 'Detail Pembayaran UTK')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        @if(auth()->check() && auth()->user()->isAdmin())
            <a href="{{ route('units.show', $utkPayment->unitSelection->id) }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-blue-600 hover:text-blue-800 transition">
                ← Kembali ke Detail Pemilihan Unit
            </a>
        @else
            <a href="{{ route('units.show', $utkPayment->unitSelection->id) }}" class="text-blue-600 hover:text-blue-800 text-sm flex items-center gap-1 font-semibold">
                ← Kembali ke Detail Unit
            </a>
        @endif
    </div>

    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        {{-- Header --}}
        @php
            $headerColor = match($utkPayment->status) {
                'paid'    => 'from-green-500 to-emerald-600',
                'failed'  => 'from-red-500 to-red-600',
                default   => 'from-yellow-500 to-orange-500',
            };
            $statusLabel = match(true) {
                $utkPayment->status === 'paid'                                          => 'Lunas',
                $utkPayment->status === 'failed'                                        => 'Gagal',
                $utkPayment->status === 'pending' && $utkPayment->payment_method === 'cash' => 'Menunggu Konfirmasi Admin',
                $utkPayment->status === 'pending'                                       => 'Menunggu Pembayaran',
                default                                                                 => ucfirst($utkPayment->status),
            };
        @endphp
        <div class="bg-gradient-to-r {{ $headerColor }} px-6 py-5 flex justify-between items-center text-white">
            <div>
                <p class="text-xs opacity-75 uppercase tracking-wide">Nomor Pembayaran UTK</p>
                <p class="font-bold text-xl">{{ $utkPayment->payment_number }}</p>
                <p class="text-sm opacity-90 mt-0.5">{{ $utkPayment->unitSelection->housingUnit->full_identifier }}</p>
            </div>
            <span class="px-4 py-2 rounded-full text-sm font-bold bg-white bg-opacity-20 text-white border border-white border-opacity-40">
                {{ $statusLabel }}
            </span>
        </div>

        <div class="p-6">
            {{-- Flash messages --}}
            @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 rounded-xl p-4 text-green-800 text-sm font-medium">
                    <svg class="w-4 h-4 inline-block text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 rounded-xl p-4 text-red-800 text-sm font-medium">
                    <svg class="w-4 h-4 inline-block text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg> {{ session('error') }}
                </div>
            @endif

            {{-- Detail --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                <div class="bg-gray-50 rounded-xl p-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-3">Informasi Pembayaran</p>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Metode</span>
                            <span class="font-semibold text-gray-800">{{ ucfirst($utkPayment->payment_method) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Tanggal Transaksi</span>
                            <span class="font-semibold text-gray-800">
                                {{ $utkPayment->payment_date ? $utkPayment->payment_date->format('d M Y H:i') : '-' }}
                            </span>
                        </div>
                        @if($utkPayment->expired_at && $utkPayment->status === 'pending')
                        <div class="flex justify-between">
                            <span class="text-gray-500">Batas Waktu</span>
                            <span class="font-bold text-red-600">
                                {{ $utkPayment->expired_at->format('d M Y H:i') }}
                            </span>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="bg-gray-50 rounded-xl p-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-3">Jumlah Tagihan</p>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Total Biaya UTK</span>
                            <span class="font-bold text-gray-800 text-lg">Rp {{ number_format($utkPayment->amount, 0, ',', '.') }}</span>
                        </div>
                        @if(isset($utkPayment->metadata['promo_code']))
                            <div class="flex justify-between text-xs text-green-700 bg-green-50 p-1.5 rounded">
                                <span><svg class="w-4 h-4 inline-block text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg> Kode Promo</span>
                                <span class="font-bold">{{ $utkPayment->metadata['promo_code'] }}</span>
                            </div>
                            <div class="flex justify-between text-xs text-gray-500">
                                <span>Nominal Asli</span>
                                <span class="line-through">Rp {{ number_format($utkPayment->metadata['original_amount'], 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between border-t border-gray-200 pt-2 mt-2">
                            <span class="text-gray-500">Unit Terpilih</span>
                            <span class="font-semibold text-gray-700">{{ $utkPayment->unitSelection->housingUnit->full_identifier }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- DOKU Checkout (hanya untuk transfer) --}}
            @if($utkPayment->status === 'pending' && $utkPayment->payment_method === 'transfer' && $utkPayment->snap_token)
            <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-6 mb-6">
                <h3 class="font-semibold text-indigo-800 mb-2">Lanjutkan pembayaran via DOKU</h3>
                <p class="text-sm text-indigo-700 mb-4">Silakan lanjutkan pembayaran UTK melalui halaman DOKU.</p>
                <a href="{{ $utkPayment->snap_token }}" target="_blank" rel="noopener" class="inline-flex px-6 py-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition font-semibold">Bayar Sekarang via DOKU</a>
            </div>
            @endif

            {{-- Info Tunai Kantor --}}
            @if($utkPayment->status === 'pending' && $utkPayment->payment_method === 'cash')
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 mb-6">
                <h3 class="font-semibold text-yellow-800 mb-2">Menunggu Konfirmasi Pembayaran Tunai</h3>
                <p class="text-sm text-yellow-700 mb-2">Silakan datang ke kantor kami dan lakukan pembayaran UTK secara tunai.</p>
                <p class="text-sm text-yellow-700">Setelah pembayaran diterima, admin akan mengkonfirmasi dan status akan berubah menjadi <strong>Lunas</strong>.</p>
            </div>
            @endif

            {{-- Info Transfer Manual & Bukti Transfer --}}
            @if($utkPayment->status === 'pending' && ($utkPayment->payment_method === 'manual_transfer' || $utkPayment->payment_method === 'bank_transfer'))
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 mb-6">
                <div class="flex items-start justify-between gap-4 flex-wrap">
                    <div>
                        <h3 class="font-bold text-amber-900 text-base mb-1">Bukti Transfer Sedang Diverifikasi</h3>
                        <p class="text-xs text-amber-800 leading-relaxed">
                            Bukti pembayaran transfer Anda telah kami terima pada {{ $utkPayment->created_at->format('d M Y H:i') }}. Saat ini staf keuangan developer sedang memverifikasi dana transfer Anda.
                        </p>
                    </div>
                    @if(auth()->check() && auth()->user()->isAdmin())
                        <div class="flex items-center gap-2">
                            <form action="{{ route('utk.confirm-transfer', $utkPayment->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin menyetujui bukti transfer ini?')">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm transition">
                                    Setujui Pembayaran (Lunas)
                                </button>
                            </form>
                            <form action="{{ route('utk.reject-transfer', $utkPayment->id) }}" method="POST" onsubmit="return confirm('Tolak bukti transfer ini?')">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-sm transition">
                                    Tolak
                                </button>
                            </form>
                        </div>
                    @endif
                </div>

                @if($utkPayment->proof_of_transfer)
                    <div class="mt-4 pt-4 border-t border-amber-200">
                        <p class="text-xs font-bold text-amber-950 mb-2">Lampiran Bukti Transfer Pengirim:</p>
                        <a href="{{ asset('storage/' . $utkPayment->proof_of_transfer) }}" target="_blank" class="inline-flex items-center gap-2 px-3 py-2 bg-white border border-amber-300 rounded-xl text-xs font-semibold text-blue-700 hover:bg-amber-100 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span>Buka / Lihat Bukti Transfer</span>
                        </a>
                    </div>
                @endif
            </div>
            @endif

            @if($utkPayment->status === 'pending' && !config('services.doku.client_id') && $utkPayment->payment_method === 'transfer')

            <div class="bg-green-50 border border-green-200 rounded-xl p-6 mb-6">
                <h3 class="font-semibold text-green-800 mb-2">Simulasi pembayaran</h3>
                <a href="{{ route('utk.mock-success', $utkPayment->id) }}" class="inline-flex items-center gap-2 px-6 py-3 bg-green-600 text-white rounded-xl">Simulasikan pembayaran</a>
            </div>
            @endif
            @if($utkPayment->status === 'paid')
            <div class="bg-gradient-to-r from-emerald-50 to-green-50 border border-green-200 rounded-2xl p-6 mb-6">
                <div class="flex items-start gap-4">
                    <svg class="w-14 h-14 text-emerald-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                    <div class="flex-1">
                        <h3 class="font-bold text-green-800 text-lg mb-1">Pembayaran UTK Berhasil!</h3>
                        <p class="text-sm text-green-700 mb-4">
                            Uang Tanda Jadi untuk unit <strong>{{ $utkPayment->unitSelection->housingUnit->full_identifier }}</strong> telah diterima. Silakan lanjutkan ke pemilihan skema pembayaran rumah.
                        </p>
                        <a href="{{ route('units.show', $utkPayment->unitSelection->id) }}" class="inline-flex items-center gap-2 px-6 py-3 bg-green-600 text-white rounded-xl hover:bg-green-700 transition font-semibold text-sm shadow-md">
                            <svg class="w-4 h-4 inline-block text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg> Lanjut ke Detail Unit & Skema Pembayaran →
                        </a>
                    </div>
                </div>
            </div>
            @endif

            {{-- Action buttons --}}
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('units.show', $utkPayment->unitSelection->id) }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition font-semibold text-sm">
                    ← Kembali ke Detail Unit
                </a>
                <a href="{{ route('dashboard') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition font-semibold text-sm">
                    <svg class="w-4 h-4 inline-block text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg> Ke Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@if($utkPayment->status === 'pending' && $utkPayment->payment_method === 'transfer')
    @push('scripts')
        <script>
            // DOKU returns the browser before its webhook is always processed.
            // Reload while pending so it auto-redirects to unit detail as soon as webhook arrives.
            window.setTimeout(() => window.location.reload(), 4000);
        </script>
    @endpush
@endif