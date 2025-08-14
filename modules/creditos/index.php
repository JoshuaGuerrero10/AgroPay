<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

protegerPagina('admin,vendedor'); // Solo estos roles pueden acceder

$tab = $_GET['tab'] ?? 'activos';
$cliente_id = $_GET['cliente_id'] ?? null;
$vencimiento = $_GET['vencimiento'] ?? null;

switch ($tab) {
    case 'vencidos':
        $sql = "SELECT c.*, cl.nombre as cliente_nombre, 
                DATEDIFF(NOW(), c.fecha_vencimiento) as dias_vencido
                FROM creditos c
                JOIN clientes cl ON c.cliente_id = cl.id
                WHERE c.estado = 'vencido'
                ORDER BY c.fecha_vencimiento ASC";
        break;
    case 'historial':
        $sql = "SELECT c.*, cl.nombre as cliente_nombre,
                u.nombre as usuario_nombre
                FROM creditos c
                JOIN clientes cl ON c.cliente_id = cl.id
                JOIN usuarios u ON c.usuario_id = u.id
                ORDER BY c.fecha_creacion DESC";
        break;
    case 'activos':
    default:
        $sql = "SELECT c.*, cl.nombre as cliente_nombre 
                FROM creditos c
                JOIN clientes cl ON c.cliente_id = cl.id
                WHERE c.estado = 'activo'
                ORDER BY c.fecha_vencimiento ASC";
        break;
}

if ($cliente_id) {
    $sql .= strpos($sql, 'WHERE') === false ? ' WHERE ' : ' AND ';
    $sql .= "c.cliente_id = " . intval($cliente_id);
}

if ($vencimiento) {
    $sql .= strpos($sql, 'WHERE') === false ? ' WHERE ' : ' AND ';
    $sql .= "c.fecha_vencimiento <= '" . date('Y-m-d', strtotime($vencimiento)) . "'";
}

$creditos = ejecutarConsulta($sql);

$clientes = ejecutarConsulta("SELECT id, nombre FROM clientes WHERE estado = 'activo' ORDER BY nombre");

$titulo_pagina = "Administración de Créditos";
$css_extra = "creditos.css";
include '../../includes/header.php';
include '../../includes/navbar.php';
include '../../includes/sidebar.php';
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="bi bi-coin"></i> Gestión de Créditos</h4>
        </div>
        <div class="card-body">
            <form method="get" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <select name="tab" class="form-select">
                            <option value="activos" <?= $tab === 'activos' ? 'selected' : '' ?>>Créditos Activos</option>
                            <option value="vencidos" <?= $tab === 'vencidos' ? 'selected' : '' ?>>Créditos Vencidos</option>
                            <option value="historial" <?= $tab === 'historial' ? 'selected' : '' ?>>Historial</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select name="cliente_id" class="form-select">
                            <option value="">Todos los clientes</option>
                            <?php foreach ($clientes as $cliente): ?>
                            <option value="<?= $cliente['id'] ?>" <?= $cliente_id == $cliente['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cliente['nombre']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                    </div>
                    <div class="col-md-2">
                        <a href="crear.php" class="btn btn-success w-100">
                            <i class="bi bi-plus-lg"></i> Nuevo Crédito
                        </a>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-striped">
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
                            <td colspan="7" class="text-center py-4">No se encontraron créditos</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($creditos as $credito): ?>
                        <tr>
                            <td><?= htmlspecialchars($credito['cliente_nombre']) ?></td>
                            <td>₡<?= number_format($credito['limite'], 2) ?></td>
                            <td>₡<?= number_format($credito['utilizado'], 2) ?></td>
                            <td>₡<?= number_format($credito['limite'] - $credito['utilizado'], 2) ?></td>
                            <td><?= formatearFecha($credito['fecha_vencimiento']) ?></td>
                            <td>
                                <span class="badge bg-<?= 
                                    $credito['estado'] == 'activo' ? 'success' : 
                                    ($credito['estado'] == 'vencido' ? 'danger' : 'warning')
                                ?>">
                                    <?= ucfirst($credito['estado']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="editar.php?id=<?= $credito['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="procesar.php?action=eliminar&id=<?= $credito['id'] ?>" 
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('¿Está seguro de eliminar este crédito?')">
                                    <i class="bi bi-trash"></i>
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
$js_extra = "creditos.js";
include '../../includes/footer.php'; 
?>