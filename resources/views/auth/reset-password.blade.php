<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - APL NUP</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-blue-100 text-blue-600 mb-3 shadow-sm">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">APRO System</h1>
                <p class="text-gray-600 mt-1">Set Password Baru</p>
            </div>

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <ul class="text-sm text-red-700">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @session('status')
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <p class="text-sm text-blue-700">{{ session('status') }}</p>
                </div>
            @endsession

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

            <form action="{{ route('password.update') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Email</label>
                    <input type="email" name="email" value="{{ $email }}" class="w-full border border-gray-300 rounded-lg p-3 bg-gray-50" readonly>
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Password Baru</label>
                    <div class="relative">
                        <input type="password" id="password" name="password" class="w-full border border-gray-300 rounded-lg p-3 pr-10 focus:ring-2 focus:ring-blue-500 focus:border-transparent" required placeholder="Minimal 8 karakter">
                        <button type="button" id="togglePassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 focus:outline-none" title="Lihat/Sembunyikan Password">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Konfirmasi Password Baru</label>
                    <div class="relative">
                        <input type="password" id="password_confirmation" name="password_confirmation" class="w-full border border-gray-300 rounded-lg p-3 pr-10 focus:ring-2 focus:ring-blue-500 focus:border-transparent" required placeholder="Ulangi password baru">
                        <button type="button" id="togglePasswordConfirm" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 focus:outline-none" title="Lihat/Sembunyikan Password">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-3 rounded-lg hover:bg-blue-700 transition">
                    Reset Password
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-800 font-semibold">
                    ← Kembali ke Login
                </a>
            </div>
        </div>
    </div>

    <script>
        const eyeOpenSvg = `<svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>`;
        const eyeClosedSvg = `<svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>`;

        function setupPasswordSanitizer(inputId) {
            const input = document.getElementById(inputId);
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

        document.addEventListener('DOMContentLoaded', function () {
            const togglePassword = document.querySelector('#togglePassword');
            const password = document.querySelector('#password');
            if (togglePassword && password) {
                togglePassword.addEventListener('click', function () {
                    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                    password.setAttribute('type', type);
                    this.innerHTML = type === 'password' ? eyeOpenSvg : eyeClosedSvg;
                });
            }

            const togglePasswordConfirm = document.querySelector('#togglePasswordConfirm');
            const passwordConfirm = document.querySelector('#password_confirmation');
            if (togglePasswordConfirm && passwordConfirm) {
                togglePasswordConfirm.addEventListener('click', function () {
                    const type = passwordConfirm.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordConfirm.setAttribute('type', type);
                    this.innerHTML = type === 'password' ? eyeOpenSvg : eyeClosedSvg;
                });
            }

            setupPasswordSanitizer('password');
            setupPasswordSanitizer('password_confirmation');

            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', () => {
                    const pwd = document.getElementById('password');
                    const pwdConf = document.getElementById('password_confirmation');
                    if (pwd) pwd.value = pwd.value.trim();
                    if (pwdConf) pwdConf.value = pwdConf.value.trim();
                });
            }
        });
    </script>
</body>
</html>