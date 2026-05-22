<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Flash;
use App\Core\Response;
use App\Core\Security;
use App\Models\User;

final class AuthController extends Controller
{
    public function login(): void
    {
        if (Auth::check()) {
            Response::redirect(url('dashboard/index'));
        }
        $this->view('auth/login', [], 'auth');
    }

    public function doLogin(): void
    {
        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            Flash::error('CSRF token tidak valid.');
            Response::redirect(url('auth/login'));
        }

        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            Flash::error('Email dan password wajib diisi.');
            Response::redirect(url('auth/login'));
        }

        $model = new User();
        $user = $model->findAuthByEmail($email);

        if (!$user || !password_verify($password, (string)$user['password_hash'])) {
            Flash::error('Email atau password salah.');
            Response::redirect(url('auth/login'));
        }

        Auth::login([
            'id' => (int)$user['id'],
            'name' => (string)($user['name'] ?? ''),
            'role' => (string)($user['role'] ?? 'mahasiswa'),
        ]);
        Flash::success('Berhasil login.');
        Response::redirect(url('dashboard/index'));
    }

    public function register(): void
    {
        if (Auth::check()) {
            Response::redirect(url('dashboard/index'));
        }
        $this->view('auth/register', [], 'auth');
    }

    public function doRegister(): void
    {
        if (!Security::verifyCsrf($_POST['_csrf'] ?? null)) {
            Flash::error('CSRF token tidak valid.');
            Response::redirect(url('auth/register'));
        }

        $name = trim((string)($_POST['name'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $password2 = (string)($_POST['password2'] ?? '');

        if ($name === '' || $email === '' || $password === '' || $password2 === '') {
            Flash::error('Semua field wajib diisi.');
            Response::redirect(url('auth/register'));
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Flash::error('Format email tidak valid.');
            Response::redirect(url('auth/register'));
        }
        if (strlen($password) < 8) {
            Flash::error('Password minimal 8 karakter.');
            Response::redirect(url('auth/register'));
        }
        if ($password !== $password2) {
            Flash::error('Konfirmasi password tidak sama.');
            Response::redirect(url('auth/register'));
        }

        $model = new User();
        if ($model->existsEmail($email)) {
            Flash::error('Email sudah terdaftar.');
            Response::redirect(url('auth/register'));
        }

        $id = $model->create($name, $email, $password, 'mahasiswa');
        $newUser = $model->findById($id);
        Auth::login($newUser ?: ['id' => $id, 'name' => $name, 'role' => 'mahasiswa']);
        Flash::success('Akun berhasil dibuat.');
        Response::redirect(url('dashboard/index'));
    }

    public function logout(): void
    {
        Auth::logout();
        Response::redirect(url('auth/login'));
    }
}
