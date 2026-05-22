<?php
$doc = $doc ?? [];
$id = (int)($doc['id'] ?? 0);
?>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
  <div>
    <div class="fw-semibold fs-5"><?= e($doc['title'] ?? '') ?></div>
    <div class="small text-secondary">Tipe: <?= e(strtoupper((string)($doc['type'] ?? ''))) ?> • Update: <?= e((string)($doc['updated_at'] ?? $doc['created_at'] ?? '')) ?></div>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <a class="btn btn-outline-secondary" href="<?= e(url('documents/index')) ?>"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
    <a class="btn btn-outline-primary" href="<?= e(url('documents/edit', ['id' => $id])) ?>"><i class="bi bi-pencil me-1"></i>Edit</a>
    <button class="btn btn-outline-secondary" type="button" id="btnCopyDoc"><i class="bi bi-clipboard me-1"></i>Copy</button>
    <a class="btn btn-outline-secondary" href="<?= e(url('export/txt', ['id' => $id])) ?>"><i class="bi bi-filetype-txt me-1"></i>TXT</a>
    <a class="btn btn-outline-secondary" href="<?= e(url('export/doc', ['id' => $id])) ?>"><i class="bi bi-filetype-doc me-1"></i>DOC</a>
    <a class="btn btn-outline-secondary" href="<?= e(url('export/pdf', ['id' => $id])) ?>" target="_blank"><i class="bi bi-filetype-pdf me-1"></i>PDF</a>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body">
    <pre class="doc-pre mb-0" id="docContent"><?= e($doc['content'] ?? '') ?></pre>
  </div>
</div>

<script>
  $(function () {
    $('#btnCopyDoc').on('click', function () {
      APP.copyToClipboard($('#docContent').text() || '');
    });
  });
</script>

