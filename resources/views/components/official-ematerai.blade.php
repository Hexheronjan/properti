@props([
    'spr',
    'width' => 130,
    'height' => 130,
])

@php
    $issueDate = $spr->issued_date ?? ($spr->spr_date ? \Carbon\Carbon::parse($spr->spr_date) : now());
    // Format 22 digit alphanumeric SN khas Peruri
    $rawHash = strtoupper(hash('sha256', 'PERURI_' . $spr->id . '_' . $spr->spr_number));
    $serialNumber = $spr->ematerai_id ?? (substr($rawHash, 0, 4) . ' ' . substr($rawHash, 4, 4) . ' ' . substr($rawHash, 8, 4) . ' ' . substr($rawHash, 12, 4) . ' ' . substr($rawHash, 16, 4));
    $dateStr = $issueDate->format('d/m/Y');
@endphp

<div class="relative inline-block select-none" style="width: {{ $width }}px; height: {{ $height }}px;" title="Meterai Elektronik Resmi 10000 Peruri RI">
    <svg viewBox="0 0 280 280" class="w-full h-full drop-shadow-sm" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <!-- Background Gradient Khas e-Materai Peruri -->
            <radialGradient id="ematerai-bg" cx="50%" cy="50%" r="50%">
                <stop offset="0%" stop-color="#ffffff" />
                <stop offset="70%" stop-color="#fff0f5" />
                <stop offset="100%" stop-color="#fce4ec" />
            </radialGradient>

            <!-- Rosette Security Guilloche Pattern -->
            <pattern id="guilloche" width="20" height="20" patternUnits="userSpaceOnUse">
                <circle cx="10" cy="10" r="8" fill="none" stroke="#f8bbd0" stroke-width="0.5" opacity="0.6"/>
                <circle cx="10" cy="10" r="4" fill="none" stroke="#f48fb1" stroke-width="0.3" opacity="0.4"/>
            </pattern>
        </defs>

        <!-- Base Background -->
        <rect x="2" y="2" width="276" height="276" rx="14" fill="url(#ematerai-bg)" stroke="#e91e63" stroke-width="2" stroke-dasharray="4 2"/>
        <rect x="8" y="8" width="264" height="264" rx="10" fill="url(#guilloche)"/>

        <!-- BINGKAI DATA MATRIX / QR CODE MOSAIC PATTERN KHAS PERURI (WARNA MAGENTA) -->
        <!-- Corner Top-Left QR Marker -->
        <rect x="14" y="14" width="38" height="38" fill="#d81b60" rx="3"/>
        <rect x="20" y="20" width="26" height="26" fill="#fff" rx="2"/>
        <rect x="26" y="26" width="14" height="14" fill="#d81b60" rx="1"/>

        <!-- Corner Top-Right QR Marker -->
        <rect x="228" y="14" width="38" height="38" fill="#d81b60" rx="3"/>
        <rect x="234" y="20" width="26" height="26" fill="#fff" rx="2"/>
        <rect x="240" y="26" width="14" height="14" fill="#d81b60" rx="1"/>

        <!-- Corner Bottom-Left QR Marker -->
        <rect x="14" y="228" width="38" height="38" fill="#d81b60" rx="3"/>
        <rect x="20" y="234" width="26" height="26" fill="#fff" rx="2"/>
        <rect x="26" y="240" width="14" height="14" fill="#d81b60" rx="1"/>

        <!-- Corner Bottom-Right QR Marker -->
        <rect x="228" y="228" width="38" height="38" fill="#d81b60" rx="3"/>
        <rect x="234" y="234" width="26" height="26" fill="#fff" rx="2"/>
        <rect x="240" y="240" width="14" height="14" fill="#d81b60" rx="1"/>

        <!-- Sisi Kiri Data Matrix Pixel Grid -->
        <g fill="#d81b60">
            <rect x="16" y="60" width="8" height="8"/><rect x="30" y="60" width="8" height="8"/>
            <rect x="22" y="74" width="8" height="8"/><rect x="38" y="74" width="8" height="8"/>
            <rect x="16" y="88" width="8" height="8"/><rect x="28" y="88" width="8" height="8"/>
            <rect x="22" y="102" width="8" height="8"/><rect x="36" y="102" width="8" height="8"/>
            <rect x="16" y="116" width="8" height="8"/><rect x="30" y="116" width="8" height="8"/>
            <rect x="22" y="130" width="8" height="8"/><rect x="38" y="130" width="8" height="8"/>
            <rect x="16" y="144" width="8" height="8"/><rect x="30" y="144" width="8" height="8"/>
            <rect x="24" y="158" width="8" height="8"/><rect x="38" y="158" width="8" height="8"/>
            <rect x="16" y="172" width="8" height="8"/><rect x="28" y="172" width="8" height="8"/>
            <rect x="22" y="186" width="8" height="8"/><rect x="36" y="186" width="8" height="8"/>
            <rect x="16" y="200" width="8" height="8"/><rect x="30" y="200" width="8" height="8"/><rect x="40" y="200" width="8" height="8"/>
        </g>

        <!-- Sisi Kanan Data Matrix Pixel Grid -->
        <g fill="#d81b60">
            <rect x="234" y="60" width="8" height="8"/><rect x="248" y="60" width="8" height="8"/>
            <rect x="242" y="74" width="8" height="8"/><rect x="256" y="74" width="8" height="8"/>
            <rect x="234" y="88" width="8" height="8"/><rect x="248" y="88" width="8" height="8"/>
            <rect x="226" y="102" width="8" height="8"/><rect x="242" y="102" width="8" height="8"/>
            <rect x="234" y="116" width="8" height="8"/><rect x="250" y="116" width="8" height="8"/>
            <rect x="226" y="130" width="8" height="8"/><rect x="242" y="130" width="8" height="8"/>
            <rect x="234" y="144" width="8" height="8"/><rect x="254" y="144" width="8" height="8"/>
            <rect x="226" y="158" width="8" height="8"/><rect x="242" y="158" width="8" height="8"/>
            <rect x="234" y="172" width="8" height="8"/><rect x="248" y="172" width="8" height="8"/>
            <rect x="226" y="186" width="8" height="8"/><rect x="242" y="186" width="8" height="8"/>
            <rect x="234" y="200" width="8" height="8"/><rect x="248" y="200" width="8" height="8"/>
        </g>

        <!-- Sisi Atas Data Matrix Grid -->
        <g fill="#d81b60">
            <rect x="62" y="16" width="8" height="8"/><rect x="76" y="16" width="8" height="8"/>
            <rect x="90" y="22" width="8" height="8"/><rect x="104" y="16" width="8" height="8"/>
            <rect x="168" y="16" width="8" height="8"/><rect x="182" y="22" width="8" height="8"/>
            <rect x="196" y="16" width="8" height="8"/><rect x="210" y="16" width="8" height="8"/>
        </g>

        <!-- GARUDA PANCASILA EMBLEM (GOLDEN DETAILING) -->
        <g transform="translate(140, 52) scale(0.68)">
            <!-- Golden Wings Silhouette & Feathers -->
            <path d="M 0,-30 
                     C -10,-28 -28,-18 -45,-5 
                     C -36,0 -30,8 -32,16 
                     C -24,14 -16,18 -12,25 
                     C -8,18 -3,20 0,22
                     C 3,20 8,18 12,25 
                     C 16,18 24,14 32,16 
                     C 30,8 36,0 45,-5 
                     C 28,-18 10,-28 0,-30 Z" 
                  fill="#c8961c" stroke="#926806" stroke-width="1.2"/>
            <!-- Tail Feathers -->
            <path d="M -12,22 C -10,35 -6,45 0,48 C 6,45 10,35 12,22 Z" fill="#d4af37" stroke="#926806" stroke-width="1"/>
            <!-- Shield Perisai Pancasila -->
            <path d="M -14,-8 L 14,-8 L 14,8 C 14,18 0,24 0,24 C 0,24 -14,18 -14,8 Z" 
                  fill="#b71c1c" stroke="#ffe082" stroke-width="1.5"/>
            <!-- Shield Center Gold Star -->
            <circle cx="0" cy="4" r="3.5" fill="#fff" stroke="#ffd54f" stroke-width="1"/>
            <!-- Head facing right -->
            <circle cx="0" cy="-26" r="6" fill="#d4af37"/>
            <path d="M 4,-26 L 10,-24 L 4,-22 Z" fill="#926806"/>
            <!-- Pita Bhinneka Tunggal Ika -->
            <rect x="-18" y="32" width="36" height="5" rx="1.5" fill="#ffffff" stroke="#c8961c" stroke-width="0.8"/>
        </g>

        <!-- TEKS UTAMA RESMI: METERAI ELEKTRONIK -->
        <text x="140" y="112" 
              text-anchor="middle" 
              fill="#c2185b" 
              font-family="'Arial Black', 'Helvetica', sans-serif" 
              font-weight="900" 
              font-size="14.5" 
              letter-spacing="1">METERAI</text>
        <text x="140" y="128" 
              text-anchor="middle" 
              fill="#c2185b" 
              font-family="'Arial', 'Helvetica', sans-serif" 
              font-weight="800" 
              font-size="11.5" 
              letter-spacing="2">ELEKTRONIK</text>

        <!-- NOMINAL 10000 RESMI (WARNA EMAS / GOLDEN BRONZE TEBAL) -->
        <text x="140" y="172" 
              text-anchor="middle" 
              fill="#b58102" 
              stroke="#ffe082"
              stroke-width="0.5"
              font-family="'Impact', 'Arial Black', sans-serif" 
              font-weight="900" 
              font-size="44" 
              letter-spacing="1">10000</text>

        <!-- TEKS TERBILANG: SEPULUH RIBU RUPIAH -->
        <text x="140" y="188" 
              text-anchor="middle" 
              fill="#ad1457" 
              font-family="'Arial', 'Helvetica', sans-serif" 
              font-weight="800" 
              font-size="9" 
              letter-spacing="0.8">SEPULUH RIBU RUPIAH</text>

        <!-- GARIS PEMISAH HALUS -->
        <line x1="58" y1="198" x2="222" y2="198" stroke="#f48fb1" stroke-width="1" stroke-dasharray="2 2"/>

        <!-- TANGGAL & KODE PERURI SERIAL NUMBER -->
        <text x="140" y="210" 
              text-anchor="middle" 
              fill="#6a1b9a" 
              font-family="'Courier New', Courier, monospace" 
              font-weight="bold" 
              font-size="8.5">TGL: {{ $dateStr }}</text>

        <!-- 22 DIGIT SERIAL NUMBER UNIQUE -->
        <text x="140" y="222" 
              text-anchor="middle" 
              fill="#37474f" 
              font-family="'Courier New', Courier, monospace" 
              font-weight="bold" 
              font-size="7.5"
              letter-spacing="0.5">SN: {{ $serialNumber }}</text>
    </svg>
</div>
