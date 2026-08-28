<?php
/**
 * Base Model dengan helper CRUD umum
 */

namespace App\Models;

use Core\Database;

abstract class BaseModel
{
    protected string $table;
    protected string $primaryKey = 'id';

    public function findAll(string $orderBy = ''): array
    {
        $sql = "SELECT * FROM {$this->table}";
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        return Database::fetchAll($sql);
    }

    public function find(int $id): ?array
    {
        return Database::fetch("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?", [$id]);
    }

    public function findBy(string $column, mixed $value): ?array
    {
        return Database::fetch("SELECT * FROM {$this->table} WHERE `{$column}` = ?", [$value]);
    }

    public function allWhere(string $column, mixed $value, string $orderBy = ''): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE `{$column}` = ?";
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        return Database::fetchAll($sql, [$value]);
    }

    public function create(array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($c) => ':' . $c, $columns);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        Database::query($sql, $data);
        return Database::lastInsertId();
    }

    public function update(int $id, array $data): int
    {
        if (empty($data)) {
            return 0;
        }

        $sets = implode(', ', array_map(fn($c) => "`{$c}` = :{$c}", array_keys($data)));
        $data[$this->primaryKey] = $id;

        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s = :%s',
            $this->table,
            $sets,
            $this->primaryKey,
            $this->primaryKey
        );

        return Database::execute($sql, $data);
    }

    public function delete(int $id): int
    {
        return Database::execute("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?", [$id]);
    }

    public function count(): int
    {
        $row = Database::fetch("SELECT COUNT(*) as c FROM {$this->table}");
        return (int) ($row['c'] ?? 0);
    }
}