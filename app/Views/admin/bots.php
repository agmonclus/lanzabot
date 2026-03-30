<?php $pageTitle = 'Bots'; ?>
<div class="page-header">
    <div>
        <h1>🤖 Bots</h1>
        <p class="text-muted"><?= count($bots) ?> bots desplegados</p>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Plataforma</th>
                    <th>Usuario</th>
                    <th>Docker Image</th>
                    <th>Estado</th>
                    <th>Código</th>
                    <th>Creado</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($bots as $b): ?>
                <tr>
                    <td>#<?= $b['id'] ?></td>
                    <td><?= \App\Core\View::e($b['name']) ?></td>
                    <td>
                        <?php $icons = ['telegram' => '✈️', 'discord' => '🎮', 'other' => '⚙️']; ?>
                        <?= $icons[$b['platform']] ?? '⚙️' ?> <?= \App\Core\View::e($b['platform']) ?>
                    </td>
                    <td>
                        <?= \App\Core\View::e($b['user_name']) ?>
                        <small class="text-muted"><?= \App\Core\View::e($b['user_email'] ?? '') ?></small>
                    </td>
                    <td><code><?= \App\Core\View::e($b['docker_image'] ?? '—') ?></code></td>
                    <td>
                        <span class="badge badge-<?= ($b['coolify_status'] ?? 'stopped') === 'running' ? 'success' : 'warning' ?>">
                            <?= \App\Core\View::e($b['coolify_status'] ?? 'stopped') ?>
                        </span>
                    </td>
                    <td><?= $b['code_uploaded'] ? '✅' : '❌' ?></td>
                    <td><?= date('d/m/Y', strtotime($b['created_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
