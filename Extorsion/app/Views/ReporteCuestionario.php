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
                                <a href="<?= base_url('reporte') ?>" class="btn-exportar mt-4">
                                    <span class="arrow">
                                    </span>
                                    Dashboard
                                </a>
                                <a href="<?= base_url('index.php/reporte/exportar') ?>" class="btn-exportar mt-4">
                                    <span class="arrow">
                                    </span>
                                    Exportar
                                </a>
                                <a href="<?= base_url('reporte/cuestionario/exportar-comentarios' . (! empty($cuestionarioFechaFiltro) ? '?dia=' . rawurlencode($cuestionarioFechaFiltro) : '')) ?>" class="btn-exportar mt-4">
                                    <span class="arrow">
                                    </span>
                                    Exportar comentarios
                                </a>
                                <div class="menu-item mt-4">
                                    <span class="arrow">
                                    </span> Cuestionario
                                </div>
                                <div class="menu-item mt-4" id="menuFiltro">
                                    <span class="arrow">
                                    </span> Filtro
                                </div>
                                <form class="filter-panel" id="dashboardFiltros" method="get"
                                    action="<?= base_url('reporte/cuestionario') ?>">
                                    <div class="filter-title">Filtro</div>
                                    <label>
                                        Dia
                                        <select name="dia" aria-label="Dia" onchange="this.form.submit()">
                                            <option value="">Todos los dias</option>
                                            <?php foreach (($cuestionarioDias ?? []) as $dia): ?>
                                            <option value="<?= esc($dia['fecha']) ?>"
                                                <?= ($cuestionarioFechaFiltro ?? '') === $dia['fecha'] ? 'selected' : '' ?>>
                                                <?= esc($dia['fecha']) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </label>
                                    <button type="submit">Aplicar filtro</button>
                                    <button type="button"
                                        onclick="window.location.href='<?= base_url('reporte/cuestionario') ?>'">Limpiar
                                        filtro</button>
                                </form>
                            </div>
                            <a href="<?= base_url('registro') ?>" class="menu-salir text-decoration-none">
                                <img src="<?= base_url('assets/img/cerrar-sesion.png') ?>" class="logo-salir"
                                    alt="Logo salir">
                                <h4 class="mt-2" style="color: white;">Salir</h4>
                            </a>
                        </div>

                        <div class="contenido flex-grow-1">
                            <div class="d-flex flex-column align-items-center">
                                <h1 class="titulo fw-bold mt-3">
                                    Platicas de Medidas Preventivas en Casos de Extorsion
                                </h1>
                            </div>

                            <div class="container px-0 py-4 mt-3">
                                <div class="row g-5">
                                    <div>
                                        <h3 class="titulo fw-bold mt-4 mb-2">Reporte de cuestionario</h3>
                                    </div>

                                    <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-5 g-3">
                                        <div class="col">
                                            <div class="card-total card-modern questionnaire-card">
                                                <div class="card-body">
                                                    <h6>Total de cuestionarios contestados</h6>
                                                    <h2 class="fw-bold"><?= esc((string) ($cuestionarioTotal ?? 0)) ?>
                                                    </h2>
                                                    <?php if (! empty($cuestionarioFechaFiltro)): ?>
                                                    <p class="mb-0" style="font-weight:700;">Dia
                                                        <?= esc($cuestionarioFechaFiltro) ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <?php if (empty($cuestionarioDias ?? [])): ?>
                                        <div class="col">
                                            <div class="card-total card-modern questionnaire-card card-blue">
                                                <div class="card-body">
                                                    <h6>Total por dia</h6>
                                                    <h2 class="fw-bold">0</h2>
                                                    <p class="mb-0" style="font-weight:700;">Cuestionarios</p>
                                                </div>
                                            </div>
                                        </div>
                                        <?php else: ?>
                                        <?php foreach (($cuestionarioDias ?? []) as $index => $dia): ?>
                                        <div class="col">
                                            <div
                                                class="card-total card-modern questionnaire-card <?= $index % 2 === 0 ? 'card-blue' : 'card-red' ?> <?= ($cuestionarioFechaFiltro ?? '') === $dia['fecha'] ? 'questionnaire-card-active' : '' ?>">
                                                <div class="card-body">
                                                    <h6><?= esc($dia['fecha']) ?></h6>
                                                    <h2 class="fw-bold"><?= esc((string) $dia['total']) ?></h2>
                                                    <p class="mb-0" style="font-weight:700;">Cuestionarios</p>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>

                                    <div class="dashboard-panel mb-4" style="padding: 18px;">
                                        <h3>Escala</h3>
                                        <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-5 g-3 mt-2">
                                            <?php
                                            $escala = [
                                                ['numero' => '1', 'texto' => 'Muy malo / totalmente en desacuerdo', 'clase' => 'card-red'],
                                                ['numero' => '2', 'texto' => 'Malo / desacuerdo', 'clase' => 'card-red'],
                                                ['numero' => '3', 'texto' => 'Regular', 'clase' => 'card-total'],
                                                ['numero' => '4', 'texto' => 'Bueno / de acuerdo', 'clase' => 'card-blue'],
                                                ['numero' => '5', 'texto' => 'Excelente / totalmente de acuerdo', 'clase' => 'card-blue'],
                                            ];
                                            ?>
                                            <?php foreach ($escala as $item): ?>
                                            <div class="col">
                                                <div class="card-modern scale-card <?= esc($item['clase']) ?>">
                                                    <div class="card-body">
                                                        <h2 class="fw-bold mb-1"><?= esc($item['numero']) ?></h2>
                                                        <p class="mb-0" style="font-weight:700; line-height:1.25;">
                                                            <?= esc($item['texto']) ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>

                                    <div class="dashboard-panel mb-4" style="padding: 22px;">
                                        <h3>Resultados por pregunta</h3>
                                        <div class="table-responsive cuestionario-tabla-scroll mt-3">
                                            <table class="table table-striped table-hover align-middle">
                                                <thead>
                                                    <tr>
                                                        <th>Pregunta</th>
                                                        <th class="text-center">1</th>
                                                        <th class="text-center">2</th>
                                                        <th class="text-center">3</th>
                                                        <th class="text-center">4</th>
                                                        <th class="text-center">5</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach (($cuestionarioResumen ?? []) as $pregunta): ?>
                                                    <?php if (($pregunta['tipo'] ?? '') === 'textarea') {
                                                            continue;
                                                        } ?>
                                                    <tr>
                                                        <td><?= esc($pregunta['texto']) ?></td>
                                                        <?php for ($opcion = 1; $opcion <= 5; $opcion++): ?>
                                                        <td class="text-center fw-bold">
                                                            <?= esc((string) ($pregunta['conteos'][(string) $opcion] ?? 0)) ?>
                                                        </td>
                                                        <?php endfor; ?>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="dashboard-panel" style="padding: 22px;">
                                        <h3>Comentarios adicionales</h3>
                                        <div class="table-responsive cuestionario-tabla-scroll mt-3">
                                            <table class="table table-striped table-hover align-middle">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Comentario</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $comentarios = [];
                                                    foreach (($cuestionarioResumen ?? []) as $pregunta) {
                                                        if (($pregunta['tipo'] ?? '') === 'textarea') {
                                                            $comentarios = $pregunta['respuestas_abiertas'] ?? [];
                                                            break;
                                                        }
                                                    }
                                                    ?>

                                                    <?php if (empty($comentarios)): ?>
                                                    <tr>
                                                        <td colspan="2">Sin comentarios registrados.</td>
                                                    </tr>
                                                    <?php else: ?>
                                                    <?php foreach ($comentarios as $index => $comentario): ?>
                                                    <tr>
                                                        <td><?= $index + 1 ?></td>
                                                        <td><?= esc($comentario) ?></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                    <?php endif; ?>
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
        </section>
    </main>

    <script src="<?= base_url('assets/JS/index.js') ?>"></script>
</body>

</html>
