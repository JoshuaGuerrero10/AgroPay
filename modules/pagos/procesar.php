<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

protegerPagina('admin,vendedor');

ini_set('display_errors', 1);
error_reporting(E_ALL);

function logPago($mensaje) {
    file_put_contents(__DIR__.'/pagos.log', date('Y-m-d H:i:s').' - '.$mensaje.PHP_EOL, FILE_APPEND);
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    $conn = abrirConexion();
    
    switch ($action) {
        case 'crear':
            $requiredFields = ['cliente_id', 'fecha', 'monto', 'metodo'];
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("El campo $field es requerido");
                }
            }

            $cliente_id = intval($_POST['cliente_id']);
            $fecha = $_POST['fecha'];
            $monto = floatval(str_replace(',', '', $_POST['monto']));
            $metodo = in_array($_POST['metodo'], ['efectivo', 'transferencia', 'cheque']) ? $_POST['metodo'] : 'efectivo';
            $referencia = $_POST['referencia'] ?? '';
            $notas = $_POST['notas'] ?? '';
            $usuario_id = $_SESSION['usuario_id'];
            $pedidos = $_POST['pedidos'] ?? [];

            if ($cliente_id <= 0) throw new Exception("ID de cliente inválido");
            if ($monto <= 0) throw new Exception("El monto debe ser mayor a cero");
            
            if (!DateTime::createFromFormat('Y-m-d', $fecha)) {
                throw new Exception("Formato de fecha inválido");
            }

            $conn->begin_transaction();

            try {
                $sql_pago = "INSERT INTO pagos 
                            (cliente_id, usuario_id, fecha, monto, metodo, referencia, notas)
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
                
                $stmt_pago = $conn->prepare($sql_pago);
                if (!$stmt_pago) throw new Exception("Error al preparar consulta: ".$conn->error);
                
                $stmt_pago->bind_param('issdsss', $cliente_id, $usuario_id, $fecha, $monto, $metodo, $referencia, $notas);
                
                if (!$stmt_pago->execute()) {
                    throw new Exception("Error al registrar pago: ".$stmt_pago->error);
                }
                
                $pago_id = $conn->insert_id;
                logPago("Nuevo pago registrado - ID: $pago_id, Cliente: $cliente_id, Monto: $monto");

                if (!empty($pedidos)) {
                    $total_aplicado = 0;
                    $sql_aplicar = "INSERT INTO pago_pedidos (pago_id, pedido_id, monto_aplicado)
                                   VALUES (?, ?, ?)";
                    $stmt_aplicar = $conn->prepare($sql_aplicar);
                    
                    foreach ($pedidos as $pedido_id => $monto_aplicado) {
                        $monto_aplicado = floatval($monto_aplicado);
                        if ($monto_aplicado <= 0) continue;
                        
                        $stmt_aplicar->bind_param('iid', $pago_id, $pedido_id, $monto_aplicado);
                        $stmt_aplicar->execute();
                        
                        $total_aplicado += $monto_aplicado;
                    }
                    
                    if ($total_aplicado > $monto) {
                        throw new Exception("El total aplicado a pedidos excede el monto del pago");
                    }
                    
                    logPago("Pago ID $pago_id aplicado a pedidos - Total: $total_aplicado");
                }

                $conn->commit();
                
                $_SESSION['mensaje_exito'] = "Pago registrado exitosamente (ID: $pago_id)";
                header('Location: ver.php?id='.$pago_id);
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
    logPago("ERROR: ".$e->getMessage());
    $_SESSION['mensaje_error'] = $e->getMessage();
    header('Location: '.($_SERVER['HTTP_REFERER'] ?? 'index.php'));
    exit;
}