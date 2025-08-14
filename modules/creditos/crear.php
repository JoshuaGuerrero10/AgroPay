<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

protegerPagina('admin,vendedor');

$clientes = ejecutarConsulta("SELECT id, nombre FROM clientes WHERE estado = 'activo' ORDER BY nombre");

if (empty($clientes)) {
    redirigirConMensaje('index.php', 'error', 'No hay clientes activos disponibles para asignar créditos');
}

$titulo_pagina = "Nuevo Crédito";
include '../../includes/header.php';
include '../../includes/navbar.php';
include '../../includes/sidebar.php';
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0"><i class="bi bi-plus-lg"></i> Registrar Nuevo Crédito</h4>
        </div>
        <div class="card-body">
            <form action="procesar.php" method="POST" id="formCredito" onsubmit="return validarFormulario()">
                <input type="hidden" name="action" value="crear">
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Cliente *</label>
                        <select name="cliente_id" class="form-select" required id="selectCliente">
                            <option value="">Seleccionar cliente...</option>
                            <?php foreach ($clientes as $cliente): ?>
                            <option value="<?= $cliente['id'] ?>">
                                <?= htmlspecialchars($cliente['nombre']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback" id="clienteFeedback">Debe seleccionar un cliente</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Límite de Crédito (₡) *</label>
                        <input type="text" name="limite" class="form-control" id="inputLimite" required
                               oninput="formatCurrency(this)">
                        <div class="invalid-feedback" id="limiteFeedback">El límite debe ser mayor a cero</div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Fecha de Vencimiento *</label>
                        <input type="date" name="fecha_vencimiento" class="form-control" required
                               min="<?= date('Y-m-d') ?>" id="inputVencimiento">
                        <div class="invalid-feedback" id="vencimientoFeedback">Fecha inválida</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-select">
                            <option value="activo" selected>Activo</option>
                            <option value="suspendido">Suspendido</option>
                        </select>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Notas</label>
                    <textarea name="notas" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="d-flex justify-content-end">
                    <a href="index.php" class="btn btn-secondary me-2">Cancelar</a>
                    <button type="submit" class="btn btn-success" id="btnSubmit">
                        <span id="submitText">Guardar Crédito</span>
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

function validarFormulario() {
    let isValid = true;
    
    const clienteSelect = document.getElementById('selectCliente');
    if (!clienteSelect.value) {
        clienteSelect.classList.add('is-invalid');
        isValid = false;
    } else {
        clienteSelect.classList.remove('is-invalid');
    }
    
    const limiteInput = document.getElementById('inputLimite');
    const limiteValue = parseFloat(limiteInput.value.replace(/,/g, ''));
    if (isNaN(limiteValue) || limiteValue <= 0) {
        limiteInput.classList.add('is-invalid');
        isValid = false;
    } else {
        limiteInput.classList.remove('is-invalid');
    }
    
    const fechaInput = document.getElementById('inputVencimiento');
    if (!fechaInput.value) {
        fechaInput.classList.add('is-invalid');
        isValid = false;
    } else {
        fechaInput.classList.remove('is-invalid');
    }
    
    if (isValid) {
        document.getElementById('submitText').classList.add('d-none');
        document.getElementById('submitSpinner').classList.remove('d-none');
        document.getElementById('btnSubmit').disabled = true;
    }
    
    return isValid;
}
</script>

<?php include '../../includes/footer.php'; ?>