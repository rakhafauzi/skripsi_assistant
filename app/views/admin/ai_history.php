<?php
$items = $items ?? [];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <div class="fw-semibold fs-5">History Generate AI</div>
    <div class="small text-secondary">Admin dapat melihat seluruh history penggunaan AI.</div>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body">
    <?php if (!$items): ?>
      <div class="text-secondary">Belum ada history.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>Mahasiswa</th>
              <th>Feature</th>
              <th>Model</th>
              <th>Token</th>
              <th>Waktu</th>
              <th class="text-end">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $h): ?>
              <tr>
                <td>
                  <div class="fw-semibold"><?= e((string)($h['user_name'] ?? '')) ?></div>
                  <div class="small text-secondary"><?= e((string)($h['user_email'] ?? '')) ?></div>
                </td>
                <td class="fw-semibold"><?= e(strtoupper((string)($h['feature'] ?? ''))) ?></td>
                <td class="small text-secondary"><?= e((string)($h['model'] ?? '')) ?></td>
                <td class="small text-secondary"><?= e((string)($h['total_tokens'] ?? 0)) ?></td>
                <td class="small text-secondary"><?= e((string)($h['created_at'] ?? '')) ?></td>
                <td class="text-end">
                  <a class="btn btn-sm btn-outline-secondary" href="<?= e(url('admin/ai-history-detail', ['id' => (int)($h['id'] ?? 0)])) ?>">Detail</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

