<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

protegerPagina('admin,vendedor');

$pago_id = intval($_GET['id'] ?? 0);

if ($pago_id <= 0) {
    redirigirConMensaje('index.php', 'error', 'ID de pago inválido');
}

$sql_pago = "SELECT p.*, c.nombre as cliente_nombre, u.nombre as usuario_nombre
             FROM pagos p
             JOIN clientes c ON p.cliente_id = c.id
             JOIN usuarios u ON p.usuario_id = u.id
             WHERE p.id = ?";
$pago = obtenerUnRegistro($sql_pago, [$pago_id]);

if (!$pago) {
    redirigirConMensaje('index.php', 'error', 'Pago no encontrado');
}

$sql_pedidos = "SELECT pp.*, pe.fecha as pedido_fecha, pe.total as pedido_total
                FROM pago_pedidos pp
                JOIN pedidos pe ON pp.pedido_id = pe.id
                WHERE pp.pago_id = ?";
$pedidos = ejecutarConsulta($sql_pedidos, [$pago_id]);

$titulo_pagina = "Detalle de Pago #".$pago['id'];
include '../../includes/header.php';
include '../../includes/navbar.php';
include '../../includes/sidebar.php';
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="bi bi-cash-coin"></i> Detalle de Pago #<?= $pago['id'] ?></h4>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5>Información del Pago</h5>
                    <table class="table table-bordered">
                        <tr>
                            <th>Fecha:</th>
                            <td><?= formatearFecha($pago['fecha'], 'd/m/Y') ?></td>
                        </tr>
                        <tr>
                            <th>Cliente:</th>
                            <td><?= htmlspecialchars($pago['cliente_nombre']) ?></td>
                        </tr>
                        <tr>
                            <th>Monto:</th>
                            <td>₡<?= number_format($pago['monto'], 2) ?></td>
                        </tr>
                        <tr>
                            <th>Método:</th>
                            <td><?= ucfirst($pago['metodo']) ?></td>
                        </tr>
                        <tr>
                            <th>Referencia:</th>
                            <td><?= htmlspecialchars($pago['referencia'] ?? 'N/A') ?></td>
                        </tr>
                        <tr>
                            <th>Registrado por:</th>
                            <td><?= htmlspecialchars($pago['usuario_nombre']) ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h5>Notas</h5>
                    <div class="border p-3 bg-light">
                        <?= !empty($pago['notas']) ? nl2br(htmlspecialchars($pago['notas'])) : 'Sin notas' ?>
                    </div>
                </div>
            </div>

            <?php if (!empty($pedidos)): ?>
            <h5>Pedidos Aplicados</h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Pedido ID</th>
                            <th>Fecha</th>
                            <th>Total Pedido</th>
                            <th>Monto Aplicado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedidos as $pedido): ?>
                        <tr>
                            <td><?= $pedido['pedido_id'] ?></td>
                            <td><?= formatearFecha($pedido['pedido_fecha'], 'd/m/Y') ?></td>
                            <td>₡<?= number_format($pedido['pedido_total'], 2) ?></td>
                            <td>₡<?= number_format($pedido['monto_aplicado'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <div class="d-flex justify-content-end mt-3">
                <a href="imprimir.php?id=<?= $pago['id'] ?>" class="btn btn-outline-primary me-2">
                    <i class="bi bi-printer"></i> Imprimir Recibo
                </a>
                <a href="index.php" class="btn btn-primary">
                    <i class="bi bi-list"></i> Volver al Listado
                </a>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>