-- Migración 003: Panel de administración y plantillas de bots
-- Ejecutar: mysql -u <user> -p <db> < database/migrations/003_admin_and_bot_templates.sql

SET NAMES utf8mb4;

-- ============================================================
-- 1. Añadir campo is_admin a users
-- ============================================================
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS is_admin TINYINT(1) NOT NULL DEFAULT 0 AFTER stripe_customer_id;

-- ============================================================
-- 2. Tabla de plantillas de bots (bot_templates)
-- ============================================================
CREATE TABLE IF NOT EXISTS bot_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(80) UNIQUE NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    short_description VARCHAR(255) NOT NULL DEFAULT '',
    platform ENUM('telegram','discord','other','multi') DEFAULT 'telegram',
    category VARCHAR(50) NOT NULL DEFAULT 'utility',
    icon VARCHAR(10) NOT NULL DEFAULT '🤖',
    docker_image VARCHAR(255) NOT NULL DEFAULT 'python:3.11-slim',
    git_repo_url VARCHAR(500) NULL,
    default_env_vars JSON COMMENT 'Env vars por defecto con claves y valores de ejemplo',
    required_env_vars JSON COMMENT 'Array de objetos {key, label, placeholder, required}',
    ram_mb_min INT NOT NULL DEFAULT 128,
    min_plan_slug VARCHAR(50) NOT NULL DEFAULT 'free',
    difficulty ENUM('easy','medium','advanced') DEFAULT 'easy',
    tags VARCHAR(500) DEFAULT '',
    documentation_url VARCHAR(500) NULL,
    setup_instructions TEXT NULL COMMENT 'Instrucciones paso a paso en texto plano',
    is_featured TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    install_count INT UNSIGNED DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_platform (platform),
    INDEX idx_category (category),
    INDEX idx_featured (is_featured),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 3. Relación bots ↔ plantillas (opcional, para rastrear despliegues desde plantilla)
-- ============================================================
ALTER TABLE bots
    ADD COLUMN IF NOT EXISTS template_id INT NULL AFTER docker_image,
    ADD INDEX IF NOT EXISTS idx_template_id (template_id);

-- ============================================================
-- 4. 10 Plantillas de bots semilla
-- ============================================================
INSERT INTO bot_templates (slug, name, description, short_description, platform, category, icon, docker_image, git_repo_url, default_env_vars, required_env_vars, ram_mb_min, min_plan_slug, difficulty, tags, setup_instructions, is_featured, sort_order) VALUES

-- 1. Telegram Echo Bot
('telegram-echo-bot', 'Telegram Echo Bot', 
'Bot de Telegram que repite cada mensaje que recibe. Perfecto como punto de partida para aprender a crear bots de Telegram con Python y la librería python-telegram-bot. Incluye manejo de comandos /start y /help.',
'Bot básico que repite tus mensajes. Ideal para aprender.',
'telegram', 'starter', '📢', 'python:3.11-slim',
'https://github.com/python-telegram-bot/python-telegram-bot',
'{"BOT_TOKEN": ""}',
'[{"key": "BOT_TOKEN", "label": "Token del Bot de Telegram", "placeholder": "123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11", "required": true}]',
128, 'free', 'easy', 'telegram,python,echo,starter,principiante',
'1. Habla con @BotFather en Telegram y crea un bot nuevo con /newbot\n2. Copia el token que te da BotFather\n3. Pega el token en la variable BOT_TOKEN\n4. Haz clic en Desplegar\n5. ¡Listo! Escribe a tu bot y verás cómo repite tus mensajes',
1, 1),

-- 2. Discord Music Bot
('discord-music-bot', 'Discord Music Bot',
'Bot de Discord que reproduce música de YouTube en canales de voz. Usa discord.js y yt-dlp para streaming de audio en tiempo real. Comandos: !play, !skip, !stop, !queue, !pause, !resume. Cola de reproducción con lista de espera.',
'Reproduce música de YouTube en tu servidor de Discord.',
'discord', 'entertainment', '🎵', 'node:20-alpine',
'https://github.com/eritislami/evobot',
'{"DISCORD_TOKEN": "", "PREFIX": "!"}',
'[{"key": "DISCORD_TOKEN", "label": "Token del Bot de Discord", "placeholder": "MTIzNDU2Nzg5MDEyMzQ1Njc4.Gabcde.XXXXXX", "required": true}, {"key": "PREFIX", "label": "Prefijo de comandos", "placeholder": "!", "required": false}]',
256, 'starter', 'easy', 'discord,music,youtube,nodejs,entretenimiento',
'1. Ve a https://discord.com/developers/applications y crea una aplicación\n2. En la sección Bot, copia el Token\n3. Activa los intents: Message Content, Server Members\n4. Invita el bot a tu servidor con permisos de voz\n5. Pega el token en DISCORD_TOKEN\n6. Despliega y usa !play <url> en un canal de voz',
1, 2),

-- 3. Telegram GPT Bot
('telegram-gpt-bot', 'Telegram AI Chat (GPT)',
'Bot de Telegram potenciado con la API de OpenAI (GPT-4o / GPT-3.5). Responde preguntas, genera texto, traduce, resume y mucho más. Mantiene contexto de conversación por usuario. Configurable con system prompt personalizado.',
'Asistente IA en Telegram con ChatGPT integrado.',
'telegram', 'ai', '🧠', 'python:3.11-slim',
'https://github.com/karfly/chatgpt_telegram_bot',
'{"BOT_TOKEN": "", "OPENAI_API_KEY": "", "OPENAI_MODEL": "gpt-4o-mini", "SYSTEM_PROMPT": "Eres un asistente útil y amigable."}',
'[{"key": "BOT_TOKEN", "label": "Token del Bot de Telegram", "placeholder": "123456:ABC-DEF...", "required": true}, {"key": "OPENAI_API_KEY", "label": "API Key de OpenAI", "placeholder": "sk-...", "required": true}, {"key": "OPENAI_MODEL", "label": "Modelo de OpenAI", "placeholder": "gpt-4o-mini", "required": false}]',
256, 'starter', 'easy', 'telegram,gpt,openai,chatgpt,ia,inteligencia-artificial',
'1. Crea un bot en Telegram con @BotFather y copia el token\n2. Obtén una API Key en https://platform.openai.com/api-keys\n3. Rellena BOT_TOKEN y OPENAI_API_KEY\n4. Opcionalmente personaliza el SYSTEM_PROMPT\n5. Despliega y empieza a chatear con tu bot IA',
1, 3),

-- 4. Discord Moderation Bot
('discord-mod-bot', 'Discord Moderación Bot',
'Bot de moderación automática para servidores de Discord. Detecta spam, palabras prohibidas, raid de cuentas nuevas y flood de mensajes. Comandos: !ban, !kick, !mute, !warn, !purge. Sistema de warns con escalado automático. Logs de moderación en canal dedicado.',
'Modera tu servidor de Discord automáticamente.',
'discord', 'moderation', '🛡️', 'node:20-alpine',
'https://github.com/jagrosh/Vortex',
'{"DISCORD_TOKEN": "", "PREFIX": "!", "MOD_LOG_CHANNEL": "", "WARN_THRESHOLD": "3"}',
'[{"key": "DISCORD_TOKEN", "label": "Token del Bot de Discord", "placeholder": "MTIzNDU2...", "required": true}, {"key": "MOD_LOG_CHANNEL", "label": "ID del canal de logs", "placeholder": "123456789012345678", "required": false}]',
256, 'starter', 'medium', 'discord,moderacion,antispam,seguridad,nodejs',
'1. Crea una aplicación en Discord Developer Portal\n2. Crea el bot y copia el token\n3. Activa intents: Message Content, Server Members, Moderation\n4. Invita con permisos de administrador\n5. Configura DISCORD_TOKEN y opcionalmente MOD_LOG_CHANNEL\n6. Despliega el bot',
1, 4),

-- 5. Telegram Shop Bot
('telegram-shop-bot', 'Telegram Tienda Bot',
'Bot de Telegram para crear una tienda online con catálogo de productos, carrito de compra y pagos integrados. Los clientes navegan productos con botones inline, añaden al carrito y finalizan compra. Panel de admin vía comandos para gestionar productos y pedidos.',
'Tienda online completa dentro de Telegram.',
'telegram', 'ecommerce', '🛒', 'python:3.11-slim',
'https://github.com/cleandersonlobo/python-telegram-shop-bot',
'{"BOT_TOKEN": "", "ADMIN_USER_ID": "", "CURRENCY": "EUR", "SHOP_NAME": "Mi Tienda"}',
'[{"key": "BOT_TOKEN", "label": "Token del Bot de Telegram", "placeholder": "123456:ABC-DEF...", "required": true}, {"key": "ADMIN_USER_ID", "label": "Tu ID de usuario de Telegram", "placeholder": "123456789", "required": true}]',
256, 'starter', 'medium', 'telegram,tienda,shop,ecommerce,pagos,python',
'1. Crea un bot con @BotFather\n2. Obtén tu ID de usuario (usa @userinfobot en Telegram)\n3. Configura BOT_TOKEN y ADMIN_USER_ID\n4. Despliega el bot\n5. Envía /admin a tu bot para gestionar productos\n6. Comparte el enlace del bot para que tus clientes compren',
0, 5),

-- 6. Discord Welcome Bot
('discord-welcome-bot', 'Discord Bienvenida Bot',
'Bot que da la bienvenida a nuevos miembros con mensajes personalizados, imágenes generadas y asignación automática de roles. Configura mensajes de bienvenida y despedida, canal de reglas, y roles por reacción. Perfecto para comunidades.',
'Bienvenidas automáticas y roles por reacción.',
'discord', 'utility', '👋', 'node:20-alpine',
'https://github.com/Androz2091/welcome-bot',
'{"DISCORD_TOKEN": "", "WELCOME_CHANNEL": "", "WELCOME_MESSAGE": "¡Bienvenido/a {user} a {server}! 🎉", "AUTO_ROLE_ID": ""}',
'[{"key": "DISCORD_TOKEN", "label": "Token del Bot de Discord", "placeholder": "MTIzNDU2...", "required": true}, {"key": "WELCOME_CHANNEL", "label": "ID del canal de bienvenida", "placeholder": "123456789012345678", "required": true}]',
128, 'free', 'easy', 'discord,welcome,bienvenida,roles,comunidad,nodejs',
'1. Crea una aplicación y bot en Discord Developer Portal\n2. Activa intents: Server Members\n3. Crea un canal #bienvenidas en tu servidor\n4. Copia el ID del canal (clic derecho → Copiar ID)\n5. Configura DISCORD_TOKEN y WELCOME_CHANNEL\n6. Despliega y los nuevos miembros recibirán bienvenida',
1, 6),

-- 7. AI Image Generator Bot
('ai-image-bot', 'Bot Generador de Imágenes IA',
'Bot de Telegram que genera imágenes a partir de descripciones de texto usando DALL-E 3 o Stable Diffusion. Envía una descripción y recibe una imagen generada por IA en segundos. Soporta diferentes estilos, tamaños y variaciones.',
'Genera imágenes con IA desde Telegram.',
'telegram', 'ai', '🎨', 'python:3.11-slim',
'https://github.com/nickhilton/dalle-telegram-bot',
'{"BOT_TOKEN": "", "OPENAI_API_KEY": "", "IMAGE_MODEL": "dall-e-3", "IMAGE_SIZE": "1024x1024"}',
'[{"key": "BOT_TOKEN", "label": "Token del Bot de Telegram", "placeholder": "123456:ABC-DEF...", "required": true}, {"key": "OPENAI_API_KEY", "label": "API Key de OpenAI", "placeholder": "sk-...", "required": true}]',
256, 'starter', 'easy', 'telegram,dall-e,imagen,ia,arte,generativo,python',
'1. Crea un bot en Telegram con @BotFather\n2. Obtén una API Key de OpenAI con acceso a DALL-E\n3. Configura BOT_TOKEN y OPENAI_API_KEY\n4. Despliega el bot\n5. Envía cualquier descripción al bot y recibirás una imagen generada',
1, 7),

-- 8. Telegram RSS Feed Bot
('telegram-rss-bot', 'Telegram RSS Feed Bot',
'Bot de Telegram que monitorea feeds RSS/Atom y envía notificaciones automáticas cuando hay contenido nuevo. Ideal para seguir blogs, noticias, canales de YouTube, podcasts o cualquier fuente con RSS. Soporta múltiples feeds y filtros por palabras clave.',
'Monitorea feeds RSS y notifica en Telegram.',
'telegram', 'utility', '📡', 'python:3.11-slim',
'https://github.com/Rongronggg9/RSS-to-Telegram-Bot',
'{"BOT_TOKEN": "", "CHAT_ID": "", "RSS_URLS": "https://feeds.example.com/rss", "CHECK_INTERVAL": "300"}',
'[{"key": "BOT_TOKEN", "label": "Token del Bot de Telegram", "placeholder": "123456:ABC-DEF...", "required": true}, {"key": "CHAT_ID", "label": "ID del chat/canal donde enviar", "placeholder": "-1001234567890", "required": true}, {"key": "RSS_URLS", "label": "URLs de feeds RSS (separados por coma)", "placeholder": "https://blog.example.com/feed", "required": true}]',
128, 'free', 'medium', 'telegram,rss,feed,noticias,notificaciones,python',
'1. Crea un bot con @BotFather\n2. Añade el bot a un grupo o canal como admin\n3. Obtén el CHAT_ID del grupo (usa @raw_data_bot)\n4. Introduce las URLs de los feeds RSS que quieres seguir\n5. Ajusta CHECK_INTERVAL (en segundos, por defecto 300 = 5 min)\n6. Despliega y recibirás noticias automáticamente',
0, 8),

-- 9. Discord Ticket Bot
('discord-ticket-bot', 'Discord Tickets / Soporte Bot',
'Sistema de tickets de soporte para Discord. Los usuarios abren tickets con un botón o comando, se crea un canal privado para la conversación. Incluye: transcripciones, categorías de tickets, panel de estadísticas, cierre automático por inactividad y notificaciones al staff.',
'Sistema de soporte con tickets para Discord.',
'discord', 'utility', '🎫', 'node:20-alpine',
'https://github.com/discord-tickets/bot',
'{"DISCORD_TOKEN": "", "PREFIX": "!", "SUPPORT_ROLE_ID": "", "TICKET_CATEGORY_ID": ""}',
'[{"key": "DISCORD_TOKEN", "label": "Token del Bot de Discord", "placeholder": "MTIzNDU2...", "required": true}, {"key": "SUPPORT_ROLE_ID", "label": "ID del rol de soporte", "placeholder": "123456789012345678", "required": true}]',
256, 'starter', 'medium', 'discord,tickets,soporte,support,helpdesk,nodejs',
'1. Crea aplicación y bot en Discord Developer Portal\n2. Activa intents necesarios\n3. Crea un rol @Soporte y una categoría para tickets\n4. Copia los IDs del rol y la categoría\n5. Configura DISCORD_TOKEN, SUPPORT_ROLE_ID y TICKET_CATEGORY_ID\n6. Despliega y usa !ticket para abrir un ticket',
1, 9),

-- 10. Multi-platform AI Assistant
('multi-ai-assistant', 'Asistente IA Multi-plataforma',
'Asistente de inteligencia artificial que funciona en Telegram y Discord simultáneamente. Potenciado por OpenAI GPT-4o con capacidades de: chat conversacional, generación de código, análisis de imágenes, resumen de documentos, búsqueda web y traducción. Memoria de conversación persistente.',
'Asistente IA avanzado para Telegram y Discord.',
'multi', 'ai', '🌐', 'python:3.11-slim',
'https://github.com/openai/openai-python',
'{"TELEGRAM_TOKEN": "", "DISCORD_TOKEN": "", "OPENAI_API_KEY": "", "OPENAI_MODEL": "gpt-4o", "MAX_HISTORY": "20"}',
'[{"key": "OPENAI_API_KEY", "label": "API Key de OpenAI", "placeholder": "sk-...", "required": true}, {"key": "TELEGRAM_TOKEN", "label": "Token de Telegram (opcional)", "placeholder": "123456:ABC-DEF...", "required": false}, {"key": "DISCORD_TOKEN", "label": "Token de Discord (opcional)", "placeholder": "MTIzNDU2...", "required": false}]',
512, 'medium', 'advanced', 'telegram,discord,multi,gpt,openai,ia,asistente,avanzado',
'1. Obtén una API Key de OpenAI\n2. Crea bots en Telegram y/o Discord (al menos uno)\n3. Rellena las claves correspondientes\n4. Elige el modelo (gpt-4o para máxima calidad, gpt-4o-mini para ahorrar)\n5. Despliega y tendrás un asistente IA en ambas plataformas',
1, 10)

ON DUPLICATE KEY UPDATE
    name=VALUES(name), description=VALUES(description), short_description=VALUES(short_description),
    platform=VALUES(platform), category=VALUES(category), icon=VALUES(icon),
    docker_image=VALUES(docker_image), git_repo_url=VALUES(git_repo_url),
    default_env_vars=VALUES(default_env_vars), required_env_vars=VALUES(required_env_vars),
    ram_mb_min=VALUES(ram_mb_min), min_plan_slug=VALUES(min_plan_slug),
    difficulty=VALUES(difficulty), tags=VALUES(tags),
    setup_instructions=VALUES(setup_instructions), is_featured=VALUES(is_featured),
    sort_order=VALUES(sort_order);
