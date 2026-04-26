-- Migración 015: Tabla de bases de datos de usuario
-- Ejecutar: mysql -u <user> -p <db> < database/migrations/015_user_databases.sql

CREATE TABLE IF NOT EXISTS user_databases (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    label       VARCHAR(100) NOT NULL,                  -- nombre amigable elegido por el usuario
    type        ENUM('postgresql','mongodb') NOT NULL,
    db_name     VARCHAR(64) NOT NULL,                   -- nombre real en el servidor (lzb{id})
    db_user     VARCHAR(64) NOT NULL,                   -- usuario real en el servidor (lzb{id})
    db_password_enc TEXT NOT NULL,                      -- contraseña encriptada (AES-256-CBC)
    db_host     VARCHAR(255) NOT NULL,
    db_port     SMALLINT UNSIGNED NOT NULL,
    status      ENUM('creating','active','error') DEFAULT 'creating',
    error_msg   TEXT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
