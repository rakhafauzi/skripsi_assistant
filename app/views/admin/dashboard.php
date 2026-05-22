<?php
$totalUsers = (int)($totalUsers ?? 0);
$totalDocs = (int)($totalDocs ?? 0);
$totalAi = (int)($totalAi ?? 0);
$totalTokens = (int)($totalTokens ?? 0);
$latestAi = $latestAi ?? [];
$latestDocs = $latestDocs ?? [];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <div class="fw-semibold fs-5">Dashboard Admin</div>
    <div class="small text-secondary">Statistik penggunaan aplikasi.</div>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-12 col-md-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="text-secondary small">Total Users</div>
        <div class="fs-3 fw-bold"><?= e((string)$totalUsers) ?></div>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="text-secondary small">Total Dokumen</div>
        <div class="fs-3 fw-bold"><?= e((string)$totalDocs) ?></div>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="text-secondary small">History AI</div>
        <div class="fs-3 fw-bold"><?= e((string)$totalAi) ?></div>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="text-secondary small">Total Tokens</div>
        <div class="fs-3 fw-bold"><?= e((string)$totalTokens) ?></div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-12 col-lg-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <div class="fw-semibold">Dokumen Terbaru</div>
        <a class="btn btn-sm btn-outline-secondary" href="<?= e(url('admin/documents')) ?>">Lihat semua</a>
      </div>
      <div class="card-body">
        <?php if (!$latestDocs): ?>
          <div class="text-secondary">Belum ada dokumen.</div>
        <?php else: ?>
          <div class="list-group list-group-flush">
            <?php foreach ($latestDocs as $d): ?>
              <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" href="<?= e(url('admin/document-detail', ['id' => (int)$d['id']])) ?>">
                <div>
                  <div class="fw-semibold"><?= e((string)($d['title'] ?? '')) ?></div>
                  <div class="small text-secondary">
                    <?= e((string)($d['user_name'] ?? '')) ?> • <?= e(strtoupper((string)($d['type'] ?? ''))) ?> • <?= e((string)($d['updated_at'] ?? $d['created_at'] ?? '')) ?>
                  </div>
                </div>
                <i class="bi bi-chevron-right text-secondary"></i>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <div class="fw-semibold">History AI Terbaru</div>
        <a class="btn btn-sm btn-outline-secondary" href="<?= e(url('admin/ai-history')) ?>">Lihat semua</a>
      </div>
      <div class="card-body">
        <?php if (!$latestAi): ?>
          <div class="text-secondary">Belum ada history AI.</div>
        <?php else: ?>
          <div class="list-group list-group-flush">
            <?php foreach ($latestAi as $h): ?>
              <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" href="<?= e(url('admin/ai-history-detail', ['id' => (int)$h['id']])) ?>">
                <div>
                  <div class="fw-semibold"><?= e((string)($h['user_name'] ?? '')) ?> • <?= e(strtoupper((string)($h['feature'] ?? ''))) ?></div>
                  <div class="small text-secondary"><?= e((string)($h['model'] ?? '')) ?> • <?= e((string)($h['total_tokens'] ?? 0)) ?> tokens • <?= e((string)($h['created_at'] ?? '')) ?></div>
                </div>
                <i class="bi bi-chevron-right text-secondary"></i>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

