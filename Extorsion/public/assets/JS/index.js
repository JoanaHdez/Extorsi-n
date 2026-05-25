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
const categoriaSelect = document.getElementById('id_categoria');
const categoriaOtroContenedor = document.getElementById('categoria_otro_contenedor');
const categoriaOtroInput = document.getElementById('categoria_otro');

function actualizarCampoCategoriaOtro() {
    if (!categoriaSelect || !categoriaOtroContenedor || !categoriaOtroInput) {
        return;
    }

    const opcionSeleccionada = categoriaSelect.options[categoriaSelect.selectedIndex];
    const categoriaTexto = opcionSeleccionada ? opcionSeleccionada.textContent.trim().toLowerCase() : '';
    const esOtraCategoria = categoriaTexto === 'otros' || categoriaTexto === 'otro';

    categoriaOtroContenedor.style.display = esOtraCategoria ? '' : 'none';
    categoriaOtroInput.required = esOtraCategoria;

    if (!esOtraCategoria) {
        categoriaOtroInput.value = '';
    }
}

if (sector) {
    sector.addEventListener('change', function() {
        const sectorId = this.value;

        categoriaSelect.innerHTML = '<option value="">Cargando...</option>';
        actualizarCampoCategoriaOtro();

        if (!sectorId) {
            categoriaSelect.innerHTML = '<option value="">Seleccione un sector</option>';
            actualizarCampoCategoriaOtro();
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
            actualizarCampoCategoriaOtro();
        });
    });
}

if (categoriaSelect) {
    categoriaSelect.addEventListener('change', actualizarCampoCategoriaOtro);
    actualizarCampoCategoriaOtro();
}


document.addEventListener("DOMContentLoaded", function () {
    const dashboardData = Array.isArray(window.dashboardData) ? window.dashboardData : [];

    if (!dashboardData.length || typeof Chart === 'undefined') {
        return;
    }

    const filtroEstado = document.getElementById('filtroEstado');
    const filtroMunicipio = document.getElementById('filtroMunicipio');
    const filtroSector = document.getElementById('filtroSector');
    const filtroCategoria = document.getElementById('filtroCategoria');
    const limpiarFiltros = document.getElementById('limpiarFiltros');
    const menuFiltro = document.getElementById('menuFiltro');
    const dashboardFiltros = document.getElementById('dashboardFiltros');
    const buscarRegistrosForm = document.getElementById('buscarRegistrosForm');
    const buscarRegistros = document.getElementById('buscarRegistros');
    const filasRegistro = document.querySelectorAll('.registro-tabla');
    const dashboardTotal = document.getElementById('dashboardTotal');
    const totalSectorComercial = document.getElementById('totalSectorComercial');
    const totalSectorServicio = document.getElementById('totalSectorServicio');
    const sectorInfoTitulo = document.getElementById('sectorInfoTitulo');
    const sectorInfoTexto = document.getElementById('sectorInfoTexto');
    const sectorCards = document.querySelectorAll('[data-sector-card]');

    let sectorSeleccionado = '';
    let graficaDias = null;
    let graficaSexo = null;
    let graficaEstado = null;
    const colorAzul = '#00538E';
    const colorRojo = '#A40000';
    const colorDorado = '#B8893A';
    const colorVerde = '#16A34A';
    const chartGridColor = 'rgba(15, 23, 42, 0.08)';
    const chartTextColor = '#334155';

    const registros = dashboardData.map(r => ({
        ...r,
        estado: r.estado || '',
        municipio: r.municipio || '',
        sector: r.sector || '',
        categoria: r.categoria || '',
        sexo: r.sexo || '',
        fecha: r.fecha || (r.fecha_registro ? r.fecha_registro.substring(0, 10) : '')
    }));

    function opcionesUnicas(campo, datos = registros) {
        return [...new Set(datos.map(r => r[campo]).filter(Boolean))].sort();
    }

    function llenarSelect(select, opciones, etiqueta) {
        if (!select) {
            return;
        }

        const valorActual = select.value;
        select.innerHTML = `<option value="">${etiqueta}</option>`;

        opciones.forEach(opcion => {
            const option = document.createElement('option');
            option.value = opcion;
            option.textContent = opcion;
            select.appendChild(option);
        });

        if (opciones.includes(valorActual)) {
            select.value = valorActual;
        }
    }

    function agrupar(datos, campo) {
        return datos.reduce((acumulado, registro) => {
            const llave = registro[campo] || 'Sin dato';
            acumulado[llave] = (acumulado[llave] || 0) + 1;
            return acumulado;
        }, {});
    }

    function datosAgrupados(datos, campo) {
        const grupos = agrupar(datos, campo);
        return Object.keys(grupos).sort().map(llave => ({
            label: llave,
            total: grupos[llave]
        }));
    }

    function filtrarRegistros() {
        const estado = filtroEstado ? filtroEstado.value : '';
        const municipio = filtroMunicipio ? filtroMunicipio.value : '';
        const sector = filtroSector ? filtroSector.value : '';
        const categoria = filtroCategoria ? filtroCategoria.value : '';

        return registros.filter(registro => {
            return (!estado || registro.estado === estado)
                && (!municipio || registro.municipio === municipio)
                && (!sector || registro.sector === sector)
                && (!categoria || registro.categoria === categoria);
        });
    }

    function actualizarGrafica(chart, labels, valores) {
        if (!chart) {
            return;
        }

        chart.data.labels = labels;
        chart.data.datasets[0].data = valores;
        chart.update();
    }

    function opcionesBarras() {
        return {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#0B2E4A',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    padding: 12,
                    cornerRadius: 10
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: chartTextColor
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: chartGridColor
                    },
                    ticks: {
                        color: chartTextColor,
                        precision: 0
                    }
                }
            }
        };
    }

    function totalPorSector(datos, sector) {
        return datos.filter(registro => registro.sector === sector).length;
    }

    function actualizarInfoSector(datos) {
        if (!sectorInfoTitulo || !sectorInfoTexto) {
            return;
        }

        if (!sectorSeleccionado) {
            sectorInfoTitulo.textContent = 'Informacion del Sector';
            sectorInfoTexto.textContent = 'Seleccione una card de sector para ver su informacion.';
            return;
        }

        const total = totalPorSector(datos, sectorSeleccionado);
        const categorias = datos
            .filter(registro => registro.sector === sectorSeleccionado)
            .reduce((acumulado, registro) => {
                const categoria = registro.categoria || 'Sin categoria';
                acumulado[categoria] = (acumulado[categoria] || 0) + 1;
                return acumulado;
            }, {});

        const categoriaPrincipal = Object.keys(categorias)
            .sort((a, b) => categorias[b] - categorias[a])[0] || 'Sin datos';

        sectorInfoTitulo.textContent = `Informacion del Sector ${sectorSeleccionado}`;
        sectorInfoTexto.textContent = `Sector: ${sectorSeleccionado}. Total: ${total}. Categoria con mas registros: ${categoriaPrincipal}.`;
    }

    function actualizarDashboard() {
        const datosFiltrados = filtrarRegistros();
        const dias = datosAgrupados(datosFiltrados, 'fecha');
        const sexo = datosAgrupados(datosFiltrados, 'sexo');
        const estados = datosAgrupados(datosFiltrados, 'estado');

        if (dashboardTotal) {
            dashboardTotal.textContent = datosFiltrados.length;
        }

        if (totalSectorComercial) {
            totalSectorComercial.textContent = totalPorSector(datosFiltrados, 'Comercial');
        }

        if (totalSectorServicio) {
            totalSectorServicio.textContent = totalPorSector(datosFiltrados, 'Servicio');
        }

        actualizarGrafica(graficaDias, dias.map(d => d.label), dias.map(d => d.total));
        actualizarGrafica(graficaSexo, sexo.map(s => s.label), sexo.map(s => s.total));
        actualizarGrafica(graficaEstado, estados.map(e => e.label), estados.map(e => e.total));
        actualizarInfoSector(datosFiltrados);
    }

    function actualizarMunicipios() {
        const estado = filtroEstado ? filtroEstado.value : '';
        const datos = estado ? registros.filter(registro => registro.estado === estado) : registros;
        llenarSelect(filtroMunicipio, opcionesUnicas('municipio', datos), 'Municipio');
    }

    function actualizarCategorias() {
        const sector = filtroSector ? filtroSector.value : '';
        const datos = sector ? registros.filter(registro => registro.sector === sector) : registros;
        llenarSelect(filtroCategoria, opcionesUnicas('categoria', datos), 'Categoria');
    }

    function filtrarTablaRegistros() {
        if (!buscarRegistros || !filasRegistro.length) {
            return;
        }

        const busqueda = buscarRegistros.value.trim().toLowerCase();

        filasRegistro.forEach(fila => {
            const texto = fila.textContent.toLowerCase();
            fila.style.display = !busqueda || texto.includes(busqueda) ? '' : 'none';
        });
    }

    llenarSelect(filtroEstado, opcionesUnicas('estado'), 'Estado');
    llenarSelect(filtroMunicipio, opcionesUnicas('municipio'), 'Municipio');
    llenarSelect(filtroSector, opcionesUnicas('sector'), 'Sector');
    llenarSelect(filtroCategoria, opcionesUnicas('categoria'), 'Categoria');

    const canvasDias = document.getElementById('graficaDependencias');
    const canvasSexo = document.getElementById('graficaSexo');
    const canvasEstado = document.getElementById('graficaEstado');

    if (canvasDias) {
        graficaDias = new Chart(canvasDias.getContext('2d'), {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Registros por dia',
                    data: [],
                    backgroundColor: colorAzul,
                    hoverBackgroundColor: colorDorado,
                    borderRadius: 10,
                    borderSkipped: false
                }]
            },
            options: opcionesBarras()
        });
    }

    if (canvasSexo) {
        graficaSexo = new Chart(canvasSexo.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: [],
                datasets: [{
                    label: 'Registros por sexo',
                    data: [],
                    backgroundColor: [colorAzul, colorRojo, colorDorado, colorVerde],
                    borderColor: '#ffffff',
                    borderWidth: 4,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: chartTextColor,
                            usePointStyle: true,
                            padding: 18
                        }
                    },
                    tooltip: {
                        backgroundColor: '#0B2E4A',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        padding: 12,
                        cornerRadius: 10
                    }
                }
            }
        });
    }

    if (canvasEstado) {
        graficaEstado = new Chart(canvasEstado.getContext('2d'), {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Registros por estado',
                    data: [],
                    backgroundColor: [colorVerde, colorRojo, colorDorado, colorAzul],
                    borderRadius: 10,
                    borderSkipped: false
                }]
            },
            options: opcionesBarras()
        });
    }

    if (filtroEstado) {
        filtroEstado.addEventListener('change', function () {
            actualizarMunicipios();
            actualizarDashboard();
        });
    }

    if (filtroSector) {
        filtroSector.addEventListener('change', function () {
            actualizarCategorias();
            actualizarDashboard();
        });
    }

    [filtroMunicipio, filtroCategoria].forEach(select => {
        if (select) {
            select.addEventListener('change', actualizarDashboard);
        }
    });

    if (limpiarFiltros) {
        limpiarFiltros.addEventListener('click', function () {
            if (filtroEstado) filtroEstado.value = '';
            actualizarMunicipios();
            if (filtroMunicipio) filtroMunicipio.value = '';
            if (filtroSector) filtroSector.value = '';
            actualizarCategorias();
            if (filtroCategoria) filtroCategoria.value = '';
            sectorSeleccionado = '';
            sectorCards.forEach(card => card.classList.remove('active'));
            actualizarDashboard();
        });
    }

    if (menuFiltro) {
        menuFiltro.addEventListener('click', function () {
            if (dashboardFiltros) {
                dashboardFiltros.classList.toggle('active');
                menuFiltro.classList.toggle('active', dashboardFiltros.classList.contains('active'));
            }
        });
    }

    if (buscarRegistrosForm) {
        buscarRegistrosForm.addEventListener('submit', function (event) {
            event.preventDefault();
            filtrarTablaRegistros();
        });
    }

    if (buscarRegistros) {
        buscarRegistros.addEventListener('input', filtrarTablaRegistros);
    }

    sectorCards.forEach(card => {
        card.addEventListener('click', function () {
            sectorSeleccionado = this.dataset.sectorCard || '';
            sectorCards.forEach(item => item.classList.remove('active'));
            this.classList.add('active');
            actualizarDashboard();
        });
    });

    actualizarDashboard();
});

