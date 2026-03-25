<div class="login-page">
    <div class="login-box">
        <div class="login-header">
            <div class="login-logo">⚡</div>
            <h1>lanzabot<span>.com</span></h1>
            <p>Despliega tu bot en 1 minuto</p>
        </div>

        <div class="login-methods">
            <a href="<?= APP_URL ?>/auth/google" class="btn-oauth btn-google">
                <svg viewBox="0 0 24 24" fill="none"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                Continuar con Google
            </a>

            <a href="<?= APP_URL ?>/auth/discord" class="btn-oauth btn-discord">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.317 4.37a19.791 19.791 0 00-4.885-1.515.074.074 0 00-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 00-5.487 0 12.64 12.64 0 00-.617-1.25.077.077 0 00-.079-.037A19.736 19.736 0 003.677 4.37a.07.07 0 00-.032.027C.533 9.046-.32 13.58.099 18.057c.002.022.015.043.032.054a19.9 19.9 0 005.993 3.03.077.077 0 00.084-.028c.462-.63.874-1.295 1.226-1.994a.076.076 0 00-.041-.106 13.107 13.107 0 01-1.872-.892.077.077 0 01-.008-.128 10.2 10.2 0 00.372-.292.074.074 0 01.077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 01.078.01c.12.098.246.198.373.292a.077.077 0 01-.006.127 12.299 12.299 0 01-1.873.892.077.077 0 00-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 00.084.028 19.839 19.839 0 006.002-3.03.077.077 0 00.032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 00-.031-.03z"/></svg>
                Continuar con Discord
            </a>

            <?php if (TELEGRAM_BOT_USERNAME): ?>
            <div class="telegram-login">
                <script async src="https://telegram.org/js/telegram-widget.js?22"
                    data-telegram-login="<?= \App\Core\View::e(TELEGRAM_BOT_USERNAME) ?>"
                    data-size="large"
                    data-auth-url="<?= APP_URL ?>/auth/telegram"
                    data-request-access="write"
                    data-radius="8">
                </script>
            </div>
            <?php else: ?>
            <div class="telegram-placeholder">
                <span>Telegram (configura BOT_USERNAME en .env)</span>
            </div>
            <?php endif; ?>
        </div>

        <div class="login-divider"><span>o continúa con tu correo</span></div>

        <form method="POST" action="<?= APP_URL ?>/login/email">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::csrfToken() ?>">
            <label class="login-label" for="email">Correo electrónico</label>
            <input class="login-input" type="email" id="email" name="email" required
                   placeholder="tu@correo.com" autocomplete="email">
            <label class="login-label" for="password">Contraseña</label>
            <input class="login-input" type="password" id="password" name="password" required
                   placeholder="••••••••" autocomplete="current-password">
            <button type="submit" class="login-btn-submit">Iniciar sesión</button>
        </form>

        <div class="login-form-links">
            <a href="<?= APP_URL ?>/forgot-password">¿Olvidaste tu contraseña?</a>
            <span class="sep">·</span>
            <a href="<?= APP_URL ?>/register">Crear cuenta</a>
        </div>

        <p class="login-footer">
            Al iniciar sesión aceptas nuestros <a href="#">Términos de Servicio</a>
        </p>
    </div>
</div>
