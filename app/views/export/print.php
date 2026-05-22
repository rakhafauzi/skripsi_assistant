<?php
$doc = $doc ?? [];
?>
<div class="container py-4">
  <div class="no-print d-flex justify-content-between align-items-center mb-3">
    <div>
      <div class="fw-bold"><?= e($doc['title'] ?? 'Dokumen') ?></div>
      <div class="small text-secondary">Halaman ini akan membuka dialog Print. Pilih "Save as PDF" untuk export PDF.</div>
    </div>
    <a class="btn btn-sm btn-outline-secondary" href="<?= e(url('documents/detail', ['id' => (int)($doc['id'] ?? 0)])) ?>">Kembali</a>
  </div>
  <hr class="no-print">
  <div class="doc-body bg-white p-4 border rounded-3 shadow-sm">
<?= e($doc['content'] ?? '') ?>
  </div>
</div>

