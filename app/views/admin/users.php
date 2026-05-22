<?php
$users = $users ?? [];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <div class="fw-semibold fs-5">Users</div>
      <div class="small text-secondary">Manajemen role mahasiswa/admin.</div>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>Nama</th>
            <th>Email</th>
            <th>Role</th>
            <th>Created</th>
            <th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <td class="fw-semibold"><?= e((string)($u['name'] ?? '')) ?></td>
              <td class="small text-secondary"><?= e((string)($u['email'] ?? '')) ?></td>
              <td>
                <span class="badge <?= (($u['role'] ?? '') === 'admin') ? 'text-bg-primary' : 'text-bg-secondary' ?>">
                  <?= e((string)($u['role'] ?? 'mahasiswa')) ?>
                </span>
              </td>
              <td class="small text-secondary"><?= e((string)($u['created_at'] ?? '')) ?></td>
              <td class="text-end">
                <form class="d-inline" method="post" action="<?= e(url('admin/update-role')) ?>">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= e((string)($u['id'] ?? 0)) ?>">
                  <select class="form-select form-select-sm d-inline w-auto" name="role">
                    <option value="mahasiswa" <?= (($u['role'] ?? '') === 'mahasiswa' || ($u['role'] ?? '') === 'user') ? 'selected' : '' ?>>mahasiswa</option>
                    <option value="admin" <?= (($u['role'] ?? '') === 'admin') ? 'selected' : '' ?>>admin</option>
                  </select>
                  <button class="btn btn-sm btn-outline-primary" type="submit">Update</button>
                </form>
                <form class="d-inline" method="post" action="<?= e(url('admin/delete-user')) ?>" onsubmit="return confirm('Hapus user ini? Semua dokumen & history AI user akan ikut terhapus.')">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= e((string)($u['id'] ?? 0)) ?>">
                  <button class="btn btn-sm btn-outline-danger" type="submit">Hapus</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
