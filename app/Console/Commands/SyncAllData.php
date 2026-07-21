<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Services\GeographyService;
use App\Services\RestCountriesService;
use App\Services\RiskScoringService;
use App\Services\WeatherService;
use App\Services\WorldBankService;
use Illuminate\Console\Command;

// Kelas SyncAllData: sync all data
class SyncAllData extends Command
{
    protected $signature = 'data:sync-all {--only-missing : Cuma sync negara yang datanya masih kosong (GDP null), skip yang sudah lengkap. Cocok dipakai kalau proses sebelumnya terputus di tengah jalan (mis. internet putus) supaya tidak perlu ulang dari awal.}';

    protected $description = 'Sync data World Bank, REST Countries, cuaca, dan hitung risk score untuk semua negara';

    // handle
    public function handle(
        WorldBankService $worldBankService,
        RestCountriesService $restCountriesService,
        WeatherService $weatherService,
        GeographyService $geographyService,
        RiskScoringService $riskScoringService
    ) {
        $query = Country::query();

        if ($this->option('only-missing')) {
            
            
            
            
            $query->whereNull('gdp');
        }

        $countries = $query->get();

        if ($countries->isEmpty()) {
            if ($this->option('only-missing')) {
                $this->info('Semua negara sudah punya data GDP. Tidak ada yang perlu di-sync.');
                return self::SUCCESS;
            }
            $this->error('Tabel countries masih kosong. Jalankan seeder dulu: php artisan db:seed --class=CountrySeeder');
            return self::FAILURE;
        }

        $this->info("Menyinkronkan data untuk {$countries->count()} negara...");
        $bar = $this->output->createProgressBar($countries->count());
        $bar->start();

        foreach ($countries as $country) {
            
            if ($country->latitude && $country->longitude) {
                $weather = $weatherService->getWeather((float) $country->latitude, (float) $country->longitude);
                if ($weather) {
                    $country->weather_data = $weather;
                }
            }

            
            if (!$country->gdp) {
                $gdp = $worldBankService->getGDP($country->code);
                if ($gdp) $country->gdp = $gdp;
            }
            if (!$country->inflation) {
                $inflation = $worldBankService->getInflation($country->code);
                if ($inflation) $country->inflation = $inflation;
            }
            if (!$country->population) {
                $population = $worldBankService->getPopulation($country->code);
                if ($population) $country->population = $population;
            }
            if (!$country->exports) {
                $exports = $worldBankService->getExports($country->code);
                if ($exports) $country->exports = $exports;
            }
            if (!$country->imports) {
                $imports = $worldBankService->getImports($country->code);
                if ($imports) $country->imports = $imports;
            }
            if (!$country->reserves) {
                $reserves = $worldBankService->getReserves($country->code);
                if ($reserves) $country->reserves = $reserves;
            }

            
            $info = $restCountriesService->getCountryInfo($country->code);
            if ($info) {
                $country->region = $info['region'] ?? $country->region;
                $country->subregion = $info['subregion'] ?? $country->subregion;
                $country->languages = $info['languages'] ?? $country->languages;
                $country->capital = $info['capital'] ?? $country->capital;
                $country->flag_url = $info['flag_url'] ?? $country->flag_url;
            }

            
            
            $geographyService->getSummary($country->name);

            $country->external_synced_at = now();
            $country->save();

            
            $riskScoringService->saveRiskScore($country);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Selesai! Silakan refresh halaman /dashboard.');

        return self::SUCCESS;
    }
}