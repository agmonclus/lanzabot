<?php

namespace App\Models;

use App\Core\Database;

class UserDatabase
{
    // ---- CRUD ----

    public static function forUser(int $userId): array
    {
        return Database::fetchAll(
            'SELECT * FROM user_databases WHERE user_id = ? ORDER BY created_at DESC',
            [$userId]
        );
    }

    public static function find(int $id): ?array
    {
        return Database::fetch('SELECT * FROM user_databases WHERE id = ?', [$id]);
    }

    public static function findForUser(int $id, int $userId): ?array
    {
        return Database::fetch(
            'SELECT * FROM user_databases WHERE id = ? AND user_id = ?',
            [$id, $userId]
        );
    }

    public static function countForUser(int $userId): int
    {
        $row = Database::fetch(
            "SELECT COUNT(*) AS cnt FROM user_databases WHERE user_id = ? AND status != 'error'",
            [$userId]
        );
        return (int)($row['cnt'] ?? 0);
    }

    /**
     * Inserta el registro inicial con status='creating'.
     * Devuelve el nuevo ID para usarlo como identificador único del recurso.
     */
    public static function createPending(int $userId, string $label, string $type): int
    {
        return Database::insert(
            'INSERT INTO user_databases (user_id, label, type, db_name, db_user, db_password_enc, db_host, db_port, status)
             VALUES (?, ?, ?, \'\', \'\', \'\', \'\', 0, \'creating\')',
            [$userId, $label, $type]
        );
    }

    public static function activate(int $id, array $data): void
    {
        Database::execute(
            'UPDATE user_databases SET db_name=?, db_user=?, db_password_enc=?, db_host=?, db_port=?, status=\'active\', error_msg=NULL
             WHERE id=?',
            [
                $data['db_name'],
                $data['db_user'],
                $data['db_password_enc'],
                $data['db_host'],
                $data['db_port'],
                $id,
            ]
        );
    }

    public static function setError(int $id, string $msg): void
    {
        Database::execute(
            "UPDATE user_databases SET status='error', error_msg=? WHERE id=?",
            [$msg, $id]
        );
    }

    public static function updatePassword(int $id, string $encryptedPassword): void
    {
        Database::execute(
            'UPDATE user_databases SET db_password_enc=? WHERE id=?',
            [$encryptedPassword, $id]
        );
    }

    public static function delete(int $id): void
    {
        Database::execute('DELETE FROM user_databases WHERE id=?', [$id]);
    }

    // ---- Cifrado de contraseña (AES-256-CBC con APP_SECRET) ----

    public static function encryptPassword(string $plain): string
    {
        $key = substr(hash('sha256', APP_SECRET, true), 0, 32);
        $iv  = random_bytes(16);
        $enc = openssl_encrypt($plain, 'AES-256-CBC', $key, 0, $iv);
        return base64_encode($iv . $enc);
    }

    public static function decryptPassword(string $stored): string
    {
        $key  = substr(hash('sha256', APP_SECRET, true), 0, 32);
        $data = base64_decode($stored);
        $iv   = substr($data, 0, 16);
        $enc  = substr($data, 16);
        return (string)openssl_decrypt($enc, 'AES-256-CBC', $key, 0, $iv);
    }

    // ---- Helpers de naming ----

    /**
     * Nombre del recurso de BD/usuario en el servidor compartido, basado en el ID de fila.
     * Ej: lzb42  →  único, válido en PG (≤63 chars) y Mongo.
     */
    public static function resourceName(int $rowId): string
    {
        return 'lzb' . $rowId;
    }
}
