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
        $facilityClientCode = config('app.client_code', env('CLIENT_CODE', '122377'));

        // Detailed Comment: Seed or update default secretary account with documented credentials and guaranteed non-null secrefno
        $secretary = SecretaryModel::where('username', 'secretary')
            ->orWhere('secemail', 'secretary.dummy@gmail.com')
            ->first();

        $defaultSecRef = '051519900001TASK';

        if (!$secretary) {
            SecretaryModel::create([
                'secrefno' => $defaultSecRef,
                'username' => 'secretary',
                'secpassword' => Hash::make('12345'),
                'secfname' => 'Jane',
                'seclname' => 'Doe',
                'secidno' => '2026001',
                'secbday' => '1990-05-15',
                'secgender' => 'FEMALE',
                'secadrs' => '123 Main Street',
                'seccontactno' => '09123456789',
                'secemail' => 'secretary.dummy@gmail.com',
                'clientcode' => $facilityClientCode,
                'recordeddate' => now(),
                'verifieddate' => now(),
                'verified' => true
            ]);
        } else {
            $updateData = [
                'username' => 'secretary',
                'secpassword' => Hash::make('12345'),
                'clientcode' => $facilityClientCode,
                'verified' => true
            ];
            if (empty($secretary->secrefno)) {
                $updateData['secrefno'] = $defaultSecRef;
            }
            $secretary->update($updateData);
        }

        // Repair any secretary records with empty secrefno
        $legacySecs = SecretaryModel::whereNull('secrefno')->orWhere('secrefno', '')->get();
        foreach ($legacySecs as $idx => $legacySec) {
            $legacySec->update([
                'secrefno' => now()->format('mdYHis') . ($idx + 1) . 'TASK'
            ]);
        }

        // Detailed Comment: Seed or update default doctor account with documented credentials
        $doctor = DoctorModel::where('username', 'doctor')
            ->orWhere('eadd', 'doctor.dummy@gmail.com')
            ->first();

        if (!$doctor) {
            $doctor = DoctorModel::create([
                'dw_clientcode' => $facilityClientCode,
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
        } else {
            $doctor->update([
                'pass' => Hash::make('12345'),
                'dw_clientcode' => $facilityClientCode,
                'status' => 'ACTIVE'
            ]);
        }

        // Detailed Comment: Repair any existing legacy doctor records with empty docrefno
        $legacyDocs = DoctorModel::whereNull('docrefno')->orWhere('docrefno', '')->get();
        foreach ($legacyDocs as $legacyDoc) {
            $newRef = now()->format('mdYHis') . 'MD';
            $legacyDoc->update([
                'docrefno' => $newRef,
                'pass' => Hash::make('12345'),
                'dw_clientcode' => $facilityClientCode,
            ]);

            DoctorsProfileModel::firstOrCreate(
                ['docrefno' => $newRef],
                [
                    'doclname' => $legacyDoc->doclname ?: 'Cruz',
                    'docfname' => $legacyDoc->docfname ?: 'Juan',
                    'docname' => strtoupper(($legacyDoc->doclname ?: 'Cruz') . ', ' . ($legacyDoc->docfname ?: 'Juan')),
                    'adrs' => $legacyDoc->address ?: '123 Main Street',
                    'emailadd' => $legacyDoc->eadd ?: 'doctor@gmail.com',
                    'status' => true,
                ]
            );
        }
    }
}
