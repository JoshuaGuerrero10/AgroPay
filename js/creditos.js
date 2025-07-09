document.addEventListener('DOMContentLoaded', function() {
  const creditosActivos = [
    { cliente: 'Finca La Esperanza', limite: 500000, utilizado: 125000, vencimiento: '30/06/2023' },
    { cliente: 'Don Carlos', limite: 300000, utilizado: 300000, vencimiento: '25/06/2023' },
    { cliente: 'Agricola San José', limite: 750000, utilizado: 250000, vencimiento: '15/07/2023' }
  ];

  const creditosVencidos = [
    { cliente: 'Agropecuaria Santa Ana', monto: 185000, dias: 15 },
    { cliente: 'Finca Los Pinos', monto: 75000, dias: 5 }
  ];

  const historialCreditos = [
    { fecha: '15/06/2023', cliente: 'Finca La Esperanza', accion: 'Aumento límite', monto: '+₡200,000', usuario: 'admin' },
    { fecha: '10/06/2023', cliente: 'Don Carlos', accion: 'Pago recibido', monto: '-₡50,000', usuario: 'admin' }
  ];

  const proximosVencimientos = [
    { cliente: 'Finca La Esperanza', fecha: '30/06/2023', monto: '₡125,000' },
    { cliente: 'Don Carlos', fecha: '25/06/2023', monto: '₡300,000' }
  ];

  const tablaActivos = document.getElementById('tablaCreditosActivos').getElementsByTagName('tbody')[0];
  const tablaVencidos = document.getElementById('tablaCreditosVencidos').getElementsByTagName('tbody')[0];
  const tablaHistorial = document.getElementById('tablaHistorialCreditos').getElementsByTagName('tbody')[0];
  const listaVencimientos = document.getElementById('listaVencimientos');

  function renderCreditos() {
    tablaActivos.innerHTML = '';
    creditosActivos.forEach(credito => {
      const disponible = credito.limite - credito.utilizado;
      const row = document.createElement('tr');
      row.innerHTML = `
        <td>${credito.cliente}</td>
        <td>₡${credito.limite.toLocaleString()}</td>
        <td class="credito-utilizado">₡${credito.utilizado.toLocaleString()}</td>
        <td class="credito-disponible">₡${disponible.toLocaleString()}</td>
        <td>${credito.vencimiento}</td>
        <td>
          <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#gestionCreditoModal">
            <i class="bi bi-pencil"></i>
          </button>
        </td>
      `;
      tablaActivos.appendChild(row);
    });

    tablaVencidos.innerHTML = '';
    creditosVencidos.forEach(credito => {
      const row = document.createElement('tr');
      row.innerHTML = `
        <td>${credito.cliente}</td>
        <td>₡${credito.monto.toLocaleString()}</td>
        <td class="vencido-dias">${credito.dias} días</td>
        <td>
          <button class="btn btn-sm btn-outline-warning">
            <i class="bi bi-arrow-repeat"></i> Renegociar
          </button>
        </td>
      `;
      tablaVencidos.appendChild(row);
    });

    tablaHistorial.innerHTML = '';
    historialCreditos.forEach(item => {
      const row = document.createElement('tr');
      row.innerHTML = `
        <td>${item.fecha}</td>
        <td>${item.cliente}</td>
        <td>${item.accion}</td>
        <td>${item.monto}</td>
        <td>${item.usuario}</td>
      `;
      tablaHistorial.appendChild(row);
    });

    listaVencimientos.innerHTML = '';
    proximosVencimientos.forEach(vencimiento => {
      const item = document.createElement('li');
      item.className = 'list-group-item';
      item.innerHTML = `
        <div>
          <h6 class="vencimiento-cliente mb-1">${vencimiento.cliente}</h6>
          <small class="text-muted">Vence: ${vencimiento.fecha}</small>
        </div>
        <span class="vencimiento-monto">${vencimiento.monto}</span>
      `;
      listaVencimientos.appendChild(item);
    });
  }

  function initChart() {
    const ctx = document.getElementById('graficoCreditos').getContext('2d');
    const chart = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Utilizado', 'Disponible'],
        datasets: [{
          data: [1250000, 750000],
          backgroundColor: ['#dc3545', '#28a745'],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'bottom'
          }
        }
      }
    });
  }

  document.querySelectorAll('#tablaCreditosActivos .btn-outline-primary').forEach(btn => {
    btn.addEventListener('click', function() {
      const row = this.closest('tr');
      const cliente = row.cells[0].textContent;
      const limite = row.cells[1].textContent.replace(/[^\d]/g, '');
      
      document.getElementById('creditoCliente').value = cliente;
      document.getElementById('creditoLimiteActual').value = `₡${parseInt(limite).toLocaleString()}`;
      document.getElementById('creditoNuevoLimite').value = limite;
    });
  });

  document.getElementById('guardarCredito').addEventListener('click', function() {
    showNotification('Límite de crédito actualizado exitosamente');
    const modal = bootstrap.Modal.getInstance(document.getElementById('gestionCreditoModal'));
    modal.hide();
  });

  // Inicializar
  renderCreditos();
  initChart();
});