<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;

final class ScholarClient
{
    public function isEnabled(): bool
    {
        $provider = strtolower(trim((string)($_SERVER['SCHOLAR_PROVIDER'] ?? '')));
        if ($provider === '') {
            $provider = 'serpapi';
        }
        if ($provider !== 'serpapi') {
            return false;
        }
        $key = $this->getApiKey();
        return $key !== '';
    }

    public function search(array $criteria, array $filters, int $page, int $perPage): array
    {
        if (!$this->isEnabled()) {
            return ['ok' => true, 'items' => [], 'warning' => 'Integrasi Google Scholar belum dikonfigurasi.'];
        }

        $q = trim((string)($criteria['keyword'] ?? ''));
        if ($q === '') {
            $q = trim((string)($criteria['title'] ?? ''));
        }
        if ($q === '') {
            $q = trim((string)($criteria['topic'] ?? ''));
        }
        if ($q === '') {
            $q = trim((string)($criteria['author'] ?? ''));
        }
        if ($q === '') {
            return ['ok' => true, 'items' => []];
        }

        $page = max(1, $page);
        $perPage = max(1, min(20, $perPage));
        $start = ($page - 1) * $perPage;

        $params = [
            'engine' => 'google_scholar',
            'q' => $q,
            'api_key' => $this->getApiKey(),
            'start' => $start,
            'num' => $perPage,
        ];

        $yearFrom = trim((string)($filters['year_from'] ?? ''));
        $yearTo = trim((string)($filters['year_to'] ?? ''));
        if ($yearFrom !== '' && ctype_digit($yearFrom)) {
            $params['as_ylo'] = (int)$yearFrom;
        }
        if ($yearTo !== '' && ctype_digit($yearTo)) {
            $params['as_yhi'] = (int)$yearTo;
        }

        $url = 'https://serpapi.com/search.json?' . http_build_query($params);
        if (!function_exists('curl_init')) {
            return ['ok' => false, 'error' => 'PHP ext-curl belum aktif.'];
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 25,
        ]);
        $raw = curl_exec($ch);
        $errno = curl_errno($ch);
        $err = curl_error($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false || $errno) {
            return ['ok' => false, 'error' => 'cURL error: ' . ($err ?: (string)$errno)];
        }

        if ($status < 200 || $status >= 300) {
            return ['ok' => false, 'error' => 'HTTP ' . $status];
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return ['ok' => false, 'error' => 'Response Google Scholar tidak valid.'];
        }

        $results = $data['organic_results'] ?? [];
        if (!is_array($results)) {
            $results = [];
        }

        $items = [];
        foreach ($results as $r) {
            if (!is_array($r)) {
                continue;
            }
            $title = (string)($r['title'] ?? '');
            if ($title === '') {
                continue;
            }

            $snippet = (string)($r['snippet'] ?? '');
            $link = (string)($r['link'] ?? '');

            $authors = '';
            $year = null;
            $source = 'Google Scholar';
            $pub = $r['publication_info'] ?? null;
            if (is_array($pub)) {
                $source = (string)($pub['summary'] ?? $source);
                $authArr = $pub['authors'] ?? null;
                if (is_array($authArr)) {
                    $names = [];
                    foreach ($authArr as $a) {
                        if (is_array($a) && !empty($a['name'])) {
                            $names[] = (string)$a['name'];
                        }
                    }
                    $authors = implode('; ', $names);
                }
            }

            $yearFromText = $r['year'] ?? null;
            if (is_int($yearFromText)) {
                $year = $yearFromText;
            } elseif (is_string($yearFromText) && ctype_digit($yearFromText)) {
                $year = (int)$yearFromText;
            } else {
                if (preg_match('/\b(19\d{2}|20\d{2})\b/', $snippet, $m)) {
                    $year = (int)$m[1];
                }
            }

            $pdfUrl = '';
            $resources = $r['resources'] ?? null;
            if (is_array($resources)) {
                foreach ($resources as $res) {
                    if (!is_array($res)) {
                        continue;
                    }
                    $file = strtolower((string)($res['file_format'] ?? ''));
                    $resLink = (string)($res['link'] ?? '');
                    if ($file === 'pdf' && $resLink !== '') {
                        $pdfUrl = $resLink;
                        break;
                    }
                }
            }

            $items[] = [
                'external' => true,
                'external_provider' => 'google_scholar',
                'title' => $title,
                'author' => $authors,
                'abstract' => $snippet,
                'year' => $year,
                'source' => $source,
                'doi' => '',
                'pdf_url' => $pdfUrl,
                'category' => (string)($criteria['topic'] ?? ''),
                'indexed_by' => '',
                'link' => $link,
            ];
        }

        return ['ok' => true, 'items' => $items];
    }

    private function getApiKey(): string
    {
        $key = trim((string)(new Setting())->get('serpapi_token', ''));
        if ($key !== '') {
            return $key;
        }
        return trim((string)($_SERVER['SERPAPI_KEY'] ?? ''));
    }
}
