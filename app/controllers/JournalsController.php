<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Flash;
use App\Core\Response;
use App\Core\Security;
use App\Models\Journal;
use App\Models\JournalFavorite;
use App\Models\JournalSearchHistory;
use App\Services\CitationFormatter;
use App\Services\JournalRecommender;
use App\Services\ScholarClient;

final class JournalsController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $this->view('journals/search', [
            'scholarEnabled' => (new ScholarClient())->isEnabled(),
        ]);
    }

    public function detail(): void
    {
        $this->requireLogin();
        $id = (int)($_GET['id'] ?? 0);
        $journal = (new Journal())->findByIdWithFavorite($id, (int)Auth::id());
        if (!$journal) {
            Flash::error('Jurnal tidak ditemukan.');
            Response::redirect(url('journals/index'));
        }

        $apa = CitationFormatter::apa($journal);
        $ieee = CitationFormatter::ieee($journal);

        $candidates = (new Journal())->listCandidatesForRecommendation((int)$journal['id'], 80);
        $recommender = new JournalRecommender();
        $recommendations = $recommender->recommendLocal($journal, $candidates, 6);

        $this->view('journals/detail', [
            'journal' => $journal,
            'apa' => $apa,
            'ieee' => $ieee,
            'recommendations' => $recommendations,
        ]);
    }

    public function favorites(): void
    {
        $this->requireLogin();
        $items = (new JournalFavorite())->listByUser((int)Auth::id(), 300);
        $this->view('journals/favorites', ['items' => $items]);
    }

    public function history(): void
    {
        $this->requireLogin();
        $items = (new JournalSearchHistory())->latestByUser((int)Auth::id(), 60);
        $this->view('journals/history', ['items' => $items]);
    }

    public function apiSearch(): void
    {
        // #region debug-point A:api-search-entry
        $traceId = '';
        try {
            $traceId = substr(bin2hex(random_bytes(8)), 0, 12);
        } catch (\Throwable) {
            $traceId = (string)time();
        }
        $envFile = APP_BASE_PATH . '/.dbg/journals-search-http200.env';
        $env = @file_get_contents($envFile);
        $dbgUrl = (is_string($env) && $env !== '' && preg_match('/^DEBUG_SERVER_URL=(.+)$/m', $env, $m)) ? trim((string)$m[1]) : '';
        $dbgSid = (is_string($env) && $env !== '' && preg_match('/^DEBUG_SESSION_ID=(.+)$/m', $env, $m2)) ? trim((string)$m2[1]) : 'journals-search-http200';
        if ($dbgUrl !== '') {
            $payload = json_encode([
                'sessionId' => $dbgSid,
                'runId' => 'pre-fix',
                'hypothesisId' => 'A',
                'traceId' => $traceId,
                'location' => 'app/controllers/JournalsController.php:apiSearch(entry)',
                'msg' => '[DEBUG] apiSearch entry',
                'data' => [
                    'route' => (string)($_GET['r'] ?? ''),
                    'user_id' => (int)Auth::id(),
                    'accept' => (string)($_SERVER['HTTP_ACCEPT'] ?? ''),
                    'xhr' => (string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''),
                    'content_type' => (string)($_SERVER['CONTENT_TYPE'] ?? ''),
                    'cookie_len' => strlen((string)($_SERVER['HTTP_COOKIE'] ?? '')),
                    'session_name' => session_name(),
                    'session_id_len' => strlen((string)session_id()),
                    'query' => $_GET,
                ],
                'ts' => (int)(microtime(true) * 1000),
            ], JSON_UNESCAPED_UNICODE);
            if (is_string($payload) && $payload !== '') {
                @file_get_contents($dbgUrl, false, stream_context_create([
                    'http' => [
                        'method' => 'POST',
                        'header' => "Content-Type: application/json\r\n",
                        'content' => $payload,
                        'timeout' => 0.7,
                    ],
                ]));
            }
        }
        // #endregion

        $this->requireLogin();

        $criteria = [
            'title' => (string)($_GET['title'] ?? ''),
            'keyword' => (string)($_GET['keyword'] ?? ''),
            'author' => (string)($_GET['author'] ?? ''),
            'year' => (string)($_GET['year'] ?? ''),
            'topic' => (string)($_GET['topic'] ?? ''),
        ];
        $filters = [
            'latest' => (string)($_GET['latest'] ?? ''),
            'scope' => (string)($_GET['scope'] ?? ''),
            'sinta' => (string)($_GET['sinta'] ?? ''),
            'scopus' => (string)($_GET['scopus'] ?? ''),
            'open_access' => (string)($_GET['open_access'] ?? ''),
            'year_from' => (string)($_GET['year_from'] ?? ''),
            'year_to' => (string)($_GET['year_to'] ?? ''),
        ];

        $page = (int)($_GET['page'] ?? 1);
        $perPage = (int)($_GET['per_page'] ?? 10);

        $model = new Journal();
        $result = $model->search($criteria, $filters, $page, $perPage, (int)Auth::id());

        $items = $result['items'] ?? [];
        foreach ($items as &$it) {
            $it['external'] = false;
            $it['abstract_short'] = $this->abstractShort((string)($it['abstract'] ?? ''), 220);
        }
        unset($it);

        $external = [];
        $includeExternal = (string)($_GET['include_external'] ?? '') === '1';
        $scholarWarning = '';
        if ($includeExternal && $page === 1) {
            $scholar = new ScholarClient();
            $res = $scholar->search($criteria, $filters, 1, 8);
            if (!($res['ok'] ?? false)) {
                $scholarWarning = (string)($res['error'] ?? 'Gagal mengambil data Google Scholar.');
            } else {
                $external = $res['items'] ?? [];
                $scholarWarning = (string)($res['warning'] ?? '');
                foreach ($external as &$ex) {
                    $ex['abstract_short'] = $this->abstractShort((string)($ex['abstract'] ?? ''), 220);
                    $ex['is_favorite'] = 0;
                }
                unset($ex);
            }
        }

        // #region debug-point D:api-search-after-scholar
        if ($dbgUrl !== '') {
            $payload = json_encode([
                'sessionId' => $dbgSid,
                'runId' => 'pre-fix',
                'hypothesisId' => 'D',
                'traceId' => $traceId,
                'location' => 'app/controllers/JournalsController.php:apiSearch(afterScholar)',
                'msg' => '[DEBUG] apiSearch after scholar',
                'data' => [
                    'include_external' => $includeExternal ? 1 : 0,
                    'page' => $page,
                    'local_items' => is_array($items) ? count($items) : 0,
                    'external_items' => is_array($external) ? count($external) : 0,
                    'scholar_warning' => $scholarWarning,
                ],
                'ts' => (int)(microtime(true) * 1000),
            ], JSON_UNESCAPED_UNICODE);
            if (is_string($payload) && $payload !== '') {
                @file_get_contents($dbgUrl, false, stream_context_create([
                    'http' => [
                        'method' => 'POST',
                        'header' => "Content-Type: application/json\r\n",
                        'content' => $payload,
                        'timeout' => 0.7,
                    ],
                ]));
            }
        }
        // #endregion

        if ($page === 1 && $this->shouldSaveHistory($criteria, $filters)) {
            (new JournalSearchHistory())->create((int)Auth::id(), [
                'criteria' => $criteria,
                'filters' => $filters,
                'include_external' => $includeExternal ? 1 : 0,
            ], (int)($result['total'] ?? 0));
        }

        // #region debug-point C:api-search-before-json
        if ($dbgUrl !== '') {
            $payload = json_encode([
                'sessionId' => $dbgSid,
                'runId' => 'pre-fix',
                'hypothesisId' => 'C',
                'traceId' => $traceId,
                'location' => 'app/controllers/JournalsController.php:apiSearch(beforeJson)',
                'msg' => '[DEBUG] apiSearch about to respond json',
                'data' => [
                    'result_total' => (int)($result['total'] ?? 0),
                    'result_total_pages' => (int)($result['total_pages'] ?? 0),
                    'local_items' => is_array($items) ? count($items) : 0,
                    'external_items' => is_array($external) ? count($external) : 0,
                    'headers_sent' => headers_sent(),
                ],
                'ts' => (int)(microtime(true) * 1000),
            ], JSON_UNESCAPED_UNICODE);
            if (is_string($payload) && $payload !== '') {
                @file_get_contents($dbgUrl, false, stream_context_create([
                    'http' => [
                        'method' => 'POST',
                        'header' => "Content-Type: application/json\r\n",
                        'content' => $payload,
                        'timeout' => 0.7,
                    ],
                ]));
            }
        }
        // #endregion

        Response::json([
            'ok' => true,
            'result' => $result,
            'items' => $items,
            'external_items' => $external,
            'scholar_warning' => $scholarWarning,
        ]);
    }

    public function toggleFavorite(): void
    {
        $this->requireLogin();

        $token = (string)($_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));
        if (!Security::verifyCsrf($token)) {
            Response::json(['ok' => false, 'error' => 'CSRF token tidak valid.'], 419);
            return;
        }

        $journalId = (int)($_POST['journal_id'] ?? 0);
        if ($journalId <= 0) {
            Response::json(['ok' => false, 'error' => 'Input tidak valid.'], 422);
            return;
        }

        $isFav = (new JournalFavorite())->toggle((int)Auth::id(), $journalId);
        Response::json(['ok' => true, 'is_favorite' => $isFav ? 1 : 0]);
    }

    public function saveExternal(): void
    {
        $this->requireLogin();

        $token = (string)($_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));
        if (!Security::verifyCsrf($token)) {
            Response::json(['ok' => false, 'error' => 'CSRF token tidak valid.'], 419);
            return;
        }

        $payload = [
            'title' => (string)($_POST['title'] ?? ''),
            'author' => (string)($_POST['author'] ?? ''),
            'abstract' => (string)($_POST['abstract'] ?? ''),
            'year' => (string)($_POST['year'] ?? ''),
            'source' => (string)($_POST['source'] ?? ''),
            'doi' => (string)($_POST['doi'] ?? ''),
            'pdf_url' => (string)($_POST['pdf_url'] ?? ''),
            'category' => (string)($_POST['category'] ?? ''),
            'indexed_by' => (string)($_POST['indexed_by'] ?? ''),
        ];

        $id = (new Journal())->upsertExternal($payload);
        if ($id <= 0) {
            Response::json(['ok' => false, 'error' => 'Gagal menyimpan jurnal.'], 422);
            return;
        }

        $autoFav = (string)($_POST['favorite'] ?? '') === '1';
        $isFav = 0;
        if ($autoFav) {
            $isFav = (new JournalFavorite())->ensureFavorite((int)Auth::id(), $id) ? 1 : 0;
        }

        Response::json(['ok' => true, 'id' => $id, 'is_favorite' => $isFav]);
    }

    public function apiRecommendations(): void
    {
        $this->requireLogin();

        $id = (int)($_GET['id'] ?? 0);
        $journal = (new Journal())->findById($id);
        if (!$journal) {
            Response::json(['ok' => false, 'error' => 'Jurnal tidak ditemukan.'], 404);
            return;
        }

        $candidates = (new Journal())->listCandidatesForRecommendation($id, 120);
        $recommender = new JournalRecommender();
        $base = $recommender->recommendLocal($journal, $candidates, 10);

        $useAi = (string)($_GET['ai'] ?? '') === '1';
        $ranked = [];
        if ($useAi) {
            $ranked = $recommender->rerankWithAi($journal, array_slice($base, 0, 10), 6);
        }

        $final = $ranked ?: array_slice($base, 0, 6);
        foreach ($final as &$it) {
            $it['abstract_short'] = $this->abstractShort((string)($it['abstract'] ?? ''), 180);
        }
        unset($it);

        Response::json(['ok' => true, 'items' => $final, 'ai' => $useAi ? 1 : 0]);
    }

    public function exportCitation(): void
    {
        $this->requireLogin();
        $id = (int)($_GET['id'] ?? 0);
        $style = strtolower(trim((string)($_GET['style'] ?? 'apa')));
        if (!in_array($style, ['apa', 'ieee'], true)) {
            $style = 'apa';
        }

        $journal = (new Journal())->findById($id);
        if (!$journal) {
            Flash::error('Jurnal tidak ditemukan.');
            Response::redirect(url('journals/index'));
        }

        $citation = ($style === 'ieee') ? CitationFormatter::ieee($journal) : CitationFormatter::apa($journal);
        $filename = $this->safeFilename(($journal['title'] ?? 'citation') . ' - ' . strtoupper($style) . '.txt');

        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $citation;
    }

    private function abstractShort(string $abstract, int $limit): string
    {
        $abstract = trim(preg_replace('/\s+/', ' ', $abstract) ?? $abstract);
        $len = function_exists('mb_strlen') ? (int)mb_strlen($abstract) : strlen($abstract);
        if ($len <= $limit) {
            return $abstract;
        }
        $sub = function_exists('mb_substr') ? (string)mb_substr($abstract, 0, $limit) : (string)substr($abstract, 0, $limit);
        return rtrim($sub) . '…';
    }

    private function shouldSaveHistory(array $criteria, array $filters): bool
    {
        foreach ($criteria as $v) {
            if (trim((string)$v) !== '') {
                return true;
            }
        }
        foreach ($filters as $k => $v) {
            if (in_array($k, ['latest', 'scope', 'sinta', 'scopus', 'open_access', 'year_from', 'year_to'], true) && trim((string)$v) !== '') {
                return true;
            }
        }
        return false;
    }

    private function safeFilename(string $name): string
    {
        $name = preg_replace('/[^\w\s\-\.]+/u', '', $name) ?? 'citation';
        $name = preg_replace('/\s+/', ' ', $name) ?? $name;
        $name = trim($name);
        return $name !== '' ? $name : 'citation';
    }
}
