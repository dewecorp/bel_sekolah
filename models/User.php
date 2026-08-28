<?php
/**
 * Model Pengguna
 */

namespace App\Models;

class User extends BaseModel
{
    protected string $table = 'users';

    public function findByUsername(string $username): ?array
    {
        return $this->findBy('username', $username);
    }

    public function publicProfile(int $id): ?array
    {
        return Database::fetch(
            'SELECT id, username, name, role, created_at FROM users WHERE id = ?',
            [$id]
        );
    }
}