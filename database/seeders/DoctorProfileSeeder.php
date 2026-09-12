<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\DoctorsProfileModel;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class DoctorProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $faker = Faker::create();

        // // Seed 10 doctors (you can change the number)
        // for ($i = 0; $i < 1; $i++) {
        //     DoctorsProfileModel::create([
        //         'docrefno' => $faker->unique()->numerify('DOC###'),
        //         'docfname' => $faker->firstName,
        //         'docmname' => $faker->firstName,
        //         'doclname' => $faker->lastName,
        //         'suffix' => $faker->randomElement(['Jr.', 'Sr.', 'III', null]),
        //         'titlename' => $faker->title,
        //         'docname' => $faker->name,
        //         'adrs' => $faker->address,
        //         'emailadd' => $faker->unique()->safeEmail,
        //         'S2n0' => $faker->randomNumber(6),
        //         'PTR' => $faker->randomNumber(6),
        //         'Licno' => $faker->randomNumber(6),
        //         'phicno' => $faker->randomNumber(10),
        //         'tin' => $faker->randomNumber(9),
        //         'phicenable' => $faker->boolean,
        //         'phicrate' => $faker->randomFloat(2, 0, 100),
        //         'pfrate' => $faker->randomFloat(2, 0, 100),
        //         'lastupdate' => now(),
        //         'proftype' => $faker->word,
        //         'disabletext' => null,
        //         'cellno' => $faker->phoneNumber,
        //         'catg' => $faker->word,
        //         'recid' => $faker->randomNumber(5),
        //         'recby' => $faker->name,
        //         'station' => $faker->city,
        //         'groupname' => $faker->word,
        //         'coaOPD' => $faker->randomNumber(6),
        //         'coaIPD' => $faker->randomNumber(6),
        //         'accountno' => $faker->bankAccountNumber,
        //         'tax' => $faker->randomFloat(2, 0, 100),
        //         'issuehosOR' => $faker->word,
        //         'expertise' => $faker->jobTitle,
        //         'clinichours' => $faker->time('H:i') . '-' . $faker->time('H:i'),
        //         'quevisible' => $faker->boolean,
        //         'profgroup' => $faker->word,
        //         'autoAddVAT' => $faker->boolean,
        //         'VAT' => $faker->randomFloat(2, 0, 20),
        //         'allowtextresult' => $faker->boolean,
        //         'allowdocsystem' => $faker->boolean,
        //         'phicexpiry' => $faker->date(),
        //         'licnoexpiry' => $faker->date(),
        //         'status' => $faker->randomElement(['Active', 'Inactive']),
        //         'statusreason' => $faker->sentence,
        //         'vatable' => $faker->boolean,
        //         'rodrate' => $faker->randomFloat(2, 0, 1000),
        //         'phicname' => $faker->company,
        //         'department' => $faker->word,
        //         'docfirst' => $faker->firstName
        //     ]);
        // }
    }
}
