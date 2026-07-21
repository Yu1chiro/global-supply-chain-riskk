<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class PgsqlDataSeeder extends Seeder
{
    public function run()
    {
        $path = database_path('data/pg_seed.sql');

        if (!File::exists($path)) {
            $this->command->error("File SQL tidak ditemukan di: {$path}");
            return;
        }

        $sql = File::get($path);

        $this->command->info('Memulai injeksi data mentah ke PostgreSQL...');
        
        // Memproses logic secara langsung pada server
        DB::unprepared($sql);

        $this->command->info('Injeksi data selesai & Sequences PostgreSQL berhasil direset.');
    }
}