<?php

namespace App\Services;

class AiBookRequestValidator
{
    /**
     * @return array{valid: bool, message: ?string}
     */
    public function validate(?string $mood, ?string $genreName, ?string $freeText): array
    {
        $freeText = trim((string) $freeText);

        if ($freeText === '') {
            return ['valid' => true, 'message' => null];
        }

        if ($this->isClearlyOffTopic($freeText)) {
            return [
                'valid' => false,
                'message' => __('ui.ai.invalid_request'),
            ];
        }

        if ($this->isBookRelated($freeText)) {
            return ['valid' => true, 'message' => null];
        }

        // Ruh hali veya tür seçiliyse serbest metin daha esnek olabilir
        if (filled($mood) || filled($genreName)) {
            return ['valid' => true, 'message' => null];
        }

        return [
            'valid' => false,
            'message' => __('ui.ai.invalid_request'),
        ];
    }

    private function isBookRelated(string $text): bool
    {
        $normalized = mb_strtolower($text, 'UTF-8');

        $bookKeywords = [
            'kitap', 'roman', 'hikaye', 'öykü', 'novella', 'yazar', 'author', 'oku', 'okuma', 'okuyacağım',
            'okudum', 'fiction', 'non-fiction', 'edebiyat', 'literature', 'sayfa', 'cilt', 'seri', 'trilogy',
            'bilimkurgu', 'bilim kurgu', 'sci-fi', 'scifi', 'fantastik', 'fantasy', 'polisiye', 'mystery',
            'thriller', 'macera', 'aşk', 'romance', 'klasik', 'distopya', 'dystopia', 'korku', 'horror',
            'tarih', 'biyografi', 'autobiography', 'memoir', 'şiir', 'poetry', 'çocuk', 'gençlik', 'ya',
            'graphic novel', 'manga', 'comic', 'alıntı', 'quote', 'karakter', 'plot', 'tür', 'genre',
            'kategori', 'raflar', 'shelf', 'öner', 'tavsiye', 'recommend', 'suggest', 'benzer', 'tarz',
            'melankolik', 'sürükleyici', 'ilham', 'karanlık', 'gripping', 'inspiring', 'dark',
            'uzay', 'space', 'vampir', 'vampire', 'dedektif', 'detective', 'savaş', 'war', 'mitoloji',
        ];

        foreach ($bookKeywords as $keyword) {
            if (str_contains($normalized, $keyword)) {
                return true;
            }
        }

        $intentPatterns = [
            '/\b(arıyorum|istiyorum|aramak|arayış|önerir\s*misin|tavsiye\s*eder\s*misin)\b/u',
            '/\b(okumak\s*istiyorum|okuyayım|okusam|okurum)\b/u',
            '/\b(benzeri|tarzında|gibi\s+bir|gibi\s+kitap)\b/u',
            '/\b(looking\s+for|want\s+to\s+read|recommend|suggest)\b/u',
        ];

        foreach ($intentPatterns as $pattern) {
            if (preg_match($pattern, $normalized)) {
                return true;
            }
        }

        return false;
    }

    private function isClearlyOffTopic(string $text): bool
    {
        $normalized = mb_strtolower($text, 'UTF-8');

        $offTopicPatterns = [
            '/\b(merhaba|selam|nasılsın|naber|günaydın|iyi\s+akşamlar|hello|hi\s+there|hey\s+there)\b/u',
            '/\b(pizza|burger|yemek\s+tarifi|tarif\s+ver|recipe|cook|cooking|kahvaltı)\b/u',
            '/\b(futbol|basketbol|maç\s+skoru|champions\s+league|galatasaray|fenerbahçe)\b/u',
            '/\b(matematik|fizik\s+ödev|ödev\s+yap|homework|integral|denklem)\b/u',
            '/\b(python\s+kod|javascript|php\s+kod|kod\s+yaz|bug\s+fix|programlama\s+öğren)\b/u',
            '/\b(hava\s+durumu|weather|yağmur\s+yağacak)\b/u',
            '/\b(burç|horoscope|yükselen)\b/u',
            '/\b(şaka|fıkra|espri\s+yap|tell\s+me\s+a\s+joke)\b/u',
            '/\b(bitcoin|kripto|hisse|borsa|dolar\s+kuru)\b/u',
            '/\b(film\s+öner|dizi\s+öner|netflix|spotify|şarkı\s+öner)\b/u',
            '/\b(saç\s+bakım|makyaj|kilo\s+verme|diyet\s+listesi)\b/u',
        ];

        foreach ($offTopicPatterns as $pattern) {
            if (preg_match($pattern, $normalized)) {
                return true;
            }
        }

        return false;
    }
}
