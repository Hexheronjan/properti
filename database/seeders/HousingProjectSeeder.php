<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HousingProject;

class HousingProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = [
            [
                'name' => 'Sinar Mulia Residence',
                'location' => 'Kedanyang, Kebomas',
                'description' => 'Perumahan subsidi dengan tipe 36 dan 45, fasilitas lengkap dan lokasi strategis.',
                'is_active' => true,
            ],
            [
                'name' => 'Banjar Lagoon Village',
                'location' => 'Banjarsari, Cerme',
                'description' => 'Perumahan modern dengan konsep lagoon, tipe 45 dan 54, dekat dengan akses tol.',
                'is_active' => true,
            ],
            [
                'name' => 'Gresik Harmoni Residence',
                'location' => 'Menganti, Gresik',
                'description' => 'Perumahan eksklusif dengan tipe 54 dan 70, dekat dengan pusat kota.',
                'is_active' => true,
            ],
        ];

        foreach ($projects as $project) {
            HousingProject::create($project);
        }
    }
}
