<?php
$pageTitle = 'Bases de datos';
$dbIcons   = ['postgresql' => '🐘', 'mongodb' => '🍃'];
$maxDbs    = (int)($plan['max_databases'] ?? 0);
$dbCount   = count($databases);
?>
<div class="page-header">
    <div>
        <h1>Bases de datos</h1>
        <p class="text-muted">
            <?= $dbCount ?> / <?= $maxDbs > 0 ? $maxDbs : ($maxDbs < 0 ? '∞' : '0') ?> bases de datos
        </p>
    </div>
    <?php if ($maxDbs !== 0): ?>
    <a href="<?= APP_URL ?>/databases/create" class="btn btn-primary">
        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
        Nueva base de datos
    </a>
    <?php endif; ?>
</div>

<?php if ($maxDbs === 0): ?>
<div class="empty-state">
    <div class="empty-icon">🗄️</div>
    <p>Tu plan <strong><?= \App\Core\View::e(strtoupper($plan['slug'])) ?></strong> no incluye bases de datos.</p>
    <a href="<?= APP_URL ?>/plans" class="btn btn-primary">Ver planes</a>
</div>

<?php elseif (empty($databases)): ?>
<div class="empty-state">
    <div class="empty-icon">🗄️</div>
    <p>Aún no tienes bases de datos. ¡Crea la primera con un clic!</p>
    <a href="<?= APP_URL ?>/databases/create" class="btn btn-primary">Crear base de datos</a>
</div>

<?php else: ?>
<section class="section">
    <div class="bot-grid">
        <?php foreach ($databases as $db): ?>
        <a href="<?= APP_URL ?>/databases/<?= $db['id'] ?>" class="bot-card">
            <div class="bot-card-header">
                <span style="font-size:1.4rem"><?= $dbIcons[$db['type']] ?? '🗄️' ?></span>
                <span class="bot-status status-<?= $db['status'] === 'active' ? 'running' : ($db['status'] === 'error' ? 'stopped' : 'deploying') ?>">
                    <?= \App\Core\View::e($db['status']) ?>
                </span>
            </div>
            <div class="bot-card-name"><?= \App\Core\View::e($db['label']) ?></div>
            <div class="bot-card-desc"><?= strtoupper($db['type']) ?> · <?= \App\Core\View::e($db['db_name']) ?></div>
        </a>
        <?php endforeach; ?>

        <?php if ($maxDbs < 0 || $dbCount < $maxDbs): ?>
        <a href="<?= APP_URL ?>/databases/create" class="bot-card bot-card-new">
            <span class="new-icon">+</span>
            <span>Nueva base de datos</span>
        </a>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>
