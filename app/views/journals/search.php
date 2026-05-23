<?php
$scholarEnabled = (bool)($scholarEnabled ?? false);
$prefill = [
  'title' => (string)($_GET['title'] ?? ''),
  'keyword' => (string)($_GET['keyword'] ?? ''),
  'author' => (string)($_GET['author'] ?? ''),
  'year' => (string)($_GET['year'] ?? ''),
  'topic' => (string)($_GET['topic'] ?? ''),
  'latest' => (string)($_GET['latest'] ?? ''),
  'scope' => (string)($_GET['scope'] ?? ''),
  'sinta' => (string)($_GET['sinta'] ?? ''),
  'scopus' => (string)($_GET['scopus'] ?? ''),
  'open_access' => (string)($_GET['open_access'] ?? ''),
  'year_from' => (string)($_GET['year_from'] ?? ''),
  'year_to' => (string)($_GET['year_to'] ?? ''),
  'include_external' => (string)($_GET['include_external'] ?? ''),
];

// #region debug-point C:client-debug-config
$dbgUrl = '';
$dbgSid = 'journals-search-http200';
$envFile = defined('APP_BASE_PATH') ? (APP_BASE_PATH . '/.dbg/journals-search-http200.env') : '';
if ($envFile !== '' && is_file($envFile)) {
  $env = @file_get_contents($envFile);
  if (is_string($env) && $env !== '') {
    if (preg_match('/^DEBUG_SERVER_URL=(.+)$/m', $env, $m)) $dbgUrl = trim((string)$m[1]);
    if (preg_match('/^DEBUG_SESSION_ID=(.+)$/m', $env, $m2)) $dbgSid = trim((string)$m2[1]);
  }
}
// #endregion
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <div class="fw-semibold fs-5">Pencarian Jurnal Ilmiah</div>
    <div class="small text-secondary">Cari berdasarkan judul, kata kunci, penulis, tahun, dan topik penelitian.</div>
  </div>
  <div class="d-flex gap-2">
    <a class="btn btn-sm btn-outline-secondary" href="<?= e(url('journals/history')) ?>"><i class="bi bi-clock-history me-1"></i>Riwayat</a>
    <a class="btn btn-sm btn-outline-secondary" href="<?= e(url('journals/favorites')) ?>"><i class="bi bi-bookmark-heart me-1"></i>Favorit</a>
  </div>
</div>

<div class="row g-3">
  <div class="col-12 col-lg-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent fw-semibold">Filter Pencarian</div>
      <div class="card-body">
        <form id="journalSearchForm">
          <div class="mb-3">
            <label class="form-label">Judul</label>
            <input class="form-control" name="title" value="<?= e($prefill['title']) ?>" placeholder="Contoh: Sistem Rekomendasi Jurnal">
          </div>
          <div class="mb-3">
            <label class="form-label">Kata Kunci</label>
            <input class="form-control" name="keyword" value="<?= e($prefill['keyword']) ?>" placeholder="Contoh: NLP, TF-IDF, clustering">
          </div>
          <div class="mb-3">
            <label class="form-label">Penulis</label>
            <input class="form-control" name="author" value="<?= e($prefill['author']) ?>" placeholder="Contoh: Ahmad, Sari">
          </div>

          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label">Tahun</label>
              <input class="form-control" name="year" value="<?= e($prefill['year']) ?>" inputmode="numeric" placeholder="2024">
            </div>
            <div class="col-6">
              <label class="form-label">Topik/Kategori</label>
              <input class="form-control" name="topic" value="<?= e($prefill['topic']) ?>" placeholder="Contoh: Data Mining">
            </div>
          </div>

          <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="fw-semibold small text-secondary">Filter Tambahan</div>
            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#advancedFilters">
              <i class="bi bi-sliders"></i>
            </button>
          </div>

          <div class="collapse show" id="advancedFilters">
            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" value="1" id="fLatest" name="latest" <?= $prefill['latest'] === '1' ? 'checked' : '' ?>>
              <label class="form-check-label" for="fLatest">Tahun terbaru (sort)</label>
            </div>

            <div class="row g-2 mb-2">
              <div class="col-6">
                <label class="form-label">Tahun dari</label>
                <input class="form-control" name="year_from" value="<?= e($prefill['year_from']) ?>" inputmode="numeric" placeholder="2020">
              </div>
              <div class="col-6">
                <label class="form-label">Tahun sampai</label>
                <input class="form-control" name="year_to" value="<?= e($prefill['year_to']) ?>" inputmode="numeric" placeholder="2026">
              </div>
            </div>

            <div class="mb-2">
              <label class="form-label">Skala Jurnal</label>
              <select class="form-select" name="scope">
                <option value="" <?= $prefill['scope'] === '' ? 'selected' : '' ?>>Semua</option>
                <option value="nasional" <?= $prefill['scope'] === 'nasional' ? 'selected' : '' ?>>Nasional</option>
                <option value="internasional" <?= $prefill['scope'] === 'internasional' ? 'selected' : '' ?>>Internasional</option>
              </select>
            </div>

            <div class="row g-2 mb-2">
              <div class="col-6">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="1" id="fSinta" name="sinta" <?= $prefill['sinta'] === '1' ? 'checked' : '' ?>>
                  <label class="form-check-label" for="fSinta">SINTA</label>
                </div>
              </div>
              <div class="col-6">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="1" id="fScopus" name="scopus" <?= $prefill['scopus'] === '1' ? 'checked' : '' ?>>
                  <label class="form-check-label" for="fScopus">Scopus</label>
                </div>
              </div>
            </div>

            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" value="1" id="fOA" name="open_access" <?= $prefill['open_access'] === '1' ? 'checked' : '' ?>>
              <label class="form-check-label" for="fOA">Open access (punya PDF)</label>
            </div>

            <div class="form-check mb-0">
              <input class="form-check-input" type="checkbox" value="1" id="fExternal" name="include_external" <?= $prefill['include_external'] === '1' ? 'checked' : '' ?> <?= $scholarEnabled ? '' : 'disabled' ?>>
              <label class="form-check-label" for="fExternal">
                Google Scholar
                <?php if (!$scholarEnabled): ?>
                  <span class="badge text-bg-secondary ms-1">Belum dikonfigurasi</span>
                <?php endif; ?>
              </label>
            </div>
          </div>

          <div class="d-grid gap-2 mt-3">
            <button class="btn btn-primary" type="submit">
              <i class="bi bi-search me-1"></i>Cari Jurnal
            </button>
            <button class="btn btn-outline-secondary" id="btnResetJournals" type="button">
              Reset
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent d-flex align-items-center justify-content-between">
        <div class="fw-semibold">Hasil Pencarian</div>
        <div class="small text-secondary" id="resultMeta"></div>
      </div>
      <div class="card-body">
        <div id="resultAlert" class="alert alert-warning d-none"></div>
        <div id="resultLoading" class="text-center py-5 d-none">
          <div class="spinner-border" role="status"></div>
          <div class="small text-secondary mt-2">Mencari jurnal…</div>
        </div>
        <div id="resultEmpty" class="text-center py-5 d-none">
          <div class="fw-semibold" id="emptyTitle">Tidak ada data.</div>
          <div class="small text-secondary" id="emptyDesc">Coba ubah kata kunci atau filter.</div>
        </div>
        <div id="resultList" class="vstack gap-2"></div>
        <nav class="mt-3 d-flex justify-content-center">
          <ul class="pagination mb-0" id="resultPagination"></ul>
        </nav>
      </div>
    </div>
  </div>
</div>

<script>
  (function () {
    const apiUrl = <?= json_encode(url('journals/api-search'), JSON_UNESCAPED_UNICODE) ?>;
    const favUrl = <?= json_encode(url('journals/toggle-favorite'), JSON_UNESCAPED_UNICODE) ?>;
    const saveExternalUrl = <?= json_encode(url('journals/save-external'), JSON_UNESCAPED_UNICODE) ?>;
    const detailBase = <?= json_encode(url('journals/detail'), JSON_UNESCAPED_UNICODE) ?>;
    const dbgUrl = <?= json_encode($dbgUrl, JSON_UNESCAPED_UNICODE) ?>;
    const dbgSid = <?= json_encode($dbgSid, JSON_UNESCAPED_UNICODE) ?>;

    const $form = $('#journalSearchForm');
    const $loading = $('#resultLoading');
    const $empty = $('#resultEmpty');
    const $emptyTitle = $('#emptyTitle');
    const $emptyDesc = $('#emptyDesc');
    const $list = $('#resultList');
    const $meta = $('#resultMeta');
    const $pager = $('#resultPagination');
    const $alert = $('#resultAlert');

    function setLoading(on) {
      $loading.toggleClass('d-none', !on);
    }
    function setEmpty(on, title, desc) {
      $empty.toggleClass('d-none', !on);
      if (!on) return;
      $emptyTitle.text(String(title || 'Tidak ada data.'));
      $emptyDesc.text(String(desc || 'Coba ubah kata kunci atau filter.'));
    }
    function setAlert(message) {
      const msg = String(message || '');
      $alert.toggleClass('d-none', msg === '');
      $alert.text(msg);
    }

    function collectParams(page) {
      const data = {};
      $form.serializeArray().forEach(x => { data[x.name] = x.value; });
      ['latest', 'sinta', 'scopus', 'open_access', 'include_external'].forEach((k) => {
        const el = $form.find(`[name="${k}"]`)[0];
        if (el && el.type === 'checkbox') data[k] = el.checked ? '1' : '';
      });
      data.page = String(page || 1);
      data.per_page = '10';
      return data;
    }

    function buildBadges(item) {
      const badges = [];
      if (item.category) badges.push(`<span class="badge text-bg-light border">${APP.escapeHtml(item.category)}</span>`);
      if (item.indexed_by) badges.push(`<span class="badge text-bg-light border">${APP.escapeHtml(item.indexed_by)}</span>`);
      if (item.external) badges.push(`<span class="badge text-bg-secondary">Google Scholar</span>`);
      if (item.pdf_url) badges.push(`<span class="badge text-bg-success">Open Access</span>`);
      return badges.join(' ');
    }

    function renderItem(item) {
      const title = APP.escapeHtml(item.title || '');
      const author = APP.escapeHtml(item.author || '');
      const year = APP.escapeHtml(item.year || '');
      const source = APP.escapeHtml(item.source || '');
      const abs = APP.escapeHtml(item.abstract_short || '');
      const doi = APP.escapeHtml(item.doi || '');
      const pdf = APP.escapeHtml(item.pdf_url || '');
      const link = APP.escapeHtml(item.link || '');
      const isFav = String(item.is_favorite || '0') === '1';

      const detailHref = item.external ? '' : (detailBase + '&id=' + encodeURIComponent(item.id));
      const btnFav = item.external
        ? `<button class="btn btn-sm btn-outline-primary btn-save-external" type="button"
             data-json="${encodeURIComponent(JSON.stringify({
               title: item.title || '',
               author: item.author || '',
               year: item.year || '',
               source: item.source || '',
               abstract: item.abstract || item.abstract_short || '',
               doi: item.doi || '',
               pdf_url: item.pdf_url || '',
               category: item.category || '',
               indexed_by: item.indexed_by || ''
             }))}">
             <i class="bi bi-database-add me-1"></i>Simpan
           </button>`
        : `<button class="btn btn-sm ${isFav ? 'btn-primary' : 'btn-outline-primary'} btn-favorite" type="button" data-id="${item.id}">
             <i class="bi ${isFav ? 'bi-bookmark-heart-fill' : 'bi-bookmark-heart'} me-1"></i>${isFav ? 'Favorit' : 'Simpan'}
           </button>`;

      const btnDetail = item.external
        ? (link ? `<a class="btn btn-sm btn-outline-secondary" href="${link}" target="_blank" rel="noopener">Buka</a>` : '')
        : `<a class="btn btn-sm btn-outline-secondary" href="${detailHref}">Detail</a>`;

      const linkPart = (pdf || doi) ? `
        <div class="small text-secondary mt-1">
          ${pdf ? `<a href="${pdf}" target="_blank" rel="noopener">PDF</a>` : ''}
          ${pdf && doi ? '<span class="mx-2">•</span>' : ''}
          ${doi ? `DOI: ${doi}` : ''}
        </div>
      ` : '';

      return `
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between gap-3">
              <div class="flex-grow-1">
                <div class="fw-semibold">${title}</div>
                <div class="small text-secondary">${author}${author && year ? ' • ' : ''}${year}</div>
                <div class="small text-secondary">${source}</div>
                <div class="mt-2 small">${abs || '<span class="text-secondary">Tidak ada abstrak.</span>'}</div>
                ${linkPart}
                <div class="mt-2 d-flex flex-wrap gap-1">${buildBadges(item)}</div>
              </div>
              <div class="d-flex flex-column gap-2">
                ${btnFav}
                ${btnDetail}
              </div>
            </div>
          </div>
        </div>
      `;
    }

    function renderPagination(meta, currentPage) {
      const totalPages = Number(meta.total_pages || 1);
      const page = Number(currentPage || 1);
      const maxButtons = 7;
      let start = Math.max(1, page - 3);
      let end = Math.min(totalPages, start + maxButtons - 1);
      start = Math.max(1, end - maxButtons + 1);

      const parts = [];
      const prevDisabled = page <= 1 ? 'disabled' : '';
      parts.push(`<li class="page-item ${prevDisabled}"><a class="page-link" href="#" data-page="${page - 1}">Prev</a></li>`);

      for (let p = start; p <= end; p++) {
        parts.push(`<li class="page-item ${p === page ? 'active' : ''}"><a class="page-link" href="#" data-page="${p}">${p}</a></li>`);
      }

      const nextDisabled = page >= totalPages ? 'disabled' : '';
      parts.push(`<li class="page-item ${nextDisabled}"><a class="page-link" href="#" data-page="${page + 1}">Next</a></li>`);

      $pager.html(parts.join(''));
    }

    function bindActions() {
      $list.off('click', '.btn-favorite').on('click', '.btn-favorite', function () {
        const id = $(this).data('id');
        $.ajax({
          method: 'POST',
          url: favUrl,
          dataType: 'json',
          data: { journal_id: id, _csrf: APP.csrfToken }
        }).done((res) => {
          if (!res || !res.ok) return;
          fetchResults(currentPage);
        }).fail(APP.ajaxFail);
      });

      $list.off('click', '.btn-save-external').on('click', '.btn-save-external', function () {
        const $btn = $(this);
        $btn.prop('disabled', true);
        let payload = {};
        try {
          payload = JSON.parse(decodeURIComponent(String($btn.data('json') || '')) || '{}') || {};
        } catch (_) {
          payload = {};
        }
        $.ajax({
          method: 'POST',
          url: saveExternalUrl,
          dataType: 'json',
          data: {
            _csrf: APP.csrfToken,
            title: payload.title || '',
            author: payload.author || '',
            year: payload.year || '',
            source: payload.source || '',
            abstract: payload.abstract || '',
            doi: payload.doi || '',
            pdf_url: payload.pdf_url || '',
            category: payload.category || '',
            indexed_by: payload.indexed_by || '',
            favorite: '1'
          }
        }).done((res) => {
          if (res && res.ok) {
            APP.toast('success', 'Jurnal disimpan & ditambahkan ke favorit.');
          } else {
            APP.toast('danger', (res && res.error) ? res.error : 'Gagal menyimpan jurnal.');
          }
        }).fail(APP.ajaxFail).always(() => $btn.prop('disabled', false));
      });
    }

    let currentPage = 1;
    function fetchResults(page) {
      currentPage = Number(page || 1);
      setAlert('');
      setEmpty(false);
      setLoading(true);
      $list.empty();
      $pager.empty();
      $meta.text('');

      $.getJSON(apiUrl, collectParams(currentPage)).done((res) => {
        setLoading(false);
        if (!res || !res.ok) {
          setEmpty(true);
          return;
        }

        const meta = res.result || {};
        const items = Array.isArray(res.items) ? res.items : [];
        const externalItems = Array.isArray(res.external_items) ? res.external_items : [];

        if (res.scholar_warning) setAlert(res.scholar_warning);

        const total = Number(meta.total || 0);
        const totalPages = Number(meta.total_pages || 1);
        $meta.text(total ? (`${total} hasil • halaman ${currentPage}/${totalPages}`) : '');

        const html = [];
        if (externalItems.length) {
          html.push(`<div class="small text-secondary mb-2">Hasil dari Google Scholar</div>`);
          externalItems.forEach(it => html.push(renderItem(it)));
          html.push(`<hr class="my-3">`);
        }

        if (items.length) {
          items.forEach(it => html.push(renderItem(it)));
          $list.html(html.join(''));
          renderPagination(meta, currentPage);
          bindActions();
          return;
        }

        if (!externalItems.length) {
          const p = collectParams(currentPage);
          const includeExternal = String(p.include_external || '') === '1';
          if (!includeExternal) {
            setEmpty(true, 'Tidak ada hasil di database lokal.', 'Centang "Tambahkan dari Google Scholar" atau isi data jurnal di tabel journals.');
          } else {
            setEmpty(true);
          }
        } else {
          $list.html(html.join(''));
        }
      }).fail((xhr) => {
        setLoading(false);
        setEmpty(true);
        if (window.APP && APP.ajaxFail) APP.ajaxFail(xhr);
        try {
          if (dbgUrl) {
            const ct = xhr && xhr.getResponseHeader ? (xhr.getResponseHeader('content-type') || '') : '';
            const resp = xhr && typeof xhr.responseText === 'string' ? xhr.responseText : '';
            const snippet = resp ? resp.slice(0, 260) : '';
            fetch(dbgUrl, {
              method: 'POST',
              body: JSON.stringify({
                sessionId: dbgSid,
                runId: 'pre-fix',
                hypothesisId: 'C',
                location: 'app/views/journals/search.php:ajaxFail',
                msg: '[DEBUG] client ajax fail',
                data: {
                  status: xhr && typeof xhr.status === 'number' ? xhr.status : null,
                  content_type: ct,
                  response_snippet: snippet,
                  url: apiUrl,
                  params: collectParams(currentPage)
                },
                ts: Date.now()
              })
            }).catch(() => {});
          }
        } catch (_) {}
      });
    }

    $form.on('submit', function (e) {
      e.preventDefault();
      fetchResults(1);
    });

    $('#btnResetJournals').on('click', function () {
      $form[0].reset();
      fetchResults(1);
    });

    $pager.on('click', 'a.page-link', function (e) {
      e.preventDefault();
      const p = Number($(this).data('page') || 1);
      const disabled = $(this).closest('.page-item').hasClass('disabled');
      if (!disabled) fetchResults(p);
    });

    const hasPrefill = Object.keys(collectParams(1)).some((k) => {
      if (k === 'page' || k === 'per_page') return false;
      return String(collectParams(1)[k] || '') !== '';
    });
    if (hasPrefill) {
      fetchResults(1);
    }
  })();
</script>
