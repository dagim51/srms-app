<?php

namespace App\Support;

class EthiopicTextNormalizer
{
    /**
     * Map of common Ethiopian location and real estate bilingual synonyms.
     */
    protected static array $synonyms = [
        // Neighborhoods & Cities
        'bole' => ['ቦሌ', 'bole'],
        'ቦሌ' => ['bole', 'ቦሌ'],
        'kazanchis' => ['ካዛንቺስ', 'kazanchis'],
        'ካዛንቺስ' => ['kazanchis', 'ካዛንቺስ'],
        'hawassa' => ['ሀዋሳ', 'ሐዋሳ', 'hawassa'],
        'ሀዋሳ' => ['hawassa', 'ሀዋሳ', 'ሐዋሳ'],
        'ሐዋሳ' => ['hawassa', 'ሀዋሳ', 'ሐዋሳ'],
        'bahir dar' => ['ባሕር ዳር', 'ባህር ዳር', 'bahir dar'],
        'ባሕር ዳር' => ['bahir dar', 'ባሕር ዳር', 'ባህር ዳር'],
        'ባህር ዳር' => ['bahir dar', 'ባሕር ዳር', 'ባህር ዳር'],
        'bishoftu' => ['ቢሾፍቱ', 'debre zeyit', 'bishoftu'],
        'ቢሾፍቱ' => ['bishoftu', 'ቢሾፍቱ'],
        'adama' => ['አዳማ', 'nazret', 'adama'],
        'አዳማ' => ['adama', 'አዳማ'],
        'gerji' => ['ገርጂ', 'gerji'],
        'ገርጂ' => ['gerji', 'ገርጂ'],
        'sarbet' => ['ሳርቤት', 'sarbet', 'sar bet'],
        'ሳርቤት' => ['sarbet', 'ሳርቤት'],
        'cmc' => ['ሲኤምሲ', 'cmc'],
        'ሲኤምሲ' => ['cmc', 'ሲኤምሲ'],
        'megenagna' => ['መገናኛ', 'megenagna'],
        'መገናኛ' => ['megenagna', 'መገናኛ'],
        'piassa' => ['ፒያሳ', 'piassa'],
        'ፒያሳ' => ['piassa', 'ፒያሳ'],
        'ayat' => ['አያት', 'ayat'],
        'አያት' => ['ayat', 'አያት'],
        'lebu' => ['ለቡ', 'lebu'],
        'ለቡ' => ['lebu', 'ለቡ'],
        'jomo' => ['ጀሞ', 'jomo'],
        'ጀሞ' => ['jomo', 'ጀሞ'],
        'goro' => ['ጎሮ', 'goro'],
        'ጎሮ' => ['goro', 'ጎሮ'],
        'summit' => ['ሰሚት', 'summit'],
        'ሰሚት' => ['summit', 'ሰሚት'],
        'old airport' => ['ኦልድ ኤርፖርት', 'old airport'],
        'ኦልድ ኤርፖርት' => ['old airport', 'ኦልድ ኤርፖርት'],

        // Property types & Modes
        'villa' => ['ቪላ', 'villa'],
        'ቪላ' => ['villa', 'ቪላ'],
        'apartment' => ['አፓርትመንት', 'apartment'],
        'አፓርትመንት' => ['apartment', 'አፓርትመንት'],
        'condo' => ['ኮንዶሚኒየም', 'ኮንዶ', 'condo', 'condominium'],
        'ኮንዶሚኒየም' => ['condo', 'condominium', 'ኮንዶሚኒየም'],
        'penthouse' => ['ፔንትሃውስ', 'penthouse'],
        'ፔንትሃውስ' => ['penthouse', 'ፔንትሃውስ'],
        'rent' => ['ኪራይ', 'rent', 'for rent'],
        'ኪራይ' => ['rent', 'for rent', 'ኪራይ'],
        'sale' => ['ሽያጭ', 'sale', 'for sale', 'buy'],
        'ሽያጭ' => ['sale', 'for sale', 'ሽያጭ'],
        'land' => ['መሬት', 'ቦታ', 'land'],
        'መሬት' => ['land', 'መሬት'],
    ];

    /**
     * Normalize Ethiopic homophone letters (ሀ/ሐ/ኀ, ሰ/ሠ, አ/ዐ, ጸ/ፀ, etc.)
     */
    public static function normalizeFidel(string $text): string
    {
        $patterns = [
            // H family
            '/[ሐኀኃ]/u' => 'ሀ',
            '/[ሑኁኂ]/u' => 'ሁ',
            '/[ሒኺ]/u' => 'ሂ',
            '/[ሓኻ]/u' => 'ሃ',
            '/[ሔኼ]/u' => 'ሄ',
            '/[ሕኅ]/u' => 'ህ',
            '/[ሖ]/u' => 'ሆ',

            // S family
            '/ሠ/u' => 'ሰ',
            '/ሡ/u' => 'ሱ',
            '/ሢ/u' => 'ሲ',
            '/ሣ/u' => 'ሳ',
            '/ሤ/u' => 'ሴ',
            '/ሥ/u' => 'ስ',
            '/ሦ/u' => 'ሶ',

            // A family
            '/ዐ/u' => 'አ',
            '/ዑ/u' => 'ኡ',
            '/ዒ/u' => 'ኢ',
            '/ዓ/u' => 'ኣ',
            '/ዔ/u' => 'ኤ',
            '/ዕ/u' => 'እ',
            '/ዖ/u' => 'ኦ',

            // Ts family
            '/ፀ/u' => 'ጸ',
            '/ፁ/u' => 'ጹ',
            '/ፂ/u' => 'ጺ',
            '/ፃ/u' => 'ጻ',
            '/ፄ/u' => 'ጼ',
            '/ፅ/u' => 'ጽ',
            '/ፆ/u' => 'ጾ',
        ];

        return preg_replace(array_keys($patterns), array_values($patterns), $text);
    }

    /**
     * Extract expanded bilingual search tokens for database queries.
     *
     * @return array<string>
     */
    public static function getSearchTokens(string $input): array
    {
        $clean = trim($input);
        if (empty($clean)) {
            return [];
        }

        $tokens = [$clean];
        $lower = mb_strtolower($clean);

        // Normalized Fidel form
        $normalized = self::normalizeFidel($clean);
        if ($normalized !== $clean) {
            $tokens[] = $normalized;
        }

        // Check synonyms (both exact and substring matching)
        foreach (self::$synonyms as $k => $synList) {
            if ($lower === $k || self::normalizeFidel($lower) === self::normalizeFidel($k)) {
                $tokens = array_merge($tokens, $synList);
            } elseif (str_contains($lower, $k)) {
                $tokens = array_merge($tokens, $synList);
            }
        }

        return array_values(array_unique(array_filter($tokens)));
    }
}
