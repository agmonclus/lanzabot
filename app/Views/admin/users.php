<?php $pageTitle = 'Usuarios'; ?>
<div class="page-header">
    <div>
        <h1>Usuarios</h1>
        <p class="text-muted"><?= count($users) ?> usuarios registrados</p>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Proveedor</th>
                    <th>Plan</th>
                    <th>Bots</th>
                    <th>Admin</th>
                    <th>Registro</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td>#<?= $u['id'] ?></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:.5rem">
                            <?php if (!empty($u['avatar'])): ?>
                                <img src="<?= \App\Core\View::e($u['avatar']) ?>" style="width:24px;height:24px;border-radius:50%">
                            <?php endif; ?>
                            <?= \App\Core\View::e($u['name']) ?>
                        </div>
                    </td>
                    <td><?= \App\Core\View::e($u['email'] ?? '—') ?></td>
                    <td>
                        <?php if ($u['google_id']): ?><span class="badge">Google</span><?php endif; ?>
                        <?php if ($u['discord_id']): ?><span class="badge">Discord</span><?php endif; ?>
                        <?php if ($u['telegram_id']): ?><span class="badge">Telegram</span><?php endif; ?>
                        <?php if (!empty($u['password_hash'])): ?><span class="badge">Email</span><?php endif; ?>
                    </td>
                    <td><span class="badge badge-info"><?= \App\Core\View::e($u['plan_name'] ?? 'Sin plan') ?></span></td>
                    <td><?= $u['bot_count'] ?></td>
                    <td><?= $u['is_admin'] ? '' : '—' ?></td>
                    <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
