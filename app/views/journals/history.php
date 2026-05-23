<?php
$items = $items ?? [];

function journalHistoryLabel(array $q): string
{
  $c = $q['criteria'] ?? [];
  $f = $q['filters'] ?? [];

  $parts = [];
  foreach (['title' => 'Judul', 'keyword' => 'Kata kunci', 'author' => 'Penulis', 'year' => 'Tahun', 'topic' => 'Topik'] as $k => $label) {
    $v = trim((string)($c[$k] ?? ''));
    if ($v !== '') {
      $parts[] = $label . ': ' . $v;
    }
  }
  if (trim((string)($f['scope'] ?? '')) !== '') {
    $parts[] = 'Skala: ' . (string)$f['scope'];
  }
  if ((string)($f['sinta'] ?? '') === '1') $parts[] = 'SINTA';
  if ((string)($f['scopus'] ?? '') === '1') $parts[] = 'Scopus';
  if ((string)($f['open_access'] ?? '') === '1') $parts[] = 'Open Access';
  if ((string)($f['latest'] ?? '') === '1') $parts[] = 'Tahun terbaru';
  if (trim((string)($f['year_from'] ?? '')) !== '' || trim((string)($f['year_to'] ?? '')) !== '') {
    $parts[] = 'Rentang: ' . trim((string)($f['year_from'] ?? '')) . '–' . trim((string)($f['year_to'] ?? ''));
  }

  return $parts ? implode(' • ', $parts) : 'Pencarian';
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <div class="fw-semibold fs-5">Riwayat Pencarian Jurnal</div>
    <div class="small text-secondary">Klik untuk menjalankan ulang pencarian.</div>
  </div>
  <a class="btn btn-sm btn-outline-secondary" href="<?= e(url('journals/index')) ?>"><i class="bi bi-search me-1"></i>Cari Jurnal</a>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body">
    <?php if (!$items): ?>
      <div class="text-center py-5">
        <div class="fw-semibold">Belum ada riwayat.</div>
        <div class="small text-secondary">Mulai cari jurnal, riwayat akan tersimpan otomatis.</div>
      </div>
    <?php else: ?>
      <div class="vstack gap-2">
        <?php foreach ($items as $h): ?>
          <?php
            $q = (array)($h['query'] ?? []);
            $criteria = (array)($q['criteria'] ?? []);
            $filters = (array)($q['filters'] ?? []);
            $includeExternal = (int)($q['include_external'] ?? 0);
            $linkQuery = array_merge($criteria, $filters, ['include_external' => $includeExternal ? '1' : '']);
          ?>
          <div class="card border-0 shadow-sm">
            <div class="card-body d-flex justify-content-between gap-3">
              <div class="flex-grow-1">
                <div class="fw-semibold"><?= e(journalHistoryLabel($q)) ?></div>
                <div class="small text-secondary">
                  <?= e((string)($h['created_at'] ?? '')) ?> • <?= (int)($h['total_results'] ?? 0) ?> hasil (lokal)
                  <?php if ($includeExternal): ?>
                    • + Google Scholar
                  <?php endif; ?>
                </div>
              </div>
              <div>
                <a class="btn btn-sm btn-outline-primary" href="<?= e(url('journals/index', $linkQuery)) ?>">
                  <i class="bi bi-arrow-repeat me-1"></i>Jalankan
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

