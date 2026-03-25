<?php

namespace App\Models;

use App\Core\Database;

class Plan
{
    public static function all(): array
    {
        return Database::fetchAll('SELECT * FROM plans WHERE is_active = 1 ORDER BY sort_order');
    }

    public static function find(int $id): ?array
    {
        return Database::fetch('SELECT * FROM plans WHERE id = ?', [$id]);
    }

    public static function findBySlug(string $slug): ?array
    {
        return Database::fetch('SELECT * FROM plans WHERE slug = ?', [$slug]);
    }
}
