document.addEventListener('DOMContentLoaded', function() {
  const reporteVentas = {
    titulo: "Ventas Mensuales",
    datos: {
      labels: ["Ene", "Feb", "Mar", "Abr", "May", "Jun"],
      datasets: [{
        label: "Ventas 2023",
        data: [1200000, 1500000, 1800000, 1650000, 2100000, 1950000],
        backgroundColor: "rgba(40, 167, 69, 0.2)",
        borderColor: "rgba(40, 167, 69, 1)",
        borderWidth: 2
      }]
    },
    tabla: [
      { fecha: "15/06/2023", cliente: "Finca La Esperanza", monto: "₡250,000", estado: "Pagado" },
      { fecha: "10/06/2023", cliente: "Don Carlos", monto: "₡180,000", estado: "Pendiente" },
      { fecha: "05/06/2023", cliente: "Agricola San José", monto: "₡150,000", estado: "Pagado" }
    ]
  };

  const reporteClientes = {
    titulo: "Clientes Principales",
    datos: {
      labels: ["Finca La Esperanza", "Don Carlos", "Agricola San José", "Agropecuaria Santa Ana", "Finca Los Pinos"],
      datasets: [{
        data: [1250000, 980000, 750000, 620000, 450000],
        backgroundColor: [
          "rgba(40, 167, 69, 0.7)",
          "rgba(108, 117, 125, 0.7)",
          "rgba(255, 193, 7, 0.7)",
          "rgba(13, 110, 253, 0.7)",
          "rgba(220, 53, 69, 0.7)"
        ]
      }]
    }
  };

  const reporteProductos = {
    titulo: "Productos Más Vendidos",
    datos: {
      labels: ["Fertilizante NPK", "Semilla Maíz", "Herbicida", "Insecticida", "Fungicida"],
      datasets: [{
        data: [45, 30, 25, 20, 15],
        backgroundColor: [
          "rgba(40, 167, 69, 0.5)",
          "rgba(108, 117, 125, 0.5)",
          "rgba(255, 193, 7, 0.5)",
          "rgba(13, 110, 253, 0.5)",
          "rgba(220, 53, 69, 0.5)"
        ],
        borderColor: [
          "rgba(40, 167, 69, 1)",
          "rgba(108, 117, 125, 1)",
          "rgba(255, 193, 7, 1)",
          "rgba(13, 110, 253, 1)",
          "rgba(220, 53, 69, 1)"
        ],
        borderWidth: 1
      }]
    }
  };

  const tipoReporte = document.getElementById('tipoReporte');
  const fechaInicio = document.getElementById('fechaInicio');
  const fechaFin = document.getElementById('fechaFin');
  const tituloGraficoPrincipal = document.getElementById('tituloGraficoPrincipal');
  const tituloGraficoSecundario = document.getElementById('tituloGraficoSecundario');
  const tituloGraficoTerciario = document.getElementById('tituloGraficoTerciario');
  const tituloTabla = document.getElementById('tituloTabla');
  const columna1 = document.getElementById('columna1');
  const columna2 = document.getElementById('columna2');
  const columna3 = document.getElementById('columna3');
  const columna4 = document.getElementById('columna4');
  const tablaReporte = document.getElementById('tablaReporte').getElementsByTagName('tbody')[0];

  let graficoPrincipal, graficoSecundario, graficoTerciario;

  function initCharts() {
    const ctxPrincipal = document.getElementById('graficoPrincipal').getContext('2d');
    const ctxSecundario = document.getElementById('graficoSecundario').getContext('2d');
    const ctxTerciario = document.getElementById('graficoTerciario').getContext('2d');
    
    graficoPrincipal = new Chart(ctxPrincipal, {
      type: 'line',
      data: reporteVentas.datos,
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'top'
          }
        }
      }
    });
    
    graficoSecundario = new Chart(ctxSecundario, {
      type: 'doughnut',
      data: reporteClientes.datos,
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'bottom'
          }
        }
      }
    });
    
    graficoTerciario = new Chart(ctxTerciario, {
      type: 'bar',
      data: reporteProductos.datos,
      options: {
        responsive: true,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });
  }

  function renderTabla(data) {
    tablaReporte.innerHTML = '';
    data.forEach(item => {
      const row = document.createElement('tr');
      row.innerHTML = `
        <td>${item.fecha}</td>
        <td>${item.cliente}</td>
        <td>${item.monto}</td>
        <td>${item.estado}</td>
      `;
      tablaReporte.appendChild(row);
    });
  }

  function cambiarReporte() {
    const tipo = tipoReporte.value;
    
    switch(tipo) {
      case 'ventas':
        tituloGraficoPrincipal.textContent = "Ventas Mensuales";
        tituloGraficoSecundario.textContent = "Distribución por Cliente";
        tituloGraficoTerciario.textContent = "Top Productos";
        tituloTabla.textContent = "Detalle de Ventas";
        columna1.textContent = "Fecha";
        columna2.textContent = "Cliente";
        columna3.textContent = "Monto";
        columna4.textContent = "Estado";
        renderTabla(reporteVentas.tabla);
        break;
        
      case 'clientes':
        tituloGraficoPrincipal.textContent = "Clientes por Volumen de Compra";
        tituloGraficoSecundario.textContent = "Distribución Geográfica";
        tituloGraficoTerciario.textContent = "Histórico de Compras";
        tituloTabla.textContent = "Detalle de Clientes";
        columna1.textContent = "Cliente";
        columna2.textContent = "Ubicación";
        columna3.textContent = "Total Compras";
        columna4.textContent = "Última Compra";
        break;
        
    }
  }

  document.getElementById('btnExportarPDF').addEventListener('click', function() {
    showNotification('Generando reporte PDF...', 'info');
  });

  document.getElementById('btnExportarExcel').addEventListener('click', function() {
    showNotification('Generando reporte Excel...', 'info');
  });

  document.getElementById('btnExportarCSV').addEventListener('click', function() {
    showNotification('Generando reporte CSV...', 'info');
  });

  tipoReporte.addEventListener('change', cambiarReporte);
  fechaInicio.addEventListener('change', function() {
  });
  fechaFin.addEventListener('change', function() {
  });

  initCharts();
  renderTabla(reporteVentas.tabla);
});