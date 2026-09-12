<?php

namespace Database\Seeders;

use App\Models\KayakapProfileModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        KayakapProfileModel::create([
            'company_name' => 'DrainWiz'
        ]);
    }
}
