<?php
declare(strict_types=1);

namespace App\Services;

final class CitationFormatter
{
    public static function apa(array $j): string
    {
        $authors = self::formatAuthorsApa((string)($j['author'] ?? ''));
        $year = self::formatYear((string)($j['year'] ?? ''));
        $title = self::clean((string)($j['title'] ?? ''));
        $source = self::clean((string)($j['source'] ?? ''));
        $doi = trim((string)($j['doi'] ?? ''));
        $pdf = trim((string)($j['pdf_url'] ?? ''));

        $parts = [];
        if ($authors !== '') {
            $parts[] = $authors;
        }
        if ($year !== '') {
            $parts[] = '(' . $year . ').';
        }
        if ($title !== '') {
            $parts[] = $title . '.';
        }
        if ($source !== '') {
            $parts[] = $source . '.';
        }

        $link = $doi !== '' ? ('https://doi.org/' . $doi) : $pdf;
        if ($link !== '') {
            $parts[] = $link;
        }

        return trim(preg_replace('/\s+/', ' ', implode(' ', $parts)) ?? '');
    }

    public static function ieee(array $j): string
    {
        $authors = self::formatAuthorsIeee((string)($j['author'] ?? ''));
        $title = self::clean((string)($j['title'] ?? ''));
        $source = self::clean((string)($j['source'] ?? ''));
        $year = self::formatYear((string)($j['year'] ?? ''));
        $doi = trim((string)($j['doi'] ?? ''));
        $pdf = trim((string)($j['pdf_url'] ?? ''));

        $parts = [];
        if ($authors !== '') {
            $parts[] = $authors . ',';
        }
        if ($title !== '') {
            $parts[] = '"' . $title . ',"';
        }
        if ($source !== '') {
            $parts[] = $source . ',';
        }
        if ($year !== '') {
            $parts[] = $year . '.';
        }

        $link = $doi !== '' ? ('doi: ' . $doi . '.') : ($pdf !== '' ? $pdf : '');
        if ($link !== '') {
            $parts[] = $link;
        }

        return trim(preg_replace('/\s+/', ' ', implode(' ', $parts)) ?? '');
    }

    private static function clean(string $s): string
    {
        $s = trim($s);
        $s = preg_replace('/\s+/', ' ', $s) ?? $s;
        $s = rtrim($s, " \t\n\r\0\x0B.");
        return $s;
    }

    private static function formatYear(string $yearRaw): string
    {
        $yearRaw = trim($yearRaw);
        if ($yearRaw === '') {
            return '';
        }
        if (ctype_digit($yearRaw)) {
            $y = (int)$yearRaw;
            if ($y >= 1800 && $y <= ((int)date('Y') + 1)) {
                return (string)$y;
            }
        }
        return '';
    }

    private static function splitAuthors(string $raw): array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return [];
        }

        $raw = str_replace(['&', ' dan '], [' and ', ' and '], $raw);
        $raw = preg_replace('/\s+/', ' ', $raw) ?? $raw;

        $parts = preg_split('/\s*;\s*|\s*,\s*(?=[A-Z][a-zA-Z\-]+\s+[A-Z])|\s+and\s+/u', $raw) ?: [];
        $authors = [];
        foreach ($parts as $p) {
            $p = trim((string)$p);
            if ($p === '') {
                continue;
            }
            $authors[] = $p;
        }
        return $authors;
    }

    private static function formatAuthorsApa(string $raw): string
    {
        $authors = self::splitAuthors($raw);
        if (!$authors) {
            return '';
        }

        $formatted = [];
        foreach ($authors as $a) {
            $formatted[] = self::apaName($a);
        }

        if (count($formatted) === 1) {
            return $formatted[0] . '.';
        }

        if (count($formatted) === 2) {
            return $formatted[0] . ' & ' . $formatted[1] . '.';
        }

        $last = array_pop($formatted);
        return implode(', ', $formatted) . ', & ' . $last . '.';
    }

    private static function formatAuthorsIeee(string $raw): string
    {
        $authors = self::splitAuthors($raw);
        if (!$authors) {
            return '';
        }

        $formatted = [];
        foreach ($authors as $a) {
            $formatted[] = self::ieeeName($a);
        }

        if (count($formatted) <= 2) {
            return implode(' and ', $formatted);
        }

        $last = array_pop($formatted);
        return implode(', ', $formatted) . ', and ' . $last;
    }

    private static function apaName(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            return '';
        }

        if (str_contains($name, ',')) {
            [$last, $first] = array_map('trim', explode(',', $name, 2));
            $initials = self::initials($first);
            return trim($last . ', ' . $initials);
        }

        $parts = preg_split('/\s+/u', $name) ?: [];
        if (count($parts) === 1) {
            return $parts[0];
        }
        $last = array_pop($parts);
        $initials = self::initials(implode(' ', $parts));
        return trim($last . ', ' . $initials);
    }

    private static function ieeeName(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            return '';
        }

        if (str_contains($name, ',')) {
            [$last, $first] = array_map('trim', explode(',', $name, 2));
            return trim(self::initials($first, false) . ' ' . $last);
        }

        $parts = preg_split('/\s+/u', $name) ?: [];
        if (count($parts) === 1) {
            return $parts[0];
        }
        $last = array_pop($parts);
        $first = implode(' ', $parts);
        return trim(self::initials($first, false) . ' ' . $last);
    }

    private static function initials(string $first, bool $withDots = true): string
    {
        $first = trim($first);
        if ($first === '') {
            return '';
        }
        $parts = preg_split('/\s+/u', $first) ?: [];
        $out = [];
        foreach ($parts as $p) {
            $p = trim((string)$p);
            if ($p === '') {
                continue;
            }
            $c = self::substr($p, 0, 1);
            if ($c === '') {
                continue;
            }
            $out[] = $c . '.';
        }
        $s = implode(' ', $out);
        return $s;
    }

    private static function substr(string $s, int $start, int $length): string
    {
        if (function_exists('mb_substr')) {
            return (string)mb_substr($s, $start, $length);
        }
        return (string)substr($s, $start, $length);
    }
}
