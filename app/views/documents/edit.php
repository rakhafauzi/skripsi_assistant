<?php
$doc = $doc ?? [];
$id = (int)($doc['id'] ?? 0);
?>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
  <div>
    <div class="fw-semibold fs-5">Edit Dokumen</div>
    <div class="small text-secondary">Perubahan akan tersimpan ke database.</div>
  </div>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary" href="<?= e(url('documents/detail', ['id' => $id])) ?>">Batal</a>
    <button class="btn btn-primary" form="formEditDoc" type="submit"><i class="bi bi-save me-1"></i>Simpan</button>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body">
    <form id="formEditDoc" method="post" action="<?= e(url('documents/update')) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= e((string)$id) ?>">

      <div class="mb-3">
        <label class="form-label">Judul</label>
        <input class="form-control" name="title" value="<?= e($doc['title'] ?? '') ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Konten</label>
        <textarea class="form-control font-monospace" name="content" id="docEditContent" rows="18" required><?= e($doc['content'] ?? '') ?></textarea>
        <div class="small text-secondary mt-2">Auto-save draft aktif (tersimpan di browser).</div>
      </div>
    </form>
  </div>
</div>

<script>
  $(function () {
    const key = 'draft:doc:' + <?= (int)$id ?>;
    const $content = $('#docEditContent');

    const cached = localStorage.getItem(key);
    if (cached && cached.length > 0 && cached !== $content.val()) {
      $content.val(cached);
    }

    let t = null;
    $content.on('input', function () {
      clearTimeout(t);
      t = setTimeout(() => localStorage.setItem(key, $content.val() || ''), 400);
    });

    $('#formEditDoc').on('submit', function () {
      localStorage.removeItem(key);
    });
  });
</script>

