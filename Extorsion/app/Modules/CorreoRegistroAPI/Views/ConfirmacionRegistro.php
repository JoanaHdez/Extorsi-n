<?php
$folioUrl = 'https://congreso.seguridadneza.gob.mx/identificacion/index.php?id=' . rawurlencode((string) $id);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="es" xml:lang="es" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="format-detection" content="telephone=no,address=no,email=no,date=no,url=no">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>Registro exitoso</title>
    <!--[if gte mso 9]>
    <xml>
        <o:OfficeDocumentSettings>
            <o:AllowPNG/>
            <o:PixelsPerInch>96</o:PixelsPerInch>
        </o:OfficeDocumentSettings>
    </xml>
    <![endif]-->
    <style type="text/css">
        html,
        body {
            margin: 0 auto !important;
            padding: 0 !important;
            width: 100% !important;
            height: 100% !important;
            background: #f3f6f9;
        }

        table,
        td {
            mso-table-lspace: 0pt !important;
            mso-table-rspace: 0pt !important;
            border-collapse: collapse !important;
        }

        img {
            border: 0;
            outline: none;
            text-decoration: none;
            -ms-interpolation-mode: bicubic;
            display: block;
        }

        a {
            text-decoration: none;
        }

        a[x-apple-data-detectors],
        .unstyle-auto-detected-links a,
        .aBn {
            border-bottom: 0 !important;
            cursor: default !important;
            color: inherit !important;
            text-decoration: none !important;
            font-size: inherit !important;
            font-family: inherit !important;
            font-weight: inherit !important;
            line-height: inherit !important;
        }

        @media screen and (max-width: 680px) {
            .email-container {
                width: 100% !important;
            }

            .px-mobile {
                padding-left: 22px !important;
                padding-right: 22px !important;
            }


            .center-mobile {
                text-align: center !important;
            }

            .hero-title {
                font-size: 24px !important;
                line-height: 31px !important;
            }

            .folio-number {
                font-size: 34px !important;
                line-height: 40px !important;
            }

            .button-link {
                display: block !important;
            }
        }
    </style>
</head>

<body width="100%" style="margin:0; padding:0 !important; background:#f3f6f9;">
    <center role="article" aria-roledescription="email" aria-label="Registro exitoso" lang="es" dir="ltr" style="width:100%; background:#f3f6f9;">
        <div style="display:none; font-size:1px; line-height:1px; max-height:0; max-width:0; opacity:0; overflow:hidden; mso-hide:all; font-family:Arial, sans-serif;">
            Registro exitoso al Octavo Congreso Internacional de Seguridad y Proximidad Social.
        </div>

        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background:#f3f6f9;">
            <tr>
                <td align="center" style="padding:24px 12px;">
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="680" class="email-container" style="width:680px; max-width:680px; background:#ffffff; border:1px solid #d9e1ea; border-radius:16px; overflow:hidden;">
                        <tr>
                            <td style="background:#002248; padding:28px 38px 24px;" class="px-mobile">
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                    <?php if (! empty($hero_cid)): ?>
                                    <tr>
                                        <td align="center" style="padding:0 0 20px;">
                                            <img src="cid:<?= esc($hero_cid, 'attr') ?>" width="500" alt="Octavo Congreso Internacional de Seguridad y Proximidad Social" style="width:100%; max-width:500px; height:auto;">
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td align="center" style="font-family:Arial, Helvetica, sans-serif; color:#ffffff;">
                                            <p style="margin:0; font-size:12px; line-height:17px; letter-spacing:.2px; text-transform:uppercase; color:#c8d8ea; font-weight:600;">
                                                Confirmación de registro
                                            </p>
                                            <h1 class="hero-title" style="margin:10px 0 0; font-size:27px; line-height:34px; font-weight:600; color:#ffffff;">
                                                Octavo Congreso Internacional de Seguridad y Proximidad Social
                                            </h1>
                                            <p style="margin:12px 0 0; font-size:14px; line-height:21px; color:#dce8f4; font-style:italic; font-weight:400;">
                                                "De la proximidad a la paz; estrategias que funcionan.""
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <tr>
                            <td style="height:4px; line-height:4px; background:#8f1d35; font-size:0;">&nbsp;</td>
                        </tr>

                        <tr>
                            <td class="px-mobile" style="padding:30px 42px 18px; background:#ffffff;">
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background:#fff8fa; border:1px solid #e8c9d1; border-radius:18px; box-shadow:0 12px 24px rgba(143,29,53,.12);">
                                    <tr>
                                        <td align="center" style="padding:20px 22px; font-family:Arial, Helvetica, sans-serif; border-top:4px solid #8f1d35; border-radius:18px;">
                                            <p style="margin:0; font-size:12px; line-height:18px; letter-spacing:.2px; text-transform:uppercase; color:#002248; font-weight:700;">
                                                Folio de registro
                                            </p>
                                            <p class="folio-number" style="margin:4px 0 0; font-size:38px; line-height:44px; color:#8f1d35; font-weight:700;">
                                                <?= esc($id) ?>
                                            </p>
                                            <p style="margin:8px 0 0; font-size:21px; line-height:29px; color:#1f2937; font-weight:700; text-transform:uppercase;">
                                                <?= esc($nombre_completo) ?>
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <tr>
                            <td class="px-mobile" style="padding:8px 42px 16px; background:#ffffff;">
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background:#f8fbff; border:1px solid #c9d8ea; border-left:5px solid #8f1d35; border-radius:16px; box-shadow:0 10px 22px rgba(0,34,72,.10);">
                                    <tr>
                                        <td style="padding:22px 24px; font-family:Arial, Helvetica, sans-serif;">
                                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                               
                                                <tr>
                                                    <td style="padding-top:12px; font-size:14px; line-height:21px; text-align:justify; color:#374151; font-weight:400;">
                                                        El <strong style="color:#002248;">Gobierno Municipal de Nezahualc&oacute;yotl</strong> y la <strong style="color:#002248;">Comisar&iacute;a General de Seguridad Ciudadana</strong> agradecen su registro al <strong style="color:#8f1d35;">Octavo Congreso Internacional de Seguridad y Proximidad Social</strong>.
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding-top:10px; font-size:14px; line-height:21px; text-align:justify; color:#374151; font-weight:400;">
                                                        Para agilizar su acceso, <strong style="color:#8f1d35;">conserve este correo</strong> y presente su <strong style="color:#002248;">folio de registro</strong> el d&iacute;a del evento.
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <tr>
                            <td class="px-mobile" style="padding:8px 42px 10px; background:#ffffff;">
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background:#f6f9ff; border:1px solid #c9d8ea; border-left:5px solid #002248; border-radius:18px; box-shadow:0 12px 24px rgba(0,34,72,.12);">
                                    <tr>
                                        <td style="padding:20px 22px; font-family:Arial, Helvetica, sans-serif; color:#1f2937;">
                                            <h2 style="margin:0; font-size:18px; line-height:25px; color:#002248; font-weight:600;">
                                                Información del evento:
                                            </h2>
                                            <p style="margin:10px 0 0; font-size:14px; line-height:21px; color:#374151; font-weight:400; text-align:justify;">
                                                El <strong style="color:#8f1d35;">Octavo Congreso Internacional de Seguridad y Proximidad Social</strong> reunir&aacute; a especialistas, autoridades e instituciones para compartir experiencias, herramientas y estrategias orientadas a fortalecer la proximidad social, la prevenci&oacute;n y la construcci&oacute;n de paz desde el trabajo territorial.
                                            </p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding:0 22px 18px;">
                                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background:#ffffff; border:1px solid #d4deea; border-radius:16px; box-shadow:inset 0 1px 0 rgba(255,255,255,.90), 0 6px 14px rgba(15,23,42,.07);">
                                                <tr>
                                                    <td style="padding:14px 16px; font-family:Arial, Helvetica, sans-serif; border-bottom:1px solid #edf2f7;">
                                                        <p style="margin:0; font-size:12px; line-height:16px; color:#64748b; font-weight:600; text-transform:uppercase; letter-spacing:.2px;">Fecha</p>
                                                        <p style="margin:5px 0 0; font-size:14px; line-height:21px; color:#1f2937; font-weight:500;"><strong style="color:#002248;">6 y 7 de agosto de 2026</strong></p>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding:14px 16px; font-family:Arial, Helvetica, sans-serif; border-bottom:1px solid #edf2f7;">
                                                        <p style="margin:0; font-size:12px; line-height:16px; color:#64748b; font-weight:600; text-transform:uppercase; letter-spacing:.2px;">Horario</p>
                                                        <p style="margin:5px 0 0; font-size:14px; line-height:21px; color:#1f2937; font-weight:500;">El acto inaugural se llevar&aacute; a cabo a las <strong style="color:#8f1d35;">09:00 horas</strong> del d&iacute;a <strong style="color:#002248;">6 de agosto</strong>.</p>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding:14px 16px; font-family:Arial, Helvetica, sans-serif;">
                                                        <p style="margin:0; font-size:12px; line-height:16px; color:#64748b; font-weight:600; text-transform:uppercase; letter-spacing:.2px;">Ubicaci&oacute;n</p>
                                                        <p style="margin:5px 0 10px; font-size:14px; line-height:21px; color:#1f2937; font-weight:500; text-align:justify;"><strong style="color:#002248;">Auditorio Alfredo Harp Hel&uacute;</strong>, Universidad La Salle campus Nezahualc&oacute;yotl. Av. Bordo de Xochiaca, colonia Jard&iacute;n Bicentenario, Nezahualc&oacute;yotl, Estado de M&eacute;xico.</p>
                                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                                            <tr>
                                                                <td align="center" bgcolor="#8f1d35" style="border-radius:14px; box-shadow:0 6px 12px rgba(15,23,42,.18);">
                                                                    <a class="button-link" href="https://www.google.com/maps/place/Auditorio+Harp+Helu+La+Salle+Nezahualcoyotl/@19.4227266,-99.0165358,18z/data=!4m14!1m7!3m6!1s0x85d1fcb3bbbfbd61:0x445cfd169035318b!2sUniversidad+La+Salle+Nezahualc%C3%B3yotl!8m2!3d19.4234703!4d-99.0173808!16s%2Fg%2F11c3pzvbrq!3m5!1s0x85d1fda295d39633:0x1612cd4a8028cf4!8m2!3d19.4226026!4d-99.0166708!16s%2Fg%2F11rn7xj_1y?entry=ttu" target="_blank" style="display:inline-block; padding:10px 16px; font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:18px; font-weight:700; color:#ffffff; background:#8f1d35; border:1px solid #8f1d35; border-bottom:3px solid #641326; border-radius:14px;">Ver ubicaci&oacute;n</a>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <tr>
                            <td class="px-mobile" style="padding:18px 42px 8px; background:#ffffff;">
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background:#fff9fb; border:1px solid #ead1d7; border-left:5px solid #8f1d35; border-radius:18px; box-shadow:0 12px 24px rgba(143,29,53,.12);">
                                    <tr>
                                        <td style="padding:20px 22px; font-family:Arial, Helvetica, sans-serif;">
                                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                                <tr>
                                                    <td style="padding-bottom:14px; ">
                                                        <h2 style="margin:0; font-size:18px; line-height:25px; color:#002248; font-weight:600;">
                                                                                                           Acceso a la plataforma:

                                                        </h2>
                                                    </td>
                                                </tr>
                                                  <tr>
                                        <td  style="padding:0 0 14px; font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:21px; color:#475569; font-weight:400;">
                                            Ingrese a la plataforma para consultar información de acceso y seguimiento (<strong>Aquí podrás encontrar el enlace para la modalidad streaming</strong>).
                                        </td>
                                    </tr>
                                                <tr>
                                                    <td style="padding-top:16px;">
                                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background:#ffffff; border:1px solid #d4deea; border-radius:16px; box-shadow:inset 0 1px 0 rgba(255,255,255,.90), 0 6px 14px rgba(15,23,42,.07);">
                                                            <tr>
                                                                <td style="padding:14px 16px; font-family:Arial, Helvetica, sans-serif; border-bottom:1px solid #edf2f7;">
                                                                    <p style="margin:0; font-size:12px; line-height:16px; color:#64748b; font-weight:600; text-transform:uppercase; letter-spacing:.2px;">Correo registrado</p>
                                                                    <p style="margin:5px 0 0; font-size:14px; line-height:21px; color:#1f2937; font-weight:400;"><?= esc($correo) ?></p>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td style="padding:14px 16px; font-family:Arial, Helvetica, sans-serif;">
                                                                    <p style="margin:0; font-size:12px; line-height:16px; color:#64748b; font-weight:600; text-transform:uppercase; letter-spacing:.2px;">Clave de acceso</p>
                                                                    <p style="margin:5px 0 0; font-size:14px; line-height:21px; color:#1f2937; font-weight:500;"><?= esc($clave) ?></p>
                                                                </td>
                                                            </tr>
                                                            
                                                        </table>
                                                    </td>
                                                </tr>
                                                
                                            </table>
                                        </td>
                                    </tr>
                                                    <tr>
                            <td class="px-mobile" style="padding:22px 42px 8px; background:#ffffff;">
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                  
                                    <tr>
                                        <td align="center">
                                            <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                                <tr>
                                                    <td align="center" bgcolor="#002248" style="border-radius:14px; box-shadow:0 6px 12px rgba(15,23,42,.18);">
                                                        <a class="button-link" href="https://congreso.seguridadneza.gob.mx/acceso/index2.php" target="_blank" style="display:inline-block; padding:12px 20px; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:20px; font-weight:700; color:#ffffff; background:#002248; border:1px solid #002248; border-bottom:3px solid #00152d; border-radius:14px;">
                                                            Acceso a plataforma
                                                        </a>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                                </table>
                            </td>
                        </tr>
        

                        <tr>
                            <td class="px-mobile" style="padding:16px 42px 26px; background:#ffffff;">
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background:#fff4f7; border:1px solid #e5b8c3; border-left:5px solid #8f1d35; border-radius:18px; box-shadow:0 12px 24px rgba(143,29,53,.13);">
                                    <tr>
                                        <td align="center" style="padding:20px 22px; font-family:Arial, Helvetica, sans-serif;">
                                            <p style="margin:0 0 14px; font-size:14px; line-height:21px; color:#6f1d2f; font-weight:400;">
                                                Descargue su <strong style="color:#8f1d35;">folio de registro</strong> y preséntelo durante el <strong style="color:#002248;">proceso de asistencia</strong>.
                                            </p>
                                            <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                                <tr>
                                                    <td align="center" bgcolor="#8f1d35" style="border-radius:14px; box-shadow:0 6px 12px rgba(15,23,42,.18);">
                                                        <a class="button-link" href="<?= esc($folioUrl, 'attr') ?>" target="_blank" style="display:inline-block; padding:12px 20px; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:20px; font-weight:700; color:#ffffff; background:#8f1d35; border:1px solid #8f1d35; border-bottom:3px solid #641326; border-radius:14px;">
                                                            Descargar folio de registro
                                                        </a>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <tr>
                            <td class="px-mobile" style="padding:0 42px 34px; background:#ffffff;">
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                    <tr>
                                        <td style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:22px; color:#374151; text-align:center; font-weight:400;">
                                            <p style="margin:0;">Sin otro particular, <strong style="color:#002248;">agradecemos la atención prestada</strong> a la presente.</p>
                                            <p style="margin:18px 0 0; font-weight:500; color:#1f2937;">
                                                <strong style="color:#8f1d35;">Atentamente:</strong><br>
                                                Comisaría General de Seguridad Ciudadana<br>
                                                de Nezahualcóyotl
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <tr>
                            <td style="background:#002248; padding:28px 38px;" class="px-mobile">
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                    <?php if (! empty($logo_cid)): ?>
                                    <tr>
                                        <td align="center" style="padding-bottom:16px;">
                                            <img src="cid:<?= esc($logo_cid, 'attr') ?>" width="210" alt="Gobierno Municipal de Nezahualcoyotl" style="width:210px; max-width:70%; height:auto;">
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td align="center" style="font-family:Arial, Helvetica, sans-serif; color:#dbe7f3; font-size:14px; line-height:22px; font-weight:400;">
                                            <span style="color:#ffffff; font-weight:500;">Gobierno Municipal de Nezahualcóyotl</span><br>
                                            Comisaría General de Seguridad Ciudadana
                                            <br><br>
                                            Contacto:
                                            <a href="tel:5526197979" style="color:#ffffff; font-weight:500;">55 2619 7979 Ext. 1102</a>
                                            <br>
                                            Redes sociales:
                                            <a href="https://x.com/seguridadneza" target="_blank" style="color:#ffffff; text-decoration:underline; font-weight:400;">X</a>
                                               |   
                                            <a href="https://www.facebook.com/NezaSeguridad/" target="_blank" style="color:#ffffff; text-decoration:underline; font-weight:400;">Facebook</a>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </center>
</body>

</html>
