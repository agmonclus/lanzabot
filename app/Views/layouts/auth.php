<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lanzabot &mdash; Aloja tu bot en 1 minuto</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
</head>
<body class="auth-body">
    <?php if ($msg = \App\Core\Auth::flash('error')): ?>
        <div class="flash flash-error"><?= \App\Core\View::e($msg) ?></div>
    <?php endif; ?>
    <?php if ($msg = \App\Core\Auth::flash('success')): ?>
        <div class="flash flash-success"><?= \App\Core\View::e($msg) ?></div>
    <?php endif; ?>
    <?= $content ?>
</body>
</html>
