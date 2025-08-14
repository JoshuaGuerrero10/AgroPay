<?php
if (!function_exists('ejecutarConsulta')) {
    function ejecutarConsulta($sql, $params = []) {
        $conn = abrirConexion();
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Error al preparar consulta: " . $conn->error);
        }
        
        if (!empty($params)) {
            $tipos = str_repeat('s', count($params));
            $stmt->bind_param($tipos, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $filas = [];
        while ($fila = $result->fetch_assoc()) {
            $filas[] = $fila;
        }
        
        $stmt->close();
        cerrarConexion($conn);
        
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