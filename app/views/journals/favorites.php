<?php
$items = $items ?? [];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <div class="fw-semibold fs-5">Jurnal Favorit</div>
    <div class="small text-secondary">Daftar jurnal yang kamu simpan.</div>
  </div>
  <a class="btn btn-sm btn-outline-secondary" href="<?= e(url('journals/index')) ?>"><i class="bi bi-search me-1"></i>Cari Jurnal</a>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body">
    <?php if (!$items): ?>
      <div class="text-center py-5">
        <div class="fw-semibold">Belum ada favorit.</div>
        <div class="small text-secondary">Simpan jurnal dari halaman pencarian atau detail.</div>
      </div>
    <?php else: ?>
      <div class="vstack gap-2" id="favList">
        <?php foreach ($items as $j): ?>
          <div class="card border-0 shadow-sm">
            <div class="card-body d-flex justify-content-between gap-3">
              <div class="flex-grow-1">
                <div class="fw-semibold"><?= e((string)($j['title'] ?? '')) ?></div>
                <div class="small text-secondary">
                  <?= e((string)($j['author'] ?? '')) ?>
                  <?php if (!empty($j['year'])): ?> • <?= e((string)$j['year']) ?><?php endif; ?>
                </div>
                <div class="small text-secondary"><?= e((string)($j['source'] ?? '')) ?></div>
                <div class="mt-2 d-flex flex-wrap gap-1">
                  <?php if (!empty($j['category'])): ?><span class="badge text-bg-light border"><?= e((string)$j['category']) ?></span><?php endif; ?>
                  <?php if (!empty($j['indexed_by'])): ?><span class="badge text-bg-light border"><?= e((string)$j['indexed_by']) ?></span><?php endif; ?>
                  <?php if (!empty($j['pdf_url'])): ?><span class="badge text-bg-success">Open Access</span><?php endif; ?>
                </div>
              </div>
              <div class="d-flex flex-column gap-2">
                <a class="btn btn-sm btn-outline-secondary" href="<?= e(url('journals/detail', ['id' => (int)($j['id'] ?? 0)])) ?>">Detail</a>
                <button class="btn btn-sm btn-outline-danger btn-unfav" type="button" data-id="<?= (int)($j['id'] ?? 0) ?>">
                  <i class="bi bi-bookmark-x me-1"></i>Hapus
                </button>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
  (function () {
    const favUrl = <?= json_encode(url('journals/toggle-favorite'), JSON_UNESCAPED_UNICODE) ?>;
    $('#favList').on('click', '.btn-unfav', function () {
      const id = $(this).data('id');
      $.ajax({
        method: 'POST',
        url: favUrl,
        dataType: 'json',
        data: { journal_id: id, _csrf: APP.csrfToken }
      }).done((res) => {
        if (res && res.ok) location.reload();
      }).fail(APP.ajaxFail);
    });
  })();
</script>

