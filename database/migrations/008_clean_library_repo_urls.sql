-- Migración 008: Limpiar git_repo_url de templates que apuntan a librerías/SDKs
-- Estos repos no son bots desplegables y causarían reinicio continuo en Coolify
-- Ejecutar: mysql -u root -p lanzabot < database/migrations/008_clean_library_repo_urls.sql

SET NAMES utf8mb4;

-- slackapi/bolt-js → framework de Slack, no un bot
UPDATE bot_templates SET git_repo_url = NULL WHERE id = 11 AND git_repo_url LIKE '%slackapi/bolt-js%';

-- nicehash/whatsapp-api-client → cliente API
UPDATE bot_templates SET git_repo_url = NULL WHERE id = 12 AND git_repo_url LIKE '%whatsapp-api-client%';

-- tmijs/tmi.js → librería de chat Twitch
UPDATE bot_templates SET git_repo_url = NULL WHERE id = 13 AND git_repo_url LIKE '%tmijs/tmi.js%';

-- praw-dev/praw → wrapper API de Reddit
UPDATE bot_templates SET git_repo_url = NULL WHERE id = 14 AND git_repo_url LIKE '%praw-dev/praw%';

-- halcy/Mastodon.py → librería API de Mastodon
UPDATE bot_templates SET git_repo_url = NULL WHERE id = 15 AND git_repo_url LIKE '%halcy/Mastodon.py%';

-- turt2live/matrix-bot-sdk → SDK
UPDATE bot_templates SET git_repo_url = NULL WHERE id = 16 AND git_repo_url LIKE '%matrix-bot-sdk%';

-- nicehash/node-web-scraper → librería
UPDATE bot_templates SET git_repo_url = NULL WHERE id = 18 AND git_repo_url LIKE '%node-web-scraper%';

-- Androz2091/discord-xp → módulo npm de XP
UPDATE bot_templates SET git_repo_url = NULL WHERE id = 19 AND git_repo_url LIKE '%discord-xp%';

-- nicehash/webhook-relay → librería/repo inexistente
UPDATE bot_templates SET git_repo_url = NULL WHERE id = 21 AND git_repo_url LIKE '%webhook-relay%';

-- node-cron/node-cron → librería npm de cron
UPDATE bot_templates SET git_repo_url = NULL WHERE id = 22 AND git_repo_url LIKE '%node-cron/node-cron%';

-- openai/whisper → modelo ML, no un bot
UPDATE bot_templates SET git_repo_url = NULL WHERE id = 23 AND git_repo_url LIKE '%openai/whisper%';

-- Stability-AI/platform → docs/plataforma, no un bot
UPDATE bot_templates SET git_repo_url = NULL WHERE id = 24 AND git_repo_url LIKE '%Stability-AI/platform%';

-- man-c/pycoingecko → wrapper API de CoinGecko
UPDATE bot_templates SET git_repo_url = NULL WHERE id = 25 AND git_repo_url LIKE '%pycoingecko%';

-- Androz2091/discord-giveaways → módulo npm de giveaways
UPDATE bot_templates SET git_repo_url = NULL WHERE id = 26 AND git_repo_url LIKE '%discord-giveaways%';

-- nicehash/whatsapp-api-client (duplicado del 12)
UPDATE bot_templates SET git_repo_url = NULL WHERE id = 31 AND git_repo_url LIKE '%whatsapp-api-client%';

-- TrackerNetwork/tracker.gg → no es un bot
UPDATE bot_templates SET git_repo_url = NULL WHERE id = 33 AND git_repo_url LIKE '%tracker.gg%';

-- Androz2091/discord-giveaways (duplicado, usado para trivia-bot)
UPDATE bot_templates SET git_repo_url = NULL WHERE id = 39 AND git_repo_url LIKE '%discord-giveaways%';

-- openai/openai-python → SDK (usado en summary-bot, support-bot, code-bot)
UPDATE bot_templates SET git_repo_url = NULL WHERE id = 42 AND git_repo_url LIKE '%openai/openai-python%';
UPDATE bot_templates SET git_repo_url = NULL WHERE id = 46 AND git_repo_url LIKE '%openai/openai-python%';
UPDATE bot_templates SET git_repo_url = NULL WHERE id = 49 AND git_repo_url LIKE '%openai/openai-python%';

-- discordjs/discord.js → SDK (usado en embeds-bot, pomodoro-bot, analytics-bot)
UPDATE bot_templates SET git_repo_url = NULL WHERE id = 44 AND git_repo_url LIKE '%discordjs/discord.js%';
UPDATE bot_templates SET git_repo_url = NULL WHERE id = 48 AND git_repo_url LIKE '%discordjs/discord.js%';
UPDATE bot_templates SET git_repo_url = NULL WHERE id = 50 AND git_repo_url LIKE '%discordjs/discord.js%';

-- nicehash/node-web-scraper (duplicado del 18, usado para seo-bot)
UPDATE bot_templates SET git_repo_url = NULL WHERE id = 45 AND git_repo_url LIKE '%node-web-scraper%';
