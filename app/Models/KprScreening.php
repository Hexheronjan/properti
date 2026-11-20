<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KprScreening extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nup_registration_id',
        'unit_selection_id',
        'screening_number',
        'bank',
        'bank_other',
        'loan_amount',
        'tenor_years',
        'monthly_income',
        'monthly_expense',
        'employment_status',
        'employment_duration_years',
        'employer_name',
        'screening_fee_amount',
        'screening_fee_status',
        'screening_fee_proof',
        'screening_fee_paid_at',
        'screening_fee_verified_at',
        'screening_fee_verified_by',
        'screening_fee_notes',
        'developer_bank_account_id',
        'status',
        'pefindo_score',
        'pefindo_grade',
        'pefindo_notes',
        'pefindo_file',
        'rejection_reason',
        'submission_date',
        'approval_date',
        'approved_by',
        'notes',
        'unit_selection_deadline',
        'participant_decision',
        'decision_made_at',
        'decision_notes',
        'notification_sent_at',
    ];

    protected $casts = [
        'loan_amount' => 'decimal:2',
        'monthly_income' => 'decimal:2',
        'monthly_expense' => 'decimal:2',
        'screening_fee_amount' => 'decimal:2',
        'screening_fee_paid_at' => 'datetime',
        'screening_fee_verified_at' => 'datetime',
        'submission_date' => 'datetime',
        'approval_date' => 'datetime',
        'unit_selection_deadline' => 'datetime',
        'decision_made_at' => 'datetime',
        'notification_sent_at' => 'datetime',
    ];

    /**
     * Get the user for the KPR screening.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the NUP registration for the KPR screening.
     */
    public function nupRegistration()
    {
        return $this->belongsTo(NupRegistration::class);
    }

    /**
     * Get the unit selection for the KPR screening.
     */
    public function unitSelection()
    {
        return $this->belongsTo(UnitSelection::class);
    }

    /**
     * Get the user documents for the KPR screening.
     */
    public function userDocuments()
    {
        return $this->hasMany(UserDocument::class);
    }

    /**
     * Get the SPR documents for the KPR screening.
     */
    public function sprDocuments()
    {
        return $this->hasMany(SprDocument::class);
    }

    /**
     * Get the KPR payments for the KPR screening.
     */
    public function kprPayments()
    {
        return $this->hasMany(KprPayment::class);
    }

    /**
     * Label dokumen KPR lengkap
     */
    public static function documentLabels(): array
    {
        return [
            'ktp' => 'E-KTP Pemohon',
            'kk' => 'Kartu Keluarga (KK)',
            'npwp' => 'NPWP Pemohon',
            'marriage_certificate' => 'Buku Nikah / Akta Perkawinan / Akta Cerai',
            'sk_kerja' => 'SK Pengangkatan / Perjanjian Kerja (Karyawan/ASN/PPPK)',
            'salary_slip' => 'Slip Gaji 6 Bulan Terakhir',
            'sku_nib' => 'Surat Keterangan Usaha (Kelurahan) / NIB OSS (Wirausaha)',
            'financial_statement' => 'Laporan Keuangan / Pembukuan 6 Bulan (Wirausaha)',
            'bank_statement' => 'Rekening Koran 6 Bulan Terakhir',
            'other' => 'Dokumen Pendukung Lainnya',
        ];
    }

    /**
     * Resolusi otomatis status pekerjaan dari data NUP / User
     */
    public static function resolveEmploymentStatus(?string $occupation): string
    {
        if (!$occupation) {
            return 'employee';
        }

        $occupation = strtolower($occupation);

        if (str_contains($occupation, 'wiraswasta') || str_contains($occupation, 'usaha') || str_contains($occupation, 'umkm') || str_contains($occupation, 'entrepreneur') || str_contains($occupation, 'bisnis') || str_contains($occupation, 'dagang')) {
            return 'entrepreneur';
        }

        if (str_contains($occupation, 'freelance') || str_contains($occupation, 'professional') || str_contains($occupation, 'profesional') || str_contains($occupation, 'dokter') || str_contains($occupation, 'notaris') || str_contains($occupation, 'pengacara')) {
            return 'professional';
        }

        if (str_contains($occupation, 'pensiun')) {
            return 'retired';
        }

        if ($occupation === 'lainnya' || str_contains($occupation, 'lainnya')) {
            return 'other';
        }

        // Default: employee (karyawan tetap, asn, pppk, kontrak, dll)
        return 'employee';
    }

    /**
     * Label pekerjaan manusiawi berdasarkan data NUP
     */
    public static function getOccupationLabel(?string $occupation, ?string $occupationOther = null): string
    {
        if ($occupation === 'lainnya' && !empty($occupationOther)) {
            return $occupationOther . ' (Lainnya)';
        }

        return match($occupation) {
            'karyawan_tetap_asn' => 'Karyawan Tetap / ASN',
            'karyawan_kontrak_pppk' => 'Karyawan Kontrak / PPPK',
            'wiraswasta_umkm' => 'Wiraswasta / Pemilik Usaha (UMKM)',
            'freelancer' => 'Freelancer / Pekerja Lepas',
            'lainnya' => $occupationOther ?: 'Lainnya',
            'employee' => 'Karyawan / ASN / Kontrak / PPPK',
            'entrepreneur' => 'Wirausaha / Pemilik Usaha',
            'professional' => 'Profesional',
            'retired' => 'Pensiunan',
            'other' => $occupationOther ?: 'Lainnya',
            default => $occupation ? ucwords(str_replace('_', ' ', $occupation)) : 'Karyawan / ASN / Kontrak'
        };
    }

    /**
     * Dapatkan daftar dokumen wajib sesuai status pekerjaan
     */
    public function getRequiredDocuments(): array
    {
        if ($this->employment_status === 'entrepreneur' || $this->employment_status === 'professional') {
            return [
                'sku_nib' => 'Surat Keterangan Usaha (Kelurahan) / NIB OSS',
                'financial_statement' => 'Laporan Keuangan 6 Bulan Terakhir',
                'bank_statement' => 'Rekening Koran 6 Bulan Terakhir',
            ];
        }

        if ($this->employment_status === 'other') {
            return [
                'bank_statement' => 'Rekening Koran 6 Bulan Terakhir',
                'other' => 'Bukti Penghasilan / Dokumen Pendukung',
            ];
        }

        // Default: Karyawan / ASN / Kontrak / PPPK
        return [
            'sk_kerja' => 'SK Karyawan / Pengangkatan / Perjanjian Kerja',
            'salary_slip' => 'Slip Gaji 6 Bulan Terakhir',
            'bank_statement' => 'Rekening Koran 6 Bulan Terakhir',
        ];
    }

    /**
     * Cek apakah seluruh berkas persyaratan screening telah disetujui (ACC) oleh operator
     */
    public function areAllRequiredDocumentsApproved(): bool
    {
        $requiredDocs = $this->getRequiredDocuments();
        if (empty($requiredDocs)) {
            return false;
        }

        foreach (array_keys($requiredDocs) as $docType) {
            $doc = $this->userDocuments->where('document_type', $docType)->sortByDesc('created_at')->first();
            if (!$doc || $doc->status !== 'approved') {
                return false;
            }
        }

        return true;
    }

    /**
     * Dapatkan daftar berkas persyaratan yang belum di-ACC (pending, rejected, atau belum diunggah)
     */
    public function getUnapprovedRequiredDocuments(): array
    {
        $requiredDocs = $this->getRequiredDocuments();
        $unapproved = [];

        foreach ($requiredDocs as $docType => $docTitle) {
            $doc = $this->userDocuments->where('document_type', $docType)->sortByDesc('created_at')->first();
            if (!$doc) {
                $unapproved[] = [
                    'type' => $docType,
                    'title' => $docTitle,
                    'reason' => 'Belum diunggah oleh pemohon'
                ];
            } elseif ($doc->status === 'rejected') {
                $unapproved[] = [
                    'type' => $docType,
                    'title' => $docTitle,
                    'reason' => 'Perlu revisi: ' . ($doc->rejection_reason ?: 'Menunggu unggahan ulang pemohon')
                ];
            } elseif ($doc->status !== 'approved') {
                $unapproved[] = [
                    'type' => $docType,
                    'title' => $docTitle,
                    'reason' => 'Menunggu verifikasi / belum di-ACC oleh developer'
                ];
            }
        }

        return $unapproved;
    }

    /**
     * Sinkronisasi dokumen pendaftaran NUP ke berkas screening KPR secara otomatis
     */
    public function syncNupDocuments(): void
    {
        $nup = $this->nupRegistration;
        if (!$nup) return;

        $docMappings = [
            'sk_kerja' => $nup->sk_karyawan_file,
            'salary_slip' => $nup->salary_slip_file,
            'bank_statement' => $nup->bank_statement_file,
            'sku_nib' => $nup->sku_file,
            'financial_statement' => $nup->financial_statement_file,
            'other' => $nup->additional_income_file,
        ];

        foreach ($docMappings as $docType => $filePath) {
            if (!$filePath) continue;

            $existing = $this->userDocuments()->where('document_type', $docType)->first();
            if (!$existing) {
                $ext = pathinfo($filePath, PATHINFO_EXTENSION);
                $name = basename($filePath);
                $size = \Illuminate\Support\Facades\Storage::disk('public')->exists($filePath)
                    ? \Illuminate\Support\Facades\Storage::disk('public')->size($filePath)
                    : 0;

                $this->userDocuments()->create([
                    'user_id' => $this->user_id,
                    'document_type' => $docType,
                    'document_name' => $name,
                    'file_path' => $filePath,
                    'file_type' => $ext ?: 'pdf',
                    'file_size' => $size,
                    'status' => 'pending',
                    'uploaded_at' => now(),
                ]);
            }
        }
    }

    /**
     * Check if screening is approved (layak_proses)
     */
    public function isApproved(): bool
    {
        return $this->status === 'layak_proses' || $this->status === 'approved';
    }

    /**
     * Check if screening is rejected (tidak_layak)
     */
    public function isRejected(): bool
    {
        return $this->status === 'tidak_layak' || $this->status === 'rejected';
    }

    /**
     * Check if screening is layak_proses (fully eligible)
     */
    public function isLayakProses(): bool
    {
        return $this->status === 'layak_proses';
    }

    /**
     * Check if screening is layak_bersyarat (conditionally eligible)
     */
    public function isLayakBersyarat(): bool
    {
        return $this->status === 'layak_bersyarat';
    }

    /**
     * Check if screening is tidak_layak (not eligible)
     */
    public function isTidakLayak(): bool
    {
        return $this->status === 'tidak_layak';
    }

    /**
     * Check if screening is submitted
     */
    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    /**
     * Generate screening number
     */
    public static function generateScreeningNumber(): string
    {
        $prefix = 'SCR-' . now()->format('Ymd') . '-';
        do {
            $number = $prefix . strtoupper(\Illuminate\Support\Str::random(4));
        } while (self::where('screening_number', $number)->exists());

        return $number;
    }

    /**
     * Scope a query to only include approved screenings.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include pending screenings.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include processing screenings.
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    /**
     * Set unit selection deadline (48 hours after approval/layak_bersyarat)
     */
    public function setUnitSelectionDeadline(): void
    {
        $hours = config('nup.deadlines.unit_selection_kpr', 48);
        $this->unit_selection_deadline = now()->addHours($hours);
        $this->notification_sent_at = now();
        $this->save();
    }

    /**
     * Check if unit selection deadline has passed
     */
    public function isUnitSelectionDeadlinePassed(): bool
    {
        return $this->unit_selection_deadline && now()->gt($this->unit_selection_deadline);
    }

    /**
     * Record participant decision
     */
    public function recordParticipantDecision(string $decision, ?string $notes = null): void
    {
        $this->update([
            'participant_decision' => $decision,
            'decision_made_at' => now(),
            'decision_notes' => $notes,
        ]);
    }

    /**
     * Check if participant has made a decision
     */
    public function hasParticipantDecision(): bool
    {
        return !empty($this->participant_decision) && $this->participant_decision !== 'pending';
    }

    /**
     * Rekening developer yang dipilih untuk transfer biaya screening
     */
    public function developerBankAccount()
    {
        return $this->belongsTo(DeveloperBankAccount::class);
    }

    /**
     * Cek apakah biaya screening sudah diverifikasi atau digratiskan
     */
    public function isScreeningFeeVerified(): bool
    {
        return in_array($this->screening_fee_status, ['verified', 'waived']);
    }

    /**
     * Cek apakah pembayaran biaya screening sedang menunggu verifikasi
     */
    public function isScreeningFeePending(): bool
    {
        return $this->screening_fee_status === 'pending_verification';
    }

    /**
     * Dapatkan perumahan terkait
     */
    public function getHousingProject()
    {
        if ($this->unitSelection?->housingUnit?->housingProject) {
            return $this->unitSelection->housingUnit->housingProject;
        }

        if ($this->nupRegistration) {
            $unitSel = $this->nupRegistration->unitSelections()->latest()->first();
            if ($unitSel?->housingUnit?->housingProject) {
                return $unitSel->housingUnit->housingProject;
            }
        }

        return HousingProject::first();
    }

    /**
     * Rincian kalkulasi biaya screening KPR: Pokok (min 150rb) + PPN 11% + VE 20%
     */
    public function getScreeningFeeBreakdown(): array
    {
        $project = $this->getHousingProject();
        $base = (float) ($project?->screening_fee ?? 150000);
        if ($base < 150000 && $base > 0) {
            $base = 150000;
        }
        $ppn = round($base * 0.11, 2);
        $ve = round($base * 0.20, 2);
        $total = $base + $ppn + $ve;

        return [
            'base'  => $base,
            'ppn'   => $ppn,
            've'    => $ve,
            'total' => $total,
        ];
    }
}