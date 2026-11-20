<?php

return [
    /*
    |--------------------------------------------------------------------------
    | NUP Configuration
    |--------------------------------------------------------------------------
    */

    'nup_fee' => env('NUP_FEE', 5000000),
    'queue_number_prefix' => env('NUP_QUEUE_NUMBER_PREFIX', 'NUP-'),
    'registration_prefix' => 'REG-',

    /*
    |--------------------------------------------------------------------------
    | Payment Methods
    |--------------------------------------------------------------------------
    */

    'payment_methods' => [
        'tunai_mitra_pintar' => 'Tunai / Mitra Pintar',
        'kpr_bank_flpp' => 'KPR Bank / FLPP',
        'inhouse' => 'In-House (Cicilan Pengembang)',
        'installment' => 'In-House (Cicilan Pengembang)',
        'tunai' => 'Tunai (Lunas / Bertahap)',
        'cash' => 'Tunai (Lunas / Bertahap)',
    ],

    /*
    |--------------------------------------------------------------------------
    | Unit Types
    |--------------------------------------------------------------------------
    */

    'unit_types' => [
        'type_36' => 'Type 36',
        'type_45' => 'Type 45',
        'type_54' => 'Type 54',
        'type_70' => 'Type 70',
        'type_120' => 'Type 120',
    ],

    /*
    |--------------------------------------------------------------------------
    | User Roles
    |--------------------------------------------------------------------------
    */

    'roles' => [
        'super_admin' => 'Super Admin',
        'member' => 'Member',
        'pihak_bank' => 'Pihak Bank',
        'operator' => 'Operator',
    ],

    /*
    |--------------------------------------------------------------------------
    | KPR Banks
    |--------------------------------------------------------------------------
    */

    'kpr_banks' => [
        'btn' => 'Bank BTN (Bank Rekanan Utama FLPP & KPR)',
        'bni' => 'Bank BNI',
        'bri' => 'Bank BRI',
        'mandiri' => 'Bank Mandiri',
        'bca' => 'Bank BCA',
        'bsi' => 'Bank Syariah Indonesia (BSI)',
        'cimb' => 'CIMB Niaga',
        'other' => 'Bank Lainnya',
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Types
    |--------------------------------------------------------------------------
    */

    'notification_types' => [
        'registration' => 'Registrasi',
        'payment' => 'Pembayaran',
        'queue_number' => 'Nomor Antrian',
        'selection' => 'Pemilihan Unit',
        'screening' => 'Screening KPR',
        'spr' => 'Surat Pemesanan Rumah',
        'reminder' => 'Pengingat',
    ],

    /*
    |--------------------------------------------------------------------------
    | KPR Screening Statuses
    |--------------------------------------------------------------------------
    */

    'screening_statuses' => [
        'pending' => 'Pending',
        'submitted' => 'Submitted',
        'processing' => 'Processing',
        'layak_proses' => 'Layak Proses',
        'layak_bersyarat' => 'Layak Bersyarat',
        'tidak_layak' => 'Tidak Layak',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'requires_document' => 'Requires Document',
    ],

    /*
    |--------------------------------------------------------------------------
    | Participant Decisions
    |--------------------------------------------------------------------------
    */

    'participant_decisions' => [
        'pending' => 'Pending',
        'select_unit' => 'Lanjut KPR & Pilih Unit',
        'switch_to_inhouse' => 'Alih ke In-House Developer',
        'switch_to_cash' => 'Alih ke Tunai',
        'not_agree' => 'Batalkan Pengajuan KPR',
        'request_refund' => 'Request Refund',
        'switch_to_mitra_pintar' => 'Alih ke Mitra Pintar',
    ],

    /*
    |--------------------------------------------------------------------------
    | Deadlines (in hours/days)
    |--------------------------------------------------------------------------
    */

    'deadlines' => [
        'unit_selection_kpr' => 48, // 48 hours (2x24 jam) for KPR lane
        'unit_selection_cash' => 72, // 72 jam (3x24 jam) for cash lane (Tunai/Kredit Mitra Pintar)
        'utk_payment' => 168, // 7 days for UTK payment
    ],

];