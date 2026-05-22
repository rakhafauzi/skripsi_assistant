<div class="row g-3">
  <div class="col-12 col-lg-5">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent">
        <div class="fw-semibold">Generator Kesimpulan & Saran</div>
        <div class="small text-secondary">Masukkan ringkasan penelitian, AI membuat kesimpulan dan saran.</div>
      </div>
      <div class="card-body">
        <form id="formGenerateConclusion" method="post" action="<?= e(url('generator/conclusion')) ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="feature" value="conclusion">

          <div class="mb-3">
            <label class="form-label">Ringkasan Penelitian</label>
            <textarea class="form-control" name="ringkasan" rows="10" placeholder="Ringkas: tujuan, metode, hasil, kontribusi" required></textarea>
          </div>

          <button class="btn btn-primary w-100" type="submit">
            <i class="bi bi-stars me-1"></i>Generate Kesimpulan & Saran
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
          <button class="btn btn-sm btn-outline-secondary" type="button" id="btnCopyResultConclusion" disabled>
            <i class="bi bi-clipboard me-1"></i>Copy
          </button>
          <a class="btn btn-sm btn-outline-secondary d-none" id="btnOpenDocConclusion" href="#">
            <i class="bi bi-box-arrow-up-right me-1"></i>Buka Dokumen
          </a>
        </div>
      </div>
      <div class="card-body">
        <div class="small text-secondary mb-2">
          Token: <span id="tokenTotalConclusion">-</span> • Model: <span id="modelConclusion">-</span>
        </div>
        <textarea class="form-control font-monospace" id="resultConclusion" rows="18" placeholder="Hasil AI akan tampil di sini..." readonly></textarea>
      </div>
    </div>
  </div>
</div>

<script>
  $(function () {
    const $form = $('#formGenerateConclusion');
    const $result = $('#resultConclusion');
    const $btnCopy = $('#btnCopyResultConclusion');
    const $btnOpen = $('#btnOpenDocConclusion');

    APP.restoreDraft($form, 'draft:conclusion');
    $form.on('input', () => APP.saveDraft($form, 'draft:conclusion'));

    $form.on('submit', function (e) {
      e.preventDefault();
      APP.showLoader(true);
      $btnCopy.prop('disabled', true);
      $btnOpen.addClass('d-none').attr('href', '#');
      $result.val('');
      $('#tokenTotalConclusion').text('-');
      $('#modelConclusion').text('-');

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
        $('#tokenTotalConclusion').text((res.usage && res.usage.total_tokens) ? res.usage.total_tokens : 0);
        $('#modelConclusion').text(res.model || '-');
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
