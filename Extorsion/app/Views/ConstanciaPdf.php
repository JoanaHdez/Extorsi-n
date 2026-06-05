<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Constancia</title>
    <style>
        @page {
            size: letter landscape;
            margin: 28px 34px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #172033;
            font-family: DejaVu Sans, Arial, sans-serif;
            background: #ffffff;
        }

        .page {
            position: relative;
            padding: 0;
            background: #ffffff;
        }

        .safe {
            position: relative;
            z-index: 3;
            width: auto;
            margin: 0;
            border: 8px solid #1f3764;
            padding: 26px 34px 24px;
            background-color: transparent;
        }

        .top-line {
            display: block;
            height: 4px;
            width: 82%;
            margin: 0 auto 18px;
            background: #8a1538;
        }

        .side-line {
            display: none;
        }

        .watermark {
            position: absolute;
            top: 124px;
            left: 292px;
            width: 310px;
            opacity: 0.07;
            z-index: 1;
        }

        .logos {
            width: 86%;
            margin-left: auto;
            margin-right: auto;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .logo-left {
            width: 92px;
            height: auto;
        }

        .logo-right {
            width: 112px;
            height: auto;
        }

        .institution {
            margin: 0;
            color: #5c6a82;
            font-size: 10px;
            letter-spacing: 1.2px;
            text-align: center;
            text-transform: uppercase;
        }

        .title {
            margin: 8px 0 0;
            color: #1f3764;
            font-size: 38px;
            line-height: 1;
            letter-spacing: 2px;
            text-align: center;
            text-transform: uppercase;
        }

        .badge {
            width: 210px;
            margin: 12px auto 0;
            padding: 6px 12px;
            color: #8a1538;
            font-size: 11px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            background: #f8eaf0;
            border-radius: 18px;
        }

        .copy {
            width: 78%;
            margin: 22px auto 0;
            color: #334155;
            font-size: 14px;
            line-height: 1.55;
            text-align: center;
        }

        .course {
            width: 80%;
            margin: 14px auto 0;
            color: #1f2937;
            font-size: 14px;
            line-height: 1.45;
            text-align: center;
        }

        .course strong {
            color: #1f3764;
            font-size: 15px;
        }

        .name-box {
            width: 78%;
            margin: 20px auto 0;
            padding: 12px 22px 13px;
            text-align: center;
            border-top: 2px solid #8a1538;
            border-bottom: 2px solid #8a1538;
        }

        .name {
            margin: 0;
            color: #111827;
            font-size: 28px;
            font-weight: bold;
            line-height: 1.15;
            text-transform: uppercase;
        }

        .details {
            width: 84%;
            margin: 18px auto 0;
            border-collapse: collapse;
        }

        .details td {
            padding: 8px 10px;
            font-size: 10.5px;
            border-bottom: 1px solid #e3e8f2;
        }

        .label {
            width: 16%;
            color: #5c6a82;
            font-weight: bold;
            text-transform: uppercase;
        }

        .value {
            color: #172033;
            font-weight: bold;
        }

        .notice {
            width: 82%;
            margin: 16px auto 0;
            padding: 8px 16px;
            color: #5c6a82;
            font-size: 9.8px;
            line-height: 1.45;
            text-align: center;
            background: #f7f9fc;
            border: 1px solid #e1e7f0;
        }

        .footer {
            width: 84%;
            margin: 18px auto 0;
            border-collapse: collapse;
        }

        .folio {
            color: #8a1538;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .signature-line {
            width: 220px;
            margin: 0 auto 5px;
            border-top: 1px solid #1f3764;
        }

        .signature {
            color: #1f3764;
            font-size: 9.5px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }

        .place {
            color: #5c6a82;
            font-size: 10px;
            text-align: right;
        }
    </style>
</head>

<body>
    <?php
    $nombreCompleto = trim(($registro['nombre'] ?? '') . ' ' . ($registro['apellido_p'] ?? '') . ' ' . ($registro['apellido_m'] ?? ''));
    $fechaRegistro = empty($registro['fecha_registro'])
        ? date('d/m/Y')
        : date('d/m/Y', strtotime($registro['fecha_registro']));
    ?>

    <div class="page">
        <?php if (! empty($coyote)): ?>
            <img class="watermark" src="<?= esc($coyote, 'attr') ?>" alt="">
        <?php endif; ?>

        <div class="safe">
            <div class="top-line"></div>
            <div class="side-line"></div>

            <table class="logos">
                <tr>
                    <td style="width: 33%; text-align: left; vertical-align: middle;">
                        <?php if (! empty($logoAyuntamiento)): ?>
                            <img class="logo-left" src="<?= esc($logoAyuntamiento, 'attr') ?>" alt="Ayuntamiento">
                        <?php endif; ?>
                    </td>
                    <td style="width: 34%; text-align: center; vertical-align: middle;">
                        <p class="institution">Secretaría de Seguridad y Protección Ciudadana</p>
                    </td>
                    <td style="width: 33%; text-align: right; vertical-align: middle;">
                        <?php if (! empty($logoComisaria)): ?>
                            <img class="logo-right" src="<?= esc($logoComisaria, 'attr') ?>" alt="Comisaría">
                        <?php endif; ?>
                    </td>
                </tr>
            </table>

            <h1 class="title">Constancia</h1>
            <div class="badge">Registro de asistencia</div>

            <p class="copy">
                Se hace constar que la persona participante completó su registro de asistencia a las:
            </p>

            <p class="course">
                <strong>Pláticas de Medidas Preventivas en Casos de Extorsión</strong><br>
                Unidad de Antisecuestro y Antiextorsión
            </p>

            <div class="name-box">
                <p class="name"><?= esc($nombreCompleto) ?></p>
            </div>

            <table class="details">
                <tr>
                    <td class="label">Tipo</td>
                    <td class="value"><?= esc($registro['tipo_registro']) ?></td>
                    <td class="label">Sexo</td>
                    <td class="value"><?= esc($registro['sexo'] ?: 'No especificado') ?></td>
                </tr>
                <tr>
                    <td class="label">Correo</td>
                    <td class="value"><?= esc($registro['correo']) ?></td>
                    <td class="label">Fecha</td>
                    <td class="value"><?= esc($fechaRegistro) ?></td>
                </tr>
                <?php if (($registro['tipo_registro'] ?? '') === 'Comisaría'): ?>
                    <tr>
                        <td class="label">Nómina</td>
                        <td class="value"><?= esc($registro['nomina']) ?></td>
                        <td class="label">Área</td>
                        <td class="value"><?= esc($registro['area']) ?></td>
                    </tr>
                    <tr>
                        <td class="label">Función</td>
                        <td class="value" colspan="3"><?= esc($registro['funcion']) ?></td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td class="label">Dependencia</td>
                        <td class="value" colspan="3"><?= esc($registro['dependencia']) ?></td>
                    </tr>
                <?php endif; ?>
            </table>

            <div class="notice">
                Documento generado digitalmente por el sistema de registro. Esta constancia acredita asistencia registrada en la plataforma institucional.
            </div>

            <table class="footer">
                <tr>
                    <td style="width: 33%; vertical-align: bottom;" class="folio">Folio: <?= esc($registro['folio']) ?></td>
                    <td style="width: 34%; vertical-align: bottom;">
                        <div class="signature-line"></div>
                        <div class="signature">Registro validado digitalmente</div>
                    </td>
                    <td style="width: 33%; vertical-align: bottom;" class="place">Nezahualcóyotl, Estado de México</td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>
