<?php $pageTitle = 'Planes'; ?>
<div class="page-header">
    <div>
        <h1>💎 Planes</h1>
        <p class="text-muted">Configuración de planes de suscripción</p>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Slug</th>
                    <th>Nombre</th>
                    <th>Precio/mes</th>
                    <th>Max Bots</th>
                    <th>RAM</th>
                    <th>Disco</th>
                    <th>DBs</th>
                    <th>Redis</th>
                    <th>Backups</th>
                    <th>Activo</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($plans as $p): ?>
                <tr>
                    <td>#<?= $p['id'] ?></td>
                    <td><code><?= \App\Core\View::e($p['slug']) ?></code></td>
                    <td><strong><?= \App\Core\View::e($p['name']) ?></strong></td>
                    <td><?= $p['price_monthly'] > 0 ? number_format($p['price_monthly'], 2) . ' €' : 'Gratis' ?></td>
                    <td><?= $p['max_bots'] ?: '∞' ?></td>
                    <td><?= $p['ram_mb'] >= 1024 ? number_format($p['ram_mb'] / 1024, 1) . ' GB' : $p['ram_mb'] . ' MB' ?></td>
                    <td>
                        <?php if ($p['disk_gb'] > 0): ?>
                            <?= $p['disk_gb'] ?> GB
                        <?php elseif (!empty($p['disk_temp_mb']) && $p['disk_temp_mb'] > 0): ?>
                            <?= $p['disk_temp_mb'] ?> MB temp
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                    <td><?= $p['max_databases'] ?: '—' ?></td>
                    <td><?= $p['has_redis'] ? '✅' : '—' ?></td>
                    <td><?= $p['has_backups'] ? '✅' : '—' ?></td>
                    <td><?= $p['is_active'] ? '✅' : '❌' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
