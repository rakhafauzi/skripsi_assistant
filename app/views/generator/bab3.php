<div class="row g-3">
  <div class="col-12 col-lg-5">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent">
        <div class="fw-semibold">Generator BAB 3</div>
        <div class="small text-secondary">Metodologi penelitian dan metode pengembangan sistem.</div>
      </div>
      <div class="card-body">
        <form id="formGenerateBab3" method="post" action="<?= e(url('generator/bab3')) ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="feature" value="bab3">

          <div class="mb-3">
            <label class="form-label">Judul Penelitian</label>
            <input class="form-control" name="judul" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Metode Penelitian</label>
            <input class="form-control" name="metode" placeholder="Contoh: Studi Kasus / R&D / Eksperimen / Survei" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Gambaran Alur Penelitian/Sistem</label>
            <textarea class="form-control" name="alur" rows="6" placeholder="Contoh: pengumpulan data -> analisis -> desain -> implementasi -> pengujian" required></textarea>
          </div>

          <button class="btn btn-primary w-100" type="submit">
            <i class="bi bi-stars me-1"></i>Generate BAB 3
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
          <button class="btn btn-sm btn-outline-secondary" type="button" id="btnCopyResultBab3" disabled>
            <i class="bi bi-clipboard me-1"></i>Copy
          </button>
          <a class="btn btn-sm btn-outline-secondary d-none" id="btnOpenDocBab3" href="#">
            <i class="bi bi-box-arrow-up-right me-1"></i>Buka Dokumen
          </a>
        </div>
      </div>
      <div class="card-body">
        <div class="small text-secondary mb-2">
          Token: <span id="tokenTotalBab3">-</span> • Model: <span id="modelBab3">-</span>
        </div>
        <textarea class="form-control font-monospace" id="resultBab3" rows="18" placeholder="Hasil AI akan tampil di sini..." readonly></textarea>
      </div>
    </div>
  </div>
</div>

<script>
  $(function () {
    const $form = $('#formGenerateBab3');
    const $result = $('#resultBab3');
    const $btnCopy = $('#btnCopyResultBab3');
    const $btnOpen = $('#btnOpenDocBab3');

    APP.restoreDraft($form, 'draft:bab3');
    $form.on('input', () => APP.saveDraft($form, 'draft:bab3'));

    $form.on('submit', function (e) {
      e.preventDefault();
      APP.showLoader(true);
      $btnCopy.prop('disabled', true);
      $btnOpen.addClass('d-none').attr('href', '#');
      $result.val('');
      $('#tokenTotalBab3').text('-');
      $('#modelBab3').text('-');

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
        $('#tokenTotalBab3').text((res.usage && res.usage.total_tokens) ? res.usage.total_tokens : 0);
        $('#modelBab3').text(res.model || '-');
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
