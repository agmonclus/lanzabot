<div class="login-page">
    <div class="login-box">
        <div class="login-header">
            <div class="login-logo">⚡</div>
            <h1>lanzabot<span>.com</span></h1>
            <p>¿Olvidaste tu contraseña?</p>
        </div>

        <p style="color:#6c757d;font-size:.88rem;text-align:center;margin-bottom:1.5rem;line-height:1.6;">
            Introduce tu correo y te enviaremos un enlace para restablecer tu contraseña.
        </p>

        <form method="POST" action="<?= APP_URL ?>/forgot-password">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::csrfToken() ?>">

            <label class="login-label" for="email">Correo electrónico</label>
            <input class="login-input" type="email" id="email" name="email" required
                   placeholder="tu@correo.com" autocomplete="email">

            <button type="submit" class="login-btn-submit">Enviar enlace</button>
        </form>

        <div class="login-form-links">
            <a href="<?= APP_URL ?>/login">← Volver al inicio de sesión</a>
        </div>
    </div>
</div>
