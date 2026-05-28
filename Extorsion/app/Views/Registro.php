<body>
    <main class="container-fluid">
        <section>
            <div>
                <div class="container-fluid p-0">

                    <div class="d-flex">

                        <div
                            class="cuadro shadow rounded-end p-4 w-25 vh-100 d-flex flex-column justify-content-center align-items-center gap-2">

                            <img src="<?= base_url('assets/img/ayun.png') ?>" class="img-fluid mb-3"
                                alt="Logo principal" style="max-width: 80%;">

                            <img src="<?= base_url('assets/img/comisaria.png') ?>" class="logo-secundario mb-3">

                        </div>

                        <div class="contenido flex-grow-1">

                            <div class="d-flex flex-column align-items-center">

                                <h1 class="titulo  mt-5">
                                    Pláticas de Medidas Preventivas en Casos de Extorsión
                                </h1>

                                <h3 class="subtitulo mt-4">
                                    Unidad de Antisecuestro y Antiextorsión de la Secretaría de Seguridad y Protección
                                    Ciudadana
                                </h3>
                                <h3 class="subtitulo mt-4">
                                    Del 09 al 12 de junio de 2026
                                </h3>

                                <h2 class="mt-4">
                                    Registro de Asistencia
                                </h2>

                            </div>

                            <?php if (session()->getFlashdata('success')): ?>
                            <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">

                                <div id="successToast"
                                    class="toast align-items-center border-0 shadow-lg toast-institucional"
                                    role="alert">

                                    <div class="d-flex align-items-center">

                                        <div class="toast-body d-flex align-items-center gap-3 py-3">

                                            <div class="icon-check">
                                                ✓
                                            </div>

                                            <div>
                                                <div class="fw-bold">Registro exitoso</div>
                                                <div class="small">
                                                    <?= session()->getFlashdata('success') ?>
                                                </div>
                                            </div>

                                        </div>

                                        <button type="button" class="btn-close btn-close-white me-2 m-auto"
                                            data-bs-dismiss="toast"></button>

                                    </div>
                                </div>

                            </div>
                            <?php endif; ?>

                            <?php if (session()->get('errors')): ?>
                            <div style="color: red;">
                                <ul>
                                    <?php foreach (session()->get('errors') as $error): ?>
                                    <li><?= esc($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php endif; ?>

                            <div class="container px-5 py-4 mt-5">
                                <?php if(session('errors.limite')): ?>
                                <div class="alert alert-danger">
                                    <?= session('errors.limite') ?>
                                </div>
                                <?php endif; ?>
                                <form action="<?= base_url('registro/guardar') ?>" method="post">

                                    <?= csrf_field() ?>

                                    <div class="row g-5">

                                        <div class="col-md-4">
                                            <label class="form-label">Nombre:</label>
                                            <input type="text" class="form-control linea" name="nombre" id="nombre"
                                                required oninput="this.value = this.value.toUpperCase()">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Apellido Paterno:</label>
                                            <input type="text" class="form-control linea" name="apellido_p"
                                                id="apellido_p" required
                                                oninput="this.value = this.value.toUpperCase()">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Apellido Materno:</label>
                                            <input type="text" class="form-control linea" name="apellido_m"
                                                id="apellido_m" required
                                                oninput="this.value = this.value.toUpperCase()">
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
                                                <option value="" selected disabled hidden>Seleccionar</option>
                                                <?php foreach ($sexos as $sexo): ?>
                                                <option value="<?= $sexo['id_sexo'] ?>">
                                                    <?= esc($sexo['sexo']) ?>
                                                </option>
                                                <?php endforeach;?>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Dependencia</label>

                                            <select class="form-select select-estilo" name="id_dependencia"
                                                id="id_dependencia" required>

                                                <option value="" selected disabled hidden>
                                                    Seleccionar
                                                </option>

                                                <?php foreach ($dependencias as $dependencia): ?>
                                                <option value="<?= $dependencia['id_dependencia'] ?>">
                                                    <?= esc($dependencia['dependencia']) ?>
                                                </option>
                                                <?php endforeach; ?>

                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Estado</label>
                                            <select class="form-select select-estilo" name="id_estado" id="id_estado"
                                                required>
                                                <option value="" selected disabled hidden>Seleccionar</option>
                                                <?php foreach ($estados as $estado): ?>
                                                <option value="<?= $estado['id_estado'] ?>">
                                                    <?= esc($estado['estado']) ?>
                                                </option>
                                                <?php endforeach;?>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Municipio</label>
                                            <select class="form-select select-estilo" name="id_municipio"
                                                id="id_municipio" required>
                                                <option value="" selected disabled hidden>Seleccionar</option>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Sector</label>
                                            <select class="form-select select-estilo" name="id_sector" id="id_sector"
                                                required>
                                                <option value="" selected disabled hidden>Seleccionar</option>
                                                <?php foreach ($sectores as $sector): ?>
                                                <option value="<?= $sector['id_sector'] ?>">
                                                    <?= esc($sector['sector']) ?>
                                                </option>
                                                <?php endforeach;?>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Categoría</label>
                                            <select class="form-select select-estilo" name="id_categoria"
                                                id="id_categoria" required>
                                                <option value="" selected disabled hidden>Seleccionar</option>
                                            </select>
                                        </div>

                                        <div class="col-md-4" id="categoria_otro_contenedor" style="display: none;">
                                            <label class="form-label">Especifique categoría</label>
                                            <input type="text" class="form-control linea" name="categoria_otro"
                                                id="categoria_otro" oninput="this.value = this.value.toUpperCase()">
                                        </div>
                                    </div>

                                    <div class="col-12 d-flex justify-content-end mt-5">

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

<?php if (session()->getFlashdata('success')): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {

    const toastEl = document.getElementById('successToast');

    const toast = new bootstrap.Toast(toastEl, {
        delay: 3000,
        autohide: true
    });

    toast.show();
});
</script>
<?php endif; ?>

</html>