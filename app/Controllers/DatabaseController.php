<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Models\UserDatabase;

class DatabaseController
{
    // =========================================================================
    // Listado
    // =========================================================================

    public function index(): void
    {
        Auth::require();
        $user      = Auth::user();
        $plan      = Auth::plan();
        $databases = UserDatabase::forUser($user['id']);

        View::render('databases/index', compact('user', 'plan', 'databases'));
    }

    // =========================================================================
    // Crear
    // =========================================================================

    public function create(): void
    {
        Auth::require();
        $user = Auth::user();
        $plan = Auth::plan();
        $isAdmin = (int)$user['id'] === 1;

        $dbCount = UserDatabase::countForUser($user['id']);
        $maxDbs  = (int)($plan['max_databases'] ?? 0);
        $canCreate = $isAdmin || $maxDbs < 0 || ($maxDbs > 0 && $dbCount < $maxDbs);

        $pgEnabled    = SHARED_PG_HOST !== '';
        $mongoEnabled = SHARED_MONGO_HOST !== '';

        View::render('databases/create', compact(
            'user', 'plan', 'dbCount', 'maxDbs', 'canCreate',
            'pgEnabled', 'mongoEnabled'
        ));
    }

    public function store(): void
    {
        Auth::require();
        $this->verifyCsrf();

        $user    = Auth::user();
        $plan    = Auth::plan();
        $isAdmin = (int)$user['id'] === 1;

        // Validar cuota
        $dbCount = UserDatabase::countForUser($user['id']);
        $maxDbs  = (int)($plan['max_databases'] ?? 0);
        if (!$isAdmin && $maxDbs === 0) {
            Auth::flash('error', 'Tu plan no incluye bases de datos. Actualiza a Starter o superior.');
            View::redirect('/plans');
        }
        if (!$isAdmin && $maxDbs > 0 && $dbCount >= $maxDbs) {
            Auth::flash('error', 'Has alcanzado el límite de bases de datos de tu plan.');
            View::redirect('/databases');
        }

        $type  = $_POST['type'] ?? '';
        $label = trim($_POST['label'] ?? '');

        if (!in_array($type, ['postgresql', 'mongodb'], true)) {
            Auth::flash('error', 'Tipo de base de datos no válido.');
            View::redirect('/databases/create');
        }
        if ($label === '') {
            Auth::flash('error', 'El nombre es obligatorio.');
            View::redirect('/databases/create');
        }
        if (strlen($label) > 60) {
            Auth::flash('error', 'El nombre no puede superar 60 caracteres.');
            View::redirect('/databases/create');
        }

        if ($type === 'postgresql' && SHARED_PG_HOST === '') {
            Auth::flash('error', 'El servidor PostgreSQL compartido no está configurado.');
            View::redirect('/databases/create');
        }
        if ($type === 'mongodb' && SHARED_MONGO_HOST === '') {
            Auth::flash('error', 'El servidor MongoDB compartido no está configurado.');
            View::redirect('/databases/create');
        }

        // 1. Insertar registro en "creating"
        $dbId = UserDatabase::createPending($user['id'], $label, $type);

        // 2. Determinar nombres únicos basados en el ID de fila
        $resourceName = UserDatabase::resourceName($dbId);
        $password     = bin2hex(random_bytes(16)); // 32 chars hex, sin chars especiales

        // 3. Crear el recurso en el servidor compartido
        try {
            if ($type === 'postgresql') {
                $this->provisionPostgres($resourceName, $password);
                $host = SHARED_PG_HOST;
                $port = SHARED_PG_PORT;
            } else {
                $this->provisionMongo($resourceName, $password);
                $host = SHARED_MONGO_HOST;
                $port = SHARED_MONGO_PORT;
            }

            UserDatabase::activate($dbId, [
                'db_name'          => $resourceName,
                'db_user'          => $resourceName,
                'db_password_enc'  => UserDatabase::encryptPassword($password),
                'db_host'          => $host,
                'db_port'          => $port,
            ]);

            Auth::flash('success', 'Base de datos "' . $label . '" creada correctamente.');
            View::redirect('/databases/' . $dbId);
        } catch (\Exception $e) {
            UserDatabase::setError($dbId, $e->getMessage());
            $this->log('create-db', $e->getMessage());
            Auth::flash('error', 'Error al crear la base de datos: ' . $e->getMessage());
            View::redirect('/databases');
        }
    }

    // =========================================================================
    // Ver detalle
    // =========================================================================

    public function show(string $id): void
    {
        Auth::require();
        $user = Auth::user();
        $db   = UserDatabase::findForUser((int)$id, $user['id']);
        if (!$db) {
            Auth::flash('error', 'Base de datos no encontrada.');
            View::redirect('/databases');
        }

        $password = $db['status'] === 'active'
            ? UserDatabase::decryptPassword($db['db_password_enc'])
            : null;

        View::render('databases/show', compact('user', 'db', 'password'));
    }

    // =========================================================================
    // Regenerar contraseña
    // =========================================================================

    public function regeneratePassword(string $id): void
    {
        Auth::require();
        $this->verifyCsrf();

        $user = Auth::user();
        $db   = UserDatabase::findForUser((int)$id, $user['id']);
        if (!$db || $db['status'] !== 'active') {
            Auth::flash('error', 'Base de datos no encontrada o no activa.');
            View::redirect('/databases');
        }

        $newPass = bin2hex(random_bytes(16));

        try {
            if ($db['type'] === 'postgresql') {
                $this->changePostgresPassword($db['db_user'], $newPass);
            } else {
                $this->changeMongoPassword($db['db_name'], $db['db_user'], $newPass);
            }

            UserDatabase::updatePassword((int)$id, UserDatabase::encryptPassword($newPass));
            Auth::flash('success', 'Contraseña regenerada correctamente.');
        } catch (\Exception $e) {
            $this->log('change-password', $e->getMessage());
            Auth::flash('error', 'Error al cambiar la contraseña: ' . $e->getMessage());
        }

        View::redirect('/databases/' . $id);
    }

    // =========================================================================
    // Exportar
    // =========================================================================

    public function export(string $id): void
    {
        Auth::require();
        $user = Auth::user();
        $db   = UserDatabase::findForUser((int)$id, $user['id']);
        if (!$db || $db['status'] !== 'active') {
            Auth::flash('error', 'Base de datos no encontrada o no activa.');
            View::redirect('/databases');
        }

        $pass = UserDatabase::decryptPassword($db['db_password_enc']);

        try {
            if ($db['type'] === 'postgresql') {
                $this->exportPostgres($db, $pass);
            } else {
                $this->exportMongo($db, $pass);
            }
        } catch (\Exception $e) {
            $this->log('export', $e->getMessage());
            Auth::flash('error', 'Error en la exportación: ' . $e->getMessage());
            View::redirect('/databases/' . $id);
        }
    }

    // =========================================================================
    // Importar
    // =========================================================================

    public function import(string $id): void
    {
        Auth::require();
        $this->verifyCsrf();

        $user = Auth::user();
        $db   = UserDatabase::findForUser((int)$id, $user['id']);
        if (!$db || $db['status'] !== 'active') {
            Auth::flash('error', 'Base de datos no encontrada o no activa.');
            View::redirect('/databases');
        }

        if (empty($_FILES['dump_file']['tmp_name'])) {
            Auth::flash('error', 'No se recibió ningún archivo.');
            View::redirect('/databases/' . $id);
        }

        $tmpFile = $_FILES['dump_file']['tmp_name'];
        $origName = basename($_FILES['dump_file']['name'] ?? 'dump');
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

        // Validar extensión según tipo
        $allowedExt = $db['type'] === 'postgresql'
            ? ['sql', 'dump', 'gz']
            : ['gz', 'archive', 'bson'];

        if (!in_array($ext, $allowedExt, true)) {
            Auth::flash('error', 'Formato de archivo no permitido para este tipo de base de datos.');
            View::redirect('/databases/' . $id);
        }

        // Mover a directorio seguro fuera de public/
        $importDir = defined('BASE_PATH') ? BASE_PATH . '/uploads/db_imports' : dirname(__DIR__, 2) . '/uploads/db_imports';
        if (!is_dir($importDir)) {
            mkdir($importDir, 0750, true);
        }
        $safeFile = $importDir . '/' . $db['db_name'] . '_import_' . time() . '.' . $ext;

        if (!move_uploaded_file($tmpFile, $safeFile)) {
            Auth::flash('error', 'No se pudo guardar el archivo subido.');
            View::redirect('/databases/' . $id);
        }

        $pass = UserDatabase::decryptPassword($db['db_password_enc']);

        try {
            if ($db['type'] === 'postgresql') {
                $this->importPostgres($db, $pass, $safeFile, $ext);
            } else {
                $this->importMongo($db, $pass, $safeFile);
            }
            Auth::flash('success', 'Importación completada correctamente.');
        } catch (\Exception $e) {
            $this->log('import', $e->getMessage());
            Auth::flash('error', 'Error en la importación: ' . $e->getMessage());
        } finally {
            @unlink($safeFile);
        }

        View::redirect('/databases/' . $id);
    }

    // =========================================================================
    // Eliminar (confirmación fuerte)
    // =========================================================================

    public function confirmDelete(string $id): void
    {
        Auth::require();
        $user = Auth::user();
        $db   = UserDatabase::findForUser((int)$id, $user['id']);
        if (!$db) {
            Auth::flash('error', 'Base de datos no encontrada.');
            View::redirect('/databases');
        }

        View::render('databases/confirm-delete', compact('user', 'db'));
    }

    public function destroy(string $id): void
    {
        Auth::require();
        $this->verifyCsrf();

        $user = Auth::user();
        $db   = UserDatabase::findForUser((int)$id, $user['id']);
        if (!$db) {
            Auth::flash('error', 'Base de datos no encontrada.');
            View::redirect('/databases');
        }

        // Confirmación fuerte: el usuario debe escribir el label exacto
        $typed = trim($_POST['confirm_name'] ?? '');
        if ($typed !== $db['label']) {
            Auth::flash('error', 'El nombre escrito no coincide. Eliminación cancelada.');
            View::redirect('/databases/' . $id . '/delete');
        }

        $pass = $db['status'] === 'active'
            ? UserDatabase::decryptPassword($db['db_password_enc'])
            : '';

        // Eliminar el recurso en el servidor compartido (si está activo)
        if ($db['status'] === 'active') {
            try {
                if ($db['type'] === 'postgresql') {
                    $this->dropPostgres($db['db_name'], $db['db_user']);
                } else {
                    $this->dropMongo($db['db_name'], $db['db_user']);
                }
            } catch (\Exception $e) {
                // No interrumpir la eliminación del registro aunque el drop falle
                $this->log('drop-db', $e->getMessage());
            }
        }

        UserDatabase::delete((int)$id);
        Auth::flash('success', 'Base de datos "' . $db['label'] . '" eliminada.');
        View::redirect('/databases');
    }

    // =========================================================================
    // Helpers de aprovisionamiento — PostgreSQL
    // =========================================================================

    /**
     * @throws \RuntimeException
     */
    private function provisionPostgres(string $name, string $pass): void
    {
        $pdo = $this->pgAdminConnection();
        // Sanitizar: solo alfanumérico + guión bajo (el nombre viene de resourceName → [a-z0-9]+)
        $safeName = preg_replace('/[^a-z0-9_]/', '', $name);
        $safeName = substr($safeName, 0, 63);
        if ($safeName === '') {
            throw new \RuntimeException('Nombre de base de datos inválido.');
        }

        // Contraseña generada por nosotros: sólo hex, sin necesidad de escape
        $pdo->exec("CREATE USER \"{$safeName}\" WITH PASSWORD '{$pass}'");
        $pdo->exec("CREATE DATABASE \"{$safeName}\" OWNER \"{$safeName}\"");
        $pdo->exec("GRANT ALL PRIVILEGES ON DATABASE \"{$safeName}\" TO \"{$safeName}\"");
    }

    private function changePostgresPassword(string $dbUser, string $newPass): void
    {
        $pdo      = $this->pgAdminConnection();
        $safeUser = preg_replace('/[^a-z0-9_]/', '', $dbUser);
        $pdo->exec("ALTER USER \"{$safeUser}\" WITH PASSWORD '{$newPass}'");
    }

    private function dropPostgres(string $dbName, string $dbUser): void
    {
        $pdo      = $this->pgAdminConnection();
        $safeName = preg_replace('/[^a-z0-9_]/', '', $dbName);
        $safeUser = preg_replace('/[^a-z0-9_]/', '', $dbUser);

        $pdo->exec("UPDATE pg_database SET datallowconn = 'false' WHERE datname = '{$safeName}'");
        $pdo->exec("SELECT pg_terminate_backend(pid) FROM pg_stat_activity WHERE datname = '{$safeName}'");
        $pdo->exec("DROP DATABASE IF EXISTS \"{$safeName}\"");
        $pdo->exec("DROP USER IF EXISTS \"{$safeUser}\"");
    }

    private function pgAdminConnection(): \PDO
    {
        if (SHARED_PG_HOST === '') {
            throw new \RuntimeException('Servidor PostgreSQL compartido no configurado (SHARED_PG_HOST).');
        }
        $dsn = sprintf(
            'pgsql:host=%s;port=%d;dbname=%s',
            SHARED_PG_HOST, SHARED_PG_PORT, SHARED_PG_ADMIN_DB
        );
        $pdo = new \PDO($dsn, SHARED_PG_ADMIN_USER, SHARED_PG_ADMIN_PASS);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }

    // =========================================================================
    // Helpers de aprovisionamiento — MongoDB
    // =========================================================================

    private function provisionMongo(string $name, string $pass): void
    {
        if (SHARED_MONGO_HOST === '') {
            throw new \RuntimeException('Servidor MongoDB compartido no configurado (SHARED_MONGO_HOST).');
        }
        $safeName = preg_replace('/[^a-z0-9_]/', '', $name);

        $jsCmd = sprintf(
            'db.getSiblingDB(%s).createUser({user:%s,pwd:%s,roles:[{role:"readWrite",db:%s}]})',
            json_encode($safeName),
            json_encode($safeName),
            json_encode($pass),
            json_encode($safeName)
        );

        $this->runMongosh($jsCmd, 'admin');
    }

    private function changeMongoPassword(string $dbName, string $dbUser, string $newPass): void
    {
        $safeName = preg_replace('/[^a-z0-9_]/', '', $dbName);
        $safeUser = preg_replace('/[^a-z0-9_]/', '', $dbUser);
        $jsCmd = sprintf(
            'db.getSiblingDB(%s).updateUser(%s,{pwd:%s})',
            json_encode($safeName),
            json_encode($safeUser),
            json_encode($newPass)
        );
        $this->runMongosh($jsCmd, 'admin');
    }

    private function dropMongo(string $dbName, string $dbUser): void
    {
        $safeName = preg_replace('/[^a-z0-9_]/', '', $dbName);
        $safeUser = preg_replace('/[^a-z0-9_]/', '', $dbUser);

        // Eliminar usuario y luego la base de datos
        $jsCmd = sprintf(
            'var d=db.getSiblingDB(%s); try{d.dropUser(%s)}catch(e){}; d.dropDatabase()',
            json_encode($safeName),
            json_encode($safeUser)
        );
        $this->runMongosh($jsCmd, 'admin');
    }

    /**
     * Ejecuta un comando JavaScript en mongosh contra el servidor admin compartido.
     *
     * @throws \RuntimeException si mongosh no está disponible o devuelve error
     */
    private function runMongosh(string $jsCmd, string $authDb = 'admin'): void
    {
        if (SHARED_MONGO_HOST === '') {
            throw new \RuntimeException('Servidor MongoDB compartido no configurado.');
        }

        $mongosh = trim((string)shell_exec('which mongosh 2>/dev/null'));
        if ($mongosh === '') {
            throw new \RuntimeException(
                'mongosh no está instalado en este servidor. ' .
                'Instala mongodb-mongosh para gestionar bases de datos MongoDB.'
            );
        }

        $uri = sprintf(
            'mongodb://%s:%s@%s:%d/%s',
            rawurlencode(SHARED_MONGO_ADMIN_USER),
            rawurlencode(SHARED_MONGO_ADMIN_PASS),
            SHARED_MONGO_HOST,
            SHARED_MONGO_PORT,
            $authDb
        );

        $cmd = sprintf(
            '%s %s --quiet --eval %s 2>&1',
            escapeshellarg($mongosh),
            escapeshellarg($uri),
            escapeshellarg($jsCmd)
        );

        exec($cmd, $output, $code);
        if ($code !== 0) {
            throw new \RuntimeException('mongosh error: ' . implode("\n", $output));
        }
    }

    // =========================================================================
    // Exportar / Importar — PostgreSQL
    // =========================================================================

    private function exportPostgres(array $db, string $pass): void
    {
        $pgdump = trim((string)shell_exec('which pg_dump 2>/dev/null'));
        if ($pgdump === '') {
            throw new \RuntimeException(
                'pg_dump no está instalado en este servidor. ' .
                'Usa el string de conexión para exportar desde tu cliente local.'
            );
        }

        $tmpFile = tempnam(sys_get_temp_dir(), 'pgdump_');
        register_shutdown_function(static function () use ($tmpFile) { @unlink($tmpFile); });

        $cmd = sprintf(
            'PGPASSWORD=%s %s -h %s -p %d -U %s %s -f %s 2>&1',
            escapeshellarg($pass),
            escapeshellarg($pgdump),
            escapeshellarg($db['db_host']),
            (int)$db['db_port'],
            escapeshellarg($db['db_user']),
            escapeshellarg($db['db_name']),
            escapeshellarg($tmpFile)
        );

        exec($cmd, $output, $code);
        if ($code !== 0) {
            throw new \RuntimeException('pg_dump error: ' . implode("\n", $output));
        }

        $filename = $db['db_name'] . '_' . date('Ymd_His') . '.sql';
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($tmpFile));
        readfile($tmpFile);
        exit;
    }

    private function importPostgres(array $db, string $pass, string $file, string $ext): void
    {
        $psql = trim((string)shell_exec('which psql 2>/dev/null'));
        if ($psql === '') {
            throw new \RuntimeException('psql no está instalado en este servidor.');
        }

        if ($ext === 'gz') {
            $zcat = trim((string)shell_exec('which zcat 2>/dev/null'));
            if ($zcat === '') {
                throw new \RuntimeException('zcat no encontrado. Sube un archivo .sql sin comprimir.');
            }
            $cmd = sprintf(
                '%s %s | PGPASSWORD=%s %s -h %s -p %d -U %s -d %s 2>&1',
                escapeshellarg($zcat),
                escapeshellarg($file),
                escapeshellarg($pass),
                escapeshellarg($psql),
                escapeshellarg($db['db_host']),
                (int)$db['db_port'],
                escapeshellarg($db['db_user']),
                escapeshellarg($db['db_name'])
            );
        } else {
            $cmd = sprintf(
                'PGPASSWORD=%s %s -h %s -p %d -U %s -d %s -f %s 2>&1',
                escapeshellarg($pass),
                escapeshellarg($psql),
                escapeshellarg($db['db_host']),
                (int)$db['db_port'],
                escapeshellarg($db['db_user']),
                escapeshellarg($db['db_name']),
                escapeshellarg($file)
            );
        }

        exec($cmd, $output, $code);
        if ($code !== 0) {
            throw new \RuntimeException('psql error: ' . implode("\n", $output));
        }
    }

    // =========================================================================
    // Exportar / Importar — MongoDB
    // =========================================================================

    private function exportMongo(array $db, string $pass): void
    {
        $mongodump = trim((string)shell_exec('which mongodump 2>/dev/null'));
        if ($mongodump === '') {
            throw new \RuntimeException(
                'mongodump no está instalado en este servidor. ' .
                'Usa el string de conexión para exportar desde tu cliente local.'
            );
        }

        $tmpFile = tempnam(sys_get_temp_dir(), 'mgdump_') . '.gz';
        register_shutdown_function(static function () use ($tmpFile) { @unlink($tmpFile); });

        $uri = sprintf(
            'mongodb://%s:%s@%s:%d/%s?authSource=%s',
            rawurlencode($db['db_user']),
            rawurlencode($pass),
            $db['db_host'],
            (int)$db['db_port'],
            $db['db_name'],
            $db['db_name']
        );

        $cmd = sprintf(
            '%s --uri=%s --archive=%s --gzip 2>&1',
            escapeshellarg($mongodump),
            escapeshellarg($uri),
            escapeshellarg($tmpFile)
        );

        exec($cmd, $output, $code);
        if ($code !== 0) {
            throw new \RuntimeException('mongodump error: ' . implode("\n", $output));
        }

        $filename = $db['db_name'] . '_' . date('Ymd_His') . '.gz';
        header('Content-Type: application/gzip');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($tmpFile));
        readfile($tmpFile);
        exit;
    }

    private function importMongo(array $db, string $pass, string $file): void
    {
        $mongorestore = trim((string)shell_exec('which mongorestore 2>/dev/null'));
        if ($mongorestore === '') {
            throw new \RuntimeException('mongorestore no está instalado en este servidor.');
        }

        $uri = sprintf(
            'mongodb://%s:%s@%s:%d/%s?authSource=%s',
            rawurlencode($db['db_user']),
            rawurlencode($pass),
            $db['db_host'],
            (int)$db['db_port'],
            $db['db_name'],
            $db['db_name']
        );

        $cmd = sprintf(
            '%s --uri=%s --archive=%s --gzip 2>&1',
            escapeshellarg($mongorestore),
            escapeshellarg($uri),
            escapeshellarg($file)
        );

        exec($cmd, $output, $code);
        if ($code !== 0) {
            throw new \RuntimeException('mongorestore error: ' . implode("\n", $output));
        }
    }

    // =========================================================================
    // Utilidades
    // =========================================================================

    private function verifyCsrf(): void
    {
        $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!Auth::verifyCsrf($token)) {
            http_response_code(403);
            exit('Token CSRF inválido.');
        }
    }

    private function log(string $context, string $msg): void
    {
        $logDir = defined('BASE_PATH') ? BASE_PATH . '/storage/logs' : dirname(__DIR__, 2) . '/storage/logs';
        @file_put_contents(
            $logDir . '/databases.log',
            '[' . date('Y-m-d H:i:s') . '] [' . $context . '] ' . $msg . PHP_EOL,
            FILE_APPEND
        );
    }
}
