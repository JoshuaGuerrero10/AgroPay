<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

protegerPagina();

if (!file_exists(__DIR__ . '/../../../logs')) {
    mkdir(__DIR__ . '/../../../logs', 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    registrarError("Inicio de procesamiento de pedido");
    depurarDatos($_POST);

    $cliente_id = intval($_POST['cliente_id'] ?? 0);
    $fecha = $_POST['fecha'] ?? date('Y-m-d');
    $fecha_entrega = $_POST['fecha_entrega'] ?? null;
    $notas = $_POST['notas'] ?? '';
    $productos = $_POST['productos'] ?? [];
    $usuario_id = intval($_SESSION['usuario_id']);

    if ($cliente_id <= 0) {
        registrarError("Error: Cliente no válido");
        redirigirConMensaje('crear.php', 'error', 'Debe seleccionar un cliente válido');
    }

    if (empty($productos)) {
        registrarError("Error: No hay productos");
        redirigirConMensaje('crear.php', 'error', 'Debe agregar al menos un producto');
    }

    foreach ($productos as $index => $producto) {
        if (!isset($producto['id'], $producto['cantidad'], $producto['precio'])) {
            registrarError("Error: Estructura de producto inválida en índice $index");
            redirigirConMensaje('crear.php', 'error', 'Datos de productos incompletos');
        }
        
        if (!is_numeric($producto['cantidad']) || $producto['cantidad'] <= 0) {
            registrarError("Error: Cantidad inválida para producto ID {$producto['id']}");
            redirigirConMensaje('crear.php', 'error', 'Cantidad inválida para uno o más productos');
        }
    }

    $total = 0;
    foreach ($productos as $producto) {
        $total += floatval($producto['precio']) * intval($producto['cantidad']);
    }

    if ($total <= 0) {
        registrarError("Error: Total inválido: $total");
        redirigirConMensaje('crear.php', 'error', 'El total del pedido no puede ser cero');
    }

    $conn = abrirConexion();
    if (!$conn) {
        registrarError("Error: No se pudo conectar a la base de datos");
        redirigirConMensaje('crear.php', 'error', 'Error de conexión a la base de datos');
    }

    try {
        $conn->begin_transaction();
        registrarError("Transacción iniciada");

        $sql_pedido = "INSERT INTO pedidos (cliente_id, usuario_id, fecha, fecha_entrega, total, notas, estado) 
                      VALUES (?, ?, ?, ?, ?, ?, 'pendiente')";
        $stmt_pedido = $conn->prepare($sql_pedido);
        
        if (!$stmt_pedido) {
            throw new Exception("Error al preparar consulta de pedido: " . $conn->error);
        }

        $stmt_pedido->bind_param('iissds', $cliente_id, $usuario_id, $fecha, $fecha_entrega, $total, $notas);
        
        if (!$stmt_pedido->execute()) {
            throw new Exception("Error al ejecutar consulta de pedido: " . $stmt_pedido->error);
        }

        $pedido_id = $conn->insert_id;
        registrarError("Pedido creado con ID: $pedido_id");

        if ($pedido_id <= 0) {
            throw new Exception("Error: No se obtuvo ID de pedido válido");
        }

        $sql_item = "INSERT INTO pedido_items (pedido_id, producto_id, cantidad, precio_unitario, subtotal) 
                    VALUES (?, ?, ?, ?, ?)";
        $stmt_item = $conn->prepare($sql_item);
        
        if (!$stmt_item) {
            throw new Exception("Error al preparar consulta de items: " . $conn->error);
        }

        foreach ($productos as $producto) {
            $producto_id = intval($producto['id']);
            $cantidad = intval($producto['cantidad']);
            $precio = floatval($producto['precio']);
            $subtotal = $precio * $cantidad;

            $stmt_item->bind_param('iiidd', $pedido_id, $producto_id, $cantidad, $precio, $subtotal);
            
            if (!$stmt_item->execute()) {
                throw new Exception("Error al insertar item: " . $stmt_item->error);
            }

            $sql_stock = "UPDATE productos SET stock = stock - ? WHERE id = ? AND stock >= ?";
            $stmt_stock = $conn->prepare($sql_stock);
            
            if (!$stmt_stock) {
                throw new Exception("Error al preparar consulta de stock: " . $conn->error);
            }

            $stmt_stock->bind_param('iii', $cantidad, $producto_id, $cantidad);
            $stmt_stock->execute();
            
            if ($stmt_stock->affected_rows === 0) {
                throw new Exception("Stock insuficiente para el producto ID: $producto_id");
            }
        }

        $conn->commit();
        registrarError("Transacción completada con éxito para pedido ID: $pedido_id");
        
        redirigirConMensaje('index.php', 'exito', "Pedido #$pedido_id creado correctamente");

    } catch (Exception $e) {
        $conn->rollback();
        registrarError("Error en transacción: " . $e->getMessage());
        redirigirConMensaje('crear.php', 'error', 'Error al crear el pedido: ' . $e->getMessage());
    }
}

try {
    $clientes = ejecutarConsulta("SELECT id, nombre FROM clientes WHERE estado = 'activo' ORDER BY nombre");
    $productos = ejecutarConsulta("SELECT id, nombre, precio, stock FROM productos WHERE activo = 1 AND stock > 0 ORDER BY nombre");
} catch (Exception $e) {
    registrarError("Error al obtener datos: " . $e->getMessage());
    redirigirConMensaje('index.php', 'error', 'Error al cargar datos necesarios');
}

$titulo_pagina = "Nuevo Pedido";
$css_extra = "pedidos.css";
$js_extra = "pedidos.js";
include '../../includes/header.php';
include '../../includes/navbar.php';
include '../../includes/sidebar.php';
?>

<main class="flex-grow-1 py-4">
    <div class="container">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-cart-plus"></i> Nuevo Pedido</h5>
            </div>
            <div class="card-body">
                <form method="POST" id="formPedido">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Cliente</label>
                            <select class="form-select" name="cliente_id" required>
                                <option value="">Seleccionar cliente...</option>
                                <?php foreach ($clientes as $cliente): ?>
                                <option value="<?= $cliente['id'] ?>"><?= htmlspecialchars($cliente['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha</label>
                            <input type="date" class="form-control" name="fecha" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha Entrega</label>
                            <input type="date" class="form-control" name="fecha_entrega">
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Productos</h5>
                            <button type="button" class="btn btn-sm btn-success" id="agregarProducto">
                                <i class="bi bi-plus-lg"></i> Agregar Producto
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table" id="tablaProductos">
                                    <thead>
                                        <tr>
                                            <th>Producto</th>
                                            <th width="120">Cantidad</th>
                                            <th width="120">Precio</th>
                                            <th width="120">Subtotal</th>
                                            <th width="50"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="productosBody">
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-end fw-bold">Total:</td>
                                            <td class="fw-bold" id="totalPedido">₡0.00</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notas</label>
                        <textarea class="form-control" name="notas" rows="3"></textarea>
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="index.php" class="btn btn-secondary me-2">Cancelar</a>
                        <button type="submit" class="btn btn-success">Guardar Pedido</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<template id="productoTemplate">
    <tr>
        <td>
            <select class="form-select producto-select" name="producto_temp" required>
                <option value="">Seleccionar producto...</option>
                <?php foreach ($productos as $producto): ?>
                <option value="<?= $producto['id'] ?>" 
                        data-precio="<?= $producto['precio'] ?>"
                        data-stock="<?= $producto['stock'] ?>">
                    <?= htmlspecialchars($producto['nombre']) ?> (₡<?= number_format($producto['precio'], 2) ?>)
                </option>
                <?php endforeach; ?>
            </select>
        </td>
        <td>
            <input type="number" class="form-control cantidad" name="cantidad_temp" min="1" value="1" required>
        </td>
        <td class="precio-unitario">₡0.00</td>
        <td class="subtotal">₡0.00</td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger eliminar-producto">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>
</template>

<?php include '../../includes/footer.php'; ?>