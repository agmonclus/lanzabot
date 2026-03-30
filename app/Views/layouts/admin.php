<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? \App\Core\View::e($pageTitle) . ' — ' : '' ?>Admin — Lanzabot</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/admin.css">
</head>
<body>
<div class="layout">
    <!-- Sidebar Admin -->
    <aside class="sidebar sidebar-admin">
        <div class="sidebar-brand">
            <a href="<?= APP_URL ?>/admin">
                <span class="brand-icon">⚙️</span>
                <span class="brand-name">Admin</span>
            </a>
        </div>

        <nav class="sidebar-nav">
            <a href="<?= APP_URL ?>/admin" class="nav-link <?= $_SERVER['REQUEST_URI'] === '/admin' ? 'active' : '' ?>">
                <svg viewBox="0 0 20 20" fill="currentColor"><path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm0 6a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1v-2zm1 5a1 1 0 100 2h12a1 1 0 100-2H4z"/></svg>
                Panel
            </a>
            <a href="<?= APP_URL ?>/admin/users" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/admin/users') ? 'active' : '' ?>">
                <svg viewBox="0 0 20 20" fill="currentColor"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg>
                Usuarios
            </a>
            <a href="<?= APP_URL ?>/admin/bots" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/admin/bots') ? 'active' : '' ?>">
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                Bots
            </a>
            <a href="<?= APP_URL ?>/admin/templates" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/admin/templates') ? 'active' : '' ?>">
                <svg viewBox="0 0 20 20" fill="currentColor"><path d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 0l-2 2a1 1 0 101.414 1.414L8 10.414l1.293 1.293a1 1 0 001.414 0l4-4z"/></svg>
                Plantillas
            </a>
            <a href="<?= APP_URL ?>/admin/subscriptions" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/admin/subscriptions') ? 'active' : '' ?>">
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 2a2 2 0 00-2 2v14l3.5-2 3.5 2 3.5-2 3.5 2V4a2 2 0 00-2-2H5zm4.707 3.707a1 1 0 00-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L8.414 9H10a3 3 0 013 3v1a1 1 0 102 0v-1a5 5 0 00-5-5H8.414l1.293-1.293z" clip-rule="evenodd"/></svg>
                Suscripciones
            </a>
            <a href="<?= APP_URL ?>/admin/payments" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/admin/payments') ? 'active' : '' ?>">
                <svg viewBox="0 0 20 20" fill="currentColor"><path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"/></svg>
                Pagos
            </a>
            <a href="<?= APP_URL ?>/admin/plans" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/admin/plans') ? 'active' : '' ?>">
                <svg viewBox="0 0 20 20" fill="currentColor"><path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zm0 8a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zm6-8a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2h-2zm0 8a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2h-2z"/></svg>
                Planes
            </a>

            <div style="border-top: 1px solid var(--border); margin: .75rem 0;"></div>

            <a href="<?= APP_URL ?>/dashboard" class="nav-link">
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/></svg>
                Volver a la app
            </a>
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
