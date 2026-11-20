<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin
        User::firstOrCreate(
            ['email' => 'super@admin.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'phone' => '6281234567890',
                'email_verified_at' => now(),
            ]
        );

        // Create Admin Marketing (Operator)
        User::firstOrCreate(
            ['email' => 'marketing@admin.com'],
            [
                'name' => 'Admin Marketing',
                'password' => Hash::make('password'),
                'role' => 'operator',
                'phone' => '6281234567891',
                'email_verified_at' => now(),
            ]
        );

        // Create Admin Keuangan (Operator)
        User::firstOrCreate(
            ['email' => 'keuangan@admin.com'],
            [
                'name' => 'Admin Keuangan',
                'password' => Hash::make('password'),
                'role' => 'operator',
                'phone' => '6281234567892',
                'email_verified_at' => now(),
            ]
        );

        // Create Admin Screening (Pihak Bank)
        User::firstOrCreate(
            ['email' => 'screening@admin.com'],
            [
                'name' => 'Admin Screening',
                'password' => Hash::make('password'),
                'role' => 'pihak_bank',
                'phone' => '6281234567893',
                'email_verified_at' => now(),
            ]
        );

        // Create Member
        User::firstOrCreate(
            ['email' => 'member@gmail.com'],
            [
                'name' => 'Budi Member',
                'password' => Hash::make('password'),
                'role' => 'member',
                'phone' => '6281234567894',
                'email_verified_at' => now(),
            ]
        );

        // Create Pihak Bank
        User::firstOrCreate(
            ['email' => 'bank@admin.com'],
            [
                'name' => 'Pihak Bank Mandiri',
                'password' => Hash::make('password'),
                'role' => 'pihak_bank',
                'phone' => '6281234567895',
                'email_verified_at' => now(),
            ]
        );

        // Create Operator
        User::firstOrCreate(
            ['email' => 'operator@admin.com'],
            [
                'name' => 'Operator Perumahan',
                'password' => Hash::make('password'),
                'role' => 'operator',
                'phone' => '6281234567896',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Admin and new role users created successfully.');
        $this->command->info('Login credentials (password: password):');
        $this->command->info('- Super Admin: super@admin.com (Role: super_admin)');
        $this->command->info('- Admin Marketing: marketing@admin.com (Role: operator)');
        $this->command->info('- Admin Keuangan: keuangan@admin.com (Role: operator)');
        $this->command->info('- Admin Screening: screening@admin.com (Role: pihak_bank)');
        $this->command->info('- Member: member@gmail.com (Role: member)');
        $this->command->info('- Pihak Bank: bank@admin.com (Role: pihak_bank)');
        $this->command->info('- Operator: operator@admin.com (Role: operator)');
    }
}