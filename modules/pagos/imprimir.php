<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

protegerPagina('admin,vendedor');

$pago_id = intval($_GET['id'] ?? 0);

if ($pago_id <= 0) {
    redirigirConMensaje('index.php', 'error', 'ID de pago inválido');
}

$sql_pago = "SELECT p.*, c.nombre as cliente_nombre, u.nombre as usuario_nombre
             FROM pagos p
             JOIN clientes c ON p.cliente_id = c.id
             JOIN usuarios u ON p.usuario_id = u.id
             WHERE p.id = ?";
$pago = obtenerUnRegistro($sql_pago, [$pago_id]);

if (!$pago) {
    redirigirConMensaje('index.php', 'error', 'Pago no encontrado');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo de Pago #<?= $pago['id'] ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .recibo { max-width: 800px; margin: 0 auto; border: 1px solid #ddd; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .logo { max-width: 150px; margin-bottom: 10px; }
        .titulo { font-size: 24px; font-weight: bold; }
        .info { margin-bottom: 30px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .firma { margin-top: 50px; border-top: 1px dashed #000; width: 300px; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="recibo">
        <div class="header">
            <div class="titulo">AgroPay+</div>
            <div>Recibo de Pago #<?= $pago['id'] ?></div>
        </div>
        
        <div class="info">
            <table class="table">
                <tr>
                    <th>Fecha:</th>
                    <td><?= formatearFecha($pago['fecha'], 'd/m/Y') ?></td>
                    <th>Cliente:</th>
                    <td><?= htmlspecialchars($pago['cliente_nombre']) ?></td>
                </tr>
                <tr>
                    <th>Monto:</th>
                    <td>₡<?= number_format($pago['monto'], 2) ?></td>
                    <th>Método:</th>
                    <td><?= ucfirst($pago['metodo']) ?></td>
                </tr>
                <?php if (!empty($pago['referencia'])): ?>
                <tr>
                    <th>Referencia:</th>
                    <td colspan="3"><?= htmlspecialchars($pago['referencia']) ?></td>
                </tr>
                <?php endif; ?>
            </table>
            
            <?php if (!empty($pago['notas'])): ?>
            <div>
                <strong>Notas:</strong>
                <p><?= nl2br(htmlspecialchars($pago['notas'])) ?></p>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="firma">
            <p>Recibido por: _________________________</p>
        </div>
        
        <div class="text-right no-print" style="margin-top: 20px;">
            <button onclick="window.print()" class="btn btn-primary">Imprimir</button>
            <button onclick="window.close()" class="btn btn-secondary">Cerrar</button>
        </div>
    </div>
</body>
</html>