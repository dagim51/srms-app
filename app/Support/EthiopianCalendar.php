<?php

namespace App\Support;

use Carbon\Carbon;
use DateTimeInterface;

class EthiopianCalendar
{
    public const JD_EPOCH_OFFSET = 1723856;

    public const AMHARIC_MONTHS = [
        1 => 'መስከረም',
        2 => 'ጥቅምት',
        3 => 'ኅዳር',
        4 => 'ታኅሣሥ',
        5 => 'ጥር',
        6 => 'የካቲት',
        7 => 'መጋቢት',
        8 => 'ሚያዝያ',
        9 => 'ግንቦት',
        10 => 'ሰኔ',
        11 => 'ሐምሌ',
        12 => 'ነሐሴ',
        13 => 'ጳጉሜ',
    ];

    public const ENGLISH_ETHIOPIC_MONTHS = [
        1 => 'Meskerem',
        2 => 'Tikimt',
        3 => 'Hidar',
        4 => 'Tahsas',
        5 => 'Tir',
        6 => 'Yekatit',
        7 => 'Megabit',
        8 => 'Miyazya',
        9 => 'Ginbot',
        10 => 'Sene',
        11 => 'Hamle',
        12 => 'Nehase',
        13 => 'Pagume',
    ];

    /**
     * Convert Gregorian date to Ethiopian date array.
     *
     * @return array{year: int, month: int, day: int}
     */
    public static function fromGregorian(int $year, int $month, int $day): array
    {
        $jdn = gregoriantojd($month, $day, $year);
        $r = ($jdn - self::JD_EPOCH_OFFSET) % 1461;
        $n = ($r % 365) + 365 * (int)floor($r / 1460);
        $ethYear = 4 * (int)floor(($jdn - self::JD_EPOCH_OFFSET) / 1461) + (int)floor($r / 365) - (int)floor($r / 1460);
        $ethMonth = (int)floor($n / 30) + 1;
        $ethDay = ($n % 30) + 1;

        return [
            'year' => $ethYear,
            'month' => $ethMonth,
            'day' => $ethDay,
        ];
    }

    /**
     * Convert Ethiopian date array to Gregorian date array.
     *
     * @return array{year: int, month: int, day: int}
     */
    public static function toGregorian(int $year, int $month, int $day): array
    {
        $jdn = self::JD_EPOCH_OFFSET + 365 * $year + (int)floor($year / 4) + 30 * ($month - 1) + $day - 1;
        $greg = jdtogregorian($jdn);
        [$m, $d, $y] = explode('/', $greg);

        return [
            'year' => (int)$y,
            'month' => (int)$m,
            'day' => (int)$d,
        ];
    }

    /**
     * Format a date into localized string, optionally showing dual Gregorian + Ethiopian dates.
     *
     * @param DateTimeInterface|string|null $date
     * @param string|null $locale 'am' or 'en' (defaults to current app locale)
     * @param bool $dual Whether to display both calendars
     * @return string
     */
    public static function format(DateTimeInterface|string|null $date, ?string $locale = null, bool $dual = false): string
    {
        if (empty($date)) {
            return '';
        }

        $carbon = $date instanceof DateTimeInterface ? Carbon::instance($date) : Carbon::parse($date);
        $locale = $locale ?? app()->getLocale();

        $eth = self::fromGregorian((int)$carbon->format('Y'), (int)$carbon->format('m'), (int)$carbon->format('d'));
        $amharicMonth = self::AMHARIC_MONTHS[$eth['month']] ?? '';
        $englishMonth = self::ENGLISH_ETHIOPIC_MONTHS[$eth['month']] ?? '';

        $ethAmharicFormatted = "{$amharicMonth} {$eth['day']}፣ {$eth['year']} ዓ.ም.";
        $ethEnglishFormatted = "{$englishMonth} {$eth['day']}, {$eth['year']} E.C.";
        $gregFormatted = $carbon->format('M j, Y');

        if ($locale === 'am') {
            if ($dual) {
                return "{$ethAmharicFormatted} ({$gregFormatted} እ.ኤ.አ.)";
            }
            return $ethAmharicFormatted;
        }

        // Default English
        if ($dual) {
            return "{$gregFormatted} ({$ethEnglishFormatted})";
        }
        return $gregFormatted;
    }
}
