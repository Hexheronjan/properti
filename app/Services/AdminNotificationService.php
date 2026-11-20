<?php

namespace App\Services;

use App\Models\KprPayment;
use App\Models\UtkPayment;
use App\Models\NupRegistration;
use App\Models\UnitSelection;
use App\Models\KprScreening;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class AdminNotificationService
{
    /**
     * Get the developer company ID for the currently authenticated operator.
     * Returns null for Super Admin (meaning they can see all notifications globally).
     */
    private static function getScopedCompanyId(): ?int
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        // Super Admin has global oversight across all developers
        if ($user->isSuperAdmin()) {
            return null;
        }

        // Operator is scoped to their own developer company
        if ($user->isOperator()) {
            return $user->company_id ?? $user->managedCompany?->id;
        }

        return null;
    }

    /**
     * Get list of pending items requiring operator attention / action,
     * strictly scoped to the operator's developer company.
     */
    public static function getPendingNotifications(int $limit = 15): Collection
    {
        $notifications = collect();
        $companyId = self::getScopedCompanyId();

        // 1. KPR Payments (DP, Termin/Cicilan, Pelunasan) yang pending verifikasi
        try {
            $kprQuery = KprPayment::with(['user', 'sprDocument.unitSelection.housingUnit'])
                ->where('status', 'pending');

            if ($companyId) {
                $kprQuery->whereHas('sprDocument.unitSelection.housingUnit', function ($q) use ($companyId) {
                    $q->where('company_id', $companyId)
                      ->orWhereHas('project', fn($pq) => $pq->where('company_id', $companyId));
                });
            }

            $kprPayments = $kprQuery->orderBy('created_at', 'desc')->take($limit)->get();

            foreach ($kprPayments as $p) {
                $typeLabel = match($p->payment_type) {
                    'dp', 'down_payment' => 'Transfer Uang Muka (DP)',
                    'installment'        => 'Transfer Cicilan ke-' . ($p->installment_number ?? 1),
                    'full', 'full_payment' => 'Transfer Pelunasan Unit',
                    default              => 'Transfer Pembayaran Rumah',
                };

                $unitCode = $p->sprDocument?->unitSelection?->housingUnit?->unit_number 
                    ?? $p->sprDocument?->unitSelection?->housingUnit?->unit_code 
                    ?? 'Unit';

                $notifications->push([
                    'id'          => 'kpr_' . $p->id,
                    'is_pending'  => true,
                    'category'    => 'kpr',
                    'badge'       => 'DP / Cicilan',
                    'badge_color' => 'bg-blue-50 text-blue-700 border border-blue-200/80',
                    'title'       => $typeLabel,
                    'user_name'   => $p->user?->name ?? 'Konsumen',
                    'description' => 'Rp ' . number_format($p->amount, 0, ',', '.') . ' &bull; Blok ' . $unitCode,
                    'url'         => route('admin.kpr-payments.show', $p->id),
                    'created_at'  => $p->created_at,
                    'time_human'  => $p->created_at ? $p->created_at->diffForHumans() : 'Baru saja',
                    'icon'        => 'kpr',
                ]);
            }
        } catch (\Throwable $e) {}

        // 2. UTK Payments (Uang Tanda Jadi) yang pending verifikasi
        try {
            $utkQuery = UtkPayment::with(['user', 'unitSelection.housingUnit'])
                ->where('status', 'pending');

            if ($companyId) {
                $utkQuery->whereHas('unitSelection.housingUnit', function ($q) use ($companyId) {
                    $q->where('company_id', $companyId)
                      ->orWhereHas('project', fn($pq) => $pq->where('company_id', $companyId));
                });
            }

            $utkPayments = $utkQuery->orderBy('created_at', 'desc')->take($limit)->get();

            foreach ($utkPayments as $u) {
                $unitCode = $u->unitSelection?->housingUnit?->unit_number 
                    ?? $u->unitSelection?->housingUnit?->unit_code 
                    ?? 'Unit';

                $methodLabel = $u->payment_method === 'cash' ? 'Tunai di Kantor' : 'Transfer Bank';

                $notifications->push([
                    'id'          => 'utk_' . $u->id,
                    'is_pending'  => true,
                    'category'    => 'utk',
                    'badge'       => 'UTK Unit',
                    'badge_color' => 'bg-emerald-50 text-emerald-700 border border-emerald-200/80',
                    'title'       => 'Pembayaran UTK (' . $methodLabel . ')',
                    'user_name'   => $u->user?->name ?? 'Konsumen',
                    'description' => 'Rp ' . number_format($u->amount, 0, ',', '.') . ' &bull; Blok ' . $unitCode,
                    'url'         => route('admin.utk-payments.index'),
                    'created_at'  => $u->created_at,
                    'time_human'  => $u->created_at ? $u->created_at->diffForHumans() : 'Baru saja',
                    'icon'        => 'utk',
                ]);
            }
        } catch (\Throwable $e) {}

        // 3. NUP Cash / Pending Registrations (khusus Super Admin atau NUP di bawah unit developer ini)
        try {
            $nupQuery = NupRegistration::with('user')
                ->where('payment_status', 'pending')
                ->where('payment_method', 'cash');

            if ($companyId) {
                $nupQuery->whereHas('unitSelections.housingUnit', function ($uq) use ($companyId) {
                    $uq->where('company_id', $companyId)
                       ->orWhereHas('project', fn($pq) => $pq->where('company_id', $companyId));
                });
            }

            $nupPayments = $nupQuery->orderBy('created_at', 'desc')->take($limit)->get();

            foreach ($nupPayments as $n) {
                $notifications->push([
                    'id'          => 'nup_' . $n->id,
                    'is_pending'  => true,
                    'category'    => 'nup',
                    'badge'       => 'Biaya NUP',
                    'badge_color' => 'bg-amber-50 text-amber-700 border border-amber-200/80',
                    'title'       => 'Pembayaran NUP (Tunai)',
                    'user_name'   => $n->full_name ?: ($n->user?->name ?? 'Peserta'),
                    'description' => 'Rp ' . number_format($n->nup_fee ?? 0, 0, ',', '.') . ' &bull; ' . ($n->registration_number ?? 'NUP'),
                    'url'         => route('admin.nup.show', $n->id),
                    'created_at'  => $n->created_at,
                    'time_human'  => $n->created_at ? $n->created_at->diffForHumans() : 'Baru saja',
                    'icon'        => 'nup',
                ]);
            }
        } catch (\Throwable $e) {}

        // 4. Unit Selection pending SPR (UTK lunas tapi belum dibuatkan SPR)
        try {
            $sprQuery = UnitSelection::with(['user', 'housingUnit.housingProject', 'nupRegistration', 'utkPayments'])
                ->whereIn('status', ['confirmed', 'selected'])
                ->whereDoesntHave('sprDocuments', function ($q) {
                    $q->whereNotIn('status', ['cancelled']);
                });

            if ($companyId) {
                $sprQuery->whereHas('housingUnit', function ($q) use ($companyId) {
                    $q->where('company_id', $companyId)
                      ->orWhereHas('project', fn($pq) => $pq->where('company_id', $companyId));
                });
            }

            $readySelections = $sprQuery->get()->filter(function ($selection) {
                return $selection->isCash() || $selection->isUtkWaived() || $selection->isUtkPaid();
            });

            foreach ($readySelections as $sel) {
                $unitCode = $sel->housingUnit?->unit_number ?? $sel->housingUnit?->unit_code ?? 'Unit';
                $notifications->push([
                    'id'          => 'spr_' . $sel->id,
                    'is_pending'  => true,
                    'category'    => 'spr',
                    'badge'       => 'Menunggu SPR',
                    'badge_color' => 'bg-purple-50 text-purple-700 border border-purple-200/80',
                    'title'       => 'Pemesanan Unit Baru &bull; Terbitkan SPR',
                    'user_name'   => $sel->user?->name ?? 'Konsumen',
                    'description' => 'UTK Lunas &bull; Blok ' . $unitCode,
                    'url'         => route('admin.spr.create', $sel->id),
                    'created_at'  => $sel->created_at,
                    'time_human'  => $sel->created_at ? $sel->created_at->diffForHumans() : 'Baru saja',
                    'icon'        => 'spr',
                ]);
            }
        } catch (\Throwable $e) {}

        // 5. Dokumen Mitra Pintar yang pending verifikasi
        try {
            $mitraQuery = UnitSelection::with(['user', 'housingUnit.housingProject'])
                ->where('payment_scheme', 'mitra_pintar')
                ->where('mitra_status', 'pending');

            if ($companyId) {
                $mitraQuery->whereHas('housingUnit', function ($q) use ($companyId) {
                    $q->where('company_id', $companyId)
                      ->orWhereHas('project', fn($pq) => $pq->where('company_id', $companyId));
                });
            }

            $mitraSelections = $mitraQuery->orderBy('created_at', 'desc')->take($limit)->get();

            foreach ($mitraSelections as $m) {
                $unitCode = $m->housingUnit?->unit_number 
                    ?? $m->housingUnit?->unit_code 
                    ?? 'Unit';

                $instName = $m->mitra_institution_name ?: 'Mitra Instansi';

                $notifications->push([
                    'id'          => 'mitra_' . $m->id,
                    'is_pending'  => true,
                    'category'    => 'mitra',
                    'badge'       => 'Mitra Pintar',
                    'badge_color' => 'bg-amber-50 text-amber-800 border border-amber-200/80',
                    'title'       => 'Verifikasi Dokumen Mitra: ' . $instName,
                    'user_name'   => $m->user?->name ?? 'Konsumen',
                    'description' => 'SK / Berkas Mitra &bull; Blok ' . $unitCode,
                    'url'         => route('admin.units.index'),
                    'created_at'  => $m->created_at,
                    'time_human'  => $m->created_at ? $m->created_at->diffForHumans() : 'Baru saja',
                    'icon'        => 'mitra',
                ]);
            }
        } catch (\Throwable $e) {}

        // 6. Screening KPR yang pending verifikasi / butuh input skor Pefindo
        try {
            $kprScreenQuery = KprScreening::with(['user', 'nupRegistration'])
                ->whereIn('status', ['pending', 'submitted', 'processing']);

            if ($companyId) {
                $kprScreenQuery->where(function ($sq) use ($companyId) {
                    $sq->whereHas('unitSelection.housingUnit', function ($q) use ($companyId) {
                        $q->where('company_id', $companyId)
                          ->orWhereHas('project', fn($pq) => $pq->where('company_id', $companyId));
                    })->orWhereHas('nupRegistration.housingPreferences', function ($pq) use ($companyId) {
                        $pq->whereHas('housingProject', fn($hpq) => $hpq->where('company_id', $companyId));
                    });
                });
            }

            $screenings = $kprScreenQuery->orderBy('created_at', 'desc')->take($limit)->get();

            foreach ($screenings as $sc) {
                $notifications->push([
                    'id'          => 'sc_' . $sc->id,
                    'is_pending'  => true,
                    'category'    => 'screening',
                    'badge'       => 'Screening KPR',
                    'badge_color' => 'bg-indigo-50 text-indigo-700 border border-indigo-200/80',
                    'title'       => 'Pengajuan Berkas Screening KPR',
                    'user_name'   => $sc->user?->name ?? 'Konsumen',
                    'description' => 'Verifikasi Berkas & Skor Pefindo',
                    'url'         => route('admin.kpr.show', $sc->id),
                    'created_at'  => $sc->created_at,
                    'time_human'  => $sc->created_at ? $sc->created_at->diffForHumans() : 'Baru saja',
                    'icon'        => 'screening',
                ]);
            }
        } catch (\Throwable $e) {}

        return $notifications->sortByDesc('created_at')->values()->take($limit);
    }

    /**
     * Get recent completed transactions, strictly scoped to the operator's developer company.
     */
    public static function getRecentCompletedNotifications(int $limit = 5): Collection
    {
        $recent = collect();
        $companyId = self::getScopedCompanyId();

        // Latest verified KPR payments
        try {
            $kprQuery = KprPayment::with(['user', 'sprDocument.unitSelection.housingUnit'])
                ->where('status', 'success');

            if ($companyId) {
                $kprQuery->whereHas('sprDocument.unitSelection.housingUnit', function ($q) use ($companyId) {
                    $q->where('company_id', $companyId)
                      ->orWhereHas('project', fn($pq) => $pq->where('company_id', $companyId));
                });
            }

            $kpr = $kprQuery->orderBy('updated_at', 'desc')->take($limit)->get();

            foreach ($kpr as $p) {
                $typeLabel = match($p->payment_type) {
                    'dp', 'down_payment' => 'Transfer Uang Muka (DP)',
                    'installment'        => 'Cicilan Termin ke-' . ($p->installment_number ?? 1),
                    'full', 'full_payment' => 'Pelunasan Unit',
                    default              => 'Pembayaran Unit',
                };
                $unitCode = $p->sprDocument?->unitSelection?->housingUnit?->unit_number 
                    ?? $p->sprDocument?->unitSelection?->housingUnit?->unit_code 
                    ?? 'Unit';

                $recent->push([
                    'id'          => 'rec_kpr_' . $p->id,
                    'is_pending'  => false,
                    'category'    => 'kpr',
                    'badge'       => 'Diverifikasi',
                    'badge_color' => 'bg-emerald-50 text-emerald-700 border border-emerald-200/80',
                    'title'       => $typeLabel,
                    'user_name'   => $p->user?->name ?? 'Konsumen',
                    'description' => 'Rp ' . number_format($p->amount, 0, ',', '.') . ' &bull; Blok ' . $unitCode,
                    'url'         => route('admin.kpr-payments.show', $p->id),
                    'created_at'  => $p->updated_at ?? $p->created_at,
                    'time_human'  => ($p->updated_at ?? $p->created_at)->diffForHumans(),
                    'icon'        => 'kpr',
                ]);
            }
        } catch (\Throwable $e) {}

        // Latest confirmed UTK payments
        try {
            $utkQuery = UtkPayment::with(['user', 'unitSelection.housingUnit'])
                ->where('status', 'paid');

            if ($companyId) {
                $utkQuery->whereHas('unitSelection.housingUnit', function ($q) use ($companyId) {
                    $q->where('company_id', $companyId)
                      ->orWhereHas('project', fn($pq) => $pq->where('company_id', $companyId));
                });
            }

            $utk = $utkQuery->orderBy('updated_at', 'desc')->take($limit)->get();

            foreach ($utk as $u) {
                $unitCode = $u->unitSelection?->housingUnit?->unit_number 
                    ?? $u->unitSelection?->housingUnit?->unit_code 
                    ?? 'Unit';

                $recent->push([
                    'id'          => 'rec_utk_' . $u->id,
                    'is_pending'  => false,
                    'category'    => 'utk',
                    'badge'       => 'UTK Lunas',
                    'badge_color' => 'bg-emerald-50 text-emerald-700 border border-emerald-200/80',
                    'title'       => 'Transfer UTK Unit Diterima',
                    'user_name'   => $u->user?->name ?? 'Konsumen',
                    'description' => 'Rp ' . number_format($u->amount, 0, ',', '.') . ' &bull; Blok ' . $unitCode,
                    'url'         => route('admin.utk-payments.index'),
                    'created_at'  => $u->updated_at ?? $u->created_at,
                    'time_human'  => ($u->updated_at ?? $u->created_at)->diffForHumans(),
                    'icon'        => 'utk',
                ]);
            }
        } catch (\Throwable $e) {}

        return $recent->sortByDesc('created_at')->values()->take($limit);
    }

    /**
     * Get total count of pending notifications, strictly scoped to the operator's developer company.
     */
    public static function getPendingCount(): int
    {
        try {
            $companyId = self::getScopedCompanyId();

            $kprQuery = KprPayment::where('status', 'pending');
            if ($companyId) {
                $kprQuery->whereHas('sprDocument.unitSelection.housingUnit', function ($q) use ($companyId) {
                    $q->where('company_id', $companyId)
                      ->orWhereHas('project', fn($pq) => $pq->where('company_id', $companyId));
                });
            }
            $kpr = $kprQuery->count();

            $utkQuery = UtkPayment::where('status', 'pending');
            if ($companyId) {
                $utkQuery->whereHas('unitSelection.housingUnit', function ($q) use ($companyId) {
                    $q->where('company_id', $companyId)
                      ->orWhereHas('project', fn($pq) => $pq->where('company_id', $companyId));
                });
            }
            $utk = $utkQuery->count();

            $nupQuery = NupRegistration::where('payment_status', 'pending')->where('payment_method', 'cash');
            if ($companyId) {
                $nupQuery->whereHas('unitSelections.housingUnit', function ($uq) use ($companyId) {
                    $uq->where('company_id', $companyId)
                       ->orWhereHas('project', fn($pq) => $pq->where('company_id', $companyId));
                });
            }
            $nup = $nupQuery->count();
            
            $sprQuery = UnitSelection::with(['utkPayments'])
                ->whereIn('status', ['confirmed', 'selected'])
                ->whereDoesntHave('sprDocuments', function ($q) {
                    $q->whereNotIn('status', ['cancelled']);
                });
            if ($companyId) {
                $sprQuery->whereHas('housingUnit', function ($q) use ($companyId) {
                    $q->where('company_id', $companyId)
                      ->orWhereHas('project', fn($pq) => $pq->where('company_id', $companyId));
                });
            }
            $spr = $sprQuery->get()->filter(function ($selection) {
                return $selection->isCash() || $selection->isUtkWaived() || $selection->isUtkPaid();
            })->count();

            // 5. Mitra Pintar pending documents
            $mitraQuery = UnitSelection::where('payment_scheme', 'mitra_pintar')
                ->where('mitra_status', 'pending');
            if ($companyId) {
                $mitraQuery->whereHas('housingUnit', function ($q) use ($companyId) {
                    $q->where('company_id', $companyId)
                      ->orWhereHas('project', fn($pq) => $pq->where('company_id', $companyId));
                });
            }
            $mitra = $mitraQuery->count();

            // 6. KPR Screening pending submissions
            $screenQuery = KprScreening::whereIn('status', ['pending', 'submitted', 'processing']);
            if ($companyId) {
                $screenQuery->where(function ($sq) use ($companyId) {
                    $sq->whereHas('unitSelection.housingUnit', function ($q) use ($companyId) {
                        $q->where('company_id', $companyId)
                          ->orWhereHas('project', fn($pq) => $pq->where('company_id', $companyId));
                    })->orWhereHas('nupRegistration.housingPreferences', function ($pq) use ($companyId) {
                        $pq->whereHas('housingProject', fn($hpq) => $hpq->where('company_id', $companyId));
                    });
                });
            }
            $screening = $screenQuery->count();

            return $kpr + $utk + $nup + $spr + $mitra + $screening;
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
