# Configuración Apache2 — Lanzabot (entorno local)

Entorno: Ubuntu/Debian con Apache2. El webroot general es `/home/alfredo/web` (varios proyectos), y Lanzabot usa `public/` como su propio webroot.

---

## Requisitos previos

```bash
sudo apt install apache2
sudo a2enmod rewrite headers
sudo systemctl enable apache2
```

---

## 1. VirtualHost por defecto (`000-default.conf`)

Sirve todos los proyectos en `/home/alfredo/web` accesibles desde `http://localhost`.

```bash
sudo nano /etc/apache2/sites-available/000-default.conf
```

Contenido:

```apache
<VirtualHost *:80>
        ServerAdmin webmaster@localhost
        DocumentRoot /home/alfredo/web
        <Directory /home/alfredo/web>
                Options Indexes FollowSymLinks
                AllowOverride All
                Require all granted
        </Directory>

        ErrorLog ${APACHE_LOG_DIR}/error.log
        CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

> **Importante:** no añadir `Alias` ni `<Directory>` extra para lanzabot aquí. Cada proyecto con nombre propio va en su propio archivo de sitio.

---

## 2. VirtualHost de Lanzabot (`lanzabot.conf`)

Lanzabot necesita su propio VirtualHost porque la app tiene el webroot en `public/` (no en la raíz del repositorio). Con un `Alias` el router PHP recibiría la ruta con el prefijo del alias, rompiendo todas las rutas definidas en el código.

```bash
sudo nano /etc/apache2/sites-available/lanzabot.conf
```

Contenido:

```apache
<VirtualHost *:80>
    ServerName lanzabot.local

    DocumentRoot /home/alfredo/web/lanzabot/public

    <Directory /home/alfredo/web/lanzabot/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/lanzabot-error.log
    CustomLog ${APACHE_LOG_DIR}/lanzabot-access.log combined
</VirtualHost>
```

> **Nota de sintaxis:** en `Options`, si se mezclan opciones con y sin prefijo (`-`/`+`) Apache lanza error de sintaxis. Usar siempre prefijo explícito o ninguno.

---

## 3. Activar el sitio y recargar Apache

```bash
sudo a2ensite lanzabot.conf
sudo systemctl reload apache2
```

Verificar que no hay errores de sintaxis antes de recargar:

```bash
sudo apachectl configtest
```

---

## 4. Añadir entrada en `/etc/hosts`

Para que `lanzabot.local` resuelva en local:

```bash
grep -q "lanzabot.local" /etc/hosts || echo "127.0.0.1 lanzabot.local" | sudo tee -a /etc/hosts
```

---

## 5. Permisos de carpetas de la app

```bash
sudo chown -R www-data:www-data /home/alfredo/web/lanzabot/uploads
sudo chown -R www-data:www-data /home/alfredo/web/lanzabot/storage
sudo chmod 750 /home/alfredo/web/lanzabot/uploads
sudo chmod 750 /home/alfredo/web/lanzabot/storage
```

Sin esto, las subidas de código de bot fallan silenciosamente.

---

## Resultado final

| URL | DocumentRoot |
|-----|-------------|
| `http://localhost` | `/home/alfredo/web` (todos los proyectos) |
| `http://lanzabot.local` | `/home/alfredo/web/lanzabot/public` |

El `.htaccess` en `public/` ya redirige todo a `index.php` (front controller), con lo que el router recibe rutas limpias desde `/`.
