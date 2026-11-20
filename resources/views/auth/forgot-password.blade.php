<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - APRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <div class="text-center mb-8">
                <img src="{{ asset('images/logo.png') }}" alt="APRO Logo" style="width: 100px; height: 100px;" class="mx-auto mb-2">
                <p class="text-gray-600">Reset Password</p>
            </div>

            @if(isset($errors) && $errors->any())
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

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-blue-800 mb-2">Lupa Password?</h3>
                <p class="text-sm text-blue-700">
                    Masukkan email Anda dan kami akan mengirimkan link untuk reset password.
                </p>
            </div>

            <form action="{{ route('password.email') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Email</label>
                    <input type="email" name="email" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent" required placeholder="email@example.com">
                </div>

                <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-3 rounded-lg hover:bg-blue-700 transition">
                    Kirim Link Reset
                </button>
            </form>

            <div class="mt-6 pt-4 border-t border-gray-200">
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-left mb-4">
                    <p class="text-xs font-bold text-amber-900 mb-1 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span>Mitra Pengembang (Operator)?</span>
                    </p>
                    <p class="text-[11px] text-amber-800 leading-relaxed">
                        Jika Anda tidak dapat mengakses email atau membutuhkan reset cepat, Anda dapat menghubungi <strong>Super Admin</strong> untuk bantuan reset password langsung.
                    </p>
                </div>

                <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-800 font-semibold text-sm">
                    ← Kembali ke Login
                </a>
            </div>
        </div>
    </div>
</body>
</html>