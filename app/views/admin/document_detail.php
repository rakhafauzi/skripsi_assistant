<?php
$doc = $doc ?? [];
$id = (int)($doc['id'] ?? 0);
?>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
  <div>
    <div class="fw-semibold fs-5"><?= e((string)($doc['title'] ?? '')) ?></div>
    <div class="small text-secondary">
      Mahasiswa: <?= e((string)($doc['user_name'] ?? '')) ?> (<?= e((string)($doc['user_email'] ?? '')) ?>) •
      Tipe: <?= e(strtoupper((string)($doc['type'] ?? ''))) ?>
    </div>
  </div>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary" href="<?= e(url('admin/documents')) ?>"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
    <a class="btn btn-outline-secondary" href="<?= e(url('export/txt', ['id' => $id])) ?>"><i class="bi bi-filetype-txt me-1"></i>TXT</a>
    <a class="btn btn-outline-secondary" href="<?= e(url('export/doc', ['id' => $id])) ?>"><i class="bi bi-filetype-doc me-1"></i>DOC</a>
    <a class="btn btn-outline-secondary" href="<?= e(url('export/pdf', ['id' => $id])) ?>" target="_blank"><i class="bi bi-filetype-pdf me-1"></i>PDF</a>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body">
    <pre class="doc-pre mb-0"><?= e((string)($doc['content'] ?? '')) ?></pre>
  </div>
</div>

