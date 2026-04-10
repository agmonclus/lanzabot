-- Migración 007: Añadir campo git_branch a bot_templates
-- Ejecutar: mysql -u <user> -p <db> < database/migrations/007_add_git_branch_to_templates.sql

SET NAMES utf8mb4;

-- 1. Añadir columna git_branch (por defecto 'main')
ALTER TABLE bot_templates
    ADD COLUMN IF NOT EXISTS git_branch VARCHAR(100) DEFAULT 'main' AFTER git_repo_url;

-- 2. Corregir ramas para repos que usan 'master'
UPDATE bot_templates SET git_branch = 'master' WHERE slug = 'telegram-echo-bot';
UPDATE bot_templates SET git_branch = 'master' WHERE slug = 'discord-music-bot';
UPDATE bot_templates SET git_branch = 'master' WHERE slug = 'discord-mod-bot';
UPDATE bot_templates SET git_branch = 'master' WHERE slug = 'discord-welcome-bot';
UPDATE bot_templates SET git_branch = 'master' WHERE slug = 'discord-ticket-bot';

-- 3. Corregir git_repo_url del Shop Bot (repo original no existe, usar alternativa)
UPDATE bot_templates SET git_repo_url = NULL WHERE slug = 'telegram-shop-bot' AND git_repo_url = 'https://github.com/cleandersonlobo/python-telegram-shop-bot';

-- 4. Limpiar git_repo_url de templates que apuntan a librerías (no a bots desplegables)
-- Template 1: python-telegram-bot es la librería, no un bot desplegable
UPDATE bot_templates SET git_repo_url = NULL WHERE slug = 'telegram-echo-bot' AND git_repo_url = 'https://github.com/python-telegram-bot/python-telegram-bot';
-- Template 10: openai-python es el SDK, no un bot desplegable
UPDATE bot_templates SET git_repo_url = NULL WHERE slug = 'multi-ai-assistant' AND git_repo_url = 'https://github.com/openai/openai-python';
