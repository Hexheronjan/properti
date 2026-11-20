<?php

namespace Database\Seeders;

use App\Models\HousingUnit;
use Illuminate\Database\Seeder;

class HousingUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $blocks = ['A', 'B', 'C', 'D'];
        $types = [
            'type_36' => ['price' => 350000000, 'building_area' => 36, 'land_area' => 60, 'bedrooms' => 2, 'bathrooms' => 1],
            'type_45' => ['price' => 450000000, 'building_area' => 45, 'land_area' => 72, 'bedrooms' => 2, 'bathrooms' => 1],
            'type_54' => ['price' => 550000000, 'building_area' => 54, 'land_area' => 84, 'bedrooms' => 3, 'bathrooms' => 2],
            'type_70' => ['price' => 700000000, 'building_area' => 70, 'land_area' => 96, 'bedrooms' => 3, 'bathrooms' => 2],
            'type_120' => ['price' => 1200000000, 'building_area' => 120, 'land_area' => 144, 'bedrooms' => 4, 'bathrooms' => 3],
        ];

        $unitCounter = 1;

        foreach ($blocks as $block) {
            foreach ($types as $type => $specs) {
                // Create 5-10 units per type per block
                $unitsPerType = rand(5, 10);

                for ($i = 1; $i <= $unitsPerType; $i++) {
                    $unitCode = $block . str_pad($i, 3, '0', STR_PAD_LEFT);

                    // Check if unit already exists
                    HousingUnit::firstOrCreate(
                        ['unit_code' => $unitCode],
                        [
                            'block' => $block,
                            'number' => str_pad($i, 3, '0', STR_PAD_LEFT),
                            'type' => $type,
                            'price' => $specs['price'],
                            'building_area' => $specs['building_area'],
                            'land_area' => $specs['land_area'],
                            'bedrooms' => $specs['bedrooms'],
                            'bathrooms' => $specs['bathrooms'],
                            'description' => "Rumah tipe {$type} di Blok {$block} No. " . str_pad($i, 3, '0', STR_PAD_LEFT) . ". Lokasi strategis dengan akses mudah ke fasilitas umum.",
                            'status' => 'available',
                        ]
                    );
                }
            }
        }

        $totalUnits = HousingUnit::count();
        $this->command->info('Housing units seeded successfully: ' . $totalUnits . ' units');
    }
}