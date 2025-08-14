<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

protegerPagina();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $ubicacion = $_POST['ubicacion'] ?? '';
    $identificacion = $_POST['identificacion'] ?? '';
    $estado = $_POST['estado'] ?? 'activo';
    $usuario_id = $_SESSION['usuario_id'];
    
    $sql = "INSERT INTO clientes (nombre, telefono, direccion, ubicacion, identificacion, estado, usuario_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $resultado = ejecutarConsulta($sql, [
        $nombre, $telefono, $direccion, $ubicacion, $identificacion, $estado, $usuario_id
    ]);
    
    if ($resultado > 0) {
        redirigirConMensaje('index.php', 'exito', 'Cliente creado correctamente');
    } else {
        redirigirConMensaje('crear.php', 'error', 'Error al crear el cliente');
    }
}

$titulo_pagina = "Nuevo Cliente";
$css_extra = "clientes.css";
include '../../includes/header.php';
include '../../includes/navbar.php';
include '../../includes/sidebar.php';
?>

<main class="flex-grow-1 py-4">
    <div class="container">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-person-plus"></i> Nuevo Cliente</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre Completo</label>
                            <input type="text" class="form-control" name="nombre" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Identificación</label>
                            <input type="text" class="form-control" name="identificacion" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Teléfono</label>
                            <input type="text" class="form-control" name="telefono">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ubicación</label>
                            <input type="text" class="form-control" name="ubicacion">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Dirección</label>
                        <textarea class="form-control" name="direccion" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Estado</label>
                        <select class="form-select" name="estado">
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                            <option value="moroso">Moroso</option>
                        </select>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <a href="index.php" class="btn btn-secondary me-2">Cancelar</a>
                        <button type="submit" class="btn btn-success">Guardar Cliente</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php include '../../includes/footer.php'; ?>