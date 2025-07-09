document.addEventListener('DOMContentLoaded', function() {
  const dashboardData = {
    clientes: 24,
    pedidos: 48,
    pagosPendientes: 1250000,
    pedidosRecientes: [
      { cliente: "Finca La Esperanza", fecha: "15/06/2023", total: "₡85,000" },
      { cliente: "Don Carlos", fecha: "14/06/2023", total: "₡125,000" },
      { cliente: "Agricola San José", fecha: "12/06/2023", total: "₡65,000" }
    ],
    proximosVencimientos: [
      { cliente: "Finca Los Pinos", fecha: "20/06/2023", monto: "₡250,000" },
      { cliente: "Agropecuaria Santa Ana", fecha: "22/06/2023", monto: "₡180,000" }
    ]
  };

  document.getElementById('clientes-count').textContent = dashboardData.clientes;
  document.getElementById('pedidos-count').textContent = dashboardData.pedidos;
  document.getElementById('pagos-count').textContent = `₡${dashboardData.pagosPendientes.toLocaleString()}`;

  const ordersTable = document.querySelector('#recent-orders tbody');
  dashboardData.pedidosRecientes.forEach(order => {
    const row = document.createElement('tr');
    row.innerHTML = `
      <td>${order.cliente}</td>
      <td>${order.fecha}</td>
      <td>${order.total}</td>
    `;
    ordersTable.appendChild(row);
  });

  const paymentsList = document.getElementById('upcoming-payments');
  dashboardData.proximosVencimientos.forEach(payment => {
    const item = document.createElement('li');
    item.className = 'list-group-item d-flex justify-content-between align-items-center';
    item.innerHTML = `
      <div>
        <h6 class="mb-0">${payment.cliente}</h6>
        <small class="text-muted">${payment.fecha}</small>
      </div>
      <span class="badge bg-warning rounded-pill">${payment.monto}</span>
    `;
    paymentsList.appendChild(item);
  });

  function loadDashboardData() {
    return new Promise((resolve) => {
      setTimeout(() => {
        resolve(dashboardData);
      }, 1000);
    });
  }

  async function updateDashboard() {
    try {
      const data = await loadDashboardData();
      console.log('Datos actualizados:', data);
    } catch (error) {
      console.error('Error al cargar datos:', error);
      showNotification('Error al cargar datos del dashboard', 'danger');
    }
  }

  setInterval(updateDashboard, 30000);
});