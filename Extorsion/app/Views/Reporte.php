<body>
    <main class="container-fluid">
        <section>
            <div>
                <div class="container-fluid p-0">
                    <div class="d-flex">
                        <!-- MENÚ -->
                        <div class="cuadro shadow rounded-5 p-4">

                            <!-- Logo -->
                            <h4 class="fw-bold mb-5">Logos</h4>

                            <!-- Opciones -->
                            <div class="d-flex flex-column gap-3">

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

                            </div>

                        </div>

                        <div class="contenido flex-grow-1">

                            <div class="d-flex flex-column align-items-center">

                                <h1 class="titulo fw-bold mt-5">
                                    Pláticas de Medidas Preventivas en Casos de Extorsión
                                </h1>

                            </div>

                            <div class="container px-5 py-4 mt-2">
                                <div class="row g-4">
                                    <h3>Dashboard</h3>

                                    <div class="row">
                                        <div class="col-sm-4">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h5 class="card-title">Total de registros</h5>
                                                    <p class="card-text"><?= $total?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
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
                                        <div class="col-sm-4">
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
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-4">
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
                                        </div>
                                        <div class="col-sm-4">
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
                                        </div>
                                        <div class="col-sm-4">
                                            <nav class="navbar navbar-light bg-light">
                                                <div class="container-fluid">
                                                    <form class="d-flex">
                                                        <input class="form-control me-2" type="search"
                                                            placeholder="Search" aria-label="Search">
                                                        <button class="btn btn-outline-success"
                                                            type="submit">Search</button>
                                                    </form>
                                                </div>
                                            </nav>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-4">
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
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="card" style="width: 18rem;">
                                                <div class="card-body">
                                                    <h5 class="card-title">Informacion del Sector Comercial</h5>
                                                    <p class="card-text">With supporting text below as a natural
                                                        lead-in
                                                        to additional content.</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <table class="table table-striped table-hover">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">#</th>
                                                        <th scope="col">First</th>
                                                        <th scope="col">Last</th>
                                                        <th scope="col">Handle</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <th scope="row">1</th>
                                                        <td>Mark</td>
                                                        <td>Otto</td>
                                                        <td>@mdo</td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row">2</th>
                                                        <td>Jacob</td>
                                                        <td>Thornton</td>
                                                        <td>@fat</td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row">3</th>
                                                        <td colspan="2">Larry the Bird</td>
                                                        <td>@twitter</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <p>
                                    <a href="/reporte/exportar">Exportar a CSV</a>
                                    <button onclick="window.print()">Imprimir</button>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>

</html>