<body>
    <main class="container-fluid">
        <section>
            <div>
                <div class="container-fluid p-0">
                    <!-- <div class="fondo-1 position-relative">
                        <h1 class="fw-bold mb-1 text-white">Formulario de registro</h1>
                    </div> -->

                    <div class="d-flex">

                        <!-- MENÚ -->
                        <!-- <div class="cuadro shadow rounded-end p-4 w-25 vh-100"></div> -->

                        <div
                            class="cuadro shadow rounded-end p-4 w-25 vh-100 d-flex flex-column justify-content-center align-items-center gap-2">

                            <img src="<?= base_url('assets/img/comisaria.png') ?>" class="img-fluid"
                                style="max-width: 80%;" alt="Imagen arriba">

                            <img src="<?= base_url('assets/img/comisaria.png') ?>" class="img-fluid mb-3"
                                style="max-width: 80%;" alt="Imagen abajo">

                        </div>

                        <div class="contenido flex-grow-1">

                            <div class="d-flex flex-column align-items-center">

                                <h1 class="titulo fw-bold mt-5">
                                    Pláticas de Medidas Preventivas en Casos de Extorsión
                                </h1>

                                <h2 class="subtitulo mt-4">
                                    Del 09 al 12 de junio de 2026
                                </h2>

                                <h2 class="mt-4">
                                    Registro
                                </h2>

                            </div>

                            <?php if (session()->getFlashdata('success')): ?>
                            <p style="color: green;">
                                <?= session()->getFlashdata('success') ?>
                            </p>
                            <?php endif; ?>

                            <?php if (session()->get('errors')): ?>
                            <div style="color: red;">
                                <ul>
                                    <?php /* foreach (session()->get('errors') as $error): ?>
                                    <li><?= esc($error) ?></li>
                                    <?php endforeach;  */?>
                                </ul>
                            </div>
                            <?php endif; ?>

                            <div class="container px-5 py-4 mt-4">
                                <form action="<?= base_url('registro/guardar') ?>" method="post">

                                    <?= csrf_field() ?>

                                    <div class="row g-4">

                                        <div class="col-md-4">
                                            <label class="form-label">Nombre:</label>
                                            <input type="text" class="form-control linea" name="nombre" id="nombre"
                                                required>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Apellido Paterno:</label>
                                            <input type="text" class="form-control linea" name="apellido_p"
                                                id="apellido_p" required>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Apellido Materno:</label>
                                            <input type="text" class="form-control linea" name="apellido_m"
                                                id="apellido_m" required>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Correo:</label>
                                            <input type="email" class="form-control linea" name="correo" id="correo"
                                                required>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Sexo</label>
                                            <select class="form-select select-estilo" name="id_sexo" id="id_sexo"
                                                required>
                                                <option value="">Seleccione una opción</option>
                                                <?php /* foreach ($sexos as $sexo): ?>
                                                <option value="<?= $sexo['id_sexo'] ?>">
                                                    <?= esc($sexo['sexo']) ?>
                                                </option>
                                                <?php endforeach; */ ?>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Dependencia</label>
                                            <select class="form-select select-estilo" name="id_dependencia"
                                                id="id_dependencia" required>
                                                <option value="">Seleccione una opción</option>
                                                <?php /* foreach ($dependencias as $dependencia): ?>
                                                <option value="<?= $dependencia['id_dependencia'] ?>">
                                                    <?= esc($dependencia['dependencia']) ?>
                                                </option>
                                                <?php endforeach; */ ?>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Estado</label>
                                            <select class="form-select select-estilo" name="id_estado" id="id_estado"
                                                required>
                                                <option value="">Seleccione una opción</option>
                                                <?php /* foreach ($estados as $estado): ?>
                                                <option value="<?= $estado['id_estado'] ?>">
                                                    <?= esc($estado['estado']) ?>
                                                </option>
                                                <?php endforeach; */ ?>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Municipio</label>
                                            <select class="form-select select-estilo" name="id_municipio"
                                                id="id_municipio" required>
                                                <option value="">Seleccione una opción</option>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Sector</label>
                                            <select class="form-select select-estilo" name="id_sector" id="id_sector"
                                                required>
                                                <option value="">Seleccione una opción</option>
                                                <?php /* foreach ($sectores as $sector): ?>
                                                <option value="<?= $sector['id_sector'] ?>">
                                                    <?= esc($sector['sector']) ?>
                                                </option>
                                                <?php endforeach; */ ?>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Categoría</label>
                                            <select class="form-select select-estilo" name="id_categoria"
                                                id="id_categoria" required>
                                                <option value="">Seleccione una opción</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-12 d-flex justify-content-end mt-4">

                                        <button class="btn boton-enviar px-5">
                                            Enviar
                                        </button>

                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <script src="<?= base_url('assets/JS/index.js') ?>"></script>
</body>

</html>