-- Migración 013: Soporte de almacenamiento persistente, código inicial y documentación
--
-- Añade campos a bot_templates para:
--   - Indicar si el bot necesita almacenamiento persistente
--   - Ruta de montaje del volumen
--   - Nombre del archivo de código inicial (para frameworks)
--   - Comando de inicio (para frameworks sin entrypoint propio)
--   - URL a la documentación de primeros pasos del fabricante
--
-- Ejecutar: mysql -u <user> -p <db> < database/migrations/013_storage_and_starter_code.sql

SET NAMES utf8mb4;

-- ============================================================
-- 1. Añadir columnas a bot_templates
-- ============================================================

ALTER TABLE bot_templates
  ADD COLUMN IF NOT EXISTS needs_storage TINYINT(1) NOT NULL DEFAULT 0 AFTER setup_instructions,
  ADD COLUMN IF NOT EXISTS storage_mount_path VARCHAR(255) DEFAULT NULL AFTER needs_storage,
  ADD COLUMN IF NOT EXISTS starter_filename VARCHAR(100) DEFAULT NULL AFTER storage_mount_path,
  ADD COLUMN IF NOT EXISTS start_command TEXT DEFAULT NULL AFTER starter_filename,
  ADD COLUMN IF NOT EXISTS docs_first_steps_url VARCHAR(500) DEFAULT NULL AFTER start_command;

-- ============================================================
-- 2. URLs de documentación "primeros pasos" para las 50 plantillas
-- ============================================================

-- IA Y AGENTES AUTÓNOMOS
UPDATE bot_templates SET docs_first_steps_url = 'https://docs.n8n.io/try-it-out/' WHERE slug = 'n8n';
UPDATE bot_templates SET docs_first_steps_url = 'https://docs.dify.ai/getting-started/install-self-hosted/docker-compose' WHERE slug = 'dify';
UPDATE bot_templates SET docs_first_steps_url = 'https://docs.langflow.org/get-started-installation' WHERE slug = 'langflow';
UPDATE bot_templates SET docs_first_steps_url = 'https://docs.useanything.com/installation/self-hosted/local-docker' WHERE slug = 'anythingllm';
UPDATE bot_templates SET docs_first_steps_url = 'https://docs.openwebui.com/getting-started/' WHERE slug = 'open-webui';
UPDATE bot_templates SET docs_first_steps_url = 'https://github.com/ollama/ollama#quickstart' WHERE slug = 'ollama';
UPDATE bot_templates SET docs_first_steps_url = 'https://github.com/BlockRunAI/OpenClaw#readme' WHERE slug = 'openclaw';
UPDATE bot_templates SET docs_first_steps_url = 'https://docs.crewai.com/quickstart' WHERE slug = 'crewai';
UPDATE bot_templates SET docs_first_steps_url = 'https://microsoft.github.io/autogen/docs/Getting-Started' WHERE slug = 'autogen';
UPDATE bot_templates SET docs_first_steps_url = 'https://doc.agentscope.io/tutorial/quick_start.html' WHERE slug = 'agentscope';

-- COMUNICACIONES Y PASARELAS
UPDATE bot_templates SET docs_first_steps_url = 'https://doc.evolution-api.com/v2/en/get-started/introduction' WHERE slug = 'evolution-api';
UPDATE bot_templates SET docs_first_steps_url = 'https://waha.devlikeapro.com/docs/overview/quick-start/' WHERE slug = 'waha';
UPDATE bot_templates SET docs_first_steps_url = 'https://matrix-nio.readthedocs.io/en/latest/examples.html' WHERE slug = 'matrix-nio';
UPDATE bot_templates SET docs_first_steps_url = 'https://docs.python-telegram-bot.org/en/stable/examples.html' WHERE slug = 'python-telegram-bot';
UPDATE bot_templates SET docs_first_steps_url = 'https://grammy.dev/guide/getting-started' WHERE slug = 'grammy';
UPDATE bot_templates SET docs_first_steps_url = 'https://telegraf.js.org/#md:getting-started' WHERE slug = 'telegraf';
UPDATE bot_templates SET docs_first_steps_url = 'https://github.com/EvolutionAPI/evolution-api-lite#readme' WHERE slug = 'evolution-api-lite';
UPDATE bot_templates SET docs_first_steps_url = 'https://wwebjs.dev/guide/' WHERE slug = 'wwebjs';
UPDATE bot_templates SET docs_first_steps_url = 'https://pkg.go.dev/gopkg.in/telebot.v3#section-readme' WHERE slug = 'telebot-go';
UPDATE bot_templates SET docs_first_steps_url = 'https://docs.mau.fi/bridges/go/whatsapp/index.html' WHERE slug = 'matrix-whatsapp-bridge';

-- FINANZAS Y COMERCIO
UPDATE bot_templates SET docs_first_steps_url = 'https://www.freqtrade.io/en/stable/docker_quickstart/' WHERE slug = 'freqtrade';
UPDATE bot_templates SET docs_first_steps_url = 'https://hummingbot.org/installation/docker/' WHERE slug = 'hummingbot';
UPDATE bot_templates SET docs_first_steps_url = 'https://github.com/AI4Finance-Foundation/FinRL#quick-start' WHERE slug = 'finrl';
UPDATE bot_templates SET docs_first_steps_url = 'https://docs.jesse.trade/docs/getting-started/' WHERE slug = 'jesse';
UPDATE bot_templates SET docs_first_steps_url = 'https://superalgos.org/community-quick-start-guide.shtml' WHERE slug = 'superalgos';
UPDATE bot_templates SET docs_first_steps_url = 'https://www.octobot.cloud/en/guides/octobot-installation/with-docker' WHERE slug = 'octobot';
UPDATE bot_templates SET docs_first_steps_url = 'https://github.com/Decodo/Labubu-bot#readme' WHERE slug = 'labubu-bot';
UPDATE bot_templates SET docs_first_steps_url = 'https://docs.medusajs.com/learn' WHERE slug = 'medusa';
UPDATE bot_templates SET docs_first_steps_url = 'https://devdocs.bagisto.com/2.x/introduction/installation.html' WHERE slug = 'bagisto';
UPDATE bot_templates SET docs_first_steps_url = 'https://docs.saleor.io/docs/3.x/setup/docker-compose' WHERE slug = 'saleor';

-- MODERACIÓN Y SEGURIDAD
UPDATE bot_templates SET docs_first_steps_url = 'https://github.com/skyra-project/skyra#readme' WHERE slug = 'skyra';
UPDATE bot_templates SET docs_first_steps_url = 'https://github.com/jagrosh/Vortex/wiki/Getting-Started' WHERE slug = 'vortex';
UPDATE bot_templates SET docs_first_steps_url = 'https://github.com/aternosorg/modbot#readme' WHERE slug = 'modbot';
UPDATE bot_templates SET docs_first_steps_url = 'https://the-draupnir-project.github.io/draupnir-documentation/' WHERE slug = 'draupnir';
UPDATE bot_templates SET docs_first_steps_url = 'https://github.com/matrix-org/mjolnir#setup' WHERE slug = 'mjolnir';
UPDATE bot_templates SET docs_first_steps_url = 'https://github.com/group-butler/GroupButler#readme' WHERE slug = 'group-butler';
UPDATE bot_templates SET docs_first_steps_url = 'https://docs.discord.red/en/stable/install_guides/index.html' WHERE slug = 'red-discordbot';
UPDATE bot_templates SET docs_first_steps_url = 'https://discordtickets.app/getting-started/' WHERE slug = 'discord-tickets';
UPDATE bot_templates SET docs_first_steps_url = 'https://github.com/twirapp/twir#readme' WHERE slug = 'twirapp';
UPDATE bot_templates SET docs_first_steps_url = 'https://github.com/Luca-Pelzer/engelguard#readme' WHERE slug = 'engelguard';

-- MARKETING Y DESARROLLO
UPDATE bot_templates SET docs_first_steps_url = 'https://docs.mautic.org/en/5.x/getting_started/how_to_install_mautic.html' WHERE slug = 'mautic';
UPDATE bot_templates SET docs_first_steps_url = 'https://github.com/yt-dlp/yt-dlp#usage-and-options' WHERE slug = 'yt-dlp';
UPDATE bot_templates SET docs_first_steps_url = 'https://github.com/Stickerifier/Stickerify#readme' WHERE slug = 'stickerify';
UPDATE bot_templates SET docs_first_steps_url = 'https://github.com/elebumm/RedditVideoMakerBot#readme' WHERE slug = 'reddit-video-maker';
UPDATE bot_templates SET docs_first_steps_url = 'https://www.amputatorbot.com/' WHERE slug = 'amputatorbot';
UPDATE bot_templates SET docs_first_steps_url = 'https://allcontributors.org/docs/en/bot/usage' WHERE slug = 'allcontributors-bot';
UPDATE bot_templates SET docs_first_steps_url = 'https://kodiakhq.com/docs/quickstart' WHERE slug = 'kodiak';
UPDATE bot_templates SET docs_first_steps_url = 'https://docs.codecov.com/docs/quick-start' WHERE slug = 'codecov-bot';
UPDATE bot_templates SET docs_first_steps_url = 'https://github.com/anthonydahanne/newsy-mastodon#readme' WHERE slug = 'newsy-mastodon';
UPDATE bot_templates SET docs_first_steps_url = 'https://github.com/cfultz/MastodonFrameBot#readme' WHERE slug = 'mastodon-frame-bot';

-- ============================================================
-- 3. Bots FRAMEWORK — necesitan código inicial y start_command
--    (sin git_repo_url, usan imagen base como python:3.11-slim, node:20-alpine, etc.)
--    El contenedor se reinicia continuamente sin esto porque no tiene qué ejecutar.
-- ============================================================

UPDATE bot_templates SET
  needs_storage = 1,
  storage_mount_path = '/data',
  starter_filename = 'bot.py',
  start_command = 'pip install python-telegram-bot --quiet && python /app/bot.py',
  setup_instructions = '1. Crea un bot con @BotFather en Telegram y copia el token\n2. Configura el token y despliega\n3. El bot incluye un código de ejemplo funcional (echo bot)\n4. Abre el Gestor de Archivos para editar bot.py con tu lógica\n5. Reinicia el bot para aplicar cambios\n\n📖 Docs: https://docs.python-telegram-bot.org/en/stable/examples.html'
WHERE slug = 'python-telegram-bot';

UPDATE bot_templates SET
  needs_storage = 1,
  storage_mount_path = '/data',
  starter_filename = 'bot.js',
  start_command = 'cd /app && npm install grammy 2>/dev/null && node /app/bot.js',
  setup_instructions = '1. Crea un bot con @BotFather en Telegram y copia el token\n2. Configura el token y despliega\n3. El bot incluye un código de ejemplo funcional (echo bot)\n4. Abre el Gestor de Archivos para editar bot.js con tu lógica\n5. Reinicia el bot para aplicar cambios\n\n📖 Docs: https://grammy.dev/guide/getting-started'
WHERE slug = 'grammy';

UPDATE bot_templates SET
  needs_storage = 1,
  storage_mount_path = '/data',
  starter_filename = 'bot.js',
  start_command = 'cd /app && npm install telegraf 2>/dev/null && node /app/bot.js',
  setup_instructions = '1. Crea un bot con @BotFather en Telegram y copia el token\n2. Configura el token y despliega\n3. El bot incluye un código de ejemplo funcional (echo bot)\n4. Abre el Gestor de Archivos para editar bot.js con tu lógica\n5. Reinicia el bot para aplicar cambios\n\n📖 Docs: https://telegraf.js.org/#md:getting-started'
WHERE slug = 'telegraf';

UPDATE bot_templates SET
  needs_storage = 1,
  storage_mount_path = '/data',
  starter_filename = 'bot.py',
  start_command = 'pip install matrix-nio --quiet && python /app/bot.py',
  setup_instructions = '1. Crea una cuenta de bot en tu homeserver Matrix\n2. Configura homeserver, usuario y contraseña\n3. Despliega — incluye un bot de ejemplo que responde a !hola\n4. Abre el Gestor de Archivos para editar bot.py con tu lógica\n5. Reinicia el bot para aplicar cambios\n\n📖 Docs: https://matrix-nio.readthedocs.io/en/latest/examples.html'
WHERE slug = 'matrix-nio';

UPDATE bot_templates SET
  needs_storage = 1,
  storage_mount_path = '/data',
  starter_filename = 'bot.js',
  start_command = 'cd /app && npm install whatsapp-web.js qrcode-terminal 2>/dev/null && node /app/bot.js',
  setup_instructions = '1. Despliega el bot (no necesita credenciales previas)\n2. Revisa los logs para ver el código QR\n3. Escanea el QR con tu teléfono desde WhatsApp\n4. El bot de ejemplo responde a !hola\n5. Abre el Gestor de Archivos para editar bot.js\n\n📖 Docs: https://wwebjs.dev/guide/'
WHERE slug = 'wwebjs';

UPDATE bot_templates SET
  needs_storage = 1,
  storage_mount_path = '/data',
  starter_filename = 'main.go',
  start_command = 'cd /app && go mod init bot 2>/dev/null; go get gopkg.in/telebot.v3 && go run /app/main.go',
  setup_instructions = '1. Crea un bot con @BotFather en Telegram y copia el token\n2. Configura el token y despliega\n3. El bot incluye un código de ejemplo en Go (echo bot)\n4. Abre el Gestor de Archivos para editar main.go con tu lógica\n5. Reinicia el bot para aplicar cambios\n\n📖 Docs: https://pkg.go.dev/gopkg.in/telebot.v3'
WHERE slug = 'telebot-go';

UPDATE bot_templates SET
  needs_storage = 1,
  storage_mount_path = '/data',
  starter_filename = 'bot.py',
  start_command = 'pip install praw --quiet && python /app/bot.py',
  setup_instructions = '1. Crea una app en Reddit (reddit.com/prefs/apps)\n2. Configura Client ID, Secret y credenciales\n3. Despliega — incluye un bot de ejemplo básico\n4. Abre el Gestor de Archivos para editar bot.py\n5. Reinicia para aplicar cambios\n\n📖 Docs: https://www.amputatorbot.com/'
WHERE slug = 'amputatorbot';

UPDATE bot_templates SET
  needs_storage = 1,
  storage_mount_path = '/data',
  starter_filename = 'bot.js',
  start_command = 'node /app/bot.js',
  setup_instructions = '1. Registra tu repo en codecov.io y obtén el token\n2. Genera un token de GitHub\n3. Configura credenciales y despliega\n4. El bot incluye un código de ejemplo básico\n5. Abre el Gestor de Archivos para editar bot.js\n\n📖 Docs: https://docs.codecov.com/docs/quick-start'
WHERE slug = 'codecov-bot';

-- ============================================================
-- 4. Bots SERVICIO — necesitan volumen persistente para datos
--    (imágenes Docker completas con su propio entrypoint)
-- ============================================================

-- n8n: workflows, credenciales, base de datos SQLite interna
UPDATE bot_templates SET needs_storage = 1, storage_mount_path = '/home/node/.n8n' WHERE slug = 'n8n';

-- AnythingLLM: documentos, embeddings, configuración
UPDATE bot_templates SET needs_storage = 1, storage_mount_path = '/app/server/storage' WHERE slug = 'anythingllm';

-- Open WebUI: configuración, historial de chat
UPDATE bot_templates SET needs_storage = 1, storage_mount_path = '/app/backend/data' WHERE slug = 'open-webui';

-- Ollama: modelos descargados (pueden ser varios GB)
UPDATE bot_templates SET needs_storage = 1, storage_mount_path = '/root/.ollama' WHERE slug = 'ollama';

-- Evolution API: sesiones de WhatsApp
UPDATE bot_templates SET needs_storage = 1, storage_mount_path = '/evolution/instances' WHERE slug = 'evolution-api';

-- WAHA: sesiones de WhatsApp
UPDATE bot_templates SET needs_storage = 1, storage_mount_path = '/tmp/.sessions' WHERE slug = 'waha';

-- Matrix-WhatsApp Bridge: datos del bridge
UPDATE bot_templates SET needs_storage = 1, storage_mount_path = '/data' WHERE slug = 'matrix-whatsapp-bridge';

-- Freqtrade: estrategias, datos de trading, configuración
UPDATE bot_templates SET needs_storage = 1, storage_mount_path = '/freqtrade/user_data' WHERE slug = 'freqtrade';

-- Hummingbot: configuración, estrategias, logs
UPDATE bot_templates SET needs_storage = 1, storage_mount_path = '/home/hummingbot' WHERE slug = 'hummingbot';

-- OctoBot: datos de usuario, estrategias
UPDATE bot_templates SET needs_storage = 1, storage_mount_path = '/octobot/user' WHERE slug = 'octobot';

-- Medusa: uploads de productos
UPDATE bot_templates SET needs_storage = 1, storage_mount_path = '/app/medusa/uploads' WHERE slug = 'medusa';

-- Red-DiscordBot: datos del bot, cogs instalados
UPDATE bot_templates SET needs_storage = 1, storage_mount_path = '/data' WHERE slug = 'red-discordbot';

-- Mautic: datos de marketing, caché
UPDATE bot_templates SET needs_storage = 1, storage_mount_path = '/var/www/html/var' WHERE slug = 'mautic';

-- Langflow: base de datos SQLite local
UPDATE bot_templates SET needs_storage = 1, storage_mount_path = '/app/langflow' WHERE slug = 'langflow';

-- Dify: datos de aplicación
UPDATE bot_templates SET needs_storage = 1, storage_mount_path = '/app/api/storage' WHERE slug = 'dify';

-- Saleor: media/uploads
UPDATE bot_templates SET needs_storage = 1, storage_mount_path = '/app/media' WHERE slug = 'saleor';

-- Reddit Video Maker: vídeos generados
UPDATE bot_templates SET needs_storage = 1, storage_mount_path = '/results' WHERE slug = 'reddit-video-maker';

-- yt-dlp: descargas
UPDATE bot_templates SET needs_storage = 1, storage_mount_path = '/downloads' WHERE slug = 'yt-dlp';

-- MastodonFrameBot: datos de vídeo
UPDATE bot_templates SET needs_storage = 1, storage_mount_path = '/data' WHERE slug = 'mastodon-frame-bot';

-- Skyra: base de datos local
UPDATE bot_templates SET needs_storage = 1, storage_mount_path = '/app/data' WHERE slug = 'skyra';

-- Discord-Tickets: base de datos SQLite
UPDATE bot_templates SET needs_storage = 1, storage_mount_path = '/app/data' WHERE slug = 'discord-tickets';

-- TwirApp: datos de la aplicación
UPDATE bot_templates SET needs_storage = 1, storage_mount_path = '/app/data' WHERE slug = 'twirapp';

-- ============================================================
-- 5. Mejorar setup_instructions de servicios con enlace a docs
-- ============================================================

UPDATE bot_templates SET
  setup_instructions = CONCAT(setup_instructions, '\n\n📖 Primeros pasos: ', docs_first_steps_url)
WHERE docs_first_steps_url IS NOT NULL
  AND starter_filename IS NULL
  AND setup_instructions NOT LIKE '%📖%';
