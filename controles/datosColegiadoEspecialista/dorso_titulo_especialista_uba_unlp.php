<?php
    //dorso
    $pdf->AddPage('L', $medidas);

    $pdf->SetFont($fontname, '', 18);
    //recuadro numero de reunion de mesa
    $pdf->Line(68, 30, 200, 30, array('width' => 0.50));
    $pdf->Line(68, 30, 68, 95, array('width' => 0.50));
    $pdf->Line(68, 95, 200, 95, array('width' => 0.50));
    $pdf->Line(200, 30, 200, 95, array('width' => 0.50));
    //fin recuadro numero de reunion de mesa    $pdf->lastPage();

    $html = 'La presente certificación de Especialista se adecúa al Programa Nacional de Garantía de  Calidad de la Atención Médica según Resolución 946/02 del Ministerio de Salud de la Nación.';
    $x = 78;
    $y = 35;
    $pdf->writeHTMLCell(112, 0, $x, $y, $html, 0, 1, 0, true, 'J', true);

    $pdf->SetFont($fontname, '', 11);
    $pdf->SetY(80);
    $pdf->MultiCell(50, 5, $firmaColegio2, 0, 'C', false, 0, 75, '');
    $pdf->MultiCell(50, 5, $firmaColegio1, 0, 'C', false, 1, 150, '');
    $pdf->SetY(85);
    $pdf->MultiCell(60, 5, $cargoColegio2, 0, 'C', false, 0, 70, '');
    $pdf->MultiCell(50, 5, $cargoColegio1, 0, 'C', false, 1, 150, '');
    $pdf->SetY(90);
    $pdf->MultiCell(50, 5, 'Distrito I', 0, 'C', false, 0, 75, '');
    $pdf->MultiCell(50, 5, 'Distrito I', 0, 'C', false, 1, 150, '');

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
    $pdf->write2DBarcode($codigoQR, 'QRCODE,Q', 220, 30, 25, 25, $style, 'N');
    $image_logo_web = '../public/images/logo_titulos.png';
    $pdf->Image($image_logo_web, 230, 40, 5, 5, 'png', '', 'T', false, '', '', false, false, 0, false, false, false);
    //$pdf->write2DBarcode($codigoQR, 'QRCODE,Q', 68,85,25,25, $style, 'N');
    //$pdf->Image($image_logo_web, 78, 95, 5, 5, 'png', '', 'T', false, '', '', false, false, 0, false, false, false);
?>