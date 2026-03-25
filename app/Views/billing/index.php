<?php $pageTitle = 'Facturación'; ?>
<div class="page-header">
    <h1>Facturación</h1>
</div>

<!-- Current subscription -->
<div class="card mb-4">
    <div class="card-header"><h3>Suscripción actual</h3></div>
    <div class="card-body">
        <?php if ($sub): ?>
        <div class="sub-info">
            <div>
                <span class="plan-badge"><?= strtoupper(\App\Core\View::e($plan['slug'] ?? 'free')) ?></span>
                <strong><?= \App\Core\View::e($plan['name'] ?? 'Free') ?></strong>
                <span class="text-muted ml-2">
                    <?php if ($plan['price_weekly'] > 0): ?>
                        <?= number_format($plan['price_weekly'], 2) ?>€/semana
                    <?php else: ?>
                        Gratuito
                    <?php endif; ?>
                </span>
            </div>
            <div>
                <span class="badge badge-<?= in_array($sub['status'], ['active','free','trialing']) ? 'success' : 'warning' ?>">
                    <?= \App\Core\View::e($sub['status']) ?>
                </span>
                <?php if ($sub['current_period_end']): ?>
                    <span class="text-muted ml-2">Renueva <?= date('d/m/Y', strtotime($sub['current_period_end'])) ?></span>
                <?php endif; ?>
            </div>
        </div>
        <?php if ($user['stripe_customer_id']): ?>
        <div class="mt-3">
            <a href="<?= APP_URL ?>/billing/portal" class="btn btn-outline">
                Gestionar suscripción en Stripe →
            </a>
            <small class="text-muted ml-2">Cambia plan, cancela, actualiza método de pago</small>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <p class="text-muted">No tienes suscripción activa.</p>
        <a href="<?= APP_URL ?>/plans" class="btn btn-primary">Ver planes</a>
        <?php endif; ?>
    </div>
</div>

<!-- Payment history -->
<div class="card">
    <div class="card-header"><h3>Historial de pagos</h3></div>
    <div class="card-body p-0">
        <?php if (empty($payments)): ?>
        <div class="empty-state p-4">
            <p class="text-muted">No hay pagos registrados.</p>
        </div>
        <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Concepto</th>
                    <th>Importe</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($payments as $pay): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($pay['created_at'])) ?></td>
                    <td><?= \App\Core\View::e($pay['description']) ?></td>
                    <td><?= number_format($pay['amount'] / 100, 2) ?> <?= strtoupper($pay['currency']) ?></td>
                    <td>
                        <span class="badge badge-<?= $pay['status'] === 'paid' ? 'success' : 'warning' ?>">
                            <?= \App\Core\View::e($pay['status']) ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
