<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (estaLogueado()) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $rol = 'vendedor';

    $errores = [];

    if (empty($nombre) || strlen($nombre) > 100) {
        $errores[] = "El nombre es obligatorio y debe tener máximo 100 caracteres";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
        $errores[] = "El correo electrónico no es válido o excede los 100 caracteres";
    }

    if (strlen($password) < 8) {
        $errores[] = "La contraseña debe tener al menos 8 caracteres";
    }

    if ($password !== $confirm_password) {
        $errores[] = "Las contraseñas no coinciden";
    }

    $sql = "SELECT id FROM usuarios WHERE email = ?";
    $existe = obtenerUnRegistro($sql, [$email]);
    
    if ($existe) {
        $errores[] = "Este correo electrónico ya está registrado";
    }

    if (empty($errores)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO usuarios (nombre, email, password, rol, activo) 
                VALUES (?, ?, ?, ?, TRUE)";
        
        $resultado = ejecutarConsulta($sql, [$nombre, $email, $password_hash, $rol]);
        
        if ($resultado > 0) {
            $_SESSION['mensaje_exito'] = "¡Registro exitoso! Ahora puedes iniciar sesión";
            header('Location: index.php');
            exit;
        } else {
            $errores[] = "Error al registrar el usuario. Por favor intenta nuevamente.";
        }
    }
}

$titulo_pagina = "AgroPay - Registro";
include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="bi bi-person-plus"></i> Registro de Usuario AgroPay+</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($errores)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errores as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" id="formRegistro">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nombre" class="form-label">Nombre Completo *</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" 
                                           value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>" 
                                           maxlength="100" required>
                                    <small class="text-muted">Máximo 100 caracteres</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Correo Electrónico *</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                                           maxlength="100" required>
                                    <small class="text-muted">Ejemplo: usuario@dominio.com</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Contraseña *</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <div class="password-strength mt-2">
                                        <div class="progress" style="height: 5px;">
                                            <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                        </div>
                                        <small class="text-muted">La contraseña debe tener al menos 8 caracteres</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirmar Contraseña *</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    <div id="password-match" class="mt-2 small"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Todos los nuevos usuarios se registran con el rol de <strong>Vendedor</strong> por defecto. 
                            Un administrador puede actualizar tu rol posteriormente si es necesario.
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="terminos" name="terminos" required>
                            <label class="form-check-label" for="terminos">
                                Acepto los <a href="#" data-bs-toggle="modal" data-bs-target="#modalTerminos">términos y condiciones</a> *
                            </label>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-person-check"></i> Registrarse
                            </button>
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Volver al login
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTerminos" tabindex="-1" aria-labelledby="modalTerminosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTerminosLabel">Términos y Condiciones</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5>1. Uso del Sistema AgroPay+</h5>
                <p>El sistema AgroPay+ está diseñado para la gestión de clientes, pedidos, créditos y pagos en el ámbito agrícola...</p>
                
                <h5>2. Responsabilidades del Usuario</h5>
                <p>El usuario es responsable de mantener la confidencialidad de sus credenciales de acceso...</p>
                
                <h5>3. Protección de Datos</h5>
                <p>Toda la información ingresada en el sistema está protegida según las leyes de protección de datos...</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">Entendido</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const progressBar = document.querySelector('.progress-bar');
    
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        let strength = 0;
        
        if (password.length >= 8) strength += 20;
        
        if (/\d/.test(password)) strength += 20;
        
        if (/[a-z]/.test(password)) strength += 20;
        
        if (/[A-Z]/.test(password)) strength += 20;
        
        if (/[^a-zA-Z0-9]/.test(password)) strength += 20;
        
        progressBar.style.width = strength + '%';
        
        if (strength < 40) {
            progressBar.className = 'progress-bar bg-danger';
        } else if (strength < 80) {
            progressBar.className = 'progress-bar bg-warning';
        } else {
            progressBar.className = 'progress-bar bg-success';
        }
    });
    
    const confirmPasswordInput = document.getElementById('confirm_password');
    const passwordMatchDiv = document.getElementById('password-match');
    
    confirmPasswordInput.addEventListener('input', function() {
        if (this.value !== passwordInput.value) {
            passwordMatchDiv.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle"></i> Las contraseñas no coinciden</span>';
        } else {
            passwordMatchDiv.innerHTML = '<span class="text-success"><i class="bi bi-check-circle"></i> Las contraseñas coinciden</span>';
        }
    });
    
    document.getElementById('formRegistro').addEventListener('submit', function(e) {
        if (!document.getElementById('terminos').checked) {
            e.preventDefault();
            alert('Debes aceptar los términos y condiciones para registrarte');
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>