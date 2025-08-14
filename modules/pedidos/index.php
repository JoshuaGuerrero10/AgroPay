<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

protegerPagina();

$estado = $_GET['estado'] ?? 'todos';
$cliente_id = $_GET['cliente_id'] ?? '';
$desde = $_GET['desde'] ?? '';
$hasta = $_GET['hasta'] ?? '';

$sql = "SELECT p.*, c.nombre as cliente_nombre 
        FROM pedidos p 
        JOIN clientes c ON p.cliente_id = c.id 
        WHERE 1=1";
$params = [];

if ($estado !== 'todos') {
    $sql .= " AND p.estado = ?";
    $params[] = $estado;
}

if (!empty($cliente_id)) {
    $sql .= " AND p.cliente_id = ?";
    $params[] = $cliente_id;
}

if (!empty($desde)) {
    $sql .= " AND p.fecha >= ?";
    $params[] = $desde;
}

if (!empty($hasta)) {
    $sql .= " AND p.fecha <= ?";
    $params[] = $hasta;
}

$sql .= " ORDER BY p.fecha DESC";
$pedidos = ejecutarConsulta($sql, $params);

$clientes = ejecutarConsulta("SELECT id, nombre FROM clientes ORDER BY nombre");

$titulo_pagina = "Gestión de Pedidos";
$css_extra = "pedidos.css";
include '../../includes/header.php';
include '../../includes/navbar.php';
include '../../includes/sidebar.php';
?>

<main class="flex-grow-1 py-4">
    <div class="container">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="bi bi-cart"></i> Pedidos</h4>
                <div>
                    <a href="crear.php" class="btn btn-success me-2">
                        <i class="bi bi-plus-lg"></i> Nuevo Pedido
                    </a>
                    <button class="btn btn-outline-primary">
                        <i class="bi bi-download"></i> Exportar
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" class="row mb-4 g-3">
                    <div class="col-md-3">
                        <select name="estado" class="form-select">
                            <option value="todos">Todos los estados</option>
                            <option value="pendiente" <?= $estado === 'pendiente' ? 'selected' : '' ?>>Pendientes</option>
                            <option value="completado" <?= $estado === 'completado' ? 'selected' : '' ?>>Completados</option>
                            <option value="cancelado" <?= $estado === 'cancelado' ? 'selected' : '' ?>>Cancelados</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="cliente_id" class="form-select">
                            <option value="">Todos los clientes</option>
                            <?php foreach ($clientes as $cliente): ?>
                            <option value="<?= $cliente['id'] ?>" <?= $cliente_id == $cliente['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cliente['nombre']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="desde" class="form-control" value="<?= $desde ?>">
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="hasta" class="form-control" value="<?= $hasta ?>">
                    </div>
                    <div class="col-md-12 text-end">
                        <button type="submit" class="btn btn-primary me-2">Filtrar</button>
                        <a href="index.php" class="btn btn-outline-secondary">Limpiar</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pedidos as $pedido): ?>
                            <tr>
                                <td><?= $pedido['id'] ?></td>
                                <td><?= formatearFecha($pedido['fecha']) ?></td>
                                <td><?= htmlspecialchars($pedido['cliente_nombre']) ?></td>
                                <td>₡<?= number_format($pedido['total'], 2) ?></td>
                                <td>
                                    <span class="badge bg-<?= 
                                        $pedido['estado'] == 'completado' ? 'success' : 
                                        ($pedido['estado'] == 'pendiente' ? 'warning' : 'danger') 
                                    ?>">
                                        <?= ucfirst($pedido['estado']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="editar.php?id=<?= $pedido['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="eliminar.php?id=<?= $pedido['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar este pedido?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                    <a href="ver.php?id=<?= $pedido['id'] ?>" class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-eye"></i>
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