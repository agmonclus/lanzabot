-- Migración 011: Eliminar templates con repos eliminados de GitHub
-- Template 6 (discord-welcome-bot): repo Androz2091/welcome-bot eliminado
-- Template 7 (ai-image-bot): repo nickhilton/dalle-telegram-bot eliminado

DELETE FROM bot_templates WHERE id IN (6, 7);
