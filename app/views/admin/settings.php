<?php
$settings = $settings ?? [];
$hasApiKey = (bool)($hasApiKey ?? false);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <div class="fw-semibold fs-5">Settings</div>
    <div class="small text-secondary">Konfigurasi AI dan aplikasi.</div>
  </div>
</div>

<div class="row g-3">
  <div class="col-12 col-lg-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent fw-semibold">OpenRouter</div>
      <div class="card-body">
        <div class="alert <?= $hasApiKey ? 'alert-success' : 'alert-warning' ?> mb-3">
          <div class="fw-semibold">API Key</div>
          <div class="small">
            Status: <?= $hasApiKey ? 'Tersimpan di settings (openai_token)' : 'Belum ada. Isi settings openai_token.' ?>
          </div>
        </div>

        <form method="post" action="<?= e(url('admin/save-settings')) ?>">
          <?= csrf_field() ?>
          <div class="mb-3">
            <label class="form-label">OpenRouter API Key</label>
            <input class="form-control" name="openai_token" placeholder="<?= $hasApiKey ? 'Sudah tersimpan (kosongkan jika tidak ingin mengubah)' : 'Masukkan token OpenRouter' ?>">
            <div class="form-text">Token disimpan di database (settings.openai_token).</div>
          </div>
          <div class="mb-3">
            <label class="form-label">Model</label>
            <input class="form-control" name="openai_model" value="<?= e((string)($settings['openai_model'] ?? OPENAI_MODEL)) ?>" required>
            <div class="form-text">Contoh: openai/gpt-4o-mini (format model OpenRouter).</div>
          </div>
          <div class="mb-3">
            <label class="form-label">Temperature</label>
            <input class="form-control" name="openai_temperature" value="<?= e((string)($settings['openai_temperature'] ?? (string)OPENAI_TEMPERATURE)) ?>" required>
            <div class="form-text">Angka 0.0 - 2.0 (contoh: 0.7).</div>
          </div>
          <div class="mb-3">
            <label class="form-label">Max Tokens</label>
            <input class="form-control" name="openai_max_tokens" value="<?= e((string)($settings['openai_max_tokens'] ?? (string)OPENAI_MAX_TOKENS)) ?>" required>
            <div class="form-text">Harus angka (contoh: 1200).</div>
          </div>
          <div class="mb-3">
            <label class="form-label">Prompt System (Admin)</label>
            <textarea class="form-control font-monospace" name="prompt_system" rows="7" placeholder="Kosongkan jika tidak ingin mengubah"><?= e((string)($settings['prompt_system'] ?? '')) ?></textarea>
            <div class="form-text">Jika diisi, prompt ini akan menggantikan system prompt default untuk semua generator.</div>
          </div>
          <button class="btn btn-primary" type="submit"><i class="bi bi-save me-1"></i>Simpan</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent fw-semibold">Catatan Setup (Admin)</div>
      <div class="card-body">
        <ol class="small text-secondary mb-0">
          <li>Impor SQL dari folder database/schema.sql.</li>
          <li>Pastikan config database sesuai (app/config/database.php atau env DB_*).</li>
          <li>Isi OpenRouter API key di Settings (openai_token).</li>
          <li>Set role admin di table users (kolom role = 'admin').</li>
        </ol>
      </div>
    </div>
  </div>
</div>
