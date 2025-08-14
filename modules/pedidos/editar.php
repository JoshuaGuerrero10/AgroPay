<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

protegerPagina('admin,vendedor');

// Obtener ID del pedido
$pedido_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($pedido_id <= 0) {
    redirigirConMensaje('index.php', 'error', 'ID de pedido inválido');
}

// Obtener información del pedido
$pedido = obtenerUnRegistro(
    "SELECT p.*, c.nombre as cliente_nombre 
     FROM pedidos p 
     JOIN clientes c ON p.cliente_id = c.id 
     WHERE p.id = ?",
    [$pedido_id]
);

if (!$pedido) {
    redirigirConMensaje('index.php', 'error', 'Pedido no encontrado');
}

// Obtener items del pedido
$items = ejecutarConsulta(
    "SELECT pi.*, pr.nombre as producto_nombre, pr.precio as precio_actual
     FROM pedido_items pi
     JOIN productos pr ON pi.producto_id = pr.id
     WHERE pi.pedido_id = ?",
    [$pedido_id]
);

// Obtener clientes y productos disponibles
$clientes = ejecutarConsulta("SELECT id, nombre FROM clientes WHERE estado = 'activo' ORDER BY nombre");
$productos = ejecutarConsulta("SELECT id, nombre, precio, stock FROM productos WHERE activo = 1 ORDER BY nombre");

// Procesar formulario de actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cliente_id = intval($_POST['cliente_id']);
    $fecha_entrega = $_POST['fecha_entrega'] ?: null;
    $notas = $_POST['notas'] ?: '';
    $items_actualizados = $_POST['items'] ?? [];
    
    try {
        $conn = abrirConexion();
        $conn->begin_transaction();
        
        // 1. Actualizar información básica del pedido
        $sql = "UPDATE pedidos SET 
                cliente_id = ?, 
                fecha_entrega = ?, 
                notas = ? 
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('issi', $cliente_id, $fecha_entrega, $notas, $pedido_id);
        $stmt->execute();
        
        // 2. Actualizar items del pedido
        foreach ($items_actualizados as $item_id => $item) {
            $cantidad = intval($item['cantidad']);
            $precio = floatval($item['precio']);
            
            $sql = "UPDATE pedido_items SET 
                    cantidad = ?, 
                    precio_unitario = ?, 
                    subtotal = ? 
                    WHERE id = ? AND pedido_id = ?";
            
            $stmt = $conn->prepare($sql);
            $subtotal = $cantidad * $precio;
            $stmt->bind_param('iddii', $cantidad, $precio, $subtotal, $item_id, $pedido_id);
            $stmt->execute();
        }
        
        // 3. Recalcular total del pedido
        $total = ejecutarConsulta(
            "SELECT SUM(subtotal) as total FROM pedido_items WHERE pedido_id = ?",
            [$pedido_id]
        )[0]['total'];
        
        $sql = "UPDATE pedidos SET total = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('di', $total, $pedido_id);
        $stmt->execute();
        
        $conn->commit();
        redirigirConMensaje('ver.php?id='.$pedido_id, 'exito', 'Pedido actualizado correctamente');
        
    } catch (Exception $e) {
        $conn->rollback();
        redirigirConMensaje('editar.php?id='.$pedido_id, 'error', 'Error al actualizar pedido: '.$e->getMessage());
    }
}

$titulo_pagina = "Editar Pedido #".$pedido_id;
$css_especial = "pedidos.css";
$js_especial = "pedidos.js";
include '../../includes/header.php';
include '../../includes/navbar.php';
include '../../includes/sidebar.php';
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="bi bi-pencil"></i> Editar Pedido #<?= $pedido_id ?></h4>
        </div>
        <div class="card-body">
            <form method="POST" id="formPedido">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Cliente</label>
                        <select name="cliente_id" class="form-select" required>
                            <?php foreach ($clientes as $cliente): ?>
                            <option value="<?= $cliente['id'] ?>" 
                                <?= $cliente['id'] == $pedido['cliente_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cliente['nombre']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Fecha Entrega</label>
                        <input type="date" name="fecha_entrega" class="form-control" 
                               value="<?= htmlspecialchars($pedido['fecha_entrega'] ?? '') ?>">
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Productos</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table" id="tablaProductos">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th width="120">Cantidad</th>
                                        <th width="120">Precio Unitario</th>
                                        <th width="120">Subtotal</th>
                                        <th width="50"></th>
                                    </tr>
                                </thead>
                                <tbody id="productosBody">
                                    <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td>
                                            <input type="hidden" name="items[<?= $item['id'] ?>][producto_id]" value="<?= $item['producto_id'] ?>">
                                            <?= htmlspecialchars($item['producto_nombre']) ?>
                                        </td>
                                        <td>
                                            <input type="number" name="items[<?= $item['id'] ?>][cantidad]" 
                                                   class="form-control cantidad" min="1" 
                                                   value="<?= $item['cantidad'] ?>" required>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="items[<?= $item['id'] ?>][precio]" 
                                                   class="form-control precio" min="0.01" 
                                                   value="<?= $item['precio_unitario'] ?>" required>
                                        </td>
                                        <td class="subtotal">₡<?= number_format($item['subtotal'], 2) ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-danger eliminar-producto">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold">Total:</td>
                                        <td class="fw-bold" id="totalPedido">₡<?= number_format($pedido['total'], 2) ?></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Notas</label>
                    <textarea name="notas" class="form-control" rows="3"><?= htmlspecialchars($pedido['notas'] ?? '') ?></textarea>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="index.php" class="btn btn-secondary me-2">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Script para actualizar subtotales y total en tiempo real
document.addEventListener('DOMContentLoaded', function() {
    function actualizarTotales() {
        let total = 0;
        
        document.querySelectorAll('#productosBody tr').forEach(row => {
            const cantidad = parseFloat(row.querySelector('.cantidad').value) || 0;
            const precio = parseFloat(row.querySelector('.precio').value) || 0;
            const subtotal = cantidad * precio;
            
            row.querySelector('.subtotal').textContent = '₡' + subtotal.toFixed(2);
            total += subtotal;
        });
        
        document.getElementById('totalPedido').textContent = '₡' + total.toFixed(2);
    }
    
    // Event listeners para cambios
    document.querySelectorAll('.cantidad, .precio').forEach(input => {
        input.addEventListener('change', actualizarTotales);
        input.addEventListener('keyup', actualizarTotales);
    });
    
    // Eliminar producto
    document.querySelectorAll('.eliminar-producto').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('¿Eliminar este producto del pedido?')) {
                this.closest('tr').remove();
                actualizarTotales();
            }
        });
    });
});
</script>

<?php include '../../includes/footer.php'; ?>