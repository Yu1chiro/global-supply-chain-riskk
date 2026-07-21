<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

// Kelas DatabaseSeeder: database seeder
class DatabaseSeeder extends Seeder
{
    // run
    public function run()
    {
        $this->call([
            UserSeeder::class,
            CountrySeeder::class,
            PortSeeder::class,
            SentimentWordSeeder::class,
        ]);
    }
}