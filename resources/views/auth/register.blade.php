<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - APRO Portal Properti</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-3 sm:p-6 lg:p-8">
    <div class="max-w-4xl w-full bg-white rounded-2xl sm:rounded-3xl shadow-xl sm:shadow-2xl border border-slate-200/80 overflow-hidden my-auto">
        <div class="grid grid-cols-1 md:grid-cols-12 min-h-[580px]">
            
            <!-- SISI FORM: Registrasi Member (Prioritas di Mobile) -->
            <div class="md:col-span-7 p-6 sm:p-8 lg:p-10 flex flex-col justify-center order-1">
                <!-- Mobile Only Header -->
                <div class="md:hidden flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
                    <img src="{{ asset('images/logo.png') }}" alt="APRO Logo" class="w-10 h-10 object-contain">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 leading-tight">APRO System</h2>
                        <p class="text-[11px] text-slate-500">Pendaftaran Akun Baru</p>
                    </div>
                </div>

                <div class="mb-5 sm:mb-6">
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Daftar Akun Baru</h1>
                    <p class="text-xs text-slate-500 mt-1">Lengkapi informasi berikut untuk membuat akun konsumen properti Anda.</p>
                </div>

                <!-- Alerts -->
                @if(isset($errors) && $errors->any())
                    <div class="bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-xl p-3 mb-4 shadow-sm">
                        <div class="flex items-center gap-2 font-semibold mb-1">
                            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>Periksa kembali data pendaftaran:</span>
                        </div>
                        <ul class="list-disc pl-5 space-y-0.5 text-[11px]">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @session('success')
                    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xl p-3 mb-4 shadow-sm flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endsession

                @session('error')
                    <div class="bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-xl p-3 mb-4 shadow-sm flex items-center gap-2">
                        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endsession

                <!-- Form Pendaftaran -->
                <form action="{{ route('register.post') }}" method="POST" class="space-y-3">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="Nama lengkap sesuai KTP" class="w-full border border-slate-300 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Email *</label>
                            <input type="email" name="email" value="{{ old('email') }}" required placeholder="nama@email.com" class="w-full border border-slate-300 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">No. WhatsApp *</label>
                            <input type="tel" name="phone" value="{{ old('phone') }}" required placeholder="08xxxxxxxxxx" class="w-full border border-slate-300 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Password *</label>
                            <div class="relative">
                                <input type="password" id="password" name="password" required placeholder="Minimal 8 karakter" class="w-full border border-slate-300 rounded-xl p-2.5 pr-10 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                                <button type="button" id="togglePassword" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 focus:outline-none p-1 rounded-lg transition" title="Lihat/Sembunyikan Password">
                                    <svg class="w-4 h-4 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg class="w-4 h-4 eye-closed hidden text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Konfirmasi Password *</label>
                            <div class="relative">
                                <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="Ulangi password" class="w-full border border-slate-300 rounded-xl p-2.5 pr-10 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                                <button type="button" id="togglePasswordConfirm" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 focus:outline-none p-1 rounded-lg transition" title="Lihat/Sembunyikan Password">
                                    <svg class="w-4 h-4 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg class="w-4 h-4 eye-closed hidden text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-start pt-1">
                        <input type="checkbox" id="terms" name="terms" required class="mt-0.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <label for="terms" class="ml-2 text-xs text-slate-600">
                            Saya menyetujui <a href="#" class="text-blue-600 hover:underline font-medium">Syarat & Ketentuan</a> serta <a href="#" class="text-blue-600 hover:underline font-medium">Kebijakan Privasi</a> APRO.
                        </label>
                    </div>

                    <button type="submit" class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transition text-sm flex items-center justify-center gap-2 mt-2">
                        Daftar Akun
                    </button>
                </form>

                <!-- Divider -->
                <div class="relative my-3.5">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-slate-200"></div>
                    </div>
                    <div class="relative flex justify-center text-[11px]">
                        <span class="px-3 bg-white text-slate-400 font-medium">atau daftar dengan</span>
                    </div>
                </div>

                <!-- Google Button -->
                <form action="{{ route('auth.google') }}" method="GET">
                    <button type="submit" class="w-full flex items-center justify-center gap-2.5 py-2.5 px-4 border border-slate-300 hover:bg-slate-50 active:bg-slate-100 text-slate-700 font-medium rounded-xl text-xs sm:text-sm transition shadow-sm">
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                        <span>Daftar dengan Google</span>
                    </button>
                </form>

                <p class="text-center text-xs text-slate-600 mt-4">
                    Sudah punya akun?
                    <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-800 font-semibold transition">Masuk di sini</a>
                </p>
            </div>

            <!-- SISI BRANDING & FITUR (Modern & Elegan) -->
            <div class="md:col-span-5 bg-gradient-to-br from-slate-900 via-blue-950 to-indigo-950 p-6 sm:p-8 lg:p-10 text-white flex flex-col justify-between relative overflow-hidden order-2">
                <!-- Background Glow -->
                <div class="absolute -top-16 -right-16 w-52 h-52 bg-blue-500/15 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-16 -left-16 w-52 h-52 bg-indigo-500/15 rounded-full blur-3xl pointer-events-none"></div>

                <!-- Bagian Atas: Branding Platform -->
                <div class="relative z-10">
                    <div class="hidden md:flex items-center gap-3 mb-6">
                        <div class="w-11 h-11 bg-white rounded-xl p-1.5 shadow-md flex items-center justify-center shrink-0">
                            <img src="{{ asset('images/logo.png') }}" alt="APRO Logo" class="w-full h-full object-contain">
                        </div>
                        <div>
                            <span class="text-base font-bold tracking-tight block leading-tight">APRO System</span>
                            <span class="text-[11px] text-blue-200">Portal Properti & Perumahan</span>
                        </div>
                    </div>

                    <h2 class="text-lg sm:text-xl font-bold leading-snug mb-2.5 text-white">
                        Wujudkan Kepemilikan Hunian Idaman Lebih Mudah.
                    </h2>
                    <p class="text-xs text-slate-300 leading-relaxed mb-5">
                        Daftar dan nikmati kemudahan memilih unit perumahan, pendaftaran NUP, screening KPR, serta verifikasi berkas secara online.
                    </p>

                    <div class="space-y-2 text-xs text-slate-300 hidden sm:block">
                        <div class="flex items-center gap-2.5">
                            <span class="w-4 h-4 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <span>Katalog unit perumahan resmi & terverifikasi</span>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <span class="w-4 h-4 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <span>Proses pengajuan KPR transparan & terpadu</span>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <span class="w-4 h-4 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <span>Riwayat pembayaran & dokumen tersimpan aman</span>
                        </div>
                    </div>
                </div>

                <!-- Bagian Bawah: Box Mitra Pengembang -->
                <div class="relative z-10 mt-6 pt-5 border-t border-white/10">
                    <div class="bg-white/5 border border-white/15 rounded-2xl p-4 backdrop-blur-sm">
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <span class="text-xs font-semibold text-white">Mitra Pengembang Perumahan</span>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[10px] bg-blue-400/20 text-blue-200 border border-blue-400/30 font-medium">
                                Developer
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-300 mb-3 leading-relaxed">
                            Ingin mengelola dan memasarkan perumahan Anda secara mandiri di portal APRO?
                        </p>
                        <div class="grid grid-cols-2 gap-2 text-center">
                            <a href="{{ route('developer.register') }}" class="py-2 px-3 bg-blue-600 hover:bg-blue-500 text-white font-semibold rounded-xl text-xs transition shadow-sm flex items-center justify-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                <span>Daftar Pengembang</span>
                            </a>
                            <a href="{{ route('developer.status') }}" class="py-2 px-3 bg-white/10 hover:bg-white/20 text-white font-medium rounded-xl text-xs transition border border-white/20 flex items-center justify-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                <span>Cek Status</span>
                            </a>
                        </div>
                    </div>

                    <p class="text-center text-[11px] text-slate-400 mt-3">
                        &copy; {{ date('Y') }} APRO System. Seluruh hak cipta dilindungi.
                    </p>
                </div>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const togglePassword = document.querySelector('#togglePassword');
            const password = document.querySelector('#password');
            if (togglePassword && password) {
                togglePassword.addEventListener('click', function () {
                    const isPassword = password.getAttribute('type') === 'password';
                    password.setAttribute('type', isPassword ? 'text' : 'password');
                    const eyeOpen = this.querySelector('.eye-open');
                    const eyeClosed = this.querySelector('.eye-closed');
                    if (eyeOpen && eyeClosed) {
                        eyeOpen.classList.toggle('hidden', isPassword);
                        eyeClosed.classList.toggle('hidden', !isPassword);
                    }
                });
            }

            const togglePasswordConfirm = document.querySelector('#togglePasswordConfirm');
            const passwordConfirm = document.querySelector('#password_confirmation');
            if (togglePasswordConfirm && passwordConfirm) {
                togglePasswordConfirm.addEventListener('click', function () {
                    const isPassword = passwordConfirm.getAttribute('type') === 'password';
                    passwordConfirm.setAttribute('type', isPassword ? 'text' : 'password');
                    const eyeOpen = this.querySelector('.eye-open');
                    const eyeClosed = this.querySelector('.eye-closed');
                    if (eyeOpen && eyeClosed) {
                        eyeOpen.classList.toggle('hidden', isPassword);
                        eyeClosed.classList.toggle('hidden', !isPassword);
                    }
                });
            }

            // Aturan Spasi Password:
            // 1. Spasi di awal dicegat
            // 2. Spasi di tengah diizinkan
            // 3. Spasi di akhir dipotong otomatis saat blur / submit
            function setupPasswordSanitizer(input) {
                if (!input) return;
                input.addEventListener('keydown', function (e) {
                    if ((e.key === ' ' || e.code === 'Space' || e.keyCode === 32) && (this.selectionStart === 0 || this.value.length === 0)) {
                        e.preventDefault();
                    }
                });
                input.addEventListener('input', function () {
                    if (this.value.startsWith(' ')) {
                        this.value = this.value.replace(/^\s+/, '');
                    }
                });
                input.addEventListener('blur', function () {
                    this.value = this.value.trim();
                });
            }

            setupPasswordSanitizer(password);
            setupPasswordSanitizer(passwordConfirm);

            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', () => {
                    if (password) password.value = password.value.trim();
                    if (passwordConfirm) passwordConfirm.value = passwordConfirm.value.trim();
                });
            }
        });
    </script>
</body>
</html>
