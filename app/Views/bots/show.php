<?php
$pageTitle = $bot['name'];
$isDeployed = !empty($bot['coolify_app_uuid']);
$envVars = \App\Models\Bot::getEnvVars($bot['id']);
$envText = '';
foreach ($envVars as $k => $v) $envText .= "{$k}={$v}\n";

$template = null;
$hasUpdate = false;
if (!empty($bot['template_id'])) {
    $template = \App\Models\BotTemplate::find($bot['template_id']);
    if ($template) {
        $hasUpdate = version_compare($template['version'] ?? '1.0.0', $bot['current_version'] ?? '1.0.0', '>');
    }
}

$platformIcons = [
    'telegram' => '', 'discord' => '', 'slack' => '', 'whatsapp' => '',
    'twitch' => '', 'matrix' => '', 'reddit' => '', 'mastodon' => '',
    'multi' => '', 'other' => ''
];
?>

<div class="page-header">
    <div>
        <a href="<?= APP_URL ?>/dashboard" class="breadcrumb">← Dashboard</a>
        <h1>
            <?= $platformIcons[$bot['platform']] ?? '' ?>
            <?= \App\Core\View::e($bot['name']) ?>
        </h1>
        <span class="bot-status status-<?= \App\Core\View::e($bot['coolify_status'] ?? 'stopped') ?>" id="headerStatus">
            <?= \App\Core\View::e($bot['coolify_status'] ?? 'stopped') ?>
        </span>
        <?php if ($template): ?>
            <span class="badge badge-info" style="margin-left:.5rem">v<?= \App\Core\View::e($bot['current_version'] ?? '1.0.0') ?></span>
        <?php endif; ?>
    </div>

    <div class="bot-actions" id="botActions">
        <?php if ($isDeployed): ?>
            <?php
            $status = $bot['coolify_status'] ?? '';
            $isRunning = str_contains($status, 'running');
            $isTransitional = in_array($status, ['deploying', 'starting', 'stopping', 'restarting']);
            ?>
            <?php if ($isRunning): ?>
                <form method="POST" action="<?= APP_URL ?>/bots/<?= $bot['id'] ?>/stop" style="display:inline">
                    <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
                    <button class="btn btn-sm btn-outline">Stop</button>
                </form>
                <form method="POST" action="<?= APP_URL ?>/bots/<?= $bot['id'] ?>/restart" style="display:inline">
                    <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
                    <button class="btn btn-sm btn-outline">Restart</button>
                </form>
            <?php elseif ($isTransitional): ?>
                <span class="btn btn-sm btn-outline" style="opacity:0.5;cursor:wait;"><?= ucfirst(\App\Core\View::e($status)) ?>...</span>
            <?php else: ?>
                <form method="POST" action="<?= APP_URL ?>/bots/<?= $bot['id'] ?>/start" style="display:inline">
                    <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
                    <button class="btn btn-sm btn-outline">Start</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>

        <a href="<?= APP_URL ?>/bots/<?= $bot['id'] ?>/delete" class="btn btn-sm btn-danger">
            Eliminar
        </a>
        <?php if ($isDeployed): ?>
            <a href="<?= APP_URL ?>/bots/<?= $bot['id'] ?>/files" class="btn btn-sm btn-outline">📁 Archivos</a>
        <?php endif; ?>
    </div>
</div>

<?php if ($hasUpdate): ?>
<div class="flash flash-info" style="display:flex; align-items:center; justify-content:space-between;">
    <span>
        <strong>Actualización disponible:</strong> v<?= \App\Core\View::e($bot['current_version'] ?? '1.0.0') ?> → v<?= \App\Core\View::e($template['version']) ?>
        <?php if (!empty($template['changelog'])): ?>
            <br><small><?= \App\Core\View::e($template['changelog']) ?></small>
        <?php endif; ?>
    </span>
    <form method="POST" action="<?= APP_URL ?>/bots/<?= $bot['id'] ?>/update" style="display:inline; margin-left:1rem;">
        <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
        <button class="btn btn-sm btn-primary">Actualizar ahora</button>
    </form>
</div>
<?php endif; ?>

<div class="bot-layout">
    <!-- Left column -->
    <div class="bot-main">

        <!-- Info de plantilla -->
        <?php if ($template): ?>
        <div class="card">
            <div class="card-header">
                <h3><img src="<?= APP_URL ?>/img/logos/logo_<?= (int)$template['id'] ?>.png" alt="" style="width:1.5rem;height:1.5rem;object-fit:contain;vertical-align:middle;border-radius:3px;margin-right:.35rem" onerror="this.outerHTML='<?= $template['icon'] ?> '">Plantilla</h3>
                <span class="badge badge-platform badge-<?= $template['platform'] ?>"><?= ucfirst($template['platform']) ?></span>
            </div>
            <div class="card-body">
                <p style="margin:0 0 .75rem"><?= \App\Core\View::e($template['short_description']) ?></p>
                <div style="display:flex; gap:1rem; flex-wrap:wrap; font-size:.85rem; color:var(--text-muted);">
                    <span><?= \App\Core\View::e($bot['docker_image']) ?></span>
                    <?php if (!empty($template['documentation_url'])): ?>
                        <a href="<?= \App\Core\View::e($template['documentation_url']) ?>" target="_blank" rel="noopener">Documentación</a>
                    <?php endif; ?>
                    <?php if (!empty($template['docs_first_steps_url'])): ?>
                        <a href="<?= \App\Core\View::e($template['docs_first_steps_url']) ?>" target="_blank" rel="noopener">📖 Primeros pasos</a>
                    <?php endif; ?>
                    <?php if (!empty($template['more_info_url'])): ?>
                        <a href="<?= \App\Core\View::e($template['more_info_url']) ?>" target="_blank" rel="noopener" class="btn btn-outline btn-sm">+info</a>
                    <?php endif; ?>
                    <?php if ($isDeployed): ?>
                        <a href="<?= APP_URL ?>/bots/<?= $bot['id'] ?>/files" class="btn btn-outline btn-sm">📁 Gestor de Archivos</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Variables de entorno -->
        <div class="card">
            <div class="card-header">
                <h3>Variables de entorno</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/bots/<?= $bot['id'] ?>/env">
                    <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
                    <textarea name="env_vars" id="envVarsTextarea" class="form-control code-editor"
                        placeholder="BOT_TOKEN=tu_token&#10;OTRA_VAR=valor"
                        style="resize:vertical; overflow:hidden; min-height:4rem;"><?= \App\Core\View::e(trim($envText)) ?></textarea>
                    <small class="form-hint">Una variable por línea en formato CLAVE=VALOR. Los cambios se aplicarán automáticamente al bot en ejecución.</small>
                    <div class="form-actions mt-2">
                        <button type="submit" class="btn btn-sm btn-outline">Guardar variables</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Logs -->
        <?php if ($isDeployed): ?>
        <div class="card card-logs">
            <div class="card-header">
                <h3>Logs</h3>
                <div style="display:flex;align-items:center;gap:.5rem">
                    <button class="btn btn-xs btn-ghost" onclick="downloadLogs()" title="Descargar logs">⬇ Descargar</button>
                    <label class="toggle-label">
                        <input type="checkbox" id="autoRefresh" checked>
                        <span>Auto</span>
                    </label>
                </div>
            </div>
            <div class="card-body p-0">
                <pre class="log-output" id="logOutput">Cargando...</pre>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Right column -->
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
                    <div class="stat-row"><span>Plataforma</span><span><?= $platformIcons[$bot['platform']] ?? '' ?> <?= ucfirst($bot['platform']) ?></span></div>
                    <?php if ($template): ?>
                    <div class="stat-row"><span>Versión</span><span>v<?= \App\Core\View::e($bot['current_version'] ?? '1.0.0') ?></span></div>
                    <div class="stat-row"><span>Auto-update</span><span><?= $bot['auto_update'] ? 'Sí' : 'No' ?></span></div>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-muted">Bot no desplegado aún.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Deploy manual + Auto-actualización -->
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; align-items:stretch">
        <div class="card" style="margin:0">
            <div class="card-header"><h3>Despliegue</h3></div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/bots/<?= $bot['id'] ?>/deploy">
                    <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
                    <button type="submit" class="btn btn-primary">
                        <?= $isDeployed ? 'Re-lanzar' : 'Desplegar ahora' ?>
                    </button>
                    
                </form>
            </div>
        </div>

        <!-- Auto-actualización -->
        <?php if ($template && !empty($template['auto_update_supported'])): ?>
        <div class="card" style="margin:0">
            <div class="card-header">
                <h3>Auto-actualización</h3>
            </div>
            <div class="card-body">
                <div style="display:flex; align-items:center; justify-content:space-between;">
                    <div>
                        <p style="margin:0 0 .25rem">
                            <?php if ($bot['auto_update']): ?>
                                <span class="badge badge-success">Activada</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Desactivada</span>
                            <?php endif; ?>
                        </p>
                        <small class="text-muted">
                            
                            Versión actual: <strong>v<?= \App\Core\View::e($bot['current_version'] ?? '1.0.0') ?></strong>
                            <?php if (!empty($bot['last_updated_at'])): ?>
                                · Última actualización: <?= date('d/m/Y H:i', strtotime($bot['last_updated_at'])) ?>
                            <?php endif; ?>
                        </small>
                    </div>
                    <form method="POST" action="<?= APP_URL ?>/bots/<?= $bot['id'] ?>/auto-update">
                        <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
                        <button class="btn btn-sm <?= $bot['auto_update'] ? 'btn-outline' : 'btn-primary' ?>">
                            <?= $bot['auto_update'] ? 'Desactivar' : 'Activar' ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div></div>
        <?php endif; ?>
        </div>
        <br/>

        <!-- Ayuda rápida -->
        <?php if ($template && !empty($template['setup_instructions'])): ?>
        <div class="card">
            <div class="card-header"><h3>Guía rápida</h3></div>
            <div class="card-body">
                <div class="setup-steps" style="font-size:.85rem">
                    <?= nl2br(\App\Core\View::e($template['setup_instructions'])) ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
(function() {
    var ta = document.getElementById('envVarsTextarea');
    if (!ta) return;
    function autoResize() {
        ta.style.height = 'auto';
        ta.style.height = (ta.scrollHeight) + 'px';
    }
    ta.addEventListener('input', autoResize);
    autoResize();
})();
</script>

<?php if ($isDeployed): ?>
<script>
const BOT_ID = <?= $bot['id'] ?>;
const BASE   = '<?= APP_URL ?>';
const CSRF   = '<?= \App\Core\Auth::csrfToken() ?>';
let logTimer = null;
let statsTimer = null;

function statusLabel(s) {
    const map = {
        'running': 'running',
        'running:healthy': 'running:healthy',
        'running:unhealthy': 'running:unhealthy',
        'exited': 'exited',
        'restarting': 'restarting',
        'deploying': 'deploying',
        'starting': 'starting',
        'stopping': 'stopping',
        'stopped': 'stopped',
    };
    // Buscar coincidencia parcial
    for (const [key, label] of Object.entries(map)) {
        if (s && s.startsWith(key)) return label;
    }
    return s || 'unknown';
}

function statusClass(s) {
    if (!s) return 'status-unknown';
    if (s.startsWith('running')) return 'status-running';
    if (['deploying','starting','restarting'].some(x => s.startsWith(x))) return 'status-deploying';
    if (s.startsWith('exited') || s.startsWith('error')) return 'status-exited';
    return 'status-stopped';
}

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

function downloadLogs() {
    const content = document.getElementById('logOutput').textContent;
    const blob = new Blob([content], { type: 'text/plain' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'bot-<?= $bot['id'] ?>-logs.txt';
    a.click();
    URL.revokeObjectURL(a.href);
}

function renderActionButtons(s) {
    const actions = document.getElementById('botActions');
    if (!actions) return;
    const isRunning     = s && s.startsWith('running');
    const isTransitional = s && ['deploying','starting','stopping','restarting'].some(x => s.startsWith(x));
    const stopForm      = `<form method="POST" action="${BASE}/bots/${BOT_ID}/stop" style="display:inline"><input type="hidden" name="_csrf" value="${CSRF}"><button class="btn btn-sm btn-outline">Stop</button></form>`;
    const restartForm   = `<form method="POST" action="${BASE}/bots/${BOT_ID}/restart" style="display:inline"><input type="hidden" name="_csrf" value="${CSRF}"><button class="btn btn-sm btn-outline">Restart</button></form>`;
    const startForm     = `<form method="POST" action="${BASE}/bots/${BOT_ID}/start" style="display:inline"><input type="hidden" name="_csrf" value="${CSRF}"><button class="btn btn-sm btn-outline">Start</button></form>`;
    const transitional  = `<span class="btn btn-sm btn-outline" style="opacity:0.5;cursor:wait;">${s.charAt(0).toUpperCase() + s.slice(1)}...</span>`;
    const deleteBtn     = `<a href="${BASE}/bots/${BOT_ID}/delete" class="btn btn-sm btn-danger">Eliminar</a>`;
    const filesBtn      = `<a href="${BASE}/bots/${BOT_ID}/files" class="btn btn-sm btn-outline">📁 Archivos</a>`;
    let html = '';
    if (isRunning)          html = stopForm + ' ' + restartForm;
    else if (isTransitional) html = transitional;
    else                    html = startForm;
    actions.innerHTML = html + ' ' + deleteBtn + ' ' + filesBtn;
}

async function refreshStats() {
    try {
        const r = await fetch(BASE + '/bots/' + BOT_ID + '/stats');
        const d = await r.json();
        if (d.status) {
            // Actualizar badge en sidebar
            const el = document.getElementById('botStatus');
            if (el) {
                el.textContent = statusLabel(d.status);
                el.className = 'bot-status ' + statusClass(d.status);
            }
            // Actualizar badge en header
            const hdr = document.getElementById('headerStatus');
            if (hdr) {
                hdr.textContent = statusLabel(d.status);
                hdr.className = 'bot-status ' + statusClass(d.status);
            }
            // Actualizar botones de acción
            renderActionButtons(d.status);
        }
    } catch(e) {}
}

function startAutoRefresh() {
    fetchLogs();
    refreshStats();
    logTimer = setInterval(fetchLogs, 5000);
    statsTimer = setInterval(refreshStats, 8000);
}

document.getElementById('autoRefresh').addEventListener('change', function() {
    if (this.checked) {
        startAutoRefresh();
    } else {
        clearInterval(logTimer); logTimer = null;
        clearInterval(statsTimer); statsTimer = null;
    }
});

startAutoRefresh();
</script>
<?php endif; ?>
