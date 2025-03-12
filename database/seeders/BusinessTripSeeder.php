<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class BusinessTripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $faker = Faker::create();

        // Loop to create 2000 entries
        for ($i = 0; $i < 2000; $i++) {
            DB::table('bt_transaction')->insert([
                'id' => Str::uuid(),
                'user_id' => 23893,
                'jns_dinas' => $faker->randomElement(['luar kota', 'dalam kota']),
                'nama' => $faker->name,
                'no_sppd' => $faker->unique()->numerify('SPPD####'),
                'divisi' => $faker->word,
                'mulai' => $faker->dateTimeThisYear()->format('Y-m-d H:i:s'),
                'kembali' => $faker->dateTimeThisYear()->format('Y-m-d H:i:s'),
                'tujuan' => $faker->city,
                'keperluan' => $faker->sentence,
                'bb_perusahaan' => $faker->company,
                'norek_krywn' => $faker->numerify('#######'),
                'nama_pemilik_rek' => $faker->name,
                'nama_bank' => $faker->company,
                'ca' => $faker->word,
                'tiket' => $faker->numberBetween(1000, 5000),
                'hotel' => $faker->numberBetween(1000, 5000),
                'mess' => $faker->numberBetween(1000, 5000),
                'taksi' => $faker->numberBetween(1000, 5000),
                'status' => $faker->randomElement(['Pending L1']),
                'manager_l1_id' => $faker->numberBetween(1, 100),
                'manager_l2_id' => $faker->numberBetween(1, 100),
                'deleted_at' => null,
                'update_db' => 'Y',
            ]);
        }
    }
}
