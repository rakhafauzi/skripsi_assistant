<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Flash;
use App\Core\Response;
use App\Core\Security;
use App\Models\Document;

final class DocumentsController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $model = new Document();
        $docs = $model->listByUser((int)Auth::id(), 200);
        $this->view('documents/index', ['documents' => $docs]);
    }

    public function detail(): void
    {
        $this->requireLogin();
        $id = (int)($_GET['id'] ?? 0);
        $model = new Document();
        $doc = $model->findById($id, (int)Auth::id());
        if (!$doc) {
            Flash::error('Dokumen tidak ditemukan.');
            Response::redirect(url('documents/index'));
        }
        $this->view('documents/detail', ['doc' => $doc]);
    }

    public function edit(): void
    {
        $this->requireLogin();
        $id = (int)($_GET['id'] ?? 0);
        $model = new Document();
        $doc = $model->findById($id, (int)Auth::id());
        if (!$doc) {
            Flash::error('Dokumen tidak ditemukan.');
            Response::redirect(url('documents/index'));
        }
        $this->view('documents/edit', ['doc' => $doc]);
    }

    public function update(): void
    {
        $this->requireLogin();

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            Flash::error('CSRF token tidak valid.');
            Response::redirect(url('documents/index'));
        }

        $id = (int)($_POST['id'] ?? 0);
        $title = trim((string)($_POST['title'] ?? ''));
        $content = (string)($_POST['content'] ?? '');

        if ($id <= 0 || $title === '') {
            Flash::error('Input tidak valid.');
            Response::redirect(url('documents/index'));
        }

        $model = new Document();
        $ok = $model->update($id, (int)Auth::id(), $title, $content, 'text');
        if ($ok) {
            Flash::success('Dokumen diperbarui.');
            Response::redirect(url('documents/detail', ['id' => $id]));
        }

        Flash::error('Gagal memperbarui dokumen.');
        Response::redirect(url('documents/edit', ['id' => $id]));
    }

    public function delete(): void
    {
        $this->requireLogin();

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            Flash::error('CSRF token tidak valid.');
            Response::redirect(url('documents/index'));
        }

        $id = (int)($_POST['id'] ?? 0);
        $model = new Document();
        $ok = $model->delete($id, (int)Auth::id());
        Flash::info($ok ? 'Dokumen dihapus.' : 'Gagal menghapus dokumen.');
        Response::redirect(url('documents/index'));
    }
}

