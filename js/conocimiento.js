document.addEventListener('DOMContentLoaded', function() {
  const articulos = [
    { 
      id: 1,
      titulo: "Técnicas de riego eficiente",
      resumen: "Aprenda a optimizar el uso del agua en sus cultivos con estas técnicas probadas.",
      contenido: "<p>Contenido detallado sobre técnicas de riego...</p>",
      fecha: "15/06/2023",
      categoria: "riego",
      imagen: "https://img.freepik.com/fotos-premium/sistema-riego-eficiente-agricultura-conserva-agua-mejorando-rendimiento-sostenibilidad-cultivos_864588-59718.jpg?w=2000"
    },
    { 
      id: 2,
      titulo: "Control orgánico de plagas",
      resumen: "Métodos naturales para proteger sus cultivos sin químicos dañinos.",
      contenido: "<p>Contenido detallado sobre control de plagas...</p>",
      fecha: "10/06/2023",
      categoria: "plagas",
      imagen: "https://viverolandia.com/wp-content/uploads/2021/08/Control-org%C3%A1nico-de-plagas.png"
    },
  ];

  const videos = [
    {
      id: 1,
      titulo: "Preparación de suelo",
      url: "https://www.youtube.com/watch?v=KGD88HPf8pU"
    },
    {
      id: 2,
      titulo: "Podas básicas",
      url: "https://www.youtube.com/watch?v=aUT2V3zWNfg"
    },
  ];

  const categorias = document.getElementById('categoriasConocimiento');
  const busqueda = document.getElementById('busquedaConocimiento');
  const btnBuscar = document.getElementById('btnBuscarConocimiento');
  const contenido = document.getElementById('contenidoConocimiento');
  const videosContainer = document.getElementById('videosConocimiento');

  function renderArticulos(filtroCategoria = 'todos', filtroTexto = '') {
    contenido.innerHTML = '';
    
    let articulosFiltrados = articulos;
    
    if (filtroCategoria !== 'todos') {
      articulosFiltrados = articulosFiltrados.filter(a => a.categoria === filtroCategoria);
    }
    
    if (filtroTexto) {
      const texto = filtroTexto.toLowerCase();
      articulosFiltrados = articulosFiltrados.filter(a => 
        a.titulo.toLowerCase().includes(texto) || 
        a.resumen.toLowerCase().includes(texto)
      );
    }
    
    if (articulosFiltrados.length === 0) {
      contenido.innerHTML = '<div class="col-12 text-center py-4"><p>No se encontraron artículos que coincidan con la búsqueda.</p></div>';
      return;
    }
    
    articulosFiltrados.forEach(articulo => {
      const col = document.createElement('div');
      col.className = 'col-md-6 mb-4';
      
      let categoriaClass, categoriaText;
      switch(articulo.categoria) {
        case 'riego':
          categoriaClass = 'bg-success';
          categoriaText = 'Riego';
          break;
        case 'plagas':
          categoriaClass = 'bg-warning text-dark';
          categoriaText = 'Plagas';
          break;
        case 'cultivos':
          categoriaClass = 'bg-primary';
          categoriaText = 'Cultivos';
          break;
        case 'finanzas':
          categoriaClass = 'bg-info text-dark';
          categoriaText = 'Finanzas';
          break;
      }
      
      col.innerHTML = `
        <div class="card h-100">
          <img src="${articulo.imagen}" class="card-img-top" alt="${articulo.titulo}">
          <div class="card-body">
            <h5 class="card-title">${articulo.titulo}</h5>
            <p class="card-text">${articulo.resumen}</p>
            <a href="#" class="btn btn-outline-success ver-articulo" data-id="${articulo.id}">Leer más</a>
          </div>
          <div class="card-footer">
            <small class="text-muted">Publicado: ${articulo.fecha}</small>
            <span class="badge ${categoriaClass} float-end">${categoriaText}</span>
          </div>
        </div>
      `;
      contenido.appendChild(col);
    });
    
    document.querySelectorAll('.ver-articulo').forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        const articuloId = parseInt(this.dataset.id);
        const articulo = articulos.find(a => a.id === articuloId);
        
        if (articulo) {
          document.getElementById('articuloTitulo').textContent = articulo.titulo;
          document.getElementById('articuloContenido').innerHTML = articulo.contenido;
          
          const modal = new bootstrap.Modal(document.getElementById('verArticuloModal'));
          modal.show();
        }
      });
    });
  }

  function renderVideos() {
    videosContainer.innerHTML = '';
    
    videos.forEach(video => {
      const col = document.createElement('div');
      col.className = 'col-md-4 mb-4';
      col.innerHTML = `
        <div class="card">
          <div class="ratio ratio-16x9">
            <iframe src="${video.url}" allowfullscreen></iframe>
          </div>
          <div class="card-body">
            <h6 class="card-title">${video.titulo}</h6>
          </div>
        </div>
      `;
      videosContainer.appendChild(col);
    });
  }

  categorias.addEventListener('click', function(e) {
    if (e.target.classList.contains('list-group-item')) {
      e.preventDefault();
      
      document.querySelectorAll('#categoriasConocimiento .list-group-item').forEach(item => {
        item.classList.remove('active');
      });
      
      e.target.classList.add('active');
      
      const categoria = e.target.dataset.categoria;
      renderArticulos(categoria, busqueda.value);
    }
  });

  btnBuscar.addEventListener('click', function() {
    const categoriaActiva = document.querySelector('#categoriasConocimiento .list-group-item.active').dataset.categoria;
    renderArticulos(categoriaActiva, busqueda.value);
  });

  busqueda.addEventListener('keyup', function(e) {
    if (e.key === 'Enter') {
      const categoriaActiva = document.querySelector('#categoriasConocimiento .list-group-item.active').dataset.categoria;
      renderArticulos(categoriaActiva, busqueda.value);
    }
  });

  renderArticulos();
  renderVideos();
});