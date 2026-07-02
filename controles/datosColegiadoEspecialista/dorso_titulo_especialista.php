<?php
    //dorso
    $pdf->AddPage('L', $medidas);

    $pdf->SetFont('freesans', '', 14);
    //recuadro numero de reunion de mesa
    $pdf->Line(68, 30, 200, 30, array('width' => 0.50));
    $pdf->Line(68, 30, 68, 75, array('width' => 0.50));
    $pdf->Line(68, 75, 200, 75, array('width' => 0.50));
    $pdf->Line(200, 30, 200, 75, array('width' => 0.50));
    //fin recuadro numero de reunion de mesa    $pdf->lastPage();

    $pdf->SetY(35);
    $pdf->MultiCell(0, 7, 'Apellido: '.$apellido, 0, 'L', false, 1, 70, '');
    $pdf->MultiCell(0, 7, 'Nombre:   '.$nombre, 0, 'L', false, 1, 70, '');
    $pdf->MultiCell(0, 7, 'Matrícula Provincial: '.$matricula, 0, 'L', false, 1, 70, '');
    $pdf->MultiCell(0, 7, 'Resolución N°: '.$numeroResolucion, 0, 'L', false, 1, 70, '');
    $pdf->MultiCell(0, 7, 'Fecha: '.cambiarFechaFormatoParaMostrar($fechaAprobada), 0, 'L', false, 1, 70, '');

    //imprimir QR
    $style = array(
                    'border' => true,
                    'vpadding' => 'auto',
                    'hpadding' => 'auto',
                    'fgcolor' => array(0,0,0),
                    'bgcolor' => false, //array(255,255,255)
                    'module_width' => 1, // width of a single module in points
                    'module_height' => 1 // height of a single module in points
                );
    $codigoQR = 'https://www.colmed1.com.ar/verificar/titulo_especialista.php?id='.$hash_qr;
    $pdf->write2DBarcode($codigoQR, 'QRCODE,Q', 68,85,25,25, $style, 'N');
    $image_logo_web = '../public/images/logo_titulos.png';
    $pdf->Image($image_logo_web, 78, 95, 5, 5, 'png', '', 'T', false, '', '', false, false, 0, false, false, false);
?>