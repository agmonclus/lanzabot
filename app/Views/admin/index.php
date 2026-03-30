<?php $pageTitle = 'Panel de Administración'; ?>
<div class="page-header">
    <div>
        <h1>⚙️ Panel de Administración</h1>
        <p class="text-muted">Resumen general de Lanzabot</p>
    </div>
</div>

<!-- Stats cards -->
<div class="admin-stats-grid">
    <div class="stat-card">
        <div class="stat-card-icon">👥</div>
        <div class="stat-card-data">
            <div class="stat-card-number"><?= $stats['total_users'] ?></div>
            <div class="stat-card-label">Usuarios totales</div>
        </div>
        <div class="stat-card-extra">+<?= $stats['users_today'] ?> hoy</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon">🤖</div>
        <div class="stat-card-data">
            <div class="stat-card-number"><?= $stats['total_bots'] ?></div>
            <div class="stat-card-label">Bots creados</div>
        </div>
        <div class="stat-card-extra"><?= $stats['running_bots'] ?> activos</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon">📦</div>
        <div class="stat-card-data">
            <div class="stat-card-number"><?= $stats['total_templates'] ?></div>
            <div class="stat-card-label">Plantillas</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon">💳</div>
        <div class="stat-card-data">
            <div class="stat-card-number"><?= $stats['active_subs'] ?></div>
            <div class="stat-card-label">Suscripciones de pago</div>
        </div>
        <div class="stat-card-extra"><?= $stats['free_subs'] ?> gratuitas</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon">💰</div>
        <div class="stat-card-data">
            <div class="stat-card-number"><?= number_format($stats['total_revenue'] / 100, 2) ?> €</div>
            <div class="stat-card-label">Ingresos totales</div>
        </div>
    </div>
</div>

<!-- Distribución de planes -->
<section class="section">
    <h2>Distribución de planes</h2>
    <div class="admin-plans-bar">
        <?php foreach ($plans_distribution as $pd): ?>
        <div class="plan-bar-item">
            <div class="plan-bar-label"><?= \App\Core\View::e($pd['name']) ?></div>
            <div class="plan-bar-value"><?= $pd['total'] ?></div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<div class="admin-two-cols">
    <!-- Últimos usuarios -->
    <section class="section">
        <div class="section-header">
            <h2>Últimos usuarios</h2>
            <a href="<?= APP_URL ?>/admin/users" class="link-more">Ver todos →</a>
        </div>
        <table class="table">
            <thead><tr><th>Nombre</th><th>Email</th><th>Registro</th></tr></thead>
            <tbody>
            <?php foreach ($recent_users as $u): ?>
                <tr>
                    <td><?= \App\Core\View::e($u['name']) ?></td>
                    <td><?= \App\Core\View::e($u['email'] ?? '—') ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($u['created_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <!-- Últimos bots -->
    <section class="section">
        <div class="section-header">
            <h2>Últimos bots</h2>
            <a href="<?= APP_URL ?>/admin/bots" class="link-more">Ver todos →</a>
        </div>
        <table class="table">
            <thead><tr><th>Bot</th><th>Usuario</th><th>Estado</th></tr></thead>
            <tbody>
            <?php foreach ($recent_bots as $b): ?>
                <tr>
                    <td><?= \App\Core\View::e($b['name']) ?></td>
                    <td><?= \App\Core\View::e($b['user_name']) ?></td>
                    <td><span class="badge badge-<?= ($b['coolify_status'] ?? 'stopped') === 'running' ? 'success' : 'warning' ?>"><?= \App\Core\View::e($b['coolify_status'] ?? 'stopped') ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</div>
