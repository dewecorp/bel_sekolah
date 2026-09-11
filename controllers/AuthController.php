<?php
/**
 * Login/Logout Controller
 */

namespace App\Controllers;

use Core\Auth;
use Core\Controller;
use Core\App;

class AuthController extends Controller
{
    public function loginPage(): void
    {
        if (Auth::check()) {
            $this->redirect('/admin/dashboard');
        }
        $this->view('auth/login', [], 'auth.php');
    }

    public function login(): void
    {
        $username = $this->input('username', '');
        $password = $this->input('password', '');

        $errors = [];

        if (trim($username) === '' || $password === '') {
            $errors[] = 'Username dan password harus diisi';
        }

        if (empty($errors)) {
            if (!Auth::attempt($username, $password)) {
                $errors[] = 'Username atau password salah';
            }
        }

        if (!empty($errors)) {
            if (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
                $this->json(['error' => $errors[0]], 401);
                return;
            }
            $this->view('auth/login', ['error' => $errors[0]], 'auth.php');
            return;
        }

        // JSON login (ajax)
        if (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
            $this->json(['success' => true, 'redirect' => '/admin/dashboard']);
            return;
        }

        $this->redirect('/admin/dashboard');
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('/auth/login');
    }

    public function passwordPage(): void
    {
        if (!Auth::check()) {
            $this->redirect('/auth/login');
        }
        $this->view('auth/password', [], 'admin.php');
    }

    public function changePassword(): void
    {
        if (!Auth::check()) {
            $this->json(['error' => 'Belum login'], 401);
            return;
        }
        $data = $this->inputAll();
        $old = $data['old_password'] ?? '';
        $new = $data['new_password'] ?? '';
        $confirm = $data['confirm_password'] ?? $data['new_password_confirm'] ?? '';

        if (trim($old) === '') {
            $this->json(['error' => 'Password lama wajib diisi'], 400);
            return;
        }
        if (strlen($new) < 6) {
            $this->json(['error' => 'Password baru minimal 6 karakter'], 400);
            return;
        }
        if ($new !== $confirm) {
            $this->json(['error' => 'Konfirmasi password tidak cocok'], 400);
            return;
        }

        $result = Auth::changePassword(Auth::id(), $old, $new);
        if (!empty($result['error'])) {
            $this->json(['error' => $result['error']], 400);
            return;
        }
        $this->json(['message' => 'Password berhasil diubah']);
    }
}