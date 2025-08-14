<?php
function exportarReporte($formato, $titulo, $filtros, $datos, $totales = []) {
    switch ($formato) {
        case 'pdf':
            exportarPDF($titulo, $filtros, $datos, $totales);
            break;
        case 'excel':
            exportarExcel($titulo, $filtros, $datos, $totales);
            break;
        default:
            throw new Exception("Formato de exportación no soportado");
    }
}

function exportarPDF($titulo, $filtros, $datos, $totales) {
    require_once __DIR__.'/../../libs/tcpdf/tcpdf.php';
    
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    $pdf->SetCreator('AgroPay+');
    $pdf->SetAuthor('AgroPay+');
    $pdf->SetTitle($titulo);
    $pdf->SetSubject($titulo);
    
    $pdf->SetMargins(15, 25, 15);
    $pdf->SetHeaderMargin(10);
    $pdf->SetFooterMargin(10);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    
    $pdf->setHeaderData('', 0, $titulo, 'Generado el: '.date('d/m/Y H:i:s'));
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    
    $pdf->AddPage();
    
    $html = '<div style="text-align:center;">';
    $html .= '<h1>'.$titulo.'</h1>';
    $html .= '<p><strong>Sistema AgroPay+</strong></p>';
    $html .= '</div>';
    
    $html .= '<h3>Parámetros del Reporte</h3>';
    $html .= '<table border="0.5" cellpadding="4">';
    $html .= '<tr><td width="30%"><strong>Fecha de generación:</strong></td><td>'.date('d/m/Y H:i:s').'</td></tr>';
    
    if (isset($filtros['desde'])) {
        $html .= '<tr><td><strong>Desde:</strong></td><td>'.formatearFecha($filtros['desde'], 'd/m/Y').'</td></tr>';
    }
    
    if (isset($filtros['hasta'])) {
        $html .= '<tr><td><strong>Hasta:</strong></td><td>'.formatearFecha($filtros['hasta'], 'd/m/Y').'</td></tr>';
    }
    
    if (isset($filtros['metodo']) && $filtros['metodo'] !== 'todos') {
        $html .= '<tr><td><strong>Método:</strong></td><td>'.ucfirst($filtros['metodo']).'</td></tr>';
    }
    
    if (isset($filtros['estado']) && $filtros['estado'] !== 'todos') {
        $html .= '<tr><td><strong>Estado:</strong></td><td>'.ucfirst($filtros['estado']).'</td></tr>';
    }
    
    $html .= '</table><br>';
        if (!empty($totales)) {
        $html .= '<h3>Resumen</h3>';
        $html .= '<table border="0.5" cellpadding="4">';
        $html .= '<tr style="background-color:#f2f2f2;">';
        $html .= '<th width="40%">Concepto</th><th width="30%">Cantidad</th><th width="30%">Total</th>';
        $html .= '</tr>';
        
        $total_general = 0;
        $cantidad_general = 0;
        
        foreach ($totales as $total) {
            $html .= '<tr>';
            $html .= '<td>'.ucfirst($total['metodo'] ?? $total['estado'] ?? 'Total').'</td>';
            $html .= '<td>'.($total['cantidad'] ?? 0).'</td>';
            $html .= '<td>₡'.number_format($total['total'] ?? 0, 2).'</td>';
            $html .= '</tr>';
            
            $total_general += $total['total'] ?? 0;
            $cantidad_general += $total['cantidad'] ?? 0;
        }
        
        $html .= '<tr style="background-color:#f2f2f2;font-weight:bold;">';
        $html .= '<td>Total General</td>';
        $html .= '<td>'.$cantidad_general.'</td>';
        $html .= '<td>₡'.number_format($total_general, 2).'</td>';
        $html .= '</tr>';
        $html .= '</table><br>';
    }
    
    if (!empty($datos)) {
        $html .= '<h3>Detalles</h3>';
        $html .= '<table border="0.5" cellpadding="4">';
        
        $html .= '<tr style="background-color:#f2f2f2;">';
        foreach (array_keys($datos[0]) as $columna) {
            if (!in_array($columna, ['usuario_id', 'cliente_id'])) {
                $html .= '<th>'.ucwords(str_replace('_', ' ', $columna)).'</th>';
            }
        }
        $html .= '</tr>';
        
        foreach ($datos as $fila) {
            $html .= '<tr>';
            foreach ($fila as $columna => $valor) {
                if (!in_array($columna, ['usuario_id', 'cliente_id'])) {
                    if (in_array($columna, ['fecha', 'fecha_creacion', 'fecha_vencimiento'])) {
                        $valor = formatearFecha($valor, 'd/m/Y');
                    } elseif (strpos($columna, 'monto') !== false || strpos($columna, 'total') !== false || strpos($columna, 'precio') !== false) {
                        $valor = '₡'.number_format($valor, 2);
                    } elseif ($columna === 'metodo' || $columna === 'estado') {
                        $valor = ucfirst($valor);
                    }
                    
                    $html .= '<td>'.$valor.'</td>';
                }
            }
            $html .= '</tr>';
        }
        $html .= '</table>';
    }
    
    $pdf->writeHTML($html, true, false, true, false, '');
    
    $filename = strtolower(str_replace(' ', '_', $titulo)).'_'.date('Ymd_His').'.pdf';
    
    $pdf->Output($filename, 'D');
    exit;
}

function exportarExcel($titulo, $filtros, $datos, $totales) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="'.strtolower(str_replace(' ', '_', $titulo)).'_'.date('Ymd_His').'.xls"');
    header('Cache-Control: max-age=0');
    
    echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
    echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<!--[if gte mso 9]>';
    echo '<xml>';
    echo '<x:ExcelWorkbook>';
    echo '<x:ExcelWorksheets>';
    echo '<x:ExcelWorksheet>';
    echo '<x:Name>'.htmlspecialchars($titulo).'</x:Name>';
    echo '<x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>';
    echo '</x:ExcelWorksheet>';
    echo '</x:ExcelWorksheets>';
    echo '</x:ExcelWorkbook>';
    echo '</xml>';
    echo '<![endif]-->';
    echo '</head>';
    echo '<body>';
    
    echo '<h1>'.htmlspecialchars($titulo).'</h1>';
    echo '<p><strong>Sistema AgroPay+</strong></p>';
    echo '<p><strong>Fecha de generación:</strong> '.date('d/m/Y H:i:s').'</p>';
    
    echo '<h3>Parámetros del Reporte</h3>';
    echo '<table border="1" cellpadding="4">';
    echo '<tr><td width="30%"><strong>Fecha de generación:</strong></td><td>'.date('d/m/Y H:i:s').'</td></tr>';
    
    if (isset($filtros['desde'])) {
        echo '<tr><td><strong>Desde:</strong></td><td>'.formatearFecha($filtros['desde'], 'd/m/Y').'</td></tr>';
    }
    
    if (isset($filtros['hasta'])) {
        echo '<tr><td><strong>Hasta:</strong></td><td>'.formatearFecha($filtros['hasta'], 'd/m/Y').'</td></tr>';
    }
    
    if (isset($filtros['metodo']) && $filtros['metodo'] !== 'todos') {
        echo '<tr><td><strong>Método:</strong></td><td>'.ucfirst($filtros['metodo']).'</td></tr>';
    }
    
    if (isset($filtros['estado']) && $filtros['estado'] !== 'todos') {
        echo '<tr><td><strong>Estado:</strong></td><td>'.ucfirst($filtros['estado']).'</td></tr>';
    }
    
    echo '</table><br>';
    
    if (!empty($totales)) {
        echo '<h3>Resumen</h3>';
        echo '<table border="1" cellpadding="4">';
        echo '<tr style="background-color:#f2f2f2;">';
        echo '<th width="40%">Concepto</th><th width="30%">Cantidad</th><th width="30%">Total</th>';
        echo '</tr>';
        
        $total_general = 0;
        $cantidad_general = 0;
        
        foreach ($totales as $total) {
            echo '<tr>';
            echo '<td>'.ucfirst($total['metodo'] ?? $total['estado'] ?? 'Total').'</td>';
            echo '<td>'.($total['cantidad'] ?? 0).'</td>';
            echo '<td>₡'.number_format($total['total'] ?? 0, 2).'</td>';
            echo '</tr>';
            
            $total_general += $total['total'] ?? 0;
            $cantidad_general += $total['cantidad'] ?? 0;
        }
        
        echo '<tr style="background-color:#f2f2f2;font-weight:bold;">';
        echo '<td>Total General</td>';
        echo '<td>'.$cantidad_general.'</td>';
        echo '<td>₡'.number_format($total_general, 2).'</td>';
        echo '</tr>';
        echo '</table><br>';
    }
    
    if (!empty($datos)) {
        echo '<h3>Detalles</h3>';
        echo '<table border="1" cellpadding="4">';
        
        echo '<tr style="background-color:#f2f2f2;">';
        foreach (array_keys($datos[0]) as $columna) {
            if (!in_array($columna, ['usuario_id', 'cliente_id'])) {
                echo '<th>'.ucwords(str_replace('_', ' ', $columna)).'</th>';
            }
        }
        echo '</tr>';
        
        foreach ($datos as $fila) {
            echo '<tr>';
            foreach ($fila as $columna => $valor) {
                if (!in_array($columna, ['usuario_id', 'cliente_id'])) {
                    if (in_array($columna, ['fecha', 'fecha_creacion', 'fecha_vencimiento'])) {
                        $valor = formatearFecha($valor, 'd/m/Y');
                    } elseif (strpos($columna, 'monto') !== false || strpos($columna, 'total') !== false || strpos($columna, 'precio') !== false) {
                        $valor = '₡'.number_format($valor, 2);
                    } elseif ($columna === 'metodo' || $columna === 'estado') {
                        $valor = ucfirst($valor);
                    }
                    
                    echo '<td>'.htmlspecialchars($valor).'</td>';
                }
            }
            echo '</tr>';
        }
        echo '</table>';
    }
    
    echo '</body>';
    echo '</html>';
    exit;
}