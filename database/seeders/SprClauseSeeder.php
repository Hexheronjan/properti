<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HousingProject;
use App\Models\SprClause;

class SprClauseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = HousingProject::all();

        $defaultClauses = [
            [
                'clause_number' => 'Pasal 1',
                'clause_title' => 'Ketentuan Pemesanan Unit',
                'clause_content' => 'Pemesanan unit rumah dinyatakan sah apabila pemesan telah membayar Uang Tanda Komitmen (UTK) sesuai nilai yang ditetapkan developer dan menyetujui seluruh klausul SPR ini.',
                'order' => 1
            ],
            [
                'clause_number' => 'Pasal 2',
                'clause_title' => 'Batas Waktu Pembayaran Komitmen',
                'clause_content' => 'Batas waktu maksimal untuk pelunasan Uang Tanda Komitmen (UTK) adalah 48 jam (2x24 jam) terhitung sejak persetujuan klausul ini. Keterlambatan pembayaran akan membatalkan pemesanan secara otomatis.',
                'order' => 2
            ],
            [
                'clause_number' => 'Pasal 3',
                'clause_title' => 'Ketentuan Pembatalan & Refund NUP',
                'clause_content' => 'Pemesan menyatakan setuju bahwa uang pendaftaran Nomor Urut Pemesanan (NUP) bersifat non-refundable (tidak dapat dikembalikan) jika pemesan membatalkan transaksi secara sepihak setelah memilih unit.',
                'order' => 3
            ],
            [
                'clause_number' => 'Pasal 4',
                'clause_title' => 'Penerbitan SPR & E-Materai',
                'clause_content' => 'Dokumen Surat Pemesanan Rumah (SPR) yang sah akan diterbitkan secara otomatis setelah pembayaran UTK divalidasi dan akan ditandatangani secara digital terintegrasi E-Materai.',
                'order' => 4
            ]
        ];

        foreach ($projects as $project) {
            foreach ($defaultClauses as $clause) {
                SprClause::create([
                    'housing_project_id' => $project->id,
                    'clause_number' => $clause['clause_number'],
                    'clause_title' => $clause['clause_title'],
                    'clause_content' => $clause['clause_content'],
                    'is_active' => true,
                    'order' => $clause['order'],
                ]);
            }
        }
    }
}
