-- Migración 012: Ecosistema de 50 bots de código abierto
-- Basado en: "Ecosistema de Automatización Descentralizada: Análisis Arquitectónico
-- de Bots de Código Abierto para el Despliegue en un Clic mediante Coolify 4"
--
-- Categorías alineadas con el documento:
--   ai            → Inteligencia Artificial y Agentes Autónomos (1-10)
--   communication → Automatización de Comunicaciones y Pasarelas Multicanal (11-20)
--   finance       → Finanzas, Comercio y Trading Algorítmico (21-30)
--   moderation    → Moderación, Seguridad y Gestión de Comunidades (31-40)
--   marketing     → Marketing, Redes Sociales y Utilidades de Desarrollo (41-50)
--
-- Ejecutar: mysql -u <user> -p <db> < database/migrations/012_ecosystem_50_bots.sql

SET NAMES utf8mb4;

-- ============================================================
-- 1. Desactivar plantillas anteriores (no borrar por retrocompat)
-- ============================================================
UPDATE bot_templates SET is_active = 0 WHERE is_active = 1;

-- Limpiar template_id huérfanos
UPDATE bots SET template_id = NULL
WHERE template_id IS NOT NULL
  AND template_id NOT IN (SELECT id FROM bot_templates);

-- ============================================================
-- 2. Insertar las 50 plantillas del ecosistema
-- ============================================================

INSERT INTO bot_templates
  (slug, name, description, short_description, platform, category, icon,
   docker_image, git_repo_url, git_branch, install_command,
   default_env_vars, required_env_vars,
   ram_mb_min, min_plan_slug, difficulty, tags,
   more_info_url, setup_instructions,
   is_featured, is_active, sort_order, auto_update_supported, version)
VALUES

-- =============================================
-- CATEGORÍA 1: IA Y AGENTES AUTÓNOMOS (1-10)
-- =============================================

-- 1. n8n
('n8n', 'n8n — Automatización de Flujos IA',
 'Plataforma de automatización de flujos de trabajo con capacidades nativas de IA para crear agentes complejos sin código. Conecta más de 400 servicios (Gmail, Sheets, Slack, Telegram, GitHub, Shopify, Stripe). Interfaz visual drag-and-drop.',
 'Automatiza flujos de trabajo como Zapier, self-hosted con IA.',
 'multi', 'ai', '⚡',
 'n8nio/n8n:latest', NULL, 'main', NULL,
 '{"N8N_BASIC_AUTH_ACTIVE": "true", "N8N_BASIC_AUTH_USER": "admin", "N8N_BASIC_AUTH_PASSWORD": "", "GENERIC_TIMEZONE": "Europe/Madrid"}',
 '[{"key": "N8N_BASIC_AUTH_PASSWORD", "label": "Contraseña del panel de n8n", "placeholder": "tu_contraseña_segura", "required": true}]',
 512, 'medium', 'medium',
 'n8n,automatizacion,workflow,zapier,ia,agentes,nocode,multi',
 'https://n8n.io/',
 '1. Elige una contraseña segura para el panel\n2. Despliega n8n\n3. Accede al panel web con usuario admin y tu contraseña\n4. Crea flujos arrastrando y conectando nodos\n5. Conecta servicios con sus API keys desde el panel',
 1, 1, 1, 1, '1.0.0'),

-- 2. Dify
('dify', 'Dify — Plataforma LLM y Agentes',
 'Marco de desarrollo de aplicaciones LLM que permite orquestar flujos de agentes, gestionar conjuntos de datos y realizar despliegues rápidos con interfaz visual intuitiva. Soporta RAG, function calling y múltiples modelos.',
 'Orquesta agentes IA y aplicaciones LLM con interfaz visual.',
 'multi', 'ai', '🤖',
 'langgenius/dify-api:latest', 'https://github.com/langgenius/dify', 'main', NULL,
 '{"SECRET_KEY": "", "OPENAI_API_KEY": "", "LOG_LEVEL": "INFO"}',
 '[{"key": "SECRET_KEY", "label": "Clave secreta de la aplicación", "placeholder": "sk-...", "required": true}, {"key": "OPENAI_API_KEY", "label": "API Key de OpenAI (u otro proveedor)", "placeholder": "sk-...", "required": true}]',
 2048, 'pro', 'advanced',
 'dify,llm,agentes,ia,rag,chatbot,openai,multi',
 'https://dify.ai/',
 '1. Genera una SECRET_KEY aleatoria\n2. Obtén una API Key de OpenAI u otro proveedor LLM\n3. Despliega Dify (requiere Redis y PostgreSQL como servicios adjuntos)\n4. Accede al panel web para crear aplicaciones de IA\n5. Configura modelos y bases de conocimiento desde la interfaz',
 1, 1, 2, 1, '1.0.0'),

-- 3. Langflow
('langflow', 'Langflow — Flujos LangChain Visual',
 'Herramienta visual de bajo código para experimentar y desplegar flujos basados en LangChain. Permite iteración rápida de prototipos de IA con interfaz drag-and-drop para cadenas, agentes y herramientas.',
 'Crea flujos de IA con LangChain visualmente.',
 'multi', 'ai', '🔗',
 'langflowai/langflow:latest', NULL, 'main', NULL,
 '{"LANGFLOW_DATABASE_URL": "sqlite:///./langflow.db", "OPENAI_API_KEY": ""}',
 '[{"key": "OPENAI_API_KEY", "label": "API Key de OpenAI", "placeholder": "sk-...", "required": true}]',
 1024, 'medium', 'medium',
 'langflow,langchain,ia,visual,flujos,prototipo,multi',
 'https://www.langflow.org/',
 '1. Obtén una API Key de OpenAI\n2. Despliega Langflow\n3. Accede a la interfaz web\n4. Arrastra componentes para crear flujos de IA\n5. Prueba y despliega tus cadenas LangChain',
 1, 1, 3, 1, '1.0.0'),

-- 4. AnythingLLM
('anythingllm', 'AnythingLLM — Base de Conocimiento IA',
 'Solución integral para convertir documentos en una base de conocimientos privada e interactuar con diversos modelos de lenguaje. Soporta RAG, chat con documentos, múltiples proveedores LLM y gestión de workspaces.',
 'Chat privado con tus documentos usando cualquier LLM.',
 'multi', 'ai', '📚',
 'mintplexlabs/anythingllm:latest', NULL, 'main', NULL,
 '{"STORAGE_DIR": "/app/server/storage", "LLM_PROVIDER": "openai", "OPEN_AI_KEY": ""}',
 '[{"key": "OPEN_AI_KEY", "label": "API Key del proveedor LLM", "placeholder": "sk-...", "required": true}]',
 1024, 'medium', 'easy',
 'anythingllm,documentos,rag,conocimiento,llm,privado,multi',
 'https://useanything.com/',
 '1. Obtén una API Key de tu proveedor LLM preferido\n2. Despliega AnythingLLM\n3. Accede al panel web y crea un workspace\n4. Sube documentos (PDF, TXT, DOCX, etc.)\n5. Chatea con tus documentos usando IA',
 1, 1, 4, 1, '1.0.0'),

-- 5. Open WebUI
('open-webui', 'Open WebUI — Interfaz ChatGPT Local',
 'Interfaz de usuario similar a ChatGPT para modelos alojados localmente. Soporte para RAG, gestión multi-usuario, plugins, generación de imágenes y conexión con Ollama u APIs compatibles con OpenAI.',
 'Interfaz tipo ChatGPT para modelos locales o APIs.',
 'multi', 'ai', '💬',
 'ghcr.io/open-webui/open-webui:main', NULL, 'main', NULL,
 '{"OLLAMA_BASE_URL": "http://ollama:11434", "OPENAI_API_BASE_URL": "", "OPENAI_API_KEY": "", "WEBUI_SECRET_KEY": ""}',
 '[{"key": "WEBUI_SECRET_KEY", "label": "Clave secreta del panel", "placeholder": "clave_aleatoria_segura", "required": true}, {"key": "OPENAI_API_KEY", "label": "API Key OpenAI (o compatible)", "placeholder": "sk-...", "required": false}]',
 1024, 'medium', 'easy',
 'openwebui,chatgpt,ollama,interfaz,local,rag,multi',
 'https://openwebui.com/',
 '1. Genera una clave secreta para el panel\n2. Opcionalmente configura conexión a Ollama o una API OpenAI-compatible\n3. Despliega Open WebUI\n4. Regístrate como primer usuario (será admin)\n5. Comienza a chatear con modelos de IA',
 1, 1, 5, 1, '1.0.0'),

-- 6. Ollama
('ollama', 'Ollama — Servidor de Modelos LLM',
 'Servidor de modelos de lenguaje grandes para ejecución local. Pieza fundamental para que otros bots ejecuten LLMs en tu propia infraestructura. Soporta Llama 3, Mistral, CodeLlama, Gemma y más.',
 'Ejecuta modelos LLM localmente (Llama, Mistral, etc.).',
 'multi', 'ai', '🦙',
 'ollama/ollama:latest', NULL, 'main', NULL,
 '{"OLLAMA_HOST": "0.0.0.0", "OLLAMA_MODELS": "/root/.ollama/models"}',
 '[]',
 4096, 'pro', 'medium',
 'ollama,llm,local,modelos,llama,mistral,gpu,multi',
 'https://ollama.com/',
 '1. Despliega Ollama (requiere plan Pro por alto consumo de RAM)\n2. Accede al servidor\n3. Ejecuta: ollama pull llama3 para descargar un modelo\n4. Conecta Open WebUI u otros servicios apuntando a este servidor\n5. Los modelos se almacenan en el volumen persistente',
 1, 1, 6, 1, '1.0.0'),

-- 7. OpenClaw (Clawdbot)
('openclaw', 'OpenClaw — Asistente IA Personal',
 'Asistente de IA personal y local que se integra con WhatsApp y Telegram. Ejecuta tareas de forma autónoma desde el chat: busca información, gestiona archivos, programa recordatorios y más.',
 'Asistente IA personal para WhatsApp y Telegram.',
 'multi', 'ai', '🦀',
 'python:3.11-slim', 'https://github.com/BlockRunAI/OpenClaw', 'main', NULL,
 '{"TELEGRAM_BOT_TOKEN": "", "OPENAI_API_KEY": ""}',
 '[{"key": "TELEGRAM_BOT_TOKEN", "label": "Token del Bot de Telegram", "placeholder": "123456:ABC-DEF...", "required": false}, {"key": "OPENAI_API_KEY", "label": "API Key de OpenAI", "placeholder": "sk-...", "required": true}]',
 512, 'medium', 'medium',
 'openclaw,asistente,ia,personal,telegram,whatsapp,autonomo',
 'https://github.com/BlockRunAI/OpenClaw',
 '1. Obtén una API Key de OpenAI\n2. Opcionalmente crea un bot en Telegram con @BotFather\n3. Configura las credenciales\n4. Despliega OpenClaw\n5. Interactúa con el asistente desde Telegram o WhatsApp',
 0, 1, 7, 1, '1.0.0'),

-- 8. CrewAI
('crewai', 'CrewAI — Equipos de Agentes IA',
 'Framework para orquestar grupos de agentes de IA autónomos que trabajan en conjunto para resolver tareas complejas, simulando una estructura de equipo humano. Define roles, herramientas y objetivos para cada agente.',
 'Orquesta equipos de agentes IA autónomos.',
 'multi', 'ai', '👥',
 'python:3.11-slim', 'https://github.com/crewAIInc/crewAI', 'main', 'pip install crewai',
 '{"OPENAI_API_KEY": "", "CREWAI_TELEMETRY": "false"}',
 '[{"key": "OPENAI_API_KEY", "label": "API Key de OpenAI", "placeholder": "sk-...", "required": true}]',
 512, 'medium', 'advanced',
 'crewai,agentes,equipo,ia,autonomo,orquestacion,multi',
 'https://www.crewai.com/',
 '1. Obtén una API Key de OpenAI\n2. Despliega CrewAI\n3. Define agentes con roles específicos (investigador, escritor, etc.)\n4. Asigna tareas y herramientas a cada agente\n5. Los agentes colaboran automáticamente para completar objetivos',
 1, 1, 8, 1, '1.0.0'),

-- 9. AutoGen (AG2)
('autogen', 'AutoGen (AG2) — Agentes Multi-conversación',
 'Desarrollado por Microsoft, permite crear aplicaciones LLM basadas en múltiples agentes que conversan entre sí para completar objetivos específicos. Ideal para resolución de problemas complejos y generación de código.',
 'Agentes IA de Microsoft que conversan entre sí.',
 'multi', 'ai', '🔄',
 'python:3.11-slim', 'https://github.com/microsoft/autogen', 'main', 'pip install autogen-agentchat',
 '{"OPENAI_API_KEY": "", "OAI_CONFIG_LIST": ""}',
 '[{"key": "OPENAI_API_KEY", "label": "API Key de OpenAI", "placeholder": "sk-...", "required": true}]',
 512, 'medium', 'advanced',
 'autogen,microsoft,agentes,conversacion,llm,codigo,multi',
 'https://microsoft.github.io/autogen/',
 '1. Obtén una API Key de OpenAI\n2. Despliega AutoGen\n3. Define agentes conversacionales con roles\n4. Los agentes interactúan entre sí para resolver tareas\n5. Ideal para generación de código y análisis complejos',
 0, 1, 9, 1, '1.0.0'),

-- 10. AgentScope
('agentscope', 'AgentScope — Apps Multi-Agente',
 'Marco centrado en el desarrollador para construir aplicaciones multi-agente robustas. Enfoque en facilidad de uso, interacciones robustas y soporte para múltiples proveedores de modelos LLM.',
 'Framework de apps multi-agente para desarrolladores.',
 'multi', 'ai', '🔬',
 'python:3.11-slim', 'https://github.com/modelscope/agentscope', 'main', 'pip install agentscope',
 '{"OPENAI_API_KEY": "", "MODEL_CONFIG": ""}',
 '[{"key": "OPENAI_API_KEY", "label": "API Key del proveedor LLM", "placeholder": "sk-...", "required": true}]',
 512, 'medium', 'advanced',
 'agentscope,modelscope,multi-agente,llm,developer,framework',
 'https://github.com/modelscope/agentscope',
 '1. Obtén una API Key de tu proveedor LLM\n2. Despliega AgentScope\n3. Configura los modelos disponibles\n4. Crea aplicaciones multi-agente con el SDK\n5. Prueba las interacciones entre agentes',
 0, 1, 10, 1, '1.0.0'),

-- =============================================
-- CATEGORÍA 2: COMUNICACIONES Y PASARELAS (11-20)
-- =============================================

-- 11. Evolution API
('evolution-api', 'Evolution API — API REST para WhatsApp',
 'Interfaz API REST para controlar cuentas de WhatsApp. Permite integraciones avanzadas con Typebot, Chatwoot y n8n. Envía y recibe mensajes, gestiona grupos, envía medios y más desde cualquier lenguaje.',
 'API REST completa para automatizar WhatsApp.',
 'whatsapp', 'communication', '📱',
 'atendai/evolution-api:latest', NULL, 'main', NULL,
 '{"AUTHENTICATION_API_KEY": "", "AUTHENTICATION_EXPOSE_IN_FETCH_INSTANCES": "true", "SERVER_PORT": "8080"}',
 '[{"key": "AUTHENTICATION_API_KEY", "label": "API Key de autenticación", "placeholder": "tu_api_key_segura", "required": true}]',
 256, 'starter', 'medium',
 'evolution,whatsapp,api,rest,mensajes,integracion,chatwoot,n8n',
 'https://evolution-api.com/',
 '1. Define una API Key de autenticación segura\n2. Despliega Evolution API\n3. Accede al panel web para vincular tu número de WhatsApp\n4. Escanea el QR con tu teléfono\n5. Usa la API REST para enviar/recibir mensajes',
 1, 1, 11, 1, '1.0.0'),

-- 12. WAHA (WhatsApp HTTP API)
('waha', 'WAHA — WhatsApp HTTP API',
 'Solución lista para usar que expone funciones de WhatsApp mediante API REST. Facilita la creación de bots en cualquier lenguaje. Sesiones múltiples, webhooks, envío de medios, estados y gestión de contactos.',
 'API HTTP para WhatsApp, lista para usar.',
 'whatsapp', 'communication', '💚',
 'devlikeapro/waha:latest', NULL, 'main', NULL,
 '{"WHATSAPP_DEFAULT_ENGINE": "WEBJS", "WAHA_DASHBOARD_ENABLED": "true", "WAHA_DASHBOARD_USERNAME": "admin", "WAHA_DASHBOARD_PASSWORD": ""}',
 '[{"key": "WAHA_DASHBOARD_PASSWORD", "label": "Contraseña del panel WAHA", "placeholder": "contraseña_segura", "required": true}]',
 256, 'starter', 'easy',
 'waha,whatsapp,http,api,bot,sesiones,webhooks',
 'https://waha.devlikeapro.com/',
 '1. Define una contraseña para el panel\n2. Despliega WAHA\n3. Accede al dashboard web\n4. Inicia una sesión y escanea el QR\n5. Conecta tus aplicaciones mediante la API REST',
 1, 1, 12, 1, '1.0.0'),

-- 13. Matrix-nio
('matrix-nio', 'Matrix-nio — Bots para Matrix',
 'Biblioteca Python de alto nivel para el protocolo Matrix, red descentralizada centrada en seguridad y soberanía del usuario. Ideal para construir bots de chat cifrados end-to-end en la red Matrix.',
 'Framework Python para bots en Matrix (E2EE).',
 'matrix', 'communication', '🟢',
 'python:3.11-slim', NULL, 'main', NULL,
 '{"MATRIX_HOMESERVER": "", "MATRIX_USER_ID": "", "MATRIX_PASSWORD": "", "MATRIX_ROOM_ID": ""}',
 '[{"key": "MATRIX_HOMESERVER", "label": "URL del homeserver Matrix", "placeholder": "https://matrix.org", "required": true}, {"key": "MATRIX_USER_ID", "label": "ID de usuario Matrix", "placeholder": "@bot:matrix.org", "required": true}, {"key": "MATRIX_PASSWORD", "label": "Contraseña del bot", "placeholder": "contraseña_del_bot", "required": true}]',
 128, 'free', 'medium',
 'matrix,nio,python,descentralizado,e2ee,cifrado,chat',
 'https://github.com/poljar/matrix-nio',
 '1. Crea una cuenta de bot en tu homeserver Matrix\n2. Obtén el ID de usuario y contraseña\n3. Identifica la sala (room) donde operará el bot\n4. Sube tu código de bot que use matrix-nio\n5. Despliega el contenedor',
 0, 1, 13, 1, '1.0.0'),

-- 14. python-telegram-bot
('python-telegram-bot', 'python-telegram-bot — Framework Telegram',
 'Herramienta de referencia para desarrolladores Python que buscan interactuar con la API de Telegram de manera asíncrona y eficiente. Soporte completo de la Bot API, handlers, filtros y conversaciones.',
 'Framework Python de referencia para bots Telegram.',
 'telegram', 'communication', '🐍',
 'python:3.11-slim', NULL, 'main', NULL,
 '{"TELEGRAM_BOT_TOKEN": ""}',
 '[{"key": "TELEGRAM_BOT_TOKEN", "label": "Token del Bot de Telegram", "placeholder": "123456:ABC-DEF...", "required": true}]',
 128, 'free', 'easy',
 'telegram,python,bot,framework,asincrono,handlers',
 'https://python-telegram-bot.org/',
 '1. Crea un bot con @BotFather en Telegram\n2. Copia el token\n3. Sube tu código Python que use python-telegram-bot\n4. Configura el token\n5. Despliega y tu bot estará online',
 0, 1, 14, 1, '1.0.0'),

-- 15. grammY
('grammy', 'grammY — Framework Telegram Moderno',
 'Marco de trabajo moderno y extremadamente rápido para Telegram, diseñado con TypeScript. Experiencia de desarrollo superior con tipos completos, plugins extensibles y escalabilidad horizontal.',
 'Framework TypeScript moderno y rápido para Telegram.',
 'telegram', 'communication', '🎸',
 'node:20-alpine', NULL, 'main', NULL,
 '{"TELEGRAM_BOT_TOKEN": ""}',
 '[{"key": "TELEGRAM_BOT_TOKEN", "label": "Token del Bot de Telegram", "placeholder": "123456:ABC-DEF...", "required": true}]',
 128, 'free', 'easy',
 'telegram,grammy,typescript,nodejs,moderno,rapido,plugins',
 'https://grammy.dev/',
 '1. Crea un bot con @BotFather en Telegram\n2. Copia el token\n3. Sube tu código TypeScript/JS que use grammY\n4. Configura el token\n5. Despliega y tu bot estará online',
 0, 1, 15, 1, '1.0.0'),

-- 16. Telegraf
('telegraf', 'Telegraf — Framework Telegram Node.js',
 'Framework de Node.js para bots Telegram con sistema de middleware modular. Facilita la extensión de funcionalidades, manejo de sesiones, escenas y soporte completo de la Bot API.',
 'Framework Node.js con middleware para Telegram.',
 'telegram', 'communication', '📡',
 'node:20-alpine', NULL, 'main', NULL,
 '{"TELEGRAM_BOT_TOKEN": ""}',
 '[{"key": "TELEGRAM_BOT_TOKEN", "label": "Token del Bot de Telegram", "placeholder": "123456:ABC-DEF...", "required": true}]',
 128, 'free', 'easy',
 'telegram,telegraf,nodejs,middleware,modular,sesiones',
 'https://telegraf.js.org/',
 '1. Crea un bot con @BotFather en Telegram\n2. Copia el token\n3. Sube tu código Node.js que use Telegraf\n4. Configura el token\n5. Despliega y tu bot estará online',
 0, 1, 16, 1, '1.0.0'),

-- 17. Evolution API Lite
('evolution-api-lite', 'Evolution API Lite — WhatsApp Ligero',
 'Versión optimizada y de bajo consumo de Evolution API, ideal para servidores con recursos limitados. Funcionalidad esencial de WhatsApp API con menor huella de memoria.',
 'WhatsApp API ligera para servidores modestos.',
 'whatsapp', 'communication', '🪶',
 'node:20-alpine', 'https://github.com/EvolutionAPI/evolution-api-lite', 'main', NULL,
 '{"AUTHENTICATION_API_KEY": "", "SERVER_PORT": "8080"}',
 '[{"key": "AUTHENTICATION_API_KEY", "label": "API Key de autenticación", "placeholder": "tu_api_key_segura", "required": true}]',
 128, 'free', 'easy',
 'evolution,whatsapp,api,lite,ligero,bajo-consumo',
 'https://github.com/EvolutionAPI/evolution-api-lite',
 '1. Define una API Key de autenticación\n2. Despliega Evolution API Lite\n3. Vincula tu número de WhatsApp escaneando el QR\n4. Usa la API REST para enviar/recibir mensajes\n5. Menor consumo de recursos que la versión completa',
 0, 1, 17, 1, '1.0.0'),

-- 18. whatsapp-web.js (wwebjs)
('wwebjs', 'whatsapp-web.js — WhatsApp vía Navegador',
 'Biblioteca Node.js que emula un navegador para interactuar con WhatsApp Web. Automatización sin depender de la API oficial para empresas. Envío de mensajes, medios, gestión de contactos y grupos.',
 'Automatiza WhatsApp emulando el navegador web.',
 'whatsapp', 'communication', '🌐',
 'node:20-alpine', NULL, 'main', NULL,
 '{"SESSION_NAME": "lanzabot", "HEADLESS": "true"}',
 '[]',
 256, 'starter', 'medium',
 'whatsapp,wwebjs,navegador,automatizacion,nodejs,puppeteer',
 'https://wwebjs.dev/',
 '1. Sube tu código Node.js que use whatsapp-web.js\n2. Despliega el contenedor\n3. El bot generará un QR en los logs\n4. Escanea el QR con tu teléfono\n5. El bot estará conectado a tu WhatsApp',
 0, 1, 18, 1, '1.0.0'),

-- 19. Telebot (Go)
('telebot-go', 'Telebot (Go) — Framework Telegram en Go',
 'Marco de trabajo para bots de Telegram escrito en Go, diseñado para aplicaciones que requieren alto rendimiento y concurrencia. Binario compilado con mínimo consumo de recursos.',
 'Framework Telegram en Go: alto rendimiento y concurrencia.',
 'telegram', 'communication', '🐹',
 'golang:1.22-alpine', NULL, 'main', NULL,
 '{"TELEGRAM_BOT_TOKEN": ""}',
 '[{"key": "TELEGRAM_BOT_TOKEN", "label": "Token del Bot de Telegram", "placeholder": "123456:ABC-DEF...", "required": true}]',
 64, 'free', 'medium',
 'telegram,go,golang,rendimiento,concurrencia,compilado',
 'https://github.com/tucnak/telebot',
 '1. Crea un bot con @BotFather en Telegram\n2. Copia el token\n3. Sube tu código Go que use telebot\n4. Configura el token\n5. Despliega — Go se compila automáticamente',
 0, 1, 19, 1, '1.0.0'),

-- 20. Matrix-WhatsApp Bridge
('matrix-whatsapp-bridge', 'Matrix-WhatsApp Bridge',
 'Bot especializado que conecta las redes Matrix y WhatsApp, permitiendo que usuarios de Matrix se comuniquen con contactos de WhatsApp de forma nativa. Sincronización de mensajes, medios y grupos.',
 'Puente entre Matrix y WhatsApp bidireccional.',
 'matrix', 'communication', '🌉',
 'dock.mau.dev/mautrix/whatsapp:latest', NULL, 'main', NULL,
 '{"MAUTRIX_WHATSAPP_HOMESERVER_ADDRESS": "", "MAUTRIX_WHATSAPP_HOMESERVER_DOMAIN": "", "MAUTRIX_WHATSAPP_APPSERVICE_ADDRESS": "http://localhost:29318"}',
 '[{"key": "MAUTRIX_WHATSAPP_HOMESERVER_ADDRESS", "label": "URL del homeserver Matrix", "placeholder": "https://matrix.tuservidor.com", "required": true}, {"key": "MAUTRIX_WHATSAPP_HOMESERVER_DOMAIN", "label": "Dominio del homeserver", "placeholder": "tuservidor.com", "required": true}]',
 256, 'starter', 'advanced',
 'matrix,whatsapp,bridge,puente,sincronizacion,mensajes',
 'https://github.com/mautrix/whatsapp',
 '1. Ten un homeserver Matrix funcionando\n2. Configura la dirección y dominio del homeserver\n3. Despliega el bridge\n4. Registra el appservice en tu homeserver\n5. Invita al bot a una sala Matrix y vincula tu WhatsApp',
 0, 1, 20, 1, '1.0.0'),

-- =============================================
-- CATEGORÍA 3: FINANZAS Y COMERCIO (21-30)
-- =============================================

-- 21. Freqtrade
('freqtrade', 'Freqtrade — Trading Bot Crypto',
 'Bot de trading de criptomonedas altamente configurable. Backtesting, optimización de estrategias con IA y control de operaciones desde Telegram. Soporta múltiples exchanges: Binance, Kraken, OKX, etc.',
 'Trading automático de crypto con control desde Telegram.',
 'telegram', 'finance', '📈',
 'freqtradeorg/freqtrade:stable', NULL, 'main', NULL,
 '{"FREQTRADE__EXCHANGE__NAME": "binance", "FREQTRADE__EXCHANGE__KEY": "", "FREQTRADE__EXCHANGE__SECRET": "", "FREQTRADE__TELEGRAM__ENABLED": "true", "FREQTRADE__TELEGRAM__TOKEN": "", "FREQTRADE__TELEGRAM__CHAT_ID": ""}',
 '[{"key": "FREQTRADE__EXCHANGE__KEY", "label": "API Key del Exchange", "placeholder": "tu_api_key", "required": true}, {"key": "FREQTRADE__EXCHANGE__SECRET", "label": "API Secret del Exchange", "placeholder": "tu_api_secret", "required": true}, {"key": "FREQTRADE__TELEGRAM__TOKEN", "label": "Token Bot Telegram (para control)", "placeholder": "123456:ABC...", "required": false}, {"key": "FREQTRADE__TELEGRAM__CHAT_ID", "label": "Chat ID de Telegram", "placeholder": "123456789", "required": false}]',
 512, 'medium', 'advanced',
 'freqtrade,trading,crypto,bitcoin,backtesting,estrategias,binance',
 'https://www.freqtrade.io/',
 '1. Crea API keys en tu exchange (Binance, Kraken, etc.)\n2. Opcionalmente crea un bot en Telegram para control remoto\n3. Configura las credenciales del exchange\n4. Despliega Freqtrade\n5. Accede al panel web para gestionar estrategias y operaciones',
 1, 1, 21, 1, '1.0.0'),

-- 22. Hummingbot
('hummingbot', 'Hummingbot — Market Making y Arbitraje',
 'Herramienta especializada en market making y arbitraje. Ejecuta estrategias profesionales en más de 40 exchanges. Interfaz de línea de comandos y scripts personalizables en Python.',
 'Market making y arbitraje profesional multi-exchange.',
 'multi', 'finance', '🐦',
 'hummingbot/hummingbot:latest', NULL, 'main', NULL,
 '{"EXCHANGE": "binance", "API_KEY": "", "API_SECRET": ""}',
 '[{"key": "API_KEY", "label": "API Key del Exchange", "placeholder": "tu_api_key", "required": true}, {"key": "API_SECRET", "label": "API Secret del Exchange", "placeholder": "tu_api_secret", "required": true}]',
 512, 'medium', 'advanced',
 'hummingbot,market-making,arbitraje,trading,exchange,profesional',
 'https://hummingbot.org/',
 '1. Crea API keys en tu exchange preferido\n2. Configura las credenciales\n3. Despliega Hummingbot\n4. Conecta al contenedor y configura la estrategia\n5. Inicia el bot y monitorea las operaciones',
 0, 1, 22, 1, '1.0.0'),

-- 23. FinRL
('finrl', 'FinRL — Trading con IA (Deep RL)',
 'Biblioteca de aprendizaje por refuerzo profundo para trading financiero. Entrena agentes inteligentes en mercados complejos. Backtesting robusto y soporte para múltiples fuentes de datos de mercado.',
 'Entrena agentes de trading con aprendizaje por refuerzo.',
 'multi', 'finance', '🧠',
 'python:3.11-slim', 'https://github.com/AI4Finance-Foundation/FinRL', 'main', 'pip install finrl',
 '{"ALPACA_API_KEY": "", "ALPACA_API_SECRET": "", "ALPACA_API_BASE_URL": "https://paper-api.alpaca.markets"}',
 '[{"key": "ALPACA_API_KEY", "label": "API Key de Alpaca (u otro broker)", "placeholder": "PK...", "required": true}, {"key": "ALPACA_API_SECRET", "label": "API Secret de Alpaca", "placeholder": "tu_secret", "required": true}]',
 1024, 'medium', 'advanced',
 'finrl,deep-learning,reinforcement,trading,ia,mercados,python',
 'https://github.com/AI4Finance-Foundation/FinRL',
 '1. Crea una cuenta en Alpaca Markets (paper trading gratuito)\n2. Obtén tus API keys\n3. Configura las credenciales\n4. Despliega FinRL\n5. Entrena y ejecuta agentes de trading con IA',
 0, 1, 23, 1, '1.0.0'),

-- 24. Jesse
('jesse', 'Jesse — Algoritmo de Trading Avanzado',
 'Algoritmo de trading avanzado en Python enfocado en robustez de estrategias y precisión de datos de mercado durante backtesting. Motor de ejecución optimizado y sistema de indicadores técnicos completo.',
 'Trading algorítmico con backtesting preciso en Python.',
 'multi', 'finance', '📊',
 'python:3.11-slim', 'https://github.com/jesse-ai/jesse', 'main', 'pip install jesse',
 '{"EXCHANGE": "binance", "EXCHANGE_API_KEY": "", "EXCHANGE_API_SECRET": ""}',
 '[{"key": "EXCHANGE_API_KEY", "label": "API Key del Exchange", "placeholder": "tu_api_key", "required": true}, {"key": "EXCHANGE_API_SECRET", "label": "API Secret del Exchange", "placeholder": "tu_api_secret", "required": true}]',
 512, 'medium', 'advanced',
 'jesse,trading,algoritmo,backtesting,indicadores,python',
 'https://jesse.ai/',
 '1. Crea API keys en tu exchange\n2. Configura las credenciales\n3. Despliega Jesse\n4. Accede al panel web para diseñar estrategias\n5. Ejecuta backtesting antes de operar en vivo',
 0, 1, 24, 1, '1.0.0'),

-- 25. Superalgos
('superalgos', 'Superalgos — Trading Visual Sin Código',
 'Plataforma visual para diseño de estrategias de trading sin necesidad de programación. Construye sistemas complejos de automatización financiera con interfaz gráfica. Comunidad activa con estrategias compartidas.',
 'Diseña estrategias de trading visualmente sin código.',
 'multi', 'finance', '🎯',
 'node:20-alpine', 'https://github.com/Superalgos/Superalgos', 'main', NULL,
 '{"EXCHANGE": "binance", "EXCHANGE_API_KEY": "", "EXCHANGE_API_SECRET": ""}',
 '[{"key": "EXCHANGE_API_KEY", "label": "API Key del Exchange", "placeholder": "tu_api_key", "required": true}, {"key": "EXCHANGE_API_SECRET", "label": "API Secret del Exchange", "placeholder": "tu_api_secret", "required": true}]',
 1024, 'medium', 'medium',
 'superalgos,trading,visual,nocode,estrategias,comunidad',
 'https://superalgos.org/',
 '1. Crea API keys en tu exchange (Binance recomendado)\n2. Configura las credenciales\n3. Despliega Superalgos\n4. Accede a la interfaz web gráfica\n5. Diseña estrategias arrastrando componentes',
 0, 1, 25, 1, '1.0.0'),

-- 26. OctoBot
('octobot', 'OctoBot — Trading Intuitivo con IA',
 'Bot de trading intuitivo con modos para principiantes y expertos. Estrategias de IA, Grid y DCA con interfaz web moderna. Soporta múltiples exchanges y control vía Telegram.',
 'Trading con IA: Grid, DCA y más, para todos los niveles.',
 'telegram', 'finance', '🐙',
 'drakkarsoftware/octobot:stable', NULL, 'main', NULL,
 '{"EXCHANGE": "binance", "EXCHANGE_API_KEY": "", "EXCHANGE_API_SECRET": "", "TELEGRAM_BOT_TOKEN": "", "TELEGRAM_CHAT_ID": ""}',
 '[{"key": "EXCHANGE_API_KEY", "label": "API Key del Exchange", "placeholder": "tu_api_key", "required": true}, {"key": "EXCHANGE_API_SECRET", "label": "API Secret del Exchange", "placeholder": "tu_api_secret", "required": true}, {"key": "TELEGRAM_BOT_TOKEN", "label": "Token Bot Telegram (opcional)", "placeholder": "123456:ABC...", "required": false}]',
 512, 'medium', 'medium',
 'octobot,trading,ia,grid,dca,principiantes,telegram',
 'https://www.octobot.cloud/',
 '1. Crea API keys en tu exchange\n2. Opcionalmente crea un bot en Telegram\n3. Configura las credenciales\n4. Despliega OctoBot\n5. Accede al panel web para elegir y configurar estrategias',
 1, 1, 26, 1, '1.0.0'),

-- 27. Labubu-bot
('labubu-bot', 'Labubu-bot — Automatización de Compras',
 'Bot de automatización de compras diseñado para asegurar artículos de edición limitada en plataformas de e-commerce mediante técnicas de anti-detección. Monitoreo de stock y compra automática.',
 'Automatiza compras de artículos limitados.',
 'multi', 'finance', '🛍️',
 'python:3.11-slim', 'https://github.com/Decodo/Labubu-bot', 'main', NULL,
 '{"TARGET_URL": "", "PROXY_URL": ""}',
 '[{"key": "TARGET_URL", "label": "URL del producto objetivo", "placeholder": "https://tienda.com/producto", "required": true}]',
 256, 'starter', 'medium',
 'labubu,compras,automatizacion,ecommerce,stock,limitado',
 'https://github.com/Decodo/Labubu-bot',
 '1. Identifica el producto que quieres monitorear\n2. Configura la URL del producto\n3. Opcionalmente configura un proxy\n4. Despliega el bot\n5. El bot monitoreará el stock y comprará automáticamente',
 0, 1, 27, 1, '1.0.0'),

-- 28. Medusa
('medusa', 'Medusa — E-Commerce Headless',
 'Plataforma de comercio headless que permite desplegarla como backend automatizado para gestionar pedidos y catálogos de forma programática. API-first, extensible con plugins y módulos.',
 'Backend de e-commerce headless y automatizable.',
 'multi', 'finance', '🛒',
 'medusajs/medusa:latest', NULL, 'main', NULL,
 '{"MEDUSA_ADMIN_EMAIL": "admin@example.com", "MEDUSA_ADMIN_PASSWORD": "", "DATABASE_URL": "", "JWT_SECRET": "", "COOKIE_SECRET": ""}',
 '[{"key": "MEDUSA_ADMIN_PASSWORD", "label": "Contraseña del admin", "placeholder": "contraseña_segura", "required": true}, {"key": "JWT_SECRET", "label": "JWT Secret (aleatorio)", "placeholder": "jwt_secret_aleatorio", "required": true}, {"key": "COOKIE_SECRET", "label": "Cookie Secret (aleatorio)", "placeholder": "cookie_secret_aleatorio", "required": true}]',
 512, 'medium', 'medium',
 'medusa,ecommerce,headless,api,pedidos,catalogo,tienda',
 'https://medusajs.com/',
 '1. Define credenciales de admin y secrets aleatorios\n2. Necesitarás una base de datos PostgreSQL\n3. Despliega Medusa\n4. Accede al panel de administración\n5. Configura productos, precios y canales de venta',
 0, 1, 28, 1, '1.0.0'),

-- 29. Bagisto
('bagisto', 'Bagisto — Marketplace Laravel',
 'Sistema de e-commerce basado en Laravel. Facilita la creación de marketplaces automatizados y gestión de múltiples inventarios. Panel de admin completo, pasarelas de pago y multi-idioma.',
 'Marketplace e-commerce completo basado en Laravel.',
 'multi', 'finance', '🏪',
 'node:20-alpine', 'https://github.com/bagisto/bagisto', 'main', NULL,
 '{"APP_URL": "", "DB_HOST": "localhost", "DB_DATABASE": "bagisto", "DB_USERNAME": "", "DB_PASSWORD": ""}',
 '[{"key": "APP_URL", "label": "URL de tu tienda", "placeholder": "https://tienda.tudominio.com", "required": true}, {"key": "DB_USERNAME", "label": "Usuario de base de datos", "placeholder": "bagisto_user", "required": true}, {"key": "DB_PASSWORD", "label": "Contraseña de base de datos", "placeholder": "contraseña_bd", "required": true}]',
 512, 'medium', 'advanced',
 'bagisto,laravel,marketplace,ecommerce,inventario,tienda',
 'https://bagisto.com/',
 '1. Necesitarás una base de datos MySQL/MariaDB\n2. Configura la URL y credenciales de BD\n3. Despliega Bagisto\n4. Ejecuta el asistente de instalación web\n5. Configura productos, categorías y pasarelas de pago',
 0, 1, 29, 1, '1.0.0'),

-- 30. Saleor
('saleor', 'Saleor — E-Commerce Python + GraphQL',
 'Plataforma de e-commerce de alto rendimiento construida con Python y GraphQL. Ideal para integraciones con bots de atención al cliente y gestión de ventas. Dashboard moderno y API GraphQL potente.',
 'E-commerce alto rendimiento con Python y GraphQL.',
 'multi', 'finance', '🎨',
 'ghcr.io/saleor/saleor:latest', NULL, 'main', NULL,
 '{"SECRET_KEY": "", "DATABASE_URL": "", "ALLOWED_HOSTS": "*", "DEFAULT_CURRENCY": "EUR"}',
 '[{"key": "SECRET_KEY", "label": "Clave secreta Django", "placeholder": "clave_aleatoria_larga", "required": true}, {"key": "DATABASE_URL", "label": "URL de PostgreSQL", "placeholder": "postgres://user:pass@host/db", "required": true}]',
 512, 'medium', 'advanced',
 'saleor,ecommerce,python,graphql,dashboard,tienda,api',
 'https://saleor.io/',
 '1. Necesitarás PostgreSQL y Redis\n2. Genera una clave secreta aleatoria\n3. Configura la URL de la base de datos\n4. Despliega Saleor\n5. Accede al dashboard de administración',
 0, 1, 30, 1, '1.0.0'),

-- =============================================
-- CATEGORÍA 4: MODERACIÓN Y SEGURIDAD (31-40)
-- =============================================

-- 31. Skyra
('skyra', 'Skyra — Discord Bot Multipropósito',
 'Bot de Discord multipropósito conocido por estabilidad y potencia. Moderación avanzada, sistema de niveles, entretenimiento, logs detallados, sistema de economía virtual y configuración granular por servidor.',
 'Bot Discord multipropósito: moderación, niveles y más.',
 'discord', 'moderation', '🌟',
 'node:20-alpine', 'https://github.com/skyra-project/skyra', 'main', NULL,
 '{"DISCORD_TOKEN": "", "PREFIX": "!"}',
 '[{"key": "DISCORD_TOKEN", "label": "Token del Bot de Discord", "placeholder": "MTIzNDU2...", "required": true}]',
 256, 'starter', 'medium',
 'skyra,discord,moderacion,niveles,entretenimiento,multiproposito',
 'https://skyra.pw/',
 '1. Crea una app y bot en Discord Developer Portal\n2. Activa todos los intents privilegiados\n3. Invita al bot con permisos de administrador\n4. Configura el token\n5. Despliega y usa !help para ver comandos disponibles',
 1, 1, 31, 1, '1.0.0'),

-- 32. Vortex
('vortex', 'Vortex — Moderación y Anti-Raid Discord',
 'Bot de moderación para Discord centrado en eficiencia y seguridad. Logs detallados, herramientas de protección contra incursiones (anti-raid), auto-moderación configurable y sistema de strikes.',
 'Moderación eficiente y protección anti-raid para Discord.',
 'discord', 'moderation', '🌀',
 'node:20-alpine', 'https://github.com/jagrosh/Vortex', 'main', NULL,
 '{"DISCORD_TOKEN": "", "OWNER_ID": ""}',
 '[{"key": "DISCORD_TOKEN", "label": "Token del Bot de Discord", "placeholder": "MTIzNDU2...", "required": true}, {"key": "OWNER_ID", "label": "Tu ID de usuario Discord", "placeholder": "123456789012345678", "required": true}]',
 256, 'starter', 'medium',
 'vortex,discord,moderacion,antiraid,seguridad,logs,strikes',
 'https://github.com/jagrosh/Vortex',
 '1. Crea una app y bot en Discord Developer Portal\n2. Activa todos los intents privilegiados\n3. Obtén tu ID de usuario Discord (Modo Desarrollador)\n4. Invita al bot con permisos de administrador\n5. Configura el token y despliega',
 0, 1, 32, 1, '1.0.0'),

-- 33. ModBot
('modbot', 'ModBot — Moderación Moderna Discord',
 'Herramienta de moderación open source para Discord que aprovecha las últimas funciones de la API: botones, menús contextuales y slash commands. Gestión fluida de infracciones, mutes temporales y bans.',
 'Moderación moderna con botones y menús en Discord.',
 'discord', 'moderation', '🔨',
 'node:20-alpine', 'https://github.com/aternosorg/modbot', 'main', NULL,
 '{"DISCORD_TOKEN": "", "DATABASE_URL": ""}',
 '[{"key": "DISCORD_TOKEN", "label": "Token del Bot de Discord", "placeholder": "MTIzNDU2...", "required": true}]',
 256, 'starter', 'easy',
 'modbot,discord,moderacion,slash-commands,botones,aternos',
 'https://github.com/aternosorg/modbot',
 '1. Crea una app y bot en Discord Developer Portal\n2. Activa intents: Server Members, Message Content\n3. Invita al bot con permisos de moderación\n4. Configura el token\n5. Despliega y usa /setup para configurar',
 0, 1, 33, 1, '1.0.0'),

-- 34. Draupnir
('draupnir', 'Draupnir — Gestión de Comunidades Matrix',
 'Plataforma de gestión de comunidades para Matrix. Protege salas contra abuso mediante listas de baneo compartidas (ban lists). Moderación colaborativa entre múltiples servidores Matrix.',
 'Protección contra abuso para comunidades Matrix.',
 'matrix', 'moderation', '💍',
 'node:20-alpine', 'https://github.com/the-draupnir-project/Draupnir', 'main', NULL,
 '{"HOMESERVER_URL": "", "ACCESS_TOKEN": "", "MANAGEMENT_ROOM": ""}',
 '[{"key": "HOMESERVER_URL", "label": "URL del homeserver Matrix", "placeholder": "https://matrix.tuservidor.com", "required": true}, {"key": "ACCESS_TOKEN", "label": "Access Token del bot", "placeholder": "syt_...", "required": true}, {"key": "MANAGEMENT_ROOM", "label": "ID de la sala de gestión", "placeholder": "!abc123:matrix.org", "required": true}]',
 256, 'starter', 'advanced',
 'draupnir,matrix,moderacion,ban-list,abuso,comunidad',
 'https://github.com/the-draupnir-project/Draupnir',
 '1. Crea una cuenta de bot en tu homeserver Matrix\n2. Obtén el Access Token del bot\n3. Crea una sala de gestión para comandos\n4. Configura las credenciales\n5. Despliega y el bot protegerá tus salas automáticamente',
 0, 1, 34, 1, '1.0.0'),

-- 35. Mjolnir
('mjolnir', 'Mjolnir — Admin Bot para Matrix',
 'Bot de administración para Matrix con interfaz de comandos para gestionar moderación y políticas de seguridad en salas de chat. Soporte para ban lists, protecciones automáticas y auditoría.',
 'Administración y moderación avanzada para Matrix.',
 'matrix', 'moderation', '🔱',
 'node:20-alpine', 'https://github.com/matrix-org/mjolnir', 'main', NULL,
 '{"HOMESERVER_URL": "", "ACCESS_TOKEN": "", "MANAGEMENT_ROOM": ""}',
 '[{"key": "HOMESERVER_URL", "label": "URL del homeserver Matrix", "placeholder": "https://matrix.tuservidor.com", "required": true}, {"key": "ACCESS_TOKEN", "label": "Access Token del bot", "placeholder": "syt_...", "required": true}, {"key": "MANAGEMENT_ROOM", "label": "ID de la sala de gestión", "placeholder": "!abc123:matrix.org", "required": true}]',
 256, 'starter', 'advanced',
 'mjolnir,matrix,administracion,ban-list,seguridad,moderacion',
 'https://github.com/matrix-org/mjolnir',
 '1. Crea una cuenta de bot en tu homeserver Matrix\n2. Obtén el Access Token del bot\n3. Crea una sala de gestión privada\n4. Invita al bot a la sala\n5. Despliega y gestiona la moderación con comandos',
 0, 1, 35, 1, '1.0.0'),

-- 36. Group Butler
('group-butler', 'Group Butler — Gestión de Grupos Telegram',
 'Bot veterano de Telegram para gestión de grupos. Reglas automáticas, respuestas inteligentes, anti-spam, mensajes de bienvenida, advertencias y configuración granular por grupo.',
 'Gestión automática de grupos Telegram con reglas y anti-spam.',
 'telegram', 'moderation', '🤵',
 'python:3.11-slim', 'https://github.com/group-butler/GroupButler', 'main', NULL,
 '{"TELEGRAM_BOT_TOKEN": "", "ADMIN_CHAT_ID": ""}',
 '[{"key": "TELEGRAM_BOT_TOKEN", "label": "Token del Bot de Telegram", "placeholder": "123456:ABC-DEF...", "required": true}]',
 128, 'free', 'easy',
 'group-butler,telegram,grupos,moderacion,antispam,bienvenida,reglas',
 'https://github.com/group-butler/GroupButler',
 '1. Crea un bot con @BotFather en Telegram\n2. Configura el token\n3. Despliega Group Butler\n4. Añade el bot como admin a tus grupos\n5. Usa /help para configurar reglas y respuestas automáticas',
 1, 1, 36, 1, '1.0.0'),

-- 37. Red-DiscordBot
('red-discordbot', 'Red — Discord Bot Modular',
 'Bot modular para Discord donde los usuarios instalan sus propios complementos (cogs). Personalización total de funciones: moderación, música, juegos, utilidades, y miles de cogs de la comunidad.',
 'Bot Discord modular con miles de plugins (cogs).',
 'discord', 'moderation', '🔴',
 'phasecorex/red-discordbot:latest', NULL, 'main', NULL,
 '{"TOKEN": "", "PREFIX": "!", "OWNER_ID": ""}',
 '[{"key": "TOKEN", "label": "Token del Bot de Discord", "placeholder": "MTIzNDU2...", "required": true}, {"key": "OWNER_ID", "label": "Tu ID de usuario Discord", "placeholder": "123456789012345678", "required": true}]',
 512, 'medium', 'medium',
 'red,discordbot,modular,cogs,plugins,comunidad,personalizable',
 'https://github.com/Cog-Creators/Red-DiscordBot',
 '1. Crea una app y bot en Discord Developer Portal\n2. Activa todos los intents privilegiados\n3. Obtén tu ID de usuario Discord\n4. Invita al bot con permisos de administrador\n5. Despliega y usa [p]cog install para añadir módulos',
 1, 1, 37, 1, '1.0.0'),

-- 38. Discord-Tickets
('discord-tickets', 'Discord Tickets — Sistema de Soporte',
 'Sistema de soporte mediante tickets para Discord. Organiza solicitudes de usuarios en canales privados gestionados por el equipo de moderación. Categorías, prioridades, transcripciones y estadísticas.',
 'Sistema de tickets de soporte profesional para Discord.',
 'discord', 'moderation', '🎫',
 'node:20-alpine', 'https://github.com/discord-tickets/bot', 'main', NULL,
 '{"DISCORD_TOKEN": "", "DB_PROVIDER": "sqlite", "PORTAL_URL": ""}',
 '[{"key": "DISCORD_TOKEN", "label": "Token del Bot de Discord", "placeholder": "MTIzNDU2...", "required": true}]',
 256, 'starter', 'easy',
 'discord,tickets,soporte,helpdesk,canales,moderacion',
 'https://discord-tickets.com/',
 '1. Crea una app y bot en Discord Developer Portal\n2. Activa intents: Server Members, Message Content\n3. Invita al bot con permisos de gestión de canales\n4. Configura el token\n5. Despliega y usa /setup para crear categorías de tickets',
 1, 1, 38, 1, '1.0.0'),

-- 39. TwirApp
('twirapp', 'TwirApp — Bot Completo para Twitch',
 'Bot de Twitch completo con panel de control web, sistemas de lealtad, automatización de eventos, comandos personalizados, timers y alertas. Mejora la interacción en streams.',
 'Bot Twitch con panel web, lealtad y eventos.',
 'twitch', 'moderation', '🎮',
 'node:20-alpine', 'https://github.com/twirapp/twir', 'main', NULL,
 '{"TWITCH_CLIENT_ID": "", "TWITCH_CLIENT_SECRET": "", "TWITCH_BOT_USERNAME": "", "TWITCH_BOT_ACCESS_TOKEN": ""}',
 '[{"key": "TWITCH_CLIENT_ID", "label": "Client ID de Twitch", "placeholder": "tu_client_id", "required": true}, {"key": "TWITCH_CLIENT_SECRET", "label": "Client Secret de Twitch", "placeholder": "tu_client_secret", "required": true}, {"key": "TWITCH_BOT_ACCESS_TOKEN", "label": "Access Token del Bot", "placeholder": "oauth:...", "required": true}]',
 512, 'medium', 'medium',
 'twirapp,twitch,streaming,lealtad,eventos,comandos,panel',
 'https://twir.app/',
 '1. Registra una app en Twitch Developer Console\n2. Obtén Client ID y Secret\n3. Genera un Access Token para la cuenta del bot\n4. Configura las credenciales\n5. Despliega y accede al panel web para personalizar',
 0, 1, 39, 1, '1.0.0'),

-- 40. EngelGuard
('engelguard', 'EngelGuard — Moderación Twitch Open Source',
 'Bot de moderación profesional para Twitch. Alternativa open source y autohospedada a soluciones comerciales. Filtros de chat, timeouts automáticos, protección contra spam y herramientas de moderación.',
 'Moderación profesional open source para Twitch.',
 'twitch', 'moderation', '👼',
 'node:20-alpine', 'https://github.com/Luca-Pelzer/engelguard', 'main', NULL,
 '{"TWITCH_CLIENT_ID": "", "TWITCH_CLIENT_SECRET": "", "TWITCH_BOT_TOKEN": "", "TWITCH_CHANNEL": ""}',
 '[{"key": "TWITCH_CLIENT_ID", "label": "Client ID de Twitch", "placeholder": "tu_client_id", "required": true}, {"key": "TWITCH_CLIENT_SECRET", "label": "Client Secret de Twitch", "placeholder": "tu_client_secret", "required": true}, {"key": "TWITCH_CHANNEL", "label": "Canal de Twitch", "placeholder": "tu_canal", "required": true}]',
 128, 'free', 'easy',
 'engelguard,twitch,moderacion,opensource,spam,filtros,chat',
 'https://github.com/Luca-Pelzer/engelguard',
 '1. Registra una app en Twitch Developer Console\n2. Obtén Client ID y Secret\n3. Indica tu canal de Twitch\n4. Configura las credenciales\n5. Despliega y el bot moderará tu chat automáticamente',
 0, 1, 40, 1, '1.0.0'),

-- =============================================
-- CATEGORÍA 5: MARKETING Y DESARROLLO (41-50)
-- =============================================

-- 41. Mautic
('mautic', 'Mautic — Automatización de Marketing',
 'Plataforma de automatización de marketing open source más importante del mundo. Gestiona campañas de email, redes sociales, segmentación de clientes, landing pages y lead scoring.',
 'Marketing automation open source: email, redes y leads.',
 'multi', 'marketing', '📧',
 'mautic/mautic:latest', NULL, 'main', NULL,
 '{"MAUTIC_DB_HOST": "", "MAUTIC_DB_NAME": "mautic", "MAUTIC_DB_USER": "", "MAUTIC_DB_PASSWORD": "", "MAUTIC_ADMIN_EMAIL": "", "MAUTIC_ADMIN_PASSWORD": ""}',
 '[{"key": "MAUTIC_DB_HOST", "label": "Host de la base de datos", "placeholder": "mysql-host", "required": true}, {"key": "MAUTIC_DB_USER", "label": "Usuario de base de datos", "placeholder": "mautic_user", "required": true}, {"key": "MAUTIC_DB_PASSWORD", "label": "Contraseña de base de datos", "placeholder": "contraseña_bd", "required": true}, {"key": "MAUTIC_ADMIN_EMAIL", "label": "Email del administrador", "placeholder": "admin@tudominio.com", "required": true}, {"key": "MAUTIC_ADMIN_PASSWORD", "label": "Contraseña del administrador", "placeholder": "contraseña_admin", "required": true}]',
 512, 'medium', 'medium',
 'mautic,marketing,email,campañas,leads,segmentacion,automatizacion',
 'https://www.mautic.org/',
 '1. Necesitarás una base de datos MySQL\n2. Configura credenciales de BD y admin\n3. Despliega Mautic\n4. Completa el asistente de instalación web\n5. Crea tu primera campaña de email marketing',
 1, 1, 41, 1, '1.0.0'),

-- 42. yt-dlp
('yt-dlp', 'yt-dlp — Descarga de Multimedia',
 'Herramienta fundamental para descarga de contenido multimedia de miles de sitios web. Frecuentemente usado como motor de bots de descarga en Telegram y Discord. Soporta YouTube, Vimeo, Twitter y cientos más.',
 'Motor de descarga multimedia para bots de Telegram/Discord.',
 'multi', 'marketing', '📥',
 'python:3.11-slim', 'https://github.com/yt-dlp/yt-dlp', 'main', 'pip install yt-dlp',
 '{"TELEGRAM_BOT_TOKEN": "", "DOWNLOAD_PATH": "/downloads", "MAX_FILE_SIZE_MB": "50"}',
 '[{"key": "TELEGRAM_BOT_TOKEN", "label": "Token del Bot (Telegram/Discord)", "placeholder": "123456:ABC-DEF...", "required": false}]',
 256, 'starter', 'easy',
 'yt-dlp,descarga,youtube,multimedia,video,audio,telegram,discord',
 'https://github.com/yt-dlp/yt-dlp',
 '1. Opcionalmente crea un bot en Telegram o Discord\n2. Configura el token del bot\n3. Despliega yt-dlp como servicio\n4. Envía URLs al bot para descargar vídeos/audios\n5. Soporta miles de sitios web',
 0, 1, 42, 1, '1.0.0'),

-- 43. Stickerify
('stickerify', 'Stickerify — Creador de Stickers Telegram',
 'Bot de Telegram que facilita la conversión de imágenes y vídeos al formato de stickers de la plataforma de manera automatizada. Recorte automático, redimensionado y creación de packs.',
 'Convierte imágenes y vídeos en stickers de Telegram.',
 'telegram', 'marketing', '🎭',
 'node:20-alpine', 'https://github.com/Stickerifier/Stickerify', 'main', NULL,
 '{"TELEGRAM_BOT_TOKEN": ""}',
 '[{"key": "TELEGRAM_BOT_TOKEN", "label": "Token del Bot de Telegram", "placeholder": "123456:ABC-DEF...", "required": true}]',
 128, 'free', 'easy',
 'stickerify,telegram,stickers,imagenes,conversion,automatico',
 'https://github.com/Stickerifier/Stickerify',
 '1. Crea un bot con @BotFather en Telegram\n2. Configura el token\n3. Despliega Stickerify\n4. Envía una imagen al bot\n5. Recibirás el sticker listo para usar o añadir a un pack',
 0, 1, 43, 1, '1.0.0'),

-- 44. RedditVideoMakerBot
('reddit-video-maker', 'Reddit Video Maker Bot',
 'Automatiza la creación de vídeos para redes sociales basados en historias populares de Reddit. Integra narración por voz automática (TTS), capturas de pantalla y edición automática.',
 'Crea vídeos automáticos de historias de Reddit.',
 'reddit', 'marketing', '🎬',
 'python:3.11-slim', 'https://github.com/elebumm/RedditVideoMakerBot', 'main', NULL,
 '{"REDDIT_CLIENT_ID": "", "REDDIT_CLIENT_SECRET": "", "REDDIT_USERNAME": "", "REDDIT_PASSWORD": "", "SUBREDDIT": "AskReddit", "TTS_VOICE": "en_us_male"}',
 '[{"key": "REDDIT_CLIENT_ID", "label": "Client ID de Reddit", "placeholder": "tu_client_id", "required": true}, {"key": "REDDIT_CLIENT_SECRET", "label": "Client Secret de Reddit", "placeholder": "tu_client_secret", "required": true}, {"key": "REDDIT_USERNAME", "label": "Usuario de Reddit", "placeholder": "mi_usuario", "required": true}, {"key": "REDDIT_PASSWORD", "label": "Contraseña de Reddit", "placeholder": "mi_contraseña", "required": true}]',
 512, 'medium', 'medium',
 'reddit,video,tts,youtube,tiktok,automatizacion,contenido',
 'https://github.com/elebumm/RedditVideoMakerBot',
 '1. Crea una app en Reddit (https://www.reddit.com/prefs/apps)\n2. Configura Client ID, Secret y credenciales\n3. Elige el subreddit fuente\n4. Despliega el bot\n5. Los vídeos se generarán automáticamente',
 0, 1, 44, 1, '1.0.0'),

-- 45. AmputatorBot
('amputatorbot', 'AmputatorBot — Limpiador de URLs',
 'Bot esencial para mantener la calidad de enlaces en plataformas sociales. Elimina rastreadores y formatos AMP de URLs compartidas, devolviendo la URL original limpia.',
 'Elimina rastreadores AMP de URLs compartidas.',
 'reddit', 'marketing', '✂️',
 'python:3.11-slim', NULL, 'main', NULL,
 '{"REDDIT_CLIENT_ID": "", "REDDIT_CLIENT_SECRET": "", "REDDIT_USERNAME": "", "REDDIT_PASSWORD": "", "SUBREDDITS": "all"}',
 '[{"key": "REDDIT_CLIENT_ID", "label": "Client ID de Reddit", "placeholder": "tu_client_id", "required": true}, {"key": "REDDIT_CLIENT_SECRET", "label": "Client Secret de Reddit", "placeholder": "tu_client_secret", "required": true}, {"key": "REDDIT_USERNAME", "label": "Usuario de Reddit", "placeholder": "mi_usuario", "required": true}, {"key": "REDDIT_PASSWORD", "label": "Contraseña de Reddit", "placeholder": "mi_contraseña", "required": true}]',
 128, 'free', 'easy',
 'amputatorbot,reddit,urls,amp,rastreadores,limpieza,privacidad',
 'https://www.amputatorbot.com/',
 '1. Crea una app en Reddit (https://www.reddit.com/prefs/apps)\n2. Configura Client ID, Secret y credenciales\n3. Elige en qué subreddits operar\n4. Despliega el bot\n5. Responderá automáticamente con URLs limpias',
 0, 1, 45, 1, '1.0.0'),

-- 46. AllContributors Bot
('allcontributors-bot', 'AllContributors Bot — Reconocimiento GitHub',
 'Automatiza el proceso de reconocer y agradecer a los contribuyentes de proyectos open source directamente desde comentarios de GitHub. Genera tablas de contribuidores y badges.',
 'Reconoce contribuyentes en proyectos GitHub automáticamente.',
 'other', 'marketing', '🏆',
 'node:20-alpine', 'https://github.com/all-contributors/all-contributors-bot', 'main', NULL,
 '{"GITHUB_TOKEN": "", "REPO_OWNER": "", "REPO_NAME": ""}',
 '[{"key": "GITHUB_TOKEN", "label": "Token de GitHub (Personal Access)", "placeholder": "ghp_...", "required": true}, {"key": "REPO_OWNER", "label": "Propietario del repositorio", "placeholder": "mi-usuario", "required": true}, {"key": "REPO_NAME", "label": "Nombre del repositorio", "placeholder": "mi-proyecto", "required": true}]',
 128, 'free', 'easy',
 'allcontributors,github,opensource,contribuyentes,reconocimiento',
 'https://allcontributors.org/',
 '1. Genera un Personal Access Token en GitHub\n2. Indica el repositorio donde reconocer contribuyentes\n3. Despliega el bot\n4. Comenta @all-contributors add @user <tipo> en issues/PRs\n5. El bot actualizará el README con la tabla de contribuyentes',
 0, 1, 46, 1, '1.0.0'),

-- 47. Kodiak
('kodiak', 'Kodiak — Auto-merge GitHub Bot',
 'Bot para GitHub que automatiza la actualización de dependencias y la fusión de pull requests una vez que han pasado todas las pruebas de calidad. Mantiene tus ramas actualizadas.',
 'Auto-merge de PRs en GitHub tras pasar tests.',
 'other', 'marketing', '🐻',
 'python:3.11-slim', 'https://github.com/chdsbd/kodiak', 'main', NULL,
 '{"GITHUB_APP_ID": "", "GITHUB_APP_PRIVATE_KEY": ""}',
 '[{"key": "GITHUB_APP_ID", "label": "ID de la GitHub App", "placeholder": "12345", "required": true}, {"key": "GITHUB_APP_PRIVATE_KEY", "label": "Private Key de la GitHub App (PEM)", "placeholder": "-----BEGIN RSA PRIVATE KEY-----...", "required": true}]',
 256, 'starter', 'medium',
 'kodiak,github,automerge,pr,ci,dependencias,automatizacion',
 'https://kodiakhq.com/',
 '1. Registra una GitHub App en tu organización\n2. Genera una Private Key\n3. Configura App ID y Private Key\n4. Instala la app en tus repositorios\n5. Añade un .kodiak.toml a tus repos para configurar reglas',
 0, 1, 47, 1, '1.0.0'),

-- 48. CodeCov Bot
('codecov-bot', 'CodeCov Bot — Cobertura de Código',
 'Integra reportes de cobertura de código en proyectos de desarrollo. Asegura que nuevas funciones mantengan altos estándares de calidad. Comentarios automáticos en PRs con cambios en cobertura.',
 'Reportes de cobertura de código en PRs de GitHub.',
 'other', 'marketing', '📊',
 'node:20-alpine', NULL, 'main', NULL,
 '{"CODECOV_TOKEN": "", "GITHUB_TOKEN": ""}',
 '[{"key": "CODECOV_TOKEN", "label": "Token de CodeCov", "placeholder": "uuid-token", "required": true}, {"key": "GITHUB_TOKEN", "label": "Token de GitHub", "placeholder": "ghp_...", "required": true}]',
 128, 'free', 'easy',
 'codecov,cobertura,testing,calidad,github,ci,reportes',
 'https://about.codecov.io/',
 '1. Registra tu repositorio en codecov.io\n2. Obtén el token de CodeCov\n3. Genera un token de GitHub\n4. Configura las credenciales\n5. Despliega y recibirás reportes de cobertura en cada PR',
 0, 1, 48, 1, '1.0.0'),

-- 49. Newsy Mastodon
('newsy-mastodon', 'Newsy Mastodon — Noticias Tech',
 'Bot especializado que publica las noticias más relevantes de plataformas tecnológicas directamente en Mastodon. Curación automática de contenido, programación de publicaciones y hashtags inteligentes.',
 'Publica noticias tech automáticamente en Mastodon.',
 'mastodon', 'marketing', '📰',
 'python:3.11-slim', 'https://github.com/anthonydahanne/newsy-mastodon', 'main', NULL,
 '{"MASTODON_INSTANCE": "", "MASTODON_ACCESS_TOKEN": "", "NEWS_SOURCES": "hackernews,techcrunch", "POST_INTERVAL": "3600"}',
 '[{"key": "MASTODON_INSTANCE", "label": "URL de tu instancia Mastodon", "placeholder": "https://mastodon.social", "required": true}, {"key": "MASTODON_ACCESS_TOKEN", "label": "Access Token de Mastodon", "placeholder": "tu_access_token", "required": true}]',
 128, 'free', 'easy',
 'newsy,mastodon,noticias,tech,automatico,rss,publicacion',
 'https://github.com/anthonydahanne/newsy-mastodon',
 '1. Crea una cuenta de bot en tu instancia Mastodon\n2. Ve a Preferencias > Desarrollo > Nueva aplicación\n3. Copia el Access Token\n4. Elige las fuentes de noticias\n5. Despliega y el bot publicará noticias automáticamente',
 0, 1, 49, 1, '1.0.0'),

-- 50. MastodonFrameBot
('mastodon-frame-bot', 'MastodonFrameBot — Fotogramas en Mastodon',
 'Bot artístico que publica fotogramas secuenciales de películas o series en Mastodon, creando una experiencia visual continua para los seguidores. Configura película, intervalo y calidad.',
 'Publica fotogramas de películas en Mastodon.',
 'mastodon', 'marketing', '🎞️',
 'python:3.11-slim', 'https://github.com/cfultz/MastodonFrameBot', 'main', NULL,
 '{"MASTODON_INSTANCE": "", "MASTODON_ACCESS_TOKEN": "", "MOVIE_PATH": "", "FRAME_INTERVAL": "300", "POST_INTERVAL": "3600"}',
 '[{"key": "MASTODON_INSTANCE", "label": "URL de tu instancia Mastodon", "placeholder": "https://mastodon.social", "required": true}, {"key": "MASTODON_ACCESS_TOKEN", "label": "Access Token de Mastodon", "placeholder": "tu_access_token", "required": true}, {"key": "MOVIE_PATH", "label": "Ruta al archivo de vídeo", "placeholder": "/data/movie.mp4", "required": true}]',
 256, 'starter', 'easy',
 'mastodon,fotogramas,pelicula,arte,visual,automatico,bot',
 'https://github.com/cfultz/MastodonFrameBot',
 '1. Crea una cuenta de bot en tu instancia Mastodon\n2. Ve a Preferencias > Desarrollo > Nueva aplicación\n3. Copia el Access Token\n4. Sube el archivo de vídeo al volumen del contenedor\n5. Despliega y el bot publicará fotogramas periódicamente',
 0, 1, 50, 1, '1.0.0')

ON DUPLICATE KEY UPDATE
    name=VALUES(name), description=VALUES(description), short_description=VALUES(short_description),
    platform=VALUES(platform), category=VALUES(category), icon=VALUES(icon),
    docker_image=VALUES(docker_image), git_repo_url=VALUES(git_repo_url),
    git_branch=VALUES(git_branch), install_command=VALUES(install_command),
    default_env_vars=VALUES(default_env_vars), required_env_vars=VALUES(required_env_vars),
    ram_mb_min=VALUES(ram_mb_min), min_plan_slug=VALUES(min_plan_slug),
    difficulty=VALUES(difficulty), tags=VALUES(tags),
    more_info_url=VALUES(more_info_url), setup_instructions=VALUES(setup_instructions),
    is_featured=VALUES(is_featured), is_active=VALUES(is_active),
    sort_order=VALUES(sort_order), auto_update_supported=VALUES(auto_update_supported),
    version=VALUES(version);
