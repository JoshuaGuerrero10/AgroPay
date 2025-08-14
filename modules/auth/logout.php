<?php
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

logout();
$_SESSION['mensaje_exito'] = "Has cerrado sesión correctamente";
header('Location: /agropay/index.php');
exit;
?>