<?php

namespace Database\Seeders;

use App\Models\DoctorsProfileModel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\DoctorGroupModel;
use App\Models\DoctorModel;
use App\Models\SecretaryModel;
use App\Models\User;
use Date;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Dummy secretary user
        SecretaryModel::create([
            'secpassword' => Hash::make('12345'),
            'secfname' => 'Jane',
            'seclname' => 'Doe',
            'secbday' => '1990-05-15',
            'secgender' => 'FEMALE',
            'secadrs' => '123 Main Street',
            'seccontactno' => '09123456789',
            'secemail' => 'secretary.dummy@gmail.com',
            'recordeddate' => now(),
            'verifieddate' => now(),
            'verified' => true
        ]);

        // Dummy doctor user
        $doctor = DoctorModel::create([
            'doclname' => 'Cruz',
            'docfname' => 'Juan',
            'docmname' => 'D.',
            'username' => 'doctor',
            'pass' => Hash::make('12345'),
            'eadd' => 'doctor.dummy@gmail.com',
            'mnumber' => '09123456789',
            'address' => '123 Main Street',
            'updated' => now(),
            'status' => 'ACTIVE'
        ]);

        DoctorsProfileModel::create([
            'docrefno' => $doctor->docrefno,
            'doclname' => $doctor->doclname,
            'docfname' => $doctor->docfname,
            'docname' => strtoupper($doctor->doclname . ', ' . $doctor->docfname),
            'adrs' => $doctor->address,
            'emailadd' => $doctor->eadd,
            'status' => true,
        ]);
    }
}
