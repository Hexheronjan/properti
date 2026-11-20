@extends('layouts.admin')

@section('title', 'Terbitkan Surat Pemesanan Rumah (SPR)')

@section('content')
<div class="max-w-5xl mx-auto py-6 px-4">
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('admin.spr.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-blue-600 hover:text-blue-800 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            Kembali ke Manajemen SPR
        </a>
        <span class="text-xs text-gray-500 font-mono">Unit: {{ $unitSelection->housingUnit?->full_identifier }}</span>
    </div>

    <!-- Alert Notifikasi -->
    @if(session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl text-sm mb-6 flex items-center gap-2">
            <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl text-sm mb-6">
            <p class="font-bold mb-1">Periksa kembali data formulir:</p>
            <ul class="list-disc pl-5 space-y-0.5 text-xs">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Card 1: Ringkasan Pemesan & Unit Kavling -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-start justify-between pb-4 border-b border-gray-100">
            <div>
                <span class="px-2.5 py-0.5 bg-blue-100 text-blue-800 rounded-full text-[11px] font-extrabold uppercase tracking-wide">
                    Penerbitan SPR Resmi (Operator)
                </span>
                <h1 class="text-xl font-bold text-gray-900 mt-2">Formulir Penerbitan Dokumen SPR</h1>
                <p class="text-xs text-gray-500 mt-0.5">Tentukan skema pembayaran, uang muka (DP), dan jadwal cicilan untuk pemohon.</p>
            </div>
            <div class="text-right">
                <span class="text-xs text-gray-400 block">Harga Unit Rumah:</span>
                <span class="text-xl font-black text-gray-900">Rp {{ number_format($unitPrice, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mt-4 text-xs text-gray-600 bg-gray-50 p-4 rounded-xl">
            <div>
                <span class="block text-gray-400">Nama Pemesan</span>
                <span class="font-bold text-sm text-gray-900">{{ $unitSelection->nupRegistration?->full_name ?: $unitSelection->user->name }}</span>
                <span class="block text-[11px] text-gray-500 font-mono mt-0.5">{{ $unitSelection->user->phone ?? '-' }}</span>
            </div>
            <div>
                <span class="block text-gray-400">Unit Kavling</span>
                <span class="font-bold text-sm text-gray-900">{{ $unitSelection->housingUnit?->full_identifier }}</span>
                <span class="block text-[11px] text-gray-500">Tipe {{ $unitSelection->housingUnit?->type ?? '-' }}</span>
            </div>
            <div>
                <span class="block text-gray-400">Nomor Antrean NUP</span>
                <span class="font-bold text-sm text-blue-700 font-mono">{{ $unitSelection->nupRegistration?->queue_number ?: 'NUP-'.$unitSelection->nup_registration_id }}</span>
            </div>
            <div>
                <span class="block text-gray-400">Status UTK</span>
                @if($unitSelection->isCash())
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">Bebas UTK (Tunai)</span>
                @elseif($unitSelection->isUtkPaid())
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">Lunas (Rp {{ number_format($unitSelection->utk_amount, 0, ',', '.') }})</span>
                @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">Menunggu Pelunasan</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Card 2: Formulir Pengaturan Dokumen SPR -->
    <form action="{{ route('admin.spr.store', $unitSelection->id) }}" method="POST" id="sprForm">
        @csrf

        @php
            $schemeLabels = [
                'inhouse' => 'In-House Developer (Cicilan Bertahap ke Pengembang)',
                'cash' => 'Tunai Keras (Lunas)',
                'tunai_bertahap' => 'Tunai Bertahap (Tempo 30-90 Hari)',
                'kpr' => 'KPR Bank Rekanan / FLPP (Bebas DP)',
                'mitra_pintar' => 'Kredit Mitra Pintar (Kerja Sama Perusahaan)',
            ];
            $currentScheme = old('payment_scheme', $scheme);
            if (in_array($currentScheme, ['kpr', 'kpr_bank', 'kpr_bank_flpp', 'kpr_syariah'], true)) {
                $currentScheme = 'kpr';
            } elseif (in_array($currentScheme, ['tunai', 'tunai_keras'], true)) {
                $currentScheme = 'cash';
            } elseif (in_array($currentScheme, ['installment'], true)) {
                $currentScheme = 'inhouse';
            }
        @endphp

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-sm font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Rincian Skema & Pembayaran SPR</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Nomor SPR Resmi</label>
                    <input type="text" name="spr_number" value="{{ old('spr_number', $suggestedSprNumber) }}" required class="w-full border border-gray-300 rounded-xl p-2.5 text-xs font-mono font-bold text-gray-900 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <span class="text-[11px] text-gray-400 mt-1 block">Format otomatis sesuai inisial perusahaan pengembang</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal Dokumen SPR</label>
                    <input type="date" name="spr_date" value="{{ old('spr_date', now()->toDateString()) }}" required class="w-full border border-gray-300 rounded-xl p-2.5 text-xs font-semibold text-gray-800 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>

            <!-- Skema Pembayaran SPR Sesuai Pilihan Konsumen (Terkunci Sesuai Pengajuan Awal) -->
            <input type="hidden" name="payment_scheme" value="{{ $currentScheme }}">
            <div class="mb-5">
                <label class="block text-xs font-bold text-gray-700 mb-2">Skema Pembayaran SPR</label>

                <!-- Box Tampilan Skema Default Terkunci -->
                <div id="locked_scheme_badge" class="bg-blue-50/70 border border-blue-200 rounded-xl p-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-gray-500">Pilihan Konsumen:</span>
                                <span id="current_scheme_badge_text" class="px-2.5 py-0.5 rounded-md text-xs font-black bg-blue-100 text-blue-900 border border-blue-200">
                                    {{ $schemeLabels[$currentScheme] ?? 'In-House Developer' }}
                                </span>
                            </div>
                            <p class="text-[11px] text-gray-500 mt-0.5" id="current_scheme_desc_text">
                                Nilai DP, pokok tagihan, dan ketentuan otomatis terkunci sesuai skema yang dipilih oleh pemohon di awal.
                            </p>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-200 self-start sm:self-auto">
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Sesuai Pengajuan Awal (Terkunci)
                    </span>
                </div>
            </div>

            <!-- Detail Perhitungan Pembiayaan -->
            @if($currentScheme === 'kpr' || $currentScheme === 'mitra_pintar')
                {{-- KHUSUS SKEMA KPR / MITRA PINTAR: BEBAS DP (100% PLAFON PEMBIAYAAN BANK/MITRA) --}}
                <input type="hidden" id="input_dp" name="down_payment" value="0">
                <input type="hidden" name="due_date" value="">
                <div class="p-5 rounded-2xl border border-blue-200 bg-blue-50/50 mb-5">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="px-2.5 py-1 bg-blue-600 text-white font-extrabold text-xs rounded-lg uppercase tracking-wider">
                            {{ $currentScheme === 'kpr' ? 'Skema KPR Bank (Bebas DP)' : 'Skema Bebas DP' }}
                        </span>
                        <span class="text-xs text-blue-900 font-semibold">Plafon Pembiayaan Diajukan 100% ke {{ $currentScheme === 'kpr' ? 'Bank Rekanan' : 'Lembaga Mitra' }}</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="bg-white p-3.5 rounded-xl border border-blue-100 shadow-2xs">
                            <span class="block text-[11px] font-bold text-slate-500 uppercase">Kewajiban Uang Muka (DP)</span>
                            <span class="text-lg font-black text-emerald-600 block mt-0.5">Rp 0</span>
                            <span class="text-[11px] text-emerald-700 font-semibold block mt-0.5">✓ Bebas DP (Tanpa Uang Muka)</span>
                        </div>

                        <div class="bg-white p-3.5 rounded-xl border border-blue-100 shadow-2xs">
                            <span class="block text-[11px] font-bold text-slate-500 uppercase">Plafon Pengajuan {{ $currentScheme === 'kpr' ? 'KPR Bank' : 'Mitra' }}</span>
                            <span class="text-lg font-black text-slate-900 block mt-0.5">Rp {{ number_format($unitPrice, 0, ',', '.') }}</span>
                            <span class="text-[11px] text-slate-500 block mt-0.5">100% Total Harga Unit Rumah</span>
                        </div>

                        <div class="bg-white p-3.5 rounded-xl border border-blue-100 shadow-2xs">
                            <span class="block text-[11px] font-bold text-slate-500 uppercase">Tahapan Selanjutnya</span>
                            <span class="text-xs font-bold text-blue-900 block mt-1">Pengurusan Bank & SP3K</span>
                            <span class="text-[11px] text-slate-500 block mt-0.5">Argo waktu pengurusan bank otomatis aktif</span>
                        </div>
                    </div>
                </div>
            @else
                {{-- SKEMA IN-HOUSE / TUNAI BERTAHAP / TUNAI KERAS --}}
                <div class="p-4 rounded-xl border border-blue-100 bg-blue-50/40 mb-5">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Down Payment / Uang Muka (Rp)</label>
                            <input type="number" id="input_dp" name="down_payment" value="{{ old('down_payment', '') }}" placeholder="Masukkan nominal DP (Rp)" min="0" max="{{ $unitPrice }}" step="500000" class="w-full border border-gray-300 rounded-xl p-2.5 text-sm font-bold text-gray-900 focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white" oninput="calculateFinancing()" required>
                            <div class="flex gap-1.5 mt-1.5">
                                <button type="button" onclick="setDpPercent(10)" class="px-2 py-0.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded text-[10px] font-bold">10%</button>
                                <button type="button" onclick="setDpPercent(20)" class="px-2 py-0.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded text-[10px] font-bold">20%</button>
                                <button type="button" onclick="setDpPercent(30)" class="px-2 py-0.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded text-[10px] font-bold">30%</button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Sisa Pokok Tagihan (Rp)</label>
                            <input type="text" id="display_remaining" readonly class="w-full border border-gray-200 rounded-xl p-2.5 text-sm font-bold text-gray-700 bg-gray-100 focus:outline-none">
                            <span class="text-[11px] text-gray-500 mt-1 block">Harga Unit dikurangi DP</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Batas Waktu Bayar DP (Jatuh Tempo DP)</label>
                            <input type="date" name="due_date" value="{{ old('due_date', now()->addDays(14)->toDateString()) }}" class="w-full border border-gray-300 rounded-xl p-2.5 text-xs font-semibold text-gray-800 focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white">
                            <span class="text-[11px] text-gray-400 mt-1 block">Default 14 hari sejak penerbitan SPR</span>
                        </div>
                    </div>

                    @if($currentScheme === 'inhouse' || $currentScheme === 'installment')
                        <!-- Bagian Khusus In-House (Tenor & Cicilan per Bulan) -->
                        <div id="inhouse_section" class="pt-4 border-t border-blue-200 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-indigo-950 mb-1">Tenor Cicilan (Jumlah Bulan)</label>
                                <div class="flex items-center gap-2">
                                    <input type="number" id="input_tenor" name="tenor_months" value="{{ old('tenor_months', '') }}" placeholder="Contoh: 12" min="1" max="60" class="w-32 border border-indigo-300 rounded-xl p-2 text-sm font-bold text-indigo-950 focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-white" oninput="calculateFinancing()">
                                    <span class="text-xs text-gray-600 font-semibold">Bulan (Maksimal 12-24 Bulan)</span>
                                </div>
                                <span class="text-[11px] text-indigo-700 mt-1 block">Sisa pokok akan dibagi rata selama tenor ini</span>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-indigo-950 mb-1">Estimasi Angsuran per Bulan</label>
                                <div class="p-2.5 bg-white border border-indigo-200 rounded-xl">
                                    <span id="display_installment" class="text-base font-black text-indigo-700 block">Rp 0 / bulan</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Rekening Tujuan Transfer Pengembang -->
            <div class="mb-5">
                <label class="block text-xs font-bold text-gray-700 mb-1">Rekening Bank Developer (Tujuan Pembayaran Pembeli)</label>
                <select name="developer_bank_account_id" class="w-full border border-gray-300 rounded-xl p-2.5 text-xs font-semibold text-gray-800 focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white">
                    @foreach($bankAccounts as $acc)
                        <option value="{{ $acc->id }}" {{ old('developer_bank_account_id') == $acc->id ? 'selected' : '' }}>
                            {{ $acc->bank_name }} - {{ $acc->account_number }} a.n. {{ $acc->account_holder }}
                        </option>
                    @endforeach
                </select>
                <span class="text-[11px] text-gray-400 mt-1 block">Rekening yang dicantumkan pada lembar tagihan resmi SPR</span>
            </div>

            <!-- Syarat & Ketentuan Tambahan (Opsional) -->
            <div class="mb-5">
                <label class="block text-xs font-bold text-gray-700 mb-1">Syarat & Ketentuan Tambahan / Catatan Khusus <span class="text-gray-400 font-normal">(Opsional)</span></label>
                <textarea name="terms_conditions" rows="3" class="w-full border border-gray-300 rounded-xl p-2.5 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Contoh: Tanggal jatuh tempo cicilan per bulan adalah tanggal 10. Keterlambatan dikenakan denda sesuai kesepakatan...">{{ old('terms_conditions') }}</textarea>
            </div>

            <!-- Status Dokumen -->
            <div class="pt-4 border-t border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <input type="radio" id="status_issued" name="status" value="issued" checked class="text-blue-600 focus:ring-blue-500">
                    <label for="status_issued" class="text-xs font-bold text-gray-800 cursor-pointer">
                        Terbitkan Resmi (Status: ISSUED) — <span class="text-gray-500 font-normal">Dokumen langsung sah & siap ditinjau/ditandatangani pembeli</span>
                    </label>
                </div>

                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <a href="{{ route('admin.spr.index') }}" class="px-5 py-2.5 border border-gray-300 rounded-xl text-xs font-bold text-gray-700 hover:bg-gray-50 transition">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md transition inline-flex items-center gap-2">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Terbitkan Dokumen SPR Sekarang</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    const unitPrice = {{ (float) $unitPrice }};

    function formatRupiah(num) {
        return 'Rp ' + Number(num).toLocaleString('id-ID');
    }

    function setDpPercent(percent) {
        const dp = Math.round(unitPrice * (percent / 100));
        document.getElementById('input_dp').value = dp;
        calculateFinancing();
    }

    function calculateFinancing() {
        const dpInput = document.getElementById('input_dp');
        const dpVal = dpInput.value.trim();
        let dp = 0;
        
        if (dpVal !== '') {
            dp = parseFloat(dpVal) || 0;
            if (dp > unitPrice) {
                dp = unitPrice;
                dpInput.value = dp;
            }
        }
        
        const remaining = Math.max(0, unitPrice - dp);
        document.getElementById('display_remaining').value = formatRupiah(remaining);

        const tenorInput = document.getElementById('input_tenor');
        const tenorVal = tenorInput ? tenorInput.value.trim() : '';

        if (tenorVal !== '' && parseInt(tenorVal) > 0) {
            const tenor = parseInt(tenorVal);
            const installment = Math.round(remaining / Math.max(1, tenor));
            document.getElementById('display_installment').textContent = formatRupiah(installment) + ' / bulan';
        } else {
            document.getElementById('display_installment').textContent = 'Masukkan tenor bulan';
        }
    }

    const activeScheme = document.querySelector('input[name="payment_scheme"]')?.value || '{{ $currentScheme }}';

    function initScheme(scheme) {
        const inhouseSection = document.getElementById('inhouse_section');
        const dpInput = document.getElementById('input_dp');

        if (scheme === 'kpr' || scheme === 'mitra_pintar') {
            if (inhouseSection) inhouseSection.classList.add('hidden');
            if (dpInput) {
                dpInput.value = 0;
                dpInput.readOnly = true;
            }
        } else if (scheme === 'cash') {
            if (inhouseSection) inhouseSection.classList.add('hidden');
            if (dpInput) {
                dpInput.readOnly = false;
                dpInput.value = unitPrice;
            }
        } else if (scheme === 'tunai_bertahap') {
            if (inhouseSection) inhouseSection.classList.add('hidden');
            if (dpInput) {
                dpInput.readOnly = false;
            }
        } else {
            // inhouse
            if (inhouseSection) inhouseSection.classList.remove('hidden');
            if (dpInput) {
                dpInput.readOnly = false;
            }
        }
        calculateFinancing();
    }

    document.addEventListener('DOMContentLoaded', () => {
        initScheme(activeScheme);
    });
</script>
@endsection
