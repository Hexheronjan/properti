<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Broker;

class BrokerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brokers = [
            [
                'name' => 'PT. Properti Jaya',
                'office_name' => 'Cabang Gresik',
                'contact' => '081234567890',
                'address' => 'Jl. Raya Gresik No. 123',
                'description' => 'Broker properti terpercaya dengan pengalaman lebih dari 10 tahun.',
                'is_active' => true,
            ],
            [
                'name' => 'PT. Rumah Impian',
                'office_name' => 'Cabang Kebomas',
                'contact' => '081234567891',
                'address' => 'Jl. Kebomas No. 456',
                'description' => 'Spesialis rumah subsidi dan KPR.',
                'is_active' => true,
            ],
            [
                'name' => 'PT. Griya Sejahtera',
                'office_name' => 'Cabang Cerme',
                'contact' => '081234567892',
                'address' => 'Jl. Cerme No. 789',
                'description' => 'Broker dengan jaringan luas di area Gresik.',
                'is_active' => true,
            ],
        ];

        foreach ($brokers as $broker) {
            Broker::create($broker);
        }
    }
}
