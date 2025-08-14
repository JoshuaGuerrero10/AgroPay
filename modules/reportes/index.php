<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

protegerPagina('admin,vendedor');

$conn = abrirConexion();

$total_clientes = $conn->query("SELECT COUNT(*) as total FROM clientes WHERE estado = 'activo'")->fetch_assoc()['total'];

$total_pagos = $conn->query("SELECT IFNULL(SUM(monto), 0) as total FROM pagos WHERE fecha BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01') AND LAST_DAY(CURDATE())")->fetch_assoc()['total'];

$total_pedidos_pendientes = $conn->query("SELECT COUNT(*) as total FROM pedidos WHERE estado = 'pendiente'")->fetch_assoc()['total'];

cerrarConexion($conn);

$titulo_pagina = "Reportes";
$css_especial = "reportes.css";
include '../../includes/header.php';
include '../../includes/navbar.php';
include '../../includes/sidebar.php';
?>

<main class="flex-grow-1 py-4">
    <div class="container">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-graph-up"></i> Reportes</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Tarjeta de Reporte de Pagos -->
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 border-primary">
                            <div class="card-body text-center">
                                <i class="bi bi-cash-coin fs-1 text-primary mb-3"></i>
                                <h5 class="card-title">Reporte de Pagos</h5>
                                <p class="card-text">Total del mes: ₡<?= number_format($total_pagos, 2) ?></p>
                                <div class="mt-3">
                                    <a href="pagos.php" class="btn btn-primary me-2">
                                        <i class="bi bi-eye"></i> Ver
                                    </a>
                                    <a href="pagos.php?formato=pdf" class="btn btn-danger">
                                        <i class="bi bi-file-earmark-pdf"></i> PDF
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 border-success">
                            <div class="card-body text-center">
                                <i class="bi bi-cart fs-1 text-success mb-3"></i>
                                <h5 class="card-title">Reporte de Pedidos</h5>
                                <p class="card-text">Pendientes: <?= $total_pedidos_pendientes ?></p>
                                <div class="mt-3">
                                    <a href="pedidos.php" class="btn btn-success me-2">
                                        <i class="bi bi-eye"></i> Ver
                                    </a>
                                    <a href="pedidos.php?formato=excel" class="btn btn-success">
                                        <i class="bi bi-file-earmark-excel"></i> Excel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 border-warning">
                            <div class="card-body text-center">
                                <i class="bi bi-coin fs-1 text-warning mb-3"></i>
                                <h5 class="card-title">Reporte de Créditos</h5>
                                <p class="card-text">Clientes activos: <?= $total_clientes ?></p>
                                <div class="mt-3">
                                    <a href="creditos.php" class="btn btn-warning me-2">
                                        <i class="bi bi-eye"></i> Ver
                                    </a>
                                    <div class="dropdown d-inline-block">
                                        <button class="btn btn-warning dropdown-toggle" type="button" id="dropdownExport" data-bs-toggle="dropdown">
                                            <i class="bi bi-download"></i> Exportar
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="creditos.php?formato=pdf">PDF</a></li>
                                            <li><a class="dropdown-item" href="creditos.php?formato=excel">Excel</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Reportes Rápidos -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="bi bi-lightning"></i> Reportes Rápidos</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <a href="pagos.php?desde=<?= date('Y-m-01') ?>&hasta=<?= date('Y-m-d') ?>" class="btn btn-outline-primary w-100">
                                            Pagos del Mes
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="pedidos.php?estado=pendiente" class="btn btn-outline-warning w-100">
                                            Pedidos Pendientes
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="creditos.php?estado=vencidos" class="btn btn-outline-danger w-100">
                                            Créditos Vencidos
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="pedidos.php?desde=<?= date('Y-m-d', strtotime('-7 days')) ?>&hasta=<?= date('Y-m-d') ?>" class="btn btn-outline-info w-100">
                                            Últimos 7 Días
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php 
$js_especial = "reportes.js";
include '../../includes/footer.php'; 
?>