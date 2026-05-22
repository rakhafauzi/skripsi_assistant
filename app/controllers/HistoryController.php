<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Flash;
use App\Core\Response;
use App\Models\AiHistory;

final class HistoryController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $model = new AiHistory();
        $items = $model->latestByUser((int)Auth::id(), 100);
        $this->view('history/index', ['items' => $items]);
    }

    public function detail(): void
    {
        $this->requireLogin();
        $id = (int)($_GET['id'] ?? 0);
        $model = new AiHistory();
        $item = $model->findById($id, (int)Auth::id());
        if (!$item) {
            Flash::error('Riwayat tidak ditemukan.');
            Response::redirect(url('history/index'));
        }
        $this->view('history/detail', ['item' => $item]);
    }
}

