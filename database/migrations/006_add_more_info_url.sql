-- Migración 006: Añadir campo more_info_url a bot_templates
-- URL del fabricante / página principal del proyecto

ALTER TABLE bot_templates
    ADD COLUMN more_info_url VARCHAR(500) NULL DEFAULT NULL AFTER documentation_url;

-- Actualizar plantillas existentes con la URL del fabricante
UPDATE bot_templates SET more_info_url = 'https://python-telegram-bot.org' WHERE id = 1;
UPDATE bot_templates SET more_info_url = 'https://github.com/eritislami/evobot' WHERE id = 2;
UPDATE bot_templates SET more_info_url = 'https://github.com/karfly/chatgpt_telegram_bot' WHERE id = 3;
UPDATE bot_templates SET more_info_url = 'https://github.com/jagrosh/Vortex' WHERE id = 4;
UPDATE bot_templates SET more_info_url = 'https://github.com/cleandersonlobo/python-telegram-shop-bot' WHERE id = 5;
UPDATE bot_templates SET more_info_url = 'https://github.com/Androz2091/welcome-bot' WHERE id = 6;
UPDATE bot_templates SET more_info_url = 'https://openai.com/dall-e' WHERE id = 7;
UPDATE bot_templates SET more_info_url = 'https://github.com/Rongronggg9/RSS-to-Telegram-Bot' WHERE id = 8;
UPDATE bot_templates SET more_info_url = 'https://discordtickets.app' WHERE id = 9;
UPDATE bot_templates SET more_info_url = 'https://platform.openai.com' WHERE id = 10;
UPDATE bot_templates SET more_info_url = 'https://api.slack.com/bolt' WHERE id = 11;
UPDATE bot_templates SET more_info_url = 'https://business.whatsapp.com' WHERE id = 12;
UPDATE bot_templates SET more_info_url = 'https://tmijs.com' WHERE id = 13;
UPDATE bot_templates SET more_info_url = 'https://praw.readthedocs.io' WHERE id = 14;
UPDATE bot_templates SET more_info_url = 'https://mastodonpy.readthedocs.io' WHERE id = 15;
UPDATE bot_templates SET more_info_url = 'https://github.com/turt2live/matrix-bot-sdk' WHERE id = 16;
UPDATE bot_templates SET more_info_url = 'https://uptime.kuma.pet' WHERE id = 17;
UPDATE bot_templates SET more_info_url = 'https://github.com/nicehash/node-web-scraper' WHERE id = 18;
UPDATE bot_templates SET more_info_url = 'https://github.com/Androz2091/discord-xp' WHERE id = 19;
UPDATE bot_templates SET more_info_url = 'https://github.com/bot-base/telegram-bot-template' WHERE id = 20;
UPDATE bot_templates SET more_info_url = 'https://github.com/nicehash/webhook-relay' WHERE id = 21;
UPDATE bot_templates SET more_info_url = 'https://github.com/node-cron/node-cron' WHERE id = 22;
UPDATE bot_templates SET more_info_url = 'https://openai.com/research/whisper' WHERE id = 23;
UPDATE bot_templates SET more_info_url = 'https://stability.ai' WHERE id = 24;
UPDATE bot_templates SET more_info_url = 'https://www.coingecko.com' WHERE id = 25;
UPDATE bot_templates SET more_info_url = 'https://github.com/Androz2091/discord-giveaways' WHERE id = 26;
UPDATE bot_templates SET more_info_url = 'https://github.com/bot-base/telegram-bot-template' WHERE id = 27;
UPDATE bot_templates SET more_info_url = 'https://n8n.io' WHERE id = 28;
UPDATE bot_templates SET more_info_url = 'https://github.com/stegripe/rawon' WHERE id = 29;
UPDATE bot_templates SET more_info_url = 'https://www.freqtrade.io' WHERE id = 30;
UPDATE bot_templates SET more_info_url = 'https://business.whatsapp.com' WHERE id = 31;
UPDATE bot_templates SET more_info_url = 'https://github.com/zeshuaro/telegram-pdf-bot' WHERE id = 32;
UPDATE bot_templates SET more_info_url = 'https://tracker.gg' WHERE id = 33;
UPDATE bot_templates SET more_info_url = 'https://github.com/bot-base/telegram-bot-template' WHERE id = 34;
UPDATE bot_templates SET more_info_url = 'https://github.com/bot-base/telegram-bot-template' WHERE id = 35;
UPDATE bot_templates SET more_info_url = 'https://github.com/Androz2091/welcome-bot' WHERE id = 36;
UPDATE bot_templates SET more_info_url = 'https://github.com/bot-base/telegram-bot-template' WHERE id = 37;
UPDATE bot_templates SET more_info_url = 'https://github.com/bot-base/telegram-bot-template' WHERE id = 38;
UPDATE bot_templates SET more_info_url = 'https://github.com/Androz2091/discord-giveaways' WHERE id = 39;
UPDATE bot_templates SET more_info_url = 'https://github.com/bot-base/telegram-bot-template' WHERE id = 40;
UPDATE bot_templates SET more_info_url = 'https://github.com/jagrosh/Vortex' WHERE id = 41;
UPDATE bot_templates SET more_info_url = 'https://platform.openai.com' WHERE id = 42;
UPDATE bot_templates SET more_info_url = 'https://github.com/bot-base/telegram-bot-template' WHERE id = 43;
UPDATE bot_templates SET more_info_url = 'https://discord.js.org' WHERE id = 44;
UPDATE bot_templates SET more_info_url = 'https://github.com/nicehash/node-web-scraper' WHERE id = 45;
UPDATE bot_templates SET more_info_url = 'https://platform.openai.com' WHERE id = 46;
UPDATE bot_templates SET more_info_url = 'https://github.com/bot-base/telegram-bot-template' WHERE id = 47;
UPDATE bot_templates SET more_info_url = 'https://discord.js.org' WHERE id = 48;
UPDATE bot_templates SET more_info_url = 'https://platform.openai.com' WHERE id = 49;
UPDATE bot_templates SET more_info_url = 'https://discord.js.org' WHERE id = 50;
