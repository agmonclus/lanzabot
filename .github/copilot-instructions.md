# Instrucciones para el agente IA — Lanzabot

Lanzabot es un **portal SaaS de hosting de bots y automatizaciones** (Telegram, Discord, WhatsApp, Matrix, Twitch, Mastodon, Reddit, Slack y multi-plataforma). Los usuarios se registran vía OAuth, eligen un plan de suscripción semanal y despliegan bots desde un **catálogo de 50 plantillas de código abierto** en contenedores Docker gestionados por **Coolify 4** (PaaS self-hosted). Stack: **PHP 8.1+, MySQL, Apache, Composer**. MVC casero sin frameworks.

---

## Comandos esenciales

```bash
# Instalar dependencias
composer install

# Importar esquema y datos semilla
mysql -u <user> -p <db> < database/schema.sql

# Aplicar migraciones (ejecutar en orden)
mysql -u <user> -p <db> < database/migrations/012_ecosystem_50_bots.sql

# Copiar y rellenar variables de entorno
cp .env.example .env
```

**Servidor de desarrollo:**  
Apuntar `DocumentRoot` a `public/` con `mod_rewrite` activo (Apache).

---

## Arquitectura

El único punto de entrada es [`public/index.php`](../public/index.php). El ciclo es:

```
Request → Router → Controller@method → Model (SQL/PDO) → View (PHP layouts)
```

| Capa | Ubicación | Notas |
|------|-----------|-------|
| Front controller | `public/index.php` | Bootstrap, autoload, dispatch |
| Router | `app/Core/Router.php` | Patrones `{id}` → regex; override `_method` POST para DELETE |
| Auth/sesiones | `app/Core/Auth.php` | Guards, CSRF, flash messages |
| Vista | `app/Core/View.php` | Renderizado PHP puro con layouts `main`/`auth` |
| BD | `app/Core/Database.php` | PDO wrapper — `fetch`, `fetchAll`, `insert`, `execute` |
| Coolify | `app/Core/CoolifyAPI.php` | Cliente cURL → `COOLIFY_HOST/api/v1` con Bearer token |
| Stripe | `app/Core/StripeService.php` | Wrapper de Stripe PHP SDK |

**Modelos** (`app/Models/`): solo métodos estáticos, SQL directo, sin ORM.  
**Vistas** (`app/Views/`): PHP puro, dos layouts: `layouts/main.php` (app) y `layouts/auth.php` (login/register).

---

## Dominio: Bot Templates — Ecosistema de 50 Bots

El catálogo de plantillas se basa en el documento *"Ecosistema de Automatización Descentralizada"* y organiza **50 bots de código abierto** en **5 categorías**:

| Categoría (slug) | Nombre | Bots | Ejemplo |
|---|---|---|---|
| `ai` | 🧠 IA y Agentes Autónomos | 1–10 | n8n, Dify, Langflow, Ollama, CrewAI |
| `communication` | 📡 Comunicaciones y Pasarelas | 11–20 | Evolution API, WAHA, grammY, Telegraf |
| `finance` | 💰 Finanzas y Comercio | 21–30 | Freqtrade, Hummingbot, OctoBot, Medusa, Saleor |
| `moderation` | 🛡️ Moderación y Seguridad | 31–40 | Skyra, Vortex, Red-DiscordBot, Discord-Tickets, TwirApp |
| `marketing` | 📢 Marketing y Desarrollo | 41–50 | Mautic, yt-dlp, Stickerify, Kodiak, MastodonFrameBot |

### Flujo de instalación "1 clic"

1. **Elegir plantilla** → catálogo con filtros por plataforma y categoría.
2. **Configurar credenciales** → formulario dinámico según `required_env_vars` (tokens de API, claves de exchange, etc.). Las credenciales se inyectan como variables de entorno en el contenedor.
3. **Deploy automático** → si la plantilla tiene `git_repo_url`, Coolify clona y construye con Nixpacks; si no, usa `docker_image` directamente.
4. **Gestión** → start/stop/restart/logs delegados a Coolify vía `coolify_app_uuid`.
5. **Auto-update** → si `auto_update_supported`, el sistema detecta nuevas versiones y re-despliega.

### Tipos de despliegue

| Tipo | `git_repo_url` | `docker_image` | Ejemplo |
|---|---|---|---|
| Docker image directa | NULL | `n8nio/n8n:latest` | n8n, Ollama, WAHA |
| Build desde repo | `https://github.com/...` | Fallback/build base | Freqtrade, Skyra, Stickerify |
| Framework (user code) | NULL | `python:3.11-slim` | grammY, Telegraf, matrix-nio |

### Requisitos de recursos por categoría

| Categoría | RAM típica | Plan mínimo |
|---|---|---|
| IA y Agentes | 512 MB – 4 GB+ | medium / pro |
| Comunicaciones | 64 – 256 MB | free / starter |
| Finanzas | 256 MB – 1 GB | starter / medium |
| Moderación | 128 – 512 MB | free / starter |
| Marketing | 128 – 512 MB | free / starter |

Ver [`app/Controllers/BotController.php`](../app/Controllers/BotController.php), [`app/Models/BotTemplate.php`](../app/Models/BotTemplate.php) y [`app/Core/CoolifyAPI.php`](../app/Core/CoolifyAPI.php).

---

## Dominio: Bots (instancias desplegadas)

Un `Bot` es una instancia desplegada de una plantilla. Tabla `bots` con:
- `template_id` → FK a `bot_templates`
- `coolify_app_uuid` → identificador en Coolify
- `env_vars` → JSON con las credenciales del usuario
- `auto_update`, `current_version`, `last_updated_at`

---

## Autenticación

Tres proveedores en [`app/Controllers/AuthController.php`](../app/Controllers/AuthController.php):

- **Google & Discord** — `league/oauth2-client`; flujo redirect → callback → verify state.
- **Telegram** — Login Widget; verificación con `hash_hmac('sha256', ..., sha256(BOT_TOKEN))`; `auth_date` no puede tener más de 24 h.

`User::upsertOAuth()` hace merge: busca por `provider_id` → por email → crea. Tras login llama a `ensureSubscription()` (crea suscripción `free` si no tiene ninguna).

---

## Pagos — Stripe

- `PlanController@subscribe` → Stripe Checkout Session con `metadata.user_id` + `metadata.plan_slug`.
- `POST /stripe/webhook` → valida firma → maneja: `checkout.session.completed`, `customer.subscription.updated/deleted`, `invoice.payment_succeeded/failed`.
- `GET /billing/portal` → Stripe Billing Portal.

Ver [`app/Controllers/BillingController.php`](../app/Controllers/BillingController.php) y [`app/Core/StripeService.php`](../app/Core/StripeService.php).

---

## Convenciones del código

- **Modelos:** solo métodos estáticos (`User::findById(...)`, `Bot::findByUser(...)`). Sin instancias, sin ORM.
- **Controladores:** siempre empiezan con `Auth::require()` como guard en rutas protegidas. CSRF se verifica con `$this->verifyCsrf()`.
- **Vistas:** PHP puro. Sin Blade/Twig. Variables llegadas desde el controller se pasan como array al renderizador.
- **Idioma:** mensajes flash, errores y comentarios en **español**.
- **Seguridad:** CSRF via `bin2hex(random_bytes(32))` + `hash_equals`. Todos los SQL usan PDO prepared statements. Uploads siempre fuera de `public/`.

---

## BD — esquema resumido

Ver [`database/schema.sql`](../database/schema.sql) para DDL completo.

| Tabla | Propósito |
|-------|-----------|
| `users` | Identidades OAuth; puede tener google_id, discord_id, telegram_id; email nullable |
| `plans` | Planes con cuotas: `max_bots`, `ram_mb`, `disk_gb`, `max_databases` |
| `subscriptions` | Estado Stripe: `active`, `canceled`, `past_due`, `trialing`, `free`... |
| `bots` | Instancias desplegadas: `coolify_app_uuid`, `template_id`, `env_vars` JSON, `platform` ENUM |
| `bot_templates` | Catálogo de 50 plantillas: `slug`, `category`, `git_repo_url`, `docker_image`, `required_env_vars` JSON, `ram_mb_min`, `min_plan_slug` |
| `payments` | Historial de invoices Stripe |

---

## Variables de entorno clave

Ver [`config/config.php`](../config/config.php) para el mapeo completo. Variables críticas:

```
APP_URL, APP_KEY
DB_HOST, DB_NAME, DB_USER, DB_PASS
GOOGLE_CLIENT_ID/SECRET, DISCORD_CLIENT_ID/SECRET
TELEGRAM_BOT_TOKEN
STRIPE_PUBLIC_KEY, STRIPE_SECRET_KEY, STRIPE_WEBHOOK_SECRET
COOLIFY_HOST, COOLIFY_API_KEY, COOLIFY_SERVER_UUID, COOLIFY_PROJECT_UUID
```

Las credenciales de cada bot (tokens de plataforma, API keys de exchanges, etc.) **no** van en el `.env` global sino en las `env_vars` de cada instancia de bot, inyectadas en el contenedor Docker vía Coolify.

## Posibles trampas

- El **override del método HTTP** usa el campo `_method` en POST (ej. DELETE para bots). Tenerlo en cuenta al añadir rutas.
- El modelo `User` puede tener **email null** (usuarios de Telegram sin email configurado). No asumir email siempre presente.
- Los **env vars del bot** se guardan en BD como JSON pero se envían a Coolify como array `[{key, value}]`. Transformación en `BotController`.
- Al crear una Application en Coolify se debe proporcionar `server_uuid` y `project_uuid` globales (vienen de las constantes de entorno).
- Las **cuotas del plan** (`max_bots`, `ram_mb`) se comprueban en el controlador antes de deploy; si se supera el límite se muestra un flash y se redirige.
- `uploads/` y `storage/` deben tener permisos `750` y propietario `www-data`; sin esto los uploads fallan silenciosamente.
- Las **5 categorías principales** de templates son: `ai`, `communication`, `finance`, `moderation`, `marketing`. Hay categorías legacy para retrocompatibilidad.
- Bots de IA pesados (Ollama, Dify) requieren **plan pro** y 2-4 GB RAM. No desplegar en planes free/starter.
- Templates de **frameworks** (grammY, Telegraf, matrix-nio, Telebot Go) no tienen `git_repo_url` → necesitan que el usuario suba su propio código.
- Aplicaciones complejas (Dify, Bagisto, Saleor) pueden necesitar **servicios adjuntos** (PostgreSQL, Redis) que se configuran en Coolify como servicios en la misma red interna.

---

## Centinela `__COOLIFY_FQDN__`

Cuando un template necesita conocer su propio dominio público (p.ej. n8n para `N8N_HOST`, `WEBHOOK_URL`, `N8N_EDITOR_BASE_URL`), se ponen centinelas en `default_env_vars`. El `BotController` los resuelve automáticamente tras crear la aplicación en Coolify:

| Centinela | Valor resuelto | Uso típico |
|---|---|---|
| `__COOLIFY_FQDN__` | URL completa con protocolo: `http://uuid.dominio.io` | `WEBHOOK_URL`, `N8N_EDITOR_BASE_URL` |
| `__COOLIFY_HOST__` | Solo hostname sin protocolo: `uuid.dominio.io` | `N8N_HOST` (n8n falla si recibe `http://` aquí) |

El flujo es:
1. Detecta si algún valor de `$envVars` es `__COOLIFY_FQDN__` o `__COOLIFY_HOST__`.
2. Llama a `CoolifyAPI::getApplication($uuid)` y extrae el campo `fqdn`.
3. Reemplaza todos los centinelas, actualiza la BD (`Bot::setEnvVars`) y reenvía las vars a Coolify (`CoolifyAPI::updateEnvVars`) **antes** del deploy final.

Usar estos centinelas en cualquier template futuro que exponga una URL pública. **No pedir el dominio al usuario en el formulario.**

---

## Configuración correcta del template n8n (id=51)

Variables críticas que deben estar en `default_env_vars` para que n8n arranque sin crash loop:

| Variable | Valor por defecto | Motivo |
|---|---|---|
| `DB_TYPE` | `sqlite` | Sin esto n8n puede intentar conectar a un host externo y hacer timeout |
| `N8N_HOST` | `__COOLIFY_HOST__` | Solo hostname sin `http://`; n8n crashea si recibe URL completa |
| `WEBHOOK_URL` | `__COOLIFY_FQDN__` | Base URL para webhooks de workflows |
| `N8N_EDITOR_BASE_URL` | `__COOLIFY_FQDN__` | URL del editor en producción |
| `N8N_BASIC_AUTH_ACTIVE` | `true` | Habilitar autenticación básica |
| `GENERIC_TIMEZONE` | `Europe/Madrid` | Zona horaria para disparadores cron |

**Variables obsoletas — NO incluir:**
- `N8N_RUNNERS_ENABLED` — eliminada en n8n 1.x reciente; la app muestra un aviso de deprecación y pide quitarla. El warning "Python 3 is missing" persiste en el log pero no afecta al funcionamiento.

Los mensajes `[license SDK] Skipping renewal on init` y `Last session crashed` son **normales** en Community Edition; no son errores críticos por sí solos. `Last session crashed` aparece en cada reinicio del contenedor porque n8n detecta que el proceso anterior no terminó limpiamente.
