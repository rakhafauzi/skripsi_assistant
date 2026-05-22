<?php
$user = $user ?? [];
?>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
  <div>
    <div class="fw-semibold fs-5">Profil Saya</div>
    <div class="small text-secondary">Mahasiswa dapat mengubah nama dan password sendiri.</div>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body">
    <form method="post" action="<?= e(url('profile/update')) ?>">
      <?= csrf_field() ?>
      <div class="mb-3">
        <label class="form-label">Nama</label>
        <input class="form-control" name="name" value="<?= e((string)($user['name'] ?? '')) ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input class="form-control" value="<?= e((string)($user['email'] ?? '')) ?>" readonly>
      </div>
      <div class="row g-3">
        <div class="col-12 col-md-6">
          <div class="mb-3">
            <label class="form-label">Password Baru (opsional)</label>
            <input class="form-control" name="password" type="password" minlength="8" autocomplete="new-password">
          </div>
        </div>
        <div class="col-12 col-md-6">
          <div class="mb-3">
            <label class="form-label">Konfirmasi Password</label>
            <input class="form-control" name="password2" type="password" minlength="8" autocomplete="new-password">
          </div>
        </div>
      </div>
      <button class="btn btn-primary" type="submit"><i class="bi bi-save me-1"></i>Simpan</button>
    </form>
  </div>
</div>

