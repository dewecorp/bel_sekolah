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
}