<nav class="navbar navbar-light bg-light">
    <div class="container-fluid">

        <form class="d-flex" method="get" action="<?= base_url('/listado') ?>">

            <input 
                class="form-control me-2" 
                type="search" 
                name="buscar"
                placeholder="Buscar nombre o correo"
            >

            <button class="btn btn-outline-success" type="submit">
                Buscar
            </button>

        </form>

    </div>
</nav>

<table border="1">
    <tr>
        <th>Nombre</th>
        <th>Correo</th>
        <th>Sexo</th>
        <th>Dependencia</th>
        <th>Estado</th>
        <th>Municipio</th>
        <th>Sector</th>
        <th>Categoría</th>
    </tr>

    <?php foreach ($registros as $registro): ?>
        <tr>
            <td>
                <?= esc($registro['nombre']) ?>
                <?= esc($registro['apellido_p']) ?>
                <?= esc($registro['apellido_m']) ?>
            </td>

            <td><?= esc($registro['correo']) ?></td>
            <td><?= esc($registro['sexo']) ?></td>
            <td><?= esc($registro['dependencia']) ?></td>
            <td><?= esc($registro['estado']) ?></td>
            <td><?= esc($registro['municipio']) ?></td>
            <td><?= esc($registro['sector']) ?></td>
            <td><?= esc($registro['categoria']) ?></td>
        </tr>
    <?php endforeach; ?>
</table>