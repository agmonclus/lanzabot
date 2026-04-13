<?php
$pageTitle = 'Archivos — ' . $bot['name'];
$isDeployed = !empty($bot['coolify_app_uuid']);
$hasFiles = !empty($fileStorages);
?>

<div class="page-header">
    <div>
        <a href="<?= APP_URL ?>/bots/<?= $bot['id'] ?>" class="breadcrumb">← <?= \App\Core\View::e($bot['name']) ?></a>
        <h1>📁 Gestor de Archivos</h1>
        <small class="text-muted"><?= \App\Core\View::e($bot['name']) ?> · <?= \App\Core\View::e($bot['docker_image'] ?? '') ?></small>
    </div>
    <div class="bot-actions">
        <form method="POST" action="<?= APP_URL ?>/bots/<?= $bot['id'] ?>/restart" style="display:inline">
            <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
            <button class="btn btn-sm btn-outline" title="Reinicia el contenedor para aplicar cambios en archivos">🔄 Reiniciar bot</button>
        </form>
    </div>
</div>

<div class="files-layout">
    <!-- Sidebar: lista de archivos -->
    <div class="files-sidebar">
        <div class="card">
            <div class="card-header">
                <h3>Archivos</h3>
            </div>
            <div class="card-body p-0">
                <?php if ($hasFiles): ?>
                    <ul class="file-list">
                        <?php foreach ($fileStorages as $fs): ?>
                            <?php
                            $isActive = $activeFile && ($activeFile['uuid'] ?? '') === ($fs['uuid'] ?? '');
                            $path = $fs['mount_path'] ?? $fs['fs_path'] ?? '?';
                            $filename = basename($path);
                            $ext = pathinfo($filename, PATHINFO_EXTENSION);
                            $icon = match($ext) {
                                'py' => '🐍',
                                'js' => '📜',
                                'ts' => '📘',
                                'go' => '🔵',
                                'json' => '📋',
                                'yml', 'yaml' => '⚙️',
                                'sh', 'bash' => '🖥️',
                                'md' => '📝',
                                default => '📄'
                            };
                            ?>
                            <li class="<?= $isActive ? 'active' : '' ?>">
                                <a href="<?= APP_URL ?>/bots/<?= $bot['id'] ?>/files?file=<?= urlencode($fs['uuid'] ?? '') ?>">
                                    <span class="file-icon"><?= $icon ?></span>
                                    <span class="file-name"><?= \App\Core\View::e($filename) ?></span>
                                    <span class="file-path"><?= \App\Core\View::e($path) ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted" style="padding: 1rem; font-size:.85rem">No hay archivos. Crea uno nuevo para empezar.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Volúmenes persistentes -->
        <?php if (!empty($persistentStorages)): ?>
        <div class="card" style="margin-top: 1rem">
            <div class="card-header"><h3>💾 Volúmenes</h3></div>
            <div class="card-body" style="font-size: .85rem">
                <?php foreach ($persistentStorages as $ps): ?>
                    <div class="storage-item">
                        <strong><?= \App\Core\View::e($ps['name'] ?? 'volumen') ?></strong>
                        <span class="text-muted">→ <?= \App\Core\View::e($ps['mount_path'] ?? '?') ?></span>
                    </div>
                <?php endforeach; ?>
                <small class="text-muted" style="display:block; margin-top:.5rem">Los volúmenes persistentes conservan los datos entre reinicios.</small>
            </div>
        </div>
        <?php endif; ?>

        <!-- Crear nuevo archivo -->
        <div class="card" style="margin-top: 1rem">
            <div class="card-header"><h3>Nuevo archivo</h3></div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/bots/<?= $bot['id'] ?>/files/create" enctype="multipart/form-data">
                    <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
                    <div class="form-group mb-2">
                        <label class="form-label">Ruta en contenedor</label>
                        <input type="text" name="mount_path" class="form-control" placeholder="/app/mi_archivo.py" required>
                    </div>
                    <div class="form-group mb-2">
                        <label class="form-label">Contenido <small class="text-muted">(o sube un archivo)</small></label>
                        <textarea name="content" class="form-control" rows="3" placeholder="# Tu código aquí..."></textarea>
                    </div>
                    <div class="form-group mb-2">
                        <label class="form-label">O subir archivo</label>
                        <input type="file" name="upload_file" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary" style="width:100%">Crear archivo</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Editor principal -->
    <div class="files-editor">
        <?php if ($activeFile): ?>
            <?php $activePath = $activeFile['mount_path'] ?? $activeFile['fs_path'] ?? ''; ?>
            <div class="card">
                <div class="card-header">
                    <h3><?= \App\Core\View::e(basename($activePath)) ?></h3>
                    <span class="text-muted" style="font-size:.8rem"><?= \App\Core\View::e($activePath) ?></span>
                </div>
                <div class="card-body p-0">
                    <form method="POST" action="<?= APP_URL ?>/bots/<?= $bot['id'] ?>/files/update" id="editorForm">
                        <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
                        <input type="hidden" name="storage_uuid" value="<?= \App\Core\View::e($activeFile['uuid'] ?? '') ?>">
                        <textarea name="content" class="code-editor-full" id="codeEditor" spellcheck="false"><?= \App\Core\View::e($activeFile['content'] ?? '') ?></textarea>
                        <div class="editor-toolbar">
                            <div class="editor-info">
                                <span class="text-muted" id="lineCount">Líneas: <?= substr_count($activeFile['content'] ?? '', "\n") + 1 ?></span>
                                <span class="text-muted" id="charCount">Chars: <?= strlen($activeFile['content'] ?? '') ?></span>
                            </div>
                            <div class="editor-actions">
                                <form method="POST" action="<?= APP_URL ?>/bots/<?= $bot['id'] ?>/files/delete"
                                      style="display:inline" onsubmit="return confirm('¿Eliminar este archivo?')">
                                    <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
                                    <input type="hidden" name="storage_uuid" value="<?= \App\Core\View::e($activeFile['uuid'] ?? '') ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                </form>
                                <button type="submit" form="editorForm" class="btn btn-sm btn-primary">💾 Guardar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($template && !empty($template['docs_first_steps_url'])): ?>
            <div class="card" style="margin-top: 1rem">
                <div class="card-body" style="display:flex; align-items:center; gap:.75rem; font-size:.85rem">
                    <span>📖</span>
                    <div>
                        <strong>Documentación oficial</strong><br>
                        <a href="<?= \App\Core\View::e($template['docs_first_steps_url']) ?>" target="_blank" rel="noopener">
                            <?= \App\Core\View::e($template['docs_first_steps_url']) ?>
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="card">
                <div class="card-body" style="text-align:center; padding:3rem">
                    <h3 style="margin-bottom:.5rem">📁 Gestor de Archivos</h3>
                    <p class="text-muted">Selecciona un archivo de la lista o crea uno nuevo.</p>
                    <?php if ($template && !empty($template['starter_filename'])): ?>
                        <p class="text-muted" style="margin-top:1rem">
                            Este bot usa un archivo de código inicial (<code><?= \App\Core\View::e($template['starter_filename']) ?></code>).
                            Si no lo ves en la lista, re-despliega el bot para que se cree automáticamente.
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* ---- File Manager Layout ---- */
.files-layout {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 1.5rem;
    align-items: start;
}

.files-sidebar .card + .card {
    margin-top: 1rem;
}

/* File list */
.file-list {
    list-style: none;
    padding: 0;
    margin: 0;
}
.file-list li a {
    display: flex;
    align-items: center;
    gap: .5rem;
    padding: .6rem 1rem;
    text-decoration: none;
    color: var(--text);
    border-bottom: 1px solid var(--border);
    transition: background .12s;
    flex-wrap: wrap;
}
.file-list li a:hover {
    background: var(--bg);
    text-decoration: none;
}
.file-list li.active a {
    background: var(--accent-lt);
    color: var(--accent);
    font-weight: 500;
}
.file-list li:last-child a {
    border-bottom: none;
}
.file-icon { font-size: 1.1rem; flex-shrink: 0; }
.file-name { font-size: .9rem; }
.file-path {
    width: 100%;
    font-size: .75rem;
    color: var(--muted);
    padding-left: 1.6rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Storage items */
.storage-item {
    display: flex;
    flex-direction: column;
    padding: .25rem 0;
    gap: .15rem;
}
.storage-item + .storage-item {
    border-top: 1px solid var(--border);
    padding-top: .5rem;
    margin-top: .25rem;
}

/* Code editor */
.code-editor-full {
    width: 100%;
    min-height: 450px;
    padding: 1rem;
    font-family: 'SF Mono', 'Fira Code', 'Consolas', monospace;
    font-size: .85rem;
    line-height: 1.55;
    border: none;
    border-bottom: 1px solid var(--border);
    outline: none;
    resize: vertical;
    background: #fafafa;
    tab-size: 4;
    color: var(--text);
}
.code-editor-full:focus {
    background: #fff;
    box-shadow: inset 0 0 0 2px var(--accent-lt);
}

/* Editor toolbar */
.editor-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: .5rem 1rem;
    background: var(--bg);
    border-top: 1px solid var(--border);
}
.editor-info {
    display: flex;
    gap: 1rem;
    font-size: .8rem;
}
.editor-actions {
    display: flex;
    gap: .5rem;
    align-items: center;
}

/* Responsive */
@media (max-width: 800px) {
    .files-layout {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Estadísticas del editor en tiempo real
const editor = document.getElementById('codeEditor');
if (editor) {
    editor.addEventListener('input', function() {
        const lines = this.value.split('\n').length;
        const chars = this.value.length;
        document.getElementById('lineCount').textContent = 'Líneas: ' + lines;
        document.getElementById('charCount').textContent = 'Chars: ' + chars;
    });

    // Soporte Tab en el editor
    editor.addEventListener('keydown', function(e) {
        if (e.key === 'Tab') {
            e.preventDefault();
            const start = this.selectionStart;
            const end = this.selectionEnd;
            this.value = this.value.substring(0, start) + '    ' + this.value.substring(end);
            this.selectionStart = this.selectionEnd = start + 4;
        }
        // Ctrl+S / Cmd+S para guardar
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            document.getElementById('editorForm').submit();
        }
    });
}
</script>
