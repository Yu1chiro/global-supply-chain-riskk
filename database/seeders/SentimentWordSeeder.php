<?php

namespace Database\Seeders;

use App\Models\PositiveWord;
use App\Models\NegativeWord;
use Illuminate\Database\Seeder;

// Kelas SentimentWordSeeder: sentiment word seeder
class SentimentWordSeeder extends Seeder
{
    // run
    public function run()
    {
        
        $positiveWords = [
            'growth', 'increase', 'profit', 'stable', 'improve', 'positive',
            'gain', 'rise', 'success', 'recovery', 'boom', 'expansion',
            'opportunity', 'benefit', 'good', 'great', 'excellent', 'amazing',
            'breakthrough', 'advance', 'progress', 'development', 'innovation',
            'strength', 'strong', 'resilient', 'robust', 'optimistic',
        ];

        foreach ($positiveWords as $word) {
            PositiveWord::updateOrCreate(['word' => $word]);
        }

        
        $negativeWords = [
            'war', 'crisis', 'inflation', 'delay', 'disaster', 'decline',
            'loss', 'drop', 'fail', 'risk', 'danger', 'conflict',
            'instability', 'down', 'negative', 'bad', 'terrible', 'horrible',
            'catastrophe', 'emergency', 'alarm', 'warning', 'threat',
            'unstable', 'weak', 'debt', 'deficit', 'recession',
            'depression', 'inflationary', 'volatile', 'turbulent',
        ];

        foreach ($negativeWords as $word) {
            NegativeWord::updateOrCreate(['word' => $word]);
        }
    }
}