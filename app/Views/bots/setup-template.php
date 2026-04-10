<?php
$pageTitle = 'Configurar ' . \App\Core\View::e($template['name']);
$platformIcons = [
    'telegram' => '✈️', 'discord' => '🎮', 'slack' => '💬', 'whatsapp' => '📱',
    'twitch' => '🎮', 'matrix' => '🟢', 'reddit' => '🔶', 'mastodon' => '🐘',
    'multi' => '🌐', 'other' => '⚙️'
];
$diffLabels    = ['easy' => 'Fácil', 'medium' => 'Medio', 'advanced' => 'Avanzado'];
?>

<div class="page-header">
    <div>
        <a href="<?= APP_URL ?>/bots/create" class="btn btn-ghost btn-sm">← Volver al catálogo</a>
    </div>
</div>

<div class="setup-container">
    <!-- Info de la plantilla -->
    <div class="setup-tpl-info">
        <div class="setup-tpl-header">
            <span class="tpl-icon-lg"><?= $template['icon'] ?></span>
            <div>
                <h1><?= \App\Core\View::e($template['name']) ?></h1>
                <p class="text-muted"><?= \App\Core\View::e($template['short_description']) ?></p>
                <div class="setup-meta">
                    <span class="badge badge-platform badge-<?= $template['platform'] ?>">
                        <?= $platformIcons[$template['platform']] ?? '⚙️' ?> <?= ucfirst($template['platform']) ?>
                    </span>
                    <span class="badge badge-info"><?= $diffLabels[$template['difficulty']] ?? $template['difficulty'] ?></span>
                    <span class="badge badge-info">v<?= \App\Core\View::e($template['version'] ?? '1.0.0') ?></span>
                    <?php if (!empty($template['auto_update_supported'])): ?>
                        <span class="badge badge-success">🔄 Auto-actualizable</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php if ($template['description']): ?>
            <div class="setup-tpl-desc">
                <?= nl2br(\App\Core\View::e($template['description'])) ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($template['more_info_url'])): ?>
            <div class="setup-tpl-moreinfo">
                <a href="<?= \App\Core\View::e($template['more_info_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline">
                    🔗 Página del fabricante
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Formulario de configuración -->
    <div class="form-card">
        <h2 style="margin-bottom:.25rem">⚡ Configuración</h2>
        <p class="text-muted" style="margin-bottom:1.25rem">Rellena los datos necesarios para desplegar tu bot</p>

        <form method="POST" action="<?= APP_URL ?>/bots/from-template/<?= $template['id'] ?>">
            <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">

            <div class="form-group">
                <label for="bot_name">Nombre de tu bot</label>
                <input type="text" id="bot_name" name="bot_name" class="form-control"
                       value="<?= \App\Core\View::e($template['name']) ?>"
                       placeholder="Mi <?= \App\Core\View::e($template['name']) ?>">
                <small class="form-hint">Puedes personalizarlo o dejar el nombre por defecto</small>
            </div>

            <hr style="border-color:var(--border);margin:1.25rem 0">

            <?php if (!empty($requiredVars)): ?>
                <h3 style="margin-bottom:1rem">🔑 Variables de configuración</h3>
                <?php foreach ($requiredVars as $varDef):
                    $key      = $varDef['key'] ?? '';
                    $label    = $varDef['label'] ?? $key;
                    $placeholder = $varDef['placeholder'] ?? '';
                    $isReq    = !empty($varDef['required']);
                    $default  = $defaultVars[$key] ?? '';
                ?>
                <div class="form-group">
                    <label for="env_<?= \App\Core\View::e($key) ?>">
                        <?= \App\Core\View::e($label) ?>
                        <?php if ($isReq): ?><span class="required">*</span><?php endif; ?>
                    </label>
                    <?php if (in_array(strtoupper($key), ['TIMEZONE', 'TZ', 'BOT_TIMEZONE', 'APP_TIMEZONE'])): ?>
                        <?php
                            $tzSelected = $default ?: ($placeholder ?: 'Europe/Madrid');
                            $tzRegions  = [
                                'Africa'     => \DateTimeZone::AFRICA,
                                'América'    => \DateTimeZone::AMERICA,
                                'Antártica'  => \DateTimeZone::ANTARCTICA,
                                'Ártico'     => \DateTimeZone::ARCTIC,
                                'Asia'       => \DateTimeZone::ASIA,
                                'Atlántico'  => \DateTimeZone::ATLANTIC,
                                'Australia'  => \DateTimeZone::AUSTRALIA,
                                'Europa'     => \DateTimeZone::EUROPE,
                                'Índico'     => \DateTimeZone::INDIAN,
                                'Pacífico'   => \DateTimeZone::PACIFIC,
                                'UTC'        => \DateTimeZone::UTC,
                            ];
                        ?>
                        <select id="env_<?= \App\Core\View::e($key) ?>"
                                name="env_<?= \App\Core\View::e($key) ?>"
                                class="form-control"
                                <?= $isReq ? 'required' : '' ?>>
                            <option value="">— Selecciona zona horaria —</option>
                            <?php foreach ($tzRegions as $regionLabel => $regionMask): ?>
                                <optgroup label="<?= $regionLabel ?>">
                                    <?php foreach (\DateTimeZone::listIdentifiers($regionMask) as $tz): ?>
                                        <option value="<?= $tz ?>" <?= $tz === $tzSelected ? 'selected' : '' ?>>
                                            <?= $tz ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                    <input type="text"
                           id="env_<?= \App\Core\View::e($key) ?>"
                           name="env_<?= \App\Core\View::e($key) ?>"
                           class="form-control"
                           placeholder="<?= \App\Core\View::e($placeholder) ?>"
                           value="<?= \App\Core\View::e($default) ?>"
                           <?= $isReq ? 'required' : '' ?>>
                    <?php endif; ?>
                    <small class="form-hint"><code><?= \App\Core\View::e($key) ?></code></small>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info">
                    ✨ Esta plantilla no necesita configuración adicional. ¡Solo haz clic en desplegar!
                </div>
            <?php endif; ?>

            <?php if (!empty($template['auto_update_supported'])): ?>
            <div class="alert alert-info" style="margin-top:1rem">
                🔄 <strong>Auto-actualización:</strong> Este bot se mantendrá actualizado automáticamente cuando publiquemos mejoras. Podrás desactivarlo después.
            </div>
            <?php endif; ?>

            <div class="form-actions" style="margin-top:1.5rem">
                <a href="<?= APP_URL ?>/bots/create" class="btn btn-ghost">Cancelar</a>
                <button type="submit" class="btn btn-primary btn-lg">
                    🚀 Instalar bot
                </button>
            </div>
        </form>
    </div>

    <!-- Instrucciones de setup (si existen) -->
    <?php if (!empty($template['setup_instructions'])): ?>
    <div class="setup-instructions">
        <h3>📋 Cómo obtener los datos</h3>
        <div class="setup-steps">
            <?= nl2br(\App\Core\View::e($template['setup_instructions'])) ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($template['documentation_url'])): ?>
    <p style="margin-top:1rem">
        <a href="<?= \App\Core\View::e($template['documentation_url']) ?>" target="_blank" rel="noopener" class="btn btn-outline btn-sm">
            📖 Documentación completa →
        </a>
    </p>
    <?php endif; ?>
</div>