<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Journal extends Model
{
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM journals WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByIdWithFavorite(int $id, int $userId): ?array
    {
        $stmt = $this->db->prepare('
            SELECT j.*,
                   IF(f.id IS NULL, 0, 1) AS is_favorite
            FROM journals j
            LEFT JOIN journal_favorites f
              ON f.journal_id = j.id AND f.user_id = :user_id
            WHERE j.id = :id
            LIMIT 1
        ');
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function search(array $criteria, array $filters, int $page, int $perPage, int $userId): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(50, $perPage));
        $offset = ($page - 1) * $perPage;

        $where = [];
        $params = ['user_id' => $userId];

        $title = trim((string)($criteria['title'] ?? ''));
        if ($title !== '') {
            $where[] = 'j.title LIKE :title';
            $params['title'] = '%' . $title . '%';
        }

        $author = trim((string)($criteria['author'] ?? ''));
        if ($author !== '') {
            $where[] = 'j.author LIKE :author';
            $params['author'] = '%' . $author . '%';
        }

        $keyword = trim((string)($criteria['keyword'] ?? ''));
        if ($keyword !== '') {
            $where[] = '(j.title LIKE :kw_title OR j.abstract LIKE :kw_abs OR j.category LIKE :kw_cat OR j.source LIKE :kw_source OR j.doi LIKE :kw_doi)';
            $kw = '%' . $keyword . '%';
            $params['kw_title'] = $kw;
            $params['kw_abs'] = $kw;
            $params['kw_cat'] = $kw;
            $params['kw_source'] = $kw;
            $params['kw_doi'] = $kw;
        }

        $topic = trim((string)($criteria['topic'] ?? ''));
        if ($topic !== '') {
            $where[] = 'j.category LIKE :topic';
            $params['topic'] = '%' . $topic . '%';
        }

        $year = trim((string)($criteria['year'] ?? ''));
        if ($year !== '' && ctype_digit($year)) {
            $where[] = 'j.year = :year';
            $params['year'] = (int)$year;
        }

        $yearFrom = trim((string)($filters['year_from'] ?? ''));
        if ($yearFrom !== '' && ctype_digit($yearFrom)) {
            $where[] = 'j.year >= :year_from';
            $params['year_from'] = (int)$yearFrom;
        }

        $yearTo = trim((string)($filters['year_to'] ?? ''));
        if ($yearTo !== '' && ctype_digit($yearTo)) {
            $where[] = 'j.year <= :year_to';
            $params['year_to'] = (int)$yearTo;
        }

        $openAccess = (string)($filters['open_access'] ?? '');
        if ($openAccess === '1') {
            $where[] = "COALESCE(NULLIF(j.pdf_url, ''), '') <> ''";
        }

        $filterSinta = (string)($filters['sinta'] ?? '');
        if ($filterSinta === '1') {
            $where[] = "LOWER(j.indexed_by) LIKE '%sinta%'";
        }

        $filterScopus = (string)($filters['scopus'] ?? '');
        if ($filterScopus === '1') {
            $where[] = "LOWER(j.indexed_by) LIKE '%scopus%'";
        }

        $scope = strtolower(trim((string)($filters['scope'] ?? '')));
        if ($scope === 'nasional') {
            $where[] = "(LOWER(j.indexed_by) LIKE '%sinta%' OR LOWER(j.indexed_by) LIKE '%nasional%')";
        } elseif ($scope === 'internasional') {
            $where[] = "(LOWER(j.indexed_by) LIKE '%scopus%' OR LOWER(j.indexed_by) LIKE '%internasional%' OR LOWER(j.indexed_by) LIKE '%international%')";
        }

        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        $countStmt = $this->db->prepare('SELECT COUNT(*) FROM journals j ' . $whereSql);
        $countParams = $params;
        unset($countParams['user_id']);
        $countStmt->execute($countParams);
        $total = (int)$countStmt->fetchColumn();

        $sortLatest = (string)($filters['latest'] ?? '') === '1';
        $orderBy = $sortLatest ? 'ORDER BY j.year DESC, j.id DESC' : 'ORDER BY j.id DESC';

        $sql = '
            SELECT j.id, j.title, j.author, j.abstract, j.year, j.source, j.doi, j.pdf_url, j.category, j.indexed_by, j.created_at,
                   IF(f.id IS NULL, 0, 1) AS is_favorite
            FROM journals j
            LEFT JOIN journal_favorites f
              ON f.journal_id = j.id AND f.user_id = :user_id
            ' . $whereSql . '
            ' . $orderBy . '
            LIMIT :limit OFFSET :offset
        ';

        $stmt = $this->db->prepare($sql);
        $execParams = $params;
        $execParams['limit'] = $perPage;
        $execParams['offset'] = $offset;
        $stmt->execute($execParams);
        $items = $stmt->fetchAll() ?: [];

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int)max(1, (int)ceil($total / $perPage)),
        ];
    }

    public function upsertExternal(array $data): int
    {
        $title = trim((string)($data['title'] ?? ''));
        if ($title === '') {
            return 0;
        }

        $doi = trim((string)($data['doi'] ?? ''));
        if ($doi !== '') {
            $stmt = $this->db->prepare('SELECT id FROM journals WHERE doi = :doi LIMIT 1');
            $stmt->execute(['doi' => $doi]);
            $existing = (int)($stmt->fetchColumn() ?: 0);
            if ($existing > 0) {
                $this->updateById($existing, $data);
                return $existing;
            }
        }

        $yearRaw = trim((string)($data['year'] ?? ''));
        $year = (ctype_digit($yearRaw) ? (int)$yearRaw : null);

        $stmt = $this->db->prepare('
            INSERT INTO journals (title, author, abstract, year, source, doi, pdf_url, category, indexed_by)
            VALUES (:title, :author, :abstract, :year, :source, :doi, :pdf_url, :category, :indexed_by)
        ');
        $stmt->execute([
            'title' => $title,
            'author' => (string)($data['author'] ?? ''),
            'abstract' => (string)($data['abstract'] ?? ''),
            'year' => $year,
            'source' => (string)($data['source'] ?? ''),
            'doi' => ($doi !== '' ? $doi : null),
            'pdf_url' => (string)($data['pdf_url'] ?? ''),
            'category' => (string)($data['category'] ?? ''),
            'indexed_by' => (string)($data['indexed_by'] ?? ''),
        ]);

        return (int)$this->db->lastInsertId();
    }

    private function updateById(int $id, array $data): void
    {
        $stmt = $this->db->prepare('
            UPDATE journals
            SET title = :title,
                author = :author,
                abstract = :abstract,
                year = :year,
                source = :source,
                pdf_url = :pdf_url,
                category = :category,
                indexed_by = :indexed_by
            WHERE id = :id
            LIMIT 1
        ');

        $yearRaw = trim((string)($data['year'] ?? ''));
        $year = (ctype_digit($yearRaw) ? (int)$yearRaw : null);

        $stmt->execute([
            'id' => $id,
            'title' => trim((string)($data['title'] ?? '')),
            'author' => (string)($data['author'] ?? ''),
            'abstract' => (string)($data['abstract'] ?? ''),
            'year' => $year,
            'source' => (string)($data['source'] ?? ''),
            'pdf_url' => (string)($data['pdf_url'] ?? ''),
            'category' => (string)($data['category'] ?? ''),
            'indexed_by' => (string)($data['indexed_by'] ?? ''),
        ]);
    }

    public function listCandidatesForRecommendation(int $excludeId, int $limit = 80): array
    {
        $stmt = $this->db->prepare('
            SELECT id, title, author, abstract, year, source, doi, pdf_url, category, indexed_by
            FROM journals
            WHERE id <> :id
            ORDER BY id DESC
            LIMIT :limit
        ');
        $stmt->bindValue('id', $excludeId, \PDO::PARAM_INT);
        $stmt->bindValue('limit', max(1, min(200, $limit)), \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }
}
