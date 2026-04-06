<?php

declare(strict_types=1);

define('ROOT', realpath(__DIR__ . '/..') ?: dirname(__DIR__));

require ROOT . '/vendor/autoload.php';
require ROOT . '/config/config.php';

use App\Core\Auth;
use App\Core\Router;
use App\Core\View;

Auth::start();

$router = new Router();

// Auth
$router->get('/login',               'AuthController@login');
$router->get('/register',            'AuthController@registerForm');
$router->post('/register',           'AuthController@register');
$router->get('/login/email',         'AuthController@loginEmailForm');
$router->post('/login/email',        'AuthController@loginEmail');
$router->get('/auth/verify-email',   'AuthController@verifyEmail');
$router->get('/forgot-password',     'AuthController@forgotPasswordForm');
$router->post('/forgot-password',    'AuthController@forgotPassword');
$router->get('/reset-password',      'AuthController@resetPasswordForm');
$router->post('/reset-password',     'AuthController@resetPassword');
$router->get('/auth/google',         'AuthController@googleRedirect');
$router->get('/auth/google/callback','AuthController@googleCallback');
$router->get('/auth/discord',        'AuthController@discordRedirect');
$router->get('/auth/discord/callback','AuthController@discordCallback');
$router->get('/auth/telegram',       'AuthController@telegramCallback');
$router->get('/logout',              'AuthController@logout');

// Dashboard
$router->get('/',          'DashboardController@index');
$router->get('/dashboard', 'DashboardController@index');

// Bots
$router->get('/bots/create',                'BotController@create');
$router->get('/bots/from-template/{id}',    'BotController@fromTemplate');
$router->post('/bots/from-template/{id}',   'BotController@storeFromTemplate');
$router->get('/bots/check-updates',         'BotController@checkUpdates');
$router->get('/bots/{id}',                  'BotController@show');
$router->post('/bots/{id}/env',             'BotController@saveEnv');
$router->post('/bots/{id}/deploy',          'BotController@deploy');
$router->post('/bots/{id}/update',          'BotController@updateBot');
$router->post('/bots/{id}/auto-update',     'BotController@toggleAutoUpdate');
$router->post('/bots/{id}/start',           'BotController@start');
$router->post('/bots/{id}/stop',            'BotController@stop');
$router->post('/bots/{id}/restart',         'BotController@restart');
$router->post('/bots/{id}/delete',          'BotController@destroy');
$router->get('/bots/{id}/logs',             'BotController@logs');
$router->get('/bots/{id}/stats',            'BotController@stats');

// Plans
$router->get('/plans',              'PlanController@index');
$router->post('/plans/subscribe',   'PlanController@subscribe');
$router->post('/plans/custom',      'PlanController@customRequest');

// Billing
$router->get('/billing',         'BillingController@index');
$router->get('/billing/portal',  'BillingController@portal');
$router->post('/stripe/webhook', 'BillingController@webhook');

// Help
$router->get('/help',              'HelpController@index');
$router->get('/help/guide/{slug}', 'HelpController@guide');

// Admin
$router->get('/admin',                          'AdminController@index');
$router->get('/admin/users',                    'AdminController@users');
$router->get('/admin/bots',                     'AdminController@bots');
$router->get('/admin/subscriptions',            'AdminController@subscriptions');
$router->get('/admin/payments',                 'AdminController@payments');
$router->get('/admin/plans',                    'AdminController@plans');
$router->get('/admin/templates',                'AdminController@templates');
$router->get('/admin/templates/create',         'AdminController@templateCreate');
$router->post('/admin/templates',               'AdminController@templateStore');
$router->get('/admin/templates/{id}/edit',      'AdminController@templateEdit');
$router->post('/admin/templates/{id}/update',   'AdminController@templateUpdate');
$router->post('/admin/templates/{id}/delete',   'AdminController@templateDelete');

// Dispatch
$method = $_SERVER['REQUEST_METHOD'];
$uri    = $_SERVER['REQUEST_URI'];

// Support _method override for DELETE
if ($method === 'POST' && isset($_POST['_method'])) {
    $method = strtoupper($_POST['_method']);
}

$router->dispatch($method, $uri);
