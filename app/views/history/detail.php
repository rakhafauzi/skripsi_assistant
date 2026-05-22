<?php
$item = $item ?? [];
$id = (int)($item['id'] ?? 0);
?>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
  <div>
    <div class="fw-semibold fs-5">History AI #<?= e((string)$id) ?></div>
    <div class="small text-secondary">
      Feature: <?= e(strtoupper((string)($item['feature'] ?? ''))) ?> • Model: <?= e((string)($item['model'] ?? '')) ?> • Token: <?= e((string)($item['total_tokens'] ?? 0)) ?>
    </div>
  </div>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary" href="<?= e(url('history/index')) ?>"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
    <button class="btn btn-outline-secondary" type="button" id="btnCopyPrompt"><i class="bi bi-clipboard me-1"></i>Copy Prompt</button>
    <button class="btn btn-outline-secondary" type="button" id="btnCopyResponse"><i class="bi bi-clipboard me-1"></i>Copy Hasil</button>
  </div>
</div>

<div class="row g-3">
  <div class="col-12 col-lg-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent fw-semibold">Prompt</div>
      <div class="card-body">
        <pre class="doc-pre mb-0" id="promptText"><?= e((string)($item['prompt'] ?? '')) ?></pre>
      </div>
    </div>
  </div>
  <div class="col-12 col-lg-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent fw-semibold">Response</div>
      <div class="card-body">
        <pre class="doc-pre mb-0" id="responseText"><?= e((string)($item['response'] ?? '')) ?></pre>
      </div>
    </div>
  </div>
</div>

<script>
  $(function () {
    $('#btnCopyPrompt').on('click', () => APP.copyToClipboard($('#promptText').text() || ''));
    $('#btnCopyResponse').on('click', () => APP.copyToClipboard($('#responseText').text() || ''));
  });
</script>

