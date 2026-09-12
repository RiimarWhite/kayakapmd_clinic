<?php

namespace App\Console\Commands;

use App\Models\DoctorModel;
use App\Models\SecretaryModel;
use Hash;
use Illuminate\Console\Command;

class MigratePasswords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-passwords {--type= : Specify user type (doctor or secretary)} {--refno= : Specify user reference number} {--password= : Override password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hash old passwords';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('type');
        $refno = $this->option('refno');
        $password = $this->option('password');

        if ($type === "doctor") {
            $user = DoctorModel::where('docrefno', $refno)->first();
        } else if ($type === "secretary") {
            $user = SecretaryModel::where('secrefno', $refno)->first();
        }

        if (!$user) {
            $this->error("No user with this reference number found.");
            return;
        }

        $hashedPassword = Hash::make($password);

        if ($type === "doctor") {
            DoctorModel::where([
                'docrefno' => $refno
            ])->update(['pass' => $hashedPassword]);
        } else if ($type === "secretary") {
            SecretaryModel::where([
                'secrefno' => $refno
            ])->update([
                'secpassword' => $hashedPassword
            ]);
        }

        $this->info('User ' . $refno . ' successfully migrated.');
    }
}
