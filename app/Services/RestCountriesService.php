<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// Kelas RestCountriesService: rest countries service
class RestCountriesService
{
    protected string $baseUrl = 'https://restcountries.com/v3.1';

    
    // get country info
    public function getCountryInfo(string $countryCode): ?array
    {
        $countryCode = strtoupper($countryCode);
        $cacheKey = "restcountries_{$countryCode}";

        $result = Cache::remember($cacheKey, now()->addDays(7), function () use ($countryCode) {
            
            
            
            
            
            for ($attempt = 1; $attempt <= 3; $attempt++) {
                try {
                    $response = Http::timeout(10)->get("{$this->baseUrl}/alpha/{$countryCode}", [
                        'fields' => 'name,cca2,capital,region,subregion,languages,currencies,flags,population,latlng',
                    ]);

                    if ($response->successful()) {
                        $data = $response->json();

                        
                        if (isset($data[0])) {
                            $data = $data[0];
                        }

                        return $this->mapCountryData($data);
                    }

                    Log::warning("RestCountries API: gagal ambil data untuk {$countryCode} (status " . $response->status() . ", percobaan {$attempt}/3)");
                } catch (\Exception $e) {
                    Log::error("RestCountries API Error ({$countryCode}, percobaan {$attempt}/3): " . $e->getMessage());
                }

                if ($attempt < 3) {
                    usleep(300000); 
                }
            }

            return null;
        });

        
        
        
        
        
        
        $fallback = self::OFFLINE_FALLBACK[$countryCode] ?? null;
        if ($fallback) {
            if (!$result) {
                $result = [
                    'name' => null, 'code' => $countryCode, 'capital' => null,
                    'region' => null, 'subregion' => null, 'languages' => [],
                    'currency_code' => null, 'currency_name' => null, 'currency_symbol' => null,
                    'flag_url' => null, 'population' => null, 'latitude' => null, 'longitude' => null,
                ];
            }
            if (empty($result['region'])) $result['region'] = $fallback['region'];
            if (empty($result['subregion'])) $result['subregion'] = $fallback['subregion'];
            if (empty($result['languages'])) $result['languages'] = $fallback['languages'];
        }

        return $result;
    }

    
    protected const OFFLINE_FALLBACK = [
        'AD' => ['region' => 'Europe', 'subregion' => 'Southern Europe', 'languages' => ['Catalan']],
        'AE' => ['region' => 'Asia', 'subregion' => 'Western Asia', 'languages' => ['Arabic']],
        'AF' => ['region' => 'Asia', 'subregion' => 'Southern Asia', 'languages' => ['Pashto', 'Dari']],
        'AG' => ['region' => 'Americas', 'subregion' => 'Caribbean', 'languages' => ['English']],
        'AL' => ['region' => 'Europe', 'subregion' => 'Southern Europe', 'languages' => ['Albanian']],
        'AM' => ['region' => 'Asia', 'subregion' => 'Western Asia', 'languages' => ['Armenian']],
        'AO' => ['region' => 'Africa', 'subregion' => 'Middle Africa', 'languages' => ['Portuguese']],
        'AR' => ['region' => 'Americas', 'subregion' => 'South America', 'languages' => ['Spanish']],
        'AT' => ['region' => 'Europe', 'subregion' => 'Western Europe', 'languages' => ['German']],
        'AU' => ['region' => 'Oceania', 'subregion' => 'Australia and New Zealand', 'languages' => ['English']],
        'AZ' => ['region' => 'Asia', 'subregion' => 'Western Asia', 'languages' => ['Azerbaijani']],
        'BA' => ['region' => 'Europe', 'subregion' => 'Southern Europe', 'languages' => ['Bosnian', 'Croatian', 'Serbian']],
        'BB' => ['region' => 'Americas', 'subregion' => 'Caribbean', 'languages' => ['English']],
        'BD' => ['region' => 'Asia', 'subregion' => 'Southern Asia', 'languages' => ['Bengali']],
        'BE' => ['region' => 'Europe', 'subregion' => 'Western Europe', 'languages' => ['Dutch', 'French', 'German']],
        'BF' => ['region' => 'Africa', 'subregion' => 'Western Africa', 'languages' => ['French']],
        'BG' => ['region' => 'Europe', 'subregion' => 'Eastern Europe', 'languages' => ['Bulgarian']],
        'BH' => ['region' => 'Asia', 'subregion' => 'Western Asia', 'languages' => ['Arabic']],
        'BI' => ['region' => 'Africa', 'subregion' => 'Eastern Africa', 'languages' => ['French', 'Kirundi']],
        'BJ' => ['region' => 'Africa', 'subregion' => 'Western Africa', 'languages' => ['French']],
        'BN' => ['region' => 'Asia', 'subregion' => 'South-Eastern Asia', 'languages' => ['Malay']],
        'BO' => ['region' => 'Americas', 'subregion' => 'South America', 'languages' => ['Spanish']],
        'BR' => ['region' => 'Americas', 'subregion' => 'South America', 'languages' => ['Portuguese']],
        'BS' => ['region' => 'Americas', 'subregion' => 'Caribbean', 'languages' => ['English']],
        'BT' => ['region' => 'Asia', 'subregion' => 'Southern Asia', 'languages' => ['Dzongkha']],
        'BW' => ['region' => 'Africa', 'subregion' => 'Southern Africa', 'languages' => ['English', 'Tswana']],
        'BY' => ['region' => 'Europe', 'subregion' => 'Eastern Europe', 'languages' => ['Belarusian', 'Russian']],
        'BZ' => ['region' => 'Americas', 'subregion' => 'Central America', 'languages' => ['English']],
        'CA' => ['region' => 'Americas', 'subregion' => 'North America', 'languages' => ['English', 'French']],
        'CD' => ['region' => 'Africa', 'subregion' => 'Middle Africa', 'languages' => ['French']],
        'CF' => ['region' => 'Africa', 'subregion' => 'Middle Africa', 'languages' => ['French', 'Sango']],
        'CG' => ['region' => 'Africa', 'subregion' => 'Middle Africa', 'languages' => ['French']],
        'CH' => ['region' => 'Europe', 'subregion' => 'Western Europe', 'languages' => ['German', 'French', 'Italian']],
        'CI' => ['region' => 'Africa', 'subregion' => 'Western Africa', 'languages' => ['French']],
        'CL' => ['region' => 'Americas', 'subregion' => 'South America', 'languages' => ['Spanish']],
        'CM' => ['region' => 'Africa', 'subregion' => 'Middle Africa', 'languages' => ['French', 'English']],
        'CN' => ['region' => 'Asia', 'subregion' => 'Eastern Asia', 'languages' => ['Chinese']],
        'CO' => ['region' => 'Americas', 'subregion' => 'South America', 'languages' => ['Spanish']],
        'CR' => ['region' => 'Americas', 'subregion' => 'Central America', 'languages' => ['Spanish']],
        'CU' => ['region' => 'Americas', 'subregion' => 'Caribbean', 'languages' => ['Spanish']],
        'CV' => ['region' => 'Africa', 'subregion' => 'Western Africa', 'languages' => ['Portuguese']],
        'CY' => ['region' => 'Asia', 'subregion' => 'Western Asia', 'languages' => ['Greek', 'Turkish']],
        'CZ' => ['region' => 'Europe', 'subregion' => 'Eastern Europe', 'languages' => ['Czech']],
        'DE' => ['region' => 'Europe', 'subregion' => 'Western Europe', 'languages' => ['German']],
        'DJ' => ['region' => 'Africa', 'subregion' => 'Eastern Africa', 'languages' => ['French', 'Arabic']],
        'DK' => ['region' => 'Europe', 'subregion' => 'Northern Europe', 'languages' => ['Danish']],
        'DM' => ['region' => 'Americas', 'subregion' => 'Caribbean', 'languages' => ['English']],
        'DO' => ['region' => 'Americas', 'subregion' => 'Caribbean', 'languages' => ['Spanish']],
        'DZ' => ['region' => 'Africa', 'subregion' => 'Northern Africa', 'languages' => ['Arabic']],
        'EC' => ['region' => 'Americas', 'subregion' => 'South America', 'languages' => ['Spanish']],
        'EE' => ['region' => 'Europe', 'subregion' => 'Northern Europe', 'languages' => ['Estonian']],
        'EG' => ['region' => 'Africa', 'subregion' => 'Northern Africa', 'languages' => ['Arabic']],
        'ER' => ['region' => 'Africa', 'subregion' => 'Eastern Africa', 'languages' => ['Tigrinya', 'Arabic']],
        'ES' => ['region' => 'Europe', 'subregion' => 'Southern Europe', 'languages' => ['Spanish']],
        'ET' => ['region' => 'Africa', 'subregion' => 'Eastern Africa', 'languages' => ['Amharic']],
        'FI' => ['region' => 'Europe', 'subregion' => 'Northern Europe', 'languages' => ['Finnish', 'Swedish']],
        'FJ' => ['region' => 'Oceania', 'subregion' => 'Melanesia', 'languages' => ['English', 'Fijian']],
        'FM' => ['region' => 'Oceania', 'subregion' => 'Micronesia', 'languages' => ['English']],
        'FR' => ['region' => 'Europe', 'subregion' => 'Western Europe', 'languages' => ['French']],
        'GA' => ['region' => 'Africa', 'subregion' => 'Middle Africa', 'languages' => ['French']],
        'GB' => ['region' => 'Europe', 'subregion' => 'Northern Europe', 'languages' => ['English']],
        'GD' => ['region' => 'Americas', 'subregion' => 'Caribbean', 'languages' => ['English']],
        'GE' => ['region' => 'Asia', 'subregion' => 'Western Asia', 'languages' => ['Georgian']],
        'GH' => ['region' => 'Africa', 'subregion' => 'Western Africa', 'languages' => ['English']],
        'GM' => ['region' => 'Africa', 'subregion' => 'Western Africa', 'languages' => ['English']],
        'GN' => ['region' => 'Africa', 'subregion' => 'Western Africa', 'languages' => ['French']],
        'GQ' => ['region' => 'Africa', 'subregion' => 'Middle Africa', 'languages' => ['Spanish', 'French']],
        'GR' => ['region' => 'Europe', 'subregion' => 'Southern Europe', 'languages' => ['Greek']],
        'GT' => ['region' => 'Americas', 'subregion' => 'Central America', 'languages' => ['Spanish']],
        'GW' => ['region' => 'Africa', 'subregion' => 'Western Africa', 'languages' => ['Portuguese']],
        'GY' => ['region' => 'Americas', 'subregion' => 'South America', 'languages' => ['English']],
        'HN' => ['region' => 'Americas', 'subregion' => 'Central America', 'languages' => ['Spanish']],
        'HR' => ['region' => 'Europe', 'subregion' => 'Southern Europe', 'languages' => ['Croatian']],
        'HT' => ['region' => 'Americas', 'subregion' => 'Caribbean', 'languages' => ['French', 'Haitian Creole']],
        'HU' => ['region' => 'Europe', 'subregion' => 'Eastern Europe', 'languages' => ['Hungarian']],
        'ID' => ['region' => 'Asia', 'subregion' => 'South-Eastern Asia', 'languages' => ['Indonesian']],
        'IE' => ['region' => 'Europe', 'subregion' => 'Northern Europe', 'languages' => ['English', 'Irish']],
        'IL' => ['region' => 'Asia', 'subregion' => 'Western Asia', 'languages' => ['Hebrew']],
        'IN' => ['region' => 'Asia', 'subregion' => 'Southern Asia', 'languages' => ['Hindi', 'English']],
        'IQ' => ['region' => 'Asia', 'subregion' => 'Western Asia', 'languages' => ['Arabic', 'Kurdish']],
        'IR' => ['region' => 'Asia', 'subregion' => 'Southern Asia', 'languages' => ['Persian']],
        'IS' => ['region' => 'Europe', 'subregion' => 'Northern Europe', 'languages' => ['Icelandic']],
        'IT' => ['region' => 'Europe', 'subregion' => 'Southern Europe', 'languages' => ['Italian']],
        'JM' => ['region' => 'Americas', 'subregion' => 'Caribbean', 'languages' => ['English']],
        'JO' => ['region' => 'Asia', 'subregion' => 'Western Asia', 'languages' => ['Arabic']],
        'JP' => ['region' => 'Asia', 'subregion' => 'Eastern Asia', 'languages' => ['Japanese']],
        'KE' => ['region' => 'Africa', 'subregion' => 'Eastern Africa', 'languages' => ['English', 'Swahili']],
        'KG' => ['region' => 'Asia', 'subregion' => 'Central Asia', 'languages' => ['Kyrgyz', 'Russian']],
        'KH' => ['region' => 'Asia', 'subregion' => 'South-Eastern Asia', 'languages' => ['Khmer']],
        'KI' => ['region' => 'Oceania', 'subregion' => 'Micronesia', 'languages' => ['English', 'Gilbertese']],
        'KM' => ['region' => 'Africa', 'subregion' => 'Eastern Africa', 'languages' => ['Comorian', 'Arabic', 'French']],
        'KN' => ['region' => 'Americas', 'subregion' => 'Caribbean', 'languages' => ['English']],
        'KP' => ['region' => 'Asia', 'subregion' => 'Eastern Asia', 'languages' => ['Korean']],
        'KR' => ['region' => 'Asia', 'subregion' => 'Eastern Asia', 'languages' => ['Korean']],
        'KW' => ['region' => 'Asia', 'subregion' => 'Western Asia', 'languages' => ['Arabic']],
        'KZ' => ['region' => 'Asia', 'subregion' => 'Central Asia', 'languages' => ['Kazakh', 'Russian']],
        'LA' => ['region' => 'Asia', 'subregion' => 'South-Eastern Asia', 'languages' => ['Lao']],
        'LB' => ['region' => 'Asia', 'subregion' => 'Western Asia', 'languages' => ['Arabic']],
        'LC' => ['region' => 'Americas', 'subregion' => 'Caribbean', 'languages' => ['English']],
        'LI' => ['region' => 'Europe', 'subregion' => 'Western Europe', 'languages' => ['German']],
        'LK' => ['region' => 'Asia', 'subregion' => 'Southern Asia', 'languages' => ['Sinhala', 'Tamil']],
        'LR' => ['region' => 'Africa', 'subregion' => 'Western Africa', 'languages' => ['English']],
        'LS' => ['region' => 'Africa', 'subregion' => 'Southern Africa', 'languages' => ['Sesotho', 'English']],
        'LT' => ['region' => 'Europe', 'subregion' => 'Northern Europe', 'languages' => ['Lithuanian']],
        'LU' => ['region' => 'Europe', 'subregion' => 'Western Europe', 'languages' => ['French', 'German', 'Luxembourgish']],
        'LV' => ['region' => 'Europe', 'subregion' => 'Northern Europe', 'languages' => ['Latvian']],
        'LY' => ['region' => 'Africa', 'subregion' => 'Northern Africa', 'languages' => ['Arabic']],
        'MA' => ['region' => 'Africa', 'subregion' => 'Northern Africa', 'languages' => ['Arabic']],
        'MC' => ['region' => 'Europe', 'subregion' => 'Western Europe', 'languages' => ['French']],
        'MD' => ['region' => 'Europe', 'subregion' => 'Eastern Europe', 'languages' => ['Romanian']],
        'ME' => ['region' => 'Europe', 'subregion' => 'Southern Europe', 'languages' => ['Montenegrin']],
        'MG' => ['region' => 'Africa', 'subregion' => 'Eastern Africa', 'languages' => ['Malagasy', 'French']],
        'MH' => ['region' => 'Oceania', 'subregion' => 'Micronesia', 'languages' => ['English', 'Marshallese']],
        'MK' => ['region' => 'Europe', 'subregion' => 'Southern Europe', 'languages' => ['Macedonian']],
        'ML' => ['region' => 'Africa', 'subregion' => 'Western Africa', 'languages' => ['French']],
        'MM' => ['region' => 'Asia', 'subregion' => 'South-Eastern Asia', 'languages' => ['Burmese']],
        'MN' => ['region' => 'Asia', 'subregion' => 'Eastern Asia', 'languages' => ['Mongolian']],
        'MR' => ['region' => 'Africa', 'subregion' => 'Western Africa', 'languages' => ['Arabic']],
        'MT' => ['region' => 'Europe', 'subregion' => 'Southern Europe', 'languages' => ['Maltese', 'English']],
        'MU' => ['region' => 'Africa', 'subregion' => 'Eastern Africa', 'languages' => ['English', 'French']],
        'MV' => ['region' => 'Asia', 'subregion' => 'Southern Asia', 'languages' => ['Maldivian']],
        'MW' => ['region' => 'Africa', 'subregion' => 'Eastern Africa', 'languages' => ['English', 'Chichewa']],
        'MX' => ['region' => 'Americas', 'subregion' => 'North America', 'languages' => ['Spanish']],
        'MY' => ['region' => 'Asia', 'subregion' => 'South-Eastern Asia', 'languages' => ['Malay']],
        'MZ' => ['region' => 'Africa', 'subregion' => 'Eastern Africa', 'languages' => ['Portuguese']],
        'NA' => ['region' => 'Africa', 'subregion' => 'Southern Africa', 'languages' => ['English']],
        'NE' => ['region' => 'Africa', 'subregion' => 'Western Africa', 'languages' => ['French']],
        'NG' => ['region' => 'Africa', 'subregion' => 'Western Africa', 'languages' => ['English']],
        'NI' => ['region' => 'Americas', 'subregion' => 'Central America', 'languages' => ['Spanish']],
        'NL' => ['region' => 'Europe', 'subregion' => 'Western Europe', 'languages' => ['Dutch']],
        'NO' => ['region' => 'Europe', 'subregion' => 'Northern Europe', 'languages' => ['Norwegian']],
        'NP' => ['region' => 'Asia', 'subregion' => 'Southern Asia', 'languages' => ['Nepali']],
        'NR' => ['region' => 'Oceania', 'subregion' => 'Micronesia', 'languages' => ['Nauruan', 'English']],
        'NZ' => ['region' => 'Oceania', 'subregion' => 'Australia and New Zealand', 'languages' => ['English', 'Maori']],
        'OM' => ['region' => 'Asia', 'subregion' => 'Western Asia', 'languages' => ['Arabic']],
        'PA' => ['region' => 'Americas', 'subregion' => 'Central America', 'languages' => ['Spanish']],
        'PE' => ['region' => 'Americas', 'subregion' => 'South America', 'languages' => ['Spanish']],
        'PG' => ['region' => 'Oceania', 'subregion' => 'Melanesia', 'languages' => ['English', 'Tok Pisin']],
        'PH' => ['region' => 'Asia', 'subregion' => 'South-Eastern Asia', 'languages' => ['Filipino', 'English']],
        'PK' => ['region' => 'Asia', 'subregion' => 'Southern Asia', 'languages' => ['Urdu', 'English']],
        'PL' => ['region' => 'Europe', 'subregion' => 'Eastern Europe', 'languages' => ['Polish']],
        'PS' => ['region' => 'Asia', 'subregion' => 'Western Asia', 'languages' => ['Arabic']],
        'PT' => ['region' => 'Europe', 'subregion' => 'Southern Europe', 'languages' => ['Portuguese']],
        'PW' => ['region' => 'Oceania', 'subregion' => 'Micronesia', 'languages' => ['English', 'Palauan']],
        'PY' => ['region' => 'Americas', 'subregion' => 'South America', 'languages' => ['Spanish', 'Guarani']],
        'QA' => ['region' => 'Asia', 'subregion' => 'Western Asia', 'languages' => ['Arabic']],
        'RO' => ['region' => 'Europe', 'subregion' => 'Eastern Europe', 'languages' => ['Romanian']],
        'RS' => ['region' => 'Europe', 'subregion' => 'Southern Europe', 'languages' => ['Serbian']],
        'RU' => ['region' => 'Europe', 'subregion' => 'Eastern Europe', 'languages' => ['Russian']],
        'RW' => ['region' => 'Africa', 'subregion' => 'Eastern Africa', 'languages' => ['Kinyarwanda', 'French', 'English']],
        'SA' => ['region' => 'Asia', 'subregion' => 'Western Asia', 'languages' => ['Arabic']],
        'SB' => ['region' => 'Oceania', 'subregion' => 'Melanesia', 'languages' => ['English']],
        'SC' => ['region' => 'Africa', 'subregion' => 'Eastern Africa', 'languages' => ['English', 'French']],
        'SD' => ['region' => 'Africa', 'subregion' => 'Northern Africa', 'languages' => ['Arabic']],
        'SE' => ['region' => 'Europe', 'subregion' => 'Northern Europe', 'languages' => ['Swedish']],
        'SG' => ['region' => 'Asia', 'subregion' => 'South-Eastern Asia', 'languages' => ['English', 'Malay', 'Mandarin', 'Tamil']],
        'SI' => ['region' => 'Europe', 'subregion' => 'Southern Europe', 'languages' => ['Slovenian']],
        'SK' => ['region' => 'Europe', 'subregion' => 'Eastern Europe', 'languages' => ['Slovak']],
        'SL' => ['region' => 'Africa', 'subregion' => 'Western Africa', 'languages' => ['English']],
        'SM' => ['region' => 'Europe', 'subregion' => 'Southern Europe', 'languages' => ['Italian']],
        'SN' => ['region' => 'Africa', 'subregion' => 'Western Africa', 'languages' => ['French']],
        'SO' => ['region' => 'Africa', 'subregion' => 'Eastern Africa', 'languages' => ['Somali', 'Arabic']],
        'SR' => ['region' => 'Americas', 'subregion' => 'South America', 'languages' => ['Dutch']],
        'SS' => ['region' => 'Africa', 'subregion' => 'Eastern Africa', 'languages' => ['English']],
        'ST' => ['region' => 'Africa', 'subregion' => 'Middle Africa', 'languages' => ['Portuguese']],
        'SV' => ['region' => 'Americas', 'subregion' => 'Central America', 'languages' => ['Spanish']],
        'SY' => ['region' => 'Asia', 'subregion' => 'Western Asia', 'languages' => ['Arabic']],
        'SZ' => ['region' => 'Africa', 'subregion' => 'Southern Africa', 'languages' => ['English', 'Swazi']],
        'TD' => ['region' => 'Africa', 'subregion' => 'Middle Africa', 'languages' => ['French', 'Arabic']],
        'TG' => ['region' => 'Africa', 'subregion' => 'Western Africa', 'languages' => ['French']],
        'TH' => ['region' => 'Asia', 'subregion' => 'South-Eastern Asia', 'languages' => ['Thai']],
        'TJ' => ['region' => 'Asia', 'subregion' => 'Central Asia', 'languages' => ['Tajik']],
        'TL' => ['region' => 'Asia', 'subregion' => 'South-Eastern Asia', 'languages' => ['Portuguese', 'Tetum']],
        'TM' => ['region' => 'Asia', 'subregion' => 'Central Asia', 'languages' => ['Turkmen']],
        'TN' => ['region' => 'Africa', 'subregion' => 'Northern Africa', 'languages' => ['Arabic']],
        'TO' => ['region' => 'Oceania', 'subregion' => 'Polynesia', 'languages' => ['Tongan', 'English']],
        'TR' => ['region' => 'Asia', 'subregion' => 'Western Asia', 'languages' => ['Turkish']],
        'TT' => ['region' => 'Americas', 'subregion' => 'Caribbean', 'languages' => ['English']],
        'TV' => ['region' => 'Oceania', 'subregion' => 'Polynesia', 'languages' => ['Tuvaluan', 'English']],
        'TW' => ['region' => 'Asia', 'subregion' => 'Eastern Asia', 'languages' => ['Chinese']],
        'TZ' => ['region' => 'Africa', 'subregion' => 'Eastern Africa', 'languages' => ['Swahili', 'English']],
        'UA' => ['region' => 'Europe', 'subregion' => 'Eastern Europe', 'languages' => ['Ukrainian']],
        'UG' => ['region' => 'Africa', 'subregion' => 'Eastern Africa', 'languages' => ['English', 'Swahili']],
        'US' => ['region' => 'Americas', 'subregion' => 'North America', 'languages' => ['English']],
        'UY' => ['region' => 'Americas', 'subregion' => 'South America', 'languages' => ['Spanish']],
        'UZ' => ['region' => 'Asia', 'subregion' => 'Central Asia', 'languages' => ['Uzbek']],
        'VA' => ['region' => 'Europe', 'subregion' => 'Southern Europe', 'languages' => ['Italian', 'Latin']],
        'VC' => ['region' => 'Americas', 'subregion' => 'Caribbean', 'languages' => ['English']],
        'VE' => ['region' => 'Americas', 'subregion' => 'South America', 'languages' => ['Spanish']],
        'VN' => ['region' => 'Asia', 'subregion' => 'South-Eastern Asia', 'languages' => ['Vietnamese']],
        'VU' => ['region' => 'Oceania', 'subregion' => 'Melanesia', 'languages' => ['English', 'French', 'Bislama']],
        'WS' => ['region' => 'Oceania', 'subregion' => 'Polynesia', 'languages' => ['Samoan', 'English']],
        'YE' => ['region' => 'Asia', 'subregion' => 'Western Asia', 'languages' => ['Arabic']],
        'ZA' => ['region' => 'Africa', 'subregion' => 'Southern Africa', 'languages' => ['English', 'Zulu', 'Afrikaans']],
        'ZM' => ['region' => 'Africa', 'subregion' => 'Eastern Africa', 'languages' => ['English']],
        'ZW' => ['region' => 'Africa', 'subregion' => 'Eastern Africa', 'languages' => ['English', 'Shona']],
    ];

    
    // get multiple countries
    public function getMultipleCountries(array $countryCodes): array
    {
        $results = [];
        foreach ($countryCodes as $code) {
            $results[$code] = $this->getCountryInfo($code);
        }
        return $results;
    }

    
    // search by name
    public function searchByName(string $name): array
    {
        $name = trim($name);
        if ($name === '') {
            return [];
        }

        $cacheKey = 'restcountries_search_' . strtolower($name);

        
        
        
        
        
        
        
        
        
        
        
        $cached = Cache::get($cacheKey);
        if (is_array($cached) && !empty($cached)) {
            return $cached;
        }

        $results = $this->fetchByName($name);

        if (!empty($results)) {
            Cache::put($cacheKey, $results, now()->addDay());
        }

        return $results;
    }

    
    // fetch by name
    protected function fetchByName(string $name): array
    {
        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/name/{$name}", [
                'fields' => 'name,cca2,capital,region,flags,latlng',
            ]);

            if (!$response->successful()) {
                Log::warning("RestCountries Search API: gagal cari '{$name}' (status " . $response->status() . ')');
                return [];
            }

            $data = $response->json();
            if (!is_array($data)) {
                return [];
            }

            $results = [];
            foreach ($data as $entry) {
                if (empty($entry['cca2'])) {
                    continue;
                }
                $results[] = [
                    'name' => $entry['name']['common'] ?? $entry['cca2'],
                    'code' => $entry['cca2'],
                    'capital' => $entry['capital'][0] ?? null,
                    'region' => $entry['region'] ?? null,
                    'flag_url' => $entry['flags']['png'] ?? ($entry['flags']['svg'] ?? null),
                    'latitude' => $entry['latlng'][0] ?? null,
                    'longitude' => $entry['latlng'][1] ?? null,
                ];
            }

            usort($results, fn ($a, $b) => strcmp($a['name'], $b['name']));

            return array_slice($results, 0, 15);
        } catch (\Exception $e) {
            Log::error('RestCountries Search API Error: ' . $e->getMessage());
            return [];
        }
    }

    
    // get all countries
    public function getAllCountries(): array
    {
        $cacheKey = 'restcountries_all';

        return Cache::remember($cacheKey, now()->addDays(7), function () {
            try {
                
                
                
                
                
                $response = Http::timeout(30)
                    ->withOptions(['allow_redirects' => ['strict' => true, 'max' => 5]])
                    ->get("{$this->baseUrl}/all", [
                        'fields' => 'name,cca2,capital,region,subregion,languages,currencies,flags,population,latlng',
                    ]);

                if (!$response->successful()) {
                    Log::warning('RestCountries API: gagal ambil daftar semua negara (status ' . $response->status() . ', body: ' . substr($response->body(), 0, 300) . ')');
                    return [];
                }

                $data = $response->json();
                if (!is_array($data)) {
                    Log::warning('RestCountries API: response /all bukan array. Body: ' . substr($response->body(), 0, 300));
                    return [];
                }

                $results = [];
                foreach ($data as $entry) {
                    if (empty($entry['cca2'])) {
                        continue;
                    }
                    $results[] = $this->mapCountryData($entry);
                }

                usort($results, fn ($a, $b) => strcmp($a['name'] ?? '', $b['name'] ?? ''));

                return $results;
            } catch (\Exception $e) {
                Log::error('RestCountries getAllCountries Error: ' . $e->getMessage());
                return [];
            }
        });
    }

    // map country data
    protected function mapCountryData(array $data): array
    {
        $currencyCode = null;
        $currencyName = null;
        $currencySymbol = null;
        if (!empty($data['currencies']) && is_array($data['currencies'])) {
            $currencyCode = array_key_first($data['currencies']);
            $currencyName = $data['currencies'][$currencyCode]['name'] ?? null;
            $currencySymbol = $data['currencies'][$currencyCode]['symbol'] ?? null;
        }

        $languages = [];
        if (!empty($data['languages']) && is_array($data['languages'])) {
            $languages = array_values($data['languages']);
        }

        return [
            'name' => $data['name']['common'] ?? null,
            'code' => $data['cca2'] ?? null,
            'capital' => $data['capital'][0] ?? null,
            'region' => $data['region'] ?? null,
            'subregion' => $data['subregion'] ?? null,
            'languages' => $languages,
            'currency_code' => $currencyCode,
            'currency_name' => $currencyName,
            'currency_symbol' => $currencySymbol,
            'flag_url' => $data['flags']['png'] ?? ($data['flags']['svg'] ?? null),
            'population' => $data['population'] ?? null,
            'latitude' => $data['latlng'][0] ?? null,
            'longitude' => $data['latlng'][1] ?? null,
        ];
    }
}
