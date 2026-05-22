<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Flash;
use App\Core\Response;
use App\Core\Security;
use App\Models\AiHistory;
use App\Models\Document;
use App\Models\Setting;
use App\Models\User;

final class AdminController extends Controller
{
    public function dashboard(): void
    {
        $this->requireAdmin();

        $userModel = new User();
        $docModel = new Document();
        $aiModel = new AiHistory();

        $this->view('admin/dashboard', [
            'totalUsers' => $userModel->countAll(),
            'totalDocs' => $docModel->countAll(),
            'totalAi' => $aiModel->countAll(),
            'totalTokens' => $aiModel->sumTotalTokens(),
            'latestAi' => $aiModel->latestAll(10),
            'latestDocs' => $docModel->listAll(10),
        ]);
    }

    public function settings(): void
    {
        $this->requireAdmin();
        $settings = (new Setting())->all();
        $this->view('admin/settings', [
            'settings' => $settings,
            'hasApiKey' => !empty($settings['openai_token']),
        ]);
    }

    public function saveSettings(): void
    {
        $this->requireAdmin();

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            Flash::error('CSRF token tidak valid.');
            Response::redirect(url('admin/settings'));
        }

        $openaiToken = trim((string)($_POST['openai_token'] ?? ''));
        $promptSystem = trim((string)($_POST['prompt_system'] ?? ''));

        $openaiModel = trim((string)($_POST['openai_model'] ?? OPENAI_MODEL));
        $tempRaw = trim((string)($_POST['openai_temperature'] ?? (string)OPENAI_TEMPERATURE));
        $maxRaw = trim((string)($_POST['openai_max_tokens'] ?? (string)OPENAI_MAX_TOKENS));

        if ($openaiModel === '') {
            Flash::error('Model wajib diisi.');
            Response::redirect(url('admin/settings'));
        }

        if (!is_numeric($tempRaw)) {
            Flash::error('Temperature harus berupa angka.');
            Response::redirect(url('admin/settings'));
        }
        $temperature = (float)$tempRaw;
        if ($temperature < 0.0 || $temperature > 2.0) {
            Flash::error('Temperature harus di rentang 0.0 - 2.0.');
            Response::redirect(url('admin/settings'));
        }

        if (!ctype_digit($maxRaw)) {
            Flash::error('Max Tokens harus berupa angka (contoh: 1200). Jangan isi API key di field ini.');
            Response::redirect(url('admin/settings'));
        }
        $maxTokens = (int)$maxRaw;
        if ($maxTokens < 1 || $maxTokens > 200000) {
            Flash::error('Max Tokens tidak valid.');
            Response::redirect(url('admin/settings'));
        }

        $model = new Setting();
        if ($openaiToken !== '') {
            $model->set('openai_token', $openaiToken);
        }
        if ($promptSystem !== '') {
            $model->set('prompt_system', $promptSystem);
        }
        $model->set('openai_model', $openaiModel);
        $model->set('openai_temperature', (string)$temperature);
        $model->set('openai_max_tokens', (string)$maxTokens);

        Flash::success('Settings tersimpan.');
        Response::redirect(url('admin/settings'));
    }

    public function users(): void
    {
        $this->requireAdmin();
        $users = (new User())->listUsers(300);
        $this->view('admin/users', ['users' => $users]);
    }

    public function updateRole(): void
    {
        $this->requireAdmin();

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            Flash::error('CSRF token tidak valid.');
            Response::redirect(url('admin/users'));
        }

        $id = (int)($_POST['id'] ?? 0);
        $role = (string)($_POST['role'] ?? 'mahasiswa');
        if (!in_array($role, ['mahasiswa', 'admin'], true) || $id <= 0) {
            Flash::error('Input tidak valid.');
            Response::redirect(url('admin/users'));
        }

        (new User())->updateRole($id, $role);
        Flash::success('Role diperbarui.');
        Response::redirect(url('admin/users'));
    }

    public function deleteUser(): void
    {
        $this->requireAdmin();

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            Flash::error('CSRF token tidak valid.');
            Response::redirect(url('admin/users'));
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            Flash::error('User tidak valid.');
            Response::redirect(url('admin/users'));
        }
        if (Auth::id() === $id) {
            Flash::error('Tidak bisa menghapus akun sendiri.');
            Response::redirect(url('admin/users'));
        }

        $ok = (new User())->deleteById($id);
        Flash::info($ok ? 'User berhasil dihapus.' : 'Gagal menghapus user.');
        Response::redirect(url('admin/users'));
    }

    public function documents(): void
    {
        $this->requireAdmin();
        $docs = (new Document())->listAll(300);
        $this->view('admin/documents', ['documents' => $docs]);
    }

    public function documentDetail(): void
    {
        $this->requireAdmin();
        $id = (int)($_GET['id'] ?? 0);
        $doc = (new Document())->findByIdAny($id);
        if (!$doc) {
            Flash::error('Dokumen tidak ditemukan.');
            Response::redirect(url('admin/documents'));
        }
        $this->view('admin/document_detail', ['doc' => $doc]);
    }

    public function aiHistory(): void
    {
        $this->requireAdmin();
        $items = (new AiHistory())->latestAll(300);
        $this->view('admin/ai_history', ['items' => $items]);
    }

    public function aiHistoryDetail(): void
    {
        $this->requireAdmin();
        $id = (int)($_GET['id'] ?? 0);
        $item = (new AiHistory())->findByIdAny($id);
        if (!$item) {
            Flash::error('Riwayat tidak ditemukan.');
            Response::redirect(url('admin/ai-history'));
        }
        $this->view('admin/ai_history_detail', ['item' => $item]);
    }
}
