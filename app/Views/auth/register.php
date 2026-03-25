<div class="login-page">
    <div class="login-box">
        <div class="login-header">
            <div class="login-logo">⚡</div>
            <h1>lanzabot<span>.com</span></h1>
            <p>Crea tu cuenta gratuita</p>
        </div>

        <form method="POST" action="<?= APP_URL ?>/register">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\Auth::csrfToken() ?>">

            <label class="login-label" for="name">Nombre completo</label>
            <input class="login-input" type="text" id="name" name="name" required
                   placeholder="Juan García" autocomplete="name"
                   value="<?= \App\Core\View::e($_POST['name'] ?? '') ?>">

            <label class="login-label" for="email">Correo electrónico</label>
            <input class="login-input" type="email" id="email" name="email" required
                   placeholder="tu@correo.com" autocomplete="email"
                   value="<?= \App\Core\View::e($_POST['email'] ?? '') ?>">

            <label class="login-label" for="password">Contraseña</label>
            <input class="login-input" type="password" id="password" name="password" required
                   placeholder="Mínimo 8 caracteres" autocomplete="new-password">

            <label class="login-label" for="password_confirm">Confirmar contraseña</label>
            <input class="login-input" type="password" id="password_confirm" name="password_confirm" required
                   placeholder="Repite la contraseña" autocomplete="new-password">

            <button type="submit" class="login-btn-submit">Crear cuenta</button>
        </form>

        <div class="login-form-links">
            ¿Ya tienes cuenta?
            <a href="<?= APP_URL ?>/login">Inicia sesión</a>
        </div>

        <p class="login-footer">
            Al registrarte aceptas nuestros <a href="#">Términos de Servicio</a>
        </p>
    </div>
</div>
