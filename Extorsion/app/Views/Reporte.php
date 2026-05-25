<body>
    <main class="container-fluid">
        <section>
            <div>
                <div class="container-fluid p-0">
                    <div class="d-flex">
                        <!-- MENÚ -->
                        <div
                            class="cuadro shadow rounded-end p-4 w-15 vh-100 d-flex flex-column justify-content-start align-items-center gap-2">
                            <div class="logos-container">

                                <img src="<?= base_url('assets/img/ayun.png') ?>" class="logo-principal"
                                    alt="Logo principal">

                                <img src="<?= base_url('assets/img/comisaria.png') ?>" class="logo-secundario mt-5"
                                    alt="Logo secundario">

                            </div>

                            <!-- Opciones -->
                            <!-- <div class="d-flex flex-column gap-3">

                                <div class="bg-light rounded-pill p-3">
                                    Dashboard
                                </div>

                                <div class="p-3">
                                    Registros
                                </div>

                                <div class="p-3">
                                    Reportes
                                </div>

                                <div class="p-3">
                                    Exportaciones
                                </div>

                                <div class="p-3">
                                    Configuración
                                </div>

                            </div> -->

                            <div class="menu-wrapper d-flex flex-column gap-3 menu-opciones mt-5">

                                <div class="menu-item">
                                    <span class="arrow">❮</span> Dashboard
                                </div>

                                <a href="/reporte/exportar" class="btn-exportar mt-4">
                                    <span class="arrow">❮</span>
                                    Exportar
                                </a>

                                <div class="menu-item mt-4">
                                    <span class="arrow">❮</span> Filtro
                                </div>
                            </div>

                            <div class="menu-salir">
                                <img src="<?= base_url('assets/img/cerrar-sesion.png') ?>" class="logo-salir"
                                    alt="Logo salir">
                                <h4 class="mt-2" style="color: white;">Salir</h4>
                            </div>
                        </div>

                        <div class="contenido flex-grow-1">

                            <div class="d-flex flex-column align-items-center">

                                <h1 class="titulo fw-bold mt-3">
                                    Pláticas de Medidas Preventivas en Casos de Extorsión
                                </h1>

                            </div>

                            <div class="container px-0 py-4 mt-3">
                                <div class="row g-5">
                                    <h3 class="titulo fw-bold mt-4">Dashboard</h3>

                                    <!-- <div class="row">
                                        <div class="col-sm-4 g-3">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h5 class="card-title">Total de registros</h5>
                                                    <p class="card-text"><?= $total?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-4 g-3">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h5 class="card-title">Total del Sector Comercial</h5>
                                                    <p class="card-text"><?php foreach ($sector as $fila): ?>
                                                        <tr>
                                                            <td><?= esc($fila['sector']) ?></td>
                                                            <td><?= esc($fila['total']) ?></td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-4 g-3">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h5 class="card-title">Total del Sector Servicio</h5>
                                                    <p class="card-text"><?php foreach ($sector as $fila): ?>
                                                        <tr>
                                                            <td><?= esc($fila['sector']) ?></td>
                                                            <td><?= esc($fila['total']) ?></td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div> -->

                                    <div class="row g-3">

                                        <div class="col-sm-4">
                                            <div class="card-total card-modern card-green">
                                                <div class="card-body d-flex align-items-center">

                                                    <div class="flex-grow-1">
                                                        <h6>Total de registros</h6>
                                                        <h2 class="fw-bold"><?= $total ?></h2>
                                                    </div>

                                                    <div class="card-icon-bg">
                                                        📊
                                                    </div>

                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-sm-4">
                                            <div class="card-comercio card-modern card-green-light">
                                                <div class="card-body d-flex align-items-center">

                                                    <div class="flex-grow-1">
                                                        <h6>Sector Comercial</h6>

                                                        <h2 class="fw-bold">
                                                            <?php foreach ($sector as $fila): ?>
                                                            <?php if ($fila['sector'] == 'Comercial'): ?>
                                                            <?= esc($fila['total']) ?>
                                                            <?php endif; ?>
                                                            <?php endforeach; ?>
                                                        </h2>
                                                    </div>

                                                    <div class="card-icon-bg">
                                                        🏪
                                                    </div>

                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-sm-4">
                                            <div class="card-servicio card-modern card-green-soft">
                                                <div class="card-body d-flex align-items-center">

                                                    <div class="flex-grow-1">
                                                        <h6>Sector Servicio</h6>

                                                        <h2 class="fw-bold">
                                                            <?php foreach ($sector as $fila): ?>
                                                            <?php if ($fila['sector'] == 'Servicio'): ?>
                                                            <?= esc($fila['total']) ?>
                                                            <?php endif; ?>
                                                            <?php endforeach; ?>
                                                        </h2>
                                                    </div>

                                                    <div class="card-icon-bg">
                                                        🛠️
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <!-- <div class="col-sm-4 g-5">
                                            <h3>Registros por dependencia</h3>

                                            <table border="1">
                                                <tr>
                                                    <th>Dependencia</th>
                                                    <th>Total</th>
                                                </tr>

                                                <?php foreach ($dependencia as $fila): ?>
                                                <tr>
                                                    <td><?= esc($fila['dependencia']) ?></td>
                                                    <td><?= esc($fila['total']) ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </table>
                                        </div> -->

                                        <div class="col-sm-4 g-4">
                                            <h3>Registros por dependencia</h3>

                                            <canvas class="mt-4" id="graficaDependencias"></canvas>
                                        </div>

                                        <!-- <div class="col-sm-4 g-5">
                                            <h3>Registros por sexo</h3>

                                            <table border="1">
                                                <tr>
                                                    <th>Sexo</th>
                                                    <th>Total</th>
                                                </tr>

                                                <?php foreach ($sexo as $fila): ?>
                                                <tr>
                                                    <td><?= esc($fila['sexo']) ?></td>
                                                    <td><?= esc($fila['total']) ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </table>
                                        </div> -->

                                        <div class="col-sm-4 g-4">
                                            <h3>Registros por sexo</h3>

                                            <canvas class="mt-4" id="graficaSexo"></canvas>
                                        </div>

                                        <div class="col-sm-4 g-5">
                                            <nav class="navbar-simple">
    <form class="search-simple">

        <input type="search"
               placeholder="Buscar..."
               aria-label="Search">

        <button type="submit">Buscar</button>

    </form>
</nav>
                                            <!-- <div class="col-sm-12 g-5">
                                                <div class="tabla-scroll-wrapper">
                                                    <div class="tabla-scroll">

                                                        <table class="table table-striped table-hover">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Nombre completo</th>
                                                                    <th>Correo</th>
                                                                    <th>Municipio</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php $i = 1; foreach ($registros as $fila): ?>
                                                                <tr>
                                                                    <th><?= $i++ ?></th>

                                                                    <td>
                                                                        <?= esc($fila['nombre']) . ' ' . esc($fila['apellido_p']) . ' ' . esc($fila['apellido_m']) ?>
                                                                    </td>

                                                                    <td><?= esc($fila['correo']) ?></td>

                                                                    <td><?= esc($fila['municipio']) ?></td>
                                                                </tr>
                                                                <?php endforeach; ?>

                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div> -->
                                        </div>
                                    </div>

                                    <div class="row">
                                        <!-- <div class="col-sm-4 g-5">
                                            <h3>Registros por estado</h3>
                                            <table border="1">
                                                <tr>
                                                    <th>Estado</th>
                                                    <th>Total</th>
                                                </tr>

                                                <?php foreach ($estado as $fila): ?>
                                                <tr>
                                                    <td><?= esc($fila['estado']) ?></td>
                                                    <td><?= esc($fila['total']) ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </table>
                                        </div> -->
                                        <div class="col-sm-4 g-4">
                                            <h3>Registros por estado</h3>
                                            <canvas class="mt-4" id="graficaEstado"></canvas>
                                        </div>

                                        <div class="col-sm-4 g-5">
                                            <div class="card mt-4 style=" width: 18rem;">
                                                <div class="card-body">
                                                    <h5 class="card-title">Informacion del Sector Comercial</h5>
                                                    <p class="card-text">With supporting text below as a natural
                                                        lead-in
                                                        to additional content.</p>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <script>
    window.dependenciasData = <?= json_encode($dependencia) ?>;
    </script>
    <script>
    window.sexoData = <?= json_encode($sexo) ?>;
    </script>
    <script>
    window.estadoData = <?= json_encode($estado) ?>;
    </script>

    <script src="<?= base_url('assets/JS/index.js') ?>"></script>
</body>

</html>