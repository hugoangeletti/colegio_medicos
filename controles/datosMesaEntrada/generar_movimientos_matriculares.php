<?php
require_once($pathOrigen.'../tcpdf/config/lang/spa.php');
require_once($pathOrigen.'../tcpdf/tcpdf.php');

class MYPDF extends TCPDF 
{
        //Page header
        public function Header() 
        {
            /*
                // Logo
                $image_file = '../../public/images/logo_colmed1_lg.png';
                $this->Image($image_file, 10, 5, 170, 20, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
                 // Set font
                $this->SetFont('helvetica', 'B', 20);
                // Title
                $this->Cell(0, 15, '', 0, false, 'C', 0, 'Nota', 0, false, 'M', 'M');

                //MARCA DE AGUA 
                $bMargin = $this->getBreakMargin();
                $auto_page_break = $this->AutoPageBreak;
                $this->SetAutoPageBreak(false, 0);

                $img_file2 = '../../public/images/fondoCertificadoClaro.jpg';
                $this->Image($img_file2, 15, 25, 180, 180, '', '', 'C', false, 300, '', false, false, 0);
                $this->SetAutoPageBreak($auto_page_break, $bMargin);
                $this->setPageMark();
                //FIN MARCA DE AGUA 
        
             * 
             */
        }

        // Page footer
        public function Footer() {
                // Position at 15 mm from bottom
                //$this->SetY(-15);
                // Set font
                //$this->SetFont('helvetica', 'I', 8);

                //$this->Cell(0, 10, 'Relaciones con la comunidad', 0, false, 'C', 0, '', 0, false, 'T', 'M');
                //$this->Ln(3);
                // Page number
                //$this->Cell(0, 5, 'Pag. '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        }
}

if (isset($idMesaEntrada) && $idMesaEntrada > 1) {
    //guarda pdf
    /* armamaos el path donde se va a guardar el pdf */
    $camino = $_SERVER['DOCUMENT_ROOT'];
    $camino .= PATH_PDF.'/archivos/tmp/';
    $nombreArchivo = $camino.$idMesaEntrada.'.pdf';
    //echo $nombreArchivo; 
    //exit;
    if (!file_exists($camino)) {
        mkdir($camino, 0777, true);
    }

    //si el pdf ya existe, lo elimino
    if (file_exists($nombreArchivo)) {
        unlink($nombreArchivo);  
    }      

    $pdf = new MYPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
    $pdf->SetPrintHeader(true);
    $pdf->SetPrintFooter(true);
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    //$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    $pdf->SetFooterMargin(0);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    $pdf->SetFont('dejavusans', '', 10);
    $pdf->AddPage();

    //imprimo la planilla
    $image_file = '../public/images/logo_colmed1_hr.png';

    $pdf->Image($image_file, 35, 5, 80, 20, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
    $pdf->SetFont('dejavusans', 'B', 14);
    $pdf->MultiCell(0, 10, 'HOJA DE RUTA', 0, 'L', false, 1, '120', '');
    $pdf->SetFont('dejavusans', 'B', 12);
    $pdf->MultiCell(0, 7, 'MESA ENTRADA Nº '.$idMesaEntrada, 0, 'L', false, 1, '120', '');
    $pdf->MultiCell(0, 7, 'REUNIÓN MESA Nº ', 0, 'L', false, 1, '120', '');
    $pdf->MultiCell(0, 7, 'Fecha: '.cambiarFechaFormatoParaMostrar($fechaIngreso), 0, 'L', false, 1, '120', '');
    //recuadro numero de reunion de mesa
    $pdf->Line(168, 21, 190, 21, array('width' => 0.50));
    $pdf->Line(168, 21, 168, 28, array('width' => 0.50));
    $pdf->Line(168, 28, 190, 28, array('width' => 0.50));
    $pdf->Line(190, 21, 190, 28, array('width' => 0.50));
    //fin recuadro numero de reunion de mesa

    //cuerpo
    if ($idTipoMovimiento <> 10 && $idTipoMovimiento <> 8 && $idTipoMovimiento <> 43 && $idTipoMovimiento <> 5) {
        //si el tipo de movimiento no es 10 ni 8 (Colegiado del distrito I, inscripto a otro distrito, Inscripto al Distrito I), ni 5 (Ingreso Definitivo)
        $x_inicio = 10;
        $y_inicio = 55;
        $x_fin = $x_inicio + 190; 
        $y_fin = $y_inicio + 60; 
        $y_fin_linea = $y_fin + 140;
    } else {
        //datos remitente y tema
        $pdf->SetFont('dejavusans', 'B', 8);
        $pdf->MultiCell(0, 5, 'Matrícula: ', 0, 'L', false, 0, '', '');
        $pdf->SetFont('dejavusans', '', 8);
        $pdf->MultiCell(0, 5, $matricula, 0, 'L', false, 1, '35', '');
        $pdf->SetFont('dejavusans', 'B', 8);
        $pdf->MultiCell(0, 5, 'Apellido y Nombre: ', 0, 'L', false, 0, '', '');
        $pdf->SetFont('dejavusans', '', 8);
        $pdf->MultiCell(0, 5, $nombreRemitente, 0, 'L', false, 1, '48', '');
        if (isset($estadoMatricular) && $estadoMatricular <> "") {
            $pdf->SetFont('dejavusans', 'B', 8);
            $pdf->MultiCell(0, 5, 'Estado matricular al momento del trámite: ', 0, 'L', false, 0, '', '');
            $pdf->SetFont('dejavusans', '', 8);
            $pdf->MultiCell(0, 5, $elEstado, 0, 'L', false, 1, '90', '');
            $pdf->SetFont('dejavusans', 'B', 8);
            $pdf->MultiCell(0, 5, 'Estado con tesorería al momento del trámite: ', 0, 'L', false, 0, '', '');
            $pdf->SetFont('dejavusans', '', 8);
            $pdf->MultiCell(0, 5, $elEstadoTesoreria, 0, 'L', false, 1, '90', '');
        }
        if ($idTipoMovimiento == 8) {
            $pdf->SetFont('dejavusans', 'B', 8);
            $pdf->MultiCell(0, 5, 'Movimiento Solicitado: ', 0, 'L', false, 0, '', '');
            $pdf->SetFont('dejavusans', '', 8);
            $pdf->MultiCell(0, 5, 'Inscripto al Distrito I. Colegiado del Distrito '.$distrito, 0, 'L', false, 0, '55', '');
            //cuerpo
            $x_inicio = 10;
            $y_inicio = 65;
            $x_fin = $x_inicio + 190; //200;
            $y_fin = $y_inicio + 80; //185;
            $y_fin_linea = $y_fin + 100;
        } else {
            $pdf->SetFont('dejavusans', 'B', 8);
            $pdf->MultiCell(0, 5, 'Movimiento Solicitado: ', 0, 'L', false, 0, '', '');
            $pdf->SetFont('dejavusans', '', 8);
            if ($idTipoMovimiento == 43) {
                $pdf->MultiCell(0, 5, 'Cancelación por Decreto-Ley 5413/58 Art.40c.', 0, 'L', false, 1, '90', '');
            } else {
                if ($idTipoMovimiento == 5) {
                    $pdf->MultiCell(0, 5, 'Ingreso definitivo al distrito I.', 0, 'L', false, 1, '90', '');
                } else {
                    if ($idTipoMovimiento == 8) {
                        $pdf->MultiCell(0, 5, 'Inscripto al Distrito I.', 0, 'L', false, 1, '90', '');
                    } else {
                        $pdf->MultiCell(0, 5, 'Colegiado del distrito I, inscripto a otro distrito.', 0, 'L', false, 1, '90', '');
                    }
                }
                $pdf->MultiCell(0, 5, 'Distrito de cambio: '.$distrito, 0, 'L', false, 1, '90', '');
            }
            //cuerpo
            $x_inicio = 10;
            $y_inicio = 80;
            $x_fin = $x_inicio + 190; //200;
            $y_fin = $y_inicio + 80; //185;
            $y_fin_linea = $y_fin + 100;
        }
        $pdf->SetFont('dejavusans', 'B', 10);
        $pdf->Ln(6);

    }
    //titulo decision mesa 
    $pdf->SetXY($x_inicio, $y_inicio - 5);
    $pdf->MultiCell(120, 7, 'Decisión de la Mesa Directiva', 0, 'C', false, 0, '35', '');
    $pdf->MultiCell(25, 7, 'Firma', 0, 'C', false, 1, '166', '');
    //fin titulo decision mesa 

    //recuadro decision mesa 
    $pdf->Line($x_inicio, $y_inicio, $x_fin, $y_inicio, array('width' => 1));
    $pdf->Line($x_inicio, $y_inicio, $x_inicio, $y_fin, array('width' => 1));
    $pdf->Line($x_inicio + 155, $y_inicio, $x_inicio + 155, $y_fin, array('width' => 1));
    $pdf->Line($x_fin, $y_inicio, $x_fin, $y_fin, array('width' => 1));
    $pdf->Line($x_inicio, $y_fin, $x_fin, $y_fin, array('width' => 1));
    //fin recuadro decision mesa 

    //si el tipo de movimiento no es 10 (Colegiado del distrito I, inscripto a otro distrito)
    if ($idTipoMovimiento <> 10 && $idTipoMovimiento <> 8 && $idTipoMovimiento <> 43 && $idTipoMovimiento <> 5) {
        $resPresidente = $mesaEntradaLogic->obtenerPresidenteDistrito(1);
        if ($resPresidente['estado']) {
            $presidente = $resPresidente['datos'];
            $presidenteDistritoI = $presidente['presidente'];
        } else {
            $presidenteDistritoI = NULL;
        }
        //cuerpo segun tipo de movimiento
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->SetXY($x_inicio, $y_fin + 5);
        $pdf->MultiCell(120, 5, 'Señor', 0, 'L', false, 1, $x_inicio, '');
        $pdf->MultiCell(120, 5, 'Presidente del', 0, 'L', false, 1, $x_inicio, '');
        $pdf->MultiCell(120, 5, 'Colegio de Médicos - Distrito I', 0, 'L', false, 1, $x_inicio, '');
        if (isset($presidenteDistritoI) && $presidenteDistritoI <> "") {
            $pdf->MultiCell(120, 5, $presidenteDistritoI, 0, 'L', false, 1, $x_inicio, '');
        }
        $pdf->MultiCell(25, 5, 'S/D', 0, 'L', false, 1, $x_inicio, '');

        if ($sexo == "F") {
            $dr_dra = "a la Dra.";
        } else {
            $dr_dra = "al Dr.";
        }

        $html = "";
        switch ($idTipoMovimiento) {
            case 2:
            case 27:
                //Cancelacion Transitoria
                $html .= '<p style="line-height: 15em;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        Tengo el agrado de dirigirme a Usted, y por su intermedio a quien corresponda, con el motivo de solicitarle la <b>CANCELACIÓN TRANSITORIA</b> de la M.P. Nº <b>'.$matricula.'</b> perteneciente '.$dr_dra.' <b>'.$nombreRemitente.'</b> a partir del día <b>'.cambiarFechaFormatoParaMostrar($fechaMoviento).'.-</b></p>
                        <p style="line-height: 15em;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        Sin otro particular, saluda muy atentamente.-</p>
                        <br><br>
                        <p style="line-height: 15em;">* SE ADJUNTA FOTOCOPIA DEL CERTIFICADO MÉDICO.</p>
                        <br><br>
                        <p style="line-height: 15em; text-align: rigth;">
                        Firma: ______________________________<br><br>
                        Aclaración.  ______________________________<br><br>
                        M.P. ______________________________</p>'; 
                break;
            case 3:
                //Cancelacion Definitiva
                $html .= '<p style="line-height: 15em;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        Tengo el agrado de dirigirme a Usted, y por su intermedio a quien corresponda, con el motivo de solicitarle la <b>CANCELACIÓN DEFINITIVA</b> de la M.P. Nº <b>'.$matricula.'</b> perteneciente '.$dr_dra.' <b>'.$nombreRemitente.'</b> a partir del día <b>'.cambiarFechaFormatoParaMostrar($fechaMoviento).'</b> dado que no ejerceré más mi profesión en La Provincia de Buenos Aires.-</p>
                        <p style="line-height: 15em;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        Sin otro particular, saluda muy atentamente.-</p>
                        <br><br>
                        <p style="line-height: 15em; text-align: rigth;">
                        Firma: ______________________________<br><br>
                        Aclaración.  ______________________________<br><br>
                        M.P. ______________________________</p>
                        <br><br>
                        <p><b>Estado Matricular al momento del trámite: </b>'.$elEstado.'.</p>
                        <p><b>Estado con Tesorería al momento del trámite: </b>'.$elEstadoTesoreria.'.</p>';
                break;
            case 20:
                //Rehabilitacion
                $html .= '<p style="line-height: 15em;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        Tengo el agrado de dirigirme a Usted con motivo de solicitar la <b>REHABILITACIÓN</b> de mi matrícula provincial Nº <b>'.$matricula.'</b> perteneciente '.$dr_dra.' <b>'.$nombreRemitente.'</b> a partir del día <b>'.cambiarFechaFormatoParaMostrar($fechaMoviento).'</b>.-</p>
                        <p style="line-height: 15em;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        Sin otro particular, saluda muy atentamente.-</p>
                        <br><br>
                        <p style="line-height: 15em; text-align: rigth;">
                        Firma: ______________________________<br><br>
                        Aclaración.  ______________________________<br><br>
                        M.P. ______________________________</p>';
                break;

            case 11:
            case 14:
            case 25:
            case 26:
            case 42:
                //Jubilacion
                $tipoJubilacion = "";
                switch ($idTipoMovimiento)
                {
                    case 11:
                    case 25:
                    case 42:
                        $tipoJubilacion = "JUBILACIÓN ORDINARIA";
                        break;

                    case 14:
                    case 26:
                        $tipoJubilacion = "JUBILACIÓN EXTRAORDINARIA";
                        break;

                }

                $html .= '<p style="line-height: 15em;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        Tengo el agrado de dirigirme a Usted con motivo de solicitar la <b>CANCELACIÓN DEFINITIVA</b> de la M.P.  Nº <b>'.$matricula.'</b> perteneciente '.$dr_dra.' <b>'.$nombreRemitente.'</b> a partir del día <b>'.cambiarFechaFormatoParaMostrar($fechaMoviento).'</b> con motivo de <b>'.$tipoJubilacion.'</b>.-</p>
                        <p style="line-height: 15em;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        Sin otro particular, saluda muy atentamente.-</p>
                        <br><br>
                        <p style="line-height: 15em; text-align: rigth;">
                        Firma: ______________________________<br><br>
                        Aclaración.  ______________________________<br><br>
                        M.P. ______________________________</p>';
                break;

            case 44:
            case 45:
                if ($idTipoMovimiento == 44) {
                    //baja de inscripción por jubilación ordinaria (otro distrito)
                    $tipoMovimiento = "ORDINARIA";
                } else {
                    //baja de inscripción por jubilación extraordinaria (otro distrito)
                    $tipoMovimiento = "EXTRAORDINARIA";
                }

                $html .= '<p style="line-height: 15em;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        Tengo el agrado de dirigirme a Usted con motivo de solicitar la &nbsp;<b>&nbsp; BAJA DE INSCRIPCIÓN POR JUBILACIÓN '.$tipoMovimiento.' (OTRO DISTRITO) </b>de la M.P.  Nº <b>'.$matricula.'</b> perteneciente '.$dr_dra.' <b>'.$nombreRemitente.'</b> a partir del día <b>'.cambiarFechaFormatoParaMostrar($fechaMoviento).'</b>.-</p>
                        <p style="line-height: 15em;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        Sin otro particular, saluda muy atentamente.-</p>
                        <br><br>
                        <p style="line-height: 15em; text-align: rigth;">
                        Firma: ______________________________<br><br>
                        Aclaración.  ______________________________<br><br>
                        M.P. ______________________________</p>';
                break;

            case 7:
                //Defuncion
                $html .= '<p style="line-height: 15em;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        Tengo el agrado de dirigirme a Usted con motivo de solicitar la <b>CANCELACIÓN DEFINITIVA</b> de la M.P.  Nº <b>'.$matricula.'</b> perteneciente '.$dr_dra.' <b>'.$nombreRemitente.'</b> a partir del día <b>'.cambiarFechaFormatoParaMostrar($fechaMoviento).'</b> según consta en el <b>CERTIFICADO DE DEFUNCIÓN</b> adjunto a la presente nota.-</p>
                        <p style="line-height: 15em;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        Sin otro particular, saluda muy atentamente.-</p>
                        <br><br>
                        <p style="line-height: 15em; text-align: rigth;">
                        Firma: ______________________________<br><br>
                        Aclaración:  ______________________________<br><br>
                        Parentesco: ______________________________<br><br>
                        Dirección: ______________________________</p>';
                break;
            case 6:
                //Egreso definitivo
                $html .= '<p style="line-height: 15em;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        Tengo el agrado de dirigirme a Usted con motivo de solicitar la &nbsp;<b>BAJA POR EGRESO DEFINITIVO</b> de mi matrícula provincial Nº <b>'.$matricula.'</b> perteneciente '.$dr_dra.' <b>'.$nombreRemitente.'</b> a partir del día <b>'.cambiarFechaFormatoParaMostrar($fechaMoviento).'</b>, con motivo de haber dejado de ejercer la profesión en jurisdicción de este Distrito I, para continuar en el <b>Distrito____</b>.-</p>
                        <p style="line-height: 15em;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        Sin otro particular, saluda muy atentamente.-</p>
                        <br><br>
                        <p style="line-height: 15em; text-align: rigth;">
                        Firma: ______________________________<br><br>
                        Aclaración:  ______________________________<br><br>
                        M.P.: ______________________________<br><br>
                        Domicilio: ______________________________<br><br>
                        Localidad: ______________________________<br><br>
                        Teléfono: ______________________________</p>';
                break;

            case 30;
                //Baja de Inscripción
                $html .= '<p style="line-height: 15em;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        Tengo el agrado de dirigirme a Usted con motivo de solicitar la <b>BAJA DE INSCRIPCIÓN</b> de la M.P.  Nº <b>'.$matricula.'</b> perteneciente '.$dr_dra.' <b>'.$nombreRemitente.'</b> a partir del día <b>'.cambiarFechaFormatoParaMostrar($fechaMoviento).'</b>.-</p>
                        <p style="line-height: 15em;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        Sin otro particular, saluda muy atentamente.-</p>
                        <br><br>
                        <p style="line-height: 15em; text-align: rigth;">
                        Firma: ______________________________<br><br>
                        Aclaración.  ______________________________<br><br>
                        M.P. ______________________________</p>';
                break;

        }
        $pdf->writeHTMLCell(0, 0, $x_inicio, '', $html, 0, 1, 0, true, 'J', true);
    }

    //linea final
    $pdf->Line($x_inicio, $y_fin_linea, $x_fin, $y_fin_linea, array('width' => 1));
    $pdf->SetFont('dejavusans', '', 8);
    $pdf->SetXY($x_inicio, $y_fin_linea);
    $pdf->Ln(5);
    $pdf->MultiCell(50, 7, 'Realizó: '.$nombreUsuario, 0, 'L', false, 0, $x_inicio, '');
    $pdf->MultiCell(50, 7, 'Emitido el: '.date('d/m/Y H:i:s'), 0, 'R', false, 0, '150', '');
    $pdf->lastPage();

    //ob_clean();
    /* Finalmente generamos el PDF */
    $pdf->Output($nombreArchivo, 'F');       

    if (file_exists($nombreArchivo)) {
        $pdf_content = file_get_contents($nombreArchivo);        
        $hojaRutaPDF = base64_encode($pdf_content);
    } else {
        echo 'no pudo generar recibo';
        $hojaRutaPDF = NULL;
    }
} else {
    echo 'no pudo generar recibo - ingreso incorrecto';
    $hojaRutaPDF = NULL;
}
