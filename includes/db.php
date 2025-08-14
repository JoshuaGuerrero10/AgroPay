<?php
function abrirConexion() {
    try {
        $host = "127.0.0.1";
        $user = "root";
        $password = "Admin";
        $db = "agropay_db";

        $mysqli = new mysqli($host, $user, $password, $db);

        if($mysqli->connect_error) {
            throw new Exception("Error al conectar a la base de datos: " . $mysqli->connect_error);
        }

        $mysqli->set_charset('utf8mb4');
        return $mysqli;

    } catch (Exception $e) {
        die("Error de conexión: " . $e->getMessage());
    }
}

function cerrarConexion($mysqli) {
    if($mysqli instanceof mysqli) {
        $mysqli->close();
    }
}

function ejecutarConsulta($sql, $params = []) {
    $conn = abrirConexion();
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    
    if ($result = $stmt->get_result()) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        cerrarConexion($conn);
        return $data;
    } else {
        $affected = $stmt->affected_rows;
        cerrarConexion($conn);
        return $affected;
    }
}

function obtenerUnRegistro($sql, $params = []) {
    $result = ejecutarConsulta($sql, $params);
    return $result[0] ?? null;
}
?>