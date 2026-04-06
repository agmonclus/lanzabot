<?php
$pageTitle = 'Desplegar nuevo bot';
$platformIcons = [
    'telegram' => '✈️', 'discord' => '🎮', 'slack' => '💬', 'whatsapp' => '📱',
    'twitch' => '🎮', 'matrix' => '🟢', 'reddit' => '🔶', 'mastodon' => '🐘',
    'multi' => '🌐', 'other' => '⚙️'
];
$categoryLabels = [
    'starter' => '🚀 Inicio', 'ai' => '🧠 IA', 'entertainment' => '🎵 Entretenimiento',
    'moderation' => '🛡️ Moderación', 'utility' => '🔧 Utilidad', 'ecommerce' => '🛒 Comercio',
    'social' => '📣 Social', 'monitoring' => '📊 Monitoreo', 'developer' => '⚙️ Desarrollo'
];
$diffLabels    = ['easy' => 'Fácil', 'medium' => 'Medio', 'advanced' => 'Avanzado'];
$diffColors    = ['easy' => 'success', 'medium' => 'warning', 'advanced' => 'danger'];
?>
<div class="page-header">
    <div>
        <h1>🚀 Desplegar nuevo bot</h1>
        <p class="text-muted">Elige una plantilla, configura tus claves y tendrás tu bot funcionando en minutos</p>
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

<!-- Buscador -->
<div class="tpl-search" style="margin-bottom:1rem">
    <input type="text" id="templateSearch" class="form-control" placeholder="🔍 Buscar bots por nombre, plataforma o categoría..." style="max-width:500px">
</div>

<!-- Filtros por plataforma -->
<div class="tpl-filters">
    <button class="tpl-filter active" data-filter="all">Todos</button>
    <button class="tpl-filter" data-filter="telegram">✈️ Telegram</button>
    <button class="tpl-filter" data-filter="discord">🎮 Discord</button>
    <button class="tpl-filter" data-filter="slack">💬 Slack</button>
    <button class="tpl-filter" data-filter="whatsapp">📱 WhatsApp</button>
    <button class="tpl-filter" data-filter="twitch">🎮 Twitch</button>
    <button class="tpl-filter" data-filter="reddit">🔶 Reddit</button>
    <button class="tpl-filter" data-filter="mastodon">🐘 Mastodon</button>
    <button class="tpl-filter" data-filter="matrix">🟢 Matrix</button>
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
        $autoUpdate  = !empty($t['auto_update_supported']);
    ?>
    <div class="tpl-card <?= !$available ? 'tpl-locked' : '' ?>"
         data-platform="<?= \App\Core\View::e($platform) ?>"
         data-category="<?= \App\Core\View::e($category) ?>"
         data-name="<?= \App\Core\View::e(strtolower($t['name'])) ?>"
         data-tags="<?= \App\Core\View::e(strtolower($t['tags'] ?? '')) ?>">
        <div class="tpl-card-head">
            <span class="tpl-icon"><?= $t['icon'] ?></span>
            <div class="tpl-badges">
                <span class="badge badge-platform badge-<?= $platform ?>"><?= $platformIcons[$platform] ?? '⚙️' ?> <?= ucfirst($platform) ?></span>
                <span class="badge badge-<?= $diffColors[$t['difficulty']] ?? 'info' ?>"><?= $diffLabels[$t['difficulty']] ?? $t['difficulty'] ?></span>
                <?php if ($autoUpdate): ?>
                    <span class="badge badge-info" title="Se actualiza automáticamente">🔄 Auto</span>
                <?php endif; ?>
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
                    ⚡ Instalar en 1 clic
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

<?php if (empty($templates)): ?>
<div class="empty-state">
    <div class="empty-icon">🤖</div>
    <p>No hay plantillas disponibles por el momento.</p>
</div>
<?php endif; ?>

<!-- Info de ayuda -->
<div style="text-align:center; margin-top:2rem; padding:1.5rem; border:1px solid var(--border); border-radius:12px;">
    <p style="margin:0 0 .5rem">¿Necesitas ayuda eligiendo un bot?</p>
    <a href="<?= APP_URL ?>/help" class="btn btn-outline btn-sm">📖 Ver guía de ayuda</a>
</div>

<script>
// Filtros por plataforma y categoría
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

// Buscador
document.getElementById('templateSearch').addEventListener('input', function() {
    const q = this.value.toLowerCase().trim();
    document.querySelectorAll('.tpl-card').forEach(card => {
        if (!q) { card.style.display = ''; return; }
        const name = card.dataset.name || '';
        const tags = card.dataset.tags || '';
        const platform = card.dataset.platform || '';
        const match = name.includes(q) || tags.includes(q) || platform.includes(q);
        card.style.display = match ? '' : 'none';
    });
    // Resetear filtro activo
    if (q) {
        document.querySelectorAll('.tpl-filter').forEach(b => b.classList.remove('active'));
        document.querySelector('.tpl-filter[data-filter="all"]').classList.add('active');
    }
});
</script>
