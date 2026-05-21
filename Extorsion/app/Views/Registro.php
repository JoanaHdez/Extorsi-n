<body>
    <h1>Formulario de registro</h1>

    <?php if (session()->getFlashdata('success')): ?>
        <p style="color: green;">
            <?= session()->getFlashdata('success') ?>
        </p>
    <?php endif; ?>

    <form action="<?= base_url('registro/guardar') ?>" method="post">
        <label for="nombre">Nombre</label>
        <input type="text" name="nombre" id="nombre" required>

        <br><br>

        <label for="correo">Correo</label>
        <input type="email" name="correo" id="correo" required>

        <br><br>

        <label for="id_sexo">Sexo</label>
        <select name="id_sexo" id="id_sexo" required>
            <option value="">Seleccione una opción</option>
            <?php foreach ($sexos as $sexo): ?>
                <option value="<?= $sexo['id_sexo'] ?>">
                    <?= esc($sexo['sexo']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <br><br>

        <label for="id_dependencia">Dependencia</label>
        <select name="id_dependencia" id="id_dependencia" required>
            <option value="">Seleccione una opción</option>
            <?php foreach ($dependencias as $dependencia): ?>
                <option value="<?= $dependencia['id_dependencia'] ?>">
                    <?= esc($dependencia['dependencia']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <br><br>

        <label for="id_estado">Estado</label>
        <select name="id_estado" id="id_estado" required>
            <option value="">Seleccione una opción</option>
            <?php foreach ($estados as $estado): ?>
                <option value="<?= $estado['id_estado'] ?>">
                    <?= esc($estado['estado']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <br><br>

        <label for="id_municipio">Municipio</label>
        <select name="id_municipio" id="id_municipio" required>
            <option value="">Seleccione una opción</option>
        </select>

        <br><br>

        <label for="id_sector">Sector</label>
        <select name="id_sector" id="id_sector" required>
            <option value="">Seleccione una opción</option>
            <?php foreach ($sectores as $sector): ?>
                <option value="<?= $sector['id_sector'] ?>">
                    <?= esc($sector['sector']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <br><br>

        <label for="id_categoria">Categoría</label>
        <select name="id_categoria" id="id_categoria" required>
            <option value="">Seleccione una opción</option>
        </select>

        <br><br>

        <button type="submit">Guardar registro</button>
    </form>

    <script src="<?= base_url('assets/JS/index.js') ?>"></script>
</body>

</html>
