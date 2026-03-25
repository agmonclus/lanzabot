<?php

namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as MailException;

class MailService
{
    private static function mailer(): PHPMailer
    {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->Port       = MAIL_PORT;

        // starttls → PHPMailer usa ENCRYPTION_STARTTLS
        if (strtolower(MAIL_ENCRYPTION) === 'starttls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } elseif (strtolower(MAIL_ENCRYPTION) === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mail->SMTPSecure = '';
        }

        $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        $mail->addReplyTo(MAIL_REPLY_TO, MAIL_FROM_NAME);
        $mail->CharSet = 'UTF-8';

        return $mail;
    }

    /**
     * Envía el enlace de verificación de email tras el registro.
     */
    public static function sendVerification(string $to, string $name, string $token): bool
    {
        $link = APP_URL . '/auth/verify-email?token=' . urlencode($token);

        $subject = 'Verifica tu correo en lanzabot.com';
        $body    = self::template(
            $name,
            'Verifica tu dirección de correo',
            'Para activar tu cuenta haz clic en el siguiente botón. El enlace caduca en 24 horas.',
            $link,
            'Verificar correo'
        );

        return self::send($to, $name, $subject, $body);
    }

    /**
     * Envía el enlace de restablecimiento de contraseña.
     */
    public static function sendPasswordReset(string $to, string $name, string $token): bool
    {
        $link = APP_URL . '/reset-password?token=' . urlencode($token);

        $subject = 'Restablece tu contraseña en lanzabot.com';
        $body    = self::template(
            $name,
            'Restablecer contraseña',
            'Hemos recibido una solicitud para restablecer la contraseña de tu cuenta. Haz clic en el botón para continuar. El enlace caduca en 1 hora. Si no solicitaste esto, puedes ignorar este correo.',
            $link,
            'Restablecer contraseña'
        );

        return self::send($to, $name, $subject, $body);
    }

    private static function send(string $to, string $toName, string $subject, string $htmlBody): bool
    {
        try {
            $mail = self::mailer();
            $mail->addAddress($to, $toName);
            $mail->Subject  = $subject;
            $mail->isHTML(true);
            $mail->Body     = $htmlBody;
            $mail->AltBody  = strip_tags(str_replace(['<br>', '<br/>'], "\n", $htmlBody));
            $mail->send();
            return true;
        } catch (MailException $e) {
            error_log('[MailService] Error enviando correo a ' . $to . ': ' . $e->getMessage());
            return false;
        }
    }

    private static function template(
        string $name,
        string $heading,
        string $message,
        string $link,
        string $buttonText
    ): string {
        $appUrl  = APP_URL;
        $appName = MAIL_FROM_NAME;
        $safeHeading    = htmlspecialchars($heading,    ENT_QUOTES, 'UTF-8');
        $safeMessage    = htmlspecialchars($message,    ENT_QUOTES, 'UTF-8');
        $safeName       = htmlspecialchars($name,       ENT_QUOTES, 'UTF-8');
        $safeButtonText = htmlspecialchars($buttonText, ENT_QUOTES, 'UTF-8');
        $safeLink       = htmlspecialchars($link,       ENT_QUOTES, 'UTF-8');
        $safeAppName    = htmlspecialchars($appName,    ENT_QUOTES, 'UTF-8');

        $year = date('Y');

        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{$safeHeading}</title>
</head>
<body style="margin:0;padding:0;background:#f8f9fa;font-family:Inter,Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9fa;padding:40px 16px;">
    <tr>
      <td align="center">
        <table width="560" cellpadding="0" cellspacing="0" style="background:#ffffff;border:1px solid #dee2e6;border-radius:8px;overflow:hidden;max-width:560px;width:100%;">

          <!-- Header -->
          <tr>
            <td style="padding:24px 40px;border-bottom:1px solid #dee2e6;">
              <span style="font-size:20px;font-weight:700;color:#212529;letter-spacing:-0.3px;">⚡ {$safeAppName}</span>
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="padding:36px 40px 28px;">
              <h2 style="margin:0 0 20px;font-size:20px;font-weight:600;color:#212529;">{$safeHeading}</h2>
              <p style="margin:0 0 6px;font-size:15px;color:#212529;">Hola, <strong>{$safeName}</strong></p>
              <p style="margin:0 0 28px;font-size:15px;color:#495057;line-height:1.65;">{$safeMessage}</p>

              <!-- Button -->
              <table cellpadding="0" cellspacing="0">
                <tr>
                  <td style="border-radius:6px;background:#0d6efd;">
                    <a href="{$safeLink}" style="display:inline-block;padding:12px 28px;font-size:15px;font-weight:600;color:#ffffff;text-decoration:none;border-radius:6px;">{$safeButtonText}</a>
                  </td>
                </tr>
              </table>

              <!-- Fallback link -->
              <p style="margin:24px 0 0;font-size:12px;color:#6c757d;line-height:1.6;">
                Si el botón no funciona, copia y pega este enlace en tu navegador:<br>
                <a href="{$safeLink}" style="color:#0d6efd;word-break:break-all;">{$safeLink}</a>
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="padding:16px 40px;border-top:1px solid #dee2e6;background:#f8f9fa;">
              <p style="margin:0;font-size:12px;color:#6c757d;text-align:center;">
                © {$year} {$safeAppName} &nbsp;·&nbsp;
                <a href="{$appUrl}" style="color:#6c757d;text-decoration:none;">{$appUrl}</a>
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
    }
}
