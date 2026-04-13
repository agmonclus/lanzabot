<?php
$pageTitle = 'Bot desplegado — ' . \App\Core\View::e($bot['name']);
$platformIcons = ['telegram' => '', 'discord' => '', 'multi' => '', 'other' => ''];
?>

<div class="deployed-container">
    <?php if ($deployed): ?>
        <div class="deployed-hero deployed-success">
            <div class="deployed-icon"></div>
            <h1>¡Despliegue iniciado!</h1>
            <p class="text-muted"><?= \App\Core\View::e($bot['name']) ?> se está desplegando. Puedes ver el estado y los logs en el panel del bot.</p>
        </div>
    <?php else: ?>
        <div class="deployed-hero deployed-warning">
            <div class="deployed-icon"></div>
            <h1>Bot creado, pero el despliegue tuvo un problema</h1>
            <p class="text-muted">Error: <?= \App\Core\View::e($deployError) ?></p>
            <p>Puedes intentar desplegar manualmente desde el panel del bot.</p>
        </div>
    <?php endif; ?>

    <!-- Resumen del bot -->
    <div class="deployed-summary">
        <div class="deployed-card">
            <h3><?= $template['icon'] ?> <?= \App\Core\View::e($bot['name']) ?></h3>
            <table class="deployed-info-table">
                <tr>
                    <td class="label">Plataforma</td>
                    <td><?= $platformIcons[$bot['platform']] ?? '' ?> <?= ucfirst($bot['platform']) ?></td>
                </tr>
                <tr>
                    <td class="label">Plantilla</td>
                    <td><?= \App\Core\View::e($template['name']) ?></td>
                </tr>
                <tr>
                    <td class="label">Imagen Docker</td>
                    <td><code><?= \App\Core\View::e($bot['docker_image']) ?></code></td>
                </tr>
                <tr>
                    <td class="label">Estado</td>
                    <td>
                        <?php if ($deployed): ?>
                            <span class="status-badge status-deploying">Desplegando...</span>
                        <?php else: ?>
                            <span class="status-badge status-error">Error al desplegar</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Variables configuradas -->
    <?php if (!empty($envVars)): ?>
    <div class="deployed-section">
        <h3>Variables de entorno configuradas</h3>
        <div class="deployed-card">
            <table class="deployed-env-table">
                <?php foreach ($envVars as $key => $value): ?>
                <tr>
                    <td class="label"><code><?= \App\Core\View::e($key) ?></code></td>
                    <td><code class="env-value"><?= \App\Core\View::e(mb_strlen($value) > 20 ? mb_substr($value, 0, 8) . '••••' . mb_substr($value, -4) : $value) ?></code></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Instrucciones post-deploy -->
    <?php if (!empty($setupInstructions)): ?>
    <div class="deployed-section">
        <h3>Pasos para poner en marcha tu bot</h3>
        <div class="deployed-card setup-steps">
            <?= nl2br(\App\Core\View::e($setupInstructions)) ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($docUrl)): ?>
    <div class="deployed-section">
        <a href="<?= \App\Core\View::e($docUrl) ?>" target="_blank" rel="noopener" class="btn btn-outline">
            Documentación del proyecto →
        </a>
    </div>
    <?php endif; ?>

    <!-- Acciones -->
    <div class="deployed-actions">
        <a href="<?= APP_URL ?>/bots/<?= $bot['id'] ?>" class="btn btn-primary btn-lg">
            Ir al panel del bot
        </a>
        <a href="<?= APP_URL ?>/bots/create" class="btn btn-outline">
            Instalar otro bot
        </a>
        <a href="<?= APP_URL ?>/dashboard" class="btn btn-ghost">
            ← Dashboard
        </a>
    </div>

    <?php if ($deployed && !empty($template['auto_update_supported'])): ?>
    <div style="text-align:center; margin-top:1rem; padding:1rem; background:var(--accent-lt); border-radius:var(--radius);">
        <p style="margin:0; font-size:.88rem;">
            <strong>Auto-actualización activada.</strong> Tu bot se actualizará automáticamente cuando publiquemos mejoras.
            Puedes gestionar esto desde el <a href="<?= APP_URL ?>/bots/<?= $bot['id'] ?>">panel del bot</a>.
        </p>
    </div>
    <?php endif; ?>
</div>