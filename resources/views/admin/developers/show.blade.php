@extends('layouts.admin')

@section('title', 'Detail Pengembang - ' . $developer->company_name)

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.developers.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-semibold flex items-center gap-1">
            ← Kembali ke Daftar Pengembang
        </a>
    </div>

    @session('success')
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl p-4 mb-6 text-sm">
            {{ session('success') }}
        </div>
    @endsession

    @session('error')
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl p-4 mb-6 text-sm">
            {{ session('error') }}
        </div>
    @endsession

    @if(session('reset_password_data'))
        @php $rp = session('reset_password_data'); @endphp
        <div class="mb-6 bg-gradient-to-r from-emerald-50 to-teal-50 border-2 border-emerald-300 rounded-2xl p-5 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 inline-block text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                    <div>
                        <h3 class="font-bold text-emerald-900 text-sm">Password Operator Berhasil Direset!</h3>
                        <p class="text-xs text-emerald-700 mt-0.5">Informasikan password baru ini kepada penanggung jawab pengembang:</p>
                        
                        <div class="mt-2.5 bg-white border border-emerald-200 rounded-xl p-3 text-xs space-y-1 font-mono text-gray-800">
                            <div><strong>Email Login:</strong> {{ $rp['email'] }}</div>
                            <div><strong>Password Baru:</strong> <span class="bg-amber-100 px-2 py-0.5 rounded text-amber-900 font-bold select-all">{{ $rp['password'] }}</span></div>
                        </div>
                    </div>
                </div>

                @if(!empty($rp['wa_url']))
                    <a href="{{ $rp['wa_url'] }}" target="_blank" class="shrink-0 inline-flex items-center gap-1.5 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold rounded-xl text-xs shadow-md transition self-start sm:self-auto">
                        <svg class="w-4 h-4 inline-block text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg> Kirim ke WhatsApp PIC
                    </a>
                @endif
            </div>
        </div>
    @endif

    <!-- Header Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sm:p-8 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 pb-6 border-b border-gray-200">
            <div class="flex items-center gap-4">
                @if($developer->logo)
                    <img src="{{ asset('storage/' . $developer->logo) }}" alt="Logo" class="w-16 h-16 rounded-xl object-contain border border-gray-200 p-1 bg-white">
                @else
                    <div class="w-16 h-16 rounded-xl bg-blue-100 text-blue-700 font-bold flex items-center justify-center text-xl">
                        {{ substr($developer->company_name, 0, 2) }}
                    </div>
                @endif
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $developer->company_name }}</h1>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Bentuk Usaha: 
                        <span class="font-semibold text-gray-700 uppercase">
                            {{ $developer->entity_type === 'badan_usaha' ? ($developer->business_type ?? 'Badan Usaha') : 'Perorangan' }}
                        </span>
                        • Terdaftar {{ $developer->created_at->format('d M Y, H:i') }}
                    </p>
                </div>
            </div>

            <!-- Status Badge & Action -->
            <div>
                @if($developer->status === 'approved')
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-green-100 text-green-800 rounded-xl font-bold text-sm">
                        <svg class="w-4 h-4 inline-block text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> AKTIF & DISETUJUI
                    </div>
                @elseif($developer->status === 'pending')
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-amber-100 text-amber-800 rounded-xl font-bold text-sm">
                        <svg class="w-4 h-4 inline-block text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> MENUNGGU VERIFIKASI
                    </div>
                @else
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-red-100 text-red-800 rounded-xl font-bold text-sm">
                        <svg class="w-4 h-4 inline-block text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg> DITOLAK
                    </div>
                @endif
            </div>
        </div>

        @if($developer->status === 'rejected' && $developer->rejection_reason)
            <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-xl text-xs text-red-800">
                <p class="font-bold mb-1">Alasan Penolakan:</p>
                <p>{{ $developer->rejection_reason }}</p>
            </div>
        @endif

        @if($developer->status === 'approved' && $developer->approved_at)
            <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-xl text-xs text-green-800 flex items-center gap-2">
                <svg class="w-4 h-4 inline-block text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Disetujui pada {{ $developer->approved_at->format('d F Y, H:i') }} oleh {{ $developer->approvedBy->name ?? 'Super Admin' }}.
            </div>
        @endif
    </div>

    <!-- Details Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Legalitas & Kantor -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-base font-bold text-gray-800 mb-4 pb-2 border-b border-gray-100 flex items-center gap-2">
                <svg class="w-4 h-4 inline-block text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg> Data Legalitas & Perusahaan
            </h2>
            <div class="space-y-3 text-sm">
                <div>
                    <span class="text-xs text-gray-500 block">Nama Badan Usaha / Usaha:</span>
                    <span class="font-semibold text-gray-900">{{ $developer->company_name }}</span>
                </div>
                <div>
                    <span class="text-xs text-gray-500 block">Bentuk Usaha:</span>
                    <span class="font-semibold text-gray-900">{{ $developer->entity_type === 'badan_usaha' ? strtoupper($developer->business_type ?? 'PT/CV') : 'Perorangan' }}</span>
                </div>
                <div>
                    <span class="text-xs text-gray-500 block">Nomor Induk Berusaha (NIB):</span>
                    <span class="font-semibold text-gray-900">{{ $developer->nib ?: '-' }}</span>
                </div>
                <div>
                    <span class="text-xs text-gray-500 block">Alamat Kantor / Operasional:</span>
                    <span class="text-gray-800">{{ $developer->address }}</span>
                </div>
                <div class="grid grid-cols-2 gap-3 pt-2">
                    <div>
                        <span class="text-xs text-gray-500 block">Telepon Kantor:</span>
                        <span class="font-semibold text-gray-900">{{ $developer->phone }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 block">Email Perusahaan:</span>
                        <span class="font-semibold text-gray-900">{{ $developer->email }}</span>
                    </div>
                </div>
                @if($developer->website)
                    <div>
                        <span class="text-xs text-gray-500 block">Website:</span>
                        <a href="{{ $developer->website }}" target="_blank" class="text-blue-600 hover:underline">{{ $developer->website }}</a>
                    </div>
                @endif
                @if($developer->company_initials)
                    <div>
                        <span class="text-xs text-gray-500 block">Inisial Dokumen SPR:</span>
                        <span class="font-bold text-blue-700">{{ $developer->company_initials }}</span>
                    </div>
                @endif
                @if($developer->signature_image)
                    <div class="pt-2">
                        <span class="text-xs text-gray-500 block mb-1">Tanda Tangan Developer (Transparan / Remove-BG):</span>
                        <div class="p-2 border border-dashed border-gray-300 rounded-lg bg-gray-50 inline-block">
                            <img src="{{ asset('storage/' . $developer->signature_image) }}" alt="TTD Developer" class="max-h-16 object-contain">
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- PIC & Akun Operator -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-base font-bold text-gray-800 mb-4 pb-2 border-b border-gray-100 flex items-center gap-2">
                <svg class="w-4 h-4 inline-block text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg> Data Penanggung Jawab (Akun Operator)
            </h2>
            <div class="space-y-3 text-sm">
                <div>
                    <span class="text-xs text-gray-500 block">Nama Lengkap PIC:</span>
                    <span class="font-semibold text-gray-900">{{ $developer->pic_name }}</span>
                </div>
                <div>
                    <span class="text-xs text-gray-500 block">NIK PIC (KTP):</span>
                    <span class="font-semibold text-gray-900 font-mono">{{ $developer->pic_nik ?: ($developer->user->nik ?? '-') }}</span>
                </div>
                <div>
                    <span class="text-xs text-gray-500 block">Alamat PIC:</span>
                    <span class="text-gray-800">{{ $developer->pic_address ?: '-' }}</span>
                </div>
                <div>
                    <span class="text-xs text-gray-500 block">Nomor WhatsApp:</span>
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $developer->pic_phone) }}" target="_blank" class="text-green-600 font-semibold hover:underline flex items-center gap-1">
                        <svg class="w-4 h-4 inline-block text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg> {{ $developer->pic_phone }}
                    </a>
                </div>
                <div class="pt-2 border-t border-gray-100">
                    <span class="text-xs text-gray-500 block">Email Akun Login:</span>
                    <span class="font-semibold text-gray-900">{{ $developer->user->email ?? $developer->email }}</span>
                </div>
                <div>
                    <span class="text-xs text-gray-500 block">Role Akun:</span>
                    <span class="px-2 py-0.5 rounded text-xs font-bold bg-blue-100 text-blue-800 uppercase">
                        {{ $developer->user->role ?? 'operator' }}
                    </span>
                </div>
                <div>
                    <span class="text-xs text-gray-500 block">Status Akun:</span>
                    <span class="font-semibold {{ $developer->user?->approval_status === 'active' ? 'text-green-600' : 'text-amber-600' }}">
                        {{ strtoupper($developer->user?->approval_status ?? 'PENDING') }}
                    </span>
                </div>
                @if($developer->user)
                    <div class="pt-3 border-t border-gray-100">
                        <button type="button" onclick="openResetPasswordModal()" class="w-full py-2.5 px-3 bg-amber-50 hover:bg-amber-100 active:bg-amber-200 border border-amber-300 text-amber-800 font-bold rounded-xl text-xs flex items-center justify-center gap-1.5 transition shadow-sm">
                            <svg class="w-4 h-4 inline-block text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg> Reset Password Operator
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Perumahan Yang Dimiliki -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-base font-bold text-gray-800 mb-4 pb-2 border-b border-gray-100 flex items-center justify-between">
            <span class="flex items-center gap-2"><svg class="w-5 h-5 inline-block text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg> Daftar Perumahan Milik Pengembang</span>
            <span class="text-xs font-normal text-gray-500">{{ $developer->housingProjects->count() }} Perumahan</span>
        </h2>

        @if($developer->housingProjects->isNotEmpty())
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($developer->housingProjects as $proj)
                    <div class="border border-gray-200 rounded-xl p-4 hover:border-blue-300 transition">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-bold text-gray-900">{{ $proj->name }}</h3>
                            <span class="text-xs px-2 py-0.5 rounded {{ $proj->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                {{ $proj->is_active ? 'Aktif' : 'Non-aktif' }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 mb-2"><svg class="w-3.5 h-3.5 inline-block text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg> {{ $proj->location }}</p>
                        <p class="text-xs text-gray-700">Total Unit: <strong>{{ $proj->housingUnits->count() }} unit</strong></p>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 text-gray-400">
                <div class="w-8 h-8 mx-auto mb-1 flex items-center justify-center text-slate-300"><svg class="w-5 h-5 inline-block text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg></div>
                <p class="text-sm">Pengembang ini belum menginput perumahan.</p>
            </div>
        @endif
    </div>

    <!-- Action Buttons (Approve / Reject) -->
    @if($developer->status === 'pending')
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-2xl p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                <h3 class="font-bold text-gray-900 text-base">Keputusan Verifikasi Super Admin</h3>
                <p class="text-xs text-gray-600 mt-1">Setujui untuk mengaktifkan akun Operator dan memberikan akses kelola perumahan.</p>
            </div>
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <button type="button" onclick="openRejectModal()" class="flex-1 sm:flex-initial px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-xl text-sm transition shadow-sm">
                    <svg class="w-4 h-4 inline-block text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg> Tolak Pendaftaran
                </button>
                <form action="{{ route('admin.developers.approve', $developer->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menyetujui pengembang ini? Akun Operator akan langsung aktif.')">
                    @csrf
                    <button type="submit" class="flex-1 sm:flex-initial px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl text-sm transition shadow-md hover:shadow-lg">
                        <svg class="w-4 h-4 inline-block text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Setujui Pengembang
                    </button>
                </form>
            </div>
        </div>
    @elseif($developer->status === 'rejected')
        <div class="bg-gray-50 border border-gray-200 rounded-2xl p-6 flex items-center justify-between">
            <p class="text-sm text-gray-600">Pendaftaran ini sebelumnya telah ditolak. Anda dapat menyetujuinya kembali jika data telah diperbaiki.</p>
            <form action="{{ route('admin.developers.approve', $developer->id) }}" method="POST" onsubmit="return confirm('Buka kembali dan setujui pengembang ini?')">
                @csrf
                <button type="submit" class="px-5 py-2 bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl text-sm transition">
                    Ubah Status: Setujui Pengembang
                </button>
            </form>
        </div>
    @endif
</div>

<!-- Modal Reject -->
<div id="rejectModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl">
        <h3 class="text-lg font-bold text-gray-900 mb-2">Tolak Pendaftaran Pengembang</h3>
        <p class="text-xs text-gray-600 mb-4">Berikan alasan penolakan agar pemohon dapat mengetahuinya saat mencoba login.</p>
        <form action="{{ route('admin.developers.reject', $developer->id) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-700 mb-1">Alasan Penolakan *</label>
                <textarea name="reason" rows="3" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none" required placeholder="Contoh: Dokumen NIB tidak valid atau data kantor belum lengkap..."></textarea>
            </div>
            <div class="flex items-center justify-end gap-2">
                <button type="button" onclick="closeRejectModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg text-xs">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg text-xs shadow">
                    Tolak Sekarang
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Reset Password Operator -->
@if($developer->user)
<div id="resetPasswordModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-sm"><svg class="w-4 h-4 inline-block text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg></span>
                <div>
                    <h3 class="text-base font-bold text-gray-900">Reset Password Operator</h3>
                    <p class="text-[11px] text-gray-500">{{ $developer->pic_name }} ({{ $developer->user->email }})</p>
                </div>
            </div>
            <button type="button" onclick="closeResetPasswordModal()" class="text-gray-400 hover:text-gray-600 text-lg font-bold">&times;</button>
        </div>

        <form action="{{ route('admin.developers.reset-password', $developer->id) }}" method="POST">
            @csrf

            <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-800">
                <p>Masukkan password baru untuk akun operator ini, atau klik <strong>Acak Password</strong> untuk membuat password otomatis.</p>
            </div>

            <div class="space-y-3 mb-5">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-semibold text-gray-700">Password Baru *</label>
                        <button type="button" onclick="generateRandomPassword()" class="text-[11px] text-blue-600 hover:text-blue-800 font-semibold flex items-center gap-1">
                            <svg class="w-4 h-4 inline-block text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4h16v16H4V4zm4 4h.01M16 16h.01M12 12h.01M8 16h.01M16 8h.01"/></svg> Acak Password
                        </button>
                    </div>
                    <div class="relative">
                        <input type="text" id="admin_new_password" name="password" class="w-full border border-gray-300 rounded-lg p-2.5 pr-14 text-sm font-mono focus:ring-2 focus:ring-amber-500 focus:outline-none" required placeholder="Minimal 6 karakter">
                        <button type="button" onclick="copyPasswordToClipboard()" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 text-xs px-2 py-1 rounded border bg-gray-50 font-sans" title="Salin password">
                            Salin
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Konfirmasi Password Baru *</label>
                    <input type="text" id="admin_new_password_confirm" name="password_confirmation" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm font-mono focus:ring-2 focus:ring-amber-500 focus:outline-none" required placeholder="Ulangi password baru">
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                <button type="button" onclick="closeResetPasswordModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg text-xs transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-lg text-xs shadow transition">
                    Simpan & Perbarui Password
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<script>
    function openRejectModal() {
        document.getElementById('rejectModal').classList.remove('hidden');
        document.getElementById('rejectModal').classList.add('flex');
    }
    function closeRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
        document.getElementById('rejectModal').classList.remove('flex');
    }

    function openResetPasswordModal() {
        const modal = document.getElementById('resetPasswordModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }
    function closeResetPasswordModal() {
        const modal = document.getElementById('resetPasswordModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }
    function generateRandomPassword() {
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
        let result = '';
        for (let i = 0; i < 8; i++) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('admin_new_password').value = result;
        document.getElementById('admin_new_password_confirm').value = result;
    }
    function copyPasswordToClipboard() {
        const input = document.getElementById('admin_new_password');
        if (input && input.value) {
            navigator.clipboard.writeText(input.value);
            alert('Password berhasil disalin: ' + input.value);
        }
    }
</script>
@endsection
