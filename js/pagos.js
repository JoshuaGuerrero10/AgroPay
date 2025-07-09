document.addEventListener('DOMContentLoaded', function() {
  const pagos = [
    { id: 1, fecha: '15/06/2023', cliente: 'Finca La Esperanza', monto: 125000, metodo: 'transferencia', factura: 'FAC-1001' },
    { id: 2, fecha: '10/06/2023', cliente: 'Don Carlos', monto: 50000, metodo: 'efectivo', factura: 'FAC-1002' },
    { id: 3, fecha: '05/06/2023', cliente: 'Agricola San José', monto: 75000, metodo: 'cheque', factura: 'FAC-1003' }
  ];

  const facturasPendientes = [
    { id: 1, numero: 'FAC-1001', cliente: 'Finca La Esperanza', fecha: '01/06/2023', total: 250000, saldo: 125000 },
    { id: 2, numero: 'FAC-1002', cliente: 'Don Carlos', fecha: '30/05/2023', total: 300000, saldo: 250000 },
    { id: 3, numero: 'FAC-1003', cliente: 'Agricola San José', fecha: '25/05/2023', total: 150000, saldo: 75000 }
  ];

  const tablaPagos = document.getElementById('tablaPagos').getElementsByTagName('tbody')[0];
  const tablaFacturas = document.getElementById('tablaFacturasPendientes').getElementsByTagName('tbody')[0];
  const tablaFacturasAplicar = document.getElementById('tablaFacturasAplicar').getElementsByTagName('tbody')[0];
  const filtroMetodo = document.getElementById('filtroMetodoPago');
  const filtroFechaDesde = document.getElementById('filtroFechaDesde');
  const filtroFechaHasta = document.getElementById('filtroFechaHasta');
  const filtroCliente = document.getElementById('filtroCliente');

  function renderPagos() {
    tablaPagos.innerHTML = '';
    pagos.forEach(pago => {
      const row = document.createElement('tr');
      
      let metodoClass, metodoText;
      switch(pago.metodo) {
        case 'efectivo':
          metodoClass = 'metodo-efectivo';
          metodoText = 'Efectivo';
          break;
        case 'transferencia':
          metodoClass = 'metodo-transferencia';
          metodoText = 'Transferencia';
          break;
        case 'cheque':
          metodoClass = 'metodo-cheque';
          metodoText = 'Cheque';
          break;
      }
      
      row.innerHTML = `
        <td>${pago.fecha}</td>
        <td>${pago.cliente}</td>
        <td>₡${pago.monto.toLocaleString()}</td>
        <td><span class="${metodoClass}">${metodoText}</span></td>
        <td>${pago.factura}</td>
        <td>
          <button class="btn btn-sm btn-outline-primary">
            <i class="bi bi-receipt"></i> Recibo
          </button>
        </td>
      `;
      tablaPagos.appendChild(row);
    });
  }

  function renderFacturasPendientes() {
    tablaFacturas.innerHTML = '';
    facturasPendientes.forEach(factura => {
      const row = document.createElement('tr');
      const porcentajePagado = ((factura.total - factura.saldo) / factura.total * 100).toFixed(0);
      
      row.innerHTML = `
        <td>${factura.numero}</td>
        <td>${factura.cliente}</td>
        <td>${factura.fecha}</td>
        <td>₡${factura.total.toLocaleString()}</td>
        <td class="${factura.saldo > 0 ? 'factura-saldo-pendiente' : 'factura-saldo-pagado'}">
          ₡${factura.saldo.toLocaleString()}
          <div class="progress mt-1" style="height: 5px;">
            <div class="progress-bar" role="progressbar" style="width: ${porcentajePagado}%"></div>
          </div>
        </td>
        <td>
          <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#nuevoPagoModal">
            <i class="bi bi-cash"></i> Pagar
          </button>
        </td>
      `;
      tablaFacturas.appendChild(row);
    });
  }

  function renderFacturasParaPago(clienteId) {
    tablaFacturasAplicar.innerHTML = '';
    const facturasCliente = clienteId ? 
      facturasPendientes.filter(f => f.cliente.toLowerCase().includes(
        document.getElementById('pagoCliente').options[document.getElementById('pagoCliente').selectedIndex].text.toLowerCase()
      )) : 
      facturasPendientes;
    
    facturasCliente.forEach(factura => {
      const row = document.createElement('tr');
      row.innerHTML = `
        <td>${factura.numero}</td>
        <td>${factura.fecha}</td>
        <td>₡${factura.total.toLocaleString()}</td>
        <td class="${factura.saldo > 0 ? 'factura-saldo-pendiente' : 'factura-saldo-pagado'}">
          ₡${factura.saldo.toLocaleString()}
        </td>
        <td>
          <input type="checkbox" class="form-check-input aplicar-factura" ${factura.saldo <= 0 ? 'disabled' : ''}>
        </td>
        <td>
          <input type="number" class="form-control form-control-sm monto-aplicar" 
                 min="1" max="${factura.saldo}" ${factura.saldo <= 0 ? 'disabled' : ''}>
        </td>
      `;
      tablaFacturasAplicar.appendChild(row);
    });
  }

  function filtrarPagos() {
    const metodo = filtroMetodo.value;
    const fechaDesde = filtroFechaDesde.value;
    const fechaHasta = filtroFechaHasta.value;
    const cliente = filtroCliente.value;
    
    let resultados = pagos;
    
    if (metodo !== 'todos') {
      resultados = resultados.filter(p => p.metodo === metodo);
    }
    
    if (cliente !== 'todos') {
      resultados = resultados.filter(p => p.cliente.toLowerCase().includes(
        filtroCliente.options[filtroCliente.selectedIndex].text.toLowerCase()
      ));
    }
    
    if (fechaDesde) {
      resultados = resultados.filter(p => {
        return true;
      });
    }
    
    renderPagos(resultados);
  }

  filtroMetodo.addEventListener('change', filtrarPagos);
  filtroFechaDesde.addEventListener('change', filtrarPagos);
  filtroFechaHasta.addEventListener('change', filtrarPagos);
  filtroCliente.addEventListener('change', filtrarPagos);

  document.getElementById('pagoCliente').addEventListener('change', function() {
    renderFacturasParaPago(this.value);
  });

  document.getElementById('guardarPago').addEventListener('click', function() {
    showNotification('Pago registrado exitosamente');
    const modal = bootstrap.Modal.getInstance(document.getElementById('nuevoPagoModal'));
    modal.hide();
  });

  renderPagos();
  renderFacturasPendientes();
});