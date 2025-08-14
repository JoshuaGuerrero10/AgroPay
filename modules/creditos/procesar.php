<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

protegerPagina('admin,vendedor');

ini_set('display_errors', 1);
error_reporting(E_ALL);

function logCredito($mensaje) {
    file_put_contents(__DIR__.'/creditos.log', date('Y-m-d H:i:s').' - '.$mensaje.PHP_EOL, FILE_APPEND);
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    $conn = abrirConexion();
    
    switch ($action) {
        case 'crear':
            $camposRequeridos = ['cliente_id', 'limite', 'fecha_vencimiento'];
            foreach ($camposRequeridos as $campo) {
                if (empty($_POST[$campo])) {
                    throw new Exception("El campo $campo es requerido");
                }
            }

            // Obtener y validar datos
            $cliente_id = intval($_POST['cliente_id']);
            $limite = floatval(str_replace(',', '', $_POST['limite']));
            $fecha_vencimiento = $_POST['fecha_vencimiento'];
            $estado = in_array($_POST['estado'] ?? 'activo', ['activo', 'suspendido']) ? $_POST['estado'] : 'activo';
            $notas = $_POST['notas'] ?? '';
            $usuario_id = $_SESSION['usuario_id'];

            if ($cliente_id <= 0) throw new Exception("ID de cliente inválido");
            if ($limite <= 0) throw new Exception("El límite debe ser mayor a cero");
            
            $fecha_valida = DateTime::createFromFormat('Y-m-d', $fecha_vencimiento);
            if (!$fecha_valida) {
                throw new Exception("Formato de fecha inválido. Use YYYY-MM-DD");
            }

            $sql_verificar = "SELECT id FROM creditos WHERE cliente_id = ? AND estado = 'activo'";
            $stmt_verificar = $conn->prepare($sql_verificar);
            $stmt_verificar->bind_param('i', $cliente_id);
            $stmt_verificar->execute();
            $resultado = $stmt_verificar->get_result();
            
            if ($resultado->num_rows > 0) {
                throw new Exception("El cliente ya tiene un crédito activo");
            }

            $conn->begin_transaction();

            try {
                $sql_insert = "INSERT INTO creditos 
                              (cliente_id, limite, utilizado, fecha_vencimiento, estado, notas)
                              VALUES (?, ?, 0, ?, ?, ?)";
                
                $stmt_insert = $conn->prepare($sql_insert);
                if (!$stmt_insert) {
                    throw new Exception("Error al preparar consulta: ".$conn->error);
                }
                
                $stmt_insert->bind_param('idsss', $cliente_id, $limite, $fecha_vencimiento, $estado, $notas);
                
                if (!$stmt_insert->execute()) {
                    throw new Exception("Error al ejecutar consulta: ".$stmt_insert->error);
                }
                
                $credito_id = $conn->insert_id;
                logCredito("Nuevo crédito creado - ID: $credito_id, Cliente: $cliente_id, Límite: $limite");
                
                $conn->commit();
                
                $_SESSION['mensaje_exito'] = "Crédito creado exitosamente (ID: $credito_id)";
                header('Location: index.php');
                exit;
                
            } catch (Exception $e) {
                $conn->rollback();
                throw $e;
            }
            break;

        default:
            throw new Exception("Acción no válida");
    }
} catch (Exception $e) {
    logCredito("ERROR: ".$e->getMessage());
    $_SESSION['mensaje_error'] = $e->getMessage();
    header('Location: '.($_SERVER['HTTP_REFERER'] ?? 'index.php'));
    exit;
}