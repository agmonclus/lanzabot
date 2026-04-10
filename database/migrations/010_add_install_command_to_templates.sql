-- Migración 010: Añadir install_command a bot_templates
-- Permite especificar un comando de instalación personalizado para nixpacks builds en Coolify

ALTER TABLE bot_templates
    ADD COLUMN install_command VARCHAR(1000) NULL AFTER git_repo_url;

-- Fix PyYAML==6.0 incompatible con Python 3.12 en chatgpt_telegram_bot
UPDATE bot_templates
SET install_command = 'sed -i "s/PyYAML==6.0$/PyYAML>=6.0.1/" requirements.txt'
WHERE slug = 'telegram-gpt-bot';
