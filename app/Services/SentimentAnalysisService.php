<?php

namespace App\Services;

use App\Models\PositiveWord;
use App\Models\NegativeWord;
use Illuminate\Support\Facades\Log;

// Kelas SentimentAnalysisService: sentiment analysis service
class SentimentAnalysisService
{
    protected array $positiveWords = [];
    protected array $negativeWords = [];

    // inisialisasi objek
    public function __construct()
    {
        $this->loadWords();
    }

    // load words
    protected function loadWords(): void
    {
        try {
            $this->positiveWords = PositiveWord::pluck('word')->toArray();
            $this->negativeWords = NegativeWord::pluck('word')->toArray();
        } catch (\Exception $e) {
            Log::error('Error loading sentiment words: ' . $e->getMessage());
            
            $this->positiveWords = [
                'growth', 'increase', 'profit', 'stable', 'improve',
                'positive', 'gain', 'rise', 'success', 'recovery',
                'boom', 'expansion', 'opportunity', 'benefit', 'good'
            ];
            $this->negativeWords = [
                'war', 'crisis', 'inflation', 'delay', 'disaster',
                'decline', 'loss', 'drop', 'fail', 'risk',
                'danger', 'conflict', 'instability', 'down', 'negative'
            ];
        }
    }

    
    // analyze
    public function analyze(string $text): array
    {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z\s]/', '', $text);
        $words = array_filter(explode(' ', $text));

        $positiveScore = 0;
        $negativeScore = 0;
        $matchedWords = ['positive' => [], 'negative' => []];

        foreach ($words as $word) {
            if (in_array($word, $this->positiveWords)) {
                $positiveScore++;
                $matchedWords['positive'][] = $word;
            } elseif (in_array($word, $this->negativeWords)) {
                $negativeScore++;
                $matchedWords['negative'][] = $word;
            }
        }

        $total = $positiveScore + $negativeScore;

        if ($total === 0) {
            return [
                'sentiment' => 'Neutral',
                'positive_score' => 0,
                'negative_score' => 0,
                'positive_percentage' => 0,
                'negative_percentage' => 0,
                'neutral_percentage' => 100,
                'matched_words' => [],
            ];
        }

        $positivePercentage = round(($positiveScore / $total) * 100, 2);
        $negativePercentage = round(($negativeScore / $total) * 100, 2);

        $sentiment = 'Neutral';
        if ($positiveScore > $negativeScore) {
            $sentiment = 'Positive';
        } elseif ($negativeScore > $positiveScore) {
            $sentiment = 'Negative';
        }

        return [
            'sentiment' => $sentiment,
            'positive_score' => $positiveScore,
            'negative_score' => $negativeScore,
            'positive_percentage' => $positivePercentage,
            'negative_percentage' => $negativePercentage,
            'neutral_percentage' => round(100 - $positivePercentage - $negativePercentage, 2),
            'matched_words' => $matchedWords,
        ];
    }

    
    // get sentiment distribution
    public function getSentimentDistribution(array $texts): array
    {
        $distribution = [
            'Positive' => 0,
            'Neutral' => 0,
            'Negative' => 0,
        ];

        foreach ($texts as $text) {
            $result = $this->analyze($text);
            $distribution[$result['sentiment']]++;
        }

        $total = array_sum($distribution);
        if ($total > 0) {
            foreach ($distribution as $key => $value) {
                $distribution[$key] = round(($value / $total) * 100, 2);
            }
        } else {
            $distribution = ['Positive' => 0, 'Neutral' => 0, 'Negative' => 0];
        }

        return $distribution;
    }
}
