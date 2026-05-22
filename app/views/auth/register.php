<div class="container">
  <div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-5">
      <div class="text-center mb-4">
        <div class="brand-badge mx-auto mb-2"><i class="bi bi-stars"></i></div>
        <h4 class="mb-1">Register</h4>
        <div class="text-secondary small">Buat akun baru</div>
      </div>

      <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
          <form method="post" action="<?= e(url('auth/do-register')) ?>">
            <?= csrf_field() ?>
            <div class="mb-3">
              <label class="form-label">Nama</label>
              <input type="text" name="name" class="form-control" required autocomplete="name">
            </div>
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" required autocomplete="email">
            </div>
            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control" required minlength="8" autocomplete="new-password">
              <div class="form-text">Minimal 8 karakter.</div>
            </div>
            <div class="mb-3">
              <label class="form-label">Konfirmasi Password</label>
              <input type="password" name="password2" class="form-control" required minlength="8" autocomplete="new-password">
            </div>
            <button class="btn btn-primary w-100" type="submit">
              <i class="bi bi-person-plus me-1"></i>Daftar
            </button>
          </form>
        </div>
        <div class="card-footer bg-transparent border-0 p-4 pt-0">
          <div class="small text-center text-secondary">
            Sudah punya akun?
            <a href="<?= e(url('auth/login')) ?>">Login</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

