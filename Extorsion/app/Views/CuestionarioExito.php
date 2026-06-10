<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Respuesta enviada</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: #16a34a;
            color: #ffffff;
            font-family: Arial, Helvetica, sans-serif;
        }

        .card {
            width: min(560px, 100%);
            padding: 40px 32px;
            border-radius: 18px;
            background: rgba(0, 0, 0, 0.16);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        h1 {
            margin: 0 0 12px;
            font-size: 34px;
            line-height: 1.2;
        }

        p {
            margin: 0;
            font-size: 18px;
            line-height: 1.55;
        }

        a {
            display: inline-block;
            margin-top: 24px;
            padding: 13px 22px;
            border-radius: 8px;
            background: #ffffff;
            color: #166534;
            font-weight: 700;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <main class="card">
        <h1>Respuesta enviada exitosamente</h1>
        <p>Su constancia se descargará automáticamente.</p>
        <a href="<?= esc($downloadUrl, 'attr') ?>">Descargar constancia</a>
    </main>

    <iframe src="<?= esc($downloadUrl, 'attr') ?>" style="display:none;" title="Descarga de constancia"></iframe>
</body>

</html>
