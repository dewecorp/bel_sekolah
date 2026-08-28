<?php
/**
 * Otentikasi berbasis session
 */

namespace Core;

use PDO;

class Auth
{
    public static function attempt(string $username, string $password): bool
    {
        $user = Database::fetch('SELECT * FROM users WHERE username = ?', [$username]);

        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['name'];
        session_regenerate_id(true);

        return true;
    }

    public static function check(): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        return isset($_SESSION['user_id']);
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }
        return Database::fetch(
            'SELECT id, username, name, role FROM users WHERE id = ?',
            [$_SESSION['user_id']]
        );
    }

    public static function id(): ?int
    {
        if (!self::check()) {
            return null;
        }
        return (int) $_SESSION['user_id'];
    }

    public static function logout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $_SESSION = [];
        session_destroy();
    }

    public static function changePassword(int $userId, string $oldPassword, string $newPassword): array
    {
        $user = Database::fetch('SELECT password FROM users WHERE id = ?', [$userId]);
        if (!$user || !password_verify($oldPassword, $user['password'])) {
            return ['error' => 'Password lama salah'];
        }

        if (strlen($newPassword) < 6) {
            return ['error' => 'Password baru minimal 6 karakter'];
        }

        Database::execute('UPDATE users SET password = ? WHERE id = ?', [
            password_hash($newPassword, PASSWORD_DEFAULT),
            $userId,
        ]);

        return ['success' => true];
    }
}