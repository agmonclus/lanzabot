# Lanzabot.com — Guía de puesta en marcha

## 1. Variables de entorno (.env)

Copia `.env.example` a `.env` y rellena todos los valores. A continuación se explica cada sección.

---

## 2. Google OAuth

1. Ve a [Google Cloud Console](https://console.cloud.google.com/)
2. Crea un proyecto o usa uno existente
3. Ve a **APIs & Services → Credentials → Create Credentials → OAuth 2.0 Client ID**
4. Tipo de aplicación: **Web application**
5. Añade en "Authorized redirect URIs":
   - `https://lanzabot.com/auth/google/callback`
6. Copia el **Client ID** y **Client Secret** al `.env`:
   ```
   GOOGLE_CLIENT_ID=xxx.apps.googleusercontent.com
   GOOGLE_CLIENT_SECRET=GOCSPX-xxx
   GOOGLE_REDIRECT_URI=https://lanzabot.com/auth/google/callback
   ```

---

## 3. Discord OAuth

1. Ve a [Discord Developer Portal](https://discord.com/developers/applications)
2. Crea una nueva aplicación
3. Ve a **OAuth2 → General**
4. Añade en "Redirects": `https://lanzabot.com/auth/discord/callback`
5. Copia el **Client ID** y **Client Secret**:
   ```
   DISCORD_CLIENT_ID=123456789
   DISCORD_CLIENT_SECRET=xxx
   DISCORD_REDIRECT_URI=https://lanzabot.com/auth/discord/callback
   ```

---

## 4. Telegram Login Widget

1. Habla con [@BotFather](https://t.me/BotFather) en Telegram
2. Crea un bot con `/newbot` o usa uno existente
3. Ejecuta el comando `/setdomain` y especifica `lanzabot.com`
4. Rellena en `.env`:
   ```
   TELEGRAM_BOT_TOKEN=123456789:AAFxxx
   TELEGRAM_BOT_USERNAME=MiLanzabot  # sin @
   ```

---

## 5. Stripe

### 5.1 Crear cuenta y obtener claves
1. Regístrate en [stripe.com](https://stripe.com)
2. En el Dashboard, copia las claves desde **Developers → API keys**:
   ```
   STRIPE_PUBLIC_KEY=pk_live_...
   STRIPE_SECRET_KEY=sk_live_...
   ```
   (Usa `pk_test_` / `sk_test_` para pruebas)

### 5.2 Crear los productos y precios
En el Dashboard de Stripe, ve a **Products → Add product** y crea tres productos:

| Producto | Precio | Facturación |
|----------|--------|-------------|
| Medium   | 1,00 € | Semanal     |
| Starter  | 2,00 € | Semanal     |
| Pro      | 5,00 € | Semanal     |

Para cada uno, Stripe generará un **Price ID** (empieza por `price_`). Cópialos:
```
STRIPE_PRICE_MEDIUM=price_xxx
STRIPE_PRICE_STARTER=price_yyy
STRIPE_PRICE_PRO=price_zzz
```

> **Nota:** Stripe no tiene facturación nativa "semanal" pero sí "diaria" o "mensual".
> Para semanal: elige **Recurring → Custom** y pon intervalo = `week`.

### 5.3 Webhook de Stripe
1. En el Dashboard, ve a **Developers → Webhooks → Add endpoint**
2. URL del endpoint: `https://lanzabot.com/stripe/webhook`
3. Selecciona estos eventos:
   - `checkout.session.completed`
   - `customer.subscription.updated`
   - `customer.subscription.deleted`
   - `invoice.payment_succeeded`
   - `invoice.payment_failed`
4. Copia el **Signing secret** (`whsec_...`):
   ```
   STRIPE_WEBHOOK_SECRET=whsec_...
   ```

---

## 6. Coolify

### 6.1 Configurar Coolify en el servidor (37.59.113.81)

1. Accede a Coolify en `http://37.59.113.81:8000` (puerto por defecto)
2. Completa el wizard de instalación inicial

### 6.2 Obtener API Key
1. En Coolify: **Profile (arriba derecha) → API tokens → Create**
2. Dale todos los permisos y copia el token:
   ```
   COOLIFY_API_KEY=eyJ...
   ```

### 6.3 Obtener UUIDs necesarios

**Server UUID:**
```bash
curl -H "Authorization: Bearer TU_API_KEY" http://37.59.113.81/api/v1/servers | jq '.[0].uuid'
```
```
COOLIFY_SERVER_UUID=uuid-del-servidor
```

**Project UUID:**
Crea un proyecto en Coolify llamado "lanzabot-bots" o usa el default:
```bash
curl -H "Authorization: Bearer TU_API_KEY" http://37.59.113.81/api/v1/projects | jq '.[0].uuid'
```
```
COOLIFY_PROJECT_UUID=uuid-del-proyecto
```

### 6.4 Configurar en Coolify para bots
- Coolify gestiona los contenedores de cada bot automáticamente
- Cada bot se crea como una **Application** en Coolify
- Los logs y stats se obtienen vía la API de Coolify desde el portal

---

## 7. Servidor web (Apache)

El `public/` debe ser el DocumentRoot. Ejemplo de VirtualHost:

```apache
<VirtualHost *:80>
    ServerName lanzabot.com
    ServerAlias www.lanzabot.com
    DocumentRoot /ruta/a/lanzabot.com/public

    <Directory /ruta/a/lanzabot.com/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Asegúrate de tener `mod_rewrite` activo:
```bash
a2enmod rewrite
systemctl reload apache2
```

Para SSL con Let's Encrypt:
```bash
certbot --apache -d lanzabot.com -d www.lanzabot.com
```

---

## 8. Permisos de directorios

```bash
chmod 750 uploads/
chmod 750 storage/
chmod 750 storage/logs/
chown -R www-data:www-data uploads/ storage/
```

---

## 9. Base de datos

```bash
mysql -u root -p lanzabot < database/schema.sql
```

Actualiza el `.env` con las credenciales de producción.

---

## 10. Checklist de lanzamiento

- [ ] `.env` rellenado completamente
- [ ] Base de datos creada con el schema
- [ ] Google OAuth configurado y redirect URI apuntando a producción
- [ ] Discord OAuth configurado
- [ ] Telegram bot configurado con `/setdomain`
- [ ] Stripe: productos creados, Price IDs configurados
- [ ] Stripe: webhook configurado y `STRIPE_WEBHOOK_SECRET` en `.env`
- [ ] Coolify accesible y API Key obtenida
- [ ] `COOLIFY_SERVER_UUID` y `COOLIFY_PROJECT_UUID` en `.env`
- [ ] Apache/Nginx configurado con `public/` como DocumentRoot
- [ ] SSL certificado instalado
- [ ] Permisos de `uploads/` y `storage/` correctos
- [ ] `APP_ENV=production` en `.env`
- [ ] `APP_SECRET` cambiado a un valor aleatorio (usa `openssl rand -hex 32`)
- [ ] `APP_URL=https://lanzabot.com`

---

## 11. Estructura del proyecto

```
lanzabot.com/
├── public/             # DocumentRoot del servidor web
│   ├── index.php       # Front controller (único punto de entrada)
│   ├── .htaccess       # Rewrite rules
│   └── assets/         # CSS, JS, imágenes
├── app/
│   ├── Core/           # Router, Database, Auth, View, APIs
│   ├── Controllers/    # Lógica de cada sección
│   ├── Models/         # Acceso a base de datos
│   └── Views/          # Plantillas PHP
├── config/
│   └── config.php      # Carga variables de entorno
├── database/
│   └── schema.sql      # Schema y datos iniciales
├── uploads/            # Código de bots subido por usuarios
├── storage/logs/       # Logs de solicitudes custom
├── vendor/             # Dependencias Composer
├── .env                # Variables de entorno (NO subir a git)
└── composer.json
```

---

## 12. Flujo de un despliegue de bot

1. Usuario se registra (SSO) → se crea en BD + suscripción Free
2. Va a "Desplegar nuevo bot" → rellena nombre, plataforma, imagen Docker
3. En la página del bot:
   - Sube su código (.zip o .tar.gz)
   - Configura variables de entorno (BOT_TOKEN, etc.)
   - Pulsa "Desplegar ahora"
4. El portal llama a la API de Coolify para crear una Application
5. Coolify despliega el contenedor con la imagen Docker especificada
6. El bot aparece como "running" y el usuario puede ver logs en tiempo real

## 13. Planes y límites

| Plan    | Bots | RAM    | Disco | DBs | Precio   |
|---------|------|--------|-------|-----|----------|
| Free    | 1    | 128 MB | —     | 0   | Gratis   |
| Medium  | 4    | 500 MB | 10 GB | 1   | 1 €/sem  |
| Starter | 6    | 1 GB   | 20 GB | 2   | 2 €/sem  |
| Pro     | 8    | 2 GB   | 50 GB | 4   | 5 €/sem  |
| Custom  | —    | —      | —     | —   | A medida |
