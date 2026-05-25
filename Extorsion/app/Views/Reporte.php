<body>
    <main class="container-fluid">
        <section>
            <div>
                <div class="container-fluid p-0">
                    <div class="d-flex">
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
                                        < </span> Dashboard
                                </div>

                                <a href="/reporte/exportar" class="btn-exportar mt-4">
                                    <span class="arrow">
                                        < </span>
                                            Exportar
                                </a>

                                <div class="menu-item mt-4" id="menuFiltro">
                                    <span class="arrow">
                                        < </span> Filtro
                                </div>
                                <form class="filter-panel" id="dashboardFiltros">
                                    <div class="filter-title">Filtros</div>

                                    <label>
                                        Estado
                                        <select id="filtroEstado" aria-label="Estado">
                                            <option value="">Todos</option>
                                        </select>
                                    </label>

                                    <label>
                                        Municipio
                                        <select id="filtroMunicipio" aria-label="Municipio">
                                            <option value="">Todos</option>
                                        </select>
                                    </label>

                                    <label>
                                        Sector
                                        <select id="filtroSector" aria-label="Sector">
                                            <option value="">Todos</option>
                                        </select>
                                    </label>

                                    <label>
                                        Categoria
                                        <select id="filtroCategoria" aria-label="Categoria">
                                            <option value="">Todas</option>
                                        </select>
                                    </label>

                                    <button type="button" id="limpiarFiltros">Limpiar filtros</button>
                                </form>
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

                                    <div class="row g-3">

                                        <div class="col-sm-4">
                                            <div class="card-total card-modern card-green">
                                                <div class="card-body d-flex align-items-center">

                                                    <div class="flex-grow-1">
                                                        <h6>Total de registros</h6>
                                                        <h2 class="fw-bold" id="dashboardTotal"><?= $total ?></h2>
                                                    </div>

                                                    <div class="card-icon-bg">
                                                        <img src="<?= base_url('assets/img/registro.png') ?>">
                                                    </div>

                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-sm-4">
                                            <div class="card-comercio card-modern card-green-light"
                                                data-sector-card="Comercial">
                                                <div class="card-body d-flex align-items-center">

                                                    <div class="flex-grow-1">
                                                        <h6>Sector Comercial</h6>

                                                        <h2 class="fw-bold" id="totalSectorComercial">
                                                            <?php foreach ($sector as $fila): ?>
                                                            <?php if ($fila['sector'] == 'Comercial'): ?>
                                                            <?= esc($fila['total']) ?>
                                                            <?php endif; ?>
                                                            <?php endforeach; ?>
                                                        </h2>
                                                    </div>

                                                    <div class="card-icon-bg">
                                                        <img src="<?= base_url('assets/img/mercado.png') ?>">
                                                    </div>

                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-sm-4">
                                            <div class="card-servicio card-modern card-green-soft"
                                                data-sector-card="Servicio">
                                                <div class="card-body d-flex align-items-center">

                                                    <div class="flex-grow-1">
                                                        <h6>Sector Servicio</h6>

                                                        <h2 class="fw-bold" id="totalSectorServicio">
                                                            <?php foreach ($sector as $fila): ?>
                                                            <?php if ($fila['sector'] == 'Servicio'): ?>
                                                            <?= esc($fila['total']) ?>
                                                            <?php endif; ?>
                                                            <?php endforeach; ?>
                                                        </h2>
                                                    </div>

                                                    <div class="card-icon-bg">
                                                        <img src="<?= base_url('assets/img/bien.png') ?>">

                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-4 dashboard-grid">

                                        <div class="col-lg-8 g-4 dashboard-panel chart-panel chart-panel-large">
                                            <h3>Registros por día</h3>

                                            <canvas class="mt-4" id="graficaDependencias"></canvas>
                                        </div>

                                        <div class="col-lg-4 g-4 dashboard-panel chart-panel">
                                            <h3>Registros por sexo</h3>

                                            <canvas class="mt-4" id="graficaSexo"></canvas>
                                        </div>

                                        <div class="col-lg-8 g-5 dashboard-panel table-panel">
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
                                                                    <th>Municipio</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php $i = 1; foreach ($registros as $fila): ?>
                                                                <tr class="registro-tabla">
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
                                            </div>
                                        </div>
                                        <div class="col-lg-4 g-4 dashboard-side-stack">
                                            <div class="dashboard-panel chart-panel chart-panel-small">
                                                <h3>Registros por estado</h3>
                                                <canvas class="mt-4" id="graficaEstado"></canvas>
                                            </div>

                                            <div class="sector-info-card mt-4">
                                                <div class="card-body">
                                                    <h5 class="card-title" id="sectorInfoTitulo">Informacion del Sector
                                                    </h5>
                                                    <p class="card-text" id="sectorInfoTexto">
                                                        Seleccione una card de sector para ver su informacion.
                                                    </p>
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
    </script>

    <script src="<?= base_url('assets/JS/index.js') ?>"></script>
</body>

</html>