document.getElementById('id_estado').addEventListener('change', function() {
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
});
