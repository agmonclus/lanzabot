<?php

namespace App\Models;

use App\Core\Database;

class BotTemplate
{
    public static function all(): array
    {
        return Database::fetchAll('SELECT * FROM bot_templates WHERE is_active = 1 ORDER BY sort_order, name');
    }

    public static function allIncludingInactive(): array
    {
        return Database::fetchAll('SELECT * FROM bot_templates ORDER BY sort_order, name');
    }

    public static function find(int $id): ?array
    {
        return Database::fetch('SELECT * FROM bot_templates WHERE id = ?', [$id]);
    }

    public static function findBySlug(string $slug): ?array
    {
        return Database::fetch('SELECT * FROM bot_templates WHERE slug = ?', [$slug]);
    }

    public static function featured(): array
    {
        return Database::fetchAll(
            'SELECT * FROM bot_templates WHERE is_active = 1 AND is_featured = 1 ORDER BY sort_order'
        );
    }

    public static function byPlatform(string $platform): array
    {
        return Database::fetchAll(
            'SELECT * FROM bot_templates WHERE is_active = 1 AND platform = ? ORDER BY sort_order',
            [$platform]
        );
    }

    public static function byCategory(string $category): array
    {
        return Database::fetchAll(
            'SELECT * FROM bot_templates WHERE is_active = 1 AND category = ? ORDER BY sort_order',
            [$category]
        );
    }

    public static function byPlatformAndCategory(string $platform, string $category): array
    {
        return Database::fetchAll(
            'SELECT * FROM bot_templates WHERE is_active = 1 AND platform = ? AND category = ? ORDER BY sort_order',
            [$platform, $category]
        );
    }

    public static function search(string $query): array
    {
        $like = '%' . $query . '%';
        return Database::fetchAll(
            'SELECT * FROM bot_templates WHERE is_active = 1 AND (name LIKE ? OR description LIKE ? OR tags LIKE ?) ORDER BY sort_order',
            [$like, $like, $like]
        );
    }

    public static function create(array $data): int
    {
        return Database::insert(
            'INSERT INTO bot_templates (slug, name, description, short_description, platform, category, icon, docker_image, git_repo_url, default_env_vars, required_env_vars, ram_mb_min, min_plan_slug, difficulty, tags, documentation_url, setup_instructions, is_featured, is_active, sort_order)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $data['slug'],
                $data['name'],
                $data['description'] ?? '',
                $data['short_description'] ?? '',
                $data['platform'] ?? 'telegram',
                $data['category'] ?? 'utility',
                $data['icon'] ?? '🤖',
                $data['docker_image'] ?? 'python:3.11-slim',
                $data['git_repo_url'] ?? null,
                $data['default_env_vars'] ?? '{}',
                $data['required_env_vars'] ?? '[]',
                (int)($data['ram_mb_min'] ?? 128),
                $data['min_plan_slug'] ?? 'free',
                $data['difficulty'] ?? 'easy',
                $data['tags'] ?? '',
                $data['documentation_url'] ?? null,
                $data['setup_instructions'] ?? null,
                (int)($data['is_featured'] ?? 0),
                (int)($data['is_active'] ?? 1),
                (int)($data['sort_order'] ?? 0),
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
        Database::execute('UPDATE bot_templates SET ' . implode(', ', $sets) . ' WHERE id = ?', $params);
    }

    public static function delete(int $id): void
    {
        Database::execute('DELETE FROM bot_templates WHERE id = ?', [$id]);
    }

    public static function incrementInstallCount(int $id): void
    {
        Database::execute('UPDATE bot_templates SET install_count = install_count + 1 WHERE id = ?', [$id]);
    }

    public static function count(): int
    {
        $row = Database::fetch('SELECT COUNT(*) as cnt FROM bot_templates');
        return (int)($row['cnt'] ?? 0);
    }

    public static function categories(): array
    {
        return Database::fetchAll('SELECT DISTINCT category FROM bot_templates WHERE is_active = 1 ORDER BY category');
    }

    public static function getDefaultEnvVars(int $id): array
    {
        $tpl = self::find($id);
        if (!$tpl || !$tpl['default_env_vars']) return [];
        return json_decode($tpl['default_env_vars'], true) ?? [];
    }

    public static function getRequiredEnvVars(int $id): array
    {
        $tpl = self::find($id);
        if (!$tpl || !$tpl['required_env_vars']) return [];
        return json_decode($tpl['required_env_vars'], true) ?? [];
    }
}
