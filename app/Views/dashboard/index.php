<?php $pageTitle = 'Dashboard'; ?>
<div class="page-header">
    <div>
        <h1>Dashboard</h1>
        <p class="text-muted">Hola, <?= \App\Core\View::e(explode(' ', $user['name'])[0]) ?> 👋</p>
    </div>
    <a href="<?= APP_URL ?>/bots/create" class="btn btn-primary">
        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
        Desplegar nuevo bot
    </a>
</div>

<!-- Plan banner -->
<div class="plan-banner">
    <div class="plan-banner-info">
        <span class="plan-badge"><?= \App\Core\View::e(strtoupper($plan['slug'])) ?></span>
        <span>
            <?= count($bots) ?> / <?= $plan['max_bots'] > 0 ? $plan['max_bots'] : '∞' ?> bots
            &nbsp;·&nbsp;
            <?= $plan['ram_mb'] >= 1024 ? number_format($plan['ram_mb'] / 1024, 1) . ' GB' : $plan['ram_mb'] . ' MB' ?> RAM
            <?php if ($plan['disk_gb'] > 0): ?>
                &nbsp;·&nbsp; <?= $plan['disk_gb'] ?> GB disco permanente
            <?php elseif (!empty($plan['disk_temp_mb']) && $plan['disk_temp_mb'] > 0): ?>
                &nbsp;·&nbsp; <?= $plan['disk_temp_mb'] ?> MB disco temporal
            <?php endif; ?>
        </span>
    </div>
    <?php if ($plan['slug'] === 'free'): ?>
        <a href="<?= APP_URL ?>/plans" class="btn btn-sm btn-outline">Actualizar plan →</a>
    <?php endif; ?>
</div>

<!-- Bots list -->
<section class="section">
    <h2>Mis bots</h2>

    <?php if (empty($bots)): ?>
    <div class="empty-state">
        <div class="empty-icon">🤖</div>
        <p>Aún no tienes bots. ¡Despliega el primero en menos de un minuto!</p>
        <a href="<?= APP_URL ?>/bots/create" class="btn btn-primary">Desplegar bot</a>
    </div>
    <?php else: ?>
    <div class="bot-grid">
        <?php foreach ($bots as $bot): ?>
        <a href="<?= APP_URL ?>/bots/<?= $bot['id'] ?>" class="bot-card">
            <div class="bot-card-header">
                <span class="bot-platform <?= $bot['platform'] ?>"><?= $bot['platform'] === 'telegram' ? '✈️' : '🎮' ?></span>
                <span class="bot-status status-<?= \App\Core\View::e($bot['coolify_status'] ?? 'stopped') ?>">
                    <?= \App\Core\View::e($bot['coolify_status'] ?? 'stopped') ?>
                </span>
            </div>
            <div class="bot-card-name"><?= \App\Core\View::e($bot['name']) ?></div>
            <?php if ($bot['description']): ?>
            <div class="bot-card-desc"><?= \App\Core\View::e($bot['description']) ?></div>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>

        <?php if ($plan['max_bots'] === 0 || count($bots) < $plan['max_bots']): ?>
        <a href="<?= APP_URL ?>/bots/create" class="bot-card bot-card-new">
            <span class="new-icon">+</span>
            <span>Nuevo bot</span>
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</section>

<!-- Recent payments -->
<?php if (!empty($payments)): ?>
<section class="section">
    <div class="section-header">
        <h2>Pagos recientes</h2>
        <a href="<?= APP_URL ?>/billing" class="link-more">Ver todos →</a>
    </div>
    <table class="table">
        <thead><tr><th>Fecha</th><th>Concepto</th><th>Importe</th><th>Estado</th></tr></thead>
        <tbody>
        <?php foreach ($payments as $pay): ?>
            <tr>
                <td><?= date('d/m/Y', strtotime($pay['created_at'])) ?></td>
                <td><?= \App\Core\View::e($pay['description']) ?></td>
                <td><?= number_format($pay['amount'] / 100, 2) ?> <?= strtoupper($pay['currency']) ?></td>
                <td><span class="badge badge-<?= $pay['status'] === 'paid' ? 'success' : 'warning' ?>"><?= $pay['status'] ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php endif; ?>
