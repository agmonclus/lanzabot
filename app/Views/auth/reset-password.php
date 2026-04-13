<div class="login-page">
    <div class="login-box">
        <div class="login-header">
            <div class="login-logo"></div>
            <h1>LanzaBot<span>.com</span></h1>
            <p>Nueva contraseña</p>
        </div>

        <form method="POST" action="<?= APP_URL ?>/reset-password">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::csrfToken() ?>">
            <input type="hidden" name="token" value="<?= \App\Core\View::e($token) ?>">

            <label class="login-label" for="password">Nueva contraseña</label>
            <input class="login-input" type="password" id="password" name="password" required
                   placeholder="Mínimo 8 caracteres" autocomplete="new-password">

            <label class="login-label" for="password_confirm">Confirmar contraseña</label>
            <input class="login-input" type="password" id="password_confirm" name="password_confirm" required
                   placeholder="Repite la contraseña" autocomplete="new-password">

            <button type="submit" class="login-btn-submit">Guardar contraseña</button>
        </form>

        <div class="login-form-links">
            <a href="<?= APP_URL ?>/login">← Volver al inicio de sesión</a>
        </div>
    </div>
</div>
