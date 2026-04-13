<?php
$pageTitle = 'Eliminar bot: ' . $bot['name'];
$platformIcons = [
    'telegram' => '', 'discord' => '', 'slack' => '', 'whatsapp' => '',
    'twitch' => '', 'matrix' => '', 'reddit' => '', 'mastodon' => '',
    'multi' => '', 'other' => ''
];
$icon = $platformIcons[$bot['platform']] ?? '';
?>

<div class="page-header">
    <div>
        <a href="<?= APP_URL ?>/bots/<?= $bot['id'] ?>" class="breadcrumb">← Volver al bot</a>
        <h1>Eliminar bot</h1>
    </div>
</div>

<div style="max-width: 560px; margin: 2rem auto;">
    <div class="card" style="border: 2px solid var(--danger, #e53e3e);">
        <div class="card-body" style="padding: 2rem; text-align: center;">
            <div style="font-size: 3rem; margin-bottom: 1rem;"></div>

            <h2 style="margin: 0 0 .5rem; color: var(--danger, #e53e3e);">
                ¿Eliminar este bot?
            </h2>

            <p style="margin: 0 0 1.5rem; color: var(--text-muted, #666);">
                Esta acción <strong>no se puede deshacer</strong>.
            </p>

            <div class="card" style="background: var(--bg-secondary, #f7fafc); margin-bottom: 1.5rem; text-align: left;">
                <div class="card-body" style="padding: 1rem 1.25rem;">
                    <p style="margin: 0 0 .25rem; font-weight: 600; font-size: 1.1rem;">
                        <?= $icon ?> <?= \App\Core\View::e($bot['name']) ?>
                    </p>
                    <p style="margin: 0; font-size: .875rem; color: var(--text-muted, #666);">
                        Plataforma: <?= ucfirst(\App\Core\View::e($bot['platform'])) ?>
                        &nbsp;·&nbsp;
                        Estado: <?= \App\Core\View::e($bot['coolify_status'] ?? 'stopped') ?>
                    </p>
                </div>
            </div>

            <div style="background: var(--bg-secondary, #f7fafc); border-radius: .5rem; padding: 1rem 1.25rem; margin-bottom: 1.5rem; text-align: left; font-size: .875rem;">
                <p style="margin: 0 0 .5rem; font-weight: 600;">Se eliminará:</p>
                <ul style="margin: 0; padding-left: 1.25rem; color: var(--text-muted, #666); line-height: 1.8;">
                    <li>Contenedor y despliegue en Coolify</li>
                    <li>Archivos de código subidos</li>
                    <li>Variables de entorno configuradas</li>
                    <li>Registro en la base de datos</li>
                </ul>
                <p style="margin: .75rem 0 0; font-weight: 600;">No se eliminará:</p>
                <ul style="margin: 0; padding-left: 1.25rem; color: var(--text-muted, #666); line-height: 1.8;">
                    <li>Logs históricos</li>
                    <li>Bases de datos o almacenamiento externo asociado</li>
                </ul>
            </div>

            <div style="display: flex; gap: 1rem; justify-content: center;">
                <a href="<?= APP_URL ?>/bots/<?= $bot['id'] ?>" class="btn btn-outline">
                    Cancelar
                </a>
                <form method="POST" action="<?= APP_URL ?>/bots/<?= $bot['id'] ?>/delete">
                    <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
                    <button type="submit" class="btn btn-danger">
                        Sí, eliminar bot
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
