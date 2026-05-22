<div class="row g-3">
  <div class="col-12 col-lg-5">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent">
        <div class="fw-semibold">Generator UML & Diagram</div>
        <div class="small text-secondary">Output dalam format teks: use case, activity, sequence, ERD sederhana.</div>
      </div>
      <div class="card-body">
        <form id="formGenerateUml" method="post" action="<?= e(url('generator/uml')) ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="feature" value="uml">

          <div class="mb-3">
            <label class="form-label">Judul Sistem</label>
            <input class="form-control" name="judul" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Aktor</label>
            <input class="form-control" name="aktor" placeholder="Contoh: Admin, User, Dosen Pembimbing" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Fitur Utama</label>
            <textarea class="form-control" name="fitur" rows="6" placeholder="Contoh: login, generate dokumen, export PDF, manajemen user" required></textarea>
          </div>

          <button class="btn btn-primary w-100" type="submit">
            <i class="bi bi-stars me-1"></i>Generate UML Teks
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
          <div class="small text-secondary">Gunakan Copy atau Export TXT.</div>
        </div>
        <div class="d-flex flex-wrap gap-2 justify-content-end">
          <button class="btn btn-sm btn-outline-secondary" type="button" id="btnCopyResultUml" disabled>
            <i class="bi bi-clipboard me-1"></i>Copy
          </button>
          <a class="btn btn-sm btn-outline-secondary d-none" id="btnExportTxtUml" href="#">
            <i class="bi bi-filetype-txt me-1"></i>Export TXT
          </a>
          <a class="btn btn-sm btn-outline-secondary d-none" id="btnOpenDocUml" href="#">
            <i class="bi bi-box-arrow-up-right me-1"></i>Buka Dokumen
          </a>
        </div>
      </div>
      <div class="card-body">
        <div class="small text-secondary mb-2">
          Token: <span id="tokenTotalUml">-</span> • Model: <span id="modelUml">-</span>
        </div>
        <textarea class="form-control font-monospace" id="resultUml" rows="18" placeholder="Hasil AI akan tampil di sini..." readonly></textarea>
      </div>
    </div>
  </div>
</div>

<script>
  $(function () {
    const $form = $('#formGenerateUml');
    const $result = $('#resultUml');
    const $btnCopy = $('#btnCopyResultUml');
    const $btnOpen = $('#btnOpenDocUml');
    const $btnTxt = $('#btnExportTxtUml');

    APP.restoreDraft($form, 'draft:uml');
    $form.on('input', () => APP.saveDraft($form, 'draft:uml'));

    $form.on('submit', function (e) {
      e.preventDefault();
      APP.showLoader(true);
      $btnCopy.prop('disabled', true);
      $btnOpen.addClass('d-none').attr('href', '#');
      $btnTxt.addClass('d-none').attr('href', '#');
      $result.val('');
      $('#tokenTotalUml').text('-');
      $('#modelUml').text('-');

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
        $('#tokenTotalUml').text((res.usage && res.usage.total_tokens) ? res.usage.total_tokens : 0);
        $('#modelUml').text(res.model || '-');
        if (res.doc_id) {
          $btnOpen.removeClass('d-none').attr('href', "<?= e(url('documents/detail')) ?>&id=" + res.doc_id);
          $btnTxt.removeClass('d-none').attr('href', "<?= e(url('export/txt')) ?>&id=" + res.doc_id);
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
