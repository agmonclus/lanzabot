<?php
/**
 * Script de prueba de conexión a base de datos
 * Carga las variables de entorno y presenta información de debug detallada
 */

// Establecer headers
header('Content-Type: text/html; charset=utf-8');

// Cargar variables de entorno
require_once dirname(__DIR__) . '/vendor/autoload.php';

try {
    $dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->safeLoad();
} catch (\Exception $e) {
    echo "<h1>❌ Error al cargar .env</h1>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    die();
}

// Definir constantes de BD
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'lanzabot');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('DB_PORT', $_ENV['DB_PORT'] ?? 3306);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico de Base de Datos</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { padding: 20px 30px; background: #2c3e50; color: white; border-radius: 8px 8px 0 0; }
        h2 { margin: 20px 30px 10px; color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        .section { padding: 20px 30px; border-bottom: 1px solid #ecf0f1; }
        .info-grid { display: grid; grid-template-columns: 200px 1fr; gap: 15px; }
        .info-row { display: contents; }
        .info-label { font-weight: 600; color: #2c3e50; }
        .info-value { color: #555; word-break: break-all; }
        .status { padding: 12px 15px; border-radius: 4px; margin: 10px 0; }
        .status.success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .status.error { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        .status.warning { background: #fff3cd; color: #856404; border-left: 4px solid #ffc107; }
        .status.info { background: #d1ecf1; color: #0c5460; border-left: 4px solid #17a2b8; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: 'Courier New', monospace; }
        pre { background: #f4f4f4; padding: 15px; border-radius: 4px; overflow-x: auto; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ecf0f1; }
        th { background: #f8f9fa; font-weight: 600; color: #2c3e50; }
        .success { color: #28a745; font-weight: 600; }
        .error { color: #dc3545; font-weight: 600; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Diagnóstico de Conexión a Base de Datos - Lanzabot</h1>

        <!-- VARIABLES DE ENTORNO -->
        <h2>📋 Variables de Entorno</h2>
        <div class="section">
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">HOST:</div>
                    <div class="info-value"><code><?php echo htmlspecialchars(DB_HOST); ?></code></div>
                </div>
                <div class="info-row">
                    <div class="info-label">PUERTO:</div>
                    <div class="info-value"><code><?php echo htmlspecialchars(DB_PORT); ?></code></div>
                </div>
                <div class="info-row">
                    <div class="info-label">BASE DE DATOS:</div>
                    <div class="info-value"><code><?php echo htmlspecialchars(DB_NAME); ?></code></div>
                </div>
                <div class="info-row">
                    <div class="info-label">USUARIO:</div>
                    <div class="info-value"><code><?php echo htmlspecialchars(DB_USER); ?></code></div>
                </div>
                <div class="info-row">
                    <div class="info-label">CONTRASEÑA:</div>
                    <div class="info-value"><code><?php echo !empty(DB_PASS) ? '●●●●●●●●' : '(sin contraseña)'; ?></code></div>
                </div>
            </div>
        </div>

        <!-- DISPONIBILIDAD DEL HOST -->
        <h2>🌐 Disponibilidad del Host</h2>
        <div class="section">
            <?php
            $host_available = false;
            $socket = @fsockopen(DB_HOST, DB_PORT, $errno, $errstr, 5);
            if ($socket) {
                fclose($socket);
                $host_available = true;
                echo '<div class="status success">✓ El host <strong>' . htmlspecialchars(DB_HOST) . '</strong> responde en puerto ' . htmlspecialchars(DB_PORT) . '</div>';
            } else {
                echo '<div class="status error">✗ No se puede conectar a <strong>' . htmlspecialchars(DB_HOST) . ':' . htmlspecialchars(DB_PORT) . '</strong></div>';
                echo '<pre>Error: ' . htmlspecialchars($errstr) . ' (Código: ' . $errno . ')</pre>';
            }
            ?>
        </div>

        <!-- CONEXIÓN PDO -->
        <h2>🔐 Conexión PDO</h2>
        <div class="section">
            <?php
            $pdo = null;
            $connection_success = false;
            $error_message = '';

            try {
                $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
                
                echo '<p>DSN utilizado:</p>';
                echo '<code>' . htmlspecialchars($dsn) . '</code>';
                echo '<hr style="margin: 15px 0; border: none; border-top: 1px solid #ecf0f1;">';

                $pdo = new PDO(
                    $dsn,
                    DB_USER,
                    DB_PASS,
                    [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                    ]
                );

                $connection_success = true;
                echo '<div class="status success">✓ <strong>Conexión exitosa a PDO</strong></div>';

            } catch (PDOException $e) {
                $connection_success = false;
                $error_message = $e->getMessage();
                echo '<div class="status error">✗ <strong>Error de conexión PDO</strong></div>';
                echo '<pre>' . htmlspecialchars($error_message) . '</pre>';
            }
            ?>
        </div>

        <?php if ($pdo && $connection_success): ?>

        <!-- INFORMACIÓN DEL SERVIDOR MYSQL -->
        <h2>🗄️ Información del Servidor MySQL</h2>
        <div class="section">
            <?php
            try {
                $result = $pdo->query("SELECT VERSION() as version")->fetch();
                ?>
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-label">Versión MySQL:</div>
                        <div class="info-value"><code><?php echo htmlspecialchars($result['version']); ?></code></div>
                    </div>
                </div>
                <?php
                
                // Más información del servidor
                $server_info = $pdo->query("SELECT @@version, @@datadir, @@max_allowed_packet, @@max_connections")->fetch();
                if ($server_info) {
                    echo '<table>';
                    echo '<tr><th>Parámetro</th><th>Valor</th></tr>';
                    foreach ($server_info as $key => $value) {
                        echo '<tr><td>' . htmlspecialchars($key) . '</td><td><code>' . htmlspecialchars($value) . '</code></td></tr>';
                    }
                    echo '</table>';
                }
            } catch (PDOException $e) {
                echo '<div class="status error">Error al consultar información: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            ?>
        </div>

        <!-- BASES DE DATOS DISPONIBLES -->
        <h2>📁 Bases de Datos Disponibles</h2>
        <div class="section">
            <?php
            try {
                $databases = $pdo->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);
                
                if (!empty($databases)) {
                    echo '<table>';
                    echo '<tr><th>Base de Datos</th><th>Estado</th></tr>';
                    
                    foreach ($databases as $db) {
                        $is_target = ($db === DB_NAME) ? ' <span class="success">← actual</span>' : '';
                        $icon = ($db === DB_NAME) ? '✓' : '○';
                        echo '<tr><td>' . htmlspecialchars($db) . $is_target . '</td><td>' . $icon . '</td></tr>';
                    }
                    echo '</table>';
                } else {
                    echo '<div class="status warning">No hay bases de datos accesibles.</div>';
                }
            } catch (PDOException $e) {
                echo '<div class="status error">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            ?>
        </div>

        <!-- INFORMACIÓN DE LA BD OBJETIVO -->
        <h2>📊 Base de Datos: <?php echo htmlspecialchars(DB_NAME); ?></h2>
        <div class="section">
            <?php
            try {
                // Seleccionar la BD
                $pdo->query("USE " . DB_NAME);
                echo '<div class="status success">✓ Base de datos accesible</div>';
                
                // Obtener tablas
                $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
                
                if (!empty($tables)) {
                    echo '<h3>Tablas (' . count($tables) . '):</h3>';
                    echo '<table>';
                    echo '<tr><th>Nombre</th><th>Filas</th><th>Motor</th><th>Tamaño</th></tr>';
                    
                    foreach ($tables as $table) {
                        try {
                            $row_count = $pdo->query("SELECT COUNT(*) FROM " . $table)->fetchColumn();
                            
                            $status = $pdo->prepare(
                                "SELECT ENGINE, ROUND(((data_length + index_length) / 1024 / 1024), 2) as size_mb
                                FROM information_schema.TABLES 
                                WHERE table_schema = ? AND table_name = ?"
                            );
                            $status->execute([DB_NAME, $table]);
                            $table_info = $status->fetch();
                            
                            $size = $table_info ? $table_info['size_mb'] . ' MB' : 'N/A';
                            $engine = $table_info ? $table_info['ENGINE'] : 'N/A';
                            
                            echo '<tr>';
                            echo '<td><code>' . htmlspecialchars($table) . '</code></td>';
                            echo '<td>' . number_format($row_count) . '</td>';
                            echo '<td>' . htmlspecialchars($engine) . '</td>';
                            echo '<td>' . $size . '</td>';
                            echo '</tr>';
                        } catch (Exception $e) {
                            echo '<tr><td colspan="4"><span class="error">Error con tabla ' . htmlspecialchars($table) . ': ' . htmlspecialchars($e->getMessage()) . '</span></td></tr>';
                        }
                    }
                    echo '</table>';
                } else {
                    echo '<div class="status warning">⚠️ No hay tablas en la base de datos.</div>';
                }
                
            } catch (PDOException $e) {
                echo '<div class="status error">✗ Error al acceder a la base de datos: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            ?>
        </div>

        <!-- PERMISOS DEL USUARIO -->
        <h2>🔑 Permisos del Usuario</h2>
        <div class="section">
            <?php
            try {
                $grants = $pdo->query("SHOW GRANTS FOR CURRENT_USER()")->fetchAll(PDO::FETCH_COLUMN);
                
                if (!empty($grants)) {
                    echo '<ul style="list-style: none; padding: 0;">';
                    foreach ($grants as $grant) {
                        echo '<li style="padding: 8px; background: #f8f9fa; margin: 5px 0; border-radius: 3px;">';
                        echo '<code>' . htmlspecialchars($grant) . '</code>';
                        echo '</li>';
                    }
                    echo '</ul>';
                } else {
                    echo '<div class="status warning">No se pudieron obtener los grants.</div>';
                }
            } catch (PDOException $e) {
                echo '<div class="status warning">⚠️ No se pueden listar grants: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            ?>
        </div>

        <?php else: ?>
        <div class="section" style="background: #f8d7da; border-left: 4px solid #dc3545;">
            <h2 style="color: #721c24;">❌ No se pudo establecer conexión</h2>
            <p>No es posible obtener más información sin una conexión exitosa a la base de datos.</p>
        </div>
        <?php endif; ?>

        <!-- RESUMEN FINAL -->
        <h2>✅ Resumen</h2>
        <div class="section">
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Host alcanzable:</div>
                    <div class="info-value"><?php echo $host_available ? '<span class="success">✓ Sí</span>' : '<span class="error">✗ No</span>'; ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Conexión PDO:</div>
                    <div class="info-value"><?php echo $connection_success ? '<span class="success">✓ Exitosa</span>' : '<span class="error">✗ Fallida</span>'; ?></div>
                </div>
                <?php if ($connection_success): ?>
                <div class="info-row">
                    <div class="info-label">BD accesible:</div>
                    <div class="info-value"><span class="success">✓ Sí</span></div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div style="padding: 20px 30px; text-align: center; color: #7f8c8d; font-size: 12px;">
            <p>Generado: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>
</body>
</html>
