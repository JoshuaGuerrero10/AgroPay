<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

protegerPagina();

$titulo_pagina = "Panel de Control";
include 'includes/header.php';
include 'includes/navbar.php';
include 'includes/sidebar.php';

$conn = abrirConexion();
$total_clientes = $conn->query("SELECT COUNT(*) FROM clientes")->fetch_row()[0];
$total_pedidos = $conn->query("SELECT COUNT(*) FROM pedidos WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)")->fetch_row()[0];
$pagos_pendientes = $conn->query("SELECT SUM(total) FROM pedidos WHERE estado = 'pendiente'")->fetch_row()[0];
cerrarConexion($conn);
?>

<main class="flex-grow-1 py-4">
    <div class="container">
        <h2 class="mb-4"><i class="bi bi-house"></i> Panel de Control</h2>
        
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-center border-primary">
                    <div class="card-body">
                        <i class="bi bi-people fs-1 text-primary mb-3"></i>
                        <h5 class="card-title">Clientes Activos</h5>
                        <h2 class="text-primary"><?= $total_clientes ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center border-success">
                    <div class="card-body">
                        <i class="bi bi-cart-check fs-1 text-success mb-3"></i>
                        <h5 class="card-title">Pedidos Este Mes</h5>
                        <h2 class="text-success"><?= $total_pedidos ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center border-warning">
                    <div class="card-body">
                        <i class="bi bi-cash-coin fs-1 text-warning mb-3"></i>
                        <h5 class="card-title">Pagos Pendientes</h5>
                        <h2 class="text-warning">₡<?= number_format($pagos_pendientes, 2) ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5>Pedidos Recientes</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Cliente</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $conn = abrirConexion();
                                    $pedidos = $conn->query("
                                        SELECT p.id, c.nombre as cliente, p.total, p.estado 
                                        FROM pedidos p
                                        JOIN clientes c ON p.cliente_id = c.id
                                        ORDER BY p.fecha DESC LIMIT 5
                                    ");
                                    
                                    while ($pedido = $pedidos->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td><?= $pedido['id'] ?></td>
                                        <td><?= htmlspecialchars($pedido['cliente']) ?></td>
                                        <td>₡<?= number_format($pedido['total'], 2) ?></td>
                                        <td>
                                            <span class="badge bg-<?= 
                                                $pedido['estado'] == 'completado' ? 'success' : 
                                                ($pedido['estado'] == 'pendiente' ? 'warning' : 'danger') 
                                            ?>">
                                                <?= ucfirst($pedido['estado']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endwhile; cerrarConexion($conn); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5>Próximos Vencimientos</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            <?php
                            $conn = abrirConexion();
                            $vencimientos = $conn->query("
                                SELECT c.nombre, cr.fecha_vencimiento, cr.limite - cr.utilizado as saldo
                                FROM creditos cr
                                JOIN clientes c ON cr.cliente_id = c.id
                                WHERE cr.estado = 'activo'
                                ORDER BY cr.fecha_vencimiento ASC LIMIT 3
                            ");
                            
                            while ($vencimiento = $vencimientos->fetch_assoc()):
                            ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1"><?= htmlspecialchars($vencimiento['nombre']) ?></h6>
                                    <small class="text-muted">Vence: <?= date('d/m/Y', strtotime($vencimiento['fecha_vencimiento'])) ?></small>
                                </div>
                                <span class="badge bg-warning rounded-pill">₡<?= number_format($vencimiento['saldo'], 2) ?></span>
                            </li>
                            <?php endwhile; cerrarConexion($conn); ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>