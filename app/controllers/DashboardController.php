<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\AiHistory;
use App\Models\Document;

final class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();

        $docModel = new Document();
        $aiModel = new AiHistory();

        $userId = (int)Auth::id();

        $stats = $docModel->statsByUser($userId);
        $documents = $docModel->listByUser($userId, 10);
        $aiHistory = $aiModel->latestByUser($userId, 8);

        $totalDocs = array_sum($stats);
        $progressMap = [
            'title' => 10,
            'bab1' => 20,
            'bab2' => 20,
            'bab3' => 20,
            'uml' => 15,
            'conclusion' => 15,
        ];
        $progress = 0;
        foreach ($progressMap as $type => $weight) {
            if (!empty($stats[$type])) {
                $progress += $weight;
            }
        }

        $this->view('dashboard/index', [
            'stats' => $stats,
            'documents' => $documents,
            'aiHistory' => $aiHistory,
            'totalDocs' => $totalDocs,
            'progress' => min(100, $progress),
        ]);
    }
}

