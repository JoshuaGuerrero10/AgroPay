<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

protegerPagina('admin,vendedor');

$desde = isset($_GET['desde']) ? $_GET['desde'] : date('Y-m-01');
$hasta = isset($_GET['hasta']) ? $_GET['hasta'] : date('Y-m-d');
$metodo = isset($_GET['metodo']) ? $_GET['metodo'] : 'todos';
$cliente_id = isset($_GET['cliente_id']) ? $_GET['cliente_id'] : '';
$formato = isset($_GET['formato']) ? $_GET['formato'] : 'web';

if (!validarFecha($desde) || !validarFecha($hasta)) {
    $desde = date('Y-m-01');
    $hasta = date('Y-m-d');
}

$sql = "SELECT p.*, c.nombre as cliente_nombre, u.nombre as usuario_nombre 
        FROM pagos p
        JOIN clientes c ON p.cliente_id = c.id
        JOIN usuarios u ON p.usuario_id = u.id
        WHERE p.fecha BETWEEN ? AND ?";
$params = [$desde, $hasta];

if ($metodo !== 'todos') {
    $sql .= " AND p.metodo = ?";
    $params[] = $metodo;
}

if (!empty($cliente_id)) {
    $sql .= " AND p.cliente_id = ?";
    $params[] = $cliente_id;
}

$sql .= " ORDER BY p.fecha DESC";
$pagos = ejecutarConsulta($sql, $params);

$sql_totales = "SELECT 
                  metodo, 
                  COUNT(*) as cantidad,
                  SUM(monto) as total
                FROM pagos
                WHERE fecha BETWEEN ? AND ?";
$params_totales = [$desde, $hasta];

if ($metodo !== 'todos') {
    $sql_totales .= " AND metodo = ?";
    $params_totales[] = $metodo;
}

if (!empty($cliente_id)) {
    $sql_totales .= " AND cliente_id = ?";
    $params_totales[] = $cliente_id;
}

$sql_totales .= " GROUP BY metodo";
$totales = ejecutarConsulta($sql_totales, $params_totales);

$total_general = array_sum(array_column($totales, 'total'));

if ($formato !== 'web') {
    require_once __DIR__.'/../../includes/reportes/exportar.php';
    exportarReporte($formato, 'Reporte de Pagos', [
        'desde' => $desde,
        'hasta' => $hasta,
        'metodo' => $metodo,
        'cliente_id' => $cliente_id
    ], $pagos, $totales);
    exit;
}

$clientes = ejecutarConsulta("SELECT id, nombre FROM clientes ORDER BY nombre");

$titulo_pagina = "Reporte de Pagos";
$css_especial = "reportes.css";
include '../../includes/header.php';
include '../../includes/navbar.php';
include '../../includes/sidebar.php';
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="bi bi-cash-coin"></i> Reporte de Pagos</h4>
            <div>
                <a href="pagos.php?<?= http_build_query($_GET) ?>&formato=pdf" class="btn btn-danger btn-sm me-2">
                    <i class="bi bi-file-earmark-pdf"></i> PDF
                </a>
                <a href="pagos.php?<?= http_build_query($_GET) ?>&formato=excel" class="btn btn-success btn-sm">
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
                        <label class="form-label">Método</label>
                        <select name="metodo" class="form-select">
                            <option value="todos">Todos los métodos</option>
                            <option value="efectivo" <?= $metodo === 'efectivo' ? 'selected' : '' ?>>Efectivo</option>
                            <option value="transferencia" <?= $metodo === 'transferencia' ? 'selected' : '' ?>>Transferencia</option>
                            <option value="cheque" <?= $metodo === 'cheque' ? 'selected' : '' ?>>Cheque</option>
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
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-funnel"></i> Filtrar
                        </button>
                        <a href="pagos.php" class="btn btn-outline-secondary">
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
                                        $total['metodo'] == 'efectivo' ? 'success' : 
                                        ($total['metodo'] == 'transferencia' ? 'primary' : 'info')
                                    ?>">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h6 class="card-subtitle mb-1"><?= ucfirst($total['metodo']) ?></h6>
                                                    <p class="card-text h4">₡<?= number_format($total['total'], 2) ?></p>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-<?= 
                                                        $total['metodo'] == 'efectivo' ? 'success' : 
                                                        ($total['metodo'] == 'transferencia' ? 'primary' : 'info')
                                                    ?>">
                                                        <?= $total['cantidad'] ?> transacciones
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
                                                        <?= array_sum(array_column($totales, 'cantidad')) ?> transacciones
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
                            <th>Monto</th>
                            <th>Método</th>
                            <th>Referencia</th>
                            <th>Registrado por</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pagos)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">No se encontraron pagos con los filtros seleccionados</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($pagos as $pago): ?>
                        <tr>
                            <td><?= $pago['id'] ?></td>
                            <td><?= formatearFecha($pago['fecha'], 'd/m/Y') ?></td>
                            <td><?= htmlspecialchars($pago['cliente_nombre']) ?></td>
                            <td class="fw-bold">₡<?= number_format($pago['monto'], 2) ?></td>
                            <td>
                                <span class="badge bg-<?= 
                                    $pago['metodo'] == 'efectivo' ? 'success' : 
                                    ($pago['metodo'] == 'transferencia' ? 'primary' : 'info')
                                ?>">
                                    <?= ucfirst($pago['metodo']) ?>
                                </span>
                            </td>
                            <td><?= !empty($pago['referencia']) ? htmlspecialchars($pago['referencia']) : 'N/A' ?></td>
                            <td><?= htmlspecialchars($pago['usuario_nombre']) ?></td>
                            <td>
                                <a href="../pagos/ver.php?id=<?= $pago['id'] ?>" class="btn btn-sm btn-outline-primary" title="Ver detalles">
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