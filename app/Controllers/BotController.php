<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Core\CoolifyAPI;
use App\Core\StarterCode;
use App\Models\Bot;
use App\Models\BotTemplate;

class BotController
{
    private static array $planOrder = [
        'free' => 0, 'starter' => 1, 'medium' => 2, 'pro' => 3, 'custom' => 4
    ];

    public function create(): void
    {
        Auth::require();
        $user = Auth::user();
        $plan = Auth::plan();
        $isAdmin = (int)$user['id'] === 1;

        $botCount     = Bot::countForUser($user['id']);
        $canCreateMore = $isAdmin || $plan['max_bots'] <= 0 || $botCount < $plan['max_bots'];
        $templates    = BotTemplate::all();
        $planOrder    = self::$planOrder;
        $userPlanOrder = $planOrder[$plan['slug']] ?? 0;

        View::render('bots/create', compact(
            'user', 'plan', 'templates', 'botCount',
            'isAdmin', 'canCreateMore', 'planOrder', 'userPlanOrder'
        ));
    }

    public function fromTemplate(string $id): void
    {
        Auth::require();
        $user = Auth::user();
        $plan = Auth::plan();
        $isAdmin = (int)$user['id'] === 1;

        $template = BotTemplate::find((int)$id);
        if (!$template) {
            Auth::flash('error', 'Plantilla no encontrada.');
            View::redirect('/bots/create');
        }

        // Verificar plan mínimo
        $userPlanOrder = self::$planOrder[$plan['slug']] ?? 0;
        $reqPlanOrder  = self::$planOrder[$template['min_plan_slug']] ?? 0;
        if (!$isAdmin && $userPlanOrder < $reqPlanOrder) {
            Auth::flash('error', 'Necesitas un plan ' . ucfirst($template['min_plan_slug']) . ' o superior para esta plantilla.');
            View::redirect('/plans');
        }

        // Verificar límite de bots
        $botCount = Bot::countForUser($user['id']);
        if (!$isAdmin && $plan['max_bots'] > 0 && $botCount >= $plan['max_bots']) {
            Auth::flash('error', 'Has alcanzado el límite de bots de tu plan.');
            View::redirect('/plans');
        }

        $requiredVars = json_decode($template['required_env_vars'], true) ?? [];
        $defaultVars  = json_decode($template['default_env_vars'], true) ?? [];

        View::render('bots/setup-template', compact(
            'user', 'plan', 'template', 'requiredVars', 'defaultVars', 'isAdmin'
        ));
    }

    public function storeFromTemplate(string $id): void
    {
        Auth::require();
        $this->verifyCsrf();

        $user = Auth::user();
        $plan = Auth::plan();
        $isAdmin = (int)$user['id'] === 1;

        $template = BotTemplate::find((int)$id);
        if (!$template) {
            Auth::flash('error', 'Plantilla no encontrada.');
            View::redirect('/bots/create');
        }

        // Verificar permisos
        $userPlanOrder = self::$planOrder[$plan['slug']] ?? 0;
        $reqPlanOrder  = self::$planOrder[$template['min_plan_slug']] ?? 0;
        if (!$isAdmin && $userPlanOrder < $reqPlanOrder) {
            Auth::flash('error', 'Plan insuficiente para esta plantilla.');
            View::redirect('/plans');
        }

        $botCount = Bot::countForUser($user['id']);
        if (!$isAdmin && $plan['max_bots'] > 0 && $botCount >= $plan['max_bots']) {
            Auth::flash('error', 'Límite de bots alcanzado.');
            View::redirect('/dashboard');
        }

        // Recoger nombre personalizado
        $botName = trim($_POST['bot_name'] ?? $template['name']);
        if (!$botName) $botName = $template['name'];

        // Recoger variables de entorno
        $requiredVars = json_decode($template['required_env_vars'], true) ?? [];
        $defaultVars  = json_decode($template['default_env_vars'], true) ?? [];
        $envVars = $defaultVars;

        foreach ($requiredVars as $varDef) {
            $key   = $varDef['key'] ?? '';
            $value = trim($_POST['env_' . $key] ?? '');
            if (!empty($varDef['required']) && $value === '') {
                Auth::flash('error', 'El campo "' . ($varDef['label'] ?? $key) . '" es obligatorio.');
                View::redirect('/bots/from-template/' . $id);
            }
            if ($value !== '') {
                $envVars[$key] = $value;
            }
        }

        // Crear bot en BD
        $botId = Bot::create([
            'user_id'         => $user['id'],
            'name'            => $botName,
            'platform'        => $template['platform'],
            'description'     => $template['short_description'],
            'docker_image'    => $template['docker_image'],
            'template_id'     => $template['id'],
            'auto_update'     => 1,
            'current_version' => $template['version'] ?? '1.0.0',
        ]);

        // Guardar env vars
        Bot::setEnvVars($botId, $envVars);

        // Desplegar en Coolify
        $deployed = false;
        $deployError = '';
        try {
            $ramMb = $plan['ram_mb'] ?? 128;
            $slug  = preg_replace('/[^a-z0-9]/', '-', strtolower($botName)) . '-' . $botId;

            // Si el template tiene repositorio git, desplegar desde repo público;
            // si tiene start_command (framework), desplegar con Dockerfile inline;
            // si no, desplegar como imagen Docker directa (ej. n8nio/n8n:latest)
            $gitRepoUrl     = $template['git_repo_url'] ?? null;
            $gitBranch      = $template['git_branch'] ?? 'main';
            $installCommand = $template['install_command'] ?? null;
            $startCommand   = $template['start_command'] ?? null;
            if (!empty($gitRepoUrl)) {
                $result = CoolifyAPI::createPublicApplication($slug, $gitRepoUrl, $envVars, $ramMb, 'nixpacks', $gitBranch, $installCommand);
            } elseif (!empty($startCommand)) {
                $dockerfile = $this->buildFrameworkDockerfile($template);
                $result = CoolifyAPI::createDockerfileApplication($slug, $dockerfile, $envVars, $ramMb);
            } else {
                $result = CoolifyAPI::createApplication($slug, $template['docker_image'], $envVars, $ramMb);
            }

            if (!empty($result['uuid'])) {
                Bot::update($botId, [
                    'coolify_app_uuid' => $result['uuid'],
                    'coolify_status'   => 'deploying',
                ]);

                // Configurar almacenamiento y código inicial si la plantilla lo requiere
                $this->setupStorage($result['uuid'], $botId, $template);

                CoolifyAPI::deploy($result['uuid']);
                $deployed = true;
            } else {
                $apiMsg = $result['message'] ?? $result['error'] ?? null;
                $httpCode = $result['_status'] ?? '';
                if ($apiMsg) {
                    $deployError = 'Coolify error ' . $httpCode . ': ' . $apiMsg;
                } else {
                    $deployError = 'Coolify no devolvió UUID (HTTP ' . $httpCode . '). Respuesta: ' . json_encode(array_diff_key($result, ['_status' => 0]));
                }
            }
        } catch (\Exception $e) {
            $deployError = $e->getMessage();
        }

        BotTemplate::incrementInstallCount($template['id']);

        $bot = Bot::find($botId);
        $setupInstructions = $template['setup_instructions'] ?? '';
        $docUrl = $template['documentation_url'] ?? '';

        View::render('bots/deployed', compact(
            'user', 'bot', 'template', 'deployed', 'deployError',
            'setupInstructions', 'docUrl', 'envVars'
        ));
    }

    public function show(string $id): void
    {
        Auth::require();
        $user = Auth::user();
        $bot  = $this->getBot((int) $id, $user['id']);

        View::render('bots/show', compact('user', 'bot'));
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

                // Si el bot viene de un template con repositorio git, desplegar desde repo
                $gitRepoUrl = null;
                $gitBranch = 'main';
                $installCommand = null;
                $startCommand = null;
                $tpl = null;
                if (!empty($bot['template_id'])) {
                    $tpl = BotTemplate::find((int)$bot['template_id']);
                    $gitRepoUrl     = $tpl['git_repo_url'] ?? null;
                    $gitBranch      = $tpl['git_branch'] ?? 'main';
                    $installCommand = $tpl['install_command'] ?? null;
                    $startCommand   = $tpl['start_command'] ?? null;
                }

                if (!empty($gitRepoUrl)) {
                    $result = CoolifyAPI::createPublicApplication($slug, $gitRepoUrl, $envVars, $ramMb, 'nixpacks', $gitBranch, $installCommand);
                } elseif (!empty($startCommand) && $tpl) {
                    $dockerfile = $this->buildFrameworkDockerfile($tpl);
                    $result = CoolifyAPI::createDockerfileApplication($slug, $dockerfile, $envVars, $ramMb);
                } else {
                    $result = CoolifyAPI::createApplication($slug, $bot['docker_image'], $envVars, $ramMb);
                }

                if (empty($result['uuid'])) {
                    throw new \RuntimeException('Coolify no devolvió UUID: ' . json_encode($result));
                }

                Bot::update($bot['id'], [
                    'coolify_app_uuid' => $result['uuid'],
                    'coolify_status'   => 'deploying',
                ]);

                // Configurar almacenamiento y código inicial si el bot viene de una plantilla
                if (!empty($bot['template_id'])) {
                    $this->setupStorage($result['uuid'], $bot['id'], $tpl ?? BotTemplate::find((int)$bot['template_id']));
                }

                $uuid = $result['uuid'];
            } else {
                $uuid = $bot['coolify_app_uuid'];
                CoolifyAPI::updateEnvVars($uuid, $envVars);
            }

            CoolifyAPI::deploy($uuid);
            Bot::update($bot['id'], ['coolify_status' => 'deploying']);
            Auth::flash('success', 'Despliegue iniciado. El estado se actualizará automáticamente.');
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
            Bot::update($bot['id'], ['coolify_status' => 'starting']);
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
            Bot::update($bot['id'], ['coolify_status' => 'stopping']);
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
            CoolifyAPI::stopApplication($bot['coolify_app_uuid']);
            sleep(2);
            CoolifyAPI::startApplication($bot['coolify_app_uuid']);
            Bot::update($bot['id'], ['coolify_status' => 'starting']);
        }
        View::redirect('/bots/' . $id);
    }

    public function confirmDelete(string $id): void
    {
        Auth::require();
        $user = Auth::user();
        $bot  = $this->getBot((int) $id, $user['id']);

        View::render('bots/confirm-delete', compact('user', 'bot'));
    }

    public function destroy(string $id): void
    {
        Auth::require();
        $this->verifyCsrf();
        $user = Auth::user();
        $bot  = $this->getBot((int) $id, $user['id']);

        // Eliminar aplicación en Coolify (sin borrar volúmenes ni BBDDs externas)
        if ($bot['coolify_app_uuid']) {
            CoolifyAPI::deleteApplication($bot['coolify_app_uuid']);
        }

        // Eliminar archivos de código subidos por el usuario
        $dir = UPLOAD_PATH . '/' . $user['id'] . '/' . $bot['id'];
        if (is_dir($dir)) {
            $files = glob($dir . '/*') ?: [];
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($dir);
        }

        // Borrar registro de la BD (env_vars incluidas al estar en la misma fila)
        Bot::delete($bot['id']);

        Auth::flash('success', 'Bot «' . $bot['name'] . '» eliminado correctamente.');
        View::redirect('/dashboard');
    }

    public function logs(string $id): void
    {
        Auth::require();
        $user = Auth::user();
        $bot  = $this->getBot((int) $id, $user['id']);

        if (!$bot['coolify_app_uuid']) {
            View::json(['logs' => '', 'error' => 'Bot no desplegado aún.']);
            return;
        }

        $result = CoolifyAPI::getLogs($bot['coolify_app_uuid'], 200);
        $logs   = $result['logs'] ?? ($result['data'] ?? '');

        if (is_array($logs)) {
            $logs = implode("\n", array_map(fn($l) => is_array($l) ? ($l['message'] ?? json_encode($l)) : $l, $logs));
        }

        // Si no hay logs de contenedor, intentar obtener info del último despliegue
        if (empty(trim((string)$logs))) {
            $deployments = CoolifyAPI::getDeployments($bot['coolify_app_uuid']);
            if (!empty($deployments) && is_array($deployments)) {
                // Buscar el último despliegue
                $latest = null;
                foreach ($deployments as $d) {
                    if (is_array($d) && isset($d['deployment_uuid'])) {
                        $latest = $d;
                        break;
                    }
                }
                if ($latest) {
                    $depDetail = CoolifyAPI::getDeployment($latest['deployment_uuid']);
                    $depLogs = $depDetail['logs'] ?? '';
                    if ($depLogs) {
                        $logs = "=== Logs del despliegue (" . ($latest['status'] ?? '?') . ") ===\n" . $depLogs;
                    } else {
                        $logs = "Estado del despliegue: " . ($latest['status'] ?? 'desconocido') . "\nAún no hay logs disponibles.";
                    }
                }
            }
        }

        if (empty(trim((string)$logs))) {
            $logs = 'No hay logs disponibles. El bot puede estar iniciándose o en un ciclo de reinicio.';
        }

        View::json(['logs' => $logs]);
    }

    public function stats(string $id): void
    {
        Auth::require();
        $user = Auth::user();
        $bot  = $this->getBot((int) $id, $user['id']);

        if (!$bot['coolify_app_uuid']) {
            View::json(['error' => 'Bot no desplegado aún.', 'status' => 'not_deployed']);
            return;
        }

        $result = CoolifyAPI::getApplication($bot['coolify_app_uuid']);
        $realStatus = $result['status'] ?? null;

        // Sincronizar estado real de Coolify con la BD
        if ($realStatus && $realStatus !== ($bot['coolify_status'] ?? '')) {
            Bot::update($bot['id'], ['coolify_status' => $realStatus]);
        }

        View::json([
            'status'    => $realStatus ?: ($bot['coolify_status'] ?? 'unknown'),
            'http_code' => $result['_status'] ?? null,
        ]);
    }

    public function toggleAutoUpdate(string $id): void
    {
        Auth::require();
        $this->verifyCsrf();
        $user = Auth::user();
        $bot  = $this->getBot((int) $id, $user['id']);

        $newValue = $bot['auto_update'] ? 0 : 1;
        Bot::update($bot['id'], ['auto_update' => $newValue]);
        Auth::flash('success', $newValue ? 'Auto-actualización activada.' : 'Auto-actualización desactivada.');
        View::redirect('/bots/' . $id);
    }

    public function updateBot(string $id): void
    {
        Auth::require();
        $this->verifyCsrf();
        $user = Auth::user();
        $plan = Auth::plan();
        $bot  = $this->getBot((int) $id, $user['id']);

        if (!$bot['template_id']) {
            Auth::flash('error', 'Este bot no está vinculado a una plantilla.');
            View::redirect('/bots/' . $id);
            return;
        }

        $template = BotTemplate::find($bot['template_id']);
        if (!$template) {
            Auth::flash('error', 'Plantilla no encontrada.');
            View::redirect('/bots/' . $id);
            return;
        }

        // Verificar si hay actualización disponible
        if (version_compare($template['version'] ?? '1.0.0', $bot['current_version'] ?? '1.0.0', '<=')) {
            Auth::flash('info', 'Tu bot ya tiene la última versión.');
            View::redirect('/bots/' . $id);
            return;
        }

        try {
            if ($bot['coolify_app_uuid']) {
                // Actualizar imagen Docker si cambió
                if ($bot['docker_image'] !== $template['docker_image']) {
                    Bot::update($bot['id'], ['docker_image' => $template['docker_image']]);
                }

                // Re-desplegar con la nueva versión
                CoolifyAPI::deploy($bot['coolify_app_uuid']);
                Bot::update($bot['id'], [
                    'current_version'  => $template['version'],
                    'last_updated_at'  => date('Y-m-d H:i:s'),
                    'coolify_status'   => 'deploying',
                ]);
                Auth::flash('success', 'Bot actualizado a la versión ' . $template['version'] . '.');
            } else {
                Auth::flash('error', 'El bot no está desplegado aún.');
            }
        } catch (\Exception $e) {
            Auth::flash('error', 'Error al actualizar: ' . $e->getMessage());
        }

        View::redirect('/bots/' . $id);
    }

    public function checkUpdates(): void
    {
        Auth::require();
        $user = Auth::user();

        $bots = Bot::forUser($user['id']);
        $updates = [];
        foreach ($bots as $bot) {
            if (!$bot['template_id']) continue;
            $template = BotTemplate::find($bot['template_id']);
            if (!$template) continue;
            if (version_compare($template['version'] ?? '1.0.0', $bot['current_version'] ?? '1.0.0', '>')) {
                $updates[] = [
                    'bot_id'          => $bot['id'],
                    'bot_name'        => $bot['name'],
                    'current_version' => $bot['current_version'] ?? '1.0.0',
                    'new_version'     => $template['version'],
                    'auto_update'     => (bool)$bot['auto_update'],
                ];
            }
        }

        View::json(['updates' => $updates, 'count' => count($updates)]);
    }

    // ---- Gestor de archivos ----

    public function files(string $id): void
    {
        Auth::require();
        $user = Auth::user();
        $bot  = $this->getBot((int) $id, $user['id']);

        if (!$bot['coolify_app_uuid']) {
            Auth::flash('error', 'El bot debe estar desplegado para gestionar archivos.');
            View::redirect('/bots/' . $id);
            return;
        }

        $storages = CoolifyAPI::listStorages($bot['coolify_app_uuid']);
        $fileStorages       = $storages['file_storages'] ?? [];
        $persistentStorages = $storages['persistent_storages'] ?? [];

        // Determinar archivo activo (seleccionado o el primero)
        $activeUuid = $_GET['file'] ?? null;
        $activeFile = null;
        if ($activeUuid) {
            foreach ($fileStorages as $fs) {
                if (($fs['uuid'] ?? '') === $activeUuid) {
                    $activeFile = $fs;
                    break;
                }
            }
        }
        if (!$activeFile && !empty($fileStorages)) {
            $activeFile = $fileStorages[0];
        }

        $template = null;
        if ($bot['template_id']) {
            $template = BotTemplate::find((int) $bot['template_id']);
        }

        View::render('bots/files', compact(
            'user', 'bot', 'fileStorages', 'persistentStorages',
            'activeFile', 'template'
        ));
    }

    public function createFile(string $id): void
    {
        Auth::require();
        $this->verifyCsrf();
        $user = Auth::user();
        $bot  = $this->getBot((int) $id, $user['id']);

        if (!$bot['coolify_app_uuid']) {
            Auth::flash('error', 'Bot no desplegado.');
            View::redirect('/bots/' . $id);
            return;
        }

        $mountPath = trim($_POST['mount_path'] ?? '');
        $content   = $_POST['content'] ?? '';

        // Validar ruta
        if (!$mountPath || $mountPath[0] !== '/') {
            Auth::flash('error', 'La ruta debe ser absoluta (empezar con /).');
            View::redirect('/bots/' . $id . '/files');
            return;
        }

        // Si se subió un archivo, leer su contenido
        if (!empty($_FILES['upload_file']['tmp_name']) && $_FILES['upload_file']['error'] === UPLOAD_ERR_OK) {
            $content = file_get_contents($_FILES['upload_file']['tmp_name']);
            if ($mountPath === '/' || substr($mountPath, -1) === '/') {
                $mountPath = rtrim($mountPath, '/') . '/' . basename($_FILES['upload_file']['name']);
            }
        }

        $result = CoolifyAPI::createFileStorage($bot['coolify_app_uuid'], $mountPath, $content);
        if (!empty($result['uuid']) || (($result['_status'] ?? 0) >= 200 && ($result['_status'] ?? 0) < 300)) {
            Auth::flash('success', 'Archivo creado. Reinicia el bot para aplicar cambios.');
        } else {
            Auth::flash('error', 'Error al crear archivo: ' . ($result['message'] ?? json_encode($result)));
        }

        View::redirect('/bots/' . $id . '/files');
    }

    public function updateFile(string $id): void
    {
        Auth::require();
        $this->verifyCsrf();
        $user = Auth::user();
        $bot  = $this->getBot((int) $id, $user['id']);

        if (!$bot['coolify_app_uuid']) {
            Auth::flash('error', 'Bot no desplegado.');
            View::redirect('/bots/' . $id);
            return;
        }

        $storageUuid = $_POST['storage_uuid'] ?? '';
        $mountPath   = $_POST['mount_path'] ?? '';
        $content     = $_POST['content'] ?? '';

        if (!$storageUuid || !$mountPath) {
            Auth::flash('error', 'Archivo no especificado.');
            View::redirect('/bots/' . $id . '/files');
            return;
        }

        // La API PATCH de Coolify actualiza el contenido en BD pero NO lo escribe
        // al disco del host (bug en Coolify). Workaround: DELETE + CREATE.
        // El POST (create) sí llama a saveStorageOnServer() y escribe al disco.
        CoolifyAPI::deleteStorage($bot['coolify_app_uuid'], $storageUuid);
        $result = CoolifyAPI::createFileStorage($bot['coolify_app_uuid'], $mountPath, $content);

        if (!empty($result['uuid']) || (($result['_status'] ?? 0) >= 200 && ($result['_status'] ?? 0) < 300)) {
            // Reiniciar el bot para que use el archivo actualizado.
            // No necesita rebuild completo — el archivo ya está en disco.
            CoolifyAPI::stopApplication($bot['coolify_app_uuid']);
            sleep(2);
            CoolifyAPI::startApplication($bot['coolify_app_uuid']);
            Bot::update($bot['id'], ['coolify_status' => 'starting']);
            Auth::flash('success', 'Archivo guardado y bot reiniciando con los cambios.');
            $newUuid = $result['uuid'] ?? '';
        } else {
            Auth::flash('error', 'Error al guardar: ' . ($result['message'] ?? json_encode($result)));
            $newUuid = '';
        }

        View::redirect('/bots/' . $id . '/files' . ($newUuid ? '?file=' . urlencode($newUuid) : ''));
    }

    public function deleteFile(string $id): void
    {
        Auth::require();
        $this->verifyCsrf();
        $user = Auth::user();
        $bot  = $this->getBot((int) $id, $user['id']);

        if (!$bot['coolify_app_uuid']) {
            Auth::flash('error', 'Bot no desplegado.');
            View::redirect('/bots/' . $id);
            return;
        }

        $storageUuid = $_POST['storage_uuid'] ?? '';
        if (!$storageUuid) {
            Auth::flash('error', 'Archivo no especificado.');
            View::redirect('/bots/' . $id . '/files');
            return;
        }

        $result = CoolifyAPI::deleteStorage($bot['coolify_app_uuid'], $storageUuid);
        if (($result['_status'] ?? 0) >= 200 && ($result['_status'] ?? 0) < 300) {
            Auth::flash('success', 'Archivo eliminado. Reinicia el bot para aplicar cambios.');
        } else {
            Auth::flash('error', 'Error al eliminar: ' . ($result['message'] ?? json_encode($result)));
        }

        View::redirect('/bots/' . $id . '/files');
    }

    // ---- Helpers ----

    /**
     * Configura almacenamiento persistente y código inicial para un bot recién creado en Coolify.
     */
    private function setupStorage(string $appUuid, int $botId, ?array $template): void
    {
        if (!$template) return;

        // Crear volumen persistente si la plantilla lo requiere
        if (!empty($template['needs_storage']) && !empty($template['storage_mount_path'])) {
            $storageName = 'bot-' . $botId . '-data';
            CoolifyAPI::createPersistentStorage($appUuid, $template['storage_mount_path'], $storageName);
        }

        // Inyectar código inicial si es un bot framework
        if (!empty($template['starter_filename'])) {
            $starterCode = StarterCode::get($template['slug'] ?? '');
            if ($starterCode) {
                CoolifyAPI::createFileStorage($appUuid, '/app/' . $template['starter_filename'], $starterCode);
            }
        }
    }

    /**
     * Genera un Dockerfile inline para bots de tipo framework.
     * Solo define la imagen base, directorio de trabajo y CMD.
     * El código inicial se inyecta mediante file storage de Coolify.
     */
    private function buildFrameworkDockerfile(array $template): string
    {
        $image    = $template['docker_image'] ?? 'python:3.11-slim';
        $startCmd = $template['start_command'];

        $lines = [];
        $lines[] = 'FROM ' . $image;
        $lines[] = 'WORKDIR /app';
        $lines[] = 'CMD ["sh", "-c", ' . json_encode($startCmd, JSON_UNESCAPED_SLASHES) . ']';

        return implode("\n", $lines) . "\n";
    }

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
