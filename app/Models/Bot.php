<?php

namespace App\Models;

use App\Core\Database;

class Bot
{
    public static function forUser(int $userId): array
    {
        return Database::fetchAll(
            'SELECT * FROM bots WHERE user_id = ? ORDER BY created_at DESC',
            [$userId]
        );
    }

    public static function find(int $id): ?array
    {
        return Database::fetch('SELECT * FROM bots WHERE id = ?', [$id]);
    }

    public static function findForUser(int $id, int $userId): ?array
    {
        return Database::fetch('SELECT * FROM bots WHERE id = ? AND user_id = ?', [$id, $userId]);
    }

    public static function countForUser(int $userId): int
    {
        $row = Database::fetch('SELECT COUNT(*) as cnt FROM bots WHERE user_id = ?', [$userId]);
        return (int) ($row['cnt'] ?? 0);
    }

    public static function create(array $data): int
    {
        return Database::insert(
            'INSERT INTO bots (user_id, name, platform, description, docker_image, template_id) VALUES (?, ?, ?, ?, ?, ?)',
            [
                $data['user_id'],
                $data['name'],
                $data['platform']     ?? 'telegram',
                $data['description']  ?? '',
                $data['docker_image'] ?? 'python:3.11-slim',
                $data['template_id']  ?? null,
            ]
        );
    }

    public static function update(int $id, array $data): void
    {
        $sets   = [];
        $params = [];
        foreach ($data as $col => $val) {
            $sets[]   = "{$col} = ?";
            $params[] = $val;
        }
        $params[] = $id;
        Database::execute('UPDATE bots SET ' . implode(', ', $sets) . ' WHERE id = ?', $params);
    }

    public static function delete(int $id): void
    {
        Database::execute('DELETE FROM bots WHERE id = ?', [$id]);
    }

    public static function setEnvVars(int $id, array $vars): void
    {
        Database::execute(
            'UPDATE bots SET env_vars = ? WHERE id = ?',
            [json_encode($vars), $id]
        );
    }

    public static function getEnvVars(int $id): array
    {
        $bot = self::find($id);
        if (!$bot || !$bot['env_vars']) return [];
        return json_decode($bot['env_vars'], true) ?? [];
    }
}
