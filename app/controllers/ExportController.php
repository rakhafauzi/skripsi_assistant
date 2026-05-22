<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Flash;
use App\Core\Response;
use App\Core\Security;
use App\Models\Document;

final class ExportController extends Controller
{
    public function txt(): void
    {
        $this->requireLogin();
        $doc = $this->loadDoc();
        if (!$doc) {
            return;
        }

        $filename = $this->safeFilename(($doc['title'] ?? 'dokumen') . '.txt');
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo (string)$doc['content'];
    }

    public function doc(): void
    {
        $this->requireLogin();
        $doc = $this->loadDoc();
        if (!$doc) {
            return;
        }

        $filename = $this->safeFilename(($doc['title'] ?? 'dokumen') . '.doc');
        header('Content-Type: application/msword; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $title = Security::e((string)($doc['title'] ?? 'Dokumen'));
        $body = nl2br(Security::e((string)$doc['content']));
        echo '<html><head><meta charset="utf-8"><title>' . $title . '</title></head><body>';
        echo '<h2>' . $title . '</h2>';
        echo '<div style="font-family:Calibri,Arial; font-size: 12pt; line-height: 1.5;">' . $body . '</div>';
        echo '</body></html>';
    }

    public function pdf(): void
    {
        $this->requireLogin();
        $doc = $this->loadDoc();
        if (!$doc) {
            return;
        }

        $this->view('export/print', ['doc' => $doc], 'print');
    }

    private function loadDoc(): ?array
    {
        $id = (int)($_GET['id'] ?? 0);
        $model = new Document();
        $doc = Auth::isAdmin() ? $model->findByIdAny($id) : $model->findById($id, (int)Auth::id());
        if (!$doc) {
            Flash::error('Dokumen tidak ditemukan.');
            Response::redirect(url(Auth::isAdmin() ? 'admin/documents' : 'documents/index'));
            return null;
        }
        return $doc;
    }

    private function safeFilename(string $name): string
    {
        $name = preg_replace('/[^\w\s\-\.]+/u', '', $name) ?? 'dokumen.txt';
        $name = preg_replace('/\s+/', ' ', $name) ?? $name;
        $name = trim($name);
        return $name !== '' ? $name : 'dokumen';
    }
}
