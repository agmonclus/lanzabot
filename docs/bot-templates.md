# Plantillas de Bots — Documentación

## ¿Qué son las plantillas de bots?

Las **plantillas de bots** son configuraciones prefabricadas que permiten a los usuarios desplegar bots open source populares con **un solo clic**. Cada plantilla incluye:

- Imagen Docker preconfigurada
- Variables de entorno con descripción y placeholders
- Instrucciones paso a paso
- Enlace al repositorio del proyecto original

**Objetivo:** Que cualquier usuario, sin conocimientos técnicos, pueda tener un bot funcionando en menos de 2 minutos.

---

## Tabla `bot_templates` — Esquema

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | INT PK | Identificador auto-incremental |
| `slug` | VARCHAR(80) UNIQUE | Identificador URL-friendly (ej: `telegram-gpt-bot`) |
| `name` | VARCHAR(150) | Nombre visible de la plantilla |
| `description` | TEXT | Descripción completa con detalles y características |
| `short_description` | VARCHAR(255) | Resumen de una línea para tarjetas |
| `platform` | ENUM | `telegram`, `discord`, `multi`, `other` |
| `category` | VARCHAR(50) | Categoría libre: `ai`, `utility`, `entertainment`, `moderation`, `ecommerce`, `starter` |
| `icon` | VARCHAR(10) | Emoji representativo |
| `docker_image` | VARCHAR(255) | Imagen Docker base (ej: `python:3.11-slim`) |
| `git_repo_url` | VARCHAR(500) | URL del repositorio Git del proyecto |
| `default_env_vars` | JSON | Objeto `{CLAVE: valor_defecto}` con las env vars pre-rellenadas |
| `required_env_vars` | JSON | Array de objetos con info de cada variable: `[{key, label, placeholder, required}]` |
| `ram_mb_min` | INT | RAM mínima recomendada en MB |
| `min_plan_slug` | VARCHAR(50) | Plan mínimo necesario: `free`, `starter`, `medium`, `pro` |
| `difficulty` | ENUM | `easy`, `medium`, `advanced` |
| `tags` | VARCHAR(500) | Tags separados por coma para búsqueda |
| `documentation_url` | VARCHAR(500) | Enlace a documentación externa |
| `setup_instructions` | TEXT | Instrucciones paso a paso en texto plano |
| `is_featured` | TINYINT(1) | Si se muestra como destacada |
| `is_active` | TINYINT(1) | Si está disponible para los usuarios |
| `install_count` | INT UNSIGNED | Contador de despliegues |
| `sort_order` | INT | Orden de visualización |

---

## Las 10 plantillas incluidas

### 1. 📢 Telegram Echo Bot
- **Plataforma:** Telegram | **Dificultad:** Fácil | **Plan mínimo:** Free
- **Qué hace:** Repite cada mensaje que recibe. Punto de partida perfecto.
- **Requiere:** Token de Telegram (`@BotFather`)

### 2. 🎵 Discord Music Bot
- **Plataforma:** Discord | **Dificultad:** Fácil | **Plan mínimo:** Starter
- **Qué hace:** Reproduce música de YouTube en canales de voz. Comandos: `!play`, `!skip`, `!stop`, `!queue`
- **Requiere:** Token de Discord

### 3. 🧠 Telegram AI Chat (GPT)
- **Plataforma:** Telegram | **Dificultad:** Fácil | **Plan mínimo:** Starter
- **Qué hace:** Asistente con ChatGPT integrado. Responde preguntas, traduce, resume.
- **Requiere:** Token de Telegram + API Key de OpenAI

### 4. 🛡️ Discord Moderación Bot
- **Plataforma:** Discord | **Dificultad:** Media | **Plan mínimo:** Starter
- **Qué hace:** Moderación automática: antispam, warns, kicks, bans, logs.
- **Requiere:** Token de Discord

### 5. 🛒 Telegram Tienda Bot
- **Plataforma:** Telegram | **Dificultad:** Media | **Plan mínimo:** Starter
- **Qué hace:** Tienda online con catálogo, carrito y panel de admin.
- **Requiere:** Token de Telegram + tu User ID

### 6. 👋 Discord Bienvenida Bot
- **Plataforma:** Discord | **Dificultad:** Fácil | **Plan mínimo:** Free
- **Qué hace:** Bienvenidas automáticas, roles por reacción, mensajes personalizados.
- **Requiere:** Token de Discord + ID del canal de bienvenida

### 7. 🎨 Bot Generador de Imágenes IA
- **Plataforma:** Telegram | **Dificultad:** Fácil | **Plan mínimo:** Starter
- **Qué hace:** Genera imágenes con DALL-E 3 a partir de descripciones de texto.
- **Requiere:** Token de Telegram + API Key de OpenAI

### 8. 📡 Telegram RSS Feed Bot
- **Plataforma:** Telegram | **Dificultad:** Media | **Plan mínimo:** Free
- **Qué hace:** Monitorea feeds RSS y envía notificaciones automáticas.
- **Requiere:** Token de Telegram + Chat ID + URLs de RSS

### 9. 🎫 Discord Tickets / Soporte Bot
- **Plataforma:** Discord | **Dificultad:** Media | **Plan mínimo:** Starter
- **Qué hace:** Sistema de tickets de soporte con canales privados y transcripciones.
- **Requiere:** Token de Discord + ID del rol de soporte

### 10. 🌐 Asistente IA Multi-plataforma
- **Plataforma:** Multi (Telegram + Discord) | **Dificultad:** Avanzada | **Plan mínimo:** Medium
- **Qué hace:** Asistente IA avanzado que funciona en Telegram y Discord simultáneamente.
- **Requiere:** API Key de OpenAI + Token de Telegram y/o Discord

---

## Panel de Administración

### Acceso
El panel es accesible en `/admin` solo para usuarios con `is_admin = 1` en la tabla `users`.

Para hacer admin a un usuario:
```sql
UPDATE users SET is_admin = 1 WHERE id = <user_id>;
```

### Secciones del panel

| Ruta | Descripción |
|------|-------------|
| `/admin` | Dashboard con KPIs: usuarios, bots, plantillas, suscripciones, ingresos |
| `/admin/users` | Lista de todos los usuarios con plan, proveedor OAuth y nº de bots |
| `/admin/bots` | Lista de todos los bots con estado, plataforma y usuario |
| `/admin/templates` | CRUD completo de plantillas de bots |
| `/admin/subscriptions` | Todas las suscripciones con estado y datos de Stripe |
| `/admin/payments` | Historial completo de pagos |
| `/admin/plans` | Vista de los planes configurados |

### CRUD de plantillas

- **Crear:** `/admin/templates/create` → Formulario completo
- **Editar:** `/admin/templates/{id}/edit` → Mismo formulario con datos pre-rellenados
- **Eliminar:** POST a `/admin/templates/{id}/delete` con confirmación

---

## Formato de `required_env_vars` (JSON)

Cada variable se describe como un objeto con esta estructura:

```json
[
  {
    "key": "BOT_TOKEN",
    "label": "Token del Bot de Telegram",
    "placeholder": "123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11",
    "required": true
  },
  {
    "key": "OPENAI_API_KEY",
    "label": "API Key de OpenAI",
    "placeholder": "sk-...",
    "required": true
  }
]
```

Este formato permite generar formularios dinámicos para que el usuario rellene solo las variables necesarias.

---

## Formato de `default_env_vars` (JSON)

Objeto simple clave-valor con valores por defecto o vacíos:

```json
{
  "BOT_TOKEN": "",
  "OPENAI_API_KEY": "",
  "OPENAI_MODEL": "gpt-4o-mini",
  "SYSTEM_PROMPT": "Eres un asistente útil."
}
```

---

## Flujo de despliegue desde plantilla (futuro)

1. Usuario navega el catálogo de plantillas
2. Selecciona una plantilla → ve detalles y requisitos
3. Rellena las variables de entorno requeridas (formulario dinámico)
4. Click en "Desplegar" → se crea un Bot automáticamente con:
   - `template_id` vinculado
   - `docker_image` de la plantilla
   - `env_vars` rellenadas por el usuario
5. Se despliega en Coolify → el bot está funcionando

---

## Ejecutar la migración

```bash
mysql -u root -p lanzabot < database/migrations/003_admin_and_bot_templates.sql
```

Esto creará:
- Campo `is_admin` en tabla `users`
- Tabla `bot_templates`
- Campo `template_id` en tabla `bots`
- 10 plantillas de ejemplo

---

## Estructura de archivos creados

```
app/
  Controllers/AdminController.php    — Controlador del panel admin
  Models/BotTemplate.php             — Modelo de plantillas
  Views/
    admin/
      index.php                      — Dashboard admin
      users.php                      — Lista de usuarios
      bots.php                       — Lista de bots
      subscriptions.php              — Lista de suscripciones
      payments.php                   — Lista de pagos
      plans.php                      — Vista de planes
      templates/
        index.php                    — Lista de plantillas
        form.php                     — Formulario crear/editar plantilla
    layouts/
      admin.php                      — Layout con sidebar admin
database/
  migrations/
    003_admin_and_bot_templates.sql   — Migración completa
public/
  assets/css/admin.css               — Estilos del panel admin
docs/
  bot-templates.md                   — Este documento
```
