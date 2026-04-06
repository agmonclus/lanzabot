-- Migración 004: Bots auto-instalables y auto-actualizables
-- Ejecutar: mysql -u <user> -p <db> < database/migrations/004_auto_install_bots.sql

SET NAMES utf8mb4;

-- ============================================================
-- 1. Ampliar plataformas soportadas en bots y bot_templates
-- ============================================================
ALTER TABLE bots
    MODIFY COLUMN platform ENUM('telegram','discord','slack','whatsapp','twitch','matrix','reddit','mastodon','multi','other') DEFAULT 'telegram';

ALTER TABLE bot_templates
    MODIFY COLUMN platform ENUM('telegram','discord','slack','whatsapp','twitch','matrix','reddit','mastodon','multi','other') DEFAULT 'telegram';

-- ============================================================
-- 2. Campos de auto-actualización en bot_templates
-- ============================================================
ALTER TABLE bot_templates
    ADD COLUMN IF NOT EXISTS auto_update_supported TINYINT(1) NOT NULL DEFAULT 1 AFTER install_count,
    ADD COLUMN IF NOT EXISTS version VARCHAR(20) NOT NULL DEFAULT '1.0.0' AFTER auto_update_supported,
    ADD COLUMN IF NOT EXISTS changelog TEXT NULL AFTER version;

-- ============================================================
-- 3. Campos de auto-actualización en bots desplegados
-- ============================================================
ALTER TABLE bots
    ADD COLUMN IF NOT EXISTS auto_update TINYINT(1) NOT NULL DEFAULT 1 AFTER template_id,
    ADD COLUMN IF NOT EXISTS current_version VARCHAR(20) NOT NULL DEFAULT '1.0.0' AFTER auto_update,
    ADD COLUMN IF NOT EXISTS last_updated_at TIMESTAMP NULL AFTER current_version;

-- ============================================================
-- 4. Eliminar requirement de subir código (ya no se usa)
--    Marcar code_uploaded como deprecated pero no eliminar por retrocompat
-- ============================================================

-- ============================================================
-- 5. Nuevas plantillas de bots (12 adicionales, multi-plataforma)
-- ============================================================
INSERT INTO bot_templates (slug, name, description, short_description, platform, category, icon, docker_image, git_repo_url, default_env_vars, required_env_vars, ram_mb_min, min_plan_slug, difficulty, tags, setup_instructions, is_featured, sort_order, auto_update_supported, version) VALUES

-- 11. Slack Notification Bot
('slack-notify-bot', 'Slack Notificaciones Bot',
'Bot de Slack que envía notificaciones automáticas a canales. Integra webhooks para recibir alertas de GitHub, GitLab, Jira, Sentry y más servicios. Configura reglas de filtrado para enviar solo las notificaciones relevantes al canal adecuado. Dashboard de estadísticas incluido.',
'Centraliza alertas y notificaciones en Slack.',
'slack', 'utility', '💬', 'node:20-alpine',
'https://github.com/slackapi/bolt-js',
'{"SLACK_BOT_TOKEN": "", "SLACK_SIGNING_SECRET": "", "SLACK_APP_TOKEN": "", "DEFAULT_CHANNEL": "#general"}',
'[{"key": "SLACK_BOT_TOKEN", "label": "Token del Bot de Slack (xoxb-...)", "placeholder": "xoxb-1234567890-abcdefghij", "required": true}, {"key": "SLACK_SIGNING_SECRET", "label": "Signing Secret de la App", "placeholder": "abc123def456...", "required": true}, {"key": "SLACK_APP_TOKEN", "label": "App-Level Token (xapp-...)", "placeholder": "xapp-1-...", "required": true}]',
128, 'free', 'easy', 'slack,notificaciones,alertas,webhooks,nodejs',
'1. Ve a https://api.slack.com/apps y crea una nueva App\n2. En OAuth & Permissions, añade los scopes: chat:write, channels:read\n3. Instala la app en tu workspace\n4. Copia el Bot Token (xoxb-...) y el Signing Secret\n5. En Socket Mode, genera un App-Level Token\n6. Configura los tokens y despliega',
1, 11, 1, '1.0.0'),

-- 12. WhatsApp Business Bot
('whatsapp-business-bot', 'WhatsApp Business Bot',
'Bot para WhatsApp Business API que automatiza respuestas, catálogo de productos y atención al cliente. Respuestas automáticas por palabras clave, menús interactivos con botones, envío de imágenes y documentos. Ideal para negocios que necesitan atención 24/7.',
'Automatiza la atención al cliente en WhatsApp.',
'whatsapp', 'ecommerce', '📱', 'node:20-alpine',
'https://github.com/nicehash/whatsapp-api-client',
'{"WHATSAPP_TOKEN": "", "WHATSAPP_PHONE_ID": "", "VERIFY_TOKEN": "lanzabot_verify", "WELCOME_MESSAGE": "¡Hola! ¿En qué puedo ayudarte?"}',
'[{"key": "WHATSAPP_TOKEN", "label": "Token de acceso de WhatsApp Business API", "placeholder": "EAABs...", "required": true}, {"key": "WHATSAPP_PHONE_ID", "label": "ID del número de teléfono", "placeholder": "1234567890", "required": true}, {"key": "VERIFY_TOKEN", "label": "Token de verificación del webhook", "placeholder": "mi_token_secreto", "required": true}]',
256, 'starter', 'medium', 'whatsapp,business,ecommerce,atencion-cliente,nodejs',
'1. Crea una cuenta en Meta for Developers (developers.facebook.com)\n2. Crea una App de tipo Business\n3. Configura la API de WhatsApp Business\n4. Obtén el token de acceso y el Phone Number ID\n5. Define un Verify Token para el webhook\n6. Despliega y configura la URL del webhook en Meta',
1, 12, 1, '1.0.0'),

-- 13. Twitch Chat Bot
('twitch-chat-bot', 'Twitch Chat Bot',
'Bot para canales de Twitch con comandos personalizados, sistema de puntos, encuestas, sorteos, moderación de chat y alertas de eventos (follows, subs, raids). Integración con StreamElements y OBS. Muy personalizable con comandos que definís vos mismo.',
'Modera y anima el chat de tu canal de Twitch.',
'twitch', 'entertainment', '🎮', 'node:20-alpine',
'https://github.com/tmijs/tmi.js',
'{"TWITCH_BOT_USERNAME": "", "TWITCH_OAUTH_TOKEN": "", "TWITCH_CHANNEL": "", "PREFIX": "!", "POINTS_NAME": "puntos"}',
'[{"key": "TWITCH_BOT_USERNAME", "label": "Nombre de usuario del bot en Twitch", "placeholder": "mi_bot", "required": true}, {"key": "TWITCH_OAUTH_TOKEN", "label": "Token OAuth de Twitch", "placeholder": "oauth:abc123def456...", "required": true}, {"key": "TWITCH_CHANNEL", "label": "Canal de Twitch donde operar", "placeholder": "mi_canal", "required": true}]',
128, 'free', 'easy', 'twitch,chat,stream,moderacion,entretenimiento,nodejs',
'1. Crea una cuenta de Twitch para tu bot (o usa la tuya)\n2. Ve a https://twitchapps.com/tmi/ para generar un token OAuth\n3. Introduce el nombre del bot, token y nombre de tu canal\n4. Despliega y el bot aparecerá en el chat de tu canal\n5. Usa !help para ver los comandos disponibles',
1, 13, 1, '1.0.0'),

-- 14. Reddit Moderation Bot
('reddit-mod-bot', 'Reddit Moderación Bot',
'Bot de Reddit para moderar comunidades automáticamente. Filtra posts y comentarios por reglas, detecta spam, gestiona flairs, responde con mensajes automáticos, banea usuarios recurrentes y genera reportes semanales de actividad. Usa la API oficial de Reddit.',
'Modera tu subreddit de forma automática.',
'reddit', 'moderation', '🔶', 'python:3.11-slim',
'https://github.com/praw-dev/praw',
'{"REDDIT_CLIENT_ID": "", "REDDIT_CLIENT_SECRET": "", "REDDIT_USERNAME": "", "REDDIT_PASSWORD": "", "SUBREDDIT": "", "SPAM_KEYWORDS": "casino,viagra,crypto-scam"}',
'[{"key": "REDDIT_CLIENT_ID", "label": "Client ID de la App de Reddit", "placeholder": "AbC123dEf456", "required": true}, {"key": "REDDIT_CLIENT_SECRET", "label": "Client Secret", "placeholder": "xYz789...", "required": true}, {"key": "REDDIT_USERNAME", "label": "Nombre de usuario del bot", "placeholder": "mi_bot_reddit", "required": true}, {"key": "REDDIT_PASSWORD", "label": "Contraseña del bot", "placeholder": "••••••••", "required": true}, {"key": "SUBREDDIT", "label": "Nombre del subreddit (sin r/)", "placeholder": "mi_comunidad", "required": true}]',
128, 'starter', 'medium', 'reddit,moderacion,subreddit,spam,python',
'1. Ve a https://www.reddit.com/prefs/apps y crea una app tipo \"script\"\n2. Copia el Client ID (bajo el nombre de la app) y el Client Secret\n3. Usa las credenciales de la cuenta que será moderadora\n4. Asegúrate de que la cuenta tenga permisos de mod en el subreddit\n5. Configura las variables y despliega',
0, 14, 1, '1.0.0'),

-- 15. Mastodon Auto-poster
('mastodon-poster-bot', 'Mastodon Auto-poster',
'Bot que publica automáticamente en Mastodon desde feeds RSS, posts programados o integraciones con otras plataformas. Soporta texto, imágenes, encuestas y hashtags automáticos. Ideal para mantener tu presencia en el fediverso activa sin esfuerzo.',
'Publica automáticamente en Mastodon y el Fediverso.',
'mastodon', 'social', '🐘', 'python:3.11-slim',
'https://github.com/halcy/Mastodon.py',
'{"MASTODON_INSTANCE": "https://mastodon.social", "MASTODON_ACCESS_TOKEN": "", "RSS_FEED_URL": "", "POST_INTERVAL": "3600", "HASHTAGS": "#bot #automatizado"}',
'[{"key": "MASTODON_INSTANCE", "label": "URL de tu instancia de Mastodon", "placeholder": "https://mastodon.social", "required": true}, {"key": "MASTODON_ACCESS_TOKEN", "label": "Token de acceso de la aplicación", "placeholder": "abc123...", "required": true}]',
128, 'free', 'easy', 'mastodon,fediverse,social,rss,automatizacion,python',
'1. Ve a tu instancia de Mastodon → Preferencias → Desarrollo\n2. Crea una nueva aplicación con permisos de escritura\n3. Copia el token de acceso\n4. Opcionalmente configura un feed RSS para auto-publicar\n5. Despliega y tu cuenta publicará automáticamente',
1, 15, 1, '1.0.0'),

-- 16. Matrix/Element Bot
('matrix-element-bot', 'Matrix/Element Bot',
'Bot para la red Matrix (compatible con Element, FluffyChat, etc.). Responde comandos, modera salas, traduce mensajes, integra servicios externos y puede hacer de puente con Telegram/Discord. Perfecto para comunidades que usan Matrix como mensajería descentralizada.',
'Bot para salas de Matrix/Element con moderación e integraciones.',
'matrix', 'utility', '🟢', 'node:20-alpine',
'https://github.com/turt2live/matrix-bot-sdk',
'{"MATRIX_HOMESERVER": "https://matrix.org", "MATRIX_ACCESS_TOKEN": "", "MATRIX_BOT_USER": "", "COMMAND_PREFIX": "!"}',
'[{"key": "MATRIX_HOMESERVER", "label": "URL del homeserver Matrix", "placeholder": "https://matrix.org", "required": true}, {"key": "MATRIX_ACCESS_TOKEN", "label": "Token de acceso del bot", "placeholder": "syt_...", "required": true}, {"key": "MATRIX_BOT_USER", "label": "User ID del bot (@user:server)", "placeholder": "@mibot:matrix.org", "required": true}]',
128, 'free', 'medium', 'matrix,element,descentralizado,moderacion,nodejs',
'1. Crea una cuenta para tu bot en tu homeserver de Matrix\n2. Inicia sesión y ve a Ajustes → Ayuda → Acceso avanzado\n3. Genera un token de acceso (o usa Element y la API)\n4. Invita al bot a las salas donde quieres que opere\n5. Configura las variables y despliega',
0, 16, 1, '1.0.0'),

-- 17. Uptime Monitor Bot
('uptime-monitor-bot', 'Monitor de Uptime',
'Bot que monitorea la disponibilidad de tus sitios web y servicios. Hace ping periódico a URLs configuradas y envía alertas por Telegram, Discord, Slack o email cuando detecta caídas. Dashboard con historial de uptime, tiempos de respuesta y estadísticas. Reportes diarios/semanales opcionales.',
'Monitorea tus webs y recibe alertas de caídas.',
'multi', 'monitoring', '📊', 'node:20-alpine',
'https://github.com/louislam/uptime-kuma',
'{"URLS_TO_MONITOR": "", "CHECK_INTERVAL": "60", "ALERT_TELEGRAM_TOKEN": "", "ALERT_TELEGRAM_CHAT_ID": "", "ALERT_DISCORD_WEBHOOK": ""}',
'[{"key": "URLS_TO_MONITOR", "label": "URLs a monitorear (una por línea o separadas por coma)", "placeholder": "https://miapp.com, https://api.miapp.com", "required": true}, {"key": "CHECK_INTERVAL", "label": "Intervalo de comprobación (segundos)", "placeholder": "60", "required": false}, {"key": "ALERT_TELEGRAM_TOKEN", "label": "Token de Telegram para alertas (opcional)", "placeholder": "123456:ABC...", "required": false}, {"key": "ALERT_DISCORD_WEBHOOK", "label": "Webhook de Discord para alertas (opcional)", "placeholder": "https://discord.com/api/webhooks/...", "required": false}]',
128, 'free', 'easy', 'monitor,uptime,alertas,web,ping,multi',
'1. Lista las URLs que quieres monitorear\n2. Configura al menos un canal de alertas (Telegram o Discord)\n3. Ajusta el intervalo de comprobación (por defecto 60 seg)\n4. Despliega y recibirás alertas cuando una URL no responda\n5. Consulta /status para ver el dashboard',
1, 17, 1, '1.0.0'),

-- 18. Web Scraper + Notifier
('web-scraper-bot', 'Web Scraper + Notificador',
'Bot que rastrea cambios en páginas web y te notifica. Ideal para seguir precios de productos, detectar disponibilidad de stock, monitorear ofertas de empleo o cambios en cualquier página. Configura selectores CSS y recibe notificaciones en Telegram o Discord cuando algo cambie.',
'Rastrea cambios en webs y recibe notificaciones.',
'multi', 'utility', '🔍', 'node:20-alpine',
'https://github.com/nicehash/node-web-scraper',
'{"SCRAPE_URLS": "", "CSS_SELECTORS": "", "CHECK_INTERVAL": "1800", "NOTIFY_TELEGRAM_TOKEN": "", "NOTIFY_TELEGRAM_CHAT_ID": "", "NOTIFY_DISCORD_WEBHOOK": ""}',
'[{"key": "SCRAPE_URLS", "label": "URLs a rastrear (separadas por coma)", "placeholder": "https://tienda.com/producto", "required": true}, {"key": "CSS_SELECTORS", "label": "Selectores CSS a vigilar (separados por coma)", "placeholder": ".price, .stock-status", "required": true}, {"key": "CHECK_INTERVAL", "label": "Intervalo de comprobación (segundos)", "placeholder": "1800", "required": false}]',
256, 'starter', 'medium', 'scraper,web,monitor,precios,stock,notificaciones',
'1. Identifica las URLs que quieres rastrear\n2. Usa el inspector del navegador (F12) para encontrar los selectores CSS\n3. Configura al menos un canal de notificación\n4. Ajusta el intervalo (por defecto 30 min)\n5. Despliega y recibirás alertas cuando cambie el contenido',
1, 18, 1, '1.0.0'),

-- 19. Discord Leveling Bot
('discord-leveling-bot', 'Discord Sistema de Niveles',
'Bot de Discord con sistema completo de niveles y experiencia. Los miembros ganan XP al chatear, suben de nivel, desbloquean roles automáticamente y compiten en un leaderboard. Tarjetas de perfil personalizables con imagen. Comandos: !rank, !leaderboard, !rewards.',
'Sistema de niveles, XP y leaderboard para Discord.',
'discord', 'entertainment', '🏆', 'node:20-alpine',
'https://github.com/Androz2091/discord-xp',
'{"DISCORD_TOKEN": "", "PREFIX": "!", "XP_PER_MESSAGE": "15", "XP_COOLDOWN": "60", "LEADERBOARD_SIZE": "10"}',
'[{"key": "DISCORD_TOKEN", "label": "Token del Bot de Discord", "placeholder": "MTIzNDU2...", "required": true}]',
256, 'starter', 'easy', 'discord,niveles,xp,leaderboard,gamificacion,nodejs',
'1. Crea una aplicación y bot en Discord Developer Portal\n2. Activa intents: Message Content, Server Members\n3. Copia el token del bot\n4. Invita al bot a tu servidor con permisos de gestión de roles\n5. Despliega y los miembros empezarán a ganar XP\n6. Usa !rank para ver tu nivel y !leaderboard para el ranking',
1, 19, 1, '1.0.0'),

-- 20. Telegram Reminder Bot
('telegram-reminder-bot', 'Telegram Recordatorios Bot',
'Bot de Telegram que programa recordatorios con lenguaje natural. Escribe \"recuérdame comprar leche mañana a las 10\" y el bot te avisará. Soporta: recordatorios únicos, recurrentes (diarios/semanales), listas de tareas, y zona horaria configurable. Perfecto como asistente personal.',
'Programa recordatorios y tareas en Telegram.',
'telegram', 'utility', '⏰', 'python:3.11-slim',
'https://github.com/bot-base/telegram-bot-template',
'{"BOT_TOKEN": "", "TIMEZONE": "Europe/Madrid", "LANGUAGE": "es"}',
'[{"key": "BOT_TOKEN", "label": "Token del Bot de Telegram", "placeholder": "123456:ABC-DEF...", "required": true}, {"key": "TIMEZONE", "label": "Tu zona horaria", "placeholder": "Europe/Madrid", "required": false}]',
128, 'free', 'easy', 'telegram,recordatorios,reminders,tareas,productividad,python',
'1. Crea un bot con @BotFather en Telegram\n2. Copia el token que te da\n3. Configura tu zona horaria (por defecto Europe/Madrid)\n4. Despliega el bot\n5. Escríbele para programar recordatorios con lenguaje natural',
1, 20, 1, '1.0.0'),

-- 21. Webhook Relay / API Gateway Bot
('webhook-relay-bot', 'Webhook Relay / API Gateway',
'Recibe webhooks de cualquier servicio (GitHub, Stripe, Shopify, etc.) y los reenvía formateados a Telegram, Discord, Slack o email. Transforma los datos JSON en mensajes legibles. Incluye cola de reintentos, logs de eventos y panel de estadísticas. Perfecto para integrar servicios sin código.',
'Recibe webhooks y reenvía notificaciones formateadas.',
'multi', 'developer', '🔗', 'node:20-alpine',
'https://github.com/nicehash/webhook-relay',
'{"WEBHOOK_SECRET": "", "NOTIFY_TELEGRAM_TOKEN": "", "NOTIFY_TELEGRAM_CHAT_ID": "", "NOTIFY_DISCORD_WEBHOOK": "", "NOTIFY_SLACK_WEBHOOK": ""}',
'[{"key": "WEBHOOK_SECRET", "label": "Secreto para validar webhooks entrantes", "placeholder": "mi_secreto_123", "required": true}]',
128, 'free', 'medium', 'webhook,relay,api,integracion,github,stripe,devops',
'1. Configura un secreto para validar los webhooks entrantes\n2. Configura al menos un canal de notificación (Telegram, Discord o Slack)\n3. Despliega el bot\n4. Usa la URL del bot como destino de webhooks en tus servicios\n5. Los eventos se recibirán formateados en tu canal elegido',
1, 21, 1, '1.0.0'),

-- 22. Cron Jobs / Task Scheduler
('cron-scheduler-bot', 'Programador de Tareas (Cron)',
'Ejecuta tareas programadas como un cron job en la nube. Llama a URLs (webhooks, APIs), ejecuta scripts, envía mensajes periódicos, limpia datos y genera reportes automatizados. Interfaz por Telegram para gestionar tareas. Ideal para automatización sin servidor propio.',
'Programa tareas periódicas tipo cron en la nube.',
'multi', 'developer', '⚙️', 'node:20-alpine',
'https://github.com/node-cron/node-cron',
'{"ADMIN_TELEGRAM_TOKEN": "", "ADMIN_CHAT_ID": "", "TIMEZONE": "Europe/Madrid"}',
'[{"key": "ADMIN_TELEGRAM_TOKEN", "label": "Token de Telegram para administrar (opcional)", "placeholder": "123456:ABC...", "required": false}, {"key": "TIMEZONE", "label": "Zona horaria", "placeholder": "Europe/Madrid", "required": false}]',
128, 'free', 'medium', 'cron,tareas,scheduler,automatizacion,devops',
'1. Despliega el bot (no requiere configuración mínima)\n2. Opcionalmente configura Telegram para gestionar tareas por chat\n3. Define tus tareas en el panel o vía API\n4. El bot ejecutará las tareas según la programación definida\n5. Consulta los logs para verificar ejecuciones',
0, 22, 1, '1.0.0')

ON DUPLICATE KEY UPDATE
    name=VALUES(name), description=VALUES(description), short_description=VALUES(short_description),
    platform=VALUES(platform), category=VALUES(category), icon=VALUES(icon),
    docker_image=VALUES(docker_image), git_repo_url=VALUES(git_repo_url),
    default_env_vars=VALUES(default_env_vars), required_env_vars=VALUES(required_env_vars),
    ram_mb_min=VALUES(ram_mb_min), min_plan_slug=VALUES(min_plan_slug),
    difficulty=VALUES(difficulty), tags=VALUES(tags),
    setup_instructions=VALUES(setup_instructions), is_featured=VALUES(is_featured),
    sort_order=VALUES(sort_order), auto_update_supported=VALUES(auto_update_supported),
    version=VALUES(version);

-- ============================================================
-- 6. Actualizar versión de plantillas existentes
-- ============================================================
UPDATE bot_templates SET auto_update_supported = 1, version = '1.0.0'
WHERE auto_update_supported IS NULL OR version IS NULL;
