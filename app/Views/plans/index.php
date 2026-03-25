<?php $pageTitle = 'Planes'; ?>
<div class="page-header">
    <h1>Planes de alojamiento</h1>
    <p class="text-muted">Escala según tus necesidades. Sin permanencia.</p>
</div>

<div class="plans-grid">
<?php foreach ($plans as $plan): if ($plan['slug'] === 'custom') continue; ?>
<?php $isCurrent = ($current['slug'] === $plan['slug']); ?>
<div class="plan-card <?= $isCurrent ? 'plan-current' : '' ?> <?= $plan['slug'] === 'pro' ? 'plan-featured' : '' ?>">
    <?php if ($plan['slug'] === 'pro'): ?><div class="plan-tag">Popular</div><?php endif; ?>
    <div class="plan-name"><?= \App\Core\View::e($plan['name']) ?></div>
    <div class="plan-price">
        <?php if ($plan['price_weekly'] > 0): ?>
            <span class="price-amount"><?= number_format($plan['price_weekly'], 0) ?>€</span>
            <span class="price-period">/semana</span>
        <?php else: ?>
            <span class="price-amount">Gratis</span>
        <?php endif; ?>
    </div>
    <ul class="plan-features">
        <li>✓ <?= $plan['max_bots'] ?> bot<?= $plan['max_bots'] > 1 ? 's' : '' ?></li>
        <li>✓ <?= $plan['ram_mb'] >= 1024 ? ($plan['ram_mb'] / 1024) . 'GB' : $plan['ram_mb'] . 'MB' ?> RAM</li>
        <?php if ($plan['disk_gb'] > 0): ?><li>✓ <?= $plan['disk_gb'] ?>GB disco</li><?php else: ?><li class="text-muted">– Sin disco</li><?php endif; ?>
        <?php if ($plan['max_databases'] > 0): ?><li>✓ <?= $plan['max_databases'] ?> base<?= $plan['max_databases'] > 1 ? 's' : '' ?> de datos</li><?php else: ?><li class="text-muted">– Sin base de datos</li><?php endif; ?>
    </ul>
    <?php if ($isCurrent): ?>
        <div class="plan-current-badge">Plan actual</div>
    <?php elseif ($plan['slug'] !== 'free'): ?>
        <form method="POST" action="<?= APP_URL ?>/plans/subscribe">
            <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
            <input type="hidden" name="plan" value="<?= $plan['slug'] ?>">
            <button type="submit" class="btn btn-primary btn-full">Elegir <?= $plan['name'] ?></button>
        </form>
    <?php else: ?>
        <a href="<?= APP_URL ?>/billing" class="btn btn-ghost btn-full">Gestionar</a>
    <?php endif; ?>
</div>
<?php endforeach; ?>
</div>

<!-- Custom plan -->
<div class="custom-plan-section" id="custom">
    <h2>¿Necesitas más? Plan personalizado</h2>
    <p>Cuéntanos tus necesidades y te preparamos un presupuesto a medida.</p>

    <form method="POST" action="<?= APP_URL ?>/plans/custom" class="custom-form">
        <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
        <div class="form-row">
            <div class="form-group">
                <label>Tu email</label>
                <input type="email" name="email" class="form-control" required placeholder="hola@tuempresa.com"
                    value="<?= \App\Core\View::e(\App\Core\Auth::user()['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Empresa / Proyecto</label>
                <input type="text" name="company" class="form-control" placeholder="Mi Startup">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>¿Cuántos bots necesitas?</label>
                <input type="number" name="bots" class="form-control" placeholder="10" min="1">
            </div>
        </div>
        <div class="form-group">
            <label>Cuéntanos más</label>
            <textarea name="needs" class="form-control" rows="3" placeholder="RAM necesaria, tráfico esperado, funcionalidades especiales..."></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Solicitar presupuesto</button>
    </form>
</div>
