<?php
/**
 * Model Jenis Bel
 */

namespace App\Models;

class BellType extends BaseModel
{
    protected string $table = 'bell_types';

    public const CATEGORIES = [
        'Bel Masuk',
        'Bel Pergantian Pelajaran',
        'Bel Istirahat',
        'Bel Masuk Setelah Istirahat',
        'Bel Pulang',
        'Bel Khusus',
    ];

    public function allByCategory(): array
    {
        return $this->findAll('category, name');
    }
}