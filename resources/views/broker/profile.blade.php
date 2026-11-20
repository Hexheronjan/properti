@extends('layouts.broker')

@section('title', 'Profil & Tanda Tangan Broker')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900 flex items-center gap-2">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span>Profil & Tanda Tangan Digital</span>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Kelola kontak, informasi agensi, dan master tanda tangan digital Anda untuk dokumen SPR.
            </p>
        </div>

        <a href="{{ route('broker.dashboard') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 inline-flex items-center gap-1">
            ← Kembali ke Dashboard
        </a>
    </div>

    @if(isset($errors) && $errors->any())
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs font-semibold flex items-center gap-2">
            <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
        <form action="{{ route('broker.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
            @csrf

            <!-- Info Akun Readonly -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pb-3 border-b border-slate-100">
                <div>
                    <label class="block font-bold text-slate-500 mb-1">Nama Broker / Sales</label>
                    <input type="text" value="{{ $broker?->name ?? $user->name }}" readonly class="w-full border border-slate-200 bg-slate-100 text-slate-700 font-bold rounded-xl p-2.5 text-xs focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-500 mb-1">Email Login</label>
                    <input type="email" value="{{ $user->email }}" readonly class="w-full border border-slate-200 bg-slate-100 text-slate-700 font-bold rounded-xl p-2.5 text-xs focus:outline-none">
                </div>
            </div>

            <!-- Developer Rekanan -->
            <div>
                <label class="block font-bold text-slate-500 mb-1">Perusahaan Developer Rekanan</label>
                @if(isset($partnerships) && $partnerships->count() > 1)
                    <div class="flex flex-wrap gap-2">
                        @foreach($partnerships as $p)
                            @if($p->company)
                                <div class="p-2.5 bg-amber-50 border border-amber-200 rounded-xl font-bold text-amber-900 flex items-center gap-2 text-xs">
                                    <svg class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    <span>{{ $p->company->company_name }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="p-2.5 bg-amber-50 border border-amber-200 rounded-xl font-bold text-amber-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span>{{ $broker?->company?->company_name ?? 'Developer' }}</span>
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Kantor / Agensi Pemasar</label>
                    <input type="text" name="office_name" value="{{ old('office_name', $broker?->office_name) }}" placeholder="Contoh: Era Prima Properti" class="w-full border border-slate-300 rounded-xl p-2.5 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">No. WhatsApp / Kontak <span class="text-rose-500">*</span></label>
                    <input type="text" name="contact" value="{{ old('contact', $broker?->contact ?? $user->phone) }}" required placeholder="081234567890" class="w-full border border-slate-300 rounded-xl p-2.5 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Alamat Domisili / Kantor</label>
                <input type="text" name="address" value="{{ old('address', $broker?->address) }}" placeholder="Alamat kantor / tempat tinggal" class="w-full border border-slate-300 rounded-xl p-2.5 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
            </div>

            <!-- Master TTD -->
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-3">
                <div>
                    <label class="block font-bold text-slate-800 mb-0.5">Master Tanda Tangan Digital (PNG Transparan / Remove-BG)</label>
                    <p class="text-[11px] text-slate-500">Tanda tangan ini akan otomatis tertera di dokumen Surat Pemesanan Rumah (SPR) yang diterbitkan.</p>
                </div>

                @if(!empty($broker?->signature_image))
                    <div class="flex items-center gap-4 p-3 bg-white border border-slate-200 rounded-xl">
                        <div class="p-1 border border-dashed border-slate-300 rounded-lg bg-slate-50">
                            <img src="{{ asset('storage/' . $broker->signature_image) }}" alt="Tanda Tangan" class="h-14 max-w-[120px] object-contain">
                        </div>
                        <div>
                            <span class="text-xs font-bold text-emerald-700 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span>Tanda Tangan Terpasang</span>
                            </span>
                            <span class="text-[11px] text-slate-400 block mt-0.5">Unggah file baru di bawah jika ingin mengganti.</span>
                        </div>
                    </div>
                @else
                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-xs font-semibold flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Anda belum mengunggah master tanda tangan. Dokumen SPR tidak akan menampilkan tanda tangan digital Anda sampai file diunggah.</span>
                    </div>
                @endif

                <input type="file" name="signature_image" accept="image/png,image/webp" class="w-full border border-slate-300 rounded-xl p-2 text-xs bg-white focus:outline-none">
            </div>

            <!-- Ganti Password -->
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-3">
                <div>
                    <h3 class="font-bold text-slate-800 text-xs">Ganti Password Login</h3>
                    <p class="text-[11px] text-slate-500">Kosongkan jika tidak ingin mengubah password akun Anda.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Password Baru</label>
                        <div class="relative">
                            <input type="password" name="password" id="prof_password" placeholder="Minimal 6 karakter" class="w-full border border-slate-300 rounded-xl p-2.5 pr-9 text-xs bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            <button type="button" onclick="togglePasswordVisibility('prof_password', 'prof_eye_1')" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-700 transition focus:outline-none">
                                <span id="prof_eye_1" class="flex items-center">
                                    <svg class="w-4 h-4 text-slate-400 hover:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Ulangi Password Baru</label>
                        <div class="relative">
                            <input type="password" name="password_confirmation" id="prof_password_confirm" placeholder="Konfirmasi password baru" class="w-full border border-slate-300 rounded-xl p-2.5 pr-9 text-xs bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            <button type="button" onclick="togglePasswordVisibility('prof_password_confirm', 'prof_eye_2')" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-700 transition focus:outline-none">
                                <span id="prof_eye_2" class="flex items-center">
                                    <svg class="w-4 h-4 text-slate-400 hover:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3">
                <button type="submit" class="px-6 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-bold text-xs shadow-md transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

</div>

<script>
const eyeOpenSvg = `<svg class="w-4 h-4 text-slate-400 hover:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>`;

const eyeClosedSvg = `<svg class="w-4 h-4 text-amber-600 hover:text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>`;

function togglePasswordVisibility(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (!input || !icon) return;
    if (input.type === 'password') {
        input.type = 'text';
        icon.innerHTML = eyeClosedSvg;
    } else {
        input.type = 'password';
        icon.innerHTML = eyeOpenSvg;
    }
}
</script>
@endsection
