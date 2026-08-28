<?php
/**
 * Model File Audio Bel
 */

namespace App\Models;

use Core\Database;

class Audio extends BaseModel
{
    protected string $table = 'audio_files';

    public function allWithType(): array
    {
        return Database::fetchAll("
            SELECT af.*, bt.name AS bell_type_name
            FROM audio_files af
            LEFT JOIN bell_types bt ON af.bell_type_id = bt.id
            ORDER BY af.name
        ");
    }

    public function getDefault(): ?array
    {
        return Database::fetch(
            'SELECT * FROM audio_files WHERE is_default = 1 LIMIT 1'
        );
    }

    public function unsetAllDefaults(): void
    {
        Database::execute('UPDATE audio_files SET is_default = 0');
    }
}