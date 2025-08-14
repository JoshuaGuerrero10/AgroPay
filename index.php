<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (estaLogueado()) {
    header('Location: dashboard.php');
    exit;
}

$titulo_pagina = "AgroPay - Inicio";
include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="bi bi-tree"></i> AgroPay+ - Inicio de Sesión</h4>
                </div>
                <div class="card-body">
                    <?phprequire_once 'functions.php';
                    echo mostrarMensajes('success', 'Bienvenido al sistema');?>
                    <form action="modules/auth/login.php" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100 mb-3">Iniciar Sesión</button>
                        <div class="text-center">
                            <a href="registro.php" class="text-success">¿No tienes cuenta? Regístrate aquí</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>