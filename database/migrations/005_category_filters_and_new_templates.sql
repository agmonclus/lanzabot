-- Migración 005: Filtro por funcionalidad y 28 nuevas plantillas de bots
-- Ejecutar: mysql -u <user> -p <db> < database/migrations/005_category_filters_and_new_templates.sql

SET NAMES utf8mb4;

-- ============================================================
-- 1. Actualizar categorías de plantillas existentes (normalizar)
-- ============================================================
-- Las categorías ahora representan la funcionalidad del bot:
-- ai, productivity, commerce, entertainment, moderation, monitoring,
-- developer, social, utility, starter, finance, gaming, education,
-- marketing, security

-- ============================================================
-- 2. Nuevas plantillas de bots (28 adicionales, total ~50)
-- ============================================================
INSERT INTO bot_templates (slug, name, description, short_description, platform, category, icon, docker_image, git_repo_url, default_env_vars, required_env_vars, ram_mb_min, min_plan_slug, difficulty, tags, setup_instructions, is_featured, sort_order, auto_update_supported, version) VALUES

-- 23. Telegram AI Voice Transcription Bot (Whisper)
('telegram-whisper-bot', 'Telegram Transcripción de Voz IA',
'Bot de Telegram que transcribe mensajes de voz y audios usando OpenAI Whisper. Envía un audio o nota de voz y recibe el texto transcrito en segundos. Soporta más de 90 idiomas con detección automática. También resume audios largos con GPT.',
'Transcribe audios y notas de voz con IA Whisper.',
'telegram', 'ai', '🎙️', 'python:3.11-slim',
'https://github.com/openai/whisper',
'{"BOT_TOKEN": "", "OPENAI_API_KEY": "", "WHISPER_MODEL": "whisper-1", "LANGUAGE": "es"}',
'[{"key": "BOT_TOKEN", "label": "Token del Bot de Telegram", "placeholder": "123456:ABC-DEF...", "required": true}, {"key": "OPENAI_API_KEY", "label": "API Key de OpenAI", "placeholder": "sk-...", "required": true}]',
256, 'starter', 'easy', 'telegram,whisper,voz,audio,transcripcion,ia,python,speech-to-text',
'1. Crea un bot con @BotFather en Telegram\n2. Obtén una API Key de OpenAI con acceso a Whisper\n3. Configura BOT_TOKEN y OPENAI_API_KEY\n4. Despliega el bot\n5. Envía una nota de voz o audio y recibirás la transcripción',
1, 23, 1, '1.0.0'),

-- 24. Discord AI Art Bot (Stable Diffusion)
('discord-stablediff-bot', 'Discord Generador de Arte IA',
'Bot de Discord que genera imágenes con Stable Diffusion vía API de Stability AI o Replicate. Comandos /imagine para generar, /variations para variantes, /upscale para mejorar resolución. Estilos artísticos configurables: anime, realista, pixel art, etc.',
'Genera arte con IA directamente en Discord.',
'discord', 'ai', '🎨', 'node:20-alpine',
'https://github.com/Stability-AI/platform',
'{"DISCORD_TOKEN": "", "STABILITY_API_KEY": "", "DEFAULT_STYLE": "photographic", "MAX_STEPS": "30"}',
'[{"key": "DISCORD_TOKEN", "label": "Token del Bot de Discord", "placeholder": "MTIzNDU2...", "required": true}, {"key": "STABILITY_API_KEY", "label": "API Key de Stability AI o Replicate", "placeholder": "sk-...", "required": true}]',
256, 'starter', 'easy', 'discord,stable-diffusion,imagen,arte,ia,generativo,nodejs',
'1. Crea una app y bot en Discord Developer Portal\n2. Obtén una API Key en stability.ai o replicate.com\n3. Activa intents: Message Content\n4. Invita al bot con permisos de adjuntar archivos\n5. Usa /imagine <prompt> para generar imágenes',
1, 24, 1, '1.0.0'),

-- 25. Telegram Crypto Price Bot
('telegram-crypto-bot', 'Telegram Crypto Precios Bot',
'Bot de Telegram que muestra precios de criptomonedas en tiempo real. Alertas personalizables por precio, porcentaje de cambio o volumen. Portfolio tracker, gráficos de 24h/7d/30d, comparativas entre monedas. Datos de CoinGecko (gratis).',
'Precios crypto en tiempo real y alertas personalizadas.',
'telegram', 'finance', '📈', 'python:3.11-slim',
'https://github.com/man-c/pycoingecko',
'{"BOT_TOKEN": "", "DEFAULT_CURRENCY": "eur", "ALERT_CHECK_INTERVAL": "60"}',
'[{"key": "BOT_TOKEN", "label": "Token del Bot de Telegram", "placeholder": "123456:ABC-DEF...", "required": true}]',
128, 'free', 'easy', 'telegram,crypto,bitcoin,ethereum,precios,finanzas,alertas,python,coingecko',
'1. Crea un bot con @BotFather\n2. Pega el token en BOT_TOKEN\n3. Elige la moneda base (eur, usd, etc.)\n4. Despliega el bot\n5. Usa /price btc o /alert btc > 100000 para alertas',
1, 25, 1, '1.0.0'),

-- 26. Discord Giveaway Bot
('discord-giveaway-bot', 'Discord Sorteos Bot',
'Bot de Discord para organizar sorteos y giveaways profesionales. Temporización automática, requisitos de participación (roles, antigüedad, nivel), múltiples ganadores, re-sorteos, y base de datos de sorteos pasados. Integra con sistema de niveles.',
'Organiza sorteos profesionales en Discord.',
'discord', 'entertainment', '🎁', 'node:20-alpine',
'https://github.com/Androz2091/discord-giveaways',
'{"DISCORD_TOKEN": "", "PREFIX": "!", "GIVEAWAY_EMOJI": "🎉", "DEFAULT_DURATION": "24h"}',
'[{"key": "DISCORD_TOKEN", "label": "Token del Bot de Discord", "placeholder": "MTIzNDU2...", "required": true}]',
128, 'free', 'easy', 'discord,sorteos,giveaway,comunidad,eventos,nodejs',
'1. Crea una app y bot en Discord Developer Portal\n2. Activa intents: Message Content, Server Members\n3. Invita al bot con permisos de gestión de mensajes\n4. Usa !giveaway 24h 1 Premio para iniciar un sorteo\n5. Los usuarios reaccionan con 🎉 para participar',
1, 26, 1, '1.0.0'),

-- 27. Telegram Expense Tracker Bot
('telegram-expense-bot', 'Telegram Control de Gastos Bot',
'Bot de Telegram para registrar y controlar tus gastos personales. Envía "café 3.50" y queda registrado. Categorías automáticas, presupuestos mensuales, gráficos de gastos, exportación a CSV/PDF. Resúmenes diarios/semanales/mensuales.',
'Controla tus gastos personales desde Telegram.',
'telegram', 'productivity', '💰', 'python:3.11-slim',
'https://github.com/bot-base/telegram-bot-template',
'{"BOT_TOKEN": "", "CURRENCY": "EUR", "TIMEZONE": "Europe/Madrid", "MONTHLY_BUDGET": "1000"}',
'[{"key": "BOT_TOKEN", "label": "Token del Bot de Telegram", "placeholder": "123456:ABC-DEF...", "required": true}, {"key": "TIMEZONE", "label": "Tu zona horaria", "placeholder": "Europe/Madrid", "required": false}]',
128, 'free', 'easy', 'telegram,gastos,finanzas,presupuesto,productividad,python',
'1. Crea un bot con @BotFather\n2. Configura el token y tu zona horaria\n3. Despliega el bot\n4. Envía mensajes como \"café 3.50\" o \"supermercado 45\"\n5. Usa /resumen para ver tus gastos del mes',
1, 27, 1, '1.0.0'),

-- 28. n8n Workflow Automation
('n8n-automation-bot', 'n8n Automatización de Flujos',
'n8n es una plataforma de automatización de flujos de trabajo (como Zapier pero self-hosted). Conecta más de 400 servicios: Gmail, Sheets, Slack, Telegram, GitHub, Shopify, Stripe, etc. Interfaz visual drag-and-drop para crear automatizaciones sin código.',
'Automatiza flujos de trabajo como Zapier, self-hosted.',
'multi', 'developer', '⚡', 'n8nio/n8n:latest',
'https://github.com/n8n-io/n8n',
'{"N8N_BASIC_AUTH_ACTIVE": "true", "N8N_BASIC_AUTH_USER": "admin", "N8N_BASIC_AUTH_PASSWORD": "", "WEBHOOK_URL": "", "TIMEZONE": "Europe/Madrid"}',
'[{"key": "N8N_BASIC_AUTH_PASSWORD", "label": "Contraseña del panel de n8n", "placeholder": "tu_contraseña_segura", "required": true}, {"key": "WEBHOOK_URL", "label": "URL pública del servicio (para webhooks)", "placeholder": "https://n8n.midominio.com", "required": false}]',
512, 'medium', 'medium', 'n8n,automatizacion,workflow,zapier,integracion,nocode,multi',
'1. Elige una contraseña segura para el panel de administración\n2. Despliega n8n\n3. Accede al panel web con las credenciales configuradas\n4. Crea tu primer flujo de trabajo arrastrando y conectando nodos\n5. Conecta servicios con sus API keys desde el panel',
1, 28, 1, '1.0.0'),

-- 29. Discord Advanced Music (Lavalink)
('discord-lavalink-bot', 'Discord Música Avanzada (Lavalink)',
'Bot de música avanzado para Discord con Lavalink como backend de audio. Soporta YouTube, Spotify, SoundCloud, Deezer, Apple Music y más. Ecualizador, filtros de audio (bass boost, nightcore, 8D), letras, cola ilimitada y playlists guardadas.',
'Música avanzada con Spotify, filtros y ecualizador.',
'discord', 'entertainment', '🎶', 'node:20-alpine',
'https://github.com/stegripe/rawon',
'{"DISCORD_TOKEN": "", "LAVALINK_HOST": "localhost", "LAVALINK_PORT": "2333", "LAVALINK_PASSWORD": "youshallnotpass", "SPOTIFY_CLIENT_ID": "", "SPOTIFY_CLIENT_SECRET": ""}',
'[{"key": "DISCORD_TOKEN", "label": "Token del Bot de Discord", "placeholder": "MTIzNDU2...", "required": true}, {"key": "SPOTIFY_CLIENT_ID", "label": "Spotify Client ID (opcional)", "placeholder": "abc123...", "required": false}, {"key": "SPOTIFY_CLIENT_SECRET", "label": "Spotify Client Secret (opcional)", "placeholder": "xyz789...", "required": false}]',
512, 'medium', 'advanced', 'discord,musica,lavalink,spotify,soundcloud,ecualizador,nodejs',
'1. Crea una app y bot en Discord Developer Portal\n2. Activa intents: Message Content, Voice\n3. Opcionalmente crea una app en Spotify Developer\n4. Configura los tokens\n5. Despliega (Lavalink se incluye internamente)\n6. Usa /play <canción> para reproducir',
1, 29, 1, '1.0.0'),

-- 30. Telegram Trading Signals Bot
('telegram-trading-bot', 'Telegram Señales de Trading Bot',
'Bot de Telegram que analiza mercados y envía señales de trading basadas en indicadores técnicos (RSI, MACD, Bollinger Bands, EMA). Soporta crypto y forex. Alertas por niveles de precio, volumen anormal y cruces de medias. Backtest incluido.',
'Señales de trading y análisis técnico automatizado.',
'telegram', 'finance', '📊', 'python:3.11-slim',
'https://github.com/freqtrade/freqtrade',
'{"BOT_TOKEN": "", "EXCHANGE": "binance", "TRADING_PAIRS": "BTC/USDT,ETH/USDT", "TIMEFRAME": "1h", "RSI_PERIOD": "14"}',
'[{"key": "BOT_TOKEN", "label": "Token del Bot de Telegram", "placeholder": "123456:ABC-DEF...", "required": true}, {"key": "TRADING_PAIRS", "label": "Pares de trading (separados por coma)", "placeholder": "BTC/USDT,ETH/USDT", "required": true}]',
256, 'starter', 'medium', 'telegram,trading,crypto,forex,señales,indicadores,finanzas,python',
'1. Crea un bot con @BotFather\n2. Define los pares de trading que quieres analizar\n3. Elige el exchange (binance, kraken, etc.)\n4. Configura el intervalo de análisis\n5. Despliega y recibe señales en tu chat',
1, 30, 1, '1.0.0'),

-- 31. WhatsApp Product Catalog Bot
('whatsapp-catalog-bot', 'WhatsApp Catálogo de Productos',
'Bot de WhatsApp que muestra un catálogo de productos interactivo. Los clientes navegan categorías, ven precios, fotos y detalles. Carrito de compra, proceso de pedido y notificación al vendedor. Ideal para tiendas pequeñas y negocios locales.',
'Catálogo de productos y pedidos por WhatsApp.',
'whatsapp', 'commerce', '🛍️', 'node:20-alpine',
'https://github.com/nicehash/whatsapp-api-client',
'{"WHATSAPP_TOKEN": "", "WHATSAPP_PHONE_ID": "", "VERIFY_TOKEN": "lanzabot_verify", "SHOP_NAME": "Mi Tienda", "CURRENCY": "EUR", "ADMIN_PHONE": ""}',
'[{"key": "WHATSAPP_TOKEN", "label": "Token de WhatsApp Business API", "placeholder": "EAABs...", "required": true}, {"key": "WHATSAPP_PHONE_ID", "label": "ID del número de teléfono", "placeholder": "1234567890", "required": true}, {"key": "VERIFY_TOKEN", "label": "Token de verificación del webhook", "placeholder": "mi_token_secreto", "required": true}]',
256, 'starter', 'medium', 'whatsapp,catalogo,productos,ecommerce,pedidos,tienda,nodejs',
'1. Configura WhatsApp Business API en Meta for Developers\n2. Obtén el token de acceso y Phone Number ID\n3. Define un nombre de tienda y moneda\n4. Despliega el bot\n5. Los clientes envían \"catálogo\" para ver productos',
1, 31, 1, '1.0.0'),

-- 32. Telegram PDF Tools Bot
('telegram-pdf-bot', 'Telegram Herramientas PDF Bot',
'Bot de Telegram para trabajar con PDFs. Convierte imágenes a PDF, une varios PDFs, comprime archivos pesados, extrae texto (OCR), añade marcas de agua, protege con contraseña y convierte PDF a Word/imágenes. Todo gratis y sin límites.',
'Convierte, une, comprime y edita PDFs desde Telegram.',
'telegram', 'productivity', '📄', 'python:3.11-slim',
'https://github.com/zeshuaro/telegram-pdf-bot',
'{"BOT_TOKEN": "", "MAX_FILE_SIZE_MB": "50", "LANGUAGE": "es"}',
'[{"key": "BOT_TOKEN", "label": "Token del Bot de Telegram", "placeholder": "123456:ABC-DEF...", "required": true}]',
256, 'free', 'easy', 'telegram,pdf,convertir,comprimir,ocr,herramientas,python',
'1. Crea un bot con @BotFather\n2. Configura el token\n3. Despliega el bot\n4. Envía archivos PDF o imágenes al bot\n5. Elige la operación: unir, comprimir, convertir, etc.',
1, 32, 1, '1.0.0'),

-- 33. Discord Gamer Stats Bot
('discord-gamerstats-bot', 'Discord Estadísticas Gamer Bot',
'Bot de Discord que muestra estadísticas de tus juegos: Steam, Valorant, League of Legends, Fortnite, Apex Legends, CS2. Perfiles de jugador, rankings, historial de partidas, comparativas. Se actualiza automáticamente.',
'Consulta estadísticas de videojuegos en Discord.',
'discord', 'gaming', '🕹️', 'node:20-alpine',
'https://github.com/TrackerNetwork/tracker.gg',
'{"DISCORD_TOKEN": "", "STEAM_API_KEY": "", "TRACKER_API_KEY": ""}',
'[{"key": "DISCORD_TOKEN", "label": "Token del Bot de Discord", "placeholder": "MTIzNDU2...", "required": true}, {"key": "STEAM_API_KEY", "label": "Steam API Key (opcional)", "placeholder": "ABC123...", "required": false}]',
128, 'free', 'easy', 'discord,gaming,stats,steam,valorant,lol,fortnite,nodejs',
'1. Crea una app y bot en Discord Developer Portal\n2. Opcionalmente obtén una Steam API Key en steamcommunity.com/dev\n3. Configura el token de Discord\n4. Invita al bot a tu servidor\n5. Usa /stats valorant <nombre> para ver estadísticas',
1, 33, 1, '1.0.0'),

-- 34. Telegram Language Learning Bot
('telegram-language-bot', 'Telegram Aprendizaje de Idiomas Bot',
'Bot de Telegram para aprender idiomas con repetición espaciada (SRS). Vocabulario, frases, pronunciación con audio, ejercicios de traducción y tests. Soporta: inglés, español, francés, alemán, portugués, italiano, japonés. Estadísticas de progreso.',
'Aprende idiomas con repetición espaciada desde Telegram.',
'telegram', 'education', '📚', 'python:3.11-slim',
'https://github.com/bot-base/telegram-bot-template',
'{"BOT_TOKEN": "", "NATIVE_LANGUAGE": "es", "TARGET_LANGUAGE": "en", "DAILY_WORDS": "10", "REMINDER_TIME": "09:00"}',
'[{"key": "BOT_TOKEN", "label": "Token del Bot de Telegram", "placeholder": "123456:ABC-DEF...", "required": true}, {"key": "TARGET_LANGUAGE", "label": "Idioma a aprender (en, fr, de, pt, it, ja)", "placeholder": "en", "required": true}]',
128, 'free', 'easy', 'telegram,idiomas,aprendizaje,educacion,vocabulario,srs,python',
'1. Crea un bot con @BotFather\n2. Elige tu idioma nativo y el idioma objetivo\n3. Configura cuántas palabras al día quieres aprender\n4. Despliega el bot\n5. Recibirás palabras nuevas diariamente y tests de repaso',
1, 34, 1, '1.0.0'),

-- 35. Telegram Advanced Poll Bot
('telegram-poll-bot', 'Telegram Encuestas Avanzadas Bot',
'Bot de Telegram para crear encuestas avanzadas con múltiples formatos: opción múltiple, texto libre, escala 1-10, matriz, NPS y más. Resultados en tiempo real con gráficos, exportación en CSV/PDF, encuestas anónimas o nominales. Ideal para comunidades.',
'Crea encuestas profesionales con gráficos y estadísticas.',
'telegram', 'social', '📊', 'python:3.11-slim',
'https://github.com/bot-base/telegram-bot-template',
'{"BOT_TOKEN": "", "LANGUAGE": "es", "MAX_OPTIONS": "20"}',
'[{"key": "BOT_TOKEN", "label": "Token del Bot de Telegram", "placeholder": "123456:ABC-DEF...", "required": true}]',
128, 'free', 'easy', 'telegram,encuestas,polls,votacion,estadisticas,comunidad,python',
'1. Crea un bot con @BotFather\n2. Configura el token\n3. Despliega el bot\n4. Usa /newpoll para crear una encuesta\n5. Comparte el enlace en tus grupos o canales',
1, 35, 1, '1.0.0'),

-- 36. Discord Reaction Roles Bot
('discord-reaction-roles', 'Discord Roles por Reacción Bot',
'Bot de Discord que permite a los usuarios auto-asignarse roles reaccionando a mensajes. Panel de configuración sencillo, múltiples mensajes de roles, roles exclusivos (solo uno del grupo), roles temporales y logs de asignación. El más popular para gestionar roles.',
'Auto-asignación de roles con reacciones en Discord.',
'discord', 'utility', '🏷️', 'node:20-alpine',
'https://github.com/Androz2091/welcome-bot',
'{"DISCORD_TOKEN": "", "PREFIX": "!"}',
'[{"key": "DISCORD_TOKEN", "label": "Token del Bot de Discord", "placeholder": "MTIzNDU2...", "required": true}]',
128, 'free', 'easy', 'discord,roles,reacciones,comunidad,gestion,nodejs',
'1. Crea una app y bot en Discord Developer Portal\n2. Activa intents: Server Members, Message Content\n3. Invita al bot con permisos de gestión de roles\n4. Usa !reactionroles para crear un mensaje de roles\n5. Los miembros solo necesitan reaccionar para obtener sus roles',
1, 36, 1, '1.0.0'),

-- 37. Telegram Link Shortener Bot
('telegram-shortener-bot', 'Telegram Acortador de URLs Bot',
'Bot de Telegram que acorta URLs y genera estadísticas detalladas: clics, países, dispositivos, navegadores, referrers. Genera QR codes de cada enlace. Ideal para marketing, redes sociales y seguimiento de campañas. URLs personalizables.',
'Acorta URLs y obtén estadísticas de clics.',
'telegram', 'marketing', '🔗', 'python:3.11-slim',
'https://github.com/bot-base/telegram-bot-template',
'{"BOT_TOKEN": "", "SHORT_DOMAIN": "", "TIMEZONE": "Europe/Madrid"}',
'[{"key": "BOT_TOKEN", "label": "Token del Bot de Telegram", "placeholder": "123456:ABC-DEF...", "required": true}]',
128, 'free', 'easy', 'telegram,url,acortador,marketing,estadisticas,qr,python',
'1. Crea un bot con @BotFather\n2. Configura el token\n3. Opcionalmente configura un dominio corto propio\n4. Despliega el bot\n5. Envía cualquier URL para obtener un enlace corto con estadísticas',
1, 37, 1, '1.0.0'),

-- 38. Telegram Sticker Maker Bot
('telegram-sticker-bot', 'Telegram Creador de Stickers Bot',
'Bot de Telegram que convierte imágenes, GIFs y vídeos cortos en stickers y packs de stickers. Recorte automático de fondo, redimensionado inteligente, texto personalizable, emojis asociados. Crea packs de stickers completos desde el chat.',
'Crea stickers y packs de stickers desde imágenes.',
'telegram', 'entertainment', '🎭', 'python:3.11-slim',
'https://github.com/bot-base/telegram-bot-template',
'{"BOT_TOKEN": "", "MAX_STICKERS_PER_PACK": "120", "REMOVE_BACKGROUND": "true"}',
'[{"key": "BOT_TOKEN", "label": "Token del Bot de Telegram", "placeholder": "123456:ABC-DEF...", "required": true}]',
128, 'free', 'easy', 'telegram,stickers,imagenes,creatividad,diversion,python',
'1. Crea un bot con @BotFather\n2. Configura el token\n3. Despliega el bot\n4. Envía una imagen al bot\n5. Elige si quieres recortar fondo, añadir texto, etc.\n6. El bot creará el sticker y lo añadirá a tu pack',
1, 38, 1, '1.0.0'),

-- 39. Discord Trivia Bot
('discord-trivia-bot', 'Discord Trivia / Quiz Bot',
'Bot de Discord con miles de preguntas de trivia en múltiples categorías: ciencia, historia, deportes, geografía, cine, música, videojuegos. Modos: individual, versus, equipos, contrarreloj. Sistema de puntos, rachas y leaderboard mensual.',
'Juegos de trivia y quiz para servidores de Discord.',
'discord', 'gaming', '🧩', 'node:20-alpine',
'https://github.com/Androz2091/discord-giveaways',
'{"DISCORD_TOKEN": "", "PREFIX": "!", "DEFAULT_CATEGORY": "general", "QUESTIONS_PER_ROUND": "10"}',
'[{"key": "DISCORD_TOKEN", "label": "Token del Bot de Discord", "placeholder": "MTIzNDU2...", "required": true}]',
128, 'free', 'easy', 'discord,trivia,quiz,juegos,preguntas,educacion,nodejs',
'1. Crea una app y bot en Discord Developer Portal\n2. Activa intents: Message Content\n3. Invita al bot a tu servidor\n4. Usa !trivia para iniciar una partida\n5. Los jugadores responden con !a, !b, !c o !d',
1, 39, 1, '1.0.0'),

-- 40. Telegram QR Code Bot
('telegram-qr-bot', 'Telegram Código QR Bot',
'Bot de Telegram que genera y lee códigos QR. Genera QR desde texto, URLs, vCards (contacto), ubicaciones, WiFi y más. Lee QR desde imágenes o fotos enviadas. QR personalizables con colores, logos y formatos (PNG, SVG, PDF).',
'Genera y lee códigos QR desde Telegram.',
'telegram', 'utility', '📲', 'python:3.11-slim',
'https://github.com/bot-base/telegram-bot-template',
'{"BOT_TOKEN": "", "DEFAULT_SIZE": "500", "DEFAULT_FORMAT": "png"}',
'[{"key": "BOT_TOKEN", "label": "Token del Bot de Telegram", "placeholder": "123456:ABC-DEF...", "required": true}]',
128, 'free', 'easy', 'telegram,qr,codigo,generador,lector,utilidad,python',
'1. Crea un bot con @BotFather\n2. Configura el token\n3. Despliega el bot\n4. Envía texto o URL para generar un QR\n5. Envía una foto con QR para leerlo',
1, 40, 1, '1.0.0'),

-- 41. Discord Anti-Raid Bot
('discord-antiraid-bot', 'Discord Anti-Raid / Seguridad Bot',
'Bot de seguridad avanzada para Discord. Detecta y mitiga raids (ataques masivos de cuentas), verificación CAPTCHA para nuevos miembros, detección de cuentas alt/nuevas, anti-nuke (previene borrado masivo), modo lockdown y whitelist de bots.',
'Protección avanzada contra raids y ataques.',
'discord', 'security', '🔐', 'node:20-alpine',
'https://github.com/jagrosh/Vortex',
'{"DISCORD_TOKEN": "", "CAPTCHA_ENABLED": "true", "MIN_ACCOUNT_AGE_DAYS": "7", "RAID_THRESHOLD": "10", "LOCKDOWN_AUTO": "true"}',
'[{"key": "DISCORD_TOKEN", "label": "Token del Bot de Discord", "placeholder": "MTIzNDU2...", "required": true}]',
128, 'starter', 'medium', 'discord,seguridad,antiraid,captcha,antinuke,proteccion,nodejs',
'1. Crea una app y bot en Discord Developer Portal\n2. Activa TODOS los intents privilegiados\n3. Invita con permisos de administrador\n4. Configura DISCORD_TOKEN\n5. Despliega y usa /setup para configurar la protección\n6. El bot actuará automáticamente ante amenazas',
1, 41, 1, '1.0.0'),

-- 42. Telegram AI Summary Bot
('telegram-summary-bot', 'Telegram Resumen IA Bot',
'Bot de Telegram que resume artículos, noticias y textos largos con IA. Envía un enlace o texto y recibe un resumen conciso. Soporta: artículos web, PDFs, vídeos de YouTube (transcripción + resumen), hilos de Twitter/X. Idioma configurable.',
'Resume artículos, webs y vídeos con IA.',
'telegram', 'ai', '📝', 'python:3.11-slim',
'https://github.com/openai/openai-python',
'{"BOT_TOKEN": "", "OPENAI_API_KEY": "", "SUMMARY_LANGUAGE": "es", "MAX_TOKENS": "500"}',
'[{"key": "BOT_TOKEN", "label": "Token del Bot de Telegram", "placeholder": "123456:ABC-DEF...", "required": true}, {"key": "OPENAI_API_KEY", "label": "API Key de OpenAI", "placeholder": "sk-...", "required": true}]',
256, 'starter', 'easy', 'telegram,resumen,summary,ia,articulos,noticias,youtube,python',
'1. Crea un bot con @BotFather\n2. Obtén una API Key de OpenAI\n3. Configura los tokens\n4. Despliega el bot\n5. Envía un enlace o texto largo para obtener el resumen',
1, 42, 1, '1.0.0'),

-- 43. Telegram Appointment Bot
('telegram-appointment-bot', 'Telegram Reservas y Citas Bot',
'Bot de Telegram para gestionar reservas y citas. Agenda visual con horarios disponibles, confirmación automática, recordatorios antes de la cita, cancelaciones, re-agendamiento. Ideal para peluquerías, médicos, consultorías y servicios.',
'Gestiona reservas y citas desde Telegram.',
'telegram', 'commerce', '📅', 'python:3.11-slim',
'https://github.com/bot-base/telegram-bot-template',
'{"BOT_TOKEN": "", "TIMEZONE": "Europe/Madrid", "BUSINESS_NAME": "Mi Negocio", "SLOT_DURATION": "30", "WORKING_HOURS": "09:00-18:00", "WORKING_DAYS": "1,2,3,4,5"}',
'[{"key": "BOT_TOKEN", "label": "Token del Bot de Telegram", "placeholder": "123456:ABC-DEF...", "required": true}, {"key": "TIMEZONE", "label": "Tu zona horaria", "placeholder": "Europe/Madrid", "required": true}, {"key": "BUSINESS_NAME", "label": "Nombre de tu negocio", "placeholder": "Mi Consulta", "required": true}]',
128, 'free', 'medium', 'telegram,reservas,citas,agenda,booking,negocios,python',
'1. Crea un bot con @BotFather\n2. Configura nombre del negocio, horario laboral y duración de citas\n3. Define tu zona horaria\n4. Despliega el bot\n5. Comparte el enlace @tubot con tus clientes para que reserven',
1, 43, 1, '1.0.0'),

-- 44. Discord Custom Embeds Bot
('discord-embeds-bot', 'Discord Embeds Personalizados Bot',
'Bot de Discord para crear mensajes embed enriquecidos sin código. Editor visual de embeds: título, descripción, color, campos, imágenes, footer, timestamps. Plantillas guardables, embeds programados y edición posterior. Perfecto para anuncios y reglas.',
'Crea mensajes embed ricos y bonitos fácilmente.',
'discord', 'utility', '✨', 'node:20-alpine',
'https://github.com/discordjs/discord.js',
'{"DISCORD_TOKEN": "", "PREFIX": "!", "DEFAULT_COLOR": "#5865F2"}',
'[{"key": "DISCORD_TOKEN", "label": "Token del Bot de Discord", "placeholder": "MTIzNDU2...", "required": true}]',
128, 'free', 'easy', 'discord,embeds,mensajes,diseño,anuncios,personalizado,nodejs',
'1. Crea una app y bot en Discord Developer Portal\n2. Invita al bot con permisos de enviar mensajes y embeds\n3. Configura el token\n4. Usa /embed para abrir el editor visual\n5. Personaliza título, descripción, campos y colores\n6. Publica en el canal que quieras',
0, 44, 1, '1.0.0'),

-- 45. Telegram SEO Monitor Bot
('telegram-seo-bot', 'Telegram Monitor SEO Bot',
'Bot de Telegram que monitorea tus posiciones en Google para las keywords que elijas. Alertas cuando subes o bajas posiciones, informe semanal con evolución, análisis de competidores, tracking de backlinks y métricas de visibilidad.',
'Monitorea tus posiciones SEO y recibe alertas.',
'telegram', 'marketing', '🔍', 'python:3.11-slim',
'https://github.com/nicehash/node-web-scraper',
'{"BOT_TOKEN": "", "CHAT_ID": "", "DOMAIN": "", "KEYWORDS": "", "COUNTRY": "es", "CHECK_INTERVAL": "86400"}',
'[{"key": "BOT_TOKEN", "label": "Token del Bot de Telegram", "placeholder": "123456:ABC-DEF...", "required": true}, {"key": "CHAT_ID", "label": "ID de chat para informes", "placeholder": "-1001234567890", "required": true}, {"key": "DOMAIN", "label": "Tu dominio web", "placeholder": "miempresa.com", "required": true}, {"key": "KEYWORDS", "label": "Keywords a monitorear (separadas por coma)", "placeholder": "hosting bots, bot telegram", "required": true}]',
256, 'starter', 'medium', 'telegram,seo,google,keywords,posiciones,marketing,python',
'1. Crea un bot con @BotFather\n2. Obtén el CHAT_ID donde quieres recibir informes\n3. Introduce tu dominio y las keywords objetivo\n4. Elige el país de búsqueda\n5. Despliega y recibirás un informe diario de posiciones',
0, 45, 1, '1.0.0'),

-- 46. AI Customer Support Bot
('ai-support-bot', 'Asistente de Soporte IA Multi-plataforma',
'Bot de soporte al cliente potenciado con IA. Responde preguntas frecuentes, aprende de tu base de conocimiento (FAQ, docs), escala a humanos cuando no puede resolver, genera tickets y guarda historial. Funciona en Telegram, Discord y WhatsApp simultáneamente.',
'Soporte al cliente con IA, aprende de tu documentación.',
'multi', 'ai', '🤝', 'python:3.11-slim',
'https://github.com/openai/openai-python',
'{"OPENAI_API_KEY": "", "TELEGRAM_TOKEN": "", "DISCORD_TOKEN": "", "COMPANY_NAME": "", "KNOWLEDGE_BASE": "", "ESCALATION_EMAIL": ""}',
'[{"key": "OPENAI_API_KEY", "label": "API Key de OpenAI", "placeholder": "sk-...", "required": true}, {"key": "COMPANY_NAME", "label": "Nombre de tu empresa", "placeholder": "Mi Empresa", "required": true}, {"key": "TELEGRAM_TOKEN", "label": "Token de Telegram (opcional)", "placeholder": "123456:ABC...", "required": false}, {"key": "DISCORD_TOKEN", "label": "Token de Discord (opcional)", "placeholder": "MTIzNDU2...", "required": false}]',
512, 'medium', 'medium', 'multi,ia,soporte,customer-support,faq,helpdesk,empresa',
'1. Obtén una API Key de OpenAI\n2. Configura al menos un canal (Telegram o Discord)\n3. Introduce el nombre de tu empresa\n4. Opcionalmente añade tu base de conocimiento\n5. Despliega y el bot aprenderá de tus docs para responder',
1, 46, 1, '1.0.0'),

-- 47. Telegram Invoicing Bot
('telegram-invoice-bot', 'Telegram Facturación Bot',
'Bot de Telegram para crear y enviar facturas profesionales. Genera PDFs con logo, datos fiscales, líneas de concepto, impuestos y totales. Envío directo al cliente por Telegram, email o enlace. Numeración automática, histórico y estadísticas de facturación.',
'Crea y envía facturas profesionales desde Telegram.',
'telegram', 'finance', '🧾', 'python:3.11-slim',
'https://github.com/bot-base/telegram-bot-template',
'{"BOT_TOKEN": "", "BUSINESS_NAME": "", "TAX_ID": "", "TAX_RATE": "21", "CURRENCY": "EUR", "LOGO_URL": ""}',
'[{"key": "BOT_TOKEN", "label": "Token del Bot de Telegram", "placeholder": "123456:ABC-DEF...", "required": true}, {"key": "BUSINESS_NAME", "label": "Nombre de tu empresa/autónomo", "placeholder": "Mi Empresa S.L.", "required": true}, {"key": "TAX_ID", "label": "NIF/CIF", "placeholder": "B12345678", "required": true}]',
128, 'starter', 'medium', 'telegram,facturas,invoicing,negocios,contabilidad,pdf,python',
'1. Crea un bot con @BotFather\n2. Introduce los datos de facturación de tu empresa\n3. Configura tipo de IVA y moneda\n4. Despliega el bot\n5. Usa /nueva para crear una factura y /enviar para enviarla',
0, 47, 1, '1.0.0'),

-- 48. Discord Study / Pomodoro Bot
('discord-pomodoro-bot', 'Discord Estudio / Pomodoro Bot',
'Bot de Discord con temporizador Pomodoro para sesiones de estudio en grupo. Study rooms con audio ambiente, contadores de sesiones, streaks diarios, leaderboard de horas estudiadas. Roles automáticos por horas acumuladas. Perfecto para servidores de estudio.',
'Sesiones de estudio Pomodoro con leaderboard.',
'discord', 'education', '🍅', 'node:20-alpine',
'https://github.com/discordjs/discord.js',
'{"DISCORD_TOKEN": "", "POMODORO_WORK": "25", "POMODORO_BREAK": "5", "LONG_BREAK": "15"}',
'[{"key": "DISCORD_TOKEN", "label": "Token del Bot de Discord", "placeholder": "MTIzNDU2...", "required": true}]',
128, 'free', 'easy', 'discord,pomodoro,estudio,productividad,educacion,timer,nodejs',
'1. Crea una app y bot en Discord Developer Portal\n2. Activa intents: Server Members\n3. Invita al bot a tu servidor\n4. Usa /pomodoro para iniciar una sesión\n5. El bot avisará cuando terminen el periodo de trabajo y descanso\n6. Consulta /stats para ver tu progreso',
1, 48, 1, '1.0.0'),

-- 49. Telegram AI Code Assistant Bot
('telegram-code-bot', 'Telegram Asistente de Código IA Bot',
'Bot de Telegram especializado en programación. Genera código en cualquier lenguaje, explica código existente, encuentra bugs, sugiere optimizaciones, convierte entre lenguajes y genera tests unitarios. Potenciado por GPT-4o con contexto de programación.',
'Asistente de programación con IA en Telegram.',
'telegram', 'ai', '💻', 'python:3.11-slim',
'https://github.com/openai/openai-python',
'{"BOT_TOKEN": "", "OPENAI_API_KEY": "", "OPENAI_MODEL": "gpt-4o", "DEFAULT_LANGUAGE": "python"}',
'[{"key": "BOT_TOKEN", "label": "Token del Bot de Telegram", "placeholder": "123456:ABC-DEF...", "required": true}, {"key": "OPENAI_API_KEY", "label": "API Key de OpenAI", "placeholder": "sk-...", "required": true}]',
256, 'starter', 'easy', 'telegram,codigo,programacion,ia,gpt,developer,python',
'1. Crea un bot con @BotFather\n2. Obtén una API Key de OpenAI\n3. Configura los tokens\n4. Despliega el bot\n5. Envía código o pide que genere código en cualquier lenguaje',
1, 49, 1, '1.0.0'),

-- 50. Discord Server Analytics Bot
('discord-analytics-bot', 'Discord Analíticas del Servidor Bot',
'Bot de Discord que genera estadísticas detalladas de tu servidor. Actividad de canales, mensajes por día/semana, miembros más activos, retención de usuarios, horas pico, crecimiento del servidor. Dashboard web incluido con gráficos interactivos.',
'Analíticas y estadísticas completas de tu servidor Discord.',
'discord', 'monitoring', '📈', 'node:20-alpine',
'https://github.com/discordjs/discord.js',
'{"DISCORD_TOKEN": "", "WEB_PORT": "3000", "ANALYTICS_CHANNEL": ""}',
'[{"key": "DISCORD_TOKEN", "label": "Token del Bot de Discord", "placeholder": "MTIzNDU2...", "required": true}]',
256, 'starter', 'medium', 'discord,analiticas,estadisticas,servidor,dashboard,monitoreo,nodejs',
'1. Crea una app y bot en Discord Developer Portal\n2. Activa TODOS los intents privilegiados\n3. Invita al bot con permisos de leer mensajes\n4. Configura el token\n5. Despliega y accede al dashboard web\n6. Usa /stats en Discord para resúmenes rápidos',
1, 50, 1, '1.0.0')

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
