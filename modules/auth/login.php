<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (login($email, $password)) {
        $_SESSION['mensaje_exito'] = "Bienvenido de nuevo!";
        header('Location: /agropay/dashboard.php');
        exit;
    } else {
        $_SESSION['mensaje_error'] = "Credenciales incorrectas";
        header('Location: /agropay/index.php');
        exit;
    }
} else {
    header('Location: /agropay/index.php');
    exit;
}
?>