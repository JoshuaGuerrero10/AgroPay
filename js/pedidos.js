document.addEventListener('DOMContentLoaded', function() {
  const pedidos = [
    { id: 1001, fecha: '15/06/2023', cliente: 'Finca La Esperanza', total: 85000, estado: 'completado' },
    { id: 1002, fecha: '14/06/2023', cliente: 'Don Carlos', total: 125000, estado: 'pendiente' },
    { id: 1003, fecha: '12/06/2023', cliente: 'Agricola San José', total: 65000, estado: 'completado' },
    { id: 1004, fecha: '10/06/2023', cliente: 'Agropecuaria Santa Ana', total: 185000, estado: 'pendiente' },
    { id: 1005, fecha: '08/06/2023', cliente: 'Finca Los Pinos', total: 75000, estado: 'cancelado' }
  ];

  const productos = [
    { id: 1, nombre: 'Fertilizante NPK', precio: 12500 },
    { id: 2, nombre: 'Semilla Maíz', precio: 3500 },
    { id: 3, nombre: 'Herbicida', precio: 8500 },
    { id: 4, nombre: 'Insecticida', precio: 9500 }
  ];

  const tablaPedidos = document.getElementById('tablaPedidos').getElementsByTagName('tbody')[0];
  const filtroEstado = document.getElementById('filtroEstadoPedido');
  const filtroFechaDesde = document.getElementById('filtroFechaDesde');
  const filtroFechaHasta = document.getElementById('filtroFechaHasta');
  const filtroCliente = document.getElementById('filtroCliente');
  const tablaProductos = document.getElementById('tablaProductosPedido').getElementsByTagName('tbody')[0];
  const btnAgregarProducto = document.getElementById('agregarProducto');
  const pedidoTotal = document.getElementById('pedidoTotal');

  function renderPedidos(data) {
    tablaPedidos.innerHTML = '';
    data.forEach(pedido => {
      const row = document.createElement('tr');
      
      let estadoClass, estadoText;
      switch(pedido.estado) {
        case 'pendiente':
          estadoClass = 'status-pendiente';
          estadoText = 'Pendiente';
          break;
        case 'completado':
          estadoClass = 'status-completado';
          estadoText = 'Completado';
          break;
        case 'cancelado':
          estadoClass = 'status-cancelado';
          estadoText = 'Cancelado';
          break;
      }
      
      row.innerHTML = `
        <td>${pedido.id}</td>
        <td>${pedido.fecha}</td>
        <td>${pedido.cliente}</td>
        <td>₡${pedido.total.toLocaleString()}</td>
        <td><span class="${estadoClass}">${estadoText}</span></td>
        <td>
          <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#detallePedidoModal">
            <i class="bi bi-eye"></i>
          </button>
          <button class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-pencil"></i>
          </button>
        </td>
      `;
      tablaPedidos.appendChild(row);
    });
  }

  function filtrarPedidos() {
    const estado = filtroEstado.value;
    const fechaDesde = filtroFechaDesde.value;
    const fechaHasta = filtroFechaHasta.value;
    const cliente = filtroCliente.value;
    
    let resultados = pedidos;
    
    if (estado !== 'todos') {
      resultados = resultados.filter(p => p.estado === estado);
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
    
    renderPedidos(resultados);
  }

  btnAgregarProducto.addEventListener('click', function() {
    const row = document.createElement('tr');
    row.innerHTML = `
      <td>
        <select class="form-select producto-select">
          <option value="">Seleccionar producto...</option>
          ${productos.map(p => `<option value="${p.id}" data-precio="${p.precio}">${p.nombre}</option>`).join('')}
        </select>
      </td>
      <td><input type="number" class="form-control cantidad" min="1" value="1"></td>
      <td class="precio-unitario">₡0</td>
      <td class="subtotal">₡0</td>
      <td>
        <button class="btn btn-sm btn-outline-danger eliminar-producto">
          <i class="bi bi-trash"></i>
        </button>
      </td>
    `;
    tablaProductos.appendChild(row);
    
    const select = row.querySelector('.producto-select');
    const cantidad = row.querySelector('.cantidad');
    const precioUnitario = row.querySelector('.precio-unitario');
    const subtotal = row.querySelector('.subtotal');
    
    function actualizarSubtotal() {
      const precio = select.selectedOptions[0]?.dataset.precio || 0;
      const cant = cantidad.value || 0;
      const sub = precio * cant;
      
      precioUnitario.textContent = `₡${parseInt(precio).toLocaleString()}`;
      subtotal.textContent = `₡${sub.toLocaleString()}`;
      actualizarTotal();
    }
    
    select.addEventListener('change', actualizarSubtotal);
    cantidad.addEventListener('input', actualizarSubtotal);
    
    row.querySelector('.eliminar-producto').addEventListener('click', function() {
      row.remove();
      actualizarTotal();
    });
  });

  function actualizarTotal() {
    let total = 0;
    document.querySelectorAll('#tablaProductosPedido tbody tr').forEach(row => {
      const subtotalText = row.querySelector('.subtotal').textContent.replace('₡', '').replace(/,/g, '');
      total += parseInt(subtotalText) || 0;
    });
    
    pedidoTotal.textContent = `₡${total.toLocaleString()}`;
  }

  document.getElementById('guardarPedido').addEventListener('click', function() {
    showNotification('Pedido guardado exitosamente');
    const modal = bootstrap.Modal.getInstance(document.getElementById('nuevoPedidoModal'));
    modal.hide();
  });

  filtroEstado.addEventListener('change', filtrarPedidos);
  filtroFechaDesde.addEventListener('change', filtrarPedidos);
  filtroFechaHasta.addEventListener('change', filtrarPedidos);
  filtroCliente.addEventListener('change', filtrarPedidos);

  renderPedidos(pedidos);
});