<?php

namespace Database\Seeders;

use App\Models\AdminModel;
use Date;
use Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Detailed Comment: Check if the default 'admin' account already exists
        // before creating a new record. This ensures the seeder remains idempotent
        // and does not generate duplicate admin accounts upon multiple runs.
        if (!AdminModel::where('username', 'admin')->exists()) {
            AdminModel::create([
                'adminrefno' => Date::now()->format('mdYHis') . 'ADMN',
                'username' => 'admin',
                'password' => Hash::make('admin123')
            ]);
        }
    }
}
