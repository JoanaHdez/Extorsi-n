<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuestionario</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            padding: 24px;
            background: #eef2f7;
            color: #1f2933;
            font-family: Arial, Helvetica, sans-serif;
        }

        .page {
            width: min(760px, 100%);
            margin: 0 auto;
            background: #ffffff;
            border-top: 8px solid #8a1538;
            box-shadow: 0 14px 34px rgba(20, 36, 64, 0.14);
        }

        .header {
            padding: 28px 28px 18px;
            text-align: center;
            border-bottom: 1px solid #e5e9ef;
        }

        h1 {
            margin: 0;
            color: #243b6b;
            font-size: 28px;
            line-height: 1.25;
        }

        .intro {
            margin: 12px 0 0;
            color: #667085;
            font-size: 16px;
            line-height: 1.5;
        }

        form {
            padding: 26px 28px 30px;
        }

        .question {
            margin-bottom: 22px;
        }

        .scale {
            margin: 22px 28px 0;
            padding: 18px 20px;
            border: 1px solid #e5e9ef;
            border-radius: 8px;
            background: #f8fafc;
            color: #344054;
            font-size: 15px;
            line-height: 1.55;
        }

        .scale-title {
            margin: 0 0 8px;
            color: #243b6b;
            font-weight: 700;
        }

        label {
            display: block;
            margin-bottom: 10px;
            color: #344054;
            font-size: 16px;
            font-weight: 700;
            line-height: 1.4;
        }

        textarea,
        select,
        input[type="text"] {
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 12px 14px;
            color: #1f2933;
            font-size: 16px;
            font-family: Arial, Helvetica, sans-serif;
        }

        textarea {
            min-height: 96px;
            resize: vertical;
        }

        .options {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .option {
            display: flex;
            width: 48px;
            height: 44px;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 0;
            border: 1px solid #e5e9ef;
            border-radius: 8px;
            color: #344054;
            font-size: 18px;
            font-weight: 700;
        }

        .option input {
            position: absolute;
            opacity: 0;
        }

        .option:has(input:checked) {
            border-color: #8a1538;
            background: #8a1538;
            color: #ffffff;
        }

        button {
            width: 100%;
            border: 0;
            border-radius: 8px;
            padding: 15px 20px;
            background: #8a1538;
            color: #ffffff;
            font-size: 17px;
            font-weight: 700;
        }

        .error {
            margin: 0 0 18px;
            padding: 12px 14px;
            border-radius: 8px;
            background: #fee2e2;
            color: #991b1b;
            font-size: 15px;
        }
    </style>
</head>

<body>
    <?php
    $nombreCompleto = trim(($registro['nombre'] ?? '') . ' ' . ($registro['apellido_p'] ?? '') . ' ' . ($registro['apellido_m'] ?? ''));
    ?>

    <main class="page">
        <section class="header">
            <h1>Cuestionario previo a constancia</h1>
            <p class="intro">
                <?= esc($nombreCompleto) ?>, responda el siguiente cuestionario para descargar su constancia.
            </p>
            <p class="intro">
                Gracias por tu asistencia.
            </p>
        </section>

        <section class="scale">
            <p class="scale-title">Escala</p>
            <div>1 = Muy malo / totalmente en desacuerdo</div>
            <div>2 = Malo / desacuerdo</div>
            <div>3 = Regular</div>
            <div>4 = Bueno / de acuerdo</div>
            <div>5 = Excelente / totalmente de acuerdo</div>
        </section>

        <form method="post" action="<?= esc(base_url('constancia/' . $token . '/cuestionario'), 'attr') ?>">
            <?= csrf_field() ?>

            <?php if (session('errors.cuestionario')): ?>
                <div class="error"><?= esc(session('errors.cuestionario')) ?></div>
            <?php endif; ?>

            <?php foreach ($preguntas as $pregunta): ?>
                <div class="question">
                    <label for="<?= esc($pregunta['id'], 'attr') ?>"><?= esc($pregunta['texto']) ?></label>

                    <?php if (($pregunta['tipo'] ?? '') === 'textarea'): ?>
                        <textarea id="<?= esc($pregunta['id'], 'attr') ?>" name="respuestas[<?= esc($pregunta['id'], 'attr') ?>]" <?= ! empty($pregunta['required']) ? 'required' : '' ?>></textarea>
                    <?php elseif (($pregunta['tipo'] ?? '') === 'radio'): ?>
                        <div class="options">
                            <?php foreach (($pregunta['opciones'] ?? []) as $opcion): ?>
                                <label class="option">
                                    <input type="radio" name="respuestas[<?= esc($pregunta['id'], 'attr') ?>]" value="<?= esc($opcion, 'attr') ?>" <?= ! empty($pregunta['required']) ? 'required' : '' ?>>
                                    <?= esc($opcion) ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <input type="text" id="<?= esc($pregunta['id'], 'attr') ?>" name="respuestas[<?= esc($pregunta['id'], 'attr') ?>]" <?= ! empty($pregunta['required']) ? 'required' : '' ?>>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <button type="submit">Enviar respuestas y descargar constancia</button>
        </form>
    </main>
</body>

</html>
