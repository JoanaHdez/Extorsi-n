/* document.getElementById('id_estado').addEventListener('change', function() {
    const estadoId = this.value;
    const municipioSelect = document.getElementById('id_municipio');
    
    municipioSelect.innerHTML = '<option value="">Cargando...</option>';

    if (!estadoId) {
        municipioSelect.innerHTML = '<option value="">Seleccione un estado</option>';
        return;
    }
    fetch(`/registro/municipios/${estadoId}`)
    .then(response => response.json())
    .then(data => {
        municipioSelect.innerHTML = '<option value="">Seleccione un municipio</option>';
        data.forEach(municipio => {
            const option = document.createElement('option');
            option.value = municipio.id_municipio;
            option.textContent = municipio.municipio;
            municipioSelect.appendChild(option);
        });
    });
});

document.getElementById('id_sector').addEventListener('change', function() {
    const sectorId = this.value;
    const categoriaSelect = document.getElementById('id_categoria');

    categoriaSelect.innerHTML = '<option value="">Cargando...</option>';

    if (!sectorId) {
        categoriaSelect.innerHTML = '<option value="">Seleccione un sector</option>';
        return;
    }
    fetch(`/registro/categorias/${sectorId}`)
    .then(response => response.json())
    .then(data => {
        categoriaSelect.innerHTML = '<option value="">Seleccione una categoría</option>';
        data.forEach(categoria => {
            const option = document.createElement('option');
            option.value = categoria.id_categoria;
            option.textContent = categoria.categoria;
            categoriaSelect.appendChild(option);
        });
    });
}); */


const estado = document.getElementById('id_estado');
if (estado) {
    estado.addEventListener('change', function() {
        const estadoId = this.value;
        const municipioSelect = document.getElementById('id_municipio');

        municipioSelect.innerHTML = '<option value="">Cargando...</option>';

        if (!estadoId) {
            municipioSelect.innerHTML = '<option value="">Seleccione un estado</option>';
            return;
        }

        fetch(`/registro/municipios/${estadoId}`)
        .then(r => r.json())
        .then(data => {
            municipioSelect.innerHTML = '<option value="">Seleccione un municipio</option>';
            data.forEach(m => {
                const option = document.createElement('option');
                option.value = m.id_municipio;
                option.textContent = m.municipio;
                municipioSelect.appendChild(option);
            });
        });
    });
}

const sector = document.getElementById('id_sector');

if (sector) {
    sector.addEventListener('change', function() {
        const sectorId = this.value;
        const categoriaSelect = document.getElementById('id_categoria');

        categoriaSelect.innerHTML = '<option value="">Cargando...</option>';

        if (!sectorId) {
            categoriaSelect.innerHTML = '<option value="">Seleccione un sector</option>';
            return;
        }

        fetch(`/registro/categorias/${sectorId}`)
        .then(r => r.json())
        .then(data => {
            categoriaSelect.innerHTML = '<option value="">Seleccione una categoría</option>';
            data.forEach(c => {
                const option = document.createElement('option');
                option.value = c.id_categoria;
                option.textContent = c.categoria;
                categoriaSelect.appendChild(option);
            });
        });
    });
}


document.addEventListener("DOMContentLoaded", function () {
console.log("CHART INICIADO");

/* DEPENDENCIAS */
    const data = window.dependenciasData;

    const labels = data.map(d => d.dependencia);
    const valores = data.map(d => Number(d.total));

    // 👇 AQUÍ VA LA LÍNEA
    const ctx = document.getElementById('graficaDependencias').getContext('2d');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Registros por dependencia',
                data: valores,
                backgroundColor: ['#00538E', '#0B2E4A', '#3B82F6', '#6B7280'],
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    /* SEXO */

    const sexo = window.sexoData;

const labelsSexo = sexo.map(s => s.sexo);
const valoresSexo = sexo.map(s => Number(s.total));

const ctxSexo = document.getElementById('graficaSexo').getContext('2d');

new Chart(ctxSexo, {
    type: 'doughnut',
    data: {
        labels: labelsSexo,
        datasets: [{
            label: 'Registros por sexo',
            data: valoresSexo,
            backgroundColor: [
                '#00538E',  // Masculino → azul institucional
                '#A40000'   // Femenino → rojo institucional
            ],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        cutout: '65%', // 👈 esto lo hace "rosquilla"
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

/* ESTADO */
const estado = window.estadoData;

const labelsEstado = estado.map(e => e.estado);
const valoresEstado = estado.map(e => Number(e.total));

const ctxEstado = document.getElementById('graficaEstado').getContext('2d');

new Chart(ctxEstado, {
    type: 'bar',
    data: {
        labels: labelsEstado,
        datasets: [{
            label: 'Registros por estado',
            data: valoresEstado,
            backgroundColor: ['#22C55E', '#DC2626', '#F59E0B'],
            borderRadius: 6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

});

