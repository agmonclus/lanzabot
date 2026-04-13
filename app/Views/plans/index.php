<?php $pageTitle = 'Planes'; ?>
<?php
$subtitles = [
    'free'    => 'El Laboratorio',
    'starter' => 'El Lanzamiento',
    'medium'  => 'Potencia Total',
    'pro'     => 'La Central de Bots',
];
$planEmojis = ['free' => '', 'starter' => '', 'medium' => '', 'pro' => ''];
$highlights = [
    'free'    => '<strong>Modo Eco (Hibernación):</strong> Si tu bot no tiene actividad o no visitas el panel en 7 días, entrará en "Modo Eco". ¿Quieres volver? Dale a <em>Despertar</em> desde el panel y estará online en segundos.',
    'starter' => '<strong>Disco Permanente:</strong> Toda la información que tu bot guarde (SQLite, archivos JSON, niveles de usuarios) sobrevivirá a cualquier reinicio o actualización.',
    'medium'  => '<strong>Rendimiento Garantizado:</strong> Con almacenamiento de alta velocidad y mayor memoria, tus bots responderán de forma instantánea incluso en horas pico.',
    'pro'     => '<strong>Infraestructura de Élite:</strong> El plan más robusto para quienes necesitan máxima persistencia, sistemas complejos con bases de datos pesadas o grandes volúmenes de caché.',
];
?>
<div class="page-header">
    <h1>Planes de Alojamiento</h1>
    <p class="text-muted">Sin permanencia. Cancela cuando quieras.</p>
</div>

<div class="plans-grid">
<?php foreach ($plans as $plan): if ($plan['slug'] === 'custom') continue; ?>
<?php $isCurrent = ($current['slug'] === $plan['slug']); ?>
<div class="plan-card <?= $isCurrent ? 'plan-current' : '' ?> <?= $plan['slug'] === 'pro' ? 'plan-featured' : '' ?>">
    <?php if ($plan['slug'] === 'pro'): ?><div class="plan-tag">Popular</div><?php endif; ?>
    <div class="plan-emoji"><?= $planEmojis[$plan['slug']] ?? '' ?></div>
    <div class="plan-name"><?= \App\Core\View::e($plan['name']) ?></div>
    <?php if (isset($subtitles[$plan['slug']])): ?>
        <div class="plan-subtitle"><?= $subtitles[$plan['slug']] ?></div>
    <?php endif; ?>
    <div class="plan-price">
        <?php if ($plan['price_monthly'] > 0): ?>
            <span class="price-amount">$<?= number_format($plan['price_monthly'], 2) ?></span>
            <span class="price-period">/mes</span>
        <?php else: ?>
            <span class="price-amount">Gratis</span>
        <?php endif; ?>
    </div>
    <ul class="plan-features">
        <li><?= $plan['max_bots'] ?> bot<?= $plan['max_bots'] > 1 ? 's (Slots)' : '' ?></li>
        <li><?= $plan['ram_mb'] >= 1024 ? number_format($plan['ram_mb'] / 1024, 1) . ' GB' : $plan['ram_mb'] . ' MB' ?> RAM</li>
        <?php if ($plan['disk_gb'] > 0): ?>
            <li><?= $plan['disk_gb'] ?> GB Disco Permanente</li>
        <?php elseif ($plan['disk_temp_mb'] > 0): ?>
            <li><?= $plan['disk_temp_mb'] ?> MB Disco Temporal</li>
        <?php else: ?>
            <li class="text-muted">– Sin disco</li>
        <?php endif; ?>
        <?php if ($plan['max_databases'] > 0): ?>
            <li><?= $plan['max_databases'] ?> base<?= $plan['max_databases'] > 1 ? 's' : '' ?> de datos</li>
        <?php else: ?>
            <li class="text-muted">– Sin base de datos</li>
        <?php endif; ?>
        <?php if ($plan['has_redis']): ?><li>Redis (caché ultra rápida)</li><?php endif; ?>
        <?php if ($plan['has_backups']): ?><li>Backups automáticos</li><?php endif; ?>
        <?php if (in_array($plan['slug'], ['medium', 'pro'])): ?><li>Soporte prioritario</li><?php endif; ?>
    </ul>
    <?php if (isset($highlights[$plan['slug']])): ?>
    <div class="plan-highlight">
        <p><?= $highlights[$plan['slug']] ?></p>
    </div>
    <?php endif; ?>
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

<!-- FAQ / Glosario -->
<div class="faq-section">
    <h2>Preguntas frecuentes</h2>
    <div class="faq-grid">
        <div class="faq-item">
            <h4>¿Qué es el Disco Temporal (Plan Free)?</h4>
            <p>Imagínalo como una pizarra: puedes escribir en ella, pero si borramos la pizarra (reiniciar el bot), los datos desaparecen. No es apto para guardar memoria a largo plazo.</p>
        </div>
        <div class="faq-item">
            <h4>¿Qué es el Disco Permanente (Planes de Pago)?</h4>
            <p>Es como un cuaderno: lo que escribes con bolígrafo se queda ahí aunque cierres el cuaderno. Esencial si tu bot tiene economía, niveles o configuraciones personalizadas de usuarios.</p>
        </div>
        <div class="faq-item">
            <h4>¿El Modo Eco borra mi bot?</h4>
            <p>¡Nunca! Solo lo pone a dormir para que no consuma recursos. Tu código y ajustes siempre estarán esperándote cuando decidas activarlo de nuevo desde el panel.</p>
        </div>
    </div>
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
