<?php
session_start();

function estaLogueado() {
    return isset($_SESSION['usuario_id']);
}

function protegerPagina() {
    if (!estaLogueado()) {
        $_SESSION['mensaje_error'] = "Debes iniciar sesión para acceder a esta página";
        header('Location: ../index.php');
        exit;
    }
}

function login($email, $password) {
    $sql = "SELECT id, nombre, email, password, rol FROM usuarios WHERE email = ? AND activo = 1";
    $usuario = obtenerUnRegistro($sql, [$email]);
    
    if ($usuario && password_verify($password, $usuario['password'])) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        $_SESSION['usuario_email'] = $usuario['email'];
        $_SESSION['usuario_rol'] = $usuario['rol'];
        return true;
    }
    return false;
}

function logout() {
    $_SESSION = array();
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

function obtenerUsuarioActual() {
    if (estaLogueado()) {
        return [
            'id' => $_SESSION['usuario_id'],
            'nombre' => $_SESSION['usuario_nombre'],
            'email' => $_SESSION['usuario_email'],
            'rol' => $_SESSION['usuario_rol']
        ];
    }
    return null;
}
?>