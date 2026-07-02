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
    $idMesaEntradaConsultorio = NULL;
    $resMesaEntradaConsultorio = $mesaEntradaLogic->obtenerHabilitacionConsultorioPorIdMesaEntrada($idMesaEntrada);
    if ($resMesaEntradaConsultorio['estado']) {
        $mesaEntradaConsultorio = $resMesaEntradaConsultorio['datos'];
        $idMesaEntradaConsultorio = $mesaEntradaConsultorio['idMesaEntradaConsultorio'];
        $idColegiado = $mesaEntradaConsultorio['idColegiado'];
        $idConsultorio = $mesaEntradaConsultorio['idConsultorio'];
        $nombreConsultorio = $mesaEntradaConsultorio['nombreConsultorio'];
        $domicilioCompleto = $mesaEntradaConsultorio['domicilioCompleto'];
        $telefono = $mesaEntradaConsultorio['telefono'];
        $horarioAtencion = $mesaEntradaConsultorio['horarios'];
        $idEspecialidad = $mesaEntradaConsultorio['idEspecialidad'];
        $idEspecialidadAlternativa = $mesaEntradaConsultorio['idEspecialidadAlternativa'];
        $nombreLocalidad = $mesaEntradaConsultorio['nombreLocalidad'];
        $nombreEspecialidad = $mesaEntradaConsultorio['nombreEspecialidad'];
        $nombreEspecialidadAlternativa = $mesaEntradaConsultorio['nombreEspecialidadAlternativa'];
        $nombreUsuario = $mesaEntradaConsultorio['nombreUsuario'];
        $idColegiado = $mesaEntradaConsultorio['idColegiado'];
        $resDomicilio = $colegiadoDomicilioLogic->obtenerColegiadoDomicilioPorIdColegiado($idColegiado);
        if ($resDomicilio['estado']) {
            $domicilioReal = $resDomicilio['datos'];
            $calle = $domicilioReal['calle'];
            $numeroCasa = $domicilioReal['numero'];
            $lateral = $domicilioReal['lateral'];
            $piso = $domicilioReal['piso'];
            $departamento = $domicilioReal['depto'];
            $nombreLocalidadParticular = $domicilioReal['nombreLocalidad'];
            $domicilioParticular = " ";
            if (isset($calle) && $calle <> "") {
                $domicilioParticular .= trim($calle);
            }
            if (isset($numeroCasa) && $numeroCasa <> "") {
                $domicilioParticular .= ' N°'.trim($numeroCasa);
            }
            if (isset($lateral) && $lateral <> "") {
                $domicilioParticular .= ' ('.trim($lateral).')';
            }
            if (isset($piso) && $piso <> "") {
                $domicilioParticular .= ' Piso'.trim($piso);
            }
            if (isset($departamento) && $departamento <> "") {
                $domicilioParticular .= ' Dto.'.trim($departamento);
            }
            if (isset($nombreLocalidadParticular) && $nombreLocalidadParticular <> "") {
                $domicilioParticular .= ' ('.trim($nombreLocalidadParticular).')';
            }

        } else {
            $domicilioParticular = "";
        }

        $telefonoParticular = "";
        $mail = "";
        $resContacto = $colegiadoContactoLogic->obtenerColegiadoContactoPorIdColegiado($idColegiado);
        if ($resContacto['estado']) {
            $contacto = $resContacto['datos'];
            $telefonoFijo = $contacto['telefonoFijo'];
            $telefonoMovil = $contacto['telefonoMovil'];
            $telefonoParticular = trim($telefonoFijo).' - '.trim($telefonoMovil);
            $mail = $contacto['email'];
        }

        $apellidoNombre = trim($apellido).' '.trim($nombre);

        //busco si tiene mas medicos en el consultorio
        $resConsultorioOtrosMedicos = $mesaEntradaLogic->obtenerMesaEntradaConsultorioOtrosMedicos($idMesaEntradaConsultorio);
        if ($resConsultorioOtrosMedicos['estado']) {
            $consultorioOtrosMedicos = $resConsultorioOtrosMedicos['datos'];
        } else {
            $consultorioOtrosMedicos = array();
        }

        //guarda pdf
        /* armamaos el path donde se va a guardar el pdf */
        $camino = $_SERVER['DOCUMENT_ROOT'];
        $camino .= PATH_PDF.'/archivos/tmp/';
        $nombreArchivo = $camino.$idMesaEntrada.'.pdf';
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
        $dia = substr($fechaIngreso, 8, 2);
        $mes = substr($fechaIngreso, 5, 2);
        $anio = substr($fechaIngreso, 0, 4);
        $fecha_texto = 'La Plata, '.$dia.' de '.obtenerMes($mes).' de '.$anio;

        //$pdf->Line(100, 5, 100, 52, array('width' => 0));
        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->MultiCell(0, 5, 'FORMULARIO DE HABILITACIÓN DE CONSULTORIO', 0, 'C', false, 1, '30', '');
        $pdf->Ln(2);
        $pdf->MultiCell(0, 7, 'Nº '.rellenarCeros($idMesaEntradaConsultorio, 8), 0, 'R', false, 1, '115', '');
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->MultiCell(0, 5, $fecha_texto, 0, 'R', false, 1, '50', '');
        $pdf->MultiCell(0, 5, 'Sr. Presidente del', 0, 'L', false, 1, '', '');
        $pdf->MultiCell(0, 5, 'Colegio de Médicos - Distrito I', 0, 'L', false, 1, '', '');
        $pdf->MultiCell(0, 5, 'S/D', 0, 'L', false, 1, '', '');
        $pdf->Ln(10);
        //ARMAMOS EL HTML
        //ARMAMOS EL HTML
        $pdf->SetFont('dejavusans', '', 10);
        $html = '<p style="line-height: 15em;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                Tengo el agrado de dirigirme a Usted, y por su intermedio a quien corresponda a los efectos de solicitar la <b>HABILITACIÓN DE MI CONSULTORIO</b>'.$nombreConsultorio.', ubicado en la CALLE '.$domicilioCompleto.' con teléfono '.$telefono.', en la LOCALIDAD de '.$nombreLocalidad.'. En dicho consultorio atenderé en los días y horarios <b>'.$horarioAtencion.'</b>, realizando la ESPECIALIDAD de '.$nombreEspecialidad.'.-</p>
            <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                Sin otro particular, saluda a Usted muy atentamente.-</p>';        

        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, 'J', true);

        //si tiene medicos autorizados
        if (sizeof($consultorioOtrosMedicos) > 0) {
            $pdf->Ln(10);
            $pdf->SetFont('dejavusans', 'B', 10);
            $pdf->MultiCell(0, 5, 'Médicos Autorizados', 0, 'L', false, 1, '', '');
            $pdf->SetFont('dejavusans', '', 10);
            foreach ($consultorioOtrosMedicos as $otro) {
                $pdf->MultiCell(0, 5, $otro['matricula'], 0, 'L', false, 0, '', '');
                $pdf->MultiCell(0, 5, $otro['apellido'].' '.$otro['nombre'], 0, 'L', false, 0, '35', '');
                $pdf->Ln(5);                
            }
        }
        //$pdf->Line(0, 52, 220, 52, array('width' => 0));
        $pdf->Ln(12);
        $pdf->MultiCell(0, 5, 'Domicilio particular: '.$domicilioParticular, 0, 'L', false, 1, '', '');
        $pdf->Ln(2);
        $pdf->MultiCell(0, 5, 'Telefono particular: '.$telefonoParticular, 0, 'L', false, 1, '', '');
        $pdf->Ln(2);
        $pdf->MultiCell(0, 5, 'E-Mail: '.$mail, 0, 'L', false, 1, '', '');
        $pdf->Ln(7);
        $pdf->MultiCell(0, 5, 'Firma: _______________________________', 0, 'L', false, 1, '100', '', true);
        $pdf->Ln(2);
        $pdf->MultiCell(0, 5, 'Apellido y Nombre: '.$apellidoNombre, 0, 'L', false, 1, '100', '');
        $pdf->Ln(2);
        $pdf->MultiCell(0, 5, 'Matrícula: '.$matricula, 0, 'L', false, 1, '100', '');        

        $pdf->Line(15, 250, 192, 250, array('width' => 1));
        $pdf->SetY(255);
        $pdf->SetFont('dejavusans', '', 8);
        $pdf->MultiCell(50, 7, 'Realizó: '.$nombreUsuario, 0, 'L', false, 0, '35', '');
        $pdf->MultiCell(80, 7, 'Emitido el: '.date('d/m/Y H:i:s'), 0, 'L', false, 0, '140', '');
        $pdf->lastPage();
            
        //ob_clean();
        /* Finalmente generamos el PDF */
        //echo 'generar 4'; exit;
        $pdf->Output($nombreArchivo, 'F');       

        if (file_exists($nombreArchivo)) {
            $pdf_content = file_get_contents($nombreArchivo);        
            $hojaRutaPDF = base64_encode($pdf_content);
        } else {
            echo 'no pudo generar planilla';
            $hojaRutaPDF = NULL;
        }
    } else {
        echo $resMesaEntradaConsultorio['mensaje'];
        $hojaRutaPDF = NULL;
    }
} else {
    echo 'no pudo generar planilla - ingreso incorrecto';
    $hojaRutaPDF = NULL;
}
