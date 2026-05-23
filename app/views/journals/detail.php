<?php
$journal = $journal ?? [];
$recommendations = $recommendations ?? [];
$apa = (string)($apa ?? '');
$ieee = (string)($ieee ?? '');
$isFav = (string)($journal['is_favorite'] ?? '0') === '1';
$id = (int)($journal['id'] ?? 0);
$doi = trim((string)($journal['doi'] ?? ''));
$pdf = trim((string)($journal['pdf_url'] ?? ''));
?>

<div class="d-flex justify-content-between align-items-start gap-3 mb-3">
  <div>
    <div class="fw-semibold fs-5"><?= e((string)($journal['title'] ?? '')) ?></div>
    <div class="small text-secondary">
      <?= e((string)($journal['author'] ?? '')) ?>
      <?php if (!empty($journal['year'])): ?>
        • <?= e((string)($journal['year'] ?? '')) ?>
      <?php endif; ?>
    </div>
    <div class="small text-secondary"><?= e((string)($journal['source'] ?? '')) ?></div>
    <div class="d-flex flex-wrap gap-1 mt-2">
      <?php if (!empty($journal['category'])): ?>
        <span class="badge text-bg-light border"><?= e((string)$journal['category']) ?></span>
      <?php endif; ?>
      <?php if (!empty($journal['indexed_by'])): ?>
        <span class="badge text-bg-light border"><?= e((string)$journal['indexed_by']) ?></span>
      <?php endif; ?>
      <?php if ($pdf !== ''): ?>
        <span class="badge text-bg-success">Open Access</span>
      <?php endif; ?>
    </div>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a class="btn btn-sm btn-outline-secondary" href="<?= e(url('journals/index')) ?>"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
    <button class="btn btn-sm <?= $isFav ? 'btn-primary' : 'btn-outline-primary' ?>" id="btnFav" type="button" data-id="<?= (int)$id ?>">
      <i class="bi <?= $isFav ? 'bi-bookmark-heart-fill' : 'bi-bookmark-heart' ?> me-1"></i><?= $isFav ? 'Favorit' : 'Simpan Favorit' ?>
    </button>
    <?php if ($pdf !== ''): ?>
      <a class="btn btn-sm btn-outline-success" href="<?= e($pdf) ?>" target="_blank" rel="noopener">
        <i class="bi bi-file-earmark-pdf me-1"></i>PDF
      </a>
    <?php endif; ?>
    <?php if ($doi !== ''): ?>
      <a class="btn btn-sm btn-outline-secondary" href="<?= e('https://doi.org/' . $doi) ?>" target="_blank" rel="noopener">
        <i class="bi bi-link-45deg me-1"></i>DOI
      </a>
    <?php endif; ?>
  </div>
</div>

<div class="row g-3">
  <div class="col-12 col-lg-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent fw-semibold">Abstrak</div>
      <div class="card-body">
        <?php if (trim((string)($journal['abstract'] ?? '')) === ''): ?>
          <div class="text-secondary">Tidak ada abstrak.</div>
        <?php else: ?>
          <div class="small" style="white-space: pre-wrap;"><?= e((string)$journal['abstract']) ?></div>
        <?php endif; ?>
      </div>
    </div>

    <div class="card border-0 shadow-sm mt-3">
      <div class="card-header bg-transparent fw-semibold d-flex justify-content-between align-items-center">
        <div>Export Sitasi</div>
        <div class="small text-secondary">APA & IEEE</div>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-12">
            <div class="fw-semibold mb-1">APA</div>
            <div class="input-group">
              <input class="form-control" id="citeApa" value="<?= e($apa) ?>" readonly>
              <button class="btn btn-outline-secondary" type="button" data-copy="#citeApa"><i class="bi bi-clipboard me-1"></i>Copy</button>
              <a class="btn btn-outline-secondary" href="<?= e(url('journals/export-citation', ['id' => $id, 'style' => 'apa'])) ?>"><i class="bi bi-download me-1"></i>Export</a>
            </div>
          </div>
          <div class="col-12">
            <div class="fw-semibold mb-1">IEEE</div>
            <div class="input-group">
              <input class="form-control" id="citeIeee" value="<?= e($ieee) ?>" readonly>
              <button class="btn btn-outline-secondary" type="button" data-copy="#citeIeee"><i class="bi bi-clipboard me-1"></i>Copy</button>
              <a class="btn btn-outline-secondary" href="<?= e(url('journals/export-citation', ['id' => $id, 'style' => 'ieee'])) ?>"><i class="bi bi-download me-1"></i>Export</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <div class="fw-semibold">Rekomendasi Terkait</div>
        <button class="btn btn-sm btn-outline-secondary" id="btnAiRank" type="button"><i class="bi bi-stars me-1"></i>AI</button>
      </div>
      <div class="card-body">
        <div id="recLoading" class="text-center py-4 d-none">
          <div class="spinner-border" role="status"></div>
          <div class="small text-secondary mt-2">Mencari jurnal serupa…</div>
        </div>
        <div id="recList" class="vstack gap-2">
          <?php if (!$recommendations): ?>
            <div class="text-secondary small">Belum ada rekomendasi.</div>
          <?php else: ?>
            <?php foreach ($recommendations as $r): ?>
              <a class="card border-0 shadow-sm text-decoration-none" href="<?= e(url('journals/detail', ['id' => (int)($r['id'] ?? 0)])) ?>">
                <div class="card-body">
                  <div class="fw-semibold small"><?= e((string)($r['title'] ?? '')) ?></div>
                  <div class="small text-secondary"><?= e((string)($r['author'] ?? '')) ?><?= !empty($r['year']) ? (' • ' . e((string)$r['year'])) : '' ?></div>
                  <?php if (!empty($r['category'])): ?>
                    <div class="mt-1"><span class="badge text-bg-light border"><?= e((string)$r['category']) ?></span></div>
                  <?php endif; ?>
                </div>
              </a>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  (function () {
    const favUrl = <?= json_encode(url('journals/toggle-favorite'), JSON_UNESCAPED_UNICODE) ?>;
    const recUrl = <?= json_encode(url('journals/api-recommendations', ['id' => $id]), JSON_UNESCAPED_UNICODE) ?>;
    const detailBase = <?= json_encode(url('journals/detail'), JSON_UNESCAPED_UNICODE) ?>;

    $(document).on('click', '[data-copy]', function () {
      const sel = $(this).data('copy');
      const el = document.querySelector(sel);
      if (!el) return;
      APP.copyToClipboard(el.value || '');
    });

    $('#btnFav').on('click', function () {
      const id = $(this).data('id');
      $.ajax({
        method: 'POST',
        url: favUrl,
        dataType: 'json',
        data: { journal_id: id, _csrf: APP.csrfToken }
      }).done((res) => {
        if (!res || !res.ok) return;
        location.reload();
      }).fail(APP.ajaxFail);
    });

    function renderRec(items) {
      if (!items || !items.length) {
        return '<div class="text-secondary small">Belum ada rekomendasi.</div>';
      }
      const out = [];
      items.forEach((it) => {
        const title = APP.escapeHtml(it.title || '');
        const author = APP.escapeHtml(it.author || '');
        const year = APP.escapeHtml(it.year || '');
        const abs = APP.escapeHtml(it.abstract_short || '');
        const cat = APP.escapeHtml(it.category || '');
        const href = detailBase + '&id=' + encodeURIComponent(it.id);
        out.push(`
          <a class="card border-0 shadow-sm text-decoration-none" href="${href}">
            <div class="card-body">
              <div class="fw-semibold small">${title}</div>
              <div class="small text-secondary">${author}${author && year ? ' • ' : ''}${year}</div>
              ${cat ? `<div class="mt-1"><span class="badge text-bg-light border">${cat}</span></div>` : ''}
              <div class="small text-secondary mt-2">${abs}</div>
            </div>
          </a>
        `);
      });
      return out.join('');
    }

    function loadAiRecs() {
      $('#recLoading').removeClass('d-none');
      $('#recList').empty();
      $.getJSON(recUrl + '&ai=1').done((res) => {
        $('#recLoading').addClass('d-none');
        if (!res || !res.ok) {
          $('#recList').html('<div class="text-secondary small">Gagal memuat rekomendasi AI.</div>');
          return;
        }
        $('#recList').html(renderRec(res.items || []));
      }).fail(() => {
        $('#recLoading').addClass('d-none');
        $('#recList').html('<div class="text-secondary small">Gagal memuat rekomendasi AI.</div>');
      });
    }

    $('#btnAiRank').on('click', function () {
      loadAiRecs();
    });
  })();
</script>

