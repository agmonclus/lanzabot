-- Migración 001: Añade autenticación por email
-- Ejecutar en instalaciones existentes:
--   mysql -u <user> -p <db> < database/migrations/001_add_email_auth.sql

SET NAMES utf8mb4;

-- Nuevas columnas en users (sólo si no existen)
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS password_hash    VARCHAR(255) NULL AFTER avatar,
    ADD COLUMN IF NOT EXISTS email_verified_at TIMESTAMP NULL AFTER password_hash;

-- Tabla de tokens de autenticación
CREATE TABLE IF NOT EXISTS auth_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(128) UNIQUE NOT NULL,
    type ENUM('verify_email','reset_password') NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
