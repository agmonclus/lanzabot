-- Lanzabot.com Database Schema
-- Run: mysql -u alfredo -pcrealogica lanzabot < database/schema.sql

SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- Users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE,
    name VARCHAR(255) NOT NULL,
    avatar VARCHAR(500),
    password_hash VARCHAR(255) NULL,
    email_verified_at TIMESTAMP NULL,
    google_id VARCHAR(255),
    discord_id VARCHAR(255),
    telegram_id VARCHAR(255),
    stripe_customer_id VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_google_id (google_id),
    INDEX idx_discord_id (discord_id),
    INDEX idx_telegram_id (telegram_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tokens de autenticación (verificación de email y restablecimiento de contraseña)
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

-- Plans
CREATE TABLE IF NOT EXISTS plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    price_monthly DECIMAL(10,2) NOT NULL DEFAULT 0,
    max_bots INT NOT NULL DEFAULT 1,
    ram_mb INT NOT NULL DEFAULT 128,
    disk_gb INT NOT NULL DEFAULT 0,
    disk_temp_mb INT NOT NULL DEFAULT 0,
    max_databases INT NOT NULL DEFAULT 0,
    has_eco_mode TINYINT(1) DEFAULT 0,
    has_redis TINYINT(1) DEFAULT 0,
    has_backups TINYINT(1) DEFAULT 0,
    stripe_price_id VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Subscriptions
CREATE TABLE IF NOT EXISTS subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    plan_id INT NOT NULL,
    stripe_subscription_id VARCHAR(255),
    stripe_customer_id VARCHAR(255),
    status ENUM('active','canceled','past_due','trialing','unpaid','free') DEFAULT 'active',
    current_period_end TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES plans(id),
    INDEX idx_user_id (user_id),
    INDEX idx_stripe_subscription (stripe_subscription_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bots
CREATE TABLE IF NOT EXISTS bots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    platform ENUM('telegram','discord','other') DEFAULT 'telegram',
    description TEXT,
    coolify_service_uuid VARCHAR(255),
    coolify_app_uuid VARCHAR(255),
    coolify_status VARCHAR(50) DEFAULT 'stopped',
    env_vars JSON,
    code_uploaded BOOLEAN DEFAULT FALSE,
    code_path VARCHAR(500),
    docker_image VARCHAR(255) DEFAULT 'python:3.11-slim',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Payments
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    stripe_invoice_id VARCHAR(255),
    stripe_payment_intent_id VARCHAR(255),
    amount INT NOT NULL DEFAULT 0,
    currency VARCHAR(10) DEFAULT 'eur',
    status VARCHAR(50) DEFAULT 'pending',
    description VARCHAR(500),
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed plans (2026)
INSERT INTO plans (slug, name, price_monthly, max_bots, ram_mb, disk_gb, disk_temp_mb, max_databases, has_eco_mode, has_redis, has_backups, sort_order) VALUES
('free',    'Free',    0.00,  1,  256,  0,   500, 0, 1, 0, 0, 1),
('starter', 'Starter', 5.99,  4,  1024, 15,  0,   1, 0, 0, 0, 2),
('medium',  'Medium',  11.99, 10, 2560, 40,  0,   2, 0, 0, 0, 3),
('pro',     'Pro',     24.99, 25, 6144, 100, 0,   5, 0, 1, 1, 4),
('custom',  'Custom',  0.00,  0,  0,    0,   0,   0, 0, 0, 0, 5)
ON DUPLICATE KEY UPDATE
    name=VALUES(name), price_monthly=VALUES(price_monthly), max_bots=VALUES(max_bots),
    ram_mb=VALUES(ram_mb), disk_gb=VALUES(disk_gb), disk_temp_mb=VALUES(disk_temp_mb),
    max_databases=VALUES(max_databases), has_eco_mode=VALUES(has_eco_mode),
    has_redis=VALUES(has_redis), has_backups=VALUES(has_backups), sort_order=VALUES(sort_order);
