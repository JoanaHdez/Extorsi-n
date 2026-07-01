<?php

namespace App\Modules\Extorsion\Registro\Services;

use App\Modules\Extorsion\Constancias\Services\ConstanciaRegistroService;
use App\Modules\Extorsion\Constancias\Services\ConstanciaTokenService;

class RegistroCorreoService
{
    public function enviar(string $correo, string $tipoRegistro, int $idRegistro): void
    {
        $ligaConstancia = base_url('constancia/' . (new ConstanciaTokenService())->generar($tipoRegistro, $idRegistro));
        $registroCorreo = (new ConstanciaRegistroService())->obtener($tipoRegistro, $idRegistro);
        $nombreCompleto = $registroCorreo === null
            ? ''
            : trim(($registroCorreo['nombre'] ?? '') . ' ' . ($registroCorreo['apellido_p'] ?? '') . ' ' . ($registroCorreo['apellido_m'] ?? ''));

        $logoAyuntamientoPath = FCPATH . 'assets/img/ayun.png';
        $logoComisariaPath = FCPATH . 'assets/img/comisaria.png';
        $iconoExitoPath = FCPATH . 'assets/img/bien.png';

        $email = \Config\Services::email();
        $smtpPassB64 = env('email.SMTPPassB64');
        $smtpPass = $smtpPassB64 !== null && $smtpPassB64 !== ''
            ? (base64_decode($smtpPassB64) ?: '')
            : env('email.SMTPPass', '');

        $emailConfig = [
            'protocol'    => env('email.protocol', 'smtp'),
            'SMTPHost'    => env('email.SMTPHost'),
            'SMTPUser'    => env('email.SMTPUser'),
            'SMTPPass'    => $smtpPass,
            'SMTPPort'    => (int) env('email.SMTPPort'),
            'SMTPTimeout' => (int) env('email.SMTPTimeout', 10),
            'SMTPCrypto'  => env('email.SMTPCrypto', ''),
            'mailType'    => env('email.mailType', 'html'),
            'charset'     => env('email.charset', 'UTF-8'),
            'newline'     => "\r\n",
            'CRLF'        => "\r\n",
        ];

        $email->initialize($emailConfig);

        $email->setFrom(
            env('email.fromEmail'),
            env('email.fromName')
        );

        $email->setTo($correo);
        $email->setSubject('Registro exitoso');

        $email->attach($logoAyuntamientoPath, 'inline');
        $email->attach($logoComisariaPath, 'inline');
        $email->attach($iconoExitoPath, 'inline');

        $logoAyuntamiento = $email->setAttachmentCID($logoAyuntamientoPath);
        $logoComisaria = $email->setAttachmentCID($logoComisariaPath);
        $iconoExito = $email->setAttachmentCID($iconoExitoPath);

        $logoAyuntamientoSrc = $logoAyuntamiento ? 'cid:' . $logoAyuntamiento : base_url('assets/img/ayun.png');
        $logoComisariaSrc = $logoComisaria ? 'cid:' . $logoComisaria : base_url('assets/img/comisaria.png');
        $iconoExitoSrc = $iconoExito ? 'cid:' . $iconoExito : base_url('assets/img/bien.png');

        $mensaje = '
            <!doctype html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Registro exitoso</title>
            </head>
            <body style="margin:0; padding:0; background:#eef2f7; font-family:Arial, Helvetica, sans-serif; color:#1f2933;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#eef2f7; padding:28px 12px;">
                    <tr>
                        <td align="center">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:660px; background:#ffffff; border-radius:14px; overflow:hidden; box-shadow:0 14px 34px rgba(20, 36, 64, 0.14);">
                                <tr>
                                    <td style="height:8px; background:#8a1538; line-height:8px; font-size:1px;">&nbsp;</td>
                                </tr>

                                <tr>
                                    <td style="background:#ffffff; padding:18px 30px; border-bottom:1px solid #e6ebf2;">
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td align="left" width="50%" style="vertical-align:middle;">
                                                    <img src="' . esc($logoAyuntamientoSrc, 'attr') . '" alt="Ayuntamiento" width="132" style="display:block; width:132px; max-width:132px; height:auto; border:0;">
                                                </td>
                                                <td align="right" width="50%" style="vertical-align:middle;">
                                                    <img src="' . esc($logoComisariaSrc, 'attr') . '" alt="Comisaría" width="128" style="display:block; width:128px; max-width:128px; height:auto; border:0;">
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="background:#243b6b; padding:34px 36px 34px; text-align:center;">
                                        <p style="margin:0; color:#ffffff; font-size:22px; line-height:1.35; font-weight:700;">
                                            Pl&aacute;tica de Medidas Preventivas en Casos de Extorsi&oacute;n
                                        </p>
                                        <p style="margin:8px 0 22px; color:#dbe7ff; font-size:16px; line-height:1.45; font-weight:700;">
                                            Comisar&iacute;a General de Seguridad Ciudadana
                                        </p>
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" style="margin:0 auto 18px;">
                                            <tr>
                                                <td style="background:#ffffff; border-radius:50%; padding:12px;">
                                                    <img src="' . esc($iconoExitoSrc, 'attr') . '" alt="" width="54" style="display:block; width:54px; height:auto; border:0;">
                                                </td>
                                            </tr>
                                        </table>
                                        <h1 style="margin:0; color:#ffffff; font-size:30px; line-height:1.25; font-weight:700;">
                                            Registro exitoso
                                        </h1>
                                        <p style="margin:12px 0 0; color:#dbe7ff; font-size:17px; line-height:1.5;">
                                            Su asistencia quedó registrada correctamente.
                                        </p>
                                        <p style="margin:18px 0 0; color:#ffffff; font-size:20px; line-height:1.35; font-weight:700; text-transform:uppercase;">
                                            ' . esc($nombreCompleto) . '
                                        </p>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:34px 38px 8px;">
                                        <p style="margin:0 0 22px; color:#344054; font-size:22px; line-height:1.4; font-weight:700; text-align:center;">
                                            Gracias por registrarse
                                        </p>
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f8fafc; border:1px solid #e6ebf2; border-radius:10px;">
                                            <tr>
                                                <td style="padding:18px 20px;">
                                                    <p style="margin:0; color:#243b6b; font-size:14px; line-height:1.5; font-weight:700; text-transform:uppercase;">
                                                        Próximo paso
                                                    </p>
                                                    <p style="margin:6px 0 0; color:#344054; font-size:16px; line-height:1.6;">
                                                        Conserve este correo para poder descargar su constancia al concluir el evento.
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                        <p style="margin:22px 0 0; color:#344054; font-size:16px; line-height:1.65;">
                                            Puede descargar su constancia desde el siguiente enlace:
                                        </p>
                                    </td>
                                </tr>

                                <tr>
                                    <td align="center" style="padding:24px 38px 34px;">
                                        <a href="' . esc($ligaConstancia, 'attr') . '" style="display:inline-block; background:#8a1538; color:#ffffff; font-size:16px; font-weight:700; text-decoration:none; padding:15px 30px; border-radius:8px;">
                                            Descargar constancia
                                        </a>
                                        <p style="margin:18px 0 0; color:#667085; font-size:13px; line-height:1.5;">
                                            Si el botón no funciona, copie y pegue esta liga en su navegador:
                                        </p>
                                        <p style="margin:8px 0 0; color:#243b6b; font-size:13px; line-height:1.5; word-break:break-all;">
                                            <a href="' . esc($ligaConstancia, 'attr') . '" style="color:#243b6b; text-decoration:underline;">' . esc($ligaConstancia) . '</a>
                                        </p>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="background:#f8fafc; padding:22px 34px; border-top:1px solid #e5e9ef; text-align:center;">
                                        <p style="margin:0; color:#667085; font-size:12px; line-height:1.5;">
                                            Secretaría de Seguridad y Protección Ciudadana
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </body>
            </html>
        ';

        $email->setMessage($mensaje);

        try {
            if (! $email->send()) {
                log_message(
                    'error',
                    'ERROR ENVIO CORREO: protocol=' . $emailConfig['protocol']
                        . ' host=' . ($emailConfig['SMTPHost'] ?: 'N/A')
                        . ' port=' . ($emailConfig['SMTPPort'] ?: 'N/A')
                        . ' crypto=' . ($emailConfig['SMTPCrypto'] ?: 'N/A')
                        . ' detalle=' . $email->printDebugger(['headers', 'subject'])
                );
            }
        } catch (\Throwable $exception) {
            log_message(
                'error',
                'ERROR ENVIO CORREO EXCEPTION: protocol=' . $emailConfig['protocol']
                    . ' host=' . ($emailConfig['SMTPHost'] ?: 'N/A')
                    . ' port=' . ($emailConfig['SMTPPort'] ?: 'N/A')
                    . ' crypto=' . ($emailConfig['SMTPCrypto'] ?: 'N/A')
                    . ' detalle=' . $exception->getMessage()
            );
        }
    }
}