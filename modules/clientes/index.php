<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

protegerPagina();

$titulo_pagina = "Clientes";
$css_extra = "clientes.css";
include '../../includes/header.php';
include '../../includes/navbar.php';
include '../../includes/sidebar.php';
$busqueda = $_GET['busqueda'] ?? '';
$estado = $_GET['estado'] ?? 'todos';
$sql = "SELECT * FROM clientes WHERE 1=1";
$params = [];

if (!empty($busqueda)) {
    $sql .= " AND (nombre LIKE ? OR telefono LIKE ? OR identificacion LIKE ?)";
    $like = "%$busqueda%";
    $params = array_merge($params, [$like, $like, $like]);
}

if ($estado !== 'todos') {
    $sql .= " AND estado = ?";
    $params[] = $estado;
}

$sql .= " ORDER BY nombre ASC";

// Ejecutar consulta
$clientes = ejecutarConsulta($sql, $params);
?>

<main class="flex-grow-1 py-4">
    <div class="container">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="bi bi-people"></i> Gestión de Clientes</h4>
                <a href="crear.php" class="btn btn-success">
                    <i class="bi bi-plus-lg"></i> Nuevo Cliente
                </a>
            </div>
            <div class="card-body">
                <form method="GET" class="row mb-4 g-3">
                    <div class="col-md-4">
                        <select name="estado" class="form-select">
                            <option value="todos">Todos los estados</option>
                            <option value="activo" <?= $estado === 'activo' ? 'selected' : '' ?>>Activos</option>
                            <option value="inactivo" <?= $estado === 'inactivo' ? 'selected' : '' ?>>Inactivos</option>
                            <option value="moroso" <?= $estado === 'moroso' ? 'selected' : '' ?>>Morosos</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" name="busqueda" class="form-control" placeholder="Buscar cliente..." value="<?= htmlspecialchars($busqueda) ?>">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <a href="index.php" class="btn btn-outline-danger w-100">Limpiar</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Teléfono</th>
                                <th>Ubicación</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clientes as $cliente): ?>
                            <tr>
                                <td><?= $cliente['id'] ?></td>
                                <td><?= htmlspecialchars($cliente['nombre']) ?></td>
                                <td><?= htmlspecialchars($cliente['telefono']) ?></td>
                                <td><?= htmlspecialchars($cliente['ubicacion']) ?></td>
                                <td>
                                    <span class="badge bg-<?= 
                                        $cliente['estado'] == 'activo' ? 'success' : 
                                        ($cliente['estado'] == 'inactivo' ? 'secondary' : 'warning') 
                                    ?>">
                                        <?= ucfirst($cliente['estado']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="editar.php?id=<?= $cliente['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="eliminar.php?id=<?= $cliente['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Estás seguro?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../../includes/footer.php'; ?>