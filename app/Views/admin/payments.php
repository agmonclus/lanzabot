<?php $pageTitle = 'Pagos'; ?>
<div class="page-header">
    <div>
        <h1>Pagos</h1>
        <p class="text-muted"><?= count($payments) ?> pagos registrados</p>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Importe</th>
                    <th>Moneda</th>
                    <th>Estado</th>
                    <th>Descripción</th>
                    <th>Stripe Invoice</th>
                    <th>Fecha pago</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($payments as $p): ?>
                <tr>
                    <td>#<?= $p['id'] ?></td>
                    <td>
                        <?= \App\Core\View::e($p['user_name']) ?>
                        <small class="text-muted"><?= \App\Core\View::e($p['user_email'] ?? '') ?></small>
                    </td>
                    <td><strong><?= number_format($p['amount'] / 100, 2) ?></strong></td>
                    <td><?= strtoupper(\App\Core\View::e($p['currency'])) ?></td>
                    <td>
                        <span class="badge badge-<?= $p['status'] === 'paid' ? 'success' : 'warning' ?>">
                            <?= \App\Core\View::e($p['status']) ?>
                        </span>
                    </td>
                    <td><?= \App\Core\View::e($p['description'] ?? '—') ?></td>
                    <td><code><?= \App\Core\View::e(substr($p['stripe_invoice_id'] ?? '—', 0, 20)) ?></code></td>
                    <td><?= $p['paid_at'] ? date('d/m/Y H:i', strtotime($p['paid_at'])) : '—' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
