-- Migración 009: Eliminar templates que no son bots funcionales autónomos
-- Se conservan solo los 12 templates con repos de bots reales desplegables
-- Ejecutar: mysql -u root -p lanzabot < database/migrations/009_delete_non_functional_templates.sql

SET NAMES utf8mb4;

-- Limpiar referencia template_id en bots que apunten a templates que vamos a borrar
-- (para no dejar datos huérfanos)
UPDATE bots SET template_id = NULL
WHERE template_id NOT IN (2, 3, 4, 6, 7, 8, 9, 17, 28, 29, 30, 32);

-- Borrar todos los templates excepto los 12 bots funcionales
DELETE FROM bot_templates
WHERE id NOT IN (2, 3, 4, 6, 7, 8, 9, 17, 28, 29, 30, 32);
