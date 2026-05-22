<div class="row g-3">
  <div class="col-12 col-lg-5">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent">
        <div class="fw-semibold">Generator BAB 1</div>
        <div class="small text-secondary">Pendahuluan: latar belakang, rumusan masalah, batasan, tujuan, manfaat.</div>
      </div>
      <div class="card-body">
        <form id="formGenerateBab1" method="post" action="<?= e(url('generator/bab1')) ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="feature" value="bab1">

          <div class="mb-3">
            <label class="form-label">Judul Penelitian</label>
            <input class="form-control" name="judul" placeholder="Masukkan judul penelitian" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Konteks / Problem Nyata</label>
            <textarea class="form-control" name="konteks" rows="6" placeholder="Jelaskan masalah yang ingin diselesaikan (ringkas namun jelas)" required></textarea>
          </div>

          <button class="btn btn-primary w-100" type="submit">
            <i class="bi bi-stars me-1"></i>Generate BAB 1
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
          <button class="btn btn-sm btn-outline-secondary" type="button" id="btnCopyResultBab1" disabled>
            <i class="bi bi-clipboard me-1"></i>Copy
          </button>
          <a class="btn btn-sm btn-outline-secondary d-none" id="btnOpenDocBab1" href="#">
            <i class="bi bi-box-arrow-up-right me-1"></i>Buka Dokumen
          </a>
        </div>
      </div>
      <div class="card-body">
        <div class="small text-secondary mb-2">
          Token: <span id="tokenTotalBab1">-</span> • Model: <span id="modelBab1">-</span>
        </div>
        <textarea class="form-control font-monospace" id="resultBab1" rows="18" placeholder="Hasil AI akan tampil di sini..." readonly></textarea>
      </div>
    </div>
  </div>
</div>

<script>
  $(function () {
    const $form = $('#formGenerateBab1');
    const $result = $('#resultBab1');
    const $btnCopy = $('#btnCopyResultBab1');
    const $btnOpen = $('#btnOpenDocBab1');

    APP.restoreDraft($form, 'draft:bab1');
    $form.on('input', () => APP.saveDraft($form, 'draft:bab1'));

    $form.on('submit', function (e) {
      e.preventDefault();
      APP.showLoader(true);
      $btnCopy.prop('disabled', true);
      $btnOpen.addClass('d-none').attr('href', '#');
      $result.val('');
      $('#tokenTotalBab1').text('-');
      $('#modelBab1').text('-');

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
        $('#tokenTotalBab1').text((res.usage && res.usage.total_tokens) ? res.usage.total_tokens : 0);
        $('#modelBab1').text(res.model || '-');
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
