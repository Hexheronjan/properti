@extends('layouts.app')

@section('title', 'Registrasi NUP')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200/80 mb-3 shadow-xs">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
            LAYANAN PEMESANAN &middot; REGISTRASI NUP
        </span>
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Registrasi NUP</h1>

        @if(!empty($hasUnpaidHouse))
            <div class="bg-amber-50 border-2 border-amber-300 rounded-xl p-4 mb-6 flex items-start gap-3">
                <svg class="w-6 h-6 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div>
                    <h3 class="font-bold text-amber-950 text-sm">Pemberitahuan: Anda Masih Memiliki Rumah yang Belum Lunas</h3>
                    <p class="text-xs text-amber-800 mt-0.5 leading-relaxed">
                        Anda dapat melihat dan mengisi formulir registrasi NUP ini. Namun pendaftaran unit rumah baru baru dapat diproses setelah pembayaran rumah sebelumnya dilunasi.
                    </p>
                </div>
            </div>
        @endif

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <h3 class="font-semibold text-blue-800 mb-2">Informasi NUP</h3>
            <p class="text-sm text-blue-700">
                Biaya Pendaftaran: <strong>Rp {{ number_format(config('nup.nup_fee'), 0, ',', '.') }}</strong>
            </p>
        </div>

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-red-800 mb-2">Error</h3>
                <ul class="text-sm text-red-700">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif



        <form action="{{ route('nup.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-1">Nama Lengkap Pemesan Unit</label>
                <span class="text-xs text-gray-400 block mb-2">Nama pemohon/pembeli yang tercantum di dokumen pemesanan unit ini (bisa berbeda dari nama akun login).</span>
                <input type="text" name="full_name" class="w-full border border-gray-300 rounded-lg p-2" required placeholder="Nama lengkap sesuai KTP" value="{{ old('full_name', Auth::user()->name ?? '') }}">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Alamat Sesuai KTP</label>
                <textarea name="address" class="w-full border border-gray-300 rounded-lg p-2" rows="3" required placeholder="Alamat lengkap sesuai KTP">{{ Auth::user()->address ?? '' }}</textarea>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Alamat Domisili (diisi jika tidak sesuai KTP)</label>
                <textarea name="domicile_address" class="w-full border border-gray-300 rounded-lg p-2" rows="3" placeholder="Alamat domisili saat ini"></textarea>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Nomor Telepon</label>
                <input type="text" name="phone" class="w-full border border-gray-300 rounded-lg p-2" required placeholder="08xxxxxxxxxx" value="{{ Auth::user()->phone ?? '' }}">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Pekerjaan</label>
                <select name="occupation" id="occupation" class="w-full border border-gray-300 rounded-lg p-2" required onchange="toggleOccupationFields()">
                    <option value="">Pilih Pekerjaan</option>
                    <option value="karyawan_tetap_asn" {{ old('occupation', Auth::user()->occupation ?? '') == 'karyawan_tetap_asn' ? 'selected' : '' }}>ASN / Karyawan Tetap</option>
                    <option value="karyawan_kontrak_pppk" {{ old('occupation', Auth::user()->occupation ?? '') == 'karyawan_kontrak_pppk' ? 'selected' : '' }}>Karyawan Kontrak / PPPK</option>
                    <option value="wiraswasta_umkm" {{ old('occupation', Auth::user()->occupation ?? '') == 'wiraswasta_umkm' ? 'selected' : '' }}>Wiraswasta / Pemilik Usaha / UMKM</option>
                    <option value="freelancer" {{ old('occupation', Auth::user()->occupation ?? '') == 'freelancer' ? 'selected' : '' }}>Freelancer / Profesional</option>
                    <option value="lainnya" {{ old('occupation', Auth::user()->occupation ?? '') == 'lainnya' ? 'selected' : '' }}>Lainnya (Isi Sendiri)</option>
                </select>
            </div>

            <!-- Input Mandiri Jika Memilih 'Lainnya' -->
            <div id="occupation_other_container" class="mb-4 hidden bg-amber-50 border border-amber-200 rounded-lg p-3">
                <label class="block text-gray-800 font-semibold mb-1 text-xs">Sebutkan Pekerjaan / Profesi Anda <span class="text-red-500">*</span></label>
                <input type="text" name="occupation_other" id="occupation_other" class="w-full border border-gray-300 rounded-lg p-2 text-sm bg-white" placeholder="Contoh: Petani, Pengemudi Online, Seniman, dll." value="{{ old('occupation_other') }}">
                <span class="text-[11px] text-gray-500 mt-1 block">Silakan ketikkan profesi atau pekerjaan Anda secara mandiri.</span>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Status Pernikahan</label>
                <select name="marital_status" id="marital_status" class="w-full border border-gray-300 rounded-lg p-2" required onchange="toggleMaritalFields()">
                    <option value="">Pilih Status Pernikahan</option>
                    <option value="belum_menikah">Belum Menikah</option>
                    <option value="menikah">Menikah</option>
                    <option value="cerai_hidup">Cerai Hidup</option>
                    <option value="cerai_meninggal">Cerai Meninggal</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Penghasilan/Bulan</label>
                <input type="number" name="monthly_income" class="w-full border border-gray-300 rounded-lg p-2" required placeholder="Contoh: 5000000" min="0" step="1000">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Angsuran/Bulan Aktif</label>
                <input type="number" name="active_installment" class="w-full border border-gray-300 rounded-lg p-2" placeholder="Contoh: 1000000" min="0" step="1000">
                <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ada angsuran aktif</p>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">NIK (Nomor Induk Kependudukan)</label>
                <input type="text" name="nik" class="w-full border border-gray-300 rounded-lg p-2" required placeholder="16 digit NIK" maxlength="16" value="{{ Auth::user()->nik ?? '' }}">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Tanggal Lahir</label>
                <input type="date" name="birth_date" class="w-full border border-gray-300 rounded-lg p-2" required value="{{ Auth::user()->birth_date ? Auth::user()->birth_date->format('Y-m-d') : '' }}">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Upload KTP</label>
                <input type="file" name="ktp_file" class="w-full border border-gray-300 rounded-lg p-2" accept=".pdf,.jpg,.jpeg,.png" required>
                <p class="text-xs text-gray-500 mt-1">Upload 1 file yang didukung: PDF, atau image. Maks 10 MB.</p>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Upload Kartu Keluarga (KK)</label>
                <input type="file" name="kk_file" class="w-full border border-gray-300 rounded-lg p-2" accept=".pdf,.jpg,.jpeg,.png" required>
                <p class="text-xs text-gray-500 mt-1">Upload 1 file yang didukung: PDF, atau image. Maks 10 MB.</p>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Upload NPWP</label>
                <input type="file" name="npwp_file" class="w-full border border-gray-300 rounded-lg p-2" accept=".pdf,.jpg,.jpeg,.png">
                <p class="text-xs text-gray-500 mt-1">Upload 1 file yang didukung: PDF, atau image. Maks 10 MB. (Opsional)</p>
            </div>

            <!-- Dokumen Tambahan untuk Status Belum Menikah -->
            <div id="single_documents" class="border-l-4 border-gray-400 pl-4 mb-4">
                <h4 class="font-semibold text-gray-800 mb-3">Dokumen Tambahan (Status Belum Menikah)</h4>
                <p class="text-sm text-gray-600">Tidak ada dokumen tambahan yang diperlukan.</p>
            </div>

            <!-- Dokumen Tambahan untuk Status Menikah -->
            <div id="married_documents" class="hidden border-l-4 border-blue-500 pl-4 mb-4">
                <h4 class="font-semibold text-gray-800 mb-3">Dokumen Tambahan (Status Menikah)</h4>
                <div class="mb-3">
                    <label class="block text-gray-700 font-semibold mb-2">Upload KTP Pasangan (*)</label>
                    <input type="file" name="spouse_ktp_file" class="w-full border border-gray-300 rounded-lg p-2" accept=".pdf,.jpg,.jpeg,.png">
                    <p class="text-xs text-gray-500 mt-1">Upload 1 file yang didukung: PDF, atau image. Maks 10 MB.</p>
                </div>
                <div class="mb-3">
                    <label class="block text-gray-700 font-semibold mb-2">Upload KK Pasangan (opsional/jika KK berbeda)</label>
                    <input type="file" name="spouse_kk_file" class="w-full border border-gray-300 rounded-lg p-2" accept=".pdf,.jpg,.jpeg,.png">
                    <p class="text-xs text-gray-500 mt-1">Upload 1 file yang didukung: PDF, atau image. Maks 10 MB.</p>
                </div>
                <div class="mb-3">
                    <label class="block text-gray-700 font-semibold mb-2">Upload Surat Nikah (*)</label>
                    <input type="file" name="marriage_certificate_file" class="w-full border border-gray-300 rounded-lg p-2" accept=".pdf,.jpg,.jpeg,.png">
                    <p class="text-xs text-gray-500 mt-1">Upload 1 file yang didukung: PDF, atau image. Maks 10 MB.</p>
                </div>
            </div>

            <!-- Dokumen Tambahan untuk Status Cerai Hidup/Meninggal -->
            <div id="divorced_documents" class="hidden border-l-4 border-orange-500 pl-4 mb-4">
                <h4 class="font-semibold text-gray-800 mb-3">Dokumen Tambahan (Status Janda/Duda)</h4>
                <div class="mb-3">
                    <label class="block text-gray-700 font-semibold mb-2">Upload Surat Cerai/Kematian Pasangan (*)</label>
                    <input type="file" name="divorce_death_certificate_file" class="w-full border border-gray-300 rounded-lg p-2" accept=".pdf,.jpg,.jpeg,.png">
                    <p class="text-xs text-gray-500 mt-1">Upload 1 file yang didukung: PDF, atau image. Maks 10 MB.</p>
                </div>
            </div>

            <!-- Perumahan yang diminati -->
            <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-semibold text-gray-800">Perumahan yang Diminati</h3>
                    <button type="button" onclick="addHousingPreference()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition text-sm">
                        + Tambah Perumahan
                    </button>
                </div>

                <div id="housing_preferences_container">
                    <!-- Housing preference items will be added here dynamically -->
                </div>

                <p class="text-xs text-gray-500 mt-2">Klik tombol "Tambah Perumahan" untuk menambahkan pilihan perumahan yang diminati.</p>
            </div>

            <div class="mb-6 bg-gray-50 border border-gray-200 rounded-lg p-4">
                <h3 class="font-semibold text-gray-800 mb-3">Pernyataan</h3>
                <div class="space-y-3 text-sm text-gray-700">
                    <div class="flex items-start">
                        <input type="checkbox" id="agreement" name="agreement_accepted" value="1" class="mt-1 mr-2" required>
                        <label for="agreement" class="cursor-pointer">
                            Saya menyatakan bahwa data yang saya isi adalah benar dan dapat dipertanggungjawabkan.
                        </label>
                    </div>
                    <div class="flex items-start">
                        <input type="checkbox" id="statement1" class="mt-1 mr-2" disabled checked>
                        <label class="text-gray-600">
                            Saya memahami bahwa formulir ini bukan merupakan pendaftaran pembelian rumah, bukan perjanjian jual beli, dan bukan pengajuan KPR.
                        </label>
                    </div>
                    <div class="flex items-start">
                        <input type="checkbox" id="statement2" class="mt-1 mr-2" disabled checked>
                        <label class="text-gray-600">
                            Saya memahami bahwa Rumah Anugerah Propertindo berperan sebagai fasilitator edukasi dan penghubung, bukan sebagai penjual properti atau lembaga pembiayaan.
                        </label>
                    </div>
                    <div class="flex items-start">
                        <input type="checkbox" id="statement3" class="mt-1 mr-2" disabled checked>
                        <label class="text-gray-600">
                            Saya memahami bahwa ketersediaan rumah, harga, dan skema pembiayaan sepenuhnya mengikuti kebijakan developer dan lembaga pembiayaan terkait.
                        </label>
                    </div>
                    <div class="flex items-start">
                        <input type="checkbox" id="statement4" class="mt-1 mr-2" disabled checked>
                        <label class="text-gray-600">
                            Saya menyetujui dilakukan pengecekan kesiapan data pembiayaan (SLIK readiness) secara internal dan tidak resmi, sebagai bahan edukasi awal sebelum pengajuan pembiayaan ke lembaga keuangan.
                        </label>
                    </div>
                    <div class="flex items-start">
                        <input type="checkbox" id="statement5" class="mt-1 mr-2" disabled checked>
                        <label class="text-gray-600">
                            Saya memahami bahwa pengecekan ini bukan merupakan akses resmi terhadap Sistem Layanan Informasi Keuangan (SLIK) OJK dan tidak menjamin persetujuan KPR di kemudian hari.
                        </label>
                    </div>
                    <div class="flex items-start">
                        <input type="checkbox" id="statement6" class="mt-1 mr-2" disabled checked>
                        <label class="text-gray-600">
                            Saya memberikan persetujuan kepada Anugerah Propertindo untuk menyampaikan hasil kesiapan data saya kepada agen mitra terkait untuk keperluan tindak lanjut edukasi dan penawaran proyek perumahan.
                        </label>
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-3 rounded-lg hover:bg-blue-700 transition">
                Daftar NUP
            </button>
        </form>
    </div>
</div>

<script>
function toggleOccupationFields() {
    const occSelect = document.getElementById('occupation');
    if (!occSelect) return;
    const occ = occSelect.value;
    const occOtherContainer = document.getElementById('occupation_other_container');
    const occOtherInput = document.getElementById('occupation_other');

    // Tampilkan input mandiri jika memilih 'lainnya'
    if (occ === 'lainnya') {
        if (occOtherContainer) occOtherContainer.classList.remove('hidden');
        if (occOtherInput) occOtherInput.required = true;
    } else {
        if (occOtherContainer) occOtherContainer.classList.add('hidden');
        if (occOtherInput) {
            occOtherInput.required = false;
        }
    }
}

function toggleMaritalFields() {
    const maritalStatus = document.getElementById('marital_status').value;
    const singleDocs = document.getElementById('single_documents');
    const marriedDocs = document.getElementById('married_documents');
    const divorcedDocs = document.getElementById('divorced_documents');

    // Hide all first
    singleDocs.classList.add('hidden');
    marriedDocs.classList.add('hidden');
    divorcedDocs.classList.add('hidden');

    // Show based on status
    if (maritalStatus === 'belum_menikah') {
        singleDocs.classList.remove('hidden');
    } else if (maritalStatus === 'menikah') {
        marriedDocs.classList.remove('hidden');
    } else if (maritalStatus === 'cerai_hidup' || maritalStatus === 'cerai_meninggal') {
        divorcedDocs.classList.remove('hidden');
    }
}

const allBrokers = @json($brokers);

function addHousingPreference() {
    const container = document.getElementById('housing_preferences_container');
    const index = container.children.length;

    const html = `
        <div class="border-2 border-emerald-200 bg-white rounded-xl p-4 mb-4 housing-preference-item shadow-sm" data-index="${index}">
            <div class="flex justify-between items-center mb-3 pb-2 border-b border-gray-100">
                <h4 class="font-bold text-gray-800 text-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Perumahan #${index + 1}</span>
                </h4>
                <button type="button" onclick="removeHousingPreference(this)" class="text-rose-600 hover:text-rose-800 text-xs font-bold inline-flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span>Hapus</span>
                </button>
            </div>

            <!-- 1. Nama Perumahan -->
            <div class="mb-3">
                <label class="block text-gray-700 font-semibold mb-1 text-xs">Nama Perumahan <span class="text-red-500">*</span></label>
                <select name="housing_preferences[${index}][housing_name]" class="housing-select w-full border border-gray-300 rounded-lg p-2.5 text-xs font-semibold focus:ring-2 focus:ring-blue-500" onchange="onHousingSelectChange(this, ${index})" required>
                    <option value="">-- Pilih Perumahan --</option>
                    @forelse($housingProjects as $project)
                        <option value="{{ $project->name }}" 
                                data-company="{{ $project->company?->company_name ?? 'Developer Properti' }}"
                                data-company-id="{{ $project->company_id }}"
                                data-broker-name="{{ $project->broker?->name ?? '' }}"
                                data-broker-contact="{{ $project->broker?->contact ?? '' }}"
                                data-broker-id="{{ $project->broker_id ?? '' }}"
                                data-supported-schemes='@json($project->supported_payment_schemes ?? ["cash", "inhouse", "kpr_bank"])'>
                            {{ $project->name }} ({{ $project->company?->company_name ?? 'Pengembang' }})
                        </option>
                    @empty
                        <option value="" disabled>-- Belum ada perumahan dengan unit tersedia --</option>
                    @endforelse
                </select>
            </div>

            <!-- 2. PT Pengembang (Otomatis Sesuai Operator) -->
            <div class="mb-3">
                <label class="block text-gray-700 font-semibold mb-1 text-xs">PT Pengembang / Developer (Otomatis dari Operator)</label>
                <div class="flex items-center gap-2">
                    <input type="text" id="dev_company_${index}" class="w-full border border-gray-200 bg-gray-100 text-gray-700 font-bold rounded-lg p-2.5 text-xs focus:outline-none" readonly placeholder="Otomatis terisi PT pengembang perumahan...">
                    <span class="text-[11px] text-emerald-700 font-bold whitespace-nowrap bg-emerald-50 px-2 py-1 rounded border border-emerald-200 inline-flex items-center gap-1">
                        <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>PT Resmi</span>
                    </span>
                </div>
            </div>

            <!-- 3. Nama Broker (Otomatis Sesuai Pengembang Perumahan) -->
            <div class="mb-3">
                <label class="block text-gray-700 font-semibold mb-1 text-xs">Nama Broker / Agensi Mitra (Sesuai Pengembang)</label>
                <select name="housing_preferences[${index}][broker_name]" id="broker_select_${index}" class="broker-select w-full border border-gray-300 rounded-lg p-2.5 text-xs font-semibold bg-gray-50 text-gray-500" onchange="onBrokerSelectChange(this, ${index})" disabled>
                    <option value="">-- Pilih Perumahan Terlebih Dahulu --</option>
                </select>
                <input type="hidden" name="housing_preferences[${index}][broker_id]" id="broker_id_${index}">
            </div>

            <!-- 4. Nama Marketing & Contact Marketing (Otomatis Terisi dari Broker) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                <div>
                    <label class="block text-gray-700 font-semibold mb-1 text-xs">Nama Marketing (Otomatis)</label>
                    <input type="text" name="housing_preferences[${index}][marketing_name]" id="mkt_name_${index}" class="w-full border border-gray-200 bg-gray-50 text-gray-800 rounded-lg p-2 text-xs font-semibold focus:ring-2 focus:ring-blue-500" placeholder="Nama marketing penanggung jawab">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-1 text-xs">Contact Marketing (WhatsApp)</label>
                    <input type="text" name="housing_preferences[${index}][marketing_contact]" id="mkt_contact_${index}" class="w-full border border-gray-200 bg-gray-50 text-gray-800 rounded-lg p-2 text-xs font-semibold focus:ring-2 focus:ring-blue-500" placeholder="Nomor telepon marketing">
                </div>
            </div>

            <!-- 5. Rencana Pembelian (Dinamis Sesuai Skema Perumahan yang Dipilih) -->
            <div>
                <label class="block text-gray-700 font-semibold mb-1 text-xs">Rencana Pembelian <span class="text-red-500">*</span></label>
                <select name="housing_preferences[${index}][purchase_plan]" id="purchase_plan_${index}" class="purchase-plan-select w-full border border-gray-300 rounded-lg p-2.5 text-xs font-semibold bg-gray-50 text-gray-500" required disabled>
                    <option value="">-- Pilih Perumahan Terlebih Dahulu --</option>
                </select>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', html);
}

function onHousingSelectChange(selectElem, index) {
    const selectedOpt = selectElem.options[selectElem.selectedIndex];
    const devInput = document.getElementById('dev_company_' + index);
    const brokerSelect = document.getElementById('broker_select_' + index);
    const mktNameInput = document.getElementById('mkt_name_' + index);
    const mktContactInput = document.getElementById('mkt_contact_' + index);
    const brokerIdInput = document.getElementById('broker_id_' + index);
    const planSelect = document.getElementById('purchase_plan_' + index);

    if (!selectedOpt || !selectedOpt.value) {
        if (devInput) devInput.value = '';
        if (brokerSelect) {
            brokerSelect.disabled = true;
            brokerSelect.className = 'broker-select w-full border border-gray-300 rounded-lg p-2.5 text-xs font-semibold bg-gray-50 text-gray-500';
            brokerSelect.innerHTML = '<option value="">-- Pilih Perumahan Terlebih Dahulu --</option>';
        }
        if (planSelect) {
            planSelect.disabled = true;
            planSelect.className = 'purchase-plan-select w-full border border-gray-300 rounded-lg p-2.5 text-xs font-semibold bg-gray-50 text-gray-500';
            planSelect.innerHTML = '<option value="">-- Pilih Perumahan Terlebih Dahulu --</option>';
        }
        if (mktNameInput) mktNameInput.value = '';
        if (mktContactInput) mktContactInput.value = '';
        if (brokerIdInput) brokerIdInput.value = '';
        return;
    }

    const companyName = selectedOpt.getAttribute('data-company') || '';
    const companyId = selectedOpt.getAttribute('data-company-id') || '';
    const defaultBrokerName = selectedOpt.getAttribute('data-broker-name') || '';
    const defaultBrokerContact = selectedOpt.getAttribute('data-broker-contact') || '';
    const defaultBrokerId = selectedOpt.getAttribute('data-broker-id') || '';

    // Otomatis isi Nama PT Pengembang
    if (devInput) devInput.value = companyName;

    // Filter broker HANYA milik developer / perumahan yang dipilih
    if (brokerSelect) {
        brokerSelect.disabled = false;
        brokerSelect.className = 'broker-select w-full border border-gray-300 rounded-lg p-2.5 text-xs font-semibold focus:ring-2 focus:ring-blue-500 bg-white text-gray-900';
        brokerSelect.innerHTML = '';

        const companyBrokers = allBrokers.filter(b => String(b.company_id) === String(companyId));

        brokerSelect.appendChild(new Option('-- Pilih Broker / Mitra Pemasar --', ''));
        brokerSelect.appendChild(new Option('Tanpa Broker (Langsung Pengembang)', 'none'));

        if (companyBrokers.length > 0) {
            companyBrokers.forEach(b => {
                const opt = document.createElement('option');
                opt.value = b.name;
                opt.setAttribute('data-id', b.id);
                opt.setAttribute('data-name', b.name);
                opt.setAttribute('data-contact', b.contact || '');
                opt.textContent = b.name + (b.office_name ? ' (' + b.office_name + ')' : '');

                if ((defaultBrokerId && String(b.id) === String(defaultBrokerId)) || (defaultBrokerName && b.name === defaultBrokerName)) {
                    opt.selected = true;
                }
                brokerSelect.appendChild(opt);
            });
        }

        onBrokerSelectChange(brokerSelect, index);
    }

    // Filter Rencana Pembelian HANYA sesuai checklist operator perumahan yang dipilih
    if (planSelect) {
        planSelect.disabled = false;
        planSelect.className = 'purchase-plan-select w-full border border-gray-300 rounded-lg p-2.5 text-xs font-semibold focus:ring-2 focus:ring-blue-500 bg-white text-gray-900';
        planSelect.innerHTML = '<option value="">-- Pilih Rencana Pembelian --</option>';

        let supportedSchemes = [];
        try {
            const rawSchemes = selectedOpt.getAttribute('data-supported-schemes');
            if (rawSchemes) {
                supportedSchemes = JSON.parse(rawSchemes);
            }
        } catch (e) {
            console.error('Error parsing supported schemes', e);
        }

        if (!Array.isArray(supportedSchemes) || supportedSchemes.length === 0) {
            supportedSchemes = ['cash', 'inhouse', 'kpr_bank'];
        }

        const schemeDefinitions = [
            { key: 'kpr_bank', value: 'kpr_bank_flpp', label: 'KPR Bank (FLPP / Komersial)' },
            { key: 'inhouse', value: 'inhouse', label: 'Tunai Bertahap / In-House (Cicilan ke Developer)' },
            { key: 'cash', value: 'tunai', label: 'Tunai Keras (Cash Lunas)' },
            { key: 'mitra_pintar', value: 'kredit_mitra_pintar', label: 'Kredit Mitra Pintar' }
        ];

        let optionsAdded = 0;
        schemeDefinitions.forEach(item => {
            const isSupported = supportedSchemes.some(s => {
                if (s === item.key) return true;
                if (item.key === 'cash' && (s === 'tunai' || s === 'tunai_keras' || s === 'tunai_bertahap')) return true;
                if (item.key === 'inhouse' && s === 'installment') return true;
                return false;
            });

            if (isSupported) {
                const opt = document.createElement('option');
                opt.value = item.value;
                opt.textContent = item.label;
                planSelect.appendChild(opt);
                optionsAdded++;
            }
        });

        if (optionsAdded === 0) {
            const fallbackOpt = document.createElement('option');
            fallbackOpt.value = 'kpr_bank_flpp';
            fallbackOpt.textContent = 'KPR Bank (FLPP / Komersial)';
            planSelect.appendChild(fallbackOpt);
        }
    }
}

function onBrokerSelectChange(brokerSelect, index) {
    const selectedOpt = brokerSelect.options[brokerSelect.selectedIndex];
    const mktNameInput = document.getElementById('mkt_name_' + index);
    const mktContactInput = document.getElementById('mkt_contact_' + index);
    const brokerIdInput = document.getElementById('broker_id_' + index);

    if (!selectedOpt || !selectedOpt.value) {
        if (brokerIdInput) brokerIdInput.value = '';
        if (mktNameInput) mktNameInput.value = '';
        if (mktContactInput) mktContactInput.value = '';
        return;
    }

    if (selectedOpt.value === 'none') {
        if (brokerIdInput) brokerIdInput.value = '';
        if (mktNameInput) mktNameInput.value = 'Langsung Pengembang';
        if (mktContactInput) mktContactInput.value = '-';
        return;
    }

    const contact = selectedOpt.getAttribute('data-contact') || '';
    const name = selectedOpt.getAttribute('data-name') || selectedOpt.value;
    const id = selectedOpt.getAttribute('data-id') || '';

    if (brokerIdInput) brokerIdInput.value = id;
    if (mktNameInput) mktNameInput.value = name;
    if (mktContactInput) mktContactInput.value = contact;
}

function removeHousingPreference(button) {
    const item = button.closest('.housing-preference-item');
    item.remove();
}

document.addEventListener('DOMContentLoaded', function() {
    toggleOccupationFields();
    toggleMaritalFields();
    const container = document.getElementById('housing_preferences_container');
    if (container && container.children.length === 0) {
        addHousingPreference();
    }
});
</script>
@endsection