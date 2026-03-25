-- Migración 002: Actualización de planes 2026
-- Ejecutar: mysql -u <user> -p <db> < database/migrations/002_update_plans_2026.sql

-- Renombrar precio semanal a mensual y añadir nuevas columnas de características
ALTER TABLE plans
    CHANGE price_weekly price_monthly DECIMAL(10,2) NOT NULL DEFAULT 0,
    ADD COLUMN disk_temp_mb INT NOT NULL DEFAULT 0 AFTER disk_gb,
    ADD COLUMN has_eco_mode TINYINT(1) DEFAULT 0 AFTER max_databases,
    ADD COLUMN has_redis TINYINT(1) DEFAULT 0 AFTER has_eco_mode,
    ADD COLUMN has_backups TINYINT(1) DEFAULT 0 AFTER has_redis;

-- Actualizar plan Free "El Laboratorio"
UPDATE plans SET
    name = 'Free', price_monthly = 0.00, max_bots = 1, ram_mb = 256,
    disk_gb = 0, disk_temp_mb = 500, max_databases = 0,
    has_eco_mode = 1, has_redis = 0, has_backups = 0, sort_order = 1
WHERE slug = 'free';

-- Actualizar plan Starter "El Lanzamiento"
UPDATE plans SET
    name = 'Starter', price_monthly = 5.99, max_bots = 4, ram_mb = 1024,
    disk_gb = 15, disk_temp_mb = 0, max_databases = 1,
    has_eco_mode = 0, has_redis = 0, has_backups = 0, sort_order = 2
WHERE slug = 'starter';

-- Actualizar plan Medium "Potencia Total"
UPDATE plans SET
    name = 'Medium', price_monthly = 11.99, max_bots = 10, ram_mb = 2560,
    disk_gb = 40, disk_temp_mb = 0, max_databases = 2,
    has_eco_mode = 0, has_redis = 0, has_backups = 0, sort_order = 3
WHERE slug = 'medium';

-- Actualizar plan Pro "La Central de Bots"
UPDATE plans SET
    name = 'Pro', price_monthly = 24.99, max_bots = 25, ram_mb = 6144,
    disk_gb = 100, disk_temp_mb = 0, max_databases = 5,
    has_eco_mode = 0, has_redis = 1, has_backups = 1, sort_order = 4
WHERE slug = 'pro';

-- Insertar planes que no existan (en caso de base de datos nueva)
INSERT IGNORE INTO plans (slug, name, price_monthly, max_bots, ram_mb, disk_gb, disk_temp_mb, max_databases, has_eco_mode, has_redis, has_backups, sort_order, is_active)
VALUES
    ('free',    'Free',    0.00,  1,  256,  0,   500, 0, 1, 0, 0, 1, 1),
    ('starter', 'Starter', 5.99,  4,  1024, 15,  0,   1, 0, 0, 0, 2, 1),
    ('medium',  'Medium',  11.99, 10, 2560, 40,  0,   2, 0, 0, 0, 3, 1),
    ('pro',     'Pro',     24.99, 25, 6144, 100, 0,   5, 0, 1, 1, 4, 1),
    ('custom',  'Custom',  0.00,  0,  0,    0,   0,   0, 0, 0, 0, 5, 1);
