<?php
require_once('../tcpdf/config/lang/spa.php');
require_once('../tcpdf/tcpdf.php');

class MYPDF extends TCPDF 
{
        //Page header
        public function Header() 
        {
                // Logo
                $image_file = '../public/images/logo_colmed1_lg.png';
                $this->Image($image_file, 10, 5, 190, 20, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
                 // Set font
                $this->SetFont('helvetica', 'B', 20);
                // Title
                $this->Cell(0, 15, '', 0, false, 'C', 0, 'Nota', 0, false, 'M', 'M');
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


$pdf = new MYPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
$pdf->SetPrintHeader(true);
$pdf->SetPrintFooter(false);
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->SetFont('dejavusans', '', 8);
$pdf->AddPage();

if (isset($_GET['idColegiado'])) {
    $idColegiado = $_GET['idColegiado'];
    $tipoDebito = $_GET['tipo'];
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado']) {
        $colegiado = $resColegiado['datos'];
        $matricula = $colegiado['matricula'];

        //obtengo los telefonos y el correo
        $telefonoFijo = null;
        $telefonoMovil = null;
        $mail = null;
        $resContacto = $colegiadoContactoLogic->obtenerColegiadoContactoPorIdColegiado($idColegiado);
        if ($resContacto['estado']) {
            $contacto = $resContacto['datos'];
            $telefonoFijo = $contacto['telefonoFijo'];
            if (strtoupper($telefonoFijo) == 'NR') {
                $telefonoFijo = "";
            }
            $telefonoMovil = $contacto['telefonoMovil'];
            if (strtoupper($telefonoMovil) == 'NR') {
                $telefonoMovil = "";
            }
            if (isset($contacto['email'])) {
                $mail = $contacto['email'];
            } else {
                $mail = '';
            }
        } 
        
        //obtengo los datos del debito
        switch ($tipoDebito) {
            case 'C':
            case 'D':
                $resDebito = $colegiadoDebitosLogic->obtenerDebitoPorIdColegiado($idColegiado);
                $titulo = 'DEBITO AUTOMATICO DE TARJETA VISA';
                $debitar = 'TARJETA VISA';
                break;

            case 'H':
                $resDebito = $colegiadoDebitosLogic->obtenerDebitoCBUPorIdColegiado($idColegiado);
                $titulo = 'DEBITO AUTOMATICO POR CBU';
                break;

            default:
                $resDebito['estado'] = FALSE;
                break;
        }
        
        if ($resDebito['estado']) {
            $debito = $resDebito['datos'];
            $pathArchivo = $debito['pathArchivo'];
            $nombreArchivo = $debito['nombreArchivo'];
            $tipoArchivo = $debito['tipoArchivo'];

            if (!isset($nombreArchivo) || $nombreArchivo == "") {
                $subCarpeta = date('Y');
                $pathArchivo = '/archivos/debito_automatico/'.$subCarpeta.'/';
                $nombreArchivo = 'DebitoAutomatico_'.$matricula.'_'.date('YmdHis').'.pdf';
            }

            $camino = $_SERVER['DOCUMENT_ROOT'];
            $camino .= PATH_PDF.$pathArchivo;
            if (!file_exists($camino)) {
                mkdir($camino, 0777, true);
            }

            //si el pdf ya existe, no lo vuelvo a generar
            $archivoCompleto = $camino.$nombreArchivo;
            if (file_exists($archivoCompleto)) {
                $pdf_content = file_get_contents($archivoCompleto);        
                $adhesionDebitoPDF = base64_encode($pdf_content);
            } else {
                if ($tipoDebito == 'H') {
                    if ($debito['tipo'] == '3') {
                        $debitar = 'CUENTA CORRIENTE';
                    } else {
                        if ($debito['tipo'] == '4') {
                            $debitar = 'CAJA DE AHORRO';
                        } else {
                            $debitar = 'CUENTA CORRIENTE / CAJA DE AHORRO';
                        }
                    }
                }
                //obtengo el banco
                $nombreBanco = "Sin entidad asignada";
                if (isset($debito['idBanco'])) {
                    $bancoLogic = new bancoLogic();
                    $resBanco = $bancoLogic->obtenerBancoPorId($debito['idBanco']);
                    if ($resBanco['estado']){
                        $banco = $resBanco['datos'];
                        $nombreBanco = $banco['nombre'];
                    }
                } 
                
                //imprimo la planilla
                $pdf->SetFont('dejavusans', 'B', 12);
                $pdf->MultiCell(0, 5, $titulo, 0, 'C', false, 0, '', '');
                $pdf->Ln(10);
                $pdf->SetFont('dejavusans', '', 10);
                if ($tipoDebito == 'H') {
                    $html = '<b>INSTRUCTIVO</b>: Para adherir al debito automatico de su Caja de Ahorro o Cuenta Corriente, será necesario <u>completar la planila de adhesión al débito '.
                             'automático y adjuntar a la misma una fotocopia del CBU de la cuenta</u> de la cual se debitarán las cuotas de colegiación.<br>'.
                             '<u>Es requisito indispensable estar al día con las cuotas anteriores de colegiación, como así también que sean legibles los dígitos que '.
                             'figuran al frente de la tarjeta visa de crédito. De otra manera, no se aceptará la adhesión al débito automático.</u><br>'.
                             'El sistema de débito automático utiliza los dígitos que figuran en el CBU de la cuenta. Si existe algún tipo de problema con el sistema '.
                             'de débito automático se le enviará un e-mail a la casilla de correo que registró al momento de la adhesion (es obligatorio registrar una '.
                             'casilla de e-mail).<br>'.
                             '<u>En caso de que el problema en el débito automático (como ser cuenta sin fondos, cuenta cerrada, etc.) '.
                             'persista durante tres (3) meses consecutivos, se le dará de baja a la adhesión.</u><br>'.
                             'La planilla de adhesion tendrá que ser presentada en Mesa de Entradas del Colegio de Médicos - Distrito I, y no será valida sin la firma orginal del '.
                             'colegiado. Todos los datos de la planilla son obligatorios, en caso de que algun campo no haya sido completado no se aceptará la adhesión.<br>';
                } else {
                    $html = '<b>INSTRUCTIVO</b>: Para adherir al debito automatico de tarjeta de credito VISA, será necesario <u>completar la planila de adhesión '.
                             'al débito automático y adjuntar a la misma una fotocopia de la tarjeta visa</u> de la cual se debitarán las cuotas de colegiación.<br>'.
                             'Es requisito indispensable estar al día con las cuotas anteriores de colegiación, <u>como así también que sean legibles los dieciseis (16) dígitos que '.
                             'figuran al frente de la tarjeta visa de crédito. De otra manera, no se aceptará la adhesión al débito automático.</u><br>'.
                             'El sistema de débito automático utiliza los dieciseís (16) dígitos que figuran al frente de la tárjeta visa de crédito. Por esta razon, una vez '.
                             'que esté adherido al sistema de débito automático, en caso de cambiar la tarjeta (el plástico) deberá informar inmediatamente a la Tesorería del '.
                             'Colegio de Médicos - Distrito I. Si existe algún tipo de problema con el sistema de débito automático se le enviará un e-mail a la casilla de correo '.
                             'que registró al momento de la adhesion (es obligatorio registrar una casilla de e-mail).<br>'.
                             '<u>En caso de que el problema en el débito automático (tarjeta vencida, tarjeta no operativa, tarjeta no dada de alta, tarjeta o cuenta sin fondos, etc.) '.
                             'persista durante tres (3) meses consecutivos, se le dará de baja a la adhesión.</u><br>'.
                             'La planilla de adhesion tendrá que ser presentada en Mesa de Entradas del Colegio de Médicos - Distrito I, y no será valida sin la firma orginal del '.
                             'colegiado. Todos los datos de la planilla son obligatorios, en caso de que algun campo no haya sido completado no se aceptará la adhesión.<br>';
                }
                $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, 'J', true);
                $pdf->MultiCell(0, 1, '____________________________________________________________________________________________________', 0, 'L', false, 1, '', '', true);
                $pdf->Ln(5);
                $pdf->MultiCell(0, 5, 'La Plata, '.date('d').' de '.obtenerMes(date('m')).' de '.date('Y'), 0, 'R', false, 0, '50', '');
                $pdf->Ln(5);
                $pdf->MultiCell(0, 5, 'Al Señor Tesorero del', 0, 'L', false, 0, '', '', true);
                $pdf->Ln(5);
                $pdf->MultiCell(0, 5, 'Colegio de Médicos de la Provincia de Buenos Aires - Distrito I', 0, 'L', false, 0, '', '', true);
                $pdf->Ln(5);
                $pdf->MultiCell(0, 5, 'S/D', 0, 'L', false, 0, '', '', true);
                $pdf->Ln(5);
                $html = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Por la presente, <b>conociendo el instructivo de adhesión</b> '.
                        'al débito automático de la cuota de colegiación del Colegio de Médicos de La Provincia de Buenos Aires - Distrito  I, '.
                        '<b>autorizo</b> a la Tesorería del Distrito I a descontar de mi <b>'.$debitar.'</b>, el valor '.
                        'de las cuotas correspondientes a mi Matrícula Provincial Nº: <b>'.$matricula.'</b><br>';
                $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, 'J', true);
                $pdf->Ln(10);
                $pdf->MultiCell(0, 5, 'Firma  y  aclaración: _______________________________', 0, 'L', false, 0, '', '', true);
                $pdf->Ln(15);
                            
                $posLabel = 40;
                $posDato = 100;
                
                $pdf->RoundedRect($posLabel-10, 195, 155, 45, 3.50, '', '');
                $pdf->SetFont('dejavusans', 'B', 12);
                $pdf->MultiCell(0, 5, 'Apellido y Nombres:', 0, 'L', false, 0, $posLabel, '200', true);
                $pdf->SetFont('dejavusans', '', 12);
                $pdf->MultiCell(0, 5, $colegiado['apellido'].", ".$colegiado['nombre'], 0, 'L', false, 1, $posDato, '', true);
                if ($tipoDebito <> 'H') {
                    $pdf->SetFont('dejavusans', 'B', 12);
                    $pdf->MultiCell(0, 5, 'Documento:', 0, 'L', false, 0, $posLabel, '', true);
                    $pdf->SetFont('dejavusans', '', 12);
                    $pdf->MultiCell(0, 5, $debito['numeroDocumento'], 0, 'L', false, 1, $posDato, '', true);
                }
                $pdf->SetFont('dejavusans', 'B', 12);
                $pdf->MultiCell(0, 5, 'Matrícula:', 0, 'L', false, 0, $posLabel, '', true);
                $pdf->SetFont('dejavusans', '', 12);
                $pdf->MultiCell(0, 5, $colegiado['matricula'], 0, 'L', false, 1, $posDato, '', true);
                $pdf->SetFont('dejavusans', 'B', 12);
                $pdf->MultiCell(0, 5, 'Teléfono:', 0, 'L', false, 0, $posLabel, '', true);
                $pdf->SetFont('dejavusans', '', 12);
                $pdf->MultiCell(0, 5, $telefonoFijo.' - '.$telefonoMovil, 0, 'L', false, 1, $posDato, '', true);
                $pdf->SetFont('dejavusans', 'B', 12);
                $pdf->MultiCell(0, 5, 'E-mail:', 0, 'L', false, 0, $posLabel, '', true);
                $pdf->SetFont('dejavusans', '', 12);
                $pdf->MultiCell(0, 5, $mail, 0, 'L', false, 1, $posDato, '', true);
                if ($tipoDebito == 'H') {
                    $pdf->SetFont('dejavusans', 'B', 12);
                    $pdf->MultiCell(0, 5, 'Tipo de cuenta y CBU:', 0, 'L', false, 0, $posLabel, '', true);
                    $pdf->SetFont('dejavusans', '', 12);
                    $pdf->MultiCell(0, 5, $debitar.' '.$debito['numeroCbu'], 0, 'L', false, 1, $posDato, '', true);
                } else {
                    $pdf->SetFont('dejavusans', 'B', 12);
                    $pdf->MultiCell(0, 5, 'Número de Tarjeta:', 0, 'L', false, 0, $posLabel, '', true);
                    $pdf->SetFont('dejavusans', '', 12);
                    $pdf->MultiCell(0, 5, $debito['numeroTarjeta'], 0, 'L', false, 1, $posDato, '', true);
                }
                $pdf->SetFont('dejavusans', 'B', 12);
                $pdf->MultiCell(0, 5, 'Entidad emisora:', 0, 'L', false, 0, $posLabel, '', true);
                $pdf->SetFont('dejavusans', '', 12);
                $pdf->MultiCell(0, 5, $nombreBanco, 0, 'L', false, 1, $posDato, '', true);
                $pdf->Ln(20);

                $pdf->SetFont('dejavusans', '', 10);
                $pdf->MultiCell(0, 5, 'Recibido por '.$_SESSION['user_entidad']['nombreUsuario'].': _______________________________                    Sello de Entrada', 0, 'L', false, 0, '', '', true);
    //            $pdf->Ln(5);
    //            $pdf->MultiCell(0, 5, $_SESSION['user_entidad']['nombreUsuario'], 0, 'L', false, 0, '50', '', true);
                //$pdf->writeHTML($html, true, false, true, false, '');
    //            $pdf->lastPage();

                /*
                $destination='DebitoAutomatico_'.$matricula.'.pdf';
                if (!preg_match('/\.pdf$/', $path_to_store_pdf))
                {
                       $path_to_store_pdf .= '.pdf';
                }
                ob_clean();
                if ($destination == 'D')
                {
                       echo $this->view->pdf->Output($path_to_store_pdf, $destination);
                       exit();
                }

                $pdf->Output('DebitoAutomatico_'.$matricula.'.pdf', 'I');
                */
                $pdf->Output($archivoCompleto, 'F');       
                if (file_exists($archivoCompleto)) {
                    //marca el enviamail para el envio automatico
                    $resDebito = $colegiadoDebitosLogic->guardarDebitoArchivo($idColegiado, $tipoDebito, $pathArchivo, $nombreArchivo, 'Pdf');
                    //obtiene el recibo y lo guarda como base64 para mostrar
                    $pdf_content = file_get_contents($archivoCompleto);        
                    $adhesionDebitoPDF = base64_encode($pdf_content);
                } else {
                    echo 'no pudo generar recibo';
                    $adhesionDebitoPDF = NULL;
                }
            }
        }
    }

}
