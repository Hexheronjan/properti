@extends('layouts.admin')

@section('title', 'Rekening Bank Pengembang')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                <span>Rekening Bank Pengembang (Developer)</span>
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                Kelola nomor rekening resmi perusahaan pengembang untuk tujuan pembayaran transfer pembeli (UTK, DP, dan Termin).
            </p>
        </div>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold shadow-sm transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            <span>Tambah Rekening Bank</span>
        </button>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-sm font-semibold flex items-center justify-between">
            <span class="inline-flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('success') }}</span>
            </span>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 font-bold">&times;</button>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-sm">
            <p class="font-bold mb-1 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Terjadi kesalahan input:</span>
            </p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Filter Perusahaan untuk Super Admin --}}
    @if($isSuperAdmin && $companies->count() > 1)
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-200 mb-6 flex items-center gap-3">
            <span class="text-xs font-bold text-gray-600 uppercase">Filter Pengembang:</span>
            <form method="GET" action="{{ route('admin.bank-accounts.index') }}" class="flex items-center gap-2">
                <select name="company_id" onchange="this.form.submit()" class="border border-gray-300 rounded-lg p-2 text-xs font-semibold bg-gray-50 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="">Semua Perusahaan Pengembang ({{ $companies->count() }})</option>
                    @foreach($companies as $comp)
                        <option value="{{ $comp->id }}" {{ request('company_id') == $comp->id ? 'selected' : '' }}>
                            {{ $comp->company_name }}
                        </option>
                    @endforeach
                </select>
                @if(request('company_id'))
                    <a href="{{ route('admin.bank-accounts.index') }}" class="text-xs text-rose-600 hover:underline">Reset</a>
                @endif
            </form>
        </div>
    @endif

    {{-- Daftar Rekening Bank --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 text-xs uppercase font-bold">
                    <tr>
                        <th class="px-5 py-4">Bank</th>
                        <th class="px-5 py-4">Nomor Rekening</th>
                        <th class="px-5 py-4">Atas Nama (A.N.)</th>
                        @if($isSuperAdmin)
                            <th class="px-5 py-4">Perusahaan Pengembang</th>
                        @endif
                        <th class="px-5 py-4">Cabang</th>
                        <th class="px-5 py-4 text-center">Status</th>
                        <th class="px-5 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($bankAccounts as $acc)
                        <tr class="hover:bg-gray-50/70 transition">
                            <td class="px-5 py-4 font-bold text-gray-900 flex items-center gap-2">
                                <span class="px-2.5 py-1 bg-blue-100 text-blue-800 rounded font-black text-xs uppercase tracking-wider">
                                    {{ $acc->bank_name }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="font-mono font-bold text-base text-gray-800 tracking-wide">{{ $acc->account_number }}</span>
                            </td>
                            <td class="px-5 py-4 font-semibold text-gray-800">
                                {{ $acc->account_holder_name }}
                            </td>
                            @if($isSuperAdmin)
                                <td class="px-5 py-4 text-xs font-medium text-gray-600">
                                    {{ $acc->developerCompany->company_name ?? '-' }}
                                </td>
                            @endif
                            <td class="px-5 py-4 text-xs text-gray-500">
                                {{ $acc->branch ?? '-' }}
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if($acc->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-600">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button onclick="openEditModal({{ json_encode($acc) }})"
                                            class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-bold transition">
                                        Edit
                                    </button>
                                    <form action="{{ route('admin.bank-accounts.destroy', $acc->id) }}" method="POST" onsubmit="return confirm('Hapus rekening bank ini?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg text-xs font-bold transition">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isSuperAdmin ? 7 : 6 }}" class="text-center py-12 text-gray-400">
                                <div class="w-12 h-12 mx-auto mb-2 bg-gray-100 rounded-xl flex items-center justify-center text-gray-400">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                </div>
                                <p class="text-sm font-semibold">Belum ada rekening bank yang didaftarkan.</p>
                                <p class="text-xs text-gray-400 mt-0.5">Klik tombol "Tambah Rekening Bank" di kanan atas untuk menambahkan rekening resmi perusahaan Anda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bankAccounts->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $bankAccounts->links() }}
            </div>
        @endif
    </div>
</div>

{{-- MODAL TAMBAH REKENING --}}
<div id="addModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden backdrop-blur-sm">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl animate-in fade-in zoom-in-95">
        <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-5">
            <h3 class="font-bold text-lg text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Rekening Bank</span>
            </h3>
            <button onclick="document.getElementById('addModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
        </div>

        <form action="{{ route('admin.bank-accounts.store') }}" method="POST" class="space-y-4">
            @csrf

            @if($isSuperAdmin)
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Perusahaan Pengembang <span class="text-rose-500">*</span></label>
                    <select name="developer_company_id" required class="w-full border border-gray-300 rounded-xl p-2.5 text-sm bg-white focus:ring-2 focus:ring-blue-500 font-semibold">
                        <option value="">-- Pilih Perusahaan Pengembang --</option>
                        @foreach($companies as $comp)
                            <option value="{{ $comp->id }}">{{ $comp->company_name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nama Bank <span class="text-rose-500">*</span></label>
                <input list="bankList" name="bank_name" placeholder="Contoh: BCA / MANDIRI / BRI / BNI / BSI" required
                       class="w-full border border-gray-300 rounded-xl p-2.5 text-sm uppercase font-semibold focus:ring-2 focus:ring-blue-500">
                <datalist id="bankList">
                    <option value="BCA">Bank Central Asia (BCA)</option>
                    <option value="MANDIRI">Bank Mandiri</option>
                    <option value="BRI">Bank Rakyat Indonesia (BRI)</option>
                    <option value="BNI">Bank Negara Indonesia (BNI)</option>
                    <option value="BSI">Bank Syariah Indonesia (BSI)</option>
                    <option value="CIMB NIAGA">CIMB Niaga</option>
                    <option value="PERMATA">Bank Permata</option>
                    <option value="BTN">Bank Tabungan Negara (BTN)</option>
                    <option value="BANK JATIM">Bank Jatim</option>
                </datalist>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nomor Rekening <span class="text-rose-500">*</span></label>
                <input type="text" name="account_number" placeholder="Contoh: 1234567890" required
                       class="w-full border border-gray-300 rounded-xl p-2.5 text-sm font-mono font-bold focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Atas Nama (A.N. Rekening) <span class="text-rose-500">*</span></label>
                <input type="text" name="account_holder_name" placeholder="Contoh: PT PRAJA PROPERTINDO SEJAHTERA" required
                       class="w-full border border-gray-300 rounded-xl p-2.5 text-sm font-semibold focus:ring-2 focus:ring-blue-500">
                <span class="text-[11px] text-gray-500 mt-0.5 block">Wajib atas nama badan hukum PT / rekening resmi pengembang.</span>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Kantor Cabang (Opsional)</label>
                <input type="text" name="branch" placeholder="Contoh: KC Yogyakarta Sudirman"
                       class="w-full border border-gray-300 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100">
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')"
                        class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl text-sm font-bold hover:bg-gray-200 transition">
                    Batal
                </button>
                <button type="submit"
                        class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold shadow-md transition">
                    Simpan Rekening
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT REKENING --}}
<div id="editModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden backdrop-blur-sm">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl animate-in fade-in zoom-in-95">
        <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-5">
            <h3 class="font-bold text-lg text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Edit Rekening Bank</span>
            </h3>
            <button onclick="document.getElementById('editModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
        </div>

        <form id="editForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nama Bank <span class="text-rose-500">*</span></label>
                <input type="text" id="edit_bank_name" name="bank_name" required
                       class="w-full border border-gray-300 rounded-xl p-2.5 text-sm uppercase font-semibold focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nomor Rekening <span class="text-rose-500">*</span></label>
                <input type="text" id="edit_account_number" name="account_number" required
                       class="w-full border border-gray-300 rounded-xl p-2.5 text-sm font-mono font-bold focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Atas Nama (A.N. Rekening) <span class="text-rose-500">*</span></label>
                <input type="text" id="edit_account_holder_name" name="account_holder_name" required
                       class="w-full border border-gray-300 rounded-xl p-2.5 text-sm font-semibold focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Kantor Cabang</label>
                <input type="text" id="edit_branch" name="branch"
                       class="w-full border border-gray-300 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="flex items-center gap-2 cursor-pointer mt-2">
                    <input type="checkbox" id="edit_is_active" name="is_active" value="1" class="rounded text-blue-600 focus:ring-blue-500 w-4 h-4">
                    <span class="text-sm font-semibold text-gray-700">Aktifkan rekening ini untuk menerima pembayaran</span>
                </label>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100">
                <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')"
                        class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl text-sm font-bold hover:bg-gray-200 transition">
                    Batal
                </button>
                <button type="submit"
                        class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold shadow-md transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openEditModal(acc) {
        document.getElementById('editForm').action = '/admin/bank-accounts/' + acc.id;
        document.getElementById('edit_bank_name').value = acc.bank_name;
        document.getElementById('edit_account_number').value = acc.account_number;
        document.getElementById('edit_account_holder_name').value = acc.account_holder_name;
        document.getElementById('edit_branch').value = acc.branch || '';
        document.getElementById('edit_is_active').checked = !!acc.is_active;
        document.getElementById('editModal').classList.remove('hidden');
    }
</script>
@endpush
@endsection
