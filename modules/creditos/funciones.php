<?php
function actualizarCredito($id, $limite, $fecha_vencimiento, $estado, $notas) {
    $sql = "UPDATE creditos SET 
            limite = ?,
            fecha_vencimiento = ?,
            estado = ?,
            notas = ?
            WHERE id = ?";
    
    return ejecutarConsulta($sql, [
        $limite,
        $fecha_vencimiento,
        $estado,
        $notas,
        $id
    ]);
}

function aumentarCreditoUtilizado($cliente_id, $monto) {
    $sql = "UPDATE creditos SET 
            utilizado = utilizado + ?
            WHERE cliente_id = ? AND estado = 'activo'";
    
    return ejecutarConsulta($sql, [$monto, $cliente_id]);
}

function disminuirCreditoUtilizado($cliente_id, $monto) {
    $sql = "UPDATE creditos SET 
            utilizado = GREATEST(0, utilizado - ?)
            WHERE cliente_id = ? AND estado = 'activo'";
    
    return ejecutarConsulta($sql, [$monto, $cliente_id]);
}

function verificarDisponibilidadCredito($cliente_id, $monto) {
    $sql = "SELECT (limite - utilizado) as disponible
            FROM creditos 
            WHERE cliente_id = ? AND estado = 'activo'";
    
    $resultado = obtenerUnRegistro($sql, [$cliente_id]);
    
    if (!$resultado) {
        return false;
    }
    
    return ($resultado['disponible'] >= $monto);
}

function mostrarMensajes($tipo = 'info', $mensaje = '') {
    $clases = [
        'error' => 'alert-danger',
        'success' => 'alert-success',
        'info' => 'alert-info'
    ];
    
    if(!empty($mensaje)) {
        return '<div class="alert '.$clases[$tipo].'">'.$mensaje.'</div>';
    }
    return '';
}