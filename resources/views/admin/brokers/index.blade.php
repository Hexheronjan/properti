@extends('layouts.admin')

@section('title', 'Manajemen Broker & Marketing - Admin')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:text-blue-800 text-sm font-semibold flex items-center gap-1">
            ← Kembali ke Dashboard Admin
        </a>
        <button onclick="document.getElementById('addBrokerModal').classList.remove('hidden')" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md transition flex items-center gap-1.5">
            <span>+</span> Tambah Broker / Sales Marketing
        </button>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm font-semibold flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm font-semibold flex items-center gap-2">
            <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
        <div class="mb-6 pb-4 border-b border-gray-100">
            <h1 class="text-xl font-bold text-gray-900">Master Data Broker & Tim Penjualan</h1>
            <p class="text-xs text-gray-500 mt-1">
                Kelola data broker dan unggah <strong>Master Tanda Tangan Transparan (Remove-BG)</strong> agar tanda tangan otomatis dibubuhkan pada dokumen Surat Pemesanan Rumah (SPR).
            </p>
        </div>

        @if($brokers->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-xs">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 font-bold uppercase text-[11px]">
                            <th class="px-4 py-3 text-left">Nama Broker / Sales</th>
                            @if(auth()->user()->isSuperAdmin())
                                <th class="px-4 py-3 text-left">Mitra Developer (PT)</th>
                            @else
                                <th class="px-4 py-3 text-left">Kerjasama Perusahaan Lain</th>
                            @endif
                            <th class="px-4 py-3 text-left">Kantor & Kontak</th>
                            <th class="px-4 py-3 text-left">Akun Login Portal</th>
                            <th class="px-4 py-3 text-center">Master TTD</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($brokers as $broker)
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="px-4 py-3">
                                    <div class="font-bold text-gray-900 text-sm">{{ $broker->name }}</div>
                                    @if($broker->description)
                                        <div class="text-[11px] text-gray-500 line-clamp-1">{{ $broker->description }}</div>
                                    @endif
                                </td>

                                @if(auth()->user()->isSuperAdmin())
                                    <td class="px-4 py-3">
                                        @php
                                            $allCompanies = $broker->user 
                                                ? $broker->user->brokers->map(fn($b) => $b->company?->company_name)->filter()->unique()->values() 
                                                : collect([$broker->company?->company_name]);
                                        @endphp
                                        @if($broker->company_id === null)
                                            <span class="px-2 py-0.5 bg-amber-50 text-amber-700 border border-amber-200 rounded-lg text-[10px] font-bold">Semua Developer (Mitra Umum)</span>
                                        @elseif($allCompanies->isNotEmpty())
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($allCompanies as $compName)
                                                    <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 font-bold rounded-lg text-[10px] inline-flex items-center gap-1 border border-indigo-200">
                                                        <svg class="w-3 h-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                                        {{ $compName }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-gray-400 font-bold text-xs">-</span>
                                        @endif
                                    </td>
                                @else
                                    <td class="px-4 py-3">
                                        @php
                                            $otherCompanies = $broker->user 
                                                ? $broker->user->brokers->where('company_id', '!=', $broker->company_id)->map(fn($b) => $b->company?->company_name)->filter()->unique()->values() 
                                                : collect();
                                        @endphp
                                        @if($otherCompanies->isNotEmpty())
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($otherCompanies as $compName)
                                                    <span class="px-2 py-0.5 bg-blue-50 text-blue-700 font-semibold rounded-lg text-[10px] inline-flex items-center gap-1 border border-blue-200">
                                                        <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                                        {{ $compName }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-gray-400 font-bold text-xs">-</span>
                                        @endif
                                    </td>
                                @endif

                                <td class="px-4 py-3">
                                    <div class="font-semibold text-gray-800">{{ $broker->office_name ?: '-' }}</div>
                                    <div class="text-[11px] text-gray-500">{{ $broker->contact ?: '-' }}</div>
                                </td>

                                <td class="px-4 py-3">
                                    @if($broker->user)
                                        <div class="font-semibold text-blue-700">{{ $broker->user->email }}</div>
                                        <span class="text-[10px] text-emerald-600 font-bold inline-flex items-center gap-1">
                                            <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            <span>Bisa Login Portal</span>
                                        </span>
                                    @else
                                        <span class="text-[10px] text-gray-400">Belum ada akun login</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-center">
                                    @if($broker->signature_image)
                                        <div class="inline-block p-1.5 border border-dashed border-gray-300 rounded-lg bg-gray-50">
                                            <img src="{{ asset('storage/' . $broker->signature_image) }}" alt="TTD {{ $broker->name }}" class="h-10 max-w-[90px] object-contain">
                                        </div>
                                    @else
                                        <span class="px-2 py-0.5 bg-amber-50 text-amber-700 border border-amber-200 rounded text-[10px] font-semibold">
                                            Belum Upload
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <span class="px-2.5 py-1 rounded-full font-bold text-[10px] {{ $broker->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $broker->is_active ? 'AKTIF' : 'NON-AKTIF' }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button onclick="editBroker({{ json_encode($broker->load('user')) }})" class="px-2.5 py-1 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg text-xs font-semibold transition">
                                            Edit
                                        </button>
                                        <form action="{{ route('admin.brokers.destroy', $broker->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus broker dan akun loginnya?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1 bg-rose-50 text-rose-700 hover:bg-rose-100 rounded-lg text-xs font-semibold transition">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($brokers->hasPages())
                <div class="mt-4">
                    {{ $brokers->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-12 text-gray-500">
                <div class="w-12 h-12 mx-auto mb-3 bg-gray-100 text-gray-400 rounded-2xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <p class="text-sm font-semibold text-gray-700">Belum ada broker atau tim marketing yang terdaftar.</p>
                <p class="text-xs text-gray-400 mt-1">Klik tombol "+ Tambah Broker" untuk mendaftarkan broker dan mengunggah master tanda tangannya.</p>
            </div>
        @endif
    </div>
</div>

<!-- Modal Tambah Broker -->
<div id="addBrokerModal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-gray-100 max-h-[90vh] overflow-y-auto">
        <h3 class="text-base font-bold text-gray-900 mb-1 flex items-center gap-2">
            <span>+</span> Tambah Broker / Tim Marketing
        </h3>
        <p class="text-xs text-gray-500 mb-4 pb-2 border-b border-gray-100">
            Unggah tanda tangan berlatar transparan (remove-bg) agar otomatis tertempel pada dokumen SPR.
        </p>

        <form action="{{ route('admin.brokers.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3 text-xs">
            @csrf

            @if(auth()->user()->isSuperAdmin())
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Perusahaan Developer Rekanan</label>
                    <select name="company_id" class="w-full border border-gray-300 rounded-xl p-2.5 text-xs font-semibold focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">-- Semua Developer (Mitra Umum Platform) --</option>
                        @foreach($companies as $comp)
                            <option value="{{ $comp->id }}">{{ $comp->company_name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div>
                <label class="block font-bold text-gray-700 mb-1">Nama Lengkap Broker / Agen <span class="text-red-500">*</span></label>
                <input type="text" name="name" required placeholder="Contoh: Hendra Kurniawan, S.E." class="w-full border border-gray-300 rounded-xl p-2.5 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <!-- Akun Login Portal Broker -->
            <div class="p-3 bg-blue-50/70 border border-blue-200 rounded-xl space-y-2.5">
                <div class="font-bold text-blue-900 text-xs flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    <span>Akun Login Portal Broker</span>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Email Login <span class="text-red-500">*</span></label>
                        <input type="email" name="email" required placeholder="agen@broker.com" class="w-full border border-gray-300 rounded-xl p-2.5 text-xs bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Password Awal <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="password" name="password" id="add_broker_password" required placeholder="Minimal 6 karakter" class="w-full border border-gray-300 rounded-xl p-2.5 pr-9 text-xs bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <button type="button" onclick="togglePasswordVisibility('add_broker_password', 'add_eye_icon')" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-gray-400 hover:text-gray-700 transition focus:outline-none" title="Lihat / Sembunyikan Password">
                                <span id="add_eye_icon" class="flex items-center">
                                    <svg class="w-4 h-4 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
                <p class="text-[10px] text-blue-800">Broker dapat login ke portal khusus untuk melihat stok unit pengembang Anda dan memantau status calon pembelinya.</p>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Kantor / Agensi</label>
                    <input type="text" name="office_name" placeholder="Contoh: Era Prima Properti" class="w-full border border-gray-300 rounded-xl p-2.5 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-gray-700 mb-1">No. WhatsApp / Kontak</label>
                    <input type="text" name="contact" placeholder="081234567890" class="w-full border border-gray-300 rounded-xl p-2.5 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block font-bold text-gray-700 mb-1">Alamat Kantor / Domisili</label>
                <input type="text" name="address" placeholder="Alamat kantor broker" class="w-full border border-gray-300 rounded-xl p-2.5 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="block font-bold text-gray-700 mb-1">Unggah Master TTD Broker (PNG Transparan / Remove-BG)</label>
                <input type="file" name="signature_image" accept="image/png,image/webp" class="w-full border border-gray-300 rounded-xl p-2 text-xs bg-white focus:outline-none">
                <span class="text-[10px] text-gray-500 block mt-0.5">Tanda tangan berlatar transparan akan dicetak otomatis di kolom kiri SPR.</span>
            </div>

            <div class="pt-2">
                <label class="flex items-center gap-2 cursor-pointer font-semibold text-gray-800">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded text-blue-600 w-4 h-4">
                    <span>Status Broker Aktif</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-100">
                <button type="button" onclick="document.getElementById('addBrokerModal').classList.add('hidden')" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-semibold transition">
                    Batal
                </button>
                <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-md transition">
                    Simpan Broker
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Broker -->
<div id="editBrokerModal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-gray-100 max-h-[90vh] overflow-y-auto">
        <h3 class="text-base font-bold text-gray-900 mb-1 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            <span>Edit Broker / Sales Marketing</span>
        </h3>
        <p class="text-xs text-gray-500 mb-4 pb-2 border-b border-gray-100">
            Perbarui data atau unggah ulang tanda tangan transparan broker.
        </p>

        <form id="editBrokerForm" method="POST" enctype="multipart/form-data" class="space-y-3 text-xs">
            @csrf
            @method('PUT')
            <div>
                <label class="block font-bold text-gray-700 mb-1">Nama Lengkap Broker / Agen <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="edit_broker_name" required class="w-full border border-gray-300 rounded-xl p-2.5 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <!-- Akun Login Portal Broker (Edit) -->
            <div class="p-3 bg-blue-50/70 border border-blue-200 rounded-xl space-y-2.5">
                <div class="font-bold text-blue-900 text-xs flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    <span>Pengaturan Akun Login Portal Broker</span>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Email Login</label>
                        <input type="email" name="email" id="edit_broker_email" placeholder="email@broker.com" class="w-full border border-gray-300 rounded-xl p-2.5 text-xs bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Password Baru (Opsional)</label>
                        <div class="relative">
                            <input type="password" name="password" id="edit_broker_password" placeholder="Kosongkan jika tidak diganti" class="w-full border border-gray-300 rounded-xl p-2.5 pr-9 text-xs bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <button type="button" onclick="togglePasswordVisibility('edit_broker_password', 'edit_eye_icon')" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-gray-400 hover:text-gray-700 transition focus:outline-none" title="Lihat / Sembunyikan Password">
                                <span id="edit_eye_icon" class="flex items-center">
                                    <svg class="w-4 h-4 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Kantor / Agensi</label>
                    <input type="text" name="office_name" id="edit_broker_office_name" class="w-full border border-gray-300 rounded-xl p-2.5 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-gray-700 mb-1">No. WhatsApp / Kontak</label>
                    <input type="text" name="contact" id="edit_broker_contact" class="w-full border border-gray-300 rounded-xl p-2.5 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block font-bold text-gray-700 mb-1">Alamat Kantor / Domisili</label>
                <input type="text" name="address" id="edit_broker_address" class="w-full border border-gray-300 rounded-xl p-2.5 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="block font-bold text-gray-700 mb-1">Ganti Master TTD (PNG Transparan / Remove-BG)</label>
                <input type="file" name="signature_image" accept="image/png,image/webp" class="w-full border border-gray-300 rounded-xl p-2 text-xs bg-white focus:outline-none">
                <span class="text-[10px] text-gray-400 block mt-0.5">Kosongkan jika tidak ingin mengubah tanda tangan yang sudah ada.</span>
            </div>

            <div class="pt-2">
                <label class="flex items-center gap-2 cursor-pointer font-semibold text-gray-800">
                    <input type="checkbox" name="is_active" id="edit_broker_is_active" value="1" class="rounded text-blue-600 w-4 h-4">
                    <span>Status Broker Aktif</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-100">
                <button type="button" onclick="document.getElementById('editBrokerModal').classList.add('hidden')" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-semibold transition">
                    Batal
                </button>
                <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-md transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function editBroker(broker) {
    const form = document.getElementById('editBrokerForm');
    form.action = '/admin/brokers/' + broker.id;

    document.getElementById('edit_broker_name').value = broker.name || '';
    document.getElementById('edit_broker_email').value = broker.user ? broker.user.email : '';
    document.getElementById('edit_broker_office_name').value = broker.office_name || '';
    document.getElementById('edit_broker_contact').value = broker.contact || '';
    document.getElementById('edit_broker_address').value = broker.address || '';
    document.getElementById('edit_broker_is_active').checked = Boolean(broker.is_active);

    document.getElementById('editBrokerModal').classList.remove('hidden');
}

const eyeOpenSvg = `<svg class="w-4 h-4 text-gray-500 hover:text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>`;

const eyeClosedSvg = `<svg class="w-4 h-4 text-blue-600 hover:text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>`;

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
