<?php $pageTitle = 'Suscripciones'; ?>
<div class="page-header">
    <div>
        <h1>Suscripciones</h1>
        <p class="text-muted"><?= count($subs) ?> suscripciones</p>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Plan</th>
                    <th>Estado</th>
                    <th>Stripe ID</th>
                    <th>Expira</th>
                    <th>Creada</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($subs as $s): ?>
                <tr>
                    <td>#<?= $s['id'] ?></td>
                    <td>
                        <?= \App\Core\View::e($s['user_name']) ?>
                        <small class="text-muted"><?= \App\Core\View::e($s['user_email'] ?? '') ?></small>
                    </td>
                    <td><span class="badge badge-info"><?= \App\Core\View::e($s['plan_name']) ?></span></td>
                    <td>
                        <?php
                        $statusColors = ['active' => 'success', 'free' => 'info', 'trialing' => 'info', 'canceled' => 'warning', 'past_due' => 'danger', 'unpaid' => 'danger'];
                        $color = $statusColors[$s['status']] ?? 'warning';
                        ?>
                        <span class="badge badge-<?= $color ?>"><?= \App\Core\View::e($s['status']) ?></span>
                    </td>
                    <td><code><?= \App\Core\View::e($s['stripe_subscription_id'] ?? '—') ?></code></td>
                    <td><?= $s['current_period_end'] ? date('d/m/Y', strtotime($s['current_period_end'])) : '—' ?></td>
                    <td><?= date('d/m/Y', strtotime($s['created_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
