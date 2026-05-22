<?php
$stats = $stats ?? [];
$documents = $documents ?? [];
$aiHistory = $aiHistory ?? [];
$totalDocs = (int)($totalDocs ?? 0);
$progress = (int)($progress ?? 0);
?>

<div class="row g-3 mb-3">
  <div class="col-12 col-md-4">
    <div class="card card-stat border-0 shadow-sm h-100">
      <div class="card-body d-flex flex-column">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="text-secondary small">Total Dokumen</div>
            <div class="fs-3 fw-bold"><?= e((string)$totalDocs) ?></div>
          </div>
          <div class="stat-icon bg-primary-subtle text-primary">
            <i class="bi bi-folder2-open"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-4">
    <div class="card card-stat border-0 shadow-sm h-100">
      <div class="card-body d-flex flex-column">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="text-secondary small">Progress Skripsi</div>
            <div class="fs-3 fw-bold"><?= e((string)$progress) ?>%</div>
          </div>
          <div class="stat-icon bg-success-subtle text-success">
            <i class="bi bi-graph-up"></i>
          </div>
        </div>
        <div class="progress mt-3" style="height: 10px;">
          <div class="progress-bar" role="progressbar" style="width: <?= e((string)$progress) ?>%"></div>
        </div>
        <div class="small text-secondary mt-2 mt-auto">Otomatis dihitung dari dokumen yang tersimpan.</div>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-4">
    <div class="card card-stat border-0 shadow-sm h-100">
      <div class="card-body d-flex flex-column">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="text-secondary small">Quick Menu</div>
            <div class="fw-semibold">Generator AI</div>
          </div>
          <div class="stat-icon bg-warning-subtle text-warning">
            <i class="bi bi-lightning-charge"></i>
          </div>
        </div>
        <div class="d-flex flex-wrap gap-2 mt-3 mt-auto">
          <a class="btn btn-sm btn-primary" href="<?= e(url('generator/title')) ?>">Judul</a>
          <a class="btn btn-sm btn-outline-primary" href="<?= e(url('generator/bab1')) ?>">BAB 1</a>
          <a class="btn btn-sm btn-outline-primary" href="<?= e(url('generator/bab2')) ?>">BAB 2</a>
          <a class="btn btn-sm btn-outline-primary" href="<?= e(url('generator/bab3')) ?>">BAB 3</a>
          <a class="btn btn-sm btn-outline-primary" href="<?= e(url('generator/uml')) ?>">UML</a>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-12 col-lg-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <div class="fw-semibold">Dokumen Terbaru</div>
        <a class="btn btn-sm btn-outline-secondary" href="<?= e(url('documents/index')) ?>">Lihat semua</a>
      </div>
      <div class="card-body">
        <?php if (!$documents): ?>
          <div class="text-secondary">Belum ada dokumen. Mulai dari menu Generator.</div>
        <?php else: ?>
          <div class="list-group list-group-flush">
            <?php foreach ($documents as $d): ?>
              <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" href="<?= e(url('documents/detail', ['id' => (int)$d['id']])) ?>">
                <div>
                  <div class="fw-semibold"><?= e($d['title'] ?? '') ?></div>
                  <div class="small text-secondary"><?= e(strtoupper((string)($d['type'] ?? ''))) ?> • <?= e((string)($d['updated_at'] ?? $d['created_at'] ?? '')) ?></div>
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
        <div class="fw-semibold">Riwayat Generate AI</div>
        <a class="btn btn-sm btn-outline-secondary" href="<?= e(url('history/index')) ?>">Lihat semua</a>
      </div>
      <div class="card-body">
        <?php if (!$aiHistory): ?>
          <div class="text-secondary">Belum ada riwayat AI.</div>
        <?php else: ?>
          <div class="list-group list-group-flush">
            <?php foreach ($aiHistory as $h): ?>
              <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" href="<?= e(url('history/detail', ['id' => (int)$h['id']])) ?>">
                <div>
                  <div class="fw-semibold"><?= e(strtoupper((string)($h['feature'] ?? ''))) ?></div>
                  <div class="small text-secondary">
                    <?= e((string)($h['model'] ?? '')) ?> • <?= e((string)($h['total_tokens'] ?? 0)) ?> tokens • <?= e((string)($h['created_at'] ?? '')) ?>
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
</div>
