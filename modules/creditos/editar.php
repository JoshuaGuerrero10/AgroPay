<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

protegerPagina('admin,vendedor');

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    redirigirConMensaje('index.php', 'error', 'ID de crédito inválido');
}

$sql = "SELECT c.*, cl.nombre as cliente_nombre 
        FROM creditos c
        JOIN clientes cl ON c.cliente_id = cl.id
        WHERE c.id = ?";
$credito = obtenerUnRegistro($sql, [$id]);

if (!$credito) {
    redirigirConMensaje('index.php', 'error', 'Crédito no encontrado');
}

// Obtener clientes activos
$clientes = ejecutarConsulta("SELECT id, nombre FROM clientes WHERE estado = 'activo' ORDER BY nombre");

$titulo_pagina = "Editar Crédito";
$css_extra = "creditos.css";
include '../../includes/header.php';
include '../../includes/navbar.php';
include '../../includes/sidebar.php';
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="bi bi-pencil"></i> Editar Crédito</h4>
        </div>
        <div class="card-body">
            <form action="procesar.php" method="POST">
                <input type="hidden" name="action" value="editar">
                <input type="hidden" name="id" value="<?= $credito['id'] ?>">
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Cliente</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($credito['cliente_nombre']) ?>" disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Límite de Crédito (₡) *</label>
                        <input type="number" name="limite" class="form-control" 
                               value="<?= $credito['limite'] ?>" min="1" step="0.01" required>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Fecha de Vencimiento *</label>
                        <input type="date" name="fecha_vencimiento" class="form-control" 
                               value="<?= $credito['fecha_vencimiento'] ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-select">
                            <option value="activo" <?= $credito['estado'] == 'activo' ? 'selected' : '' ?>>Activo</option>
                            <option value="suspendido" <?= $credito['estado'] == 'suspendido' ? 'selected' : '' ?>>Suspendido</option>
                            <option value="vencido" <?= $credito['estado'] == 'vencido' ? 'selected' : '' ?>>Vencido</option>
                        </select>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Utilizado (₡)</label>
                        <input type="text" class="form-control" 
                               value="<?= number_format($credito['utilizado'], 2) ?>" disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Disponible (₡)</label>
                        <input type="text" class="form-control" 
                               value="<?= number_format($credito['limite'] - $credito['utilizado'], 2) ?>" disabled>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Notas</label>
                    <textarea name="notas" class="form-control" rows="3"><?= htmlspecialchars($credito['notas']) ?></textarea>
                </div>
                
                <div class="d-flex justify-content-end">
                    <a href="index.php" class="btn btn-secondary me-2">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>