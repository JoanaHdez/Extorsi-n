

<h1>Reportes</h1>

<h3>Total de registros:</h3>

<p>
    <?= $total?>
</p>

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

<h3>Registros por municipio</h3>

<table border="1">
    <tr>
        <th>Municipio</th>
        <th>Total</th>
    </tr>

    <?php foreach ($municipio as $fila): ?>
        <tr>
            <td><?= esc($fila['municipio']) ?></td>
            <td><?= esc($fila['total']) ?></td>
        </tr>
    <?php endforeach; ?>    
</table>

<h3>Registros por sector</h3>   

<table border="1">
    <tr>
        <th>Sector</th>
        <th>Total</th>
    </tr>

    <?php foreach ($sector as $fila): ?>
        <tr>
            <td><?= esc($fila['sector']) ?></td>
            <td><?= esc($fila['total']) ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<h3>Registros por categoría</h3>    

<table border="1">
    <tr>
        <th>Categoría</th>
        <th>Total</th>
    </tr>

    <?php foreach ($categoria as $fila): ?>
        <tr>
            <td><?= esc($fila['categoria']) ?></td>
            <td><?= esc($fila['total']) ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<p>
    <a href="/reporte/exportar">Exportar a CSV</a>
    <button onclick="window.print()">Imprimir</button>
</p>