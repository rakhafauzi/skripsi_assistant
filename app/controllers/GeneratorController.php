<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Response;
use App\Core\Security;
use App\Helpers\OpenAiHelper;
use App\Models\AiHistory;
use App\Models\Document;
use App\Models\Setting;

final class GeneratorController extends Controller
{
    public function title(): void
    {
        $this->requireLogin();
        $this->view('generator/title', ['type' => 'title']);
    }

    public function bab1(): void
    {
        $this->requireLogin();
        $this->view('generator/bab1', ['type' => 'bab1']);
    }

    public function bab2(): void
    {
        $this->requireLogin();
        $this->view('generator/bab2', ['type' => 'bab2']);
    }

    public function bab3(): void
    {
        $this->requireLogin();
        $this->view('generator/bab3', ['type' => 'bab3']);
    }

    public function uml(): void
    {
        $this->requireLogin();
        $this->view('generator/uml', ['type' => 'uml']);
    }

    public function conclusion(): void
    {
        $this->requireLogin();
        $this->view('generator/conclusion', ['type' => 'conclusion']);
    }

    public function apiGenerate(): void
    {
        try {
            $this->requireLogin();

            $csrf = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
            if (!Security::verifyCsrf(is_string($csrf) ? $csrf : null)) {
                Response::json(['ok' => false, 'error' => 'CSRF token tidak valid.'], 419);
                return;
            }

            $feature = trim((string)($_POST['feature'] ?? ''));
            $payload = $_POST;

            $allowed = ['title', 'bab1', 'bab2', 'bab3', 'uml', 'conclusion'];
            if (!in_array($feature, $allowed, true)) {
                Response::json(['ok' => false, 'error' => 'Feature tidak dikenali.'], 400);
                return;
            }

            $systemPrompt = $this->systemPrompt();
            $userPrompt = $this->buildUserPrompt($feature, $payload);

            if ($userPrompt === '') {
                Response::json(['ok' => false, 'error' => 'Input belum lengkap.'], 422);
                return;
            }

            $result = OpenAiHelper::chatCompletion($systemPrompt, $userPrompt, []);
            if (!$result['ok']) {
                Response::json(['ok' => false, 'error' => $result['error'] ?? 'Gagal memanggil AI.'], 500);
                return;
            }

            $userId = (int)Auth::id();
            $model = (string)($result['model'] ?? '');
            $usage = $result['usage'] ?? ['input_tokens' => 0, 'output_tokens' => 0, 'total_tokens' => 0];
            $content = (string)$result['content'];

            $aiModel = new AiHistory();
            $fullPrompt = "SYSTEM:\n" . $systemPrompt . "\n\nUSER:\n" . $userPrompt;
            $aiId = $aiModel->create(
                $userId,
                $feature,
                $fullPrompt,
                $content,
                $model,
                (int)($usage['input_tokens'] ?? 0),
                (int)($usage['output_tokens'] ?? 0),
                (int)($usage['total_tokens'] ?? 0)
            );

            $docModel = new Document();
            $docTitle = $this->defaultDocumentTitle($feature, $payload);
            $docId = $docModel->create($userId, $feature, $docTitle, $content, 'text');

            Response::json([
                'ok' => true,
                'content' => $content,
                'doc_id' => $docId,
                'ai_history_id' => $aiId,
                'usage' => $usage,
                'model' => $model,
            ]);
        } catch (\Throwable $e) {
            $msg = APP_DEBUG ? $e->getMessage() : 'Terjadi error server.';
            Response::json(['ok' => false, 'error' => $msg], 500);
        }
    }

    private function systemPrompt(): string
    {
        $custom = trim((string)(new Setting())->get('prompt_system', ''));
        if ($custom !== '') {
            return $custom;
        }
        return implode("\n", [
            'Anda adalah AI Assistant untuk membantu mahasiswa menyusun dokumentasi Skripsi/Tugas Akhir dalam Bahasa Indonesia yang formal dan akademik.',
            'Gunakan gaya bahasa yang jelas, terstruktur, dan mudah dipahami.',
            'Hindari klaim angka/data spesifik yang tidak bisa diverifikasi.',
            'Jika diminta referensi/penelitian terdahulu, berikan contoh daftar kandidat topik/sumber yang relevan (tanpa memalsukan sitasi).',
            'Output selalu rapi menggunakan heading dan bullet/numbering seperlunya.',
        ]);
    }

    private function buildUserPrompt(string $feature, array $p): string
    {
        $get = static function (string $key) use ($p): string {
            return trim((string)($p[$key] ?? ''));
        };

        if ($feature === 'title') {
            $jurusan = $get('jurusan');
            $minat = $get('minat');
            $teknologi = $get('teknologi');
            $keywords = $get('keywords');
            if ($jurusan === '' || $minat === '' || $teknologi === '' || $keywords === '') {
                return '';
            }

            return implode("\n", [
                'Buat 10 rekomendasi judul skripsi/tugas akhir.',
                'Konteks:',
                '- Jurusan: ' . $jurusan,
                '- Minat bidang: ' . $minat,
                '- Teknologi: ' . $teknologi,
                '- Kata kunci: ' . $keywords,
                '',
                'Format output wajib:',
                '1) Daftar 10 judul (nomor 1-10).',
                '2) Untuk setiap judul, berikan:',
                '   - Tingkat kompleksitas: Rendah/Sedang/Tinggi (1 kata).',
                '   - Saran metode penelitian: mis. R&D, Eksperimen, Studi Kasus, Survei, Design Science.',
                '   - Catatan singkat ruang lingkup (1-2 kalimat).',
            ]);
        }

        if ($feature === 'bab1') {
            $judul = $get('judul');
            $latar = $get('konteks');
            if ($judul === '' || $latar === '') {
                return '';
            }

            return implode("\n", [
                'Susun BAB 1 (Pendahuluan) untuk skripsi/tugas akhir.',
                'Judul: ' . $judul,
                'Konteks/Problem nyata: ' . $latar,
                '',
                'Hasilkan bagian berikut:',
                'A. Latar Belakang (3-7 paragraf)',
                'B. Rumusan Masalah (3-6 poin)',
                'C. Batasan Masalah (3-8 poin)',
                'D. Tujuan Penelitian (3-6 poin)',
                'E. Manfaat Penelitian (untuk akademik & praktis)',
            ]);
        }

        if ($feature === 'bab2') {
            $judul = $get('judul');
            $teori = $get('topik_teori');
            $teknologi = $get('teknologi');
            if ($judul === '' || $teori === '' || $teknologi === '') {
                return '';
            }

            return implode("\n", [
                'Susun BAB 2 (Tinjauan Pustaka / Landasan Teori) untuk skripsi/tugas akhir.',
                'Judul: ' . $judul,
                'Topik teori utama: ' . $teori,
                'Teknologi yang digunakan: ' . $teknologi,
                '',
                'Hasilkan bagian berikut:',
                'A. Landasan Teori (beri subbab dan definisi singkat yang relevan)',
                'B. Penelitian Terdahulu (buat tabel ringkas: No | Fokus | Metode | Temuan | Keterbatasan)',
                'C. Referensi Teknologi (konsep + alasan pemilihan teknologi)',
            ]);
        }

        if ($feature === 'bab3') {
            $judul = $get('judul');
            $metode = $get('metode');
            $alur = $get('alur');
            if ($judul === '' || $metode === '' || $alur === '') {
                return '';
            }

            return implode("\n", [
                'Susun BAB 3 (Metodologi Penelitian) untuk skripsi/tugas akhir.',
                'Judul: ' . $judul,
                'Metode penelitian yang dipilih: ' . $metode,
                'Gambaran alur penelitian/sistem: ' . $alur,
                '',
                'Hasilkan bagian berikut:',
                'A. Metodologi Penelitian (tahapan jelas)',
                'B. Flow Penelitian (ditulis sebagai langkah bernomor)',
                'C. Metode Pengembangan Sistem (pilih Waterfall atau Agile sesuai konteks, jelaskan alasan)',
                'D. Penjelasan Waterfall/Agile (subbab ringkas + kelebihan/kekurangan)',
            ]);
        }

        if ($feature === 'uml') {
            $judul = $get('judul');
            $aktor = $get('aktor');
            $fitur = $get('fitur');
            if ($judul === '' || $aktor === '' || $fitur === '') {
                return '';
            }

            return implode("\n", [
                'Buat rancangan UML & diagram dalam format teks (bukan gambar).',
                'Judul sistem: ' . $judul,
                'Aktor: ' . $aktor,
                'Fitur utama: ' . $fitur,
                '',
                'Output wajib dengan heading berikut:',
                '1) Use Case Diagram (teks: aktor -> use case)',
                '2) Activity Diagram (teks: langkah-langkah + decision bila ada)',
                '3) Sequence Diagram (teks: Actor -> System -> DB, urutan pesan)',
                '4) ERD sederhana (daftar entitas + atribut inti + relasi)',
            ]);
        }

        if ($feature === 'conclusion') {
            $ringkasan = $get('ringkasan');
            if ($ringkasan === '') {
                return '';
            }

            return implode("\n", [
                'Buat Kesimpulan & Saran berdasarkan ringkasan penelitian berikut.',
                'Ringkasan:',
                $ringkasan,
                '',
                'Output:',
                'A. Kesimpulan (5-8 poin)',
                'B. Saran (5-8 poin, termasuk pengembangan lanjutan)',
            ]);
        }

        return '';
    }

    private function defaultDocumentTitle(string $feature, array $p): string
    {
        $judul = trim((string)($p['judul'] ?? ''));
        return match ($feature) {
            'title' => 'Generator Judul - ' . date('Y-m-d H:i'),
            'bab1' => 'BAB 1 - ' . ($judul !== '' ? $judul : date('Y-m-d H:i')),
            'bab2' => 'BAB 2 - ' . ($judul !== '' ? $judul : date('Y-m-d H:i')),
            'bab3' => 'BAB 3 - ' . ($judul !== '' ? $judul : date('Y-m-d H:i')),
            'uml' => 'UML & Diagram - ' . ($judul !== '' ? $judul : date('Y-m-d H:i')),
            'conclusion' => 'Kesimpulan & Saran - ' . date('Y-m-d H:i'),
            default => 'Dokumen - ' . date('Y-m-d H:i'),
        };
    }
}
