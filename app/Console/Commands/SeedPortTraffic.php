<?php

namespace App\Console\Commands;

use App\Models\Port;
use App\Models\PortTrafficLog;
use Illuminate\Console\Command;

// Kelas SeedPortTraffic: seed port traffic
class SeedPortTraffic extends Command
{
    protected $signature = 'ports:seed-traffic {--fresh : Hapus semua log lama sebelum membuat yang baru}';

    protected $description = 'Isi data dummy kemacetan (congestion) untuk semua pelabuhan yang belum punya log';

    
    
    private array $levels = [
        'low' => 55,
        'moderate' => 30,
        'high' => 12,
        'severe' => 3,
    ];

    // handle
    public function handle()
    {
        if ($this->option('fresh')) {
            $this->info('Menghapus semua port_traffic_logs lama...');
            PortTrafficLog::truncate();
        }

        $ports = Port::doesntHave('trafficLogs')->get();

        if ($ports->isEmpty()) {
            $this->info('Semua pelabuhan sudah punya data traffic log. Tidak ada yang perlu diisi (pakai --fresh kalau mau isi ulang semua).');
            return self::SUCCESS;
        }

        $this->info("Mengisi data dummy untuk {$ports->count()} pelabuhan...");
        $bar = $this->output->createProgressBar($ports->count());

        foreach ($ports as $port) {
            $level = $this->randomLevel();

            PortTrafficLog::create([
                'port_id' => $port->id,
                'congestion_level' => $level,
                'vessels_waiting' => $this->vesselsFor($level),
                'average_delay_hours' => $this->delayFor($level),
                'notes' => 'Data dummy (auto-generated) — belum tersambung ke sumber data real-time.',
                'recorded_at' => now(),
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Selesai. Kolom "Kemacetan Terakhir" sekarang akan terisi untuk pelabuhan-pelabuhan tersebut.');

        return self::SUCCESS;
    }

    // random level
    private function randomLevel(): string
    {
        $roll = rand(1, 100);
        $cumulative = 0;

        foreach ($this->levels as $level => $weight) {
            $cumulative += $weight;
            if ($roll <= $cumulative) {
                return $level;
            }
        }

        return 'low';
    }

    // vessels for
    private function vesselsFor(string $level): int
    {
        return match ($level) {
            'low' => rand(0, 3),
            'moderate' => rand(3, 8),
            'high' => rand(8, 15),
            'severe' => rand(15, 30),
            default => 0,
        };
    }

    // delay for
    private function delayFor(string $level): int
    {
        return match ($level) {
            'low' => rand(0, 4),
            'moderate' => rand(4, 12),
            'high' => rand(12, 24),
            'severe' => rand(24, 72),
            default => 0,
        };
    }
}
