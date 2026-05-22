<?php
use App\Core\Auth;
use App\Core\Flash;

$content = $content ?? '';
$user = Auth::user();
$isAdmin = Auth::isAdmin();
?>
<!doctype html>
<html lang="id" data-bs-theme="light" class="app-theme">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
  <title><?= e(APP_NAME) ?></title>
  <link rel="icon" type="image/png" href="<?= e(asset('favicon.png')) ?>">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= e(asset('assets/css/app.css')) ?>" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    window.APP = window.APP || {};
    window.APP.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  </script>
  <script src="<?= e(asset('assets/js/app.js')) ?>"></script>
</head>
<body class="app-body">
  <div id="appLoader" class="ai-loader d-none">
    <div class="ai-loader-inner">
      <div class="spinner-border" role="status"></div>
      <div class="mt-3 fw-semibold">AI sedang memproses…</div>
      <div class="small text-secondary">Mohon tunggu, jangan tutup halaman.</div>
    </div>
  </div>

  <div class="d-flex">
    <aside class="sidebar border-end">
      <div class="p-3 border-bottom">
        <div class="d-flex align-items-center gap-2">
          <div class="brand-badge"><i class="bi bi-stars"></i></div>
          <div>
            <div class="fw-bold">AI Assistant</div>
            <div class="small text-secondary">Dokumentasi Skripsi</div>
          </div>
        </div>
      </div>

      <nav class="p-2">
        <a class="nav-link <?= (($_GET['r'] ?? '') === 'dashboard/index' ? 'active' : '') ?>" href="<?= e(url('dashboard/index')) ?>">
          <i class="bi bi-speedometer2 me-2"></i>Dashboard
        </a>
        <a class="nav-link" href="<?= e(url('profile/edit')) ?>">
          <i class="bi bi-person-circle me-2"></i>Profil
        </a>

        <div class="nav-section mt-3 mb-1 small text-secondary px-2">Generator</div>
        <a class="nav-link" href="<?= e(url('generator/title')) ?>"><i class="bi bi-lightbulb me-2"></i>Judul Skripsi</a>
        <a class="nav-link" href="<?= e(url('generator/bab1')) ?>"><i class="bi bi-file-earmark-text me-2"></i>BAB 1</a>
        <a class="nav-link" href="<?= e(url('generator/bab2')) ?>"><i class="bi bi-journal-text me-2"></i>BAB 2</a>
        <a class="nav-link" href="<?= e(url('generator/bab3')) ?>"><i class="bi bi-diagram-3 me-2"></i>BAB 3</a>
        <a class="nav-link" href="<?= e(url('generator/uml')) ?>"><i class="bi bi-bezier2 me-2"></i>UML & Diagram</a>
        <a class="nav-link" href="<?= e(url('generator/conclusion')) ?>"><i class="bi bi-check2-circle me-2"></i>Kesimpulan & Saran</a>

        <div class="nav-section mt-3 mb-1 small text-secondary px-2">Dokumen</div>
        <a class="nav-link" href="<?= e(url('documents/index')) ?>"><i class="bi bi-folder2-open me-2"></i>Riwayat Dokumen</a>
        <a class="nav-link" href="<?= e(url('history/index')) ?>"><i class="bi bi-clock-history me-2"></i>History Prompt AI</a>

        <?php if ($isAdmin): ?>
          <div class="nav-section mt-3 mb-1 small text-secondary px-2">Admin</div>
          <a class="nav-link" href="<?= e(url('admin/dashboard')) ?>"><i class="bi bi-speedometer me-2"></i>Dashboard Admin</a>
          <a class="nav-link" href="<?= e(url('admin/documents')) ?>"><i class="bi bi-folder2-open me-2"></i>Dokumen Mahasiswa</a>
          <a class="nav-link" href="<?= e(url('admin/ai-history')) ?>"><i class="bi bi-clock-history me-2"></i>History AI</a>
          <a class="nav-link" href="<?= e(url('admin/settings')) ?>"><i class="bi bi-gear me-2"></i>Settings</a>
          <a class="nav-link" href="<?= e(url('admin/users')) ?>"><i class="bi bi-people me-2"></i>Users</a>
        <?php endif; ?>
      </nav>
    </aside>

    <div class="flex-grow-1">
      <header class="topbar border-bottom">
        <div class="container-fluid py-2 d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-outline-secondary d-md-none" id="btnToggleSidebar" type="button">
              <i class="bi bi-list"></i>
            </button>
            <span class="small welcome-text">Selamat datang</span>
            <span class="fw-semibold welcome-name"><?= e($user['name'] ?? '') ?></span>
          </div>

          <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-outline-secondary" id="btnToggleTheme" type="button">
              <i class="bi bi-moon-stars"></i>
              <span class="d-none d-sm-inline ms-1">Dark Mode</span>
            </button>
            <a class="btn btn-sm btn-outline-danger" href="<?= e(url('auth/logout')) ?>">
              <i class="bi bi-box-arrow-right"></i>
              <span class="d-none d-sm-inline ms-1">Logout</span>
            </a>
          </div>
        </div>
      </header>

      <main class="container-fluid py-4">
        <?= $content ?>
      </main>
    </div>
  </div>

  <?php $flash = Flash::pull(); ?>
  <div class="toast-container position-fixed bottom-0 end-0 p-3" id="toastStack">
    <?php if ($flash): ?>
      <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
          <strong class="me-auto"><?= e(APP_NAME) ?></strong>
          <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body text-bg-<?= e($flash['type'] ?? 'info') ?> rounded-2">
          <?= e($flash['message'] ?? '') ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
