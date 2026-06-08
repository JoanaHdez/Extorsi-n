document.addEventListener("DOMContentLoaded", function () {
  if (!window.mostrarModalComisaria) {
    return; // 👈 ya se registró, no mostrar nada
  }

  const modalElement = document.getElementById("modalComisaria");

  if (modalElement) {
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
  }
});

function mostrarToast(titulo, mensaje) {
  const toastEl = document.getElementById("toastGlobal");

  document.getElementById("toastTitulo").textContent = titulo;
  document.getElementById("toastMensaje").textContent = mensaje;

  const toast = new bootstrap.Toast(toastEl, {
    delay: 3000,
    autohide: true,
  });

  toast.show();
}

let nominaEncontrada = null;

const btnNo = document.getElementById("btnComisariaNo");

if (btnNo) {
  btnNo.addEventListener("click", function () {
    const modalElement = document.getElementById("modalComisaria");

    const modal = bootstrap.Modal.getInstance(modalElement);

    modal.hide();
  });
}

const btnSi = document.getElementById("btnComisariaSi");

if (btnSi) {
  btnSi.addEventListener("click", function () {
    const modalComisaria = bootstrap.Modal.getInstance(
      document.getElementById("modalComisaria"),
    );

    modalComisaria.hide();

    const modalNomina = new bootstrap.Modal(
      document.getElementById("modalNomina"),
    );

    modalNomina.show();
  });
}

const btnBuscarNomina = document.getElementById("btnBuscarNomina");

if (btnBuscarNomina) {
  btnBuscarNomina.addEventListener("click", function () {
    const nomina = document.getElementById("nominaBusqueda").value.trim();

    const mensaje = document.getElementById("mensajeNomina");

    const buscarNominaUrl = window.registroBuscarNominaUrl || "./registro/buscar-nomina";

    fetch(`${buscarNominaUrl}/${nomina}`)
      .then((response) => response.json())
      .then((resultado) => {
        console.log(resultado);

        if (!resultado.success) {
          mensaje.classList.remove("text-success");
          mensaje.classList.add("text-danger");

          mensaje.textContent = "No se encontró la nómina";

          return;
        }

        const empleado = resultado.data;

        nominaEncontrada = empleado.nomina;

        console.log("Nomina guardada:", nominaEncontrada);

        // Llenar formulario principal oculto
        document.getElementById("modalNombre").value = empleado.nombre.trim();

        document.getElementById("modalApellidoP").value =
          empleado.apellido_p.trim();

        document.getElementById("modalApellidoM").value =
          empleado.apellido_m.trim();

        // Llenar modal de verificación
        document.getElementById("modalNombre").value = empleado.nombre.trim();

        document.getElementById("modalApellidoP").value =
          empleado.apellido_p.trim();

        document.getElementById("modalApellidoM").value =
          empleado.apellido_m.trim();

        document.getElementById("modalArea").value = empleado.area ?? "";

        document.getElementById("modalFuncion").value = empleado.funcion ?? "";

        document.getElementById("modalSexo").value = empleado.sexo ?? "";

        mensaje.classList.remove("text-danger");
        mensaje.classList.add("text-success");

        mensaje.textContent = "Nómina encontrada";

        const modalNomina = bootstrap.Modal.getInstance(
          document.getElementById("modalNomina"),
        );

        modalNomina.hide();

        // Esperar a que cierre el modal actual
        setTimeout(() => {
          const modalDatos = new bootstrap.Modal(
            document.getElementById("modalDatosComisaria"),
          );

          modalDatos.show();
        }, 300);
      })
      .catch((error) => {
        console.error(error);

        mensaje.classList.remove("text-success");
        mensaje.classList.add("text-danger");

        mensaje.textContent = "Error al consultar la nómina";
      });
  });
}

const btnConfirmar = document.getElementById("btnConfirmarComisaria");

if (btnConfirmar) {
  btnConfirmar.addEventListener("click", function () {
    const correo = document.getElementById("modalCorreo").value.trim();
    if (!correo) {
  alert("Debe completar el correo");
  return;
}

    // CSRF primero
    const csrfName = document.querySelector(
      'input[name="csrf_test_name"]',
    ).name;
    const csrfValue = document.querySelector(
      'input[name="csrf_test_name"]',
    ).value;

    // FormData solo UNA vez
    const formData = new FormData();

    formData.append("nomina", nominaEncontrada);
    formData.append("correo", correo);

    // CSRF obligatorio
    formData.append(csrfName, csrfValue);

    fetch(window.registroGuardarPersonalUrl || "./registro/guardar-personal", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((resultado) => {
        console.log(resultado);

        if (!resultado.success) {
          mostrarToast("Error", resultado.message);
          return;
        }

        const modal = bootstrap.Modal.getInstance(
          document.getElementById("modalDatosComisaria"),
        );

        modal.hide();

        window.location.href = window.registroExitoUrl || "./registro/exito";
      })
      .catch((error) => {
        console.error(error);
        mostrarToast("Error", "No se pudo guardar el registro");
      });
  });
}

const dependenciaSelect = document.getElementById("id_dependencia");
const dependenciaOtroGrupo = document.getElementById("dependenciaOtroGrupo");
const dependenciaOtro = document.getElementById("dependencia_otro");

function actualizarDependenciaOtro() {
  if (!dependenciaSelect || !dependenciaOtroGrupo || !dependenciaOtro) {
    return;
  }

  const opcion = dependenciaSelect.selectedOptions[0];
  const esOtro = opcion && opcion.dataset.dependencia === "otro";

  dependenciaOtroGrupo.classList.toggle("d-none", !esOtro);
  dependenciaOtro.required = Boolean(esOtro);

  if (!esOtro) {
    dependenciaOtro.value = "";
  }
}

if (dependenciaSelect) {
  dependenciaSelect.addEventListener("change", actualizarDependenciaOtro);
  actualizarDependenciaOtro();
}

document.addEventListener("DOMContentLoaded", function () {
  const dashboardData = Array.isArray(window.dashboardData)
    ? window.dashboardData
    : [];
  const dependenciasCatalogo = Array.isArray(window.dependenciasCatalogo)
    ? window.dependenciasCatalogo
    : [];

  if (!dashboardData.length || typeof Chart === "undefined") {
    return;
  }

  const filtroTipo = document.getElementById("filtroTipo");
  const filtroArea = document.getElementById("filtroArea");
  const filtroDependencia = document.getElementById("filtroDependencia");
  const limpiarFiltros = document.getElementById("limpiarFiltros");
  const menuFiltro = document.getElementById("menuFiltro");
  const dashboardFiltros = document.getElementById("dashboardFiltros");
  const buscarRegistrosForm = document.getElementById("buscarRegistrosForm");
  const buscarRegistros = document.getElementById("buscarRegistros");
  const filasRegistro = document.querySelectorAll(".registro-tabla");
  const dashboardTotal = document.getElementById("dashboardTotal");
  const totalRegistroGeneral = document.getElementById("totalRegistroGeneral");
  const totalRegistroComisaria = document.getElementById(
    "totalRegistroComisaria",
  );

  let graficaDias = null;
  let graficaSexo = null;
  const colorAzul = "#00538E";
  const colorRojo = "#A40000";
  const colorDorado = "#B8893A";
  const colorVerde = "#16A34A";
  const chartGridColor = "rgba(15, 23, 42, 0.08)";
  const chartTextColor = "#334155";

const registros = dashboardData.map((r) => ({
  ...r,
  sexo: r.sexo || "",
  tipo_registro: r.tipo_registro || "",
  area: r.area || "",
  funcion: r.funcion || "",
  dependencia: r.dependencia || "",
  fecha:
    r.fecha || (r.fecha_registro ? r.fecha_registro.substring(0, 10) : ""),
}));

  function opcionesUnicas(campo, datos = registros) {
    const unicas = [...new Set(datos.map((r) => r[campo]).filter(Boolean))];

    return unicas.sort((a, b) => {
      const aLower = a.toLowerCase();
      const bLower = b.toLowerCase();

      return aLower.localeCompare(bLower, "es");
    });
  }

function llenarSelect(select, opciones) {
  if (!select) return;

  const valorActual = select.value;

  // SOLO placeholder (no forma parte del catálogo)
  select.innerHTML = '<option value="" disabled selected hidden>Seleccionar</option>';

  opciones.forEach((opcion) => {
    const option = document.createElement("option");
    option.value = opcion;
    option.textContent = opcion;
    select.appendChild(option);
  });

  // restaurar valor si existe
  if (opciones.includes(valorActual)) {
    select.value = valorActual;
  }
}

  function agrupar(datos, campo) {
    return datos.reduce((acumulado, registro) => {
      const llave = registro[campo] || "Sin dato";
      acumulado[llave] = (acumulado[llave] || 0) + 1;
      return acumulado;
    }, {});
  }

  function datosAgrupados(datos, campo) {
    const grupos = agrupar(datos, campo);
    return Object.keys(grupos)
      .sort()
      .map((llave) => ({
        label: llave,
        total: grupos[llave],
      }));
  }

  function filtrarRegistros() {
    const tipo = filtroTipo ? filtroTipo.value : "";
    const area = filtroArea ? filtroArea.value : "";
    const dependencia = filtroDependencia ? filtroDependencia.value : "";

    return registros.filter((registro) => {
      return (
        (!tipo || registro.tipo_registro === tipo) &&
        (!area || registro.area === area) &&
        (!dependencia || registro.dependencia === dependencia)
      );
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
          display: false,
        },
        tooltip: {
          backgroundColor: "#0B2E4A",
          titleColor: "#ffffff",
          bodyColor: "#ffffff",
          padding: 12,
          cornerRadius: 10,
        },
      },
      scales: {
        x: {
          grid: {
            display: false,
          },
          ticks: {
            color: chartTextColor,
          },
        },
        y: {
          beginAtZero: true,
          grid: {
            color: chartGridColor,
          },
          ticks: {
            color: chartTextColor,
            precision: 0,
          },
        },
      },
    };
  }

  function actualizarDashboard() {
    const datosFiltrados = filtrarRegistros();
    const dias = datosAgrupados(datosFiltrados, "fecha");
    const sexo = datosAgrupados(datosFiltrados, "sexo");

    if (dashboardTotal) {
      dashboardTotal.textContent = datosFiltrados.length;
    }

    if (totalRegistroGeneral) {
      totalRegistroGeneral.textContent = datosFiltrados.filter(
        (registro) => registro.tipo_registro === "Externo",
      ).length;
    }

    if (totalRegistroComisaria) {
      totalRegistroComisaria.textContent = datosFiltrados.filter(
        (registro) => registro.tipo_registro === "Comisaria",
      ).length;
    }

    actualizarGrafica(
      graficaDias,
      dias.map((d) => d.label),
      dias.map((d) => d.total),
    );
    actualizarGrafica(
      graficaSexo,
      sexo.map((s) => s.label),
      sexo.map((s) => s.total),
    );
  }



  function actualizarAreas() {
    const tipo = filtroTipo ? filtroTipo.value : "";
    const datos = tipo
      ? registros.filter((registro) => registro.tipo_registro === tipo)
      : registros;
    llenarSelect(filtroArea, opcionesUnicas("area", datos));
  }

  function filtrarTablaRegistros() {
    if (!buscarRegistros || !filasRegistro.length) {
      return;
    }

    const busqueda = buscarRegistros.value.trim().toLowerCase();

    filasRegistro.forEach((fila) => {
      const texto = fila.textContent.toLowerCase();
      fila.style.display = !busqueda || texto.includes(busqueda) ? "" : "none";
    });
  }

  llenarSelect(filtroTipo, opcionesUnicas("tipo_registro"));
  llenarSelect(filtroArea, opcionesUnicas("area"));
  llenarSelect(filtroDependencia, dependenciasCatalogo);

  const canvasDias = document.getElementById("graficaDependencias");
  const canvasSexo = document.getElementById("graficaSexo");

  if (canvasDias) {
    graficaDias = new Chart(canvasDias.getContext("2d"), {
      type: "bar",
      data: {
        labels: [],
        datasets: [
          {
            label: "Registros por dia",
            data: [],
            backgroundColor: colorAzul,
            hoverBackgroundColor: colorDorado,
            borderRadius: 10,
            borderSkipped: false,
          },
        ],
      },
      options: opcionesBarras(),
    });
  }

  if (canvasSexo) {
    graficaSexo = new Chart(canvasSexo.getContext("2d"), {
      type: "doughnut",
      data: {
        labels: [],
        datasets: [
          {
            label: "Registros por sexo",
            data: [],
            backgroundColor: [colorAzul, colorRojo, colorDorado, colorVerde],
            borderColor: "#ffffff",
            borderWidth: 4,
            hoverOffset: 8,
          },
        ],
      },
      options: {
        responsive: true,
        cutout: "65%",
        plugins: {
          legend: {
            position: "bottom",
            labels: {
              color: chartTextColor,
              usePointStyle: true,
              padding: 18,
            },
          },
          tooltip: {
            backgroundColor: "#0B2E4A",
            titleColor: "#ffffff",
            bodyColor: "#ffffff",
            padding: 12,
            cornerRadius: 10,
          },
        },
      },
    });
  }

  if (filtroTipo) {
    filtroTipo.addEventListener("change", function () {
      actualizarAreas();
      actualizarDashboard();
    });
  }

[filtroArea, filtroDependencia].forEach((select) => {
  if (select) {
    select.addEventListener("change", actualizarDashboard);
  }
});

  if (limpiarFiltros) {
    limpiarFiltros.addEventListener("click", function () {
      if (filtroTipo) filtroTipo.value = "";
      actualizarAreas();
      if (filtroArea) filtroArea.value = "";
      if (filtroDependencia) filtroDependencia.value = "";
      actualizarDashboard();
    });
  }

  if (menuFiltro) {
    menuFiltro.addEventListener("click", function () {
      if (dashboardFiltros) {
        dashboardFiltros.classList.toggle("active");
        menuFiltro.classList.toggle(
          "active",
          dashboardFiltros.classList.contains("active"),
        );
      }
    });
  }

  if (buscarRegistrosForm) {
    buscarRegistrosForm.addEventListener("submit", function (event) {
      event.preventDefault();
      filtrarTablaRegistros();
    });
  }

  if (buscarRegistros) {
    buscarRegistros.addEventListener("input", filtrarTablaRegistros);
  }

  actualizarDashboard();
});

const menuToggle = document.getElementById("menuToggle");
const cuadro = document.querySelector(".cuadro");
const menuOverlay = document.getElementById("menuOverlay");

if (menuToggle && cuadro && menuOverlay) {
  menuToggle.addEventListener("click", function () {
    cuadro.classList.toggle("active");
    menuOverlay.classList.toggle("active");
  });

  menuOverlay.addEventListener("click", function () {
    cuadro.classList.remove("active");
    menuOverlay.classList.remove("active");
  });
}

function limpiarFormularioExterno() {
  const campos = ["nombre", "apellido_p", "apellido_m", "correo"];

  campos.forEach((id) => {
    const el = document.getElementById(id);
    if (el) el.value = "";
  });

  const selects = [
  "id_sexo",
  "id_dependencia",
];

  selects.forEach((id) => {
    const el = document.getElementById(id);
    if (el) el.selectedIndex = 0;
  });

  actualizarDependenciaOtro();
}
