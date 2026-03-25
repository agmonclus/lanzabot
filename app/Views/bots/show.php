<?php
$pageTitle = $bot['name'];
$isDeployed = !empty($bot['coolify_app_uuid']);
$envVars = \App\Models\Bot::getEnvVars($bot['id']);
$envText = '';
foreach ($envVars as $k => $v) $envText .= "{$k}={$v}\n";
?>

<div class="page-header">
    <div>
        <a href="<?= APP_URL ?>/dashboard" class="breadcrumb">← Dashboard</a>
        <h1><?= \App\Core\View::e($bot['name']) ?></h1>
        <span class="bot-status status-<?= \App\Core\View::e($bot['coolify_status'] ?? 'stopped') ?>">
            <?= \App\Core\View::e($bot['coolify_status'] ?? 'stopped') ?>
        </span>
    </div>

    <div class="bot-actions">
        <?php if ($isDeployed): ?>
            <?php if (($bot['coolify_status'] ?? '') === 'running'): ?>
                <form method="POST" action="<?= APP_URL ?>/bots/<?= $bot['id'] ?>/stop" style="display:inline">
                    <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
                    <button class="btn btn-sm btn-outline">⏹ Stop</button>
                </form>
                <form method="POST" action="<?= APP_URL ?>/bots/<?= $bot['id'] ?>/restart" style="display:inline">
                    <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
                    <button class="btn btn-sm btn-outline">🔄 Restart</button>
                </form>
            <?php else: ?>
                <form method="POST" action="<?= APP_URL ?>/bots/<?= $bot['id'] ?>/start" style="display:inline">
                    <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
                    <button class="btn btn-sm btn-outline">▶ Start</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>

        <form method="POST" action="<?= APP_URL ?>/bots/<?= $bot['id'] ?>/delete"
              onsubmit="return confirm('¿Eliminar este bot? Esta acción no se puede deshacer.')" style="display:inline">
            <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
            <input type="hidden" name="_method" value="DELETE">
            <button class="btn btn-sm btn-danger">Eliminar</button>
        </form>
    </div>
</div>

<div class="bot-layout">
    <!-- Left column: code + env -->
    <div class="bot-main">

        <!-- Upload code -->
        <div class="card">
            <div class="card-header">
                <h3>Código</h3>
                <?php if ($bot['code_uploaded']): ?>
                    <span class="badge badge-success">Subido ✓</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/bots/<?= $bot['id'] ?>/upload" enctype="multipart/form-data">
                    <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
                    <div class="upload-zone" id="uploadZone">
                        <input type="file" id="codeFile" name="code" accept=".zip,.tar,.gz" style="display:none">
                        <label for="codeFile" class="upload-label">
                            <span class="upload-icon">📦</span>
                            <span>Arrastra tu archivo o <strong>haz clic</strong> para seleccionar</span>
                            <small>.zip / .tar.gz — máx. 50 MB</small>
                        </label>
                        <div id="selectedFile" style="display:none" class="selected-file"></div>
                    </div>
                    <button type="submit" class="btn btn-sm btn-outline mt-2" id="uploadBtn" style="display:none">Subir código</button>
                </form>
            </div>
        </div>

        <!-- Variables de entorno -->
        <div class="card">
            <div class="card-header">
                <h3>Variables de entorno</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/bots/<?= $bot['id'] ?>/env">
                    <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
                    <textarea name="env_vars" class="form-control code-editor" rows="8"
                        placeholder="BOT_TOKEN=tu_token&#10;OTRA_VAR=valor"><?= \App\Core\View::e(trim($envText)) ?></textarea>
                    <small class="form-hint">Una variable por línea en formato CLAVE=VALOR</small>
                    <div class="form-actions mt-2">
                        <button type="submit" class="btn btn-sm btn-outline">Guardar variables</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Deploy -->
        <div class="card">
            <div class="card-header"><h3>Despliegue</h3></div>
            <div class="card-body">
                <?php if (!$bot['code_uploaded'] && !$isDeployed): ?>
                    <p class="text-muted">Sube tu código antes de desplegar.</p>
                <?php else: ?>
                    <form method="POST" action="<?= APP_URL ?>/bots/<?= $bot['id'] ?>/deploy">
                        <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
                        <button type="submit" class="btn btn-primary">
                            <?= $isDeployed ? '🚀 Re-desplegar' : '🚀 Desplegar ahora' ?>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right column: stats + logs -->
    <div class="bot-sidebar">

        <!-- Stats -->
        <div class="card">
            <div class="card-header">
                <h3>Estado</h3>
                <?php if ($isDeployed): ?>
                    <button class="btn btn-xs btn-ghost" onclick="refreshStats()">↻</button>
                <?php endif; ?>
            </div>
            <div class="card-body" id="statsBox">
                <?php if ($isDeployed): ?>
                    <div class="stat-row"><span>UUID</span><code><?= substr($bot['coolify_app_uuid'], 0, 8) ?>...</code></div>
                    <div class="stat-row"><span>Estado</span><span id="botStatus" class="bot-status status-<?= \App\Core\View::e($bot['coolify_status']) ?>"><?= \App\Core\View::e($bot['coolify_status']) ?></span></div>
                <?php else: ?>
                    <p class="text-muted">Bot no desplegado aún.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Logs -->
        <?php if ($isDeployed): ?>
        <div class="card card-logs">
            <div class="card-header">
                <h3>Logs</h3>
                <label class="toggle-label">
                    <input type="checkbox" id="autoRefresh" checked>
                    <span>Auto</span>
                </label>
            </div>
            <div class="card-body p-0">
                <pre class="log-output" id="logOutput">Cargando...</pre>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($isDeployed): ?>
<script>
const BOT_ID = <?= $bot['id'] ?>;
const BASE   = '<?= APP_URL ?>';
let logTimer = null;

async function fetchLogs() {
    try {
        const r = await fetch(BASE + '/bots/' + BOT_ID + '/logs');
        const d = await r.json();
        const el = document.getElementById('logOutput');
        if (d.logs !== undefined) {
            el.textContent = d.logs || '(sin logs)';
            el.scrollTop = el.scrollHeight;
        }
    } catch(e) {}
}

async function refreshStats() {
    try {
        const r = await fetch(BASE + '/bots/' + BOT_ID + '/stats');
        const d = await r.json();
        if (d.status) {
            const el = document.getElementById('botStatus');
            if (el) { el.textContent = d.status; el.className = 'bot-status status-' + d.status; }
        }
    } catch(e) {}
}

function startAutoRefresh() {
    fetchLogs();
    logTimer = setInterval(fetchLogs, 5000);
}

document.getElementById('autoRefresh').addEventListener('change', function() {
    if (this.checked) startAutoRefresh();
    else { clearInterval(logTimer); logTimer = null; }
});

startAutoRefresh();
</script>
<?php endif; ?>
