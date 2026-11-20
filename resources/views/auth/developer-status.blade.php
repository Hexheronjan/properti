<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Status Pendaftaran Pengembang - APRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen py-10 px-4 text-slate-800">
    <div class="max-w-2xl mx-auto">

        <!-- Top Navigation -->
        <div class="flex items-center justify-between mb-8">
            <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-blue-600 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Kembali ke Halaman Login
            </a>
            <a href="{{ route('developer.register') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 hover:underline">
                Daftar Pengembang Baru &rarr;
            </a>
        </div>

        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-blue-100 text-blue-600 rounded-2xl mb-4 shadow-sm">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Cek Status Pendaftaran Pengembang</h1>
            <p class="text-sm text-slate-500 mt-2">Periksa status verifikasi permohonan akun mitra developer perumahan Anda.</p>
        </div>

        <!-- Form Pencarian -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6 sm:p-8 mb-6">
            <form action="{{ route('developer.status') }}" method="GET" class="space-y-4">
                <div>
                    <label for="identifier" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Email Login / Email Perusahaan / NIK Penanggung Jawab
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text"
                               name="identifier"
                               id="identifier"
                               value="{{ $identifier ?? '' }}"
                               required
                               placeholder="Contoh: ptpraja@gmail.com atau 3578012345670001"
                               class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 text-sm font-medium transition placeholder:text-slate-400">
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1.5">Masukkan salah satu: Email yang Anda daftarkan, atau 16 digit NIK PIC pengembang.</p>
                </div>

                <button type="submit" class="w-full py-3 px-6 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold rounded-xl shadow-sm hover:shadow transition flex items-center justify-center gap-2 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    Cek Status Pendaftaran
                </button>
            </form>
        </div>

        <!-- Hasil Pencarian -->
        @if($searched)
            @if($developer)
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden mb-6">
                    {{-- Status Banner --}}
                    @if($developer->status === 'approved')
                        <div class="bg-gradient-to-r from-emerald-500 to-green-600 px-6 py-5 text-white flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <div>
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-white text-emerald-800 uppercase tracking-wide">
                                        Disetujui & Aktif
                                    </span>
                                    <h2 class="text-lg font-bold text-white mt-0.5">Akun Operator Telah Aktif</h2>
                                </div>
                            </div>
                        </div>
                    @elseif($developer->status === 'rejected')
                        <div class="bg-gradient-to-r from-rose-500 to-red-600 px-6 py-5 text-white flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </div>
                                <div>
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-white text-rose-800 uppercase tracking-wide">
                                        Pendaftaran Ditolak
                                    </span>
                                    <h2 class="text-lg font-bold text-white mt-0.5">Permohonan Tidak Disetujui</h2>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-gradient-to-r from-amber-500 to-yellow-600 px-6 py-5 text-white flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div>
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-white text-amber-800 uppercase tracking-wide">
                                        Sedang Diverifikasi
                                    </span>
                                    <h2 class="text-lg font-bold text-white mt-0.5">Menunggu Verifikasi Super Admin</h2>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Isi Kartu --}}
                    <div class="p-6 sm:p-8 space-y-6">
                        <!-- Rincian Data Perusahaan -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100">
                                <p class="text-slate-400 font-medium mb-0.5">Nama Perusahaan / Usaha</p>
                                <p class="text-sm font-bold text-slate-800">{{ $developer->company_name }}</p>
                                <p class="text-[11px] text-slate-500 mt-0.5">
                                    {{ $developer->entity_type === 'badan_usaha' ? strtoupper($developer->business_type ?? 'Badan Usaha') : 'Perorangan' }}
                                    @if($developer->company_initials) ({{ $developer->company_initials }}) @endif
                                </p>
                            </div>
                            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100">
                                <p class="text-slate-400 font-medium mb-0.5">Penanggung Jawab (PIC)</p>
                                <p class="text-sm font-bold text-slate-800">{{ $developer->pic_name }}</p>
                                <p class="text-[11px] text-slate-500 mt-0.5">NIK: {{ substr($developer->pic_nik, 0, 6) }}******{{ substr($developer->pic_nik, -4) }}</p>
                            </div>
                            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100">
                                <p class="text-slate-400 font-medium mb-0.5">Email Login Akun</p>
                                <p class="text-sm font-bold text-slate-800">{{ $developer->user?->email ?? $developer->email }}</p>
                            </div>
                            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100">
                                <p class="text-slate-400 font-medium mb-0.5">Tanggal Pendaftaran</p>
                                <p class="text-sm font-bold text-slate-800">{{ $developer->created_at->format('d M Y, H:i') }} WIB</p>
                            </div>
                        </div>

                        <!-- Feedback Khusus Status -->
                        @if($developer->status === 'approved')
                            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl">
                                <p class="text-xs text-emerald-900 leading-relaxed font-medium">
                                    <strong>Kabar Baik!</strong> Akun Operator Anda telah disetujui
                                    @if($developer->approved_at) pada {{ $developer->approved_at->format('d/m/Y H:i') }} @endif.
                                    Anda kini dapat login menggunakan email dan password yang Anda daftarkan untuk mulai memposting kawasan perumahan dan stok unit rumah.
                                </p>
                            </div>
                            <a href="{{ route('login') }}" class="block w-full text-center py-3.5 px-6 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold rounded-xl shadow-md transition text-sm flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                                Masuk ke Akun Operator (Login)
                            </a>
                        @elseif($developer->status === 'rejected')
                            <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl space-y-2">
                                <div class="flex items-center gap-1.5 text-xs font-bold text-rose-800">
                                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    Alasan Penolakan dari Super Admin:
                                </div>
                                <div class="p-3 bg-white rounded-lg border border-rose-200 text-xs text-rose-900 font-mono">
                                    {{ $developer->rejection_reason ?? 'Data atau berkas legalitas belum memenuhi ketentuan.' }}
                                </div>
                                <p class="text-[11px] text-rose-700">
                                    Anda dapat memperbaiki berkas atau data Anda dan mengajukan kembali pendaftaran mitra pengembang.
                                </p>
                            </div>
                            <a href="{{ route('developer.register', ['reapply' => $developer->id]) }}" class="block w-full text-center py-3.5 px-6 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold rounded-xl shadow-md transition text-sm flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                Ajukan Pendaftaran Ulang
                            </a>
                        @else
                            <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl">
                                <p class="text-xs text-amber-900 leading-relaxed font-medium">
                                    Permohonan Anda saat ini berada dalam antrean verifikasi oleh tim Super Admin. Tim kami sedang meninjau kelengkapan legalitas dan identitas yang Anda kirimkan. Proses verifikasi biasanya memakan waktu maksimal 1x24 jam kerja.
                                </p>
                            </div>
                            <div class="text-center">
                                <span class="text-xs text-slate-400">Silakan simpan halaman ini dan cek kembali secara berkala.</span>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <!-- Tidak Ditemukan -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-8 text-center mb-6">
                    <div class="w-12 h-12 bg-rose-50 text-rose-500 rounded-2xl flex items-center justify-center mx-auto mb-3 text-2xl font-bold">
                        ?
                    </div>
                    <h3 class="text-base font-bold text-slate-900">Data Pendaftaran Tidak Ditemukan</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                        Tidak ditemukan pendaftaran pengembang dengan data: <span class="font-bold text-slate-700 font-mono">{{ $identifier }}</span>. Pastikan Anda memasukkan email login atau NIK yang sama persis saat mendaftar.
                    </p>
                    <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-3">
                        <a href="{{ route('developer.status') }}" class="px-4 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                            Coba Input Ulang
                        </a>
                        <a href="{{ route('developer.register') }}" class="px-4 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition shadow-sm">
                            Daftar Pengembang Baru
                        </a>
                    </div>
                </div>
            @endif
        @else
            <!-- Helper Information jika belum mencari -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6 sm:p-8">
                <h3 class="text-sm font-bold text-slate-900 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Informasi Status Pendaftaran
                </h3>
                <div class="space-y-3 text-xs text-slate-600">
                    <div class="flex items-start gap-3">
                        <span class="w-6 h-6 rounded-full bg-amber-100 text-amber-700 font-bold flex items-center justify-center text-xs flex-shrink-0 mt-0.5">1</span>
                        <div>
                            <p class="font-bold text-slate-800">Menunggu Verifikasi</p>
                            <p class="text-slate-500">Berkas legalitas Anda sedang dalam antrean verifikasi oleh Super Admin.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 font-bold flex items-center justify-center text-xs flex-shrink-0 mt-0.5">2</span>
                        <div>
                            <p class="font-bold text-slate-800">Disetujui (Approved)</p>
                            <p class="text-slate-500">Akun Anda langsung aktif dan dapat langsung digunakan login ke Panel Operator.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="w-6 h-6 rounded-full bg-rose-100 text-rose-700 font-bold flex items-center justify-center text-xs flex-shrink-0 mt-0.5">3</span>
                        <div>
                            <p class="font-bold text-slate-800">Ditolak (Rejected)</p>
                            <p class="text-slate-500">Super Admin akan menyertakan alasan penolakan dan Anda dapat mengajukan pendaftaran ulang.</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Footer -->
        <div class="text-center mt-8 text-xs text-slate-400">
            &copy; {{ date('Y') }} APRO System. Seluruh hak cipta dilindungi.
        </div>

    </div>
</body>
</html>
