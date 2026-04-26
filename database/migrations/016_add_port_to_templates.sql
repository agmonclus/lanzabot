-- Migración 016: añadir columna exposed_port a bot_templates
-- Permite que cada template declare el puerto que expone su contenedor.
-- Por defecto 8080 para no romper templates existentes.

ALTER TABLE bot_templates
    ADD COLUMN exposed_port SMALLINT UNSIGNED NOT NULL DEFAULT 8080
    AFTER ram_mb_min;

-- n8n escucha en 5678
UPDATE bot_templates SET exposed_port = 5678 WHERE slug = 'n8n';
