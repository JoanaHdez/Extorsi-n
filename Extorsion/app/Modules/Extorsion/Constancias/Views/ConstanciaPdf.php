<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Constancia</title>
    <style>
        @page {
            size: 11in 8.5in;
            margin: 0;
        }

        body {
            margin: 0;
            font-family: DejaVu Sans, Arial, sans-serif;
        }

        .page {
            position: relative;
            width: 11in;
            height: 8.5in;
            overflow: hidden;
        }

        .template {
            position: absolute;
            inset: 0;
            width: 11in;
            height: 8.5in;
            z-index: 1;
        }

        .name {
            position: absolute;
            top: 4.58in;
            left: 1.35in;
            z-index: 2;
            width: 8.3in;
            color: #000000;
            font-size: 24pt;
            font-weight: 700;
            line-height: 1.1;
            text-align: center;
            text-transform: uppercase;
        }
    </style>
</head>

<body>
    <?php
    $limpiarTextoPdf = static function ($valor): string {
        $texto = trim((string) $valor);

        if ($texto === '') {
            return '';
        }

        if (! mb_check_encoding($texto, 'UTF-8')) {
            $texto = mb_convert_encoding($texto, 'UTF-8', 'Windows-1252');
        }

        return str_replace('�', 'Ñ', $texto);
    };

    $nombreCompleto = trim(
        $limpiarTextoPdf($registro['nombre'] ?? '') . ' ' .
        $limpiarTextoPdf($registro['apellido_p'] ?? '') . ' ' .
        $limpiarTextoPdf($registro['apellido_m'] ?? '')
    );
    ?>

    <div class="page">
        <img class="template" src="<?= esc($plantilla, 'attr') ?>" alt="">
        <div class="name"><?= esc($nombreCompleto) ?></div>
    </div>
</body>

</html>
