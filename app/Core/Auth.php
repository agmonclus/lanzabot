<?php

namespace App\Core;

use App\Models\User;
use App\Models\Subscription;

class Auth
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function check(): bool
    {
        self::start();
        return isset($_SESSION['user_id']);
    }

    public static function user(): ?array
    {
        if (!self::check()) return null;
        return User::find($_SESSION['user_id']);
    }

    public static function login(array $user): void
    {
        self::start();
        $_SESSION['user_id'] = $user['id'];
        session_regenerate_id(true);
    }

    public static function logout(): void
    {
        self::start();
        session_destroy();
    }

    public static function require(): void
    {
        if (!self::check()) {
            header('Location: ' . APP_URL . '/login');
            exit;
        }
    }

    public static function subscription(): ?array
    {
        $user = self::user();
        if (!$user) return null;
        return Subscription::getActiveForUser($user['id']);
    }

    public static function plan(): ?array
    {
        $sub = self::subscription();
        if (!$sub) {
            // Return free plan info
            return Database::fetch('SELECT * FROM plans WHERE slug = ?', ['free']);
        }
        return Database::fetch('SELECT * FROM plans WHERE id = ?', [$sub['plan_id']]);
    }

    public static function csrfToken(): string
    {
        self::start();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrf(string $token): bool
    {
        self::start();
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function flash(string $key, ?string $message = null): ?string
    {
        self::start();
        if ($message !== null) {
            $_SESSION['flash'][$key] = $message;
            return null;
        }
        $value = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $value;
    }
}
