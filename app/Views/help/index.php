<?php
$pageTitle = 'Centro de ayuda';

$platformInfo = [
    'telegram'  => ['icon' => '', 'name' => 'Telegram',  'color' => '#0088cc'],
    'discord'   => ['icon' => '', 'name' => 'Discord',   'color' => '#5865F2'],
    'slack'     => ['icon' => '', 'name' => 'Slack',      'color' => '#4A154B'],
    'whatsapp'  => ['icon' => '', 'name' => 'WhatsApp',   'color' => '#25D366'],
    'twitch'    => ['icon' => '', 'name' => 'Twitch',     'color' => '#9146FF'],
    'matrix'    => ['icon' => '', 'name' => 'Matrix',     'color' => '#0DBD8B'],
    'reddit'    => ['icon' => '', 'name' => 'Reddit',     'color' => '#FF4500'],
    'mastodon'  => ['icon' => '', 'name' => 'Mastodon',   'color' => '#6364FF'],
    'multi'     => ['icon' => '', 'name' => 'Multi-plataforma', 'color' => '#667'],
    'other'     => ['icon' => '', 'name' => 'Otro',       'color' => '#667'],
];
?>

<div class="page-header">
    <div>
        <h1>Centro de ayuda</h1>
        <p class="text-muted">Todo lo que necesitas para empezar con tus bots</p>
    </div>
</div>

<!-- Guía rápida -->
<section class="section">
    <h2>Cómo funciona LanzaBot</h2>
    <div class="help-steps-grid">
        <div class="help-step-card">
            <div class="help-step-number">1</div>
            <h3>Elige un bot</h3>
            <p>Explora nuestro catálogo de bots pre-configurados. Hay bots para Telegram, Discord, Slack, WhatsApp, Twitch, Reddit, Mastodon y más.</p>
        </div>
        <div class="help-step-card">
            <div class="help-step-number">2</div>
            <h3>Configura tus claves</h3>
            <p>Solo necesitas introducir las claves API de la plataforma correspondiente (token del bot, API keys, etc.). Nada más.</p>
        </div>
        <div class="help-step-card">
            <div class="help-step-number">3</div>
            <h3>¡Despliega en 1 clic!</h3>
            <p>Tu bot se instala automáticamente en la nube. Se mantiene actualizado, sin que tengas que hacer nada.</p>
        </div>
    </div>
</section>

<!-- FAQ -->
<section class="section">
    <h2>Preguntas frecuentes</h2>
    <div class="help-faq">
        <details class="faq-item">
            <summary>¿Necesito saber programar?</summary>
            <p>No. Todos los bots están pre-configurados. Solo necesitas crear las credenciales en la plataforma correspondiente (ej: un token de bot en Telegram) y pegarlo en el formulario. Eso es todo.</p>
        </details>
        <details class="faq-item">
            <summary>¿Qué es la auto-actualización?</summary>
            <p>Cuando nosotros mejoramos una plantilla de bot (correcciones, nuevas funciones), tu bot se actualiza automáticamente sin que tengas que hacer nada. Puedes desactivar esta opción desde el panel del bot si lo prefieres.</p>
        </details>
        <details class="faq-item">
            <summary>¿Cuántos bots puedo tener?</summary>
            <p>Depende de tu plan. El plan Free permite 1 bot, Starter permite 4, Medium permite 10 y Pro permite 25. <a href="<?= APP_URL ?>/plans">Ver planes</a>.</p>
        </details>
        <details class="faq-item">
            <summary>¿Qué pasa si mi bot se cae?</summary>
            <p>Los bots se reinician automáticamente si fallan. Además puedes ver los logs en tiempo real desde el panel de cada bot para diagnosticar cualquier problema.</p>
        </details>
        <details class="faq-item">
            <summary>¿Mis claves API están seguras?</summary>
            <p>Sí. Las claves se almacenan cifradas y solo se usan para la ejecución de tu bot. Nunca compartimos datos con terceros.</p>
        </details>
        <details class="faq-item">
            <summary>¿Puedo cambiar las variables de mi bot después de desplegarlo?</summary>
            <p>Sí. Desde el panel de cada bot puedes modificar las variables de entorno en cualquier momento. Los cambios se aplican automáticamente.</p>
        </details>
        <details class="faq-item">
            <summary>¿Puedo usar bots de distintas plataformas a la vez?</summary>
            <p>Sí. Puedes tener bots de Telegram, Discord, Slack, WhatsApp, Twitch, Reddit, Mastodon y más, todos funcionando simultáneamente dentro de tu plan.</p>
        </details>
        <details class="faq-item">
            <summary>¿Los bots funcionan 24/7?</summary>
            <p>Sí. Los bots se ejecutan en la nube y están disponibles las 24 horas del día, los 7 días de la semana. El plan Free tiene modo eco que puede pausar el bot si lleva mucho tiempo inactivo.</p>
        </details>
    </div>
</section>

<!-- Guías por plataforma -->
<section class="section">
    <h2>Guías por plataforma</h2>
    <p class="text-muted" style="margin-bottom:1.25rem">Aprende cómo obtener las credenciales necesarias para cada plataforma</p>

    <div class="help-platform-grid">
        <?php foreach ($platformInfo as $slug => $info):
            $count = count($byPlatform[$slug] ?? []);
            if ($count === 0) continue;
        ?>
        <div class="help-platform-card">
            <div class="help-platform-header">
                <span class="help-platform-icon"><?= $info['icon'] ?></span>
                <div>
                    <h3><?= $info['name'] ?></h3>
                    <small class="text-muted"><?= $count ?> bot<?= $count !== 1 ? 's' : '' ?> disponible<?= $count !== 1 ? 's' : '' ?></small>
                </div>
            </div>
            <div class="help-platform-guide">
                <?php if ($slug === 'telegram'): ?>
                    <ol>
                        <li>Abre Telegram y busca <strong>@BotFather</strong></li>
                        <li>Envía <code>/newbot</code> y sigue las instrucciones</li>
                        <li>Copia el <strong>token</strong> que te proporciona (formato: <code>123456:ABC-DEF...</code>)</li>
                        <li>Pega el token al instalar cualquier bot de Telegram en LanzaBot</li>
                    </ol>
                <?php elseif ($slug === 'discord'): ?>
                    <ol>
                        <li>Ve a <a href="https://discord.com/developers/applications" target="_blank" rel="noopener">Discord Developer Portal</a></li>
                        <li>Crea una <strong>New Application</strong></li>
                        <li>Ve a la sección <strong>Bot</strong> y copia el Token</li>
                        <li>En <strong>Privileged Gateway Intents</strong> activa los intents que necesite el bot</li>
                        <li>Invita el bot a tu servidor con los permisos necesarios</li>
                    </ol>
                <?php elseif ($slug === 'slack'): ?>
                    <ol>
                        <li>Ve a <a href="https://api.slack.com/apps" target="_blank" rel="noopener">Slack API</a> y crea una nueva App</li>
                        <li>En <strong>OAuth &amp; Permissions</strong>, añade los scopes necesarios</li>
                        <li>Instala la app en tu workspace</li>
                        <li>Copia el <strong>Bot Token</strong> (<code>xoxb-...</code>) y el <strong>Signing Secret</strong></li>
                    </ol>
                <?php elseif ($slug === 'whatsapp'): ?>
                    <ol>
                        <li>Crea una cuenta en <a href="https://developers.facebook.com" target="_blank" rel="noopener">Meta for Developers</a></li>
                        <li>Crea una App de tipo <strong>Business</strong></li>
                        <li>Configura la <strong>WhatsApp Business API</strong></li>
                        <li>Obtén el <strong>Access Token</strong> y el <strong>Phone Number ID</strong></li>
                    </ol>
                <?php elseif ($slug === 'twitch'): ?>
                    <ol>
                        <li>Crea una cuenta de Twitch para tu bot (o usa la tuya)</li>
                        <li>Ve a <a href="https://twitchapps.com/tmi/" target="_blank" rel="noopener">twitchapps.com/tmi</a> para generar un token OAuth</li>
                        <li>Copia el token y el nombre de tu canal</li>
                    </ol>
                <?php elseif ($slug === 'reddit'): ?>
                    <ol>
                        <li>Ve a <a href="https://www.reddit.com/prefs/apps" target="_blank" rel="noopener">Reddit Apps</a></li>
                        <li>Crea una aplicación de tipo <strong>script</strong></li>
                        <li>Copia el <strong>Client ID</strong> y el <strong>Client Secret</strong></li>
                        <li>Asegúrate de que la cuenta del bot sea moderadora del subreddit objetivo</li>
                    </ol>
                <?php elseif ($slug === 'mastodon'): ?>
                    <ol>
                        <li>Ve a tu instancia de Mastodon → <strong>Preferencias → Desarrollo</strong></li>
                        <li>Crea una nueva aplicación con permisos de escritura</li>
                        <li>Copia el <strong>token de acceso</strong></li>
                    </ol>
                <?php elseif ($slug === 'matrix'): ?>
                    <ol>
                        <li>Crea una cuenta para tu bot en tu homeserver de Matrix</li>
                        <li>Inicia sesión con Element u otro cliente</li>
                        <li>Genera un <strong>token de acceso</strong> desde ajustes avanzados</li>
                        <li>Invita al bot a las salas donde quieres que opere</li>
                    </ol>
                <?php elseif ($slug === 'multi'): ?>
                    <p>Los bots multi-plataforma soportan múltiples canales de notificación. Configura las credenciales de cada plataforma que quieras usar (Telegram, Discord, Slack, etc.).</p>
                <?php endif; ?>
            </div>
            <?php if (!empty($byPlatform[$slug])): ?>
            <div class="help-platform-bots">
                <strong>Bots disponibles:</strong>
                <?php foreach ($byPlatform[$slug] as $t): ?>
                    <a href="<?= APP_URL ?>/help/guide/<?= \App\Core\View::e($t['slug']) ?>" class="help-bot-link">
                        <?= $t['icon'] ?> <?= \App\Core\View::e($t['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Contacto -->
<section class="section" style="text-align:center; padding:2rem; border:1px solid var(--border); border-radius:12px;">
    <h2>¿Tienes más dudas?</h2>
    <p class="text-muted" style="margin-bottom:1rem">Estamos aquí para ayudarte</p>
    <div style="display:flex; gap:1rem; justify-content:center; flex-wrap:wrap;">
        <a href="<?= APP_URL ?>/bots/create" class="btn btn-primary">Instalar un bot</a>
        <a href="<?= APP_URL ?>/plans" class="btn btn-outline">Ver planes</a>
    </div>
</section>
