<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

protegerPagina('admin,vendedor');

$estado = $_GET['estado'] ?? 'activos';
$cliente_id = $_GET['cliente_id'] ?? '';
$vencimiento = $_GET['vencimiento'] ?? '';
$formato = $_GET['formato'] ?? 'web';

$sql = "SELECT c.*, cl.nombre as cliente_nombre 
        FROM creditos c
        JOIN clientes cl ON c.cliente_id = cl.id";
$params = [];

switch ($estado) {
    case 'vencidos':
        $sql .= " WHERE c.estado = 'vencido'";
        break;
    case 'activos':
        $sql .= " WHERE c.estado = 'activo'";
        break;
    case 'suspendidos':
        $sql .= " WHERE c.estado = 'suspendido'";
        break;
    case 'todos':
    default:
        $sql .= " WHERE 1=1";
        break;
}

if (!empty($cliente_id)) {
    $sql .= " AND c.cliente_id = ?";
    $params[] = $cliente_id;
}

if (!empty($vencimiento)) {
    $sql .= " AND c.fecha_vencimiento <= ?";
    $params[] = $vencimiento;
}

$sql .= " ORDER BY c.fecha_vencimiento ASC";
$creditos = ejecutarConsulta($sql, $params);

$sql_totales = "SELECT 
                  estado,
                  COUNT(*) as cantidad,
                  SUM(limite) as total_limite,
                  SUM(utilizado) as total_utilizado
                FROM creditos";
$params_totales = [];

if ($estado !== 'todos') {
    $sql_totales .= " WHERE estado = ?";
    $params_totales[] = $estado;
}

if (!empty($cliente_id)) {
    $sql_totales .= (strpos($sql_totales, 'WHERE') === false ? ' WHERE ' : ' AND ');
    $sql_totales .= " cliente_id = ?";
    $params_totales[] = $cliente_id;
}

$sql_totales .= " GROUP BY estado";
$totales = ejecutarConsulta($sql_totales, $params_totales);

$total_limite = array_sum(array_column($totales, 'total_limite'));
$total_utilizado = array_sum(array_column($totales, 'total_utilizado'));
$total_disponible = $total_limite - $total_utilizado;

if ($formato !== 'web') {
    require_once '../../includes/reportes/exportar.php';
    exportarReporte($formato, 'Reporte de Créditos', [
        'estado' => $estado,
        'cliente_id' => $cliente_id,
        'vencimiento' => $vencimiento
    ], $creditos, $totales);
    exit;
}

$clientes = ejecutarConsulta("SELECT id, nombre FROM clientes ORDER BY nombre");

$titulo_pagina = "Reporte de Créditos";
$css_especial = "reportes.css";
include '../../includes/header.php';
include '../../includes/navbar.php';
include '../../includes/sidebar.php';
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-warning text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="bi bi-coin"></i> Reporte de Créditos</h4>
            <div>
                <a href="creditos.php?<?= http_build_query($_GET) ?>&formato=pdf" class="btn btn-danger btn-sm me-2">
                    <i class="bi bi-file-earmark-pdf"></i> PDF
                </a>
                <a href="creditos.php?<?= http_build_query($_GET) ?>&formato=excel" class="btn btn-success btn-sm">
                    <i class="bi bi-file-earmark-excel"></i> Excel
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="get" class="mb-4">
                <input type="hidden" name="formato" value="web">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-select">
                            <option value="todos">Todos los estados</option>
                            <option value="activos" <?= $estado === 'activos' ? 'selected' : '' ?>>Activos</option>
                            <option value="vencidos" <?= $estado === 'vencidos' ? 'selected' : '' ?>>Vencidos</option>
                            <option value="suspendidos" <?= $estado === 'suspendidos' ? 'selected' : '' ?>>Suspendidos</option>
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
                    <div class="col-md-3">
                        <label class="form-label">Vence antes de</label>
                        <input type="date" name="vencimiento" class="form-control" value="<?= $vencimiento ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex">
                            <button type="submit" class="btn btn-warning me-2 flex-grow-1">
                                <i class="bi bi-funnel"></i> Filtrar
                            </button>
                            <a href="creditos.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </a>
                        </div>
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
                                        $total['estado'] == 'activo' ? 'success' : 
                                        ($total['estado'] == 'vencido' ? 'danger' : 'warning')
                                    ?>">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h6 class="card-subtitle mb-1"><?= ucfirst($total['estado']) ?></h6>
                                                    <p class="card-text h4">₡<?= number_format($total['total_limite'], 2) ?></p>
                                                    <small class="text-muted">Utilizado: ₡<?= number_format($total['total_utilizado'], 2) ?></small>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-<?= 
                                                        $total['estado'] == 'activo' ? 'success' : 
                                                        ($total['estado'] == 'vencido' ? 'danger' : 'warning')
                                                    ?>">
                                                        <?= $total['cantidad'] ?> créditos
                                                    </span>
                                                    <div class="mt-2">
                                                        <small class="text-muted">Disponible:</small>
                                                        <p class="h5 mb-0">₡<?= number_format($total['total_limite'] - $total['total_utilizado'], 2) ?></p>
                                                    </div>
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
                                                    <p class="card-text h4">₡<?= number_format($total_limite, 2) ?></p>
                                                    <small class="text-muted">Utilizado: ₡<?= number_format($total_utilizado, 2) ?></small>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-dark">
                                                        <?= array_sum(array_column($totales, 'cantidad')) ?> créditos
                                                    </span>
                                                    <div class="mt-2">
                                                        <small class="text-muted">Disponible:</small>
                                                        <p class="h5 mb-0">₡<?= number_format($total_disponible, 2) ?></p>
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
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Cliente</th>
                            <th>Límite</th>
                            <th>Utilizado</th>
                            <th>Disponible</th>
                            <th>Vencimiento</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($creditos)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">No se encontraron créditos con los filtros seleccionados</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($creditos as $credito): ?>
                        <tr>
                            <td><?= htmlspecialchars($credito['cliente_nombre']) ?></td>
                            <td>₡<?= number_format($credito['limite'], 2) ?></td>
                            <td>₡<?= number_format($credito['utilizado'], 2) ?></td>
                            <td class="fw-bold">₡<?= number_format($credito['limite'] - $credito['utilizado'], 2) ?></td>
                            <td><?= formatearFecha($credito['fecha_vencimiento'], 'd/m/Y') ?></td>
                            <td>
                                <span class="badge bg-<?= 
                                    $credito['estado'] == 'activo' ? 'success' : 
                                    ($credito['estado'] == 'vencido' ? 'danger' : 'warning')
                                ?>">
                                    <?= ucfirst($credito['estado']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="../creditos/editar.php?id=<?= $credito['id'] ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                                    <i class="bi bi-pencil"></i>
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