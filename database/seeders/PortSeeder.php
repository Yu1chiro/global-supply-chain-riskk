<?php

namespace Database\Seeders;

use App\Models\Port;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

// Kelas PortSeeder: port seeder
class PortSeeder extends Seeder
{
    
    // run
    public function run()
    {
        $jsonPath = database_path('data/world_port_index.json');

        if (!file_exists($jsonPath)) {
            Log::warning('World Port Index dataset not found at ' . $jsonPath . '. Falling back to sample ports.');
            $this->seedFallbackPorts();
            return;
        }

        $ports = json_decode(file_get_contents($jsonPath), true);

        if (!is_array($ports) || empty($ports)) {
            Log::warning('World Port Index dataset is empty or invalid. Falling back to sample ports.');
            $this->seedFallbackPorts();
            return;
        }

        $count = 0;
        foreach ($ports as $port) {
            
            if (!empty($port['code'])) {
                Port::updateOrCreate(
                    ['code' => $port['code']],
                    $port
                );
            } else {
                Port::create($port);
            }
            $count++;
        }

        $this->command?->info("Seeded {$count} ports from World Port Index (NGA Pub. 150, 2019).");
    }

    
    // seed fallback ports
    private function seedFallbackPorts(): void
    {
        $ports = [
            ['name' => 'Port of Singapore', 'country' => 'Singapore', 'code' => 'SGSIN', 'latitude' => 1.264, 'longitude' => 103.835, 'type' => 'Sea', 'status' => 'active'],
            ['name' => 'Port of Shanghai', 'country' => 'China', 'code' => 'CNSHA', 'latitude' => 31.2304, 'longitude' => 121.4737, 'type' => 'Sea', 'status' => 'active'],
            ['name' => 'Port of Rotterdam', 'country' => 'Netherlands', 'code' => 'NLRTM', 'latitude' => 51.9225, 'longitude' => 4.4790, 'type' => 'Sea', 'status' => 'active'],
            ['name' => 'Port of Los Angeles', 'country' => 'United States', 'code' => 'USLAX', 'latitude' => 33.7415, 'longitude' => -118.2435, 'type' => 'Sea', 'status' => 'active'],
            ['name' => 'Port of Hamburg', 'country' => 'Germany', 'code' => 'DEHAM', 'latitude' => 53.5511, 'longitude' => 9.9937, 'type' => 'Sea', 'status' => 'active'],
            ['name' => 'Port of Jakarta', 'country' => 'Indonesia', 'code' => 'IDJKT', 'latitude' => -6.1275, 'longitude' => 106.6537, 'type' => 'Sea', 'status' => 'active'],
            ['name' => 'Port of Sydney', 'country' => 'Australia', 'code' => 'AUSYD', 'latitude' => -33.8688, 'longitude' => 151.2093, 'type' => 'Sea', 'status' => 'active'],
            ['name' => 'Port of Tokyo', 'country' => 'Japan', 'code' => 'JPTYO', 'latitude' => 35.6762, 'longitude' => 139.6503, 'type' => 'Sea', 'status' => 'active'],
            ['name' => 'Port of Santos', 'country' => 'Brazil', 'code' => 'BRSSZ', 'latitude' => -23.9608, 'longitude' => -46.3300, 'type' => 'Sea', 'status' => 'active'],
            ['name' => 'Port of Mumbai', 'country' => 'India', 'code' => 'INBOM', 'latitude' => 18.9220, 'longitude' => 72.8347, 'type' => 'Sea', 'status' => 'active'],
        ];

        foreach ($ports as $port) {
            Port::updateOrCreate(
                ['code' => $port['code']],
                $port
            );
        }
    }
}
