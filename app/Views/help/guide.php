<?php
$pageTitle = $template['name'] . ' — Guía';
$platformIcons = [
    'telegram' => '✈️', 'discord' => '🎮', 'slack' => '💬', 'whatsapp' => '📱',
    'twitch' => '🎮', 'matrix' => '🟢', 'reddit' => '🔶', 'mastodon' => '🐘',
    'multi' => '🌐', 'other' => '⚙️'
];
$diffLabels = ['easy' => 'Fácil', 'medium' => 'Medio', 'advanced' => 'Avanzado'];
$diffColors = ['easy' => 'success', 'medium' => 'warning', 'advanced' => 'danger'];
$planOrder  = ['free' => 0, 'starter' => 1, 'medium' => 2, 'pro' => 3, 'custom' => 4];
$userPlanOrder = $planOrder[$plan['slug']] ?? 0;
$reqPlanOrder  = $planOrder[$template['min_plan_slug']] ?? 0;
$canInstall    = (int)$user['id'] === 1 || $userPlanOrder >= $reqPlanOrder;
?>

<div class="page-header">
    <div>
        <a href="<?= APP_URL ?>/help" class="breadcrumb">← Centro de ayuda</a>
        <h1><?= $template['icon'] ?> <?= \App\Core\View::e($template['name']) ?></h1>
        <div style="margin-top:.5rem; display:flex; gap:.5rem; flex-wrap:wrap;">
            <span class="badge badge-platform badge-<?= $template['platform'] ?>">
                <?= $platformIcons[$template['platform']] ?? '⚙️' ?> <?= ucfirst($template['platform']) ?>
            </span>
            <span class="badge badge-<?= $diffColors[$template['difficulty']] ?? 'info' ?>">
                <?= $diffLabels[$template['difficulty']] ?? $template['difficulty'] ?>
            </span>
            <?php if (!empty($template['auto_update_supported'])): ?>
                <span class="badge badge-info">🔄 Auto-actualizable</span>
            <?php endif; ?>
            <span class="badge badge-info">v<?= \App\Core\View::e($template['version'] ?? '1.0.0') ?></span>
        </div>
    </div>
    <?php if ($canInstall): ?>
    <a href="<?= APP_URL ?>/bots/from-template/<?= $template['id'] ?>" class="btn btn-primary">⚡ Instalar ahora</a>
    <?php else: ?>
    <a href="<?= APP_URL ?>/plans" class="btn btn-outline">🔒 Requiere plan <?= ucfirst($template['min_plan_slug']) ?></a>
    <?php endif; ?>
</div>

<!-- Descripción completa -->
<div class="card" style="margin-bottom:1.5rem">
    <div class="card-body">
        <p style="font-size:1.05rem; line-height:1.7"><?= nl2br(\App\Core\View::e($template['description'])) ?></p>
    </div>
</div>

<div class="bot-layout">
    <div class="bot-main">
        <!-- Instrucciones paso a paso -->
        <?php if (!empty($template['setup_instructions'])): ?>
        <div class="card">
            <div class="card-header"><h3>📋 Paso a paso</h3></div>
            <div class="card-body">
                <div class="setup-steps" style="font-size:.95rem; line-height:1.8">
                    <?= nl2br(\App\Core\View::e($template['setup_instructions'])) ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Variables necesarias -->
        <?php if (!empty($requiredVars)): ?>
        <div class="card">
            <div class="card-header"><h3>🔑 Variables de configuración</h3></div>
            <div class="card-body">
                <p class="text-muted" style="margin-bottom:1rem">Estas son las variables que necesitas configurar al instalar este bot:</p>
                <table class="table">
                    <thead>
                        <tr><th>Variable</th><th>Descripción</th><th>Obligatoria</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requiredVars as $v): ?>
                        <tr>
                            <td><code><?= \App\Core\View::e($v['key'] ?? '') ?></code></td>
                            <td><?= \App\Core\View::e($v['label'] ?? $v['key'] ?? '') ?></td>
                            <td><?= !empty($v['required']) ? '<span class="badge badge-danger">Sí</span>' : '<span class="badge badge-info">No</span>' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Variables por defecto -->
        <?php if (!empty($defaultVars)): ?>
        <div class="card">
            <div class="card-header"><h3>⚙️ Configuración por defecto</h3></div>
            <div class="card-body">
                <p class="text-muted" style="margin-bottom:1rem">Estos valores se aplican automáticamente. Puedes cambiarlos después desde el panel del bot:</p>
                <table class="table">
                    <thead><tr><th>Variable</th><th>Valor por defecto</th></tr></thead>
                    <tbody>
                        <?php foreach ($defaultVars as $key => $val): ?>
                        <tr>
                            <td><code><?= \App\Core\View::e($key) ?></code></td>
                            <td><?= $val ? \App\Core\View::e($val) : '<em class="text-muted">(vacío — debes configurarlo)</em>' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="bot-sidebar">
        <!-- Info técnica -->
        <div class="card">
            <div class="card-header"><h3>📦 Info técnica</h3></div>
            <div class="card-body">
                <div class="stat-row"><span>Imagen Docker</span><code><?= \App\Core\View::e($template['docker_image']) ?></code></div>
                <div class="stat-row"><span>RAM mínima</span><span><?= $template['ram_mb_min'] ?> MB</span></div>
                <div class="stat-row"><span>Plan mínimo</span><span><?= ucfirst($template['min_plan_slug']) ?></span></div>
                <div class="stat-row"><span>Dificultad</span><span><?= $diffLabels[$template['difficulty']] ?? $template['difficulty'] ?></span></div>
                <div class="stat-row"><span>Instalaciones</span><span><?= $template['install_count'] ?></span></div>
                <div class="stat-row"><span>Auto-update</span><span><?= !empty($template['auto_update_supported']) ? '✅ Sí' : '❌ No' ?></span></div>
            </div>
        </div>

        <!-- Documentación externa -->
        <?php if (!empty($template['documentation_url'])): ?>
        <div class="card">
            <div class="card-header"><h3>🔗 Enlaces</h3></div>
            <div class="card-body">
                <a href="<?= \App\Core\View::e($template['documentation_url']) ?>" target="_blank" rel="noopener" class="btn btn-outline btn-full">
                    📖 Documentación del proyecto
                </a>
                <?php if (!empty($template['git_repo_url'])): ?>
                <a href="<?= \App\Core\View::e($template['git_repo_url']) ?>" target="_blank" rel="noopener" class="btn btn-ghost btn-full" style="margin-top:.5rem">
                    🐙 Repositorio en GitHub
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tags -->
        <?php if (!empty($template['tags'])): ?>
        <div class="card">
            <div class="card-header"><h3>🏷️ Etiquetas</h3></div>
            <div class="card-body">
                <div style="display:flex; gap:.5rem; flex-wrap:wrap;">
                    <?php foreach (explode(',', $template['tags']) as $tag): ?>
                        <span class="badge badge-info"><?= \App\Core\View::e(trim($tag)) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Acción -->
        <div style="margin-top:1rem; text-align:center;">
            <?php if ($canInstall): ?>
            <a href="<?= APP_URL ?>/bots/from-template/<?= $template['id'] ?>" class="btn btn-primary btn-full btn-lg">
                ⚡ Instalar en 1 clic
            </a>
            <?php else: ?>
            <a href="<?= APP_URL ?>/plans" class="btn btn-outline btn-full">
                🔒 Necesitas plan <?= ucfirst($template['min_plan_slug']) ?>
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>
