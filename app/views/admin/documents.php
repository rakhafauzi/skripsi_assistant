<?php
$documents = $documents ?? [];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <div class="fw-semibold fs-5">Dokumen Mahasiswa</div>
    <div class="small text-secondary">Admin dapat melihat seluruh dokumen.</div>
  </div>
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
              <th>Mahasiswa</th>
              <th>Tipe</th>
              <th>Update</th>
              <th class="text-end">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($documents as $d): ?>
              <tr>
                <td class="fw-semibold"><?= e((string)($d['title'] ?? '')) ?></td>
                <td>
                  <div class="fw-semibold"><?= e((string)($d['user_name'] ?? '')) ?></div>
                  <div class="small text-secondary"><?= e((string)($d['user_email'] ?? '')) ?></div>
                </td>
                <td><span class="badge text-bg-secondary"><?= e(strtoupper((string)($d['type'] ?? ''))) ?></span></td>
                <td class="small text-secondary"><?= e((string)($d['updated_at'] ?? $d['created_at'] ?? '')) ?></td>
                <td class="text-end">
                  <a class="btn btn-sm btn-outline-secondary" href="<?= e(url('admin/document-detail', ['id' => (int)($d['id'] ?? 0)])) ?>">Detail</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

