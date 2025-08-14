<?php
if (!function_exists('ejecutarConsulta')) {
    function ejecutarConsulta($sql, $params = []) {
        $conn = abrirConexion();
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Error al preparar consulta: " . $conn->error);
        }
        
        if (!empty($params)) {
            $tipos = '';
            foreach ($params as $param) {
                if (is_int($param)) $tipos .= 'i';
                elseif (is_double($param)) $tipos .= 'd';
                else $tipos .= 's';
            }
            $stmt->bind_param($tipos, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $filas = [];
        if ($result) {
            while ($fila = $result->fetch_assoc()) {
                $filas[] = $fila;
            }
        }
        
        $stmt->close();
        return $filas;
    }
}

if (!function_exists('obtenerUnRegistro')) {
    function obtenerUnRegistro($sql, $params = []) {
        $resultados = ejecutarConsulta($sql, $params);
        return $resultados[0] ?? null;
    }
}


if (!function_exists('formatearFecha')) {
    function formatearFecha($fecha, $formato = 'd/m/Y') {
        if (empty($fecha)) return '';
        
        try {
            $date = new DateTime($fecha);
            return $date->format($formato);
        } catch (Exception $e) {
            error_log("Error al formatear fecha: " . $e->getMessage());
            return $fecha;
        }
    }
}

if (!function_exists('mostrarMensajes')) {
    function mostrarMensajes($tipo = 'info', $mensaje = '', $autoCerrar = true) {
        $clases = [
            'error'   => 'alert alert-danger',
            'success' => 'alert alert-success',
            'info'    => 'alert alert-info',
            'warning' => 'alert alert-warning'
        ];
        
        if (!array_key_exists($tipo, $clases)) {
            $tipo = 'info';
        }
        
        $html = '<div class="'.$clases[$tipo].'" role="alert">';
        $html .= htmlspecialchars($mensaje);
        
        if ($autoCerrar) {
            $html .= '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
            $html .= '<span aria-hidden="true">&times;</span>';
            $html .= '</button>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
}

if (!function_exists('sanitizarInput')) {
    function sanitizarInput($data) {
        if (is_array($data)) {
            return array_map('sanitizarInput', $data);
        }
        
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('generarTokenCSRF')) {
    function generarTokenCSRF() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}
?>