# Instrucciones para el agente IA — Lanzabot

Lanzabot es un **portal SaaS de hosting de bots** (Telegram, Discord, otros). Los usuarios se registran vía OAuth, eligen un plan de suscripción semanal, suben código de bot en ZIP/TAR y lo despliegan en contenedores Docker gestionados por **Coolify** (PaaS self-hosted). Stack: **PHP 8.1+, MySQL, Apache, Composer**. MVC casero sin frameworks.

---

## Comandos esenciales

```bash
# Instalar dependencias
composer install

# Importar esquema y datos semilla
mysql -u <user> -p <db> < database/schema.sql

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

## Dominio: Bots

Un `Bot` representa un bot de mensajería que el usuario hostea. Flujo:
1. **Crear** → nombre + plataforma + imagen Docker base.
2. **Subir código** → ZIP/TAR ≤ 50 MB → guardado en `uploads/{user_id}/{bot_id}/code.zip` (fuera de `public/`).
3. **Configurar env vars** → formato `KEY=VALUE`, serializado como JSON en BD.
4. **Deploy** → crea (o actualiza) Application en Coolify con env vars y `limits_memory` del plan.
5. **Gestión** → start/stop/restart/logs delegados a Coolify vía `coolify_app_uuid`.

Ver [`app/Controllers/BotController.php`](../app/Controllers/BotController.php) y [`app/Core/CoolifyAPI.php`](../app/Core/CoolifyAPI.php).

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
| `bots` | `coolify_app_uuid`, `env_vars` JSON, `platform` ENUM, `code_path` |
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

---

## Posibles trampas

- El **override del método HTTP** usa el campo `_method` en POST (ej. DELETE para bots). Tenerlo en cuenta al añadir rutas.
- El modelo `User` puede tener **email null** (usuarios de Telegram sin email configurado). No asumir email siempre presente.
- Los **env vars del bot** se guardan en BD como JSON pero se envían a Coolify como array `[{key, value}]`. Transformación en `BotController`.
- Al crear una Application en Coolify se debe proporcionar `server_uuid` y `project_uuid` globales (vienen de las constantes de entorno).
- Las **cuotas del plan** (`max_bots`, `ram_mb`) se comprueban en el controlador antes de deploy; si se supera el límite se muestra un flash y se redirige.
- `uploads/` y `storage/` deben tener permisos `750` y propietario `www-data`; sin esto los uploads fallan silenciosamente.
