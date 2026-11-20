<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Berhasil - APRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-lg w-full bg-white rounded-2xl shadow-xl border border-gray-200 p-8 text-center">
        <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
        </div>

        <h1 class="text-2xl font-bold text-gray-800 mb-2">Pendaftaran Berhasil Dikirim!</h1>
        <p class="text-gray-600 text-sm mb-6 leading-relaxed">
            Terima kasih telah mendaftar sebagai mitra pengembang di APRO. Permohonan Anda saat ini berstatus:
        </p>

        <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-yellow-100 text-yellow-800 rounded-full text-xs font-bold mb-6">
            <svg class="w-4 h-4 inline-block text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> MENUNGGU VERIFIKASI SUPER ADMIN
        </div>

        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-left text-xs text-gray-700 space-y-2 mb-6">
            <p><strong>Nama Perusahaan:</strong> {{ $company->company_name }}</p>
            <p><strong>Bentuk Usaha:</strong> {{ $company->entity_type === 'badan_usaha' ? strtoupper($company->business_type ?? 'Badan Usaha') : 'Perorangan' }}</p>
            <p><strong>Penanggung Jawab:</strong> {{ $company->pic_name }}</p>
            <p><strong>Email Akun:</strong> {{ $user->email }}</p>
            <p><strong>No. WhatsApp:</strong> {{ $company->pic_phone }}</p>
        </div>

        <p class="text-xs text-gray-500 mb-6">
            Super Admin akan meninjau legalitas dan data yang Anda kirimkan. Setelah disetujui, akun Anda akan aktif dan dapat login untuk menginput perumahan & unit perumahan Anda.
        </p>

        <div class="space-y-2">
            <a href="{{ route('developer.status', ['identifier' => $user->email]) }}" class="inline-block w-full py-3 px-6 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-md transition text-sm">
                <svg class="w-4 h-4 inline-block text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg> Cek Status Pendaftaran Saya
            </a>
            <a href="{{ route('login') }}" class="inline-block w-full py-2.5 px-6 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl transition text-sm">
                Kembali ke Halaman Login
            </a>
        </div>
    </div>
</body>
</html>
