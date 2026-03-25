<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\MailService;
use App\Core\View;
use App\Models\User;
use App\Models\Subscription;
use League\OAuth2\Client\Provider\Google;
use Wohali\OAuth2\Client\Provider\Discord;

class AuthController
{
    public function login(): void
    {
        if (Auth::check()) {
            View::redirect('/dashboard');
        }
        View::render('auth/login', [], 'auth');
    }

    // ---- Google ----

    public function googleRedirect(): void
    {
        $provider = $this->googleProvider();
        $authUrl  = $provider->getAuthorizationUrl(['scope' => ['openid', 'email', 'profile']]);
        $_SESSION['oauth2state'] = $provider->getState();
        header('Location: ' . $authUrl);
        exit;
    }

    public function googleCallback(): void
    {
        Auth::start();
        $provider = $this->googleProvider();

        if (empty($_GET['state']) || $_GET['state'] !== ($_SESSION['oauth2state'] ?? '')) {
            Auth::flash('error', 'Estado OAuth inválido.');
            View::redirect('/login');
        }

        try {
            $token   = $provider->getAccessToken('authorization_code', ['code' => $_GET['code']]);
            $ownerData = $provider->getResourceOwner($token);
            $attrs   = $ownerData->toArray();

            $userId = User::upsertOAuth(
                'google',
                $ownerData->getId(),
                $ownerData->getEmail() ?? '',
                $ownerData->getName()  ?? 'Usuario',
                $attrs['picture']      ?? null
            );

            $this->ensureSubscription($userId);
            Auth::login(['id' => $userId]);
            View::redirect('/dashboard');
        } catch (\Exception $e) {
            Auth::flash('error', 'Error al autenticar con Google.');
            View::redirect('/login');
        }
    }

    // ---- Discord ----

    public function discordRedirect(): void
    {
        $provider = $this->discordProvider();
        $authUrl  = $provider->getAuthorizationUrl(['scope' => ['identify', 'email']]);
        $_SESSION['oauth2state'] = $provider->getState();
        header('Location: ' . $authUrl);
        exit;
    }

    public function discordCallback(): void
    {
        Auth::start();
        $provider = $this->discordProvider();

        if (empty($_GET['state']) || $_GET['state'] !== ($_SESSION['oauth2state'] ?? '')) {
            Auth::flash('error', 'Estado OAuth inválido.');
            View::redirect('/login');
        }

        try {
            $token     = $provider->getAccessToken('authorization_code', ['code' => $_GET['code']]);
            $ownerData = $provider->getResourceOwner($token);
            $attrs     = $ownerData->toArray();

            $avatar = null;
            if (!empty($attrs['avatar'])) {
                $avatar = 'https://cdn.discordapp.com/avatars/' . $attrs['id'] . '/' . $attrs['avatar'] . '.png';
            }

            $userId = User::upsertOAuth(
                'discord',
                (string) $attrs['id'],
                $attrs['email'] ?? '',
                $attrs['username'] ?? 'Usuario',
                $avatar
            );

            $this->ensureSubscription($userId);
            Auth::login(['id' => $userId]);
            View::redirect('/dashboard');
        } catch (\Exception $e) {
            Auth::flash('error', 'Error al autenticar con Discord.');
            View::redirect('/login');
        }
    }

    // ---- Telegram ----

    public function telegramCallback(): void
    {
        Auth::start();
        $data = $_GET;
        $hash = $data['hash'] ?? '';
        unset($data['hash']);

        if (!$this->verifyTelegramAuth($data, $hash)) {
            Auth::flash('error', 'Verificación de Telegram fallida.');
            View::redirect('/login');
        }

        if (time() - ($data['auth_date'] ?? 0) > 86400) {
            Auth::flash('error', 'Sesión de Telegram expirada.');
            View::redirect('/login');
        }

        $avatar = $data['photo_url'] ?? null;
        $name   = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));

        $userId = User::upsertOAuth(
            'telegram',
            (string) $data['id'],
            $data['username'] ?? $name ?? 'Usuario',
            $name,
            $avatar
        );

        $this->ensureSubscription($userId);
        Auth::login(['id' => $userId]);
        View::redirect('/dashboard');
    }

    public function logout(): void
    {
        Auth::logout();
        View::redirect('/login');
    }

    // ---- Registro por email ----

    public function registerForm(): void
    {
        if (Auth::check()) {
            View::redirect('/dashboard');
        }
        View::render('auth/register', [], 'auth');
    }

    public function register(): void
    {
        $this->verifyCsrf();

        $name     = trim($_POST['name']     ?? '');
        $email    = trim($_POST['email']    ?? '');
        $password = $_POST['password']      ?? '';
        $confirm  = $_POST['password_confirm'] ?? '';

        // Validaciones básicas
        if (!$name || !$email || !$password || !$confirm) {
            Auth::flash('error', 'Todos los campos son obligatorios.');
            View::redirect('/register');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Auth::flash('error', 'El correo electrónico no es válido.');
            View::redirect('/register');
        }

        if (strlen($password) < 8) {
            Auth::flash('error', 'La contraseña debe tener al menos 8 caracteres.');
            View::redirect('/register');
        }

        if ($password !== $confirm) {
            Auth::flash('error', 'Las contraseñas no coinciden.');
            View::redirect('/register');
        }

        // Verificar si el email ya existe
        if (User::findByEmail($email)) {
            Auth::flash('error', 'Ya existe una cuenta con ese correo electrónico.');
            View::redirect('/register');
        }

        $hash   = password_hash($password, PASSWORD_BCRYPT);
        $userId = User::createWithPassword($email, $name, $hash);

        $this->ensureSubscription($userId);

        // Generar token de verificación y enviar correo
        $token = User::createAuthToken($userId, 'verify_email');
        $sent  = MailService::sendVerification($email, $name, $token);

        if (!$sent) {
            // El usuario se creó; avisamos pero no bloqueamos
            Auth::flash('success', 'Cuenta creada. No pudimos enviar el correo de verificación; contacta con soporte.');
        } else {
            Auth::flash('success', 'Cuenta creada. Revisa tu correo para verificar tu dirección.');
        }

        View::redirect('/login');
    }

    // ---- Inicio de sesión por email ----

    public function loginEmailForm(): void
    {
        if (Auth::check()) {
            View::redirect('/dashboard');
        }
        View::render('auth/login', [], 'auth');
    }

    public function loginEmail(): void
    {
        $this->verifyCsrf();

        $email    = trim($_POST['email']    ?? '');
        $password = $_POST['password']      ?? '';

        if (!$email || !$password) {
            Auth::flash('error', 'Introduce tu correo y contraseña.');
            View::redirect('/login/email');
        }

        $user = User::findByEmailAndPassword($email, $password);

        if (!$user) {
            Auth::flash('error', 'Correo o contraseña incorrectos.');
            View::redirect('/login/email');
        }

        if (empty($user['email_verified_at'])) {
            Auth::flash('error', 'Debes verificar tu correo antes de iniciar sesión. Revisa tu bandeja de entrada.');
            View::redirect('/login/email');
        }

        $this->ensureSubscription($user['id']);
        Auth::login(['id' => $user['id']]);
        View::redirect('/dashboard');
    }

    // ---- Verificación de email ----

    public function verifyEmail(): void
    {
        $token = $_GET['token'] ?? '';

        if (!$token) {
            Auth::flash('error', 'Token de verificación no válido.');
            View::redirect('/login');
        }

        $row = User::findAuthToken($token, 'verify_email');

        if (!$row) {
            Auth::flash('error', 'El enlace de verificación no es válido o ha expirado.');
            View::redirect('/login');
        }

        User::setEmailVerified((int) $row['user_id']);
        User::deleteAuthToken((int) $row['id']);

        Auth::flash('success', '¡Correo verificado! Ya puedes iniciar sesión.');
        View::redirect('/login');
    }

    // ---- Recuperar contraseña ----

    public function forgotPasswordForm(): void
    {
        if (Auth::check()) {
            View::redirect('/dashboard');
        }
        View::render('auth/forgot-password', [], 'auth');
    }

    public function forgotPassword(): void
    {
        $this->verifyCsrf();

        $email = trim($_POST['email'] ?? '');

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Auth::flash('error', 'Introduce un correo electrónico válido.');
            View::redirect('/forgot-password');
        }

        $user = User::findByEmail($email);

        // Respuesta genérica para no revelar si el email existe
        if ($user) {
            $token = User::createAuthToken((int) $user['id'], 'reset_password');
            MailService::sendPasswordReset($user['email'], $user['name'], $token);
        }

        Auth::flash('success', 'Si existe una cuenta con ese correo, recibirás un enlace para restablecer tu contraseña.');
        View::redirect('/forgot-password');
    }

    public function resetPasswordForm(): void
    {
        $token = $_GET['token'] ?? '';

        if (!$token) {
            Auth::flash('error', 'Token no válido.');
            View::redirect('/forgot-password');
        }

        $row = User::findAuthToken($token, 'reset_password');

        if (!$row) {
            Auth::flash('error', 'El enlace ha expirado o no es válido. Solicita uno nuevo.');
            View::redirect('/forgot-password');
        }

        View::render('auth/reset-password', ['token' => $token], 'auth');
    }

    public function resetPassword(): void
    {
        $this->verifyCsrf();

        $token    = $_POST['token']            ?? '';
        $password = $_POST['password']         ?? '';
        $confirm  = $_POST['password_confirm'] ?? '';

        if (!$token) {
            Auth::flash('error', 'Token no válido.');
            View::redirect('/forgot-password');
        }

        $row = User::findAuthToken($token, 'reset_password');

        if (!$row) {
            Auth::flash('error', 'El enlace ha expirado o no es válido. Solicita uno nuevo.');
            View::redirect('/forgot-password');
        }

        if (strlen($password) < 8) {
            Auth::flash('error', 'La contraseña debe tener al menos 8 caracteres.');
            View::redirect('/reset-password?token=' . urlencode($token));
        }

        if ($password !== $confirm) {
            Auth::flash('error', 'Las contraseñas no coinciden.');
            View::redirect('/reset-password?token=' . urlencode($token));
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        User::updatePassword((int) $row['user_id'], $hash);
        // Marcar email como verificado si aún no lo estaba
        User::setEmailVerified((int) $row['user_id']);
        User::deleteAuthToken((int) $row['id']);

        Auth::flash('success', 'Contraseña restablecida correctamente. Ya puedes iniciar sesión.');
        View::redirect('/login');
    }

    // ---- Helpers de CSRF ----

    private function verifyCsrf(): void
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!Auth::verifyCsrf($token)) {
            Auth::flash('error', 'Token de seguridad inválido. Inténtalo de nuevo.');
            View::redirect('/login');
        }
    }

    // ---- Private helpers ----

    private function googleProvider(): Google
    {
        return new Google([
            'clientId'     => GOOGLE_CLIENT_ID,
            'clientSecret' => GOOGLE_CLIENT_SECRET,
            'redirectUri'  => GOOGLE_REDIRECT_URI,
        ]);
    }

    private function discordProvider(): Discord
    {
        return new Discord([
            'clientId'     => DISCORD_CLIENT_ID,
            'clientSecret' => DISCORD_CLIENT_SECRET,
            'redirectUri'  => DISCORD_REDIRECT_URI,
        ]);
    }

    private function verifyTelegramAuth(array $data, string $hash): bool
    {
        $token      = TELEGRAM_BOT_TOKEN;
        $secretKey  = hash('sha256', $token, true);
        ksort($data);
        $checkStr   = implode("\n", array_map(fn($k, $v) => "{$k}={$v}", array_keys($data), $data));
        $computedHash = hash_hmac('sha256', $checkStr, $secretKey);
        return hash_equals($computedHash, $hash);
    }

    private function ensureSubscription(int $userId): void
    {
        $sub = Subscription::getActiveForUser($userId);
        if (!$sub) {
            Subscription::createFree($userId);
        }
    }
}
