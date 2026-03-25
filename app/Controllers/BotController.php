<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Core\CoolifyAPI;
use App\Models\Bot;

class BotController
{
    public function create(): void
    {
        Auth::require();
        $user = Auth::user();
        $plan = Auth::plan();

        $botCount = Bot::countForUser($user['id']);
        if ($plan['max_bots'] > 0 && $botCount >= $plan['max_bots']) {
            Auth::flash('error', 'Has alcanzado el límite de bots de tu plan. Actualiza tu plan para añadir más.');
            View::redirect('/plans');
        }

        View::render('bots/create', compact('user', 'plan'));
    }

    public function store(): void
    {
        Auth::require();
        $this->verifyCsrf();

        $user = Auth::user();
        $plan = Auth::plan();

        $name     = trim($_POST['name']     ?? '');
        $platform = $_POST['platform']      ?? 'telegram';
        $desc     = trim($_POST['description'] ?? '');
        $image    = trim($_POST['docker_image'] ?? 'python:3.11-slim');

        if (!$name) {
            Auth::flash('error', 'El nombre del bot es obligatorio.');
            View::redirect('/bots/create');
        }

        $botCount = Bot::countForUser($user['id']);
        if ($plan['max_bots'] > 0 && $botCount >= $plan['max_bots']) {
            Auth::flash('error', 'Límite de bots alcanzado.');
            View::redirect('/dashboard');
        }

        $botId = Bot::create([
            'user_id'      => $user['id'],
            'name'         => $name,
            'platform'     => $platform,
            'description'  => $desc,
            'docker_image' => $image,
        ]);

        Auth::flash('success', 'Bot creado. Ahora sube tu código y configura las variables de entorno.');
        View::redirect('/bots/' . $botId);
    }

    public function show(string $id): void
    {
        Auth::require();
        $user = Auth::user();
        $bot  = $this->getBot((int) $id, $user['id']);

        View::render('bots/show', compact('user', 'bot'));
    }

    public function upload(string $id): void
    {
        Auth::require();
        $this->verifyCsrf();

        $user = Auth::user();
        $bot  = $this->getBot((int) $id, $user['id']);

        if (empty($_FILES['code']) || $_FILES['code']['error'] !== UPLOAD_ERR_OK) {
            Auth::flash('error', 'Error al subir el archivo.');
            View::redirect('/bots/' . $id);
        }

        $file     = $_FILES['code'];
        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed  = ['zip', 'tar', 'gz'];

        if (!in_array($ext, $allowed)) {
            Auth::flash('error', 'Solo se permiten archivos .zip, .tar o .tar.gz.');
            View::redirect('/bots/' . $id);
        }

        if ($file['size'] > MAX_UPLOAD_SIZE) {
            Auth::flash('error', 'El archivo supera el límite de 50 MB.');
            View::redirect('/bots/' . $id);
        }

        $dir  = UPLOAD_PATH . '/' . $user['id'] . '/' . $bot['id'];
        if (!is_dir($dir)) mkdir($dir, 0750, true);

        $dest = $dir . '/code.' . $ext;
        move_uploaded_file($file['tmp_name'], $dest);

        Bot::update($bot['id'], ['code_uploaded' => 1, 'code_path' => $dest]);
        Auth::flash('success', 'Código subido correctamente.');
        View::redirect('/bots/' . $id);
    }

    public function saveEnv(string $id): void
    {
        Auth::require();
        $this->verifyCsrf();

        $user = Auth::user();
        $bot  = $this->getBot((int) $id, $user['id']);

        $raw  = trim($_POST['env_vars'] ?? '');
        $vars = [];

        foreach (explode("\n", $raw) as $line) {
            $line = trim($line);
            if (!$line || str_starts_with($line, '#')) continue;
            if (str_contains($line, '=')) {
                [$k, $v] = explode('=', $line, 2);
                $vars[trim($k)] = trim($v);
            }
        }

        Bot::setEnvVars($bot['id'], $vars);

        // Update in Coolify if already deployed
        if ($bot['coolify_app_uuid']) {
            CoolifyAPI::updateEnvVars($bot['coolify_app_uuid'], $vars);
        }

        Auth::flash('success', 'Variables de entorno guardadas.');
        View::redirect('/bots/' . $id);
    }

    public function deploy(string $id): void
    {
        Auth::require();
        $this->verifyCsrf();

        $user = Auth::user();
        $plan = Auth::plan();
        $bot  = $this->getBot((int) $id, $user['id']);

        try {
            $envVars = Bot::getEnvVars($bot['id']);
            $ramMb   = $plan['ram_mb'] ?? 128;

            // Create application in Coolify if not exists
            if (!$bot['coolify_app_uuid']) {
                $slug   = preg_replace('/[^a-z0-9]/', '-', strtolower($bot['name'])) . '-' . $bot['id'];
                $result = CoolifyAPI::createApplication($slug, $bot['docker_image'], $envVars, $ramMb);

                if (empty($result['uuid'])) {
                    throw new \RuntimeException('Coolify no devolvió UUID: ' . json_encode($result));
                }

                Bot::update($bot['id'], [
                    'coolify_app_uuid' => $result['uuid'],
                    'coolify_status'   => 'deploying',
                ]);
                $uuid = $result['uuid'];
            } else {
                $uuid = $bot['coolify_app_uuid'];
                CoolifyAPI::updateEnvVars($uuid, $envVars);
            }

            CoolifyAPI::deploy($uuid);
            Bot::update($bot['id'], ['coolify_status' => 'running']);
            Auth::flash('success', 'Bot desplegado correctamente.');
        } catch (\Exception $e) {
            Auth::flash('error', 'Error al desplegar: ' . $e->getMessage());
        }

        View::redirect('/bots/' . $id);
    }

    public function start(string $id): void
    {
        Auth::require();
        $this->verifyCsrf();
        $user = Auth::user();
        $bot  = $this->getBot((int) $id, $user['id']);

        if ($bot['coolify_app_uuid']) {
            CoolifyAPI::startApplication($bot['coolify_app_uuid']);
            Bot::update($bot['id'], ['coolify_status' => 'running']);
        }
        View::redirect('/bots/' . $id);
    }

    public function stop(string $id): void
    {
        Auth::require();
        $this->verifyCsrf();
        $user = Auth::user();
        $bot  = $this->getBot((int) $id, $user['id']);

        if ($bot['coolify_app_uuid']) {
            CoolifyAPI::stopApplication($bot['coolify_app_uuid']);
            Bot::update($bot['id'], ['coolify_status' => 'stopped']);
        }
        View::redirect('/bots/' . $id);
    }

    public function restart(string $id): void
    {
        Auth::require();
        $this->verifyCsrf();
        $user = Auth::user();
        $bot  = $this->getBot((int) $id, $user['id']);

        if ($bot['coolify_app_uuid']) {
            CoolifyAPI::restartApplication($bot['coolify_app_uuid']);
            Bot::update($bot['id'], ['coolify_status' => 'running']);
        }
        View::redirect('/bots/' . $id);
    }

    public function destroy(string $id): void
    {
        Auth::require();
        $this->verifyCsrf();
        $user = Auth::user();
        $bot  = $this->getBot((int) $id, $user['id']);

        if ($bot['coolify_app_uuid']) {
            CoolifyAPI::deleteApplication($bot['coolify_app_uuid']);
        }

        // Remove uploaded files
        $dir = UPLOAD_PATH . '/' . $user['id'] . '/' . $bot['id'];
        if (is_dir($dir)) {
            array_map('unlink', glob($dir . '/*'));
            rmdir($dir);
        }

        Bot::delete($bot['id']);
        Auth::flash('success', 'Bot eliminado.');
        View::redirect('/dashboard');
    }

    public function logs(string $id): void
    {
        Auth::require();
        $user = Auth::user();
        $bot  = $this->getBot((int) $id, $user['id']);

        if (!$bot['coolify_app_uuid']) {
            View::json(['logs' => '', 'error' => 'Bot no desplegado aún.']);
        }

        $result = CoolifyAPI::getLogs($bot['coolify_app_uuid'], 200);
        $logs   = $result['logs'] ?? ($result['data'] ?? '');

        if (is_array($logs)) {
            $logs = implode("\n", array_map(fn($l) => $l['message'] ?? $l, $logs));
        }

        View::json(['logs' => $logs]);
    }

    public function stats(string $id): void
    {
        Auth::require();
        $user = Auth::user();
        $bot  = $this->getBot((int) $id, $user['id']);

        if (!$bot['coolify_app_uuid']) {
            View::json(['error' => 'Bot no desplegado aún.']);
        }

        $result = CoolifyAPI::getResources($bot['coolify_app_uuid']);
        View::json([
            'status' => $result['status'] ?? $bot['coolify_status'],
            'data'   => $result,
        ]);
    }

    // ---- Helpers ----

    private function getBot(int $id, int $userId): array
    {
        $bot = Bot::findForUser($id, $userId);
        if (!$bot) {
            http_response_code(404);
            exit('Bot no encontrado.');
        }
        return $bot;
    }

    private function verifyCsrf(): void
    {
        $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!Auth::verifyCsrf($token)) {
            http_response_code(403);
            exit('Token CSRF inválido.');
        }
    }
}
