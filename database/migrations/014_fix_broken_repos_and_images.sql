-- Migración 014: Corregir repos git movidos/eliminados e imágenes Docker rotas
-- Detectados durante verificación de templates el 2026-04-15

-- ============================================================
-- 1. Repos git que fueron renombrados (301 redirect)
-- ============================================================

-- #3 telegram-gpt-bot: karfly/chatgpt_telegram_bot → father-bot/chatgpt_telegram_bot
UPDATE bot_templates
SET git_repo_url = 'https://github.com/father-bot/chatgpt_telegram_bot'
WHERE id = 3 AND git_repo_url = 'https://github.com/karfly/chatgpt_telegram_bot';

-- #60 agentscope: modelscope/agentscope → agentscope-ai/agentscope
UPDATE bot_templates
SET git_repo_url = 'https://github.com/agentscope-ai/agentscope'
WHERE id = 60 AND git_repo_url = 'https://github.com/modelscope/agentscope';

-- #96 allcontributors-bot: all-contributors/all-contributors-bot → all-contributors/app
UPDATE bot_templates
SET git_repo_url = 'https://github.com/all-contributors/app'
WHERE id = 96 AND git_repo_url = 'https://github.com/all-contributors/all-contributors-bot';

-- ============================================================
-- 2. Repo git eliminado (404)
-- ============================================================

-- #57 openclaw: BlockRunAI/OpenClaw ya no existe — desactivar plantilla
UPDATE bot_templates
SET git_repo_url = NULL,
    short_description = CONCAT('[DESACTIVADO] ', COALESCE(short_description, '')),
    is_active = 0
WHERE id = 57 AND git_repo_url = 'https://github.com/BlockRunAI/OpenClaw';

-- ============================================================
-- 3. Imágenes Docker que no existen
-- ============================================================

-- #78 medusa: medusajs/medusa:latest no existe en Docker Hub
-- Convertir a deploy desde git repo (medusa-starter-default)
UPDATE bot_templates
SET docker_image = 'node:20-alpine',
    git_repo_url = 'https://github.com/medusajs/medusa-starter-default',
    git_branch = 'master'
WHERE id = 78 AND docker_image = 'medusajs/medusa:latest';

-- #80 saleor: ghcr.io/saleor/saleor:latest no tiene tag 'latest'
-- Cambiar a deploy desde git repo (saleor/saleor)
UPDATE bot_templates
SET docker_image = 'python:3.11-slim',
    git_repo_url = 'https://github.com/saleor/saleor',
    git_branch = 'main'
WHERE id = 80 AND docker_image = 'ghcr.io/saleor/saleor:latest';
