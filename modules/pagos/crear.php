<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

protegerPagina('admin,vendedor');

$clientes = ejecutarConsulta("
    SELECT c.id, c.nombre 
    FROM clientes c
    JOIN creditos cr ON c.id = cr.cliente_id
    WHERE c.estado = 'activo' AND cr.estado = 'activo'
    ORDER BY c.nombre
");

if (empty($clientes)) {
    redirigirConMensaje('index.php', 'error', 'No hay clientes con créditos activos disponibles');
}

$titulo_pagina = "Registrar Nuevo Pago";
include '../../includes/header.php';
include '../../includes/navbar.php';
include '../../includes/sidebar.php';
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0"><i class="bi bi-cash-coin"></i> Registrar Pago</h4>
        </div>
        <div class="card-body">
            <form id="formPago" action="procesar.php" method="POST">
                <input type="hidden" name="action" value="crear">
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Cliente *</label>
                        <select name="cliente_id" class="form-select" required id="selectCliente">
                            <option value="">Seleccionar cliente...</option>
                            <?php foreach ($clientes as $cliente): ?>
                            <option value="<?= $cliente['id'] ?>"><?= htmlspecialchars($cliente['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Fecha *</label>
                        <input type="date" name="fecha" class="form-control" required 
                               value="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Monto (₡) *</label>
                        <input type="text" name="monto" class="form-control" required 
                               id="inputMonto" oninput="formatCurrency(this)">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Método de Pago *</label>
                        <select name="metodo" class="form-select" required>
                            <option value="efectivo">Efectivo</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Referencia</label>
                        <input type="text" name="referencia" class="form-control">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Notas</label>
                    <textarea name="notas" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0">Aplicar Pago a Pedidos Pendientes</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table" id="tablaPedidos">
                                <thead>
                                    <tr>
                                        <th>Pedido ID</th>
                                        <th>Fecha</th>
                                        <th>Total</th>
                                        <th>Saldo Pendiente</th>
                                        <th>Monto a Aplicar</th>
                                    </tr>
                                </thead>
                                <tbody id="pedidosBody">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end">
                    <a href="index.php" class="btn btn-secondary me-2">Cancelar</a>
                    <button type="submit" class="btn btn-success" id="btnSubmit">
                        <span id="submitText">Registrar Pago</span>
                        <span id="submitSpinner" class="spinner-border spinner-border-sm d-none"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function formatCurrency(input) {
    let value = input.value.replace(/[^0-9]/g, '');
    if (value) {
        value = parseFloat(value).toLocaleString('es-CR');
    }
    input.value = value;
}

document.getElementById('selectCliente').addEventListener('change', function() {
    const clienteId = this.value;
    if (!clienteId) return;
    
    fetch(`../../api/pedidos_pendientes.php?cliente_id=${clienteId}`)
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('pedidosBody');
            tbody.innerHTML = '';
            
            data.forEach(pedido => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${pedido.id}</td>
                    <td>${pedido.fecha}</td>
                    <td>₡${pedido.total.toLocaleString('es-CR', {minimumFractionDigits: 2})}</td>
                    <td>₡${pedido.saldo_pendiente.toLocaleString('es-CR', {minimumFractionDigits: 2})}</td>
                    <td>
                        <input type="number" name="pedidos[${pedido.id}]" class="form-control monto-aplicar" 
                               min="0" max="${pedido.saldo_pendiente}" step="0.01"
                               onchange="calcularTotalAplicado()">
                    </td>
                `;
                tbody.appendChild(tr);
            });
        });
});

document.getElementById('formPago').addEventListener('submit', function(e) {
    const btn = document.getElementById('btnSubmit');
    btn.disabled = true;
    document.getElementById('submitText').classList.add('d-none');
    document.getElementById('submitSpinner').classList.remove('d-none');
    
});
</script>

<?php include '../../includes/footer.php'; ?>