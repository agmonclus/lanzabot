<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? \App\Core\View::e($pageTitle) . ' &mdash; ' : '' ?>Lanzabot</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
</head>
<body>
<div class="layout">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <a href="<?= APP_URL ?>/dashboard">
                <span class="brand-icon">⚡</span>
                <span class="brand-name">lanzabot</span>
            </a>
        </div>

        <nav class="sidebar-nav">
            <a href="<?= APP_URL ?>/dashboard"  class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/dashboard') ? 'active' : '' ?>">
                <svg viewBox="0 0 20 20" fill="currentColor"><path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm0 6a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1v-2zm1 5a1 1 0 100 2h12a1 1 0 100-2H4z"/></svg>
                Dashboard
            </a>
            <a href="<?= APP_URL ?>/plans"      class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/plans') ? 'active' : '' ?>">
                <svg viewBox="0 0 20 20" fill="currentColor"><path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zm0 8a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zm6-8a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2h-2zm0 8a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2h-2z"/></svg>
                Planes
            </a>
            <a href="<?= APP_URL ?>/billing"    class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/billing') ? 'active' : '' ?>">
                <svg viewBox="0 0 20 20" fill="currentColor"><path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"/></svg>
                Facturación
            </a>
            <a href="<?= APP_URL ?>/help"       class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/help') ? 'active' : '' ?>">
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                Ayuda
            </a>

            <?php $currentUser = \App\Core\Auth::user(); ?>
            <?php if ($currentUser && (int)$currentUser['id'] === 1): ?>
            <a href="<?= APP_URL ?>/admin" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/admin') ? 'active' : '' ?>">
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/></svg>
                Admin
            </a>
            <?php endif; ?>
        </nav>

        <div class="sidebar-user">
            <?php $u = \App\Core\Auth::user(); ?>
            <?php if ($u): ?>
            <div class="user-info">
                <?php if ($u['avatar']): ?>
                    <img src="<?= \App\Core\View::e($u['avatar']) ?>" class="user-avatar" alt="">
                <?php else: ?>
                    <div class="user-avatar-placeholder"><?= strtoupper(substr($u['name'], 0, 1)) ?></div>
                <?php endif; ?>
                <span class="user-name"><?= \App\Core\View::e(explode(' ', $u['name'])[0]) ?></span>
            </div>
            <a href="<?= APP_URL ?>/logout" class="logout-link">Salir</a>
            <?php endif; ?>
        </div>
    </aside>

    <!-- Main content -->
    <main class="main">
        <?php if ($msg = \App\Core\Auth::flash('error')): ?>
            <div class="flash flash-error"><?= \App\Core\View::e($msg) ?></div>
        <?php endif; ?>
        <?php if ($msg = \App\Core\Auth::flash('success')): ?>
            <div class="flash flash-success"><?= \App\Core\View::e($msg) ?></div>
        <?php endif; ?>
        <?php if ($msg = \App\Core\Auth::flash('info')): ?>
            <div class="flash flash-info"><?= \App\Core\View::e($msg) ?></div>
        <?php endif; ?>

        <?= $content ?>
    </main>
</div>
<script src="<?= APP_URL ?>/assets/js/app.js"></script>
</body>
</html>
