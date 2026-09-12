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
        AdminModel::create([
            'adminrefno' => Date::now()->format('mdYHis') . 'ADMN',
            'username' => 'admin',
            'password' => Hash::make('admin123')
        ]);
    }
}
