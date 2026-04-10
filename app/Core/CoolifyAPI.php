<?php

namespace App\Core;

class CoolifyAPI
{
    private static function request(string $method, string $endpoint, array $data = []): array
    {
        $url = COOLIFY_HOST . '/api/v1' . $endpoint;
        $ch = curl_init();

        $headers = [
            'Authorization: Bearer ' . COOLIFY_API_KEY,
            'Content-Type: application/json',
            'Accept: application/json',
        ];

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_CUSTOMREQUEST  => strtoupper($method),
        ]);

        if (!empty($data) && in_array(strtoupper($method), ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['error' => $error, 'status' => 0];
        }

        $decoded = json_decode($response, true) ?? [];
        $decoded['_status'] = $httpCode;

        // Log en caso de error para diagnóstico
        if ($httpCode >= 400 || ($httpCode === 0 && $error)) {
            $logDir = defined('BASE_PATH') ? BASE_PATH . '/storage/logs' : __DIR__ . '/../../storage/logs';
            @file_put_contents(
                $logDir . '/coolify.log',
                '[' . date('Y-m-d H:i:s') . '] ' . $method . ' ' . $endpoint . ' → HTTP ' . $httpCode . ' | ' . $response . PHP_EOL,
                FILE_APPEND
            );
        }

        return $decoded;
    }

    // ---- Services ----

    public static function createService(string $botName, string $dockerImage, array $envVars = [], int $ramMb = 128): array
    {
        $envArray = [];
        foreach ($envVars as $key => $value) {
            $envArray[] = ['key' => $key, 'value' => $value];
        }

        return self::request('POST', '/services', [
            'type'               => 'docker-compose',
            'name'               => 'lanzabot-' . $botName,
            'project_uuid'       => COOLIFY_PROJECT_UUID,
            'server_uuid'        => COOLIFY_SERVER_UUID,
            'docker_compose_raw' => self::buildDockerCompose($botName, $dockerImage, $ramMb, $envVars),
        ]);
    }

    public static function createApplication(string $botName, string $dockerImage, array $envVars = [], int $ramMb = 128): array
    {
        $result = self::request('POST', '/applications/dockerimage', [
            'project_uuid'               => COOLIFY_PROJECT_UUID,
            'server_uuid'                => COOLIFY_SERVER_UUID,
            'environment_name'           => 'production',
            'name'                       => 'bot-' . $botName,
            'docker_registry_image_name' => $dockerImage,
            'ports_exposes'              => '8080',
            'instant_deploy'             => false,
            'limits_memory'              => $ramMb . 'm',
            'limits_cpus'                => '0.5',
        ]);

        // Si se creó correctamente, añadir las env vars
        if (!empty($result['uuid']) && !empty($envVars)) {
            self::updateEnvVars($result['uuid'], $envVars);
        }

        return $result;
    }

    public static function createPublicApplication(string $botName, string $gitRepoUrl, array $envVars = [], int $ramMb = 128, string $buildPack = 'nixpacks', string $gitBranch = 'main'): array
    {
        $result = self::request('POST', '/applications/public', [
            'project_uuid'     => COOLIFY_PROJECT_UUID,
            'server_uuid'      => COOLIFY_SERVER_UUID,
            'environment_name' => 'production',
            'name'             => 'bot-' . $botName,
            'git_repository'   => $gitRepoUrl,
            'git_branch'       => $gitBranch,
            'build_pack'       => $buildPack,
            'ports_exposes'    => '8080',
            'instant_deploy'   => false,
            'limits_memory'    => $ramMb . 'm',
            'limits_cpus'      => '0.5',
        ]);

        // Si se creó correctamente, añadir las env vars
        if (!empty($result['uuid']) && !empty($envVars)) {
            self::updateEnvVars($result['uuid'], $envVars);
        }

        return $result;
    }

    public static function getApplication(string $uuid): array
    {
        return self::request('GET', '/applications/' . $uuid);
    }

    public static function startApplication(string $uuid): array
    {
        return self::request('GET', '/applications/' . $uuid . '/start');
    }

    public static function stopApplication(string $uuid): array
    {
        return self::request('GET', '/applications/' . $uuid . '/stop');
    }

    public static function restartApplication(string $uuid): array
    {
        return self::request('GET', '/applications/' . $uuid . '/restart');
    }

    public static function deleteApplication(string $uuid): array
    {
        return self::request('DELETE', '/applications/' . $uuid);
    }

    public static function getLogs(string $uuid, int $lines = 100): array
    {
        return self::request('GET', '/applications/' . $uuid . '/logs?lines=' . $lines);
    }

    public static function updateEnvVars(string $uuid, array $envVars): array
    {
        $vars = [];
        foreach ($envVars as $key => $value) {
            $vars[] = ['key' => $key, 'value' => $value, 'is_shown_once' => false];
        }
        return self::request('PATCH', '/applications/' . $uuid . '/envs/bulk', ['data' => $vars]);
    }

    public static function deploy(string $uuid): array
    {
        return self::request('GET', '/applications/' . $uuid . '/deploy');
    }

    public static function getDeployments(string $appUuid): array
    {
        return self::request('GET', '/deployments/applications/' . $appUuid . '?take=5');
    }

    public static function getDeployment(string $deploymentUuid): array
    {
        return self::request('GET', '/deployments/' . $deploymentUuid);
    }

    public static function getResources(string $uuid): array
    {
        return self::request('GET', '/applications/' . $uuid);
    }

    // ---- Servers ----

    public static function getServers(): array
    {
        return self::request('GET', '/servers');
    }

    // ---- Docker Compose builder ----

    private static function buildDockerCompose(string $name, string $image, int $ramMb, array $envVars): string
    {
        $envLines = '';
        foreach ($envVars as $key => $value) {
            $envLines .= "      - {$key}={$value}\n";
        }

        return "version: '3.8'\nservices:\n  bot:\n    image: {$image}\n    restart: unless-stopped\n    mem_limit: {$ramMb}m\n    environment:\n{$envLines}";
    }
}
