<?php

namespace App\Models;

use App\Core\Database;

class User
{
    public static function find(int $id): ?array
    {
        return Database::fetch('SELECT * FROM users WHERE id = ?', [$id]);
    }

    public static function findByEmail(string $email): ?array
    {
        return Database::fetch('SELECT * FROM users WHERE email = ?', [$email]);
    }

    public static function findByGoogleId(string $id): ?array
    {
        return Database::fetch('SELECT * FROM users WHERE google_id = ?', [$id]);
    }

    public static function findByDiscordId(string $id): ?array
    {
        return Database::fetch('SELECT * FROM users WHERE discord_id = ?', [$id]);
    }

    public static function findByTelegramId(string $id): ?array
    {
        return Database::fetch('SELECT * FROM users WHERE telegram_id = ?', [$id]);
    }

    public static function create(array $data): int
    {
        return Database::insert(
            'INSERT INTO users (email, name, avatar, google_id, discord_id, telegram_id) VALUES (?, ?, ?, ?, ?, ?)',
            [
                $data['email']       ?? null,
                $data['name']        ?? '',
                $data['avatar']      ?? null,
                $data['google_id']   ?? null,
                $data['discord_id']  ?? null,
                $data['telegram_id'] ?? null,
            ]
        );
    }

    /**
     * Crea un usuario con email y contraseña (registro propio).
     * El email no se marca como verificado hasta que el usuario hace clic en el enlace.
     */
    public static function createWithPassword(string $email, string $name, string $passwordHash): int
    {
        return Database::insert(
            'INSERT INTO users (email, name, password_hash) VALUES (?, ?, ?)',
            [$email, $name, $passwordHash]
        );
    }

    /**
     * Devuelve el usuario si el email y contraseña son correctos.
     */
    public static function findByEmailAndPassword(string $email, string $password): ?array
    {
        $user = self::findByEmail($email);
        if (!$user || empty($user['password_hash'])) {
            return null;
        }
        if (!password_verify($password, $user['password_hash'])) {
            return null;
        }
        return $user;
    }

    /**
     * Marca el email del usuario como verificado.
     */
    public static function setEmailVerified(int $userId): void
    {
        Database::execute(
            'UPDATE users SET email_verified_at = NOW() WHERE id = ?',
            [$userId]
        );
    }

    /**
     * Actualiza la contraseña de un usuario.
     */
    public static function updatePassword(int $userId, string $passwordHash): void
    {
        Database::execute(
            'UPDATE users SET password_hash = ? WHERE id = ?',
            [$passwordHash, $userId]
        );
    }

    // ---- Tokens de autenticación ----

    /**
     * Crea (o reemplaza si ya existe uno del mismo tipo) un token de auth para el usuario.
     * $type: 'verify_email' | 'reset_password'
     */
    public static function createAuthToken(int $userId, string $type): string
    {
        // Borrar tokens anteriores del mismo tipo para este usuario
        Database::execute(
            'DELETE FROM auth_tokens WHERE user_id = ? AND type = ?',
            [$userId, $type]
        );

        $token     = bin2hex(random_bytes(32));
        $expiresAt = $type === 'reset_password'
            ? date('Y-m-d H:i:s', time() + 3600)        // 1 hora
            : date('Y-m-d H:i:s', time() + 86400);      // 24 horas

        Database::insert(
            'INSERT INTO auth_tokens (user_id, token, type, expires_at) VALUES (?, ?, ?, ?)',
            [$userId, $token, $type, $expiresAt]
        );

        return $token;
    }

    /**
     * Busca un token válido (no expirado). Devuelve la fila o null.
     */
    public static function findAuthToken(string $token, string $type): ?array
    {
        return Database::fetch(
            'SELECT * FROM auth_tokens WHERE token = ? AND type = ? AND expires_at > NOW()',
            [$token, $type]
        );
    }

    /**
     * Elimina un token por su id.
     */
    public static function deleteAuthToken(int $tokenId): void
    {
        Database::execute('DELETE FROM auth_tokens WHERE id = ?', [$tokenId]);
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
        Database::execute('UPDATE users SET ' . implode(', ', $sets) . ' WHERE id = ?', $params);
    }

    public static function upsertOAuth(
        string  $provider,
        string  $providerId,
        string  $email,
        string  $name,
        ?string $avatar
    ): int {
        $field = $provider . '_id';
        $existing = self::{'findBy' . ucfirst($provider) . 'Id'}($providerId);

        if ($existing) {
            self::update($existing['id'], ['name' => $name, 'avatar' => $avatar]);
            return $existing['id'];
        }

        // Check if email already exists
        if ($email) {
            $byEmail = self::findByEmail($email);
            if ($byEmail) {
                self::update($byEmail['id'], [$field => $providerId, 'avatar' => $avatar]);
                return $byEmail['id'];
            }
        }

        return self::create([
            'email'  => $email,
            'name'   => $name,
            'avatar' => $avatar,
            $field   => $providerId,
        ]);
    }
}
