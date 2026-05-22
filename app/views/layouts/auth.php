<?php
use App\Core\Flash;
$content = $content ?? '';
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
<body class="bg-soft">
  <main class="auth-wrap">
    <?= $content ?>
  </main>

  <?php $flash = Flash::pull(); ?>
  <?php if ($flash): ?>
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
      <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
          <strong class="me-auto"><?= e(APP_NAME) ?></strong>
          <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body text-bg-<?= e($flash['type'] ?? 'info') ?> rounded-2">
          <?= e($flash['message'] ?? '') ?>
        </div>
      </div>
    </div>
  <?php endif; ?>
</body>
</html>
