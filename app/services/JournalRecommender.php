<?php
declare(strict_types=1);

namespace App\Services;

use App\Helpers\OpenAiHelper;

final class JournalRecommender
{
    public function recommendLocal(array $seed, array $candidates, int $limit = 6): array
    {
        $seedText = $this->buildText($seed);
        if ($seedText === '') {
            return array_slice($candidates, 0, max(1, $limit));
        }

        $seedVec = $this->vectorize($seedText);
        $scored = [];
        foreach ($candidates as $c) {
            $text = $this->buildText($c);
            if ($text === '') {
                continue;
            }
            $vec = $this->vectorize($text);
            $score = $this->cosine($seedVec, $vec);
            $scored[] = ['score' => $score, 'item' => $c];
        }

        usort($scored, static function (array $a, array $b): int {
            return $b['score'] <=> $a['score'];
        });

        $out = [];
        foreach ($scored as $row) {
            if (count($out) >= $limit) {
                break;
            }
            $item = $row['item'];
            $item['_score'] = (float)$row['score'];
            $out[] = $item;
        }
        return $out;
    }

    public function rerankWithAi(array $seed, array $candidates, int $limit = 6): array
    {
        $limit = max(1, min(10, $limit));
        $seedTitle = (string)($seed['title'] ?? '');
        $seedAbs = (string)($seed['abstract'] ?? '');

        $list = [];
        foreach ($candidates as $c) {
            $id = (int)($c['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }
            $list[] = [
                'id' => $id,
                'title' => (string)($c['title'] ?? ''),
                'year' => (string)($c['year'] ?? ''),
                'author' => (string)($c['author'] ?? ''),
                'abstract' => $this->substr((string)($c['abstract'] ?? ''), 0, 600),
                'category' => (string)($c['category'] ?? ''),
            ];
        }
        if (!$list) {
            return [];
        }

        $system = 'Kamu adalah asisten akademik. Pilih jurnal yang paling relevan dan serupa dengan jurnal target berdasarkan topik/keyword dan ringkasan abstrak. Output harus JSON valid.';
        $user = json_encode([
            'target' => [
                'title' => $seedTitle,
                'abstract' => $this->substr($seedAbs, 0, 800),
                'category' => (string)($seed['category'] ?? ''),
            ],
            'candidates' => $list,
            'limit' => $limit,
            'output' => [
                'format' => 'json',
                'schema' => [
                    'recommended_ids' => 'array of integer ids in ranked order',
                ],
            ],
        ], JSON_UNESCAPED_UNICODE);

        $resp = OpenAiHelper::chatCompletion($system, (string)$user, ['max_tokens' => 350, 'temperature' => 0.2]);
        if (!($resp['ok'] ?? false)) {
            return [];
        }

        $raw = (string)($resp['content'] ?? '');
        $decoded = json_decode($raw, true);
        if (!is_array($decoded) || !isset($decoded['recommended_ids']) || !is_array($decoded['recommended_ids'])) {
            return [];
        }

        $rankedIds = array_values(array_filter($decoded['recommended_ids'], static function ($x): bool {
            return is_int($x) || (is_string($x) && ctype_digit($x));
        }));
        if (!$rankedIds) {
            return [];
        }

        $map = [];
        foreach ($candidates as $c) {
            $id = (int)($c['id'] ?? 0);
            if ($id > 0) {
                $map[$id] = $c;
            }
        }

        $out = [];
        foreach ($rankedIds as $rid) {
            $rid = (int)$rid;
            if (!isset($map[$rid])) {
                continue;
            }
            $out[] = $map[$rid];
            if (count($out) >= $limit) {
                break;
            }
        }
        return $out;
    }

    private function buildText(array $j): string
    {
        $title = (string)($j['title'] ?? '');
        $abs = (string)($j['abstract'] ?? '');
        $cat = (string)($j['category'] ?? '');
        $author = (string)($j['author'] ?? '');
        $text = trim($title . ' ' . $cat . ' ' . $author . ' ' . $abs);
        return $text;
    }

    private function tokenize(string $text): array
    {
        $text = $this->lower($text);
        $text = preg_replace('/[^a-z0-9áéíóúàèìòùâêîôûäëïöüçñ\s]+/u', ' ', $text) ?? $text;
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;
        $parts = explode(' ', trim($text));

        $stop = [
            'dan', 'atau', 'yang', 'dengan', 'untuk', 'pada', 'dari', 'dalam', 'oleh', 'sebagai', 'ini', 'itu',
            'the', 'and', 'or', 'of', 'in', 'on', 'to', 'for', 'with', 'a', 'an', 'is', 'are', 'was', 'were',
            'study', 'analysis', 'based', 'using', 'approach', 'method', 'methods', 'result', 'results',
        ];
        $stopMap = array_fill_keys($stop, true);

        $out = [];
        foreach ($parts as $p) {
            $p = trim($p);
            if ($p === '' || $this->len($p) < 3) {
                continue;
            }
            if (isset($stopMap[$p])) {
                continue;
            }
            $out[] = $p;
        }
        return $out;
    }

    private function vectorize(string $text): array
    {
        $tokens = $this->tokenize($text);
        $vec = [];
        foreach ($tokens as $t) {
            $vec[$t] = ($vec[$t] ?? 0) + 1;
        }
        return $vec;
    }

    private function cosine(array $a, array $b): float
    {
        $dot = 0.0;
        $na = 0.0;
        $nb = 0.0;
        foreach ($a as $k => $va) {
            $na += (float)($va * $va);
            if (isset($b[$k])) {
                $dot += (float)($va * (int)$b[$k]);
            }
        }
        foreach ($b as $vb) {
            $nb += (float)($vb * $vb);
        }
        if ($na <= 0.0 || $nb <= 0.0) {
            return 0.0;
        }
        return $dot / (sqrt($na) * sqrt($nb));
    }

    private function lower(string $s): string
    {
        if (function_exists('mb_strtolower')) {
            return (string)mb_strtolower($s);
        }
        return strtolower($s);
    }

    private function len(string $s): int
    {
        if (function_exists('mb_strlen')) {
            return (int)mb_strlen($s);
        }
        return strlen($s);
    }

    private function substr(string $s, int $start, int $length): string
    {
        if (function_exists('mb_substr')) {
            return (string)mb_substr($s, $start, $length);
        }
        return (string)substr($s, $start, $length);
    }
}
