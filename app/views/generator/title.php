<div class="row g-3">
  <div class="col-12 col-lg-5">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent">
        <div class="fw-semibold"><i class="bi bi-robot me-1"></i>Generator Judul Skripsi</div>  
        <div class="small text-secondary">Isi data, lalu klik Generate.</div>
      </div>
      <div class="card-body">
        <form id="formGenerateTitle" method="post" action="<?= e(url('generator/title')) ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="feature" value="title">

          <div class="mb-3">
            <label class="form-label">Jurusan</label>
            <input class="form-control" name="jurusan" placeholder="Contoh: Teknik Informatika" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Minat Bidang</label>
            <input class="form-control" name="minat" placeholder="Contoh: Data Mining, Web, Mobile, Jaringan" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Teknologi</label>
            <input class="form-control" name="teknologi" placeholder="Contoh: Laravel, React, MySQL, IoT" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Kata Kunci</label>
            <input class="form-control" name="keywords" placeholder="Contoh: rekomendasi, klasifikasi, chatbot, absensi" required>
          </div>

          <button class="btn btn-primary w-100" type="submit">
            <i class="bi bi-stars me-1"></i>Generate 10 Judul
          </button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-7">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <div>
          <div class="fw-semibold">Hasil</div>
          <div class="small text-secondary">Otomatis tersimpan ke Riwayat Dokumen.</div>
        </div>
        <div class="d-flex gap-2">
          <button class="btn btn-sm btn-outline-secondary" type="button" id="btnCopyResultTitle" disabled>
            <i class="bi bi-clipboard me-1"></i>Copy
          </button>
          <a class="btn btn-sm btn-outline-secondary d-none" id="btnOpenDocTitle" href="#">
            <i class="bi bi-box-arrow-up-right me-1"></i>Buka Dokumen
          </a>
        </div>
      </div>
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <div class="small text-secondary">
            Token: <span id="tokenTotalTitle">-</span> • Model: <span id="modelTitle">-</span>
          </div>
        </div>
        <textarea class="form-control font-monospace" id="resultTitle" rows="18" placeholder="Hasil AI akan tampil di sini..." readonly></textarea>
      </div>
    </div>
  </div>
</div>

<script>
  $(function () {
    const $form = $('#formGenerateTitle');
    const $result = $('#resultTitle');
    const $btnCopy = $('#btnCopyResultTitle');
    const $btnOpen = $('#btnOpenDocTitle');

    APP.restoreDraft($form, 'draft:title');
    $form.on('input', () => APP.saveDraft($form, 'draft:title'));

    $form.on('submit', function (e) {
      e.preventDefault();
      APP.showLoader(true);
      $btnCopy.prop('disabled', true);
      $btnOpen.addClass('d-none').attr('href', '#');
      $result.val('');
      $('#tokenTotalTitle').text('-');
      $('#modelTitle').text('-');

      $.ajax({
        url: "<?= e(url('generator/api-generate')) ?>",
        method: 'POST',
        data: $form.serialize(),
        dataType: 'json',
        headers: { 'X-CSRF-Token': APP.csrfToken }
      }).done(function (res) {
        if (!res.ok) {
          APP.toast('danger', res.error || 'Gagal generate.');
          return;
        }
        $result.val(res.content || '');
        $btnCopy.prop('disabled', false);
        $('#tokenTotalTitle').text((res.usage && res.usage.total_tokens) ? res.usage.total_tokens : 0);
        $('#modelTitle').text(res.model || '-');
        if (res.doc_id) {
          $btnOpen.removeClass('d-none').attr('href', "<?= e(url('documents/detail')) ?>&id=" + res.doc_id);
        }
        APP.toast('success', 'Generate berhasil & dokumen tersimpan.');
      }).fail(APP.ajaxFail).always(function () {
        APP.showLoader(false);
      });
    });

    $btnCopy.on('click', function () {
      APP.copyToClipboard($result.val() || '');
    });
  });
</script>
