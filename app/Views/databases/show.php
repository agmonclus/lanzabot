<?php
$pageTitle  = $db['label'];
$isActive   = $db['status'] === 'active';
$dbIcon     = $db['type'] === 'postgresql' ? '🐘' : '🍃';
$typeLabel  = strtoupper($db['type']);
$connString = '';

if ($isActive) {
    if ($db['type'] === 'postgresql') {
        $connString = sprintf(
            'postgresql://%s:%s@%s:%d/%s',
            $db['db_user'],
            $password,
            $db['db_host'],
            $db['db_port'],
            $db['db_name']
        );
    } else {
        $connString = sprintf(
            'mongodb://%s:%s@%s:%d/%s?authSource=%s',
            $db['db_user'],
            $password,
            $db['db_host'],
            $db['db_port'],
            $db['db_name'],
            $db['db_name']
        );
    }
}
?>

<div class="page-header">
    <div>
        <a href="<?= APP_URL ?>/databases" class="breadcrumb">← Bases de datos</a>
        <h1><?= $dbIcon ?> <?= \App\Core\View::e($db['label']) ?></h1>
        <span class="bot-status status-<?= $isActive ? 'running' : ($db['status'] === 'error' ? 'stopped' : 'deploying') ?>">
            <?= \App\Core\View::e($db['status']) ?>
        </span>
        <span class="badge badge-info" style="margin-left:.5rem"><?= $typeLabel ?></span>
    </div>
    <a href="<?= APP_URL ?>/databases/<?= $db['id'] ?>/delete" class="btn btn-sm btn-danger">Eliminar</a>
</div>

<?php if ($db['status'] === 'error'): ?>
<div class="flash flash-error">
    Error al crear la base de datos: <?= \App\Core\View::e($db['error_msg'] ?? 'desconocido') ?>
</div>
<?php elseif ($db['status'] === 'creating'): ?>
<div class="flash flash-info">La base de datos se está creando&hellip;</div>
<?php endif; ?>

<?php if ($isActive): ?>

<!-- Información de conexión -->
<section class="section">
    <h2>Información de conexión</h2>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">
        <div>
            <p class="text-muted" style="margin:0;font-size:.8rem">HOST</p>
            <code><?= \App\Core\View::e($db['db_host']) ?></code>
        </div>
        <div>
            <p class="text-muted" style="margin:0;font-size:.8rem">PUERTO</p>
            <code><?= (int)$db['db_port'] ?></code>
        </div>
        <div>
            <p class="text-muted" style="margin:0;font-size:.8rem">BASE DE DATOS</p>
            <code><?= \App\Core\View::e($db['db_name']) ?></code>
        </div>
        <div>
            <p class="text-muted" style="margin:0;font-size:.8rem">USUARIO</p>
            <code><?= \App\Core\View::e($db['db_user']) ?></code>
        </div>
    </div>

    <!-- Contraseña -->
    <div class="form-group" style="margin-bottom:0">
        <label class="form-label">Contraseña</label>
        <div style="display:flex;gap:.5rem;align-items:center">
            <input
                type="password"
                id="dbPassword"
                class="form-control"
                value="<?= \App\Core\View::e($password) ?>"
                readonly
                style="font-family:monospace;max-width:340px"
            >
            <button
                type="button"
                class="btn btn-sm btn-outline"
                onclick="togglePassword()"
                id="toggleBtn"
            >Mostrar</button>
            <button
                type="button"
                class="btn btn-sm btn-outline"
                onclick="copyToClipboard('<?= \App\Core\View::e(addslashes($password)) ?>')"
            >Copiar</button>
        </div>
    </div>

    <!-- Connection string completo -->
    <div class="form-group" style="margin-top:1rem">
        <label class="form-label">String de conexión</label>
        <div style="display:flex;gap:.5rem;align-items:center">
            <input
                type="password"
                id="connString"
                class="form-control"
                value="<?= \App\Core\View::e($connString) ?>"
                readonly
                style="font-family:monospace"
            >
            <button
                type="button"
                class="btn btn-sm btn-outline"
                onclick="toggleConnString()"
            >Mostrar</button>
            <button
                type="button"
                class="btn btn-sm btn-outline"
                onclick="copyToClipboard('<?= \App\Core\View::e(addslashes($connString)) ?>')"
            >Copiar</button>
        </div>
    </div>

    <!-- Regenerar contraseña -->
    <form method="POST" action="<?= APP_URL ?>/databases/<?= $db['id'] ?>/password"
          style="margin-top:1rem"
          onsubmit="return confirm('¿Regenerar la contraseña? El string de conexión actual dejará de funcionar.')">
        <input type="hidden" name="_csrf"    value="<?= \App\Core\Auth::csrfToken() ?>">
        <button type="submit" class="btn btn-sm btn-outline">🔄 Regenerar contraseña</button>
    </form>
</section>

<!-- Exportar -->
<section class="section">
    <h2>Exportar base de datos</h2>
    <p class="text-muted">
        Descarga un volcado completo de la base de datos
        (<?= $db['type'] === 'postgresql' ? '.sql via pg_dump' : '.gz via mongodump' ?>).
        Requiere que las herramientas de línea de comandos estén instaladas en el servidor.
    </p>
    <a href="<?= APP_URL ?>/databases/<?= $db['id'] ?>/export" class="btn btn-outline">
        ⬇ Exportar
    </a>
</section>

<!-- Importar -->
<section class="section">
    <h2>Importar base de datos</h2>
    <p class="text-muted">
        <?php if ($db['type'] === 'postgresql'): ?>
            Acepta archivos <strong>.sql</strong> o <strong>.sql.gz</strong> generados con pg_dump.
            <strong>Atención:</strong> los datos existentes no se eliminarán previamente.
        <?php else: ?>
            Acepta archivos <strong>.gz</strong> (mongodump con --archive --gzip).
            <strong>Atención:</strong> los documentos existentes no se eliminarán previamente.
        <?php endif; ?>
    </p>
    <form method="POST" action="<?= APP_URL ?>/databases/<?= $db['id'] ?>/import"
          enctype="multipart/form-data">
        <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
        <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap">
            <input
                type="file"
                name="dump_file"
                accept="<?= $db['type'] === 'postgresql' ? '.sql,.gz,.dump' : '.gz,.archive' ?>"
                required
                class="form-control"
                style="max-width:340px"
            >
            <button type="submit" class="btn btn-primary">⬆ Importar</button>
        </div>
    </form>
</section>

<?php endif; ?>

<script>
function togglePassword() {
    var el  = document.getElementById('dbPassword');
    var btn = document.getElementById('toggleBtn');
    if (el.type === 'password') { el.type = 'text';     btn.textContent = 'Ocultar'; }
    else                        { el.type = 'password'; btn.textContent = 'Mostrar'; }
}
function toggleConnString() {
    var el = document.getElementById('connString');
    el.type = el.type === 'password' ? 'text' : 'password';
}
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Copiado al portapapeles');
    });
}
</script>
