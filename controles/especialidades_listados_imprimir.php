<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoEspecialistaLogic.php');
$colegiadoEspecialistaLogic = new colegiadoEspecialistaLogic();
require_once ('../dataAccess/colegiadoContactoLogic.php');
$colegiadoContactoLogic = new colegiadoContactoLogic();
require_once ('../dataAccess/colegiadoDomicilioLogic.php');
$colegiadoDomicilioLogic = new colegiadoDomicilioLogic();

require_once('../tcpdf/config/lang/spa.php');
require_once('../tcpdf/tcpdf.php');
set_time_limit(0);

class MYPDF extends TCPDF 
{
        public $el_titulo;
        public $conFecha;
        public $conDomicilio;
        public $conContacto;
        public $estadoMatricular;

        //Page header
        public function Header() 
        {
                // Logo
                $image_file = '../public/images/logo_colmed1_lg.png';
                $this->Image($image_file, 10, 10, 90, 12, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
                //$this->Image($image_file, 10, 10, 15, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
                 // Set font
                $this->SetFont('helvetica', '', 10);
                $this->SetXY(190, 10);
                $fecha_actual = cambiarFechaFormatoParaMostrar(date('Y-m-d'));
                $this->Cell(0, 10, 'Fecha: ' . $fecha_actual, 0, 1, 'R'); // A la derecha (R)

                // Title
                $this->SetFont('helvetica', 'B', 12);
                $this->Ln(5);        
                $this->Cell(0, 10, $this->el_titulo, 0, false, 'C', 0, '', 0, false, 'M', 'M');
                $this->Ln(5);
                $this->SetFont('helvetica', 'B', 7);
                $this->MultiCell(0, 5, 'Matrícula', 0, 'L', false, 0, '', '');
                $this->MultiCell(65, 5, 'Apellido y Nombres', 0, 'L', false, 0, '20', '');
                if ($this->estadoMatricular <> "ACTIVOS") {
                    $this->MultiCell(30, 5, 'Estado matricular', 0, 'L', false, 0, '80', '');
                }
                if ($this->conFecha) {
                    $this->MultiCell(15, 5, 'Especialista', 0, 'C', false, 0, '110', '');
                    $this->MultiCell(15, 5, 'Jerarquizado', 0, 'C', false, 0, '130', '');
                    $this->MultiCell(15, 5, 'Consultor', 0, 'C', false, 0, '150', '');
                    $this->MultiCell(15, 5, 'Caducidad', 0, 'C', false, 0, '170', '');
                    $this->MultiCell(10, 5, 'Distrito', 0, 'C', false, 0, '190', '');
                }
                //si no va con fechas ponemos en la misma linea si es solo con contacto
                if ($this->conDomicilio && $this->conContacto) {
                    $this->MultiCell(10, 5, '', 0, 'C', false, 1, '190', '');
                    $this->MultiCell(80, 5, 'Domicilio', 0, 'L', false, 0, '10', '');
                    //$this->MultiCell(30, 5, 'Localidad', 0, 'L', false, 0, '85', '');
                    $this->MultiCell(50, 5, 'Teléfonos', 0, 'L', false, 0, '100', '');
                    $this->MultiCell(50, 5, 'Email', 0, 'L', false, 0, '160', '');
                    $this->MultiCell(20, 5, '', 0, 'C', false, 1, '220', '');
                } else {
                    if ($this->conContacto) {
                        $this->MultiCell(50, 5, 'Teléfonos', 0, 'L', false, 0, '100', '');
                        $this->MultiCell(50, 5, 'Email', 0, 'L', false, 0, '160', '');
                        $this->MultiCell(20, 5, '', 0, 'C', false, 1, '220', '');
                    }
                }

                $y_line = $this->GetY();
                $this->Line(0, $y_line, 220, $y_line, array('width' => 0.5));
        }

        // Page footer
        public function Footer() {
                // Position at 15 mm from bottom
                $this->SetY(-15);
                // Set font
                $this->SetFont('helvetica', 'I', 8);

                // Page number
                $this->Cell(0, 10, 'Pag. '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        }
}

if (isset($_POST['datosSolicitados']) && isset($_POST['tipoEspecialista']) && isset($_POST['estadoMatricular'])  && isset($_POST['colegiadoEn'])) {
    $datosSolicitados = $_POST['datosSolicitados'];
    $margen_top = 35;
    switch ($datosSolicitados) {
        case 'SOLO_FECHAS':
            $conFecha = true;
            $conDomicilio = false;
            $conContacto = false;
            break;
         
        case 'FECHA_DOMICILIO':
            $conFecha = true;
            $conDomicilio = true;
            $conContacto = true;
            $margen_top = 42;
            break;
         
        case 'DOMICILIO_CONTACTO':
            $conFecha = false;
            $conDomicilio = true;
            $conContacto = true;
            $margen_top = 42;
            break;
         
        case 'SOLO_CONTACTO':
            $conFecha = false;
            $conDomicilio = false;
            $conContacto = true;
            $margen_top = 42;
            break;
         
        default:
            // code...
            break;
    }  
    $tipoEspecialista = $_POST['tipoEspecialista'];
    $estadoMatricularSeleccionado = $_POST['estadoMatricular'];
    switch ($estadoMatricularSeleccionado) {
        case 'ACTIVOS':
            $tituloEstado = '(Activos)';
            break;
        
        case 'ACTIVOS_INSCRIPTOS':
            $tituloEstado = '(Activos e Inscriptos)';
            break;
        
        default:
            $tituloEstado = '(Completo)';
            break;
    }
    $colegiadoEn = $_POST['colegiadoEn'];

    if (isset($_POST['idEspecialidad'])) {
        $idEspecialidad = $_POST['idEspecialidad'];
    } else {
        $idEspecialidad = NULL;
    }

    $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A4', true, 'UTF-8', false);
    $pdf->SetPrintHeader(true);
    $pdf->SetPrintFooter(true);

    // set header and footer fonts
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // set margins
    //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetMargins(5, $margen_top, 5);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    //define ('PDF_MARGIN_FOOTER', 8);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    //inicializar variables de header
    $pdf->el_titulo = 'Listado de Especialistas '.$tituloEstado;
    $pdf->conDomicilio = $conDomicilio;
    $pdf->conContacto = $conContacto;
    $pdf->conFecha = $conFecha;
    $pdf->estadoMatricular = $estadoMatricularSeleccionado;
    $pdf->AddPage();

    $resEspecialistas = $colegiadoEspecialistaLogic->obtenerEspecialistasParaImprimir($idEspecialidad, $estadoMatricularSeleccionado, $colegiadoEn);
    if ($resEspecialistas['estado']) {
        $especialidadAnterior = null;
        $nombreEspecialidadAnterior = null;
        $totalEspecialidad = 0;
        foreach ($resEspecialistas['datos'] as $fila) {
            $idColegiado = $fila['idColegiado'];
            $matricula = $fila['matricula'];
            $apellidoNombre = trim($fila["apellido"]).' '.trim($fila['nombre']);
            $fechaEspecialista = $fila['fechaEspecialista'];
            $fechaRecertificacion = $fila['fechaRecertificacion'];
            $fechaJerarquizado = $fila['fechaJerarquizado'];
            if (isset($fechaJerarquizado) && $fechaJerarquizado <> "") {
                $fechaJerarquizado = cambiarFechaFormatoParaMostrar($fechaJerarquizado);
            }
            $conVencimiento = TRUE;
            $fechaConsultor = $fila['fechaConsultor'];
            if (isset($fechaConsultor) && $fechaConsultor <> "") {
                $fechaConsultor = cambiarFechaFormatoParaMostrar($fechaConsultor);
                $conVencimiento = FALSE;
            }
            $fechaVencimiento = $fila['fechaVencimiento'];
            if (isset($fechaVencimiento) && $fechaVencimiento <> "") {
                $fechaVencimiento = cambiarFechaFormatoParaMostrar($fechaVencimiento);
            }
            $colegio = $fila['colegio'];
            $idEspecialidad = $fila['idEspecialidad'];
            $nombreEspecialidad = $fila['nombreEspecialidad'];
            $estadoMatricular = $fila['estadoMatricular'];
            switch ($estadoMatricular) {
                case 'A':
                    $estadoMatricular = 'ACTIVO'; 
                    break;
                
                case 'I':
                    $estadoMatricular = 'Inscripto'; 
                    break;
                
                case 'C':
                    $estadoMatricular = 'BAJA'; 
                    break;
                
                case 'J':
                    $estadoMatricular = 'Jubilado'; 
                    break;
                
                case 'F':
                    $estadoMatricular = 'Fallecimiento'; 
                    break;
                
                default:
                    // code...
                    break;
            }

            if ($conDomicilio) {
                $resDomicilio = $colegiadoDomicilioLogic->obtenerColegiadoDomicilioPorIdColegiado($idColegiado);
                if ($resDomicilio['estado']) {
                    $domicilio = $resDomicilio['datos'];
                    if ($domicilio['calle']) {
                        $domicilioCompleto = $domicilio['calle'];
                        if ($domicilio['numero']) {
                            $domicilioCompleto .= " Nº ".$domicilio['numero'];
                        }
                        if ($domicilio['lateral']) {
                            $domicilioCompleto .= " e/ ".$domicilio['lateral'];
                        }
                        if ($domicilio['piso'] && strtoupper($domicilio['piso']) != "NR") {
                            $domicilioCompleto .= " Piso ".$domicilio['piso'];
                        }
                        if ($domicilio['depto'] && strtoupper($domicilio['depto']) != "NR") {
                            $domicilioCompleto .= " Dto. ".$domicilio['depto'];
                        }
                    }
                    if ($domicilio['nombreLocalidad']) {
                        $domicilioCompleto .= ' ( '.$domicilio['nombreLocalidad'].' )';
                    }
                } else {
                    $domicilioCompleto = NULL;
                    $localidad = NULL;
                }
            }

            if ($conContacto) {
                $resContacto = $colegiadoContactoLogic->obtenerColegiadoContactoPorIdColegiado($idColegiado);
                if ($resContacto['estado']) {
                    $contacto = $resContacto['datos'];
                    $telefonoFijo = $contacto['telefonoFijo'];
                    $telefonoMovil = $contacto['telefonoMovil'];
                    $email = $contacto['email'];
                } else {
                    $telefonoFijo = NULL;
                    $telefonoMovil = NULL;
                    $email = NULL;
                }
            }
           
            if ($idEspecialidad <> $especialidadAnterior) {
                if (isset($especialidadAnterior)) {
                    //imprimo los totales de la especialidad anterior
                    if ($totalEspecialidad > 0) {
                        $pdf->SetFont('dejavusans', 'B', 10);
                        $pdf->Ln(5);
                        $pdf->MultiCell(0, 6, 'Total de Especialistas en '.$nombreEspecialidadAnterior.': '.$totalEspecialidad, 0, 'L', false, 1, '', '');
                        $y_line = $pdf->GetY();        
                        $pdf->Line(0, $y_line, 220, $y_line, array('width' => 0));
                    }
                }
                $imprimirEncabezadoEspecialidad = TRUE;
                $especialidadAnterior = $idEspecialidad;
                $nombreEspecialidadAnterior = $nombreEspecialidad;
                $totalEspecialidad = 0;
            }

            if ($imprimirEncabezadoEspecialidad) {
                $pdf->SetFont('dejavusans', 'B', 10);
                $pdf->Ln(1);
                $pdf->MultiCell(0, 6, $nombreEspecialidad, 0, 'L', false, 1, '', '');    
                $imprimirEncabezadoEspecialidad = FALSE;
                $y_line = $pdf->GetY();        
                $pdf->Line(0, $y_line, 220, $y_line, array('width' => 0));
            }
            $pdf->SetFont('dejavusans', '', 7);
            $pdf->Ln(1);
            $pdf->MultiCell(0, 5, $matricula, 0, 'L', false, 0, '', '');
            $pdf->MultiCell(80, 5, $apellidoNombre, 0, 'L', false, 0, '20', '');
            if ($estadoMatricularSeleccionado <> "ACTIVOS") {
                $pdf->MultiCell(30, 5, $estadoMatricular, 0, 'L', false, 0, '80', '');
            }

            if ($conFecha) {
                $pdf->MultiCell(17, 5, cambiarFechaFormatoParaMostrar($fechaEspecialista), 0, 'C', false, 0, '110', '');
                $pdf->MultiCell(17, 5, $fechaJerarquizado, 0, 'C', false, 0, '130', '');
                $pdf->MultiCell(17, 5, $fechaConsultor, 0, 'C', false, 0, '150', '');
                $pdf->MultiCell(17, 5, $fechaVencimiento, 0, 'C', false, 0, '170', '');
                $pdf->MultiCell(10, 5, $colegio, 0, 'C', false, 0, '190', '');

                if ($datosSolicitados == 'SOLO_FECHAS') {
                    $pdf->MultiCell(10, 5, '', 0, 'C', false, 1, '190', '');    
                }
            } 

            $pdf->SetFont('dejavusans', '', 6);
            //si no va con fechas ponemos en la misma linea si es solo con contacto
            if ($conDomicilio && $conContacto) {
                $pdf->MultiCell(10, 5, '', 0, 'C', false, 1, '190', '');
                $pdf->MultiCell(80, 5, $domicilioCompleto, 0, 'L', false, 0, '', '');
                //$pdf->MultiCell(30, 5, $localidad, 0, 'L', false, 0, '85', '');
                $pdf->MultiCell(20, 5, $telefonoMovil, 0, 'L', false, 0, '100', '');
                $pdf->MultiCell(20, 5, $telefonoFijo, 0, 'L', false, 0, '120', '');
                $pdf->MultiCell(50, 5, $email, 0, 'L', false, 0, '160', '');
                $pdf->MultiCell(20, 5, '', 0, 'C', false, 1, '220', '');
            } else {
                if ($conContacto) {
                    $pdf->MultiCell(20, 5, $telefonoMovil, 0, 'L', false, 0, '100', '');
                    $pdf->MultiCell(20, 5, $telefonoFijo, 0, 'L', false, 0, '120', '');
                    $pdf->MultiCell(50, 5, $email, 0, 'L', false, 0, '160', '');
                    $pdf->MultiCell(20, 5, '', 0, 'C', false, 1, '220', '');
                }
            }
            $y_line = $pdf->GetY();        
            $pdf->Line(0, $y_line, 220, $y_line, array('width' => 0));
            $totalEspecialidad += 1;
        }
        if ($totalEspecialidad > 0) {
            $pdf->SetFont('dejavusans', 'B', 10);
            $pdf->Ln(5);
            $pdf->MultiCell(0, 6, 'Total de Especialistas en '.$nombreEspecialidadAnterior.': '.$totalEspecialidad, 0, 'L', false, 1, '', '');
            $y_line = $pdf->GetY();        
            $pdf->Line(0, $y_line, 220, $y_line, array('width' => 0));
        }
        $pdf->lastPage();

        /*
        $destination = 'Presentismo.pdf';
        if (!preg_match('/\.pdf$/', $path_to_store_pdf))
        {
               $path_to_store_pdf .= '.pdf';
        }
        */
        ob_clean();
        $pdf->Output('Presentismo.pdf', 'I');        
    } else {

    }
} else {
    echo 'ERROR-><br>';
    var_dump($_POST);
}

