<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

protegerPagina('admin,vendedor');

$cliente_id = $_GET['cliente_id'] ?? '';
$metodo = $_GET['metodo'] ?? 'todos';
$desde = $_GET['desde'] ?? '';
$hasta = $_GET['hasta'] ?? '';

// Construir consulta SQL
$sql = "SELECT p.*, c.nombre as cliente_nombre, u.nombre as usuario_nombre 
        FROM pagos p
        JOIN clientes c ON p.cliente_id = c.id
        JOIN usuarios u ON p.usuario_id = u.id
        WHERE 1=1";

$params = [];
$types = '';

if (!empty($cliente_id)) {
    $sql .= " AND p.cliente_id = ?";
    $params[] = $cliente_id;
    $types .= 'i';
}

if ($metodo != 'todos') {
    $sql .= " AND p.metodo = ?";
    $params[] = $metodo;
    $types .= 's';
}

if (!empty($desde)) {
    $sql .= " AND p.fecha >= ?";
    $params[] = $desde;
    $types .= 's';
}

if (!empty($hasta)) {
    $sql .= " AND p.fecha <= ?";
    $params[] = $hasta;
    $types .= 's';
}

$sql .= " ORDER BY p.fecha DESC, p.id DESC";

$pagos = ejecutarConsulta($sql, $params);

$clientes = ejecutarConsulta("SELECT id, nombre FROM clientes WHERE estado = 'activo' ORDER BY nombre");

$titulo_pagina = "Registro de Pagos";
include '../../includes/header.php';
include '../../includes/navbar.php';
include '../../includes/sidebar.php';
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="bi bi-cash-coin"></i> Registro de Pagos</h4>
                <a href="crear.php" class="btn btn-success">
                    <i class="bi bi-plus-lg"></i> Nuevo Pago
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="get" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select name="metodo" class="form-select">
                            <option value="todos">Todos los métodos</option>
                            <option value="efectivo" <?= $metodo === 'efectivo' ? 'selected' : '' ?>>Efectivo</option>
                            <option value="transferencia" <?= $metodo === 'transferencia' ? 'selected' : '' ?>>Transferencia</option>
                            <option value="cheque" <?= $metodo === 'cheque' ? 'selected' : '' ?>>Cheque</option>
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
                        <input type="date" name="desde" class="form-control" value="<?= htmlspecialchars($desde) ?>">
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="hasta" class="form-control" value="<?= htmlspecialchars($hasta) ?>">
                    </div>
                    <div class="col-md-12 text-end">
                        <button type="submit" class="btn btn-primary me-2">Filtrar</button>
                        <a href="index.php" class="btn btn-outline-secondary">Limpiar</a>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Monto</th>
                            <th>Método</th>
                            <th>Registrado por</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pagos)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">No se encontraron pagos</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($pagos as $pago): ?>
                        <tr>
                            <td><?= $pago['id'] ?></td>
                            <td><?= formatearFecha($pago['fecha'], 'd/m/Y') ?></td>
                            <td><?= htmlspecialchars($pago['cliente_nombre']) ?></td>
                            <td>₡<?= number_format($pago['monto'], 2) ?></td>
                            <td>
                                <span class="badge bg-<?= 
                                    $pago['metodo'] == 'efectivo' ? 'success' : 
                                    ($pago['metodo'] == 'transferencia' ? 'primary' : 'info')
                                ?>">
                                    <?= ucfirst($pago['metodo']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($pago['usuario_nombre']) ?></td>
                            <td>
                                <a href="ver.php?id=<?= $pago['id'] ?>" class="btn btn-sm btn-outline-primary" title="Ver detalles">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="imprimir.php?id=<?= $pago['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Imprimir recibo">
                                    <i class="bi bi-printer"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>