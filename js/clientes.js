document.addEventListener('DOMContentLoaded', function() {
  const clientes = [
    { id: 1, nombre: "Finca La Esperanza", telefono: "8888-8888", ubicacion: "San Carlos", estado: "activo" },
    { id: 2, nombre: "Don Carlos", telefono: "7777-7777", ubicacion: "Zarcero", estado: "moroso" },
    { id: 3, nombre: "Agricola San José", telefono: "6666-6666", ubicacion: "Alajuela", estado: "activo" },
    { id: 4, nombre: "Agropecuaria Santa Ana", telefono: "5555-5555", ubicacion: "Heredia", estado: "inactivo" },
    { id: 5, nombre: "Finca Los Pinos", telefono: "4444-4444", ubicacion: "Cartago", estado: "activo" }
  ];

  const tablaClientes = document.getElementById('tablaClientes').getElementsByTagName('tbody')[0];
  const filtroEstado = document.getElementById('filtroEstado');
  const buscarCliente = document.getElementById('buscarCliente');
  const btnBuscar = document.getElementById('btnBuscar');
  const paginacion = document.getElementById('paginacionClientes');

  function renderClientes(data) {
    tablaClientes.innerHTML = '';
    data.forEach(cliente => {
      const row = document.createElement('tr');
      
      let estadoClass, estadoText;
      switch(cliente.estado) {
        case 'activo':
          estadoClass = 'status-activo';
          estadoText = 'Activo';
          break;
        case 'inactivo':
          estadoClass = 'status-inactivo';
          estadoText = 'Inactivo';
          break;
        case 'moroso':
          estadoClass = 'status-moroso';
          estadoText = 'Moroso';
          break;
      }
      
      row.innerHTML = `
        <td>${cliente.id}</td>
        <td>${cliente.nombre}</td>
        <td>${cliente.telefono}</td>
        <td>${cliente.ubicacion}</td>
        <td><span class="${estadoClass}"></span> ${estadoText}</td>
        <td class="client-actions">
          <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editarClienteModal" data-id="${cliente.id}">
            <i class="bi bi-pencil"></i>
          </button>
          <button class="btn btn-sm btn-outline-danger" data-id="${cliente.id}">
            <i class="bi bi-trash"></i>
          </button>
        </td>
      `;
      tablaClientes.appendChild(row);
    });
  }

  function filtrarClientes() {
    const estado = filtroEstado.value;
    const busqueda = buscarCliente.value.toLowerCase();
    
    let resultados = clientes;
    
    if (estado !== 'todos') {
      resultados = resultados.filter(c => c.estado === estado);
    }
    
    if (busqueda) {
      resultados = resultados.filter(c => 
        c.nombre.toLowerCase().includes(busqueda) || 
        c.telefono.includes(busqueda) ||
        c.ubicacion.toLowerCase().includes(busqueda)
      );
    }
    
    renderClientes(resultados);
    renderPaginacion(resultados.length);
  }

  function renderPaginacion(total) {
    paginacion.innerHTML = '';
    const paginas = Math.ceil(total / 5);
    
    for (let i = 1; i <= paginas; i++) {
      const li = document.createElement('li');
      li.className = 'page-item';
      li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
      paginacion.appendChild(li);
    }
  }

  filtroEstado.addEventListener('change', filtrarClientes);
  btnBuscar.addEventListener('click', filtrarClientes);
  buscarCliente.addEventListener('keyup', function(e) {
    if (e.key === 'Enter') filtrarClientes();
  });

  document.getElementById('guardarCliente').addEventListener('click', function() {
    showNotification('Cliente guardado exitosamente');
    const modal = bootstrap.Modal.getInstance(document.getElementById('nuevoClienteModal'));
    modal.hide();
  });

  renderClientes(clientes);
  renderPaginacion(clientes.length);
});