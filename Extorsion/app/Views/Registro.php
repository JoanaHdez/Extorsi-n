<body>

    <div class="modal fade" id="modalComisaria" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">

        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-body text-center">

                    <h4 class="mb-3">
                        ¿Perteneces a la Comisaría?
                    </h4>

                </div>

                <div class="modal-footer justify-content-center">

                    <button type="button" class="btn btn-success" id="btnComisariaSi">
                        Sí
                    </button>

                    <button type="button" class="btn btn-secondary" id="btnComisariaNo">
                        No
                    </button>

                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="modalNomina" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">

        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        Personal de Comisaría
                    </h5>
                </div>

                <div class="modal-body">

                    <label class="form-label">
                        Número de nómina
                    </label>

                    <input type="text" class="form-control" id="nominaBusqueda" placeholder="Ingrese su nómina">

                    <div id="mensajeNomina" class="text-danger mt-2">
                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-primary" id="btnBuscarNomina">
                        Buscar
                    </button>

                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDatosComisaria" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">

        <div class="modal-dialog modal-lg modal-dialog-centered">

            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        Verifique sus datos
                    </h5>
                </div>

                <div class="modal-body">

                    <div class="row g-3">

                        <div class="col-md-4">
                            <label>Nombre</label>
                            <input type="text" id="modalNombre" class="form-control" readonly>
                        </div>

                        <div class="col-md-4">
                            <label>Apellido paterno</label>
                            <input type="text" id="modalApellidoP" class="form-control" readonly>
                        </div>

                        <div class="col-md-4">
                            <label>Apellido materno</label>
                            <input type="text" id="modalApellidoM" class="form-control" readonly>
                        </div>

                        <div class="col-md-6">
                            <label>Área</label>
                            <input type="text" id="modalArea" class="form-control" readonly>
                        </div>

                        <div class="col-md-6">
                            <label>Función</label>
                            <input type="text" id="modalFuncion" class="form-control" readonly>
                        </div>

                        <div class="col-md-6">
                            <label>Sexo</label>
                            <input type="text" id="modalSexo" class="form-control" readonly>
                        </div>

                        <div class="col-md-6">
                            <label>Correo</label>
                            <input type="email" id="modalCorreo" class="form-control">
                        </div>



                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-primary" id="btnConfirmarComisaria">
                        Continuar
                    </button>

                </div>

            </div>

        </div>

    </div>

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
                                <?php if (session('errors.limite')): ?>
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
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Dependencia</label>

                                            <select class="form-select select-estilo" name="id_dependencia"
                                                id="id_dependencia" required>
                                                <option value="" selected disabled hidden>Seleccionar</option>
                                                <?php foreach ($dependencias as $dependencia): ?>
                                                <option value="<?= $dependencia['id_dependencia'] ?>"
                                                    data-dependencia="<?= esc(mb_strtolower($dependencia['dependencia'], 'UTF-8')) ?>"
                                                    <?= old('id_dependencia') == $dependencia['id_dependencia'] ? 'selected' : '' ?>>
                                                    <?= esc($dependencia['dependencia']) ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-4 d-none" id="dependenciaOtroGrupo">
                                            <label class="form-label">Especifique dependencia</label>

                                            <input type="text" class="form-control linea" name="dependencia_otro"
                                                id="dependencia_otro" maxlength="150"
                                                value="<?= esc(old('dependencia_otro')) ?>"
                                                oninput="this.value = this.value.toUpperCase()">
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
    <script>
    window.mostrarModalComisaria = <?= session()->getFlashdata('success') ? 'false' : 'true' ?>;
    </script>

    <script src="<?= base_url('assets/JS/index.js') ?>"></script>

    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
        <div id="toastGlobal" class="toast align-items-center border-0 shadow-lg toast-institucional" role="alert">
            <div class="d-flex align-items-center">
                <div class="toast-body d-flex align-items-center gap-3 py-3">
                    <div class="icon-check">✓</div>
                    <div>
                        <div class="fw-bold" id="toastTitulo">Éxito</div>
                        <div class="small" id="toastMensaje">Operación realizada correctamente</div>
                    </div>
                </div>

                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
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