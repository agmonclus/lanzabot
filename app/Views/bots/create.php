<?php
$pageTitle = 'Desplegar nuevo bot';
$platformIcons = ['telegram' => '✈️', 'discord' => '🎮', 'multi' => '🌐', 'other' => '⚙️'];
$diffLabels    = ['easy' => 'Fácil', 'medium' => 'Medio', 'advanced' => 'Avanzado'];
$diffColors    = ['easy' => 'success', 'medium' => 'warning', 'advanced' => 'danger'];
?>
<div class="page-header">
    <div>
        <h1>🚀 Desplegar nuevo bot</h1>
        <p class="text-muted">Elige una plantilla y tendrás tu bot funcionando en minutos</p>
    </div>
</div>

<!-- Info de plan -->
<div class="plan-banner" style="margin-bottom:1.5rem">
    <div class="plan-banner-info">
        <span class="plan-badge"><?= \App\Core\View::e(strtoupper($plan['slug'])) ?></span>
        <span>
            <?= $botCount ?> / <?= $plan['max_bots'] > 0 ? $plan['max_bots'] : '∞' ?> bots usados
            &nbsp;·&nbsp;
            <?= $plan['ram_mb'] >= 1024 ? number_format($plan['ram_mb'] / 1024, 1) . ' GB' : $plan['ram_mb'] . ' MB' ?> RAM
        </span>
    </div>
    <?php if ($plan['slug'] === 'free' && !$isAdmin): ?>
        <a href="<?= APP_URL ?>/plans" class="btn btn-sm btn-outline">Mejorar plan →</a>
    <?php endif; ?>
</div>

<!-- Filtros -->
<div class="tpl-filters">
    <button class="tpl-filter active" data-filter="all">Todos</button>
    <button class="tpl-filter" data-filter="telegram">✈️ Telegram</button>
    <button class="tpl-filter" data-filter="discord">🎮 Discord</button>
    <button class="tpl-filter" data-filter="multi">🌐 Multi</button>
    <button class="tpl-filter" data-filter="ai">🧠 IA</button>
</div>

<!-- Catálogo de plantillas -->
<div class="tpl-grid" id="templateGrid">
    <?php foreach ($templates as $t):
        $platform    = $t['platform'];
        $category    = $t['category'];
        $canInstall  = $isAdmin || $canCreateMore;
        $planOk      = $isAdmin || $userPlanOrder >= $planOrder[$t['min_plan_slug']];
        $available   = $canInstall && $planOk;
    ?>
    <div class="tpl-card <?= !$available ? 'tpl-locked' : '' ?>"
         data-platform="<?= \App\Core\View::e($platform) ?>"
         data-category="<?= \App\Core\View::e($category) ?>">
        <div class="tpl-card-head">
            <span class="tpl-icon"><?= $t['icon'] ?></span>
            <div class="tpl-badges">
                <span class="badge badge-platform badge-<?= $platform ?>"><?= $platformIcons[$platform] ?? '⚙️' ?> <?= ucfirst($platform) ?></span>
                <span class="badge badge-<?= $diffColors[$t['difficulty']] ?? 'info' ?>"><?= $diffLabels[$t['difficulty']] ?? $t['difficulty'] ?></span>
            </div>
        </div>
        <div class="tpl-card-body">
            <h3 class="tpl-name"><?= \App\Core\View::e($t['name']) ?></h3>
            <p class="tpl-desc"><?= \App\Core\View::e($t['short_description']) ?></p>
        </div>
        <div class="tpl-card-meta">
            <?php if ($t['min_plan_slug'] !== 'free'): ?>
                <span class="tpl-plan-req">Plan <?= ucfirst($t['min_plan_slug']) ?>+</span>
            <?php else: ?>
                <span class="tpl-plan-free">Gratis</span>
            <?php endif; ?>
            <span class="tpl-installs"><?= $t['install_count'] ?> instalaciones</span>
        </div>
        <div class="tpl-card-footer">
            <?php if ($available): ?>
                <a href="<?= APP_URL ?>/bots/from-template/<?= $t['id'] ?>" class="btn btn-primary btn-full">
                    ⚡ Instalar
                </a>
            <?php elseif (!$planOk): ?>
                <a href="<?= APP_URL ?>/plans" class="btn btn-outline btn-full">
                    🔒 Requiere plan <?= ucfirst($t['min_plan_slug']) ?>
                </a>
            <?php else: ?>
                <span class="btn btn-ghost btn-full" style="cursor:default">
                    Límite de bots alcanzado
                </span>
            <?php endif; ?>
        </div>
        <?php if ($t['is_featured']): ?>
            <div class="tpl-featured-badge">⭐</div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>

<!-- Separador - creación manual -->
<div class="tpl-manual-sep">
    <span>¿Tienes tu propio código?</span>
</div>

<div class="form-card" style="max-width:600px; margin:0 auto">
    <h3 style="margin-bottom:.75rem">📁 Crear bot manualmente</h3>
    <p class="text-muted" style="margin-bottom:1rem">Sube tu propio código y configúralo tú mismo.</p>
    <form method="POST" action="<?= APP_URL ?>/bots">
        <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
        <div class="form-group">
            <label for="name">Nombre del bot <span class="required">*</span></label>
            <input type="text" id="name" name="name" class="form-control" placeholder="Mi bot personalizado" required>
        </div>
        <div class="form-group">
            <label>Plataforma</label>
            <div class="platform-selector">
                <label class="platform-option"><input type="radio" name="platform" value="telegram" checked><span>✈️ Telegram</span></label>
                <label class="platform-option"><input type="radio" name="platform" value="discord"><span>🎮 Discord</span></label>
                <label class="platform-option"><input type="radio" name="platform" value="other"><span>⚙️ Otro</span></label>
            </div>
        </div>
        <div class="form-group">
            <label for="docker_image">Imagen Docker</label>
            <input type="text" id="docker_image" name="docker_image" class="form-control" value="python:3.11-slim">
        </div>
        <div class="form-group">
            <label for="description">Descripción (opcional)</label>
            <textarea id="description" name="description" class="form-control" rows="2" placeholder="Qué hace este bot..."></textarea>
        </div>
        <div class="form-actions">
            <a href="<?= APP_URL ?>/dashboard" class="btn btn-ghost">Cancelar</a>
            <button type="submit" class="btn btn-outline">Crear bot manual →</button>
        </div>
    </form>
</div>

<script>
document.querySelectorAll('.tpl-filter').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tpl-filter').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const filter = btn.dataset.filter;
        document.querySelectorAll('.tpl-card').forEach(card => {
            if (filter === 'all') { card.style.display = ''; return; }
            const match = card.dataset.platform === filter || card.dataset.category === filter;
            card.style.display = match ? '' : 'none';
        });
    });
});
</script>
