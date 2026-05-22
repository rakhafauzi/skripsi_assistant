<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Flash;
use App\Core\Response;
use App\Core\Security;
use App\Models\User;

final class ProfileController extends Controller
{
    public function edit(): void
    {
        $this->requireLogin();
        $this->view('profile/edit', ['user' => Auth::user()]);
    }

    public function update(): void
    {
        $this->requireLogin();

        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            Flash::error('CSRF token tidak valid.');
            Response::redirect(url('profile/edit'));
        }

        $name = trim((string)($_POST['name'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $password2 = (string)($_POST['password2'] ?? '');

        if ($name === '') {
            Flash::error('Nama wajib diisi.');
            Response::redirect(url('profile/edit'));
        }

        $newPassword = null;
        if ($password !== '' || $password2 !== '') {
            if (strlen($password) < 8) {
                Flash::error('Password minimal 8 karakter.');
                Response::redirect(url('profile/edit'));
            }
            if ($password !== $password2) {
                Flash::error('Konfirmasi password tidak sama.');
                Response::redirect(url('profile/edit'));
            }
            $newPassword = $password;
        }

        $model = new User();
        $ok = $model->updateProfile((int)Auth::id(), $name, $newPassword);
        if ($ok) {
            $_SESSION['user_name'] = $name;
            Flash::success('Profil diperbarui.');
            Response::redirect(url('profile/edit'));
        }

        Flash::error('Gagal memperbarui profil.');
        Response::redirect(url('profile/edit'));
    }
}

