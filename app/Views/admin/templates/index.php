<?php $pageTitle = 'Plantillas de Bots'; ?>
<div class="page-header">
    <div>
        <h1>📦 Plantillas de Bots</h1>
        <p class="text-muted"><?= count($templates) ?> plantillas</p>
    </div>
    <a href="<?= APP_URL ?>/admin/templates/create" class="btn btn-primary">+ Nueva plantilla</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table">
            <thead>
                <tr>
                    <th></th>
                    <th>Nombre</th>
                    <th>Plataforma</th>
                    <th>Categoría</th>
                    <th>Dificultad</th>
                    <th>Plan mín.</th>
                    <th>Destacada</th>
                    <th>Activa</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($templates as $t): ?>
                <tr>
                    <td style="font-size:1.3rem"><?= $t['icon'] ?></td>
                    <td>
                        <strong><?= \App\Core\View::e($t['name']) ?></strong>
                        <br><small class="text-muted"><?= \App\Core\View::e($t['slug']) ?></small>
                    </td>
                    <td>
                        <?php $pIcons = ['telegram' => '✈️', 'discord' => '🎮', 'multi' => '🌐', 'other' => '⚙️']; ?>
                        <?= $pIcons[$t['platform']] ?? '⚙️' ?> <?= \App\Core\View::e($t['platform']) ?>
                    </td>
                    <td><span class="badge"><?= \App\Core\View::e($t['category']) ?></span></td>
                    <td>
                        <?php $diffColors = ['easy' => 'success', 'medium' => 'warning', 'advanced' => 'danger']; ?>
                        <span class="badge badge-<?= $diffColors[$t['difficulty']] ?? 'info' ?>"><?= \App\Core\View::e($t['difficulty']) ?></span>
                    </td>
                    <td><code><?= \App\Core\View::e($t['min_plan_slug']) ?></code></td>
                    <td><?= $t['is_featured'] ? '⭐' : '—' ?></td>
                    <td><?= $t['is_active'] ? '✅' : '❌' ?></td>
                    <td>
                        <div style="display:flex;gap:.3rem">
                            <a href="<?= APP_URL ?>/admin/templates/<?= $t['id'] ?>/edit" class="btn btn-xs btn-outline">Editar</a>
                            <form method="POST" action="<?= APP_URL ?>/admin/templates/<?= $t['id'] ?>/delete" onsubmit="return confirm('¿Eliminar esta plantilla?')">
                                <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
                                <button class="btn btn-xs btn-danger">Eliminar</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
