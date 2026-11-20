<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Pesanan Rumah (SPR) - {{ $spr->spr_number }}</title>
    <style>
        @page {
            margin: 15mm 18mm 14mm 18mm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            line-height: 1.5;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #222;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .header-title {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #111;
        }
        .header-sub {
            font-size: 10px;
            color: #555;
            margin-top: 2px;
        }
        .spr-number-box {
            text-align: right;
            font-size: 11px;
        }
        .spr-number-title {
            font-weight: bold;
            font-size: 12.5px;
            color: #000;
        }
        .section-intro {
            font-size: 11px;
            font-weight: bold;
            margin-top: 12px;
            margin-bottom: 5px;
            color: #222;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .data-table td {
            padding: 3.2px 0;
            vertical-align: top;
            font-size: 10.5px;
        }
        .data-table .label {
            width: 28%;
            color: #333;
        }
        .data-table .separator {
            width: 3%;
            text-align: center;
        }
        .data-table .value {
            width: 69%;
            font-weight: 600;
            color: #000;
        }
        .rules-list {
            margin: 5px 0 10px 16px;
            padding: 0;
            font-size: 10px;
            color: #333;
            line-height: 1.45;
        }
        .rules-list li {
            margin-bottom: 4px;
            text-align: justify;
        }
        .signature-table {
            width: 100%;
            margin-top: 40px;
            border-collapse: collapse;
        }
        .signature-table td {
            vertical-align: top;
        }
        .broker-col {
            width: 50%;
            text-align: center;
        }
        .dev-col {
            width: 50%;
            text-align: center;
        }
        .sign-date {
            font-size: 10.5px;
            margin-bottom: 5px;
            color: #222;
        }
        .sign-role {
            font-weight: bold;
            font-size: 10.5px;
            color: #111;
            margin-bottom: 6px;
        }
        .dev-signature-img {
            max-height: 70px;
            max-width: 130px;
            object-fit: contain;
            background: transparent;
        }
        .digital-sign-placeholder {
            border: 1px solid #333;
            background-color: #f5f5f5;
            padding: 6px 12px;
            font-size: 8.5px;
            color: #000;
            font-weight: bold;
            border-radius: 4px;
            display: inline-block;
        }
        .sign-name {
            font-weight: bold;
            font-size: 11px;
            text-decoration: underline;
            margin-top: 5px;
            color: #000;
        }
        .sign-company {
            font-size: 9.5px;
            color: #444;
        }
        .watermark {
            position: fixed;
            top: 40%;
            left: 20%;
            font-size: 85px;
            color: rgba(200, 200, 200, 0.22);
            transform: rotate(-35deg);
            z-index: -1000;
            font-weight: bold;
        }
        .footer-note {
            margin-top: 40px;
            border-top: 1px solid #ddd;
            padding-top: 8px;
            font-size: 9px;
            color: #777;
            text-align: center;
        }
    </style>
</head>
<body>
    @if($spr->status === 'draft')
        <div class="watermark">DRAFT SPR</div>
    @elseif($spr->status === 'paid' || $spr->isFullyPaid())
        <div class="watermark">LUNAS</div>
    @endif

    @php
        $unit = $spr->unitSelection?->housingUnit;
        $project = $unit?->housingProject;
        $company = $project?->company;
        $broker = $spr->getBroker();
        $brokerName = $broker?->name ?? ($spr->nupRegistration?->agent_name ?? 'Tim Marketing Penjualan');
        $brokerSignature = $broker?->signature_image;
        $utkAmount = (float) ($spr->unitSelection?->utk_amount ?? 0);
        $isCash = $spr->unitSelection?->isCash() || $spr->unitSelection?->isUtkWaived() || $spr->payment_scheme === 'cash';
        $schemeLabel = $spr->payment_scheme_label;
        $city = $project?->city ?? 'Indonesia';
        $issueDate = $spr->issued_date ?? ($spr->spr_date ? \Carbon\Carbon::parse($spr->spr_date) : now());
        $isPaid = $spr->status === 'paid' || $spr->isFullyPaid();
        $paidDate = $spr->paid_date ?? ($spr->issued_date ?? ($spr->spr_date ? \Carbon\Carbon::parse($spr->spr_date) : now()));
        $totalSudahDibayar = $spr->getTotalSudahDibayar();
        $sisaPembayaran = $spr->getSisaPembayaran();
    @endphp

    <!-- HEADER & NOMOR SPR (SISI KANAN) -->
    <table class="header-table">
        <tr>
            <td style="width: 55%; vertical-align: bottom;">
                <div class="header-title">Surat Pemesanan Rumah (SPR)</div>
                <div class="header-sub">{{ $company?->company_name ?? 'Pengembang Proyek Properti' }}</div>
                @if($project?->name)
                    <div class="header-sub">Kawasan: {{ $project->name }}</div>
                @endif
            </td>
            <td style="width: 45%; vertical-align: bottom;" class="spr-number-box">
                <div class="spr-number-title">No. {{ $spr->spr_number }}</div>
                <div style="font-size: 10px; color: #444; margin-top: 2px;">
                    Tanggal Terbit: {{ $issueDate->translatedFormat('d F Y') }}
                </div>
                <div style="margin-top: 4px;">
                    @if($isPaid)
                        <span style="display: inline-block; padding: 2px 8px; border: 2px solid #000; font-size: 9.5px; font-weight: 900; background-color: #000; color: #fff; text-transform: uppercase; letter-spacing: 1px;">
                            LUNAS (100%)
                        </span>
                    @elseif($spr->status === 'issued')
                        <span style="display: inline-block; padding: 2px 8px; border: 1px solid #000; font-size: 9px; font-weight: bold; text-transform: uppercase;">
                            RESMI DITERBITKAN
                        </span>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <!-- 1. IDENTITAS PEMESAN (SISI KIRI) -->
    <div class="section-intro">Yang bertanda tangan di bawah ini :</div>
    <table class="data-table">
        <tr>
            <td class="label">Nama</td>
            <td class="separator">:</td>
            <td class="value">{{ $spr->buyer_name }}</td>
        </tr>
        <tr>
            <td class="label">NIK</td>
            <td class="separator">:</td>
            <td class="value">{{ $spr->buyer_nik }}</td>
        </tr>
        <tr>
            <td class="label">No. Telp / WhatsApp</td>
            <td class="separator">:</td>
            <td class="value">{{ $spr->buyer_phone }}</td>
        </tr>
        <tr>
            <td class="label">Alamat</td>
            <td class="separator">:</td>
            <td class="value">{{ $spr->buyer_address }}</td>
        </tr>
    </table>

    <!-- 2. DATA UNIT YANG DIPESAN -->
    <div class="section-intro">Dengan ini menyatakan melakukan pemesanan unit rumah sebagai berikut :</div>
    <table class="data-table">
        <tr>
            <td class="label">Nama Perumahan</td>
            <td class="separator">:</td>
            <td class="value">{{ $project?->name ?? 'Perumahan' }}</td>
        </tr>
        <tr>
            <td class="label">Nama Developer</td>
            <td class="separator">:</td>
            <td class="value">{{ $company?->company_name ?? 'Developer' }}</td>
        </tr>
        <tr>
            <td class="label">Tipe</td>
            <td class="separator">:</td>
            <td class="value">{{ config('nup.unit_types.' . ($unit?->type ?? ''), $unit?->type ?? '-') }}</td>
        </tr>
        <tr>
            <td class="label">Blok / No</td>
            <td class="separator">:</td>
            <td class="value">Blok {{ $unit?->block ?? '-' }} / No. {{ $unit?->number ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Harga Unit</td>
            <td class="separator">:</td>
            <td class="value">Rp {{ number_format($spr->unit_price, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">UTK (Uang Tanda Komitmen)</td>
            <td class="separator">:</td>
            <td class="value">
                @if($isCash)
                    Rp 0 (Bebas UTK - Skema Tunai)
                @else
                    Rp {{ number_format($utkAmount, 0, ',', '.') }}
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">Cara Bayar / Pembiayaan</td>
            <td class="separator">:</td>
            <td class="value" style="color: #000; font-weight: bold;">{{ $schemeLabel }}</td>
        </tr>
        <tr>
            <td class="label">Status Pembayaran</td>
            <td class="separator">:</td>
            <td class="value">
                @if($isPaid)
                    <strong style="color: #000;">LUNAS (100%)</strong> &mdash; Tanggal Lunas: {{ $paidDate->translatedFormat('d F Y') }}
                @else
                    BELUM LUNAS
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">Total Terbayar</td>
            <td class="separator">:</td>
            <td class="value">
                Rp {{ number_format($totalSudahDibayar, 0, ',', '.') }}
                @if($isPaid)
                    <span style="font-weight: normal; font-size: 9.5px; color: #444;">(Lunas Penuh)</span>
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">{{ $spr->payment_scheme === 'kpr' ? 'Plafon Pembiayaan Bank' : 'Sisa Tagihan / Kewajiban' }}</td>
            <td class="separator">:</td>
            <td class="value">
                @if($spr->payment_scheme === 'kpr')
                    Rp {{ number_format($spr->unit_price, 0, ',', '.') }} (100% Pembiayaan KPR Bank)
                @elseif($isPaid)
                    Rp 0 (Bebas Kewajiban Pembayaran)
                @else
                    Rp {{ number_format($sisaPembayaran, 0, ',', '.') }}
                @endif
            </td>
        </tr>
    </table>

    @if($isPaid)
        <!-- OFFICIAL MONOCHROME STAMP LUNAS -->
        <table style="width: 100%; margin-top: 5px; margin-bottom: 6px; border: 1px dashed #000; background-color: #fafafa;">
            <tr>
                <td style="padding: 4px 8px; vertical-align: middle;">
                    <div style="font-size: 9px; font-weight: bold; color: #000; text-transform: uppercase;">STATUS DOKUMEN: TELAH LUNAS 100%</div>
                    <div style="font-size: 8px; color: #444;">Unit pemesanan ini telah lunas dan diverifikasi resmi oleh pengembang pada tanggal {{ $paidDate->translatedFormat('d F Y') }}.</div>
                </td>
                <td style="width: 130px; text-align: right; padding: 4px 8px; vertical-align: middle;">
                    <div style="display: inline-block; border: 1.5px solid #000; padding: 2px 8px; text-align: center; background-color: #fff;">
                        <div style="font-size: 6.5px; font-weight: bold; letter-spacing: 0.5px; color: #000;">SURAT PEMESANAN RUMAH</div>
                        <div style="font-size: 10px; font-weight: 900; letter-spacing: 1px; border-top: 1px solid #000; border-bottom: 1px solid #000; margin: 1px 0; padding: 0.5px 0; color: #000;">RESMI LUNAS</div>
                        <div style="font-size: 6.5px; font-family: monospace; color: #333;">{{ $paidDate->translatedFormat('d/m/Y') }} • TERVERIFIKASI</div>
                    </div>
                </td>
            </tr>
        </table>
    @endif

    <!-- 3. ATURAN & KETENTUAN -->
    <div class="section-intro">Dan bersedia mengikuti aturan sebagai berikut :</div>
    <ol class="rules-list">
        <li>
            <strong>Komitmen Pemesanan Unit:</strong> Pembayaran Uang Tanda Komitmen (UTK) / pemilihan unit merupakan bukti sah penguncian unit dan bersifat mengikat kedua belah pihak.
        </li>
        <li>
            <strong>Kelengkapan Berkas & Administrasi:</strong> Pemesan bersedia melengkapi dan menandatangani seluruh kelengkapan berkas administrasi pembiayaan selambat-lambatnya 7 (tujuh) hari kerja sejak SPR ini diterbitkan.
        </li>
        @if($spr->payment_scheme === 'kpr' || str_contains(strtolower($spr->payment_scheme), 'kpr'))
            <li>
                <strong>Ketentuan Skema KPR Bank:</strong> Realisasi pembiayaan tunduk pada keputusan persetujuan kredit (SP3K) bank rekanan. Apabila terjadi penurunan plafon kredit oleh bank, Pemesan bersedia menambah pembayaran uang muka atau beralih ke skema In-House / Tunai.
            </li>
        @elseif($spr->payment_scheme === 'tunai_bertahap')
            <li>
                <strong>Ketentuan Skema Tunai Bertahap:</strong> Pelunasan pembayaran sisa harga unit diselesaikan bertahap dalam jangka hari (termin hari) sesuai kesepakatan pengembang.
            </li>
        @elseif($spr->payment_scheme === 'installment' || str_contains(strtolower($spr->payment_scheme), 'inhouse'))
            <li>
                <strong>Ketentuan Skema In-House:</strong> Pembayaran angsuran cicilan bulanan wajib disetorkan paling lambat sesuai tanggal jatuh tempo bulanan yang telah disepakati pengembang.
            </li>
        @elseif($spr->payment_scheme === 'mitra_pintar')
            <li>
                <strong>Ketentuan Kredit Mitra Pintar:</strong> Pemesan wajib mematuhi seluruh klausul perjanjian kerja sama (PKS) instansi mitra yang disepakati bersama pengembang.
            </li>
        @else
            <li>
                <strong>Ketentuan Skema Tunai:</strong> Pelunasan pembayaran unit diselesaikan sesuai jadwal termin yang disepakati tanpa keterlambatan.
            </li>
        @endif
        <li>
            <strong>Pembatalan Sepihak:</strong> Apabila pemesan membatalkan pemesanan secara sepihak di luar ketentuan yang disepakati, maka tata cara pengembalian dan potongan biaya administrasi mengacu pada kebijakan resmi pengembang.
        </li>
        <li>
            <strong>Keabsahan Dokumen Elektronik:</strong> Surat Pemesanan Rumah (SPR) ini sah dan mengikat para pihak secara elektronik sejak disetujui dan ditandatangani oleh para pihak.
        </li>
    </ol>

    <!-- 4. BAGIAN TANDA TANGAN (KIRI: BROKER/MARKETING, KANAN: TGL & TTD DEVELOPER) -->
    <table class="signature-table">
        <tr>
            <!-- SISI KIRI: NAMA BROKER / MARKETING -->
            <td class="broker-col">
                <div class="sign-role">Pihak Pemasar / Broker :</div>
                <div style="height: 75px; text-align: center; vertical-align: middle;">
                    @if($brokerSignature && file_exists(storage_path('app/public/' . $brokerSignature)))
                        <img src="{{ storage_path('app/public/' . $brokerSignature) }}" class="dev-signature-img" alt="TTD Broker">
                    @else
                        <div style="font-size: 8.5px; color: #888; padding-top: 30px;">[ Tanda Tangan Broker ]</div>
                    @endif
                </div>
                <div class="sign-name">{{ $brokerName }}</div>
                <div class="sign-company">{{ $broker?->office_name ?? 'Broker / Marketing Penjualan' }}</div>
            </td>

            <!-- SISI KANAN: TGL TERBIT SPR & TTD DEVELOPER -->
            <td class="dev-col">
                <div class="sign-date">{{ $city }}, {{ $issueDate->translatedFormat('d F Y') }}</div>
                <div class="sign-role">Pihak Pengembang (Developer) :</div>
                <div style="height: 75px; text-align: center; vertical-align: middle;">
                    @if($company && $company->signature_image && file_exists(storage_path('app/public/' . $company->signature_image)))
                        <img src="{{ storage_path('app/public/' . $company->signature_image) }}" class="dev-signature-img" alt="TTD Developer">
                    @else
                        <div class="digital-sign-placeholder" style="margin-top: 22px;">
                            [TERVERIFIKASI DIGITAL]<br>
                            <span style="font-size: 7.5px; font-weight: normal; color: #555;">Valid Electronic Signature</span>
                        </div>
                    @endif
                </div>
                <div class="sign-name">{{ $company?->pic_name ?? ($company?->company_name ?? 'Direktur Pengembang') }}</div>
                <div class="sign-company">{{ $company?->company_name ?? 'Developer Properti' }}</div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Dokumen Surat Pemesanan Rumah (SPR) ini diterbitkan secara sah melalui sistem elektronik APRO.<br>
        Nomor Dokumen: <strong>No. {{ $spr->spr_number }}</strong> | Pemesan: <strong>{{ $spr->buyer_name }}</strong>
    </div>
</body>
</html>
