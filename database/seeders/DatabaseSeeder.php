<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Hanya panggil seeder admin/sistem utama (tanpa data dummy proyek/unit)
        $this->call([
            AdminSeeder::class,
        ]);
    }
}
