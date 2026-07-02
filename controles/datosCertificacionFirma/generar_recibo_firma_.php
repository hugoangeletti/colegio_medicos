<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/colegiadoLogic.php');
require_once ('../../dataAccess/constanciaFirmaLogic.php');
$constanciaFirmaLogic = new constanciaFirmaLogic();

$continua = TRUE;
$mensaje = "";
$resultado = NULL;
if (isset($_POST['idColegiado']) && $_POST['idColegiado'] <> "") {
    $idColegiado = $_POST['idColegiado'];
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado'] && $resColegiado['datos']) {
        $colegiado = $resColegiado['datos'];
        $matricula = $colegiado['matricula'];
        $apellidoNombre = trim($colegiado['apellido']).', '.trim($colegiado['nombre']);
        $sexo = $colegiado['sexo'];

        switch ($sexo) {
            case '1':
            case 'M':
                $medico_1 = 'el Doctor '.$apellidoNombre;
                $medico_2 = 'del Doctor '.$apellidoNombre;
                break;
            
            case '2':
            case 'F':
                $medico_1 = 'la Doctora '.$apellidoNombre;
                $medico_2 = 'de la Doctora '.$apellidoNombre;
                break;
            
            default:
                $continua = FALSE;
                $mensaje .= "Error sexo. ";
                break;
        }

        $idTipoPago = 62; //certificacion de firma
        $resTipoPago = $tipoPagoLogic->obtenerTipoValorPorId($idTipoPago);
        if ($resTipoPago['estado']) {
            $tipoPago = $resTipoPago['datos'];
            $totalRecibo = $tipoPago['importe'];
        } else {
            $continua = FALSE;
            $mensaje .= "Error buscando imorte. ";
        }

        //agregamos el cartificacion em la tabla constanciafirma
        $resFirma = $constanciaFirmaLogic->agregarConstanciaFirma($idColegiado, $totalRecibo, NULL);
        if ($resFirma['estado']) {
            $idConstanciaFirma = $resFirma['idConstanciaFirma'];
            //armamos el archivo pdf que se va a generar para guardarlo en la tabla
            $subCarpeta = date('Y');
            $camino = $_SERVER['DOCUMENT_ROOT'];
            $camino .= PATH_PDF;
            $nombreArchivo = '/archivos/certificacion_firma/'.$subCarpeta.'/Certificado_Firma_'.$matricula.'_'.$idConstanciaFirma.'.pdf';
            $constanciaFirmaLogic->agregarArchivoEnConstanciaFirma($idConstanciaFirma, $nombreArchivo);
            $_POST['idColegiado'] = NULL;
        } else {
            $continua = FALSE;
            $mensaje .= "Error agregando constancia firma. ";
        }
    } else {
        $continua = FALSE;
        $mensaje .= "Error buscando colegiado. ";
    }
} else {
    //si viene para imprimir el certificado, controla que venga el idConstanciaFirma
    if (isset($_GET['id']) && $_GET['id'] <> "") {
        $idConstanciaFirma = $_GET['id'];
        $resCertificacion = $constanciaFirmaLogic->obtenerCertificacionFirmaPorId($idConstanciaFirma);
        if ($resCertificacion['estado']) {
            $idColegiado = $resCertificacion['datos']['idColegiado'];
            $matricula = $resCertificacion['datos']['matricula'];
            $totalRecibo = $resCertificacion['datos']['imorte'];
            if (isset($resCertificacion['datos']['nombreArchivo']) && $resCertificacion['datos']['nombreArchivo'] <> "") {
                $nombreArchivo = '../'.$resCertificacion['datos']['nombreArchivo'];
                $existaArchivo = TRUE;
            } else {
                $subCarpeta = substr($resCertificacion['datos']['fecha'], 0, 4);
                $camino = $_SERVER['DOCUMENT_ROOT'].'/'.PATH_PDF;
                $path = '/archivos/certificacion_firma/'.$subCarpeta.'/';
                $nombreArchivo = 'Certificado_Firma_'.$matricula.'_'.$idConstanciaFirma.'.pdf';
                //agregamos el cartificacion em la tabla constanciafirma
                $pathArchivo = $path.$nombreArchivo;
                /*
                $resFirma = $constanciaFirmaLogic->agregarArchivoEnConstanciaFirma($idConstanciaFirma, $pathArchivo);
                if ($resFirma['estado']) {
                    $existaArchivo = FALSE;
                } else {
                    $continua = FALSE;
                    $mensaje .= "Error acrualizabdo constancia firma. ";
                }
                */
            }
        } else {
            $continua = FALSE;
            $mensaje .= "Error buscando constancia firma. ";        
        }
    } else {
        $continua = FALSE;
        $mensaje .= "Falta colegiado / constancia firma. ";        
    }
}


if ($continua) {
    if (!$existaArchivo) {
        if (!file_exists($camino.$path)) {
            mkdir($camino.$path, 0777, true);
        }
        if (file_exists('../..'.$pathArchivo)) {
            unlink('../..'.$pathArchivo);
        }
        //echo $camino.$pathArchivo;
        //exit;

        class MYPDF extends TCPDF 
        {
            //Page header
            public function Header() 
            {
                    // Logo
                    $image_file = '../../public/images/logo_colmed1_lg.png';
                    $this->Image($image_file, 10, 5, 170, 20, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
                     // Set font
                    $this->SetFont('helvetica', 'B', 20);
                    // Title
                    $this->Cell(0, 15, '', 0, false, 'C', 0, 'Nota', 0, false, 'M', 'M');

                    /*
                    //MARCA DE AGUA 
                    $bMargin = $this->getBreakMargin();
                    $auto_page_break = $this->AutoPageBreak;
                    $this->SetAutoPageBreak(false, 0);

                    $img_file2 = '../../public/images/fondoCertificadoClaro.jpg';
                    $this->Image($img_file2, 15, 25, 180, 180, '', '', 'C', false, 300, '', false, false, 0);
                    $this->SetAutoPageBreak($auto_page_break, $bMargin);
                    $this->setPageMark();
                    //FIN MARCA DE AGUA 
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

        $pdf->SetFont('dejavusans', '', 12);
        $pdf->AddPage();

        $fechaActual = 'La Plata, '.date('d').' de '.obtenerMes(date('m')).' de '.date('Y');

        $pdf->Ln(5);
        $texto = '<p style="line-height: 20em;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            A sus efectos se deja constancia que la firma del presente certificado, guarda similitud con la 
            registrada en este Colegio de Médicos, por <b>'.$medico_1.'</b> M.P. N° <b>'.$matricula.'</b></p>';
        $pdf->writeHTMLCell(0, 8, '', '', $texto, 0, 1, 0, true, 'J', true);

        $pdf->Ln(5);

        $texto_pie = '<p style="line-height: 20em;">
            COLEGIO DE MEDICOS DE LA PROVINCIA DE BUENOS AIRES<br>
            DISTRITO I. '.$fechaActual.'.-</p>';
        $pdf->writeHTMLCell(0, 8, '', '', $texto_pie, 0, 1, 0, true, 'J', true);
        $pdf->SetXY(15, 245);
        $pdf->SetFont('dejavusans', 'B', 10);
        $pdf->MultiCell(50, 5, 'ARANCEL: $'.$totalRecibo, 0, 'L', false, 1, '', '');
        $pdf->SetFont('dejavusans', '', 6);
        $pdf->MultiCell(50, 2, 'CUIT: 30-54078002-8', 0, 'L', false, 0, '', '');
        $pdf->MultiCell(80, 2, 'Ing.Brutos: Exento.', 0, 'L', false, 1, '', '');
        $pdf->MultiCell(50, 2, 'Caja Prev. Nº 30-54078002-8', 0, 'L', false, 0, '', '');
        $pdf->MultiCell(80, 2, 'IVA EXENTO', 0, 'L', false, 1, '', '');
        $pdf->MultiCell(80, 2, 'Exceptuado cumpl. R.G.D.I. 3419 y modif. Art. 3º inc. L.', 0, 'L', false, 1, '', '');
        $pdf->lastPage();

        $pdf->SetFont('dejavusans', '', 12);
        $pdf->AddPage();
        $pdf->Ln(5);
        $texto = 'Recibí conforme la certificación de firma '.$medico_2.' M.P. N° '.$matricula;
        $pdf->writeHTMLCell(0, 8, '', '', $texto, 0, 1, 0, true, 'J', true);
        $pdf->Ln(5);
        $pdf->MultiCell(0, 2, 'FIRMA:', 0, 'L', false, 0, '', '');
        $pdf->MultiCell(0, 2, '________________________________________________', 0, 'L', false, 1, '80', '');
        $pdf->Ln(5);
        $pdf->MultiCell(0, 2, 'ACLARACIÓN:', 0, 'L', false, 0, '', '');
        $pdf->MultiCell(0, 2, '________________________________________________', 0, 'L', false, 1, '80', '');
        $pdf->Ln(5);
        $pdf->MultiCell(0, 2, 'DNI:', 0, 'L', false, 0, '', '');
        $pdf->MultiCell(0, 2, '________________________________________________', 0, 'L', false, 1, '80', '');
        $pdf->Ln(5);
        $pdf->MultiCell(0, 2, 'Institución / Razón Social:', 0, 'L', false, 0, '', '');
        $pdf->MultiCell(0, 2, '________________________________________________', 0, 'L', false, 1, '80', '');

        $texto_pie = '<p style="line-height: 20em;">
            COLEGIO DE MEDICOS DE LA PROVINCIA DE BUENOS AIRES<br>
            DISTRITO I. '.$fechaActual.'.-</p>';
        $pdf->SetXY(15, 245);
        $pdf->writeHTMLCell(0, 8, '', '', $texto_pie, 0, 1, 0, true, 'J', true);
        ob_clean();

        $pdf->Output('Certificado', 'D');       
        //$pdf->Output($camino.$pathArchivo, 'F');       
    }
} 
?>
<!--
<body onLoad="document.forms['myForm'].submit()">
    <form name="myForm"  method="POST" action="../certificacion_firma_nueva.php?idColegiado=<?php echo $idColegiado.'&id='.$idConstanciaFirma; ?>">
    </form>
</body>
-->