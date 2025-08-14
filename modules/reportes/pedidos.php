<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

protegerPagina('admin,vendedor');

$desde = $_GET['desde'] ?? date('Y-m-01');
$hasta = $_GET['hasta'] ?? date('Y-m-d');
$estado = $_GET['estado'] ?? 'todos';
$cliente_id = $_GET['cliente_id'] ?? '';
$formato = $_GET['formato'] ?? 'web';

if (!validateDate($desde)) $desde = date('Y-m-01');
if (!validateDate($hasta)) $hasta = date('Y-m-d');

$sql = "SELECT p.*, c.nombre as cliente_nombre, u.nombre as usuario_nombre 
        FROM pedidos p
        JOIN clientes c ON p.cliente_id = c.id
        JOIN usuarios u ON p.usuario_id = u.id
        WHERE p.fecha BETWEEN ? AND ?";
$params = [$desde, $hasta];

if ($estado !== 'todos') {
    $sql .= " AND p.estado = ?";
    $params[] = $estado;
}

if (!empty($cliente_id)) {
    $sql .= " AND p.cliente_id = ?";
    $params[] = $cliente_id;
}

$sql .= " ORDER BY p.fecha DESC";
$pedidos = ejecutarConsulta($sql, $params);

$sql_totales = "SELECT 
                  estado, 
                  COUNT(*) as cantidad,
                  SUM(total) as total
                FROM pedidos
                WHERE fecha BETWEEN ? AND ?";
$params_totales = [$desde, $hasta];

if ($estado !== 'todos') {
    $sql_totales .= " AND estado = ?";
    $params_totales[] = $estado;
}

if (!empty($cliente_id)) {
    $sql_totales .= " AND cliente_id = ?";
    $params_totales[] = $cliente_id;
}

$sql_totales .= " GROUP BY estado";
$totales = ejecutarConsulta($sql_totales, $params_totales);

$total_general = array_sum(array_column($totales, 'total'));

if ($formato !== 'web') {
    require_once '../../includes/reportes/exportar.php';
    exportarReporte($formato, 'Reporte de Pedidos', [
        'desde' => $desde,
        'hasta' => $hasta,
        'estado' => $estado,
        'cliente_id' => $cliente_id
    ], $pedidos, $totales);
    exit;
}

$clientes = ejecutarConsulta("SELECT id, nombre FROM clientes ORDER BY nombre");

$titulo_pagina = "Reporte de Pedidos";
$css_especial = "reportes.css";
include '../../includes/header.php';
include '../../includes/navbar.php';
include '../../includes/sidebar.php';
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="bi bi-cart"></i> Reporte de Pedidos</h4>
            <div>
                <a href="pedidos.php?<?= http_build_query($_GET) ?>&formato=pdf" class="btn btn-danger btn-sm me-2">
                    <i class="bi bi-file-earmark-pdf"></i> PDF
                </a>
                <a href="pedidos.php?<?= http_build_query($_GET) ?>&formato=excel" class="btn btn-success btn-sm">
                    <i class="bi bi-file-earmark-excel"></i> Excel
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="get" class="mb-4">
                <input type="hidden" name="formato" value="web">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Desde</label>
                        <input type="date" name="desde" class="form-control" value="<?= $desde ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Hasta</label>
                        <input type="date" name="hasta" class="form-control" value="<?= $hasta ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-select">
                            <option value="todos">Todos los estados</option>
                            <option value="pendiente" <?= $estado === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                            <option value="completado" <?= $estado === 'completado' ? 'selected' : '' ?>>Completado</option>
                            <option value="cancelado" <?= $estado === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Cliente</label>
                        <select name="cliente_id" class="form-select">
                            <option value="">Todos los clientes</option>
                            <?php foreach ($clientes as $cliente): ?>
                            <option value="<?= $cliente['id'] ?>" <?= $cliente_id == $cliente['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cliente['nombre']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-12 text-end">
                        <button type="submit" class="btn btn-success me-2">
                            <i class="bi bi-funnel"></i> Filtrar
                        </button>
                        <a href="pedidos.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-counterclockwise"></i> Limpiar
                        </a>
                    </div>
                </div>
            </form>

            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-graph-up"></i> Resumen</h5>
                            <div class="row">
                                <?php foreach ($totales as $total): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="card border-<?= 
                                        $total['estado'] == 'completado' ? 'success' : 
                                        ($total['estado'] == 'pendiente' ? 'warning' : 'danger')
                                    ?>">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h6 class="card-subtitle mb-1"><?= ucfirst($total['estado']) ?></h6>
                                                    <p class="card-text h4">₡<?= number_format($total['total'], 2) ?></p>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-<?= 
                                                        $total['estado'] == 'completado' ? 'success' : 
                                                        ($total['estado'] == 'pendiente' ? 'warning' : 'danger')
                                                    ?>">
                                                        <?= $total['cantidad'] ?> pedidos
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                
                                <div class="col-md-4 mb-3">
                                    <div class="card border-dark">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h6 class="card-subtitle mb-1">Total General</h6>
                                                    <p class="card-text h4">₡<?= number_format($total_general, 2) ?></p>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-dark">
                                                        <?= array_sum(array_column($totales, 'cantidad')) ?> pedidos
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Entrega</th>
                            <th>Registrado por</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pedidos)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">No se encontraron pedidos con los filtros seleccionados</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($pedidos as $pedido): ?>
                        <tr>
                            <td><?= $pedido['id'] ?></td>
                            <td><?= formatearFecha($pedido['fecha'], 'd/m/Y') ?></td>
                            <td><?= htmlspecialchars($pedido['cliente_nombre']) ?></td>
                            <td class="fw-bold">₡<?= number_format($pedido['total'], 2) ?></td>
                            <td>
                                <span class="badge bg-<?= 
                                    $pedido['estado'] == 'completado' ? 'success' : 
                                    ($pedido['estado'] == 'pendiente' ? 'warning' : 'danger')
                                ?>">
                                    <?= ucfirst($pedido['estado']) ?>
                                </span>
                            </td>
                            <td><?= !empty($pedido['fecha_entrega']) ? formatearFecha($pedido['fecha_entrega'], 'd/m/Y') : 'N/A' ?></td>
                            <td><?= htmlspecialchars($pedido['usuario_nombre']) ?></td>
                            <td>
                                <a href="../pedidos/detalle.php?id=<?= $pedido['id'] ?>" class="btn btn-sm btn-outline-primary" title="Ver detalles">
                                    <i class="bi bi-eye"></i>
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

<?php 
$js_especial = "reportes.js";
include '../../includes/footer.php'; 
?>