<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

protegerPagina('admin,vendedor');

$pedido_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($pedido_id <= 0) {
    redirigirConMensaje('index.php', 'error', 'ID de pedido inválido');
}

// Obtener información del pedido
$pedido = obtenerUnRegistro(
    "SELECT p.*, c.nombre as cliente_nombre, u.nombre as usuario_nombre 
     FROM pedidos p 
     JOIN clientes c ON p.cliente_id = c.id
     JOIN usuarios u ON p.usuario_id = u.id
     WHERE p.id = ?",
    [$pedido_id]
);

if (!$pedido) {
    redirigirConMensaje('index.php', 'error', 'Pedido no encontrado');
}

// Obtener items del pedido
$items = ejecutarConsulta(
    "SELECT pi.*, pr.nombre as producto_nombre 
     FROM pedido_items pi
     JOIN productos pr ON pi.producto_id = pr.id
     WHERE pi.pedido_id = ?",
    [$pedido_id]
);

$titulo_pagina = "Pedido #".$pedido_id;
include '../../includes/header.php';
include '../../includes/navbar.php';
include '../../includes/sidebar.php';
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="bi bi-cart"></i> Pedido #<?= $pedido_id ?></h4>
            <div>
                <a href="editar.php?id=<?= $pedido_id ?>" class="btn btn-warning btn-sm me-2">
                    <i class="bi bi-pencil"></i> Editar
                </a>
                <a href="index.php" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5>Información del Pedido</h5>
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">Cliente:</th>
                            <td><?= htmlspecialchars($pedido['cliente_nombre']) ?></td>
                        </tr>
                        <tr>
                            <th>Fecha:</th>
                            <td><?= formatearFecha($pedido['fecha']) ?></td>
                        </tr>
                        <tr>
                            <th>Fecha Entrega:</th>
                            <td><?= $pedido['fecha_entrega'] ? formatearFecha($pedido['fecha_entrega']) : 'No especificada' ?></td>
                        </tr>
                        <tr>
                            <th>Estado:</th>
                            <td>
                                <span class="badge bg-<?= 
                                    $pedido['estado'] == 'completado' ? 'success' : 
                                    ($pedido['estado'] == 'pendiente' ? 'warning' : 'danger')
                                ?>">
                                    <?= ucfirst($pedido['estado']) ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Total:</th>
                            <td class="fw-bold">₡<?= number_format($pedido['total'], 2) ?></td>
                        </tr>
                        <tr>
                            <th>Registrado por:</th>
                            <td><?= htmlspecialchars($pedido['usuario_nombre']) ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h5>Notas</h5>
                    <div class="border p-3 bg-light">
                        <?= !empty($pedido['notas']) ? nl2br(htmlspecialchars($pedido['notas'])) : 'Sin notas' ?>
                    </div>
                </div>
            </div>

            <h5>Productos</h5>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th class="text-end">Cantidad</th>
                            <th class="text-end">Precio Unitario</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['producto_nombre']) ?></td>
                            <td class="text-end"><?= $item['cantidad'] ?></td>
                            <td class="text-end">₡<?= number_format($item['precio_unitario'], 2) ?></td>
                            <td class="text-end">₡<?= number_format($item['subtotal'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="table-primary fw-bold">
                            <td colspan="3" class="text-end">Total:</td>
                            <td class="text-end">₡<?= number_format($pedido['total'], 2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>