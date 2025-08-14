<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

protegerPagina();

$categoria = $_GET['categoria'] ?? 'todos';
$busqueda = $_GET['busqueda'] ?? '';

$sql = "SELECT * FROM articulos WHERE 1=1";
$params = [];

if ($categoria !== 'todos') {
    $sql .= " AND categoria = ?";
    $params[] = $categoria;
}

if (!empty($busqueda)) {
    $sql .= " AND (titulo LIKE ? OR contenido LIKE ?)";
    $like = "%$busqueda%";
    $params = array_merge($params, [$like, $like]);
}

$sql .= " ORDER BY fecha_publicacion DESC";
$articulos = ejecutarConsulta($sql, $params);

$videos = [
    ['id' => 1, 'titulo' => 'Preparación de suelo', 'url' => 'https://www.youtube.com/embed/KGD88HPf8pU'],
    ['id' => 2, 'titulo' => 'Podas básicas', 'url' => 'https://www.youtube.com/embed/aUT2V3zWNfg'],
    ['id' => 3, 'titulo' => 'Fertilización orgánica', 'url' => 'https://www.youtube.com/embed/ejemplo3']
];

$titulo_pagina = "Sección Técnica";
$css_extra = "conocimiento.css";
include '../../includes/header.php';
include '../../includes/navbar.php';
include '../../includes/sidebar.php';
?>

<main class="flex-grow-1 py-4">
    <div class="container">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h4 class="mb-0"><i class="bi bi-book"></i> Conocimiento Agrícola</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="list-group mb-4">
                            <a href="?categoria=todos" class="list-group-item list-group-item-action <?= $categoria === 'todos' ? 'active' : '' ?>">
                                Todos los temas
                            </a>
                            <a href="?categoria=cultivos" class="list-group-item list-group-item-action <?= $categoria === 'cultivos' ? 'active' : '' ?>">
                                Manejo de cultivos
                            </a>
                            <a href="?categoria=plagas" class="list-group-item list-group-item-action <?= $categoria === 'plagas' ? 'active' : '' ?>">
                                Control de plagas
                            </a>
                            <a href="?categoria=riego" class="list-group-item list-group-item-action <?= $categoria === 'riego' ? 'active' : '' ?>">
                                Riego eficiente
                            </a>
                            <a href="?categoria=finanzas" class="list-group-item list-group-item-action <?= $categoria === 'finanzas' ? 'active' : '' ?>">
                                Finanzas agrícolas
                            </a>
                        </div>
                    </div>

                    <div class="col-md-9">
                        <div class="input-group mb-4">
                            <input type="text" id="busquedaInput" class="form-control" placeholder="Buscar artículos..." value="<?= htmlspecialchars($busqueda) ?>">
                            <button class="btn btn-success" type="button" id="btnBuscar">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>

                        <div class="row" id="contenidoArticulos">
                            <?php foreach ($articulos as $articulo): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card h-100">
                                    <img src="<?= htmlspecialchars($articulo['imagen'] ?? 'https://via.placeholder.com/300x200?text=AgroPay+') ?>" 
                                         class="card-img-top" alt="<?= htmlspecialchars($articulo['titulo']) ?>">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($articulo['titulo']) ?></h5>
                                        <p class="card-text">
                                            <?= substr(strip_tags($articulo['contenido']), 0, 150) ?>...
                                        </p>
                                        <a href="articulo.php?id=<?= $articulo['id'] ?>" class="btn btn-outline-success">
                                            Leer más
                                        </a>
                                    </div>
                                    <div class="card-footer">
                                        <small class="text-muted">
                                            Publicado: <?= formatearFecha($articulo['fecha_publicacion'], 'd/m/Y') ?>
                                        </small>
                                        <span class="badge bg-<?= 
                                            $articulo['categoria'] === 'cultivos' ? 'primary' : 
                                            ($articulo['categoria'] === 'plagas' ? 'warning' : 
                                            ($articulo['categoria'] === 'riego' ? 'success' : 'info')) 
                                        ?> float-end">
                                            <?= ucfirst($articulo['categoria']) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            
                            <?php if (empty($articulos)): ?>
                            <div class="col-12 text-center py-4">
                                <p>No se encontraron artículos que coincidan con la búsqueda.</p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <h5 class="mt-4 mb-3"><i class="bi bi-camera-video"></i> Videos instructivos</h5>
                        <div class="row">
                            <?php foreach ($videos as $video): ?>
                            <div class="col-md-4 mb-4">
                                <div class="card">
                                    <div class="ratio ratio-16x9">
                                        <iframe src="<?= htmlspecialchars($video['url']) ?>" allowfullscreen></iframe>
                                    </div>
                                    <div class="card-body">
                                        <h6 class="card-title"><?= htmlspecialchars($video['titulo']) ?></h6>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('btnBuscar').addEventListener('click', function() {
        const busqueda = document.getElementById('busquedaInput').value;
        const categoria = new URLSearchParams(window.location.search).get('categoria') || 'todos';
        window.location.href = `?categoria=${categoria}&busqueda=${encodeURIComponent(busqueda)}`;
    });
    
    document.getElementById('busquedaInput').addEventListener('keyup', function(e) {
        if (e.key === 'Enter') {
            document.getElementById('btnBuscar').click();
        }
    });
});
</script>

<?php include '../../includes/footer.php'; ?>