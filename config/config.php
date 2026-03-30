<?php

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

define('APP_ENV',    $_ENV['APP_ENV'] ?? 'production');
define('APP_URL',    rtrim($_ENV['APP_URL'] ?? '', '/'));
define('APP_SECRET', $_ENV['APP_SECRET']);

define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'lanzabot');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');

define('GOOGLE_CLIENT_ID',     $_ENV['GOOGLE_CLIENT_ID'] ?? '');
define('GOOGLE_CLIENT_SECRET', $_ENV['GOOGLE_CLIENT_SECRET'] ?? '');
define('GOOGLE_REDIRECT_URI',  $_ENV['GOOGLE_REDIRECT_URI'] ?? '');

define('DISCORD_CLIENT_ID',     $_ENV['DISCORD_CLIENT_ID'] ?? '');
define('DISCORD_CLIENT_SECRET', $_ENV['DISCORD_CLIENT_SECRET'] ?? '');
define('DISCORD_REDIRECT_URI',  $_ENV['DISCORD_REDIRECT_URI'] ?? '');

define('TELEGRAM_BOT_TOKEN',    $_ENV['TELEGRAM_BOT_TOKEN'] ?? '');
define('TELEGRAM_BOT_USERNAME', $_ENV['TELEGRAM_BOT_USERNAME'] ?? '');

define('STRIPE_PUBLIC_KEY',      $_ENV['STRIPE_PUBLIC_KEY'] ?? '');
define('STRIPE_SECRET_KEY',      $_ENV['STRIPE_SECRET_KEY'] ?? '');
define('STRIPE_WEBHOOK_SECRET',  $_ENV['STRIPE_WEBHOOK_SECRET'] ?? '');
define('STRIPE_PRICE_MEDIUM',    $_ENV['STRIPE_PRICE_MEDIUM'] ?? '');
define('STRIPE_PRICE_STARTER',   $_ENV['STRIPE_PRICE_STARTER'] ?? '');
define('STRIPE_PRICE_PRO',       $_ENV['STRIPE_PRICE_PRO'] ?? '');

define('COOLIFY_HOST',         rtrim($_ENV['COOLIFY_HOST'] ?? 'http://37.59.113.81', '/'));
define('COOLIFY_API_KEY',      $_ENV['COOLIFY_API_KEY'] ?? '');
define('COOLIFY_SERVER_UUID',  $_ENV['COOLIFY_SERVER_UUID'] ?? '');
define('COOLIFY_PROJECT_UUID', $_ENV['COOLIFY_PROJECT_UUID'] ?? '');

define('UPLOAD_PATH', dirname(__DIR__) . '/uploads');
define('MAX_UPLOAD_SIZE', 50 * 1024 * 1024); // 50 MB

define('MAIL_HOST',       $_ENV['MAIL_HOST']       ?? 'localhost');
define('MAIL_PORT',       (int)($_ENV['MAIL_PORT'] ?? 587));
define('MAIL_USERNAME',   $_ENV['MAIL_USERNAME']   ?? '');
define('MAIL_PASSWORD',   $_ENV['MAIL_PASSWORD']   ?? '');
define('MAIL_ENCRYPTION', $_ENV['MAIL_ENCRYPTION'] ?? 'starttls');
define('MAIL_FROM_EMAIL', $_ENV['MAIL_FROM_EMAIL'] ?? '');
define('MAIL_FROM_NAME',  $_ENV['MAIL_FROM_NAME']  ?? 'Lanzabot');
define('MAIL_REPLY_TO',   $_ENV['MAIL_REPLY_TO']   ?? '');

if (APP_ENV === 'local') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}
