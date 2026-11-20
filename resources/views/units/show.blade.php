@extends(auth()->check() && auth()->user()->isAdmin() ? 'layouts.admin' : 'layouts.app')

@section('title', 'Detail Pemilihan Unit')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        @if(auth()->check() && auth()->user()->isAdmin())
            <a href="{{ route('admin.units.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-blue-600 hover:text-blue-800 transition">
                ← Kembali ke Pemilihan Unit Admin
            </a>
        @else
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-blue-600 hover:text-blue-800 transition">
                ← Kembali ke Dashboard
            </a>
        @endif
    </div>

    <div class="bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Detail Pemilihan Unit</h1>

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

        @if($selection->status === 'cancelled')
            <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6 flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h4 class="font-bold text-red-800 text-sm flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Pemesanan Unit Dibatalkan (Cancelled)</span>
                    </h4>
                    <p class="text-xs text-red-600 mt-0.5">Unit rumah telah dibuka kembali ke katalog. {{ $selection->notes ? 'Catatan: ' . $selection->notes : '' }}</p>
                </div>
                @if(auth()->check() && auth()->user()->isAdmin())
                    @if($selection->housingUnit && $selection->housingUnit->status === 'available')
                        <form action="{{ route('admin.units.restore', $selection->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memulihkan pemesanan unit ini (Tidak Jadi Batal)? Status unit akan kembali dipesan (Booked).')">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-bold text-xs shadow-sm transition inline-flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                <span>Tidak Jadi Batal (Pulihkan Pemesanan)</span>
                            </button>
                        </form>
                    @else
                        <span class="px-3.5 py-1.5 bg-slate-100 text-slate-500 border border-slate-200 rounded-xl text-xs font-semibold inline-flex items-center gap-1.5 cursor-not-allowed" title="Unit ini sudah terisi/dipesan konsumen lain sehingga tidak dapat dipulihkan.">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            Unit Sudah Terisi / Tidak Tersedia
                        </span>
                    @endif
                @endif
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <h3 class="font-semibold text-gray-800 mb-2">Informasi Peserta</h3>
                <p class="text-sm text-gray-600"><strong>Nama Pemesan:</strong> {{ $selection->nupRegistration?->full_name ?: $selection->user->name }}</p>
                <p class="text-sm text-gray-600"><strong>Akun Pemesan:</strong> {{ $selection->user->name }} ({{ $selection->user->email }})</p>
                <p class="text-sm text-gray-600"><strong>No. Antrian:</strong> {{ $selection->nupRegistration->queue_number ?? 'N/A' }}</p>
            </div>

            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <h3 class="font-semibold text-gray-800 mb-2">Informasi Unit & Perumahan</h3>
                <p class="text-sm text-gray-600"><strong>Unit:</strong> {{ $selection->housingUnit->full_identifier }}</p>
                <p class="text-sm text-gray-600"><strong>Tipe:</strong> {{ $selection->housingUnit->formatted_type }}</p>
                <p class="text-sm text-gray-600"><strong>Harga:</strong> Rp {{ number_format($selection->housingUnit->price, 0, ',', '.') }}</p>
                @if($selection->housingUnit->project)
                    <p class="text-sm text-gray-600 mt-1"><strong>Perumahan:</strong> {{ $selection->housingUnit->project->name }}</p>
                    @if($selection->housingUnit->project->company)
                        <p class="text-xs text-gray-500 inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span>Pengembang: {{ $selection->housingUnit->project->company->company_name }}</span>
                        </p>
                    @endif
                    @if($selection->housingUnit->project->site_plan)
                        <p class="mt-2 text-xs">
                            <a href="{{ asset('storage/' . $selection->housingUnit->project->site_plan) }}" target="_blank" class="text-blue-600 hover:underline font-semibold inline-flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                                <span>Buka Site Plan Kawasan</span>
                            </a>
                        </p>
                    @endif
                @endif
            </div>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <h3 class="font-semibold text-blue-800 mb-2">Status Pemilihan</h3>
            <p class="text-sm text-blue-700">
                Urutan Pemilihan: <strong>#{{ $selection->selection_order }}</strong><br>
                Tanggal Pemilihan: <strong>{{ $selection->selection_date->format('d-m-Y H:i') }}</strong><br>
                Status: <strong>{{ strtoupper($selection->status) }}</strong>
                @if($selection->utk_payment_deadline && !$selection->isUtkPaid() && !$selection->isUtkWaived() && $selection->status !== 'cancelled')
                    <br>Batas Waktu Bayar UTK: <strong class="{{ $selection->isUtkPaymentDeadlinePassed() ? 'text-red-600 font-bold' : 'text-blue-900 font-bold' }}">{{ $selection->utk_payment_deadline->format('d-m-Y H:i') }} ({{ $selection->isUtkPaymentDeadlinePassed() ? 'Telah Lewat Batas Waktu' : 'Tersisa ' . now()->diffForHumans($selection->utk_payment_deadline, true) }})</strong>
                @endif
            </p>
        </div>

        <!-- Status Pembayaran UTK — hanya tampil jika unit belum dibatalkan -->
        @php
            // Inisialisasi default agar variabel selalu terdefinisi di seluruh halaman
            $utkPayment      = $selection->utkPayments()->where('nup_registration_id', $selection->nup_registration_id)->latest()->first();
            $scheme          = $selection->payment_scheme;
            $isCash          = $selection->isCash() || $selection->isUtkWaived();
            $isCashScheme    = $isCash;
            $isInHouseScheme = $selection->isInHouse();
            $isKprScheme     = $selection->isKpr();
            $isMitra         = $selection->isMitraPintar();
            $utkPaid         = $isCash || ($utkPayment && $utkPayment->status === 'paid');
            $utkPending      = !$isCash && $utkPayment && $utkPayment->status === 'pending';
        @endphp

        {{-- Section Khusus Skema Kredit Mitra Pintar --}}
        @if($isMitra && $selection->status !== 'cancelled')
            <div class="mb-6 rounded-2xl border p-6 {{ $selection->isMitraApproved() ? 'bg-emerald-50/70 border-emerald-300' : ($selection->isMitraRejected() ? 'bg-rose-50/70 border-rose-300' : 'bg-amber-50/70 border-amber-300') }}">
                <div class="flex items-start justify-between flex-wrap gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <svg class="w-8 h-8 text-blue-600 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3m0 0V11"/></svg>
                            <h3 class="text-base font-bold {{ $selection->isMitraApproved() ? 'text-emerald-950' : ($selection->isMitraRejected() ? 'text-rose-950' : 'text-amber-950') }}">
                                Skema Pembayaran: Kredit Mitra Pintar
                            </h3>
                            @if($selection->isMitraApproved())
                                <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 text-xs font-bold rounded-full"><svg class="w-4 h-4 inline-block text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Disetujui (ACC)</span>
                            @elseif($selection->isMitraRejected())
                                <span class="px-2.5 py-0.5 bg-rose-100 text-rose-800 text-xs font-bold rounded-full"><svg class="w-4 h-4 inline-block text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg> Ditolak</span>
                            @else
                                <span class="px-2.5 py-0.5 bg-amber-100 text-amber-800 text-xs font-bold rounded-full"><svg class="w-4 h-4 inline-block text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Menunggu Verifikasi</span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-700">
                            <strong>Instansi / Perusahaan Mitra:</strong> {{ $selection->mitra_institution_name ?? '-' }}
                        </p>
                        @if($selection->mitra_document)
                            <div class="pt-1">
                                <a href="{{ asset('storage/' . $selection->mitra_document) }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-700 hover:text-blue-900 bg-white px-3 py-1.5 rounded-lg border border-blue-200 shadow-sm hover:underline">
                                    <svg class="w-4 h-4 inline-block text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg> Buka Dokumen Kerja Sama yang Diunggah
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                @if($selection->isMitraPending())
                    <div class="mt-4 p-3.5 bg-amber-50 rounded-xl border border-amber-300 text-xs text-amber-900">
                        @if(auth()->check() && auth()->user()->isAdmin())
                            <p class="font-bold text-amber-950 flex items-center gap-1.5 text-sm">
                                <svg class="w-4 h-4 inline-block text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Dokumen Calon Pembeli Menunggu Verifikasi Anda
                            </p>
                            <p class="text-xs text-amber-800 mt-1">
                                Calon pembeli mengajukan dokumen kerja sama Mitra Pintar (Instansi: <strong>{{ $selection->mitra_institution_name ?? '-' }}</strong>). Silakan periksa berkas dokumen di atas, lalu tentukan persetujuan melalui tombol aksi di bawah (<strong>Setujui Dokumen</strong> atau <strong>Tolak Dokumen</strong>).
                            </p>
                        @else
                            <p class="font-semibold"><svg class="w-4 h-4 inline-block text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Dokumen Anda sedang diverifikasi oleh Tim Pengembang / Operator.</p>
                            <p class="text-[11px] text-gray-600 mt-0.5">
                                Pembayaran UTK (Pokok + PPN 11% + VE 20%) akan otomatis dibuka segera setelah dokumen kerja sama ini disetujui (ACC).
                            </p>
                        @endif
                    </div>
                @elseif($selection->isMitraApproved())
                    <div class="mt-4 p-3 bg-white/80 rounded-xl border border-emerald-200 text-xs text-emerald-900">
                        @if(auth()->check() && auth()->user()->isAdmin())
                            <p class="font-semibold text-emerald-950"><svg class="w-4 h-4 inline-block text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Dokumen Kerja Sama Mitra Pintar telah Anda setujui (ACC)</p>
                            <p class="text-[11px] text-emerald-800 mt-0.5">
                                Diverifikasi pada: {{ $selection->mitra_verified_at ? $selection->mitra_verified_at->format('d M Y H:i') : '-' }}. Calon pembeli telah diizinkan untuk melakukan pembayaran UTK secara mandiri.
                            </p>
                        @else
                            <p class="font-semibold"><svg class="w-4 h-4 inline-block text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg> Dokumen Kerja Sama Mitra Pintar telah disetujui!</p>
                            <p class="text-[11px] text-gray-600 mt-0.5">
                                Diverifikasi pada: {{ $selection->mitra_verified_at ? $selection->mitra_verified_at->format('d M Y H:i') : '-' }}. Silakan lanjutkan ke pembayaran UTK di bawah.
                            </p>
                        @endif
                    </div>
                @elseif($selection->isMitraRejected())
                    <div class="mt-4 p-4 bg-white/90 rounded-xl border border-rose-200 text-xs text-rose-900 space-y-3">
                        <div>
                            <p class="font-bold text-sm text-rose-800"><svg class="w-4 h-4 inline-block text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> Dokumen Kerja Sama Mitra Pintar Ditolak</p>
                            <p class="mt-1 text-xs text-gray-800 bg-rose-50 p-2 rounded-lg border border-rose-200">
                                <strong>Catatan Operator:</strong> {{ $selection->mitra_rejection_reason ?? 'Dokumen tidak memenuhi persyaratan kerja sama.' }}
                            </p>
                        </div>

                        @if(!auth()->check() || !auth()->user()->isAdmin())
                            <!-- Opsi Solusi untuk Calon Pembeli -->
                            <div class="pt-2 border-t border-rose-100">
                                <p class="font-bold text-gray-800 mb-2">Pilihan Solusi untuk Melanjutkan Pemesanan:</p>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <!-- Opsi 1: Upload Ulang Dokumen Mitra -->
                                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-200">
                                        <p class="font-bold text-gray-900 mb-1">1. Unggah Ulang Dokumen Mitra</p>
                                        <p class="text-[11px] text-gray-500 mb-2">Perbaiki dokumen yang buram atau kirim SK/surat kerja sama resmi.</p>
                                        <form action="{{ route('units.mitra.reupload', $selection->id) }}" method="POST" enctype="multipart/form-data" class="space-y-2">
                                            @csrf
                                            <input type="text" name="mitra_institution_name" value="{{ $selection->mitra_institution_name }}" placeholder="Nama Instansi" class="w-full text-xs p-1.5 border border-gray-300 rounded bg-white">
                                            <input type="file" name="mitra_document" accept=".pdf,image/*" required class="w-full text-xs p-1 border border-gray-300 rounded bg-white">
                                            <button type="submit" class="w-full py-1.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-lg text-xs transition">
                                                <svg class="w-4 h-4 inline-block text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Ajukan Ulang Verifikasi
                                            </button>
                                        </form>
                                    </div>

                                    <!-- Opsi 2: Beralih ke Skema Lain -->
                                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 space-y-2">
                                        <p class="font-bold text-gray-900 mb-1">2. Beralih ke Skema Pembayaran Lain</p>
                                        <p class="text-[11px] text-gray-500 mb-2">Anda tetap dapat memesan unit ini menggunakan skema reguler.</p>
                                        
                                        <form action="{{ route('units.mitra.switch', $selection->id) }}" method="POST" class="space-y-1.5">
                                            @csrf
                                            <button type="submit" name="target_scheme" value="kpr_bank" class="w-full text-left px-3 py-2 bg-white hover:bg-blue-50 border border-gray-200 rounded-lg text-xs font-semibold text-blue-900 transition flex items-center justify-between">
                                                <span><svg class="w-4 h-4 inline-block text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M3 10h18M5 6l7-3 7 3M4 10v11M20 10v11M8 14v4M12 14v4M16 14v4"/></svg> Ganti ke KPR Bank Reguler</span>
                                                <span>→</span>
                                            </button>
                                            <button type="submit" name="target_scheme" value="installment" class="w-full text-left px-3 py-2 bg-white hover:bg-indigo-50 border border-gray-200 rounded-lg text-xs font-semibold text-indigo-900 transition flex items-center justify-between">
                                                <span><svg class="w-4 h-4 inline-block text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg> Ganti ke In-House Developer</span>
                                                <span>→</span>
                                            </button>
                                            <button type="submit" name="target_scheme" value="cash" class="w-full text-left px-3 py-2 bg-emerald-50 hover:bg-emerald-100 border border-emerald-300 rounded-lg text-xs font-bold text-emerald-900 transition flex items-center justify-between">
                                                <span><svg class="w-4 h-4 inline-block text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg> Ganti ke Tunai (Bebas UTK Rp 0)</span>
                                                <span>→</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="pt-2 border-t border-rose-100 text-xs text-rose-700">
                                <svg class="w-4 h-4 inline-block text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Pengajuan ditolak. Menunggu calon pembeli mengunggah berkas perbaikan atau beralih ke skema pembayaran lain.
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @endif

        @if($selection->status !== 'cancelled')
        @if($isCash)
            <!-- Banner Khusus Tunai: Bebas Biaya UTK -->
            <div class="bg-emerald-50 border border-emerald-300 rounded-2xl p-6 mb-6">
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <div class="flex items-center gap-3.5">
                        <div class="w-8 h-8 mx-auto flex items-center justify-center"><svg class="w-4 h-4 inline-block text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg></div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-emerald-950 text-base">Skema Pembayaran: Tunai (Cash)</h3>
                                <span class="px-2.5 py-0.5 bg-emerald-200 text-emerald-900 rounded-full text-xs font-bold">Bebas UTK</span>
                            </div>
                            <p class="text-xs text-emerald-800 mt-1">
                                Khusus pembayaran Tunai, <strong>Biaya UTK Dihapuskan (Rp 0)</strong>. Anda dapat langsung melanjutkan proses pembuatan & penandatanganan Surat Pemesanan Rumah (SPR).
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @elseif($utkPayment)
            <div class="bg-purple-50/70 border border-purple-200 rounded-xl p-4 mb-6">
                <h3 class="font-bold text-purple-900 text-xs uppercase tracking-wider mb-2">Status Pembayaran UTK</h3>
                <p class="text-xs text-purple-800 space-y-1">
                    <span class="block">
                        <strong class="text-purple-950">Status:</strong>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold
                            @if($utkPaid) bg-emerald-100 text-emerald-800
                            @elseif($utkPending) bg-amber-100 text-amber-800
                            @else bg-rose-100 text-rose-800 @endif
                        ">
                            @if($utkPaid) Lunas
                            @elseif($utkPending) Sedang Diverifikasi Admin
                            @else {{ strtoupper($utkPayment->status) }} @endif
                        </span>
                    </span>
                    @if($utkPayment->payment_date)
                        <span class="block"><strong>Tanggal Pembayaran:</strong> {{ $utkPayment->payment_date->format('d-m-Y H:i') }}</span>
                    @endif
                    @if($utkPayment->amount)
                        <span class="block"><strong>Jumlah:</strong> Rp {{ number_format($utkPayment->amount, 0, ',', '.') }}</span>
                    @endif
                    @if($utkPayment->payment_number)
                        <span class="block"><strong>No. Pembayaran:</strong> {{ $utkPayment->payment_number }}</span>
                    @endif
                </p>
                
                @if($utkPayment->status === 'failed')
                    @php
                        $reason = data_get($utkPayment->payment_gateway_response, 'reason');
                    @endphp
                    <div class="mt-3 p-3 bg-red-100 border border-red-200 rounded-lg text-sm text-red-800">
                        <p class="font-semibold"><svg class="w-4 h-4 inline-block text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> Pembayaran UTK Sebelumnya Ditolak / Gagal</p>
                        @if($reason)
                            <p class="mt-1 text-xs text-red-700"><strong>Alasan:</strong> {{ $reason }}</p>
                        @endif
                        <p class="mt-1 text-xs">Silakan lakukan pembayaran ulang UTK untuk melanjutkan proses.</p>
                        @if($selection->status !== 'cancelled')
                            @if(!auth()->check() || !auth()->user()->isAdmin())
                                <div class="mt-3">
                                    <a href="{{ route('utk.create', $selection->id) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-semibold shadow-sm">
                                        <svg class="w-4 h-4 inline-block text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Bayar Ulang UTK Sekarang
                                    </a>
                                </div>
                            @else
                                <p class="text-xs text-red-600 font-semibold mt-2">
                                    <svg class="w-4 h-4 inline-block text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Menunggu calon pembeli melakukan transaksi ulang pembayaran UTK.
                                </p>
                            @endif
                        @endif
                    </div>
                @endif
                
                @if($utkPending)
                    <div class="mt-3 flex gap-2 flex-wrap items-center">
                        <a href="{{ route('utk.show', $utkPayment->id) }}" class="inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm">
                            Lihat Detail Pembayaran
                        </a>
                        @if(!auth()->check() || !auth()->user()->isAdmin())
                            <a href="{{ route('utk.create', $selection->id) }}" class="inline-block px-4 py-2 bg-gray-100 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-200 transition text-sm">
                                Ganti Metode Pembayaran
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        @else
            @if($selection->isMitraPending())
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
                    <h3 class="font-semibold text-amber-900 mb-2">Status Pembayaran UTK</h3>
                    <p class="text-sm text-amber-800">
                        <strong>Status:</strong>
                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-900">
                            <svg class="w-4 h-4 inline-block text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> MENUNGGU VERIFIKASI MITRA
                        </span>
                    </p>
                    <p class="text-xs text-gray-600 mt-2">
                        @if(auth()->check() && auth()->user()->isAdmin())
                            Menunggu verifikasi Anda atas dokumen kerja sama Mitra Pintar. Calon pembeli baru dapat melanjutkan ke pembayaran UTK setelah Anda menyetujui (ACC) dokumen tersebut.
                        @else
                            Pembayaran UTK akan dibuka secara otomatis setelah dokumen kerja sama Mitra Pintar Anda disetujui (ACC) oleh Operator pengembang.
                        @endif
                    </p>
                </div>
            @elseif($selection->isMitraRejected())
                <div class="bg-rose-50 border border-rose-200 rounded-lg p-4 mb-6">
                    <h3 class="font-semibold text-rose-900 mb-2">Status Pembayaran UTK</h3>
                    <p class="text-sm text-rose-800">
                        <strong>Status:</strong>
                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-900">
                            <svg class="w-4 h-4 inline-block text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg> DITAHAN (MITRA DITOLAK)
                        </span>
                    </p>
                    <p class="text-xs text-rose-700 mt-2">
                        @if(auth()->check() && auth()->user()->isAdmin())
                            Pengajuan Mitra Pintar telah ditolak. Calon pembeli dapat mengunggah perbaikan dokumen atau beralih ke skema pembayaran lain.
                        @else
                            Silakan unggah ulang dokumen kerja sama yang valid pada panel di atas, atau beralih ke skema pembayaran lain (KPR Bank / In-House / Tunai).
                        @endif
                    </p>
                </div>
            @elseif($selection->isKpr() && (!$selection->kprScreening || !in_array($selection->kprScreening->status, ['approved', 'layak_bersyarat'], true)))
                @if(!$selection->kprScreening)
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
                        <h3 class="font-semibold text-amber-900 mb-2">Status Pembayaran UTK</h3>
                        <p class="text-sm text-amber-800">
                            <strong>Status:</strong>
                            <span class="px-2 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-900">
                                <svg class="w-4 h-4 inline-block text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> DITAHAN SEMENTARA (MENUNGGU BERKAS KPR)
                            </span>
                        </p>
                        <p class="text-xs text-amber-700 mt-2">
                            Khusus skema KPR, Anda diwajibkan melengkapi data pekerjaan dan mengunggah dokumen persyaratan KPR (SK Kerja, Slip Gaji, Rekening Koran) terlebih dahulu untuk verifikasi skor kredit (Pefindo/Bank). Pembayaran UTK baru dibuka setelah Anda dinyatakan <strong>LAYAK</strong>.
                        </p>
                        @if(!auth()->check() || !auth()->user()->isAdmin())
                            <div class="mt-3">
                                <a href="{{ route('kpr.create', $selection->id) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-semibold shadow-sm">
                                    <span>Upload Berkas Persyaratan KPR Sekarang</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </a>
                            </div>
                        @endif
                    </div>
                @elseif($selection->kprScreening->status === 'rejected')
                    <div class="bg-rose-50 border border-rose-200 rounded-lg p-4 mb-6">
                        <h3 class="font-semibold text-rose-900 mb-2">Status Pembayaran UTK</h3>
                        <p class="text-sm text-rose-800">
                            <strong>Status:</strong>
                            <span class="px-2 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-900">
                                <svg class="w-4 h-4 inline-block text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg> TIDAK LAYAK KPR (DITOLAK)
                            </span>
                        </p>
                        <p class="text-xs text-rose-700 mt-2">
                            Hasil evaluasi skor kredit KPR Anda dinyatakan belum memenuhi syarat perbankan. Anda disarankan beralih ke skema In-House (Cicilan Pengembang) atau Tunai.
                        </p>
                        <div class="mt-3">
                            <a href="{{ route('kpr.outcome', $selection->kprScreening->id) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition text-sm font-semibold shadow-sm">
                                <span>Lihat Rekomendasi & Alihkan Skema</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        </div>
                    </div>
                @else
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <h3 class="font-semibold text-blue-900 mb-2">Status Pembayaran UTK</h3>
                        <p class="text-sm text-blue-800">
                            <strong>Status:</strong>
                            <span class="px-2 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-900">
                                <svg class="w-4 h-4 inline-block text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> MENUNGGU VERIFIKASI SKOR KREDIT PEFINDO / BANK
                            </span>
                        </p>
                        <p class="text-xs text-blue-700 mt-2">
                            Berkas persyaratan KPR Anda telah terkirim dan sedang dalam proses analisis skor kredit (Pefindo/Bank). Pembayaran UTK sebesar <strong>Rp {{ number_format($selection->utk_amount, 0, ',', '.') }}</strong> akan otomatis dibuka setelah hasil evaluasi diverifikasi dan dinyatakan <strong>LAYAK</strong>.
                        </p>
                        <div class="mt-3">
                            <a href="{{ route('kpr.show', $selection->kprScreening->id) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-sm font-semibold">
                                <span>Pantau Status Evaluasi Berkas KPR</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>
                @endif
            @else
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
                    <h3 class="font-semibold text-gray-800 mb-2">Status Pembayaran UTK</h3>
                    @if($selection->isKpr() && $selection->kprScreening && in_array($selection->kprScreening->status, ['approved', 'layak_bersyarat'], true))
                        <div class="mb-3 p-3 bg-emerald-50 border border-emerald-200 rounded-xl">
                            <span class="px-2 py-0.5 rounded text-xs font-bold bg-emerald-600 text-white">HASIL SKOR KREDIT: LAYAK</span>
                            <p class="text-xs text-emerald-800 mt-1 font-medium">
                                Verifikasi skor kredit Pefindo/Bank Anda telah dinyatakan <strong>LAYAK (Disetujui)</strong>. Silakan bayar UTK untuk mengunci unit kavling Anda.
                            </p>
                        </div>
                    @endif
                    <p class="text-sm text-gray-700">
                        <strong>Status:</strong>
                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-800">
                            <svg class="w-4 h-4 inline-block text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> BELUM DIBAYAR
                        </span>
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Nominal UTK yang harus dibayar: <strong>Rp {{ number_format($selection->utk_amount, 0, ',', '.') }}</strong>
                    </p>

                    @if(!auth()->check() || !auth()->user()->isAdmin())
                        <div class="mt-3">
                            <a href="{{ route('utk.create', $selection->id) }}" class="inline-block px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-semibold shadow-sm">
                                Bayar UTK Sekarang
                            </a>
                        </div>
                    @else
                        <div class="mt-3 p-2.5 bg-blue-50/70 border border-blue-200 rounded-xl text-xs text-blue-800">
                            <svg class="w-4 h-4 inline-block text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> <strong>Status Operator:</strong> Calon pembeli belum melakukan pembayaran Uang Tanda Komitmen (UTK). Menunggu pembayaran mandiri dari pembeli.
                        </div>
                    @endif
                </div>
            @endif
        @endif
        @endif
        {{-- Akhir blok UTK — tidak tampil jika unit CANCELLED --}}




        {{-- ====== LANGKAH SELANJUTNYA setelah UTK Lunas / Bebas UTK ====== --}}
        @if($utkPaid && in_array($selection->status, ['confirmed', 'selected'], true))
            @if(!$selection->sprDocuments || $selection->sprDocuments->isEmpty())
                @php
                    $scheme = $selection->payment_scheme;
                    $isCashScheme = in_array($scheme, ['cash', 'tunai', 'tunai_keras'], true);
                    $isInHouseScheme = in_array($scheme, ['installment', 'inhouse'], true);
                    $isKprScheme = in_array($scheme, ['kpr', 'kpr_bank', 'kpr_bank_flpp'], true);
                @endphp

                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-2xl p-6 mb-6">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div class="flex-1">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-1.5">
                                @if($isKprScheme && !$selection->kprScreening)
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200 self-start">
                                        Tahap Lengkapi Berkas KPR Bank
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200 self-start">
                                        Tahap Penerbitan Dokumen SPR Resmi
                                    </span>
                                @endif
                                @if(Auth::user()->isAdmin())
                                    <span class="px-2 py-0.5 rounded-md bg-amber-100 text-amber-800 font-mono text-[11px] font-bold self-start">
                                        Mode Operator
                                    </span>
                                @endif
                            </div>

                            <h3 class="font-bold text-gray-900 text-lg mb-1">
                                @if($isKprScheme && !$selection->kprScreening)
                                    Unit Terkunci — Silakan Lengkapi Berkas Persyaratan KPR Bank
                                @else
                                    Unit Terkunci — Dokumen SPR Sedang Disiapkan oleh Pengembang
                                @endif
                            </h3>

                            <p class="text-xs text-gray-600 leading-relaxed mb-4">
                                @if($isCashScheme)
                                    Pilihan unit telah dikonfirmasi dengan skema pembayaran <strong>Tunai (Bebas UTK)</strong>. Tim administrasi pengembang sedang menyiapkan Surat Pemesanan Rumah (SPR) resmi. Anda akan dapat meninjau dan menandatangani dokumen setelah diterbitkan oleh pengembang.
                                @elseif($isInHouseScheme)
                                    Uang Tanda Komitmen (UTK) telah diverifikasi lunas. Tim administrasi pengembang sedang menyusun jadwal cicilan bulanan dan menerbitkan Surat Pemesanan Rumah (SPR) resmi skema <strong>In-House Developer</strong>. Dokumen akan langsung muncul di sini setelah diterbitkan.
                                @elseif($isKprScheme && !$selection->kprScreening)
                                    Uang Tanda Jadi (UTK) telah diverifikasi lunas dan unit berhasil dikunci atas nama Anda. Sebelum dokumen SPR resmi diterbitkan untuk diajukan ke Bank rekanan, <strong>Anda wajib melengkapi formulir data pekerjaan dan mengunggah berkas persyaratan KPR (SK Kerja, Slip Gaji, Rekening Koran)</strong>.
                                @else
                                    Uang Tanda Komitmen (UTK) telah diverifikasi lunas. Tim pengembang sedang menerbitkan dokumen Surat Pemesanan Rumah (SPR) resmi sebagai kelengkapan berkas pengajuan ke Bank rekanan.
                                @endif
                            </p>

                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pt-3 border-t border-blue-100">
                                @if($isKprScheme && !$selection->kprScreening)
                                    <a href="{{ route('kpr.create', $selection->id) }}" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md transition">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span>Lengkapi Berkas Persyaratan KPR Sekarang &rarr;</span>
                                    </a>
                                @elseif($isKprScheme && $selection->kprScreening)
                                    <div class="flex flex-wrap items-center gap-2">
                                        <div class="flex items-center gap-2 text-xs text-emerald-800 font-semibold bg-emerald-50 p-2.5 rounded-xl border border-emerald-200">
                                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <span>Berkas KPR sudah diajukan (Menunggu verifikasi & penerbitan SPR)</span>
                                        </div>
                                        <a href="{{ route('kpr.show', $selection->kprScreening->id) }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-xl text-xs font-bold transition">
                                            <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Lihat Status Screening KPR &rarr;</span>
                                        </a>
                                    </div>
                                @else
                                    <div class="flex items-center gap-2 text-xs text-blue-700 font-semibold bg-white p-2.5 rounded-xl border border-blue-100">
                                        <svg class="w-4 h-4 text-blue-600 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                        <span>Menunggu Operator Pengembang menerbitkan Surat Pemesanan Rumah (SPR)</span>
                                    </div>
                                @endif

                                @if(Auth::user()->isAdmin())
                                    <a href="{{ route('admin.spr.create', $selection->id) }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md transition">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        <span>Operator: Terbitkan SPR Sekarang &rarr;</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif

        {{-- SPR & Cicilan KPR Info --}}
        @if($selection->sprDocuments && $selection->sprDocuments->isNotEmpty())
            @php
                $spr = $selection->sprDocuments()->latest()->first();
            @endphp
            @if($spr)
                <div class="bg-green-50 border border-green-200 rounded-2xl p-5 mb-6">
                    <h3 class="font-semibold text-green-800 mb-3 flex items-center gap-2"><svg class="w-4 h-4 inline-block text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg> SPR & Status Pembayaran Rumah</h3>
                    <div class="grid grid-cols-2 gap-3 text-sm mb-4">
                        <div>
                            <span class="text-gray-500">Total Harga:</span>
                            <span class="font-semibold ml-2">Rp {{ number_format($spr->unit_price, 0, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Skema:</span>
                            <span class="font-semibold ml-2 uppercase">{{ $spr->payment_scheme }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Sudah Dibayar:</span>
                            <span class="font-semibold ml-2 text-green-600">Rp {{ number_format($spr->getTotalSudahDibayar(), 0, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Sisa Pembayaran:</span>
                            <span class="font-semibold ml-2 text-red-600">Rp {{ number_format($spr->getSisaPembayaran(), 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mb-4">
                        <div class="bg-green-500 h-2 rounded-full" style="width: {{ $spr->getTotalPaymentProgress() }}%"></div>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        @if($spr->isDpPaid() || $spr->down_payment <= 0 || Auth::user()->isAdmin())
                            <a href="{{ route('spr.show', $spr->id) }}"
                               class="px-4 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 transition text-sm font-semibold">
                                <svg class="w-4 h-4 inline-block text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg> Lihat Detail SPR
                            </a>
                        @else
                            <a href="{{ route('kpr-payment.create', $spr->id) }}"
                               class="px-4 py-2 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 transition text-sm font-semibold shadow-sm flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                Bayar Uang Muka (DP)
                            </a>
                            <span class="px-3.5 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold flex items-center gap-1.5 border border-slate-200">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                                Dokumen SPR Terbuka Setelah DP Lunas
                            </span>
                        @endif
                        @if(!$spr->isFullyPaid())
                            @if($spr->payment_scheme === 'kpr')
                                <span class="px-4 py-2 bg-blue-50 text-blue-700 rounded-xl text-sm font-semibold border border-blue-200">
                                    <svg class="w-4 h-4 inline-block text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Pembayaran cicilan KPR melalui Bank penyedia KPR
                                </span>
                            @elseif($spr->payment_scheme === 'mitra_pintar')
                                <span class="px-4 py-2 bg-amber-50 text-amber-800 rounded-xl text-xs font-semibold border border-amber-300">
                                    <svg class="w-4 h-4 inline-block text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Menunggu Pencairan Dana Pelunasan dari Lembaga Mitra Pintar ke Rekening Pengembang
                                </span>
                            @elseif(!auth()->check() || !auth()->user()->isAdmin())
                                <a href="{{ route('kpr-payment.create', $spr->id) }}"
                                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition text-sm font-semibold shadow-sm">
                                    Bayar Cicilan / Lunasi
                                </a>
                            @else
                                <span class="px-4 py-2 bg-blue-50 text-blue-700 rounded-xl text-xs font-semibold border border-blue-200">
                                    <svg class="w-4 h-4 inline-block text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Menunggu Pembayaran Cicilan oleh Calon Pembeli
                                </span>
                            @endif
                        @else
                            <span class="px-4 py-2 bg-green-100 text-green-700 rounded-xl text-sm font-semibold">
                                <svg class="w-4 h-4 inline-block text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Rumah Telah Lunas 100%
                            </span>
                        @endif
                    </div>
                </div>
            @endif
        @endif

        {{-- Tombol aksi bawah --}}
        <div class="flex gap-2 flex-wrap items-center">
            @php
                $isAdminUser = auth()->check() && auth()->user()->isAdmin();
            @endphp

            @if($selection->status === 'cancelled')
                @if(!$isAdminUser)
                    <a href="{{ route('units.create', $selection->nup_registration_id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-semibold text-sm shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <span>Pilih Unit Kembali</span>
                    </a>
                @endif

                @if($isAdminUser)
                    @if($selection->housingUnit && $selection->housingUnit->status === 'available')
                        <form action="{{ route('admin.units.restore', $selection->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin memulihkan pemesanan ini (Tidak Jadi Batal)? Status unit akan kembali dipesan (Booked).')">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-500 text-white rounded-xl hover:bg-amber-600 transition font-semibold text-sm shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                <span>Tidak Jadi Batal (Pulihkan)</span>
                            </button>
                        </form>
                    @else
                        <span class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-100 text-slate-500 rounded-xl font-medium text-sm border border-slate-200 cursor-not-allowed" title="Unit ini sudah terisi/dipesan konsumen lain sehingga tidak dapat dipulihkan.">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <span>Unit Sudah Terisi (Tidak Bisa Dipulihkan)</span>
                        </span>
                    @endif

                    <a href="{{ route('admin.units.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition text-sm font-semibold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Kembali ke Daftar Unit</span>
                    </a>
                @endif
            @else
                @if($isAdminUser)
                    {{-- ================= AKSI KHUSUS OPERATOR / ADMIN ================= --}}
                    @if($selection->isMitraPintar() && $selection->isMitraPending())
                        {{-- Tombol Setujui Mitra Pintar --}}
                        <form action="{{ route('admin.units.mitra.approve', $selection->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin menyetujui dokumen kerja sama Mitra Pintar calon pembeli ini?')">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition font-semibold text-sm shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span>Setujui Dokumen Mitra</span>
                            </button>
                        </form>

                        {{-- Tombol Tolak Mitra Pintar --}}
                        <button type="button" onclick="document.getElementById('mitraRejectModal').classList.remove('hidden')" class="inline-flex items-center gap-2 px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl transition font-semibold text-sm shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            <span>Tolak Dokumen Mitra</span>
                        </button>
                    @endif

                    @if($utkPayment && $utkPayment->status === 'pending')
                        <a href="{{ route('utk.show', $utkPayment->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl transition font-semibold text-sm shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Lihat Bukti Transfer UTK</span>
                        </a>

                        <form action="{{ route('utk.confirm-transfer', $utkPayment->id) }}" method="POST" class="inline" onsubmit="return confirm('Setujui pembayaran transfer UTK ini sekarang?')">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition font-semibold text-sm shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Setujui UTK (Admin)</span>
                            </button>
                        </form>
                    @endif

                    @if($selection->status === 'selected' && (!$utkPayment || $utkPayment->status !== 'paid'))
                        <button type="button" onclick="document.getElementById('cancelUnitModal').classList.remove('hidden')" class="px-4 py-2.5 bg-gray-100 text-red-600 hover:bg-red-50 border border-red-200 rounded-xl transition text-sm font-semibold flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            <span>Batalkan Pemilihan Unit</span>
                        </button>
                    @endif

                    <a href="{{ route('admin.units.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition text-sm font-semibold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Kembali ke Daftar Unit</span>
                    </a>
                @else
                    {{-- ================= AKSI CALON PEMBELI (PESERTA) ================= --}}
                    @if(!$selection->isUtkPaid() && !$selection->isUtkPaymentDeadlinePassed() && !$isCash)
                        @if($selection->isMitraPending())
                            <span class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-50 text-amber-800 border border-amber-200 rounded-xl font-semibold text-sm">
                                <svg class="w-4 h-4 inline-block text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Menunggu Verifikasi Dokumen oleh Operator
                            </span>
                        @elseif($selection->isKpr() && (!$selection->kprScreening || !in_array($selection->kprScreening->status, ['approved', 'layak_bersyarat'], true)))
                            @if(!$selection->kprScreening)
                                <a href="{{ route('kpr.create', $selection->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition font-semibold text-sm shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <span>Lengkapi Berkas KPR (Wajib)</span>
                                </a>
                            @else
                                <a href="{{ route('kpr.show', $selection->kprScreening->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition font-semibold text-sm shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Lihat Status Evaluasi KPR</span>
                                </a>
                            @endif
                        @elseif($utkPayment && $utkPayment->status === 'pending')
                            <div class="flex items-center gap-2 flex-wrap">
                                <a href="{{ route('utk.show', $utkPayment->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl transition font-semibold text-sm shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>Bukti Transfer Sedang Diverifikasi</span>
                                </a>
                            </div>
                        @else
                            <a href="{{ route('utk.create', $selection->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 text-white rounded-xl hover:bg-green-700 transition font-semibold text-sm shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                <span>{{ $utkPayment && $utkPayment->status === 'failed' ? 'Bayar Ulang UTK' : 'Bayar UTK' }}</span>
                            </a>
                        @endif
                    @endif

                    @if($selection->status === 'selected' && (!$utkPayment || $utkPayment->status !== 'paid'))
                        <button type="button" onclick="document.getElementById('cancelUnitModal').classList.remove('hidden')" class="px-4 py-2.5 bg-gray-100 text-red-600 hover:bg-red-50 border border-red-200 rounded-xl transition text-sm font-semibold flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            <span>Batalkan Pemilihan Unit</span>
                        </button>
                    @endif

                    @if(($isKprScheme || $selection->isKpr()) && $selection->kprScreening && in_array($selection->kprScreening->status, ['approved', 'layak_bersyarat'], true))
                        <a href="{{ route('kpr.show', $selection->kprScreening->id) }}" class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition font-semibold text-sm flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span>Lihat Berkas Screening KPR</span>
                        </a>
                    @endif
                @endif
            @endif
        </div>
    </div>
</div>

{{-- MODAL KONFIRMASI PEMBATALAN UNIT SEPIHAK --}}
<div id="cancelUnitModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="document.getElementById('cancelUnitModal').classList.add('hidden')"></div>

    <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200">
            <form action="{{ route('units.cancel', $selection->id) }}" method="POST">
                @csrf
                
                {{-- Header Modal --}}
                <div class="bg-rose-50 px-6 py-5 border-b border-rose-100 flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-rose-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-rose-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900" id="modal-title">Konfirmasi Pembatalan Pemilihan Unit</h3>
                        <p class="text-xs text-rose-700 font-medium mt-0.5">Harap baca konsekuensi pembatalan sepihak sebelum melanjutkan</p>
                    </div>
                </div>

                {{-- Body Modal --}}
                <div class="px-6 py-5 space-y-4">
                    {{-- Alert Box Konsekuensi --}}
                    <div class="bg-rose-50/70 border border-rose-200 rounded-xl p-4 text-xs text-rose-900 space-y-2.5">
                        <div class="font-bold flex items-center gap-1.5 text-rose-800 text-sm">
                            <svg class="w-4 h-4 inline-block text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>Ketentuan Pembatalan Sepihak:</span>
                        </div>
                        <ul class="space-y-2.5 list-none pl-1">
                            @if($selection->isUtkPaid())
                                <li class="flex items-start gap-2">
                                    <span class="font-bold text-rose-600 mt-0.5">•</span>
                                    <div>
                                        <strong class="text-rose-950">Uang Tanda Komitmen (UTK) Hangus:</strong>
                                        <p class="text-slate-600 mt-0.5">Pembayaran UTK sebesar <strong class="text-rose-700">Rp {{ number_format($selection->utk_amount, 0, ',', '.') }}</strong> yang telah dibayarkan dinyatakan <strong class="text-rose-700 font-bold">HANGUS</strong> dan tidak dapat dikembalikan (non-refundable) sesuai ketentuan pemesanan sepihak.</p>
                                    </div>
                                </li>
                            @else
                                <li class="flex items-start gap-2">
                                    <span class="font-bold text-amber-600 mt-0.5">•</span>
                                    <div>
                                        <strong class="text-amber-950">Belum Ada Pembayaran UTK:</strong>
                                        <p class="text-slate-600 mt-0.5">Anda belum membayar biaya UTK untuk unit ini. Pembatalan tidak akan memotong dana UTK Anda.</p>
                                    </div>
                                </li>
                            @endif
                            <li class="flex items-start gap-2">
                                <span class="font-bold text-rose-600 mt-0.5">•</span>
                                <div>
                                    <strong class="text-rose-950">Unit Rumah Dilepas:</strong>
                                    <p class="text-slate-600 mt-0.5">Unit <strong class="text-slate-800">{{ $selection->housingUnit->full_identifier ?? 'Pilihan' }}</strong> akan segera dilepas dan dibuka kembali untuk calon pembeli lain.</p>
                                </div>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="font-bold text-rose-600 mt-0.5">•</span>
                                <div>
                                    <strong class="text-rose-950">Pemilihan Unit Berikutnya:</strong>
                                    <p class="text-slate-600 mt-0.5">Anda dapat memilih kembali unit yang tersedia menggunakan tiket NUP yang aktif.</p>
                                </div>
                            </li>
                        </ul>
                    </div>

                    {{-- Form Alasan Pembatalan --}}
                    <div>
                        <label for="cancel_reason" class="block text-xs font-bold text-slate-700 mb-1.5">
                            Alasan Pembatalan <span class="text-slate-400 font-normal">(Opsional)</span>
                        </label>
                        <textarea id="cancel_reason" name="reason" rows="2" placeholder="Contoh: Berubah pikiran, ingin mengganti kavling lain, kendala dana, dll..." class="w-full text-xs rounded-xl border border-slate-300 focus:border-rose-500 focus:ring focus:ring-rose-200 transition p-2.5"></textarea>
                    </div>
                </div>

                {{-- Footer Modal --}}
                <div class="bg-slate-50 px-6 py-4 border-t border-slate-100 flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5">
                    <button type="button" onclick="document.getElementById('cancelUnitModal').classList.add('hidden')" class="w-full sm:w-auto px-4 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-700 hover:bg-slate-100 text-xs font-bold transition">
                        Batal / Tetap Simpan Unit
                    </button>
                    <button type="submit" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-sm shadow-rose-500/20 transition flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <span>Ya, Saya Paham & Tetap Batalkan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL TOLAK MITRA PINTAR (ADMIN / OPERATOR) --}}
@if(auth()->check() && auth()->user()->isAdmin())
<div id="mitraRejectModal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100">
        <h3 class="text-base font-bold text-gray-900 mb-1 flex items-center gap-2">
            <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Tolak Dokumen Mitra Pintar</span>
        </h3>
        <p class="text-xs text-gray-500 mb-4">
            Masukkan catatan atau alasan penolakan dokumen kerja sama mitra pintar calon pembeli <strong>{{ $selection->user->name ?? 'Calon Pembeli' }}</strong>.
        </p>

        <form action="{{ route('admin.units.mitra.reject', $selection->id) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-xs font-bold text-gray-700 mb-1">Alasan Penolakan <span class="text-red-500">*</span></label>
                <textarea name="rejection_reason" required rows="3" class="w-full border border-gray-300 rounded-xl p-2.5 text-xs focus:ring-2 focus:ring-rose-500 focus:outline-none" placeholder="Contoh: Dokumen kerja sama tidak valid / Perusahaan belum terdaftar dalam MoU / Berkas buram"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2">
                <button type="button" onclick="document.getElementById('mitraRejectModal').classList.add('hidden')" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-lg shadow-sm transition">
                    Kirim Penolakan
                </button>
            </div>
        </form>
    </div>
</div>
@endif

@endsection