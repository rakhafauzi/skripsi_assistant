<?php
$documents = $documents ?? [];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <div class="fw-semibold fs-5">Riwayat Dokumen</div>
    <div class="small text-secondary">Semua hasil generate AI tersimpan di sini dan bisa diedit.</div>
  </div>
  <a class="btn btn-primary" href="<?= e(url('generator/title')) ?>">
    <i class="bi bi-plus-lg me-1"></i>Generate Baru
  </a>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body">
    <?php if (!$documents): ?>
      <div class="text-secondary">Belum ada dokumen.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>Judul</th>
              <th>Tipe</th>
              <th>Update</th>
              <th class="text-end">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($documents as $d): ?>
              <tr>
                <td>
                  <div class="fw-semibold"><?= e($d['title'] ?? '') ?></div>
                  <div class="small text-secondary">ID: <?= e((string)$d['id']) ?></div>
                </td>
                <td><span class="badge text-bg-secondary"><?= e(strtoupper((string)($d['type'] ?? ''))) ?></span></td>
                <td class="small text-secondary"><?= e((string)($d['updated_at'] ?? $d['created_at'] ?? '')) ?></td>
                <td class="text-end">
                  <a class="btn btn-sm btn-outline-secondary" href="<?= e(url('documents/detail', ['id' => (int)$d['id']])) ?>">Detail</a>
                  <a class="btn btn-sm btn-outline-primary" href="<?= e(url('documents/edit', ['id' => (int)$d['id']])) ?>">Edit</a>
                  <form class="d-inline" method="post" action="<?= e(url('documents/delete')) ?>" onsubmit="return confirm('Hapus dokumen ini?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= e((string)$d['id']) ?>">
                    <button class="btn btn-sm btn-outline-danger" type="submit">Hapus</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

