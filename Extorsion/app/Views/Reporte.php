<body>
    <main class="container-fluid">
        <section>
            <div>
                <div class="container-fluid p-0">
                    <div class="d-flex">
                        <div class="mobile-topbar d-md-none"></div>

                        <button class="menu-toggle d-md-none" id="menuToggle">
                            ☰
                        </button>

                        <div class="menu-overlay" id="menuOverlay"></div>

                        <div
                            class="cuadro shadow rounded-end p-4 w-15 vh-100 d-flex flex-column justify-content-start align-items-center gap-2">
                            <div class="logos-container">

                                <img src="<?= base_url('assets/img/ayun.png') ?>" class="logo-principal"
                                    alt="Logo principal">

                                <img src="<?= base_url('assets/img/comisaria.png') ?>" class="logo-secundario mt-5"
                                    alt="Logo secundario">

                            </div>

                            <div class="menu-wrapper d-flex flex-column gap-3 menu-opciones mt-5">

                                <div class="menu-item">
                                    <span class="arrow">
                                    </span> Dashboard
                                </div>

                                <a href="<?= base_url('index.php/reporte/exportar') ?>" class="btn-exportar mt-4">
                                    <span class="arrow">
                                    </span>
                                    Exportar
                                </a>

                                <div class="menu-item mt-4" id="menuFiltro">
                                    <span class="arrow">
                                    </span> Filtro
                                </div>
                                <form class="filter-panel" id="dashboardFiltros">
                                    <div class="filter-title">Filtros</div>

                                    <label>
                                        Tipo
                                        <select id="filtroTipo" aria-label="Tipo">
                                            <option value=""></option>
                                        </select>
                                    </label>

                                    <label>
                                        Área
                                        <select id="filtroArea" aria-label="Área">
                                            <option value=""></option>
                                        </select>
                                    </label>

                                    <label>
                                        Dependencia
                                        <select id="filtroDependencia" aria-label="Dependencia">
                                            <option value=""></option>
                                        </select>
                                    </label>

                                    <button type="button" id="limpiarFiltros">Limpiar filtros</button>
                                </form>
                            </div>

                            <a href="./registro" class="menu-salir text-decoration-none">
                                <img src="<?= base_url('assets/img/cerrar-sesion.png') ?>" class="logo-salir"
                                    alt="Logo salir">
                                <h4 class="mt-2" style="color: white;">Salir</h4>
                            </a>
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

                                    <div class="row g-3">

                                        <div class="col-sm-4">
                                            <div class="card-total card-modern card-green">
                                                <div class="card-body d-flex align-items-center">

                                                    <div class="flex-grow-1">
                                                        <h6>Total de Registros</h6>
                                                        <h2 class="fw-bold" id="dashboardTotal"><?= $total ?></h2>
                                                    </div>

                                                    <div class="card-icon-bg">
                                                        <img src="<?= base_url('assets/img/registro.png') ?>">
                                                    </div>

                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-sm-4">
                                            <div class="card-total card-modern card-blue">
                                                <div class="card-body d-flex align-items-center">

                                                    <div class="flex-grow-1">
                                                        <h6>Registros Externos</h6>
                                                        <h2 class="fw-bold" id="totalRegistroGeneral">0</h2>
                                                    </div>

                                                    <div class="card-icon-bg">
                                                        <img src="<?= base_url('assets/img/registro.png') ?>">
                                                    </div>

                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-sm-4">
                                            <div class="card-total card-modern card-red">
                                                <div class="card-body d-flex align-items-center">

                                                    <div class="flex-grow-1">
                                                        <h6>Registros Comisaría</h6>
                                                        <h2 class="fw-bold" id="totalRegistroComisaria">0</h2>
                                                    </div>

                                                    <div class="card-icon-bg">
                                                        <img src="<?= base_url('assets/img/escudo-policial.png') ?>">
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-4 dashboard-grid">

                                        <div class="col-lg-8 g-4 dashboard-panel chart-panel chart-panel-large me-5">
                                            <h3>Registros por día</h3>

                                            <canvas class="mt-4" id="graficaDependencias"></canvas>
                                        </div>

                                        <div class="col-lg-3 g-4 dashboard-panel chart-panel me-5">
                                            <h3>Registros por sexo</h3>

                                            <canvas class="mt-4" id="graficaSexo"></canvas>
                                        </div>

                                        <div class="col-lg-8 g-5 dashboard-panel table-panel mt-5">
                                            <nav class="navbar-simple">
                                                <form class="search-simple" id="buscarRegistrosForm">

                                                    <input type="search" id="buscarRegistros" placeholder="Buscar..."
                                                        aria-label="Search">

                                                    <button type="submit">Buscar</button>

                                                </form>
                                            </nav>
                                            <div class="col-sm-12 g-5">
                                                <div class="tabla-scroll-wrapper">
                                                    <div class="tabla-scroll">

                                                        <table class="table table-striped table-hover">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Nombre completo</th>
                                                                    <th>Correo</th>
                                                                    <th>Tipo</th>
                                                                    <th>Área</th>
                                                                    <th>Dependencia</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php $i = 1;
                                                                foreach ($registros as $fila): ?>
                                                                    <tr class="registro-tabla">
                                                                        <th><?= $i++ ?></th>

                                                                        <td>
                                                                            <?= esc(mb_strtoupper($fila['nombre'] . ' ' . $fila['apellido_p'] . ' ' . $fila['apellido_m'], 'UTF-8')) ?>
                                                                        </td>

                                                                        <td><?= esc(mb_strtoupper($fila['correo'], 'UTF-8')) ?>
                                                                        </td>

                                                                        <td><?= esc(mb_strtoupper($fila['tipo_registro'], 'UTF-8')) ?>
                                                                        </td>

                                                                        <td><?= esc(mb_strtoupper($fila['area'] ?: 'NO APLICA', 'UTF-8')) ?>
                                                                        </td>

                                                                        <td><?= esc(mb_strtoupper($fila['dependencia'] ?: 'NO APLICA', 'UTF-8')) ?>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>

                                                            </tbody>
                                                        </table>
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
            </div>
        </section>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <script>
        window.dashboardData = <?= json_encode($dashboard) ?>;
        window.dependenciasCatalogo = <?= json_encode(array_map(
            fn($dependencia) => mb_strtoupper($dependencia['dependencia'], 'UTF-8'),
            $dependencias
        )) ?>;
    </script>

    <script src="<?= base_url('assets/JS/index.js') ?>"></script>
</body>

</html>
