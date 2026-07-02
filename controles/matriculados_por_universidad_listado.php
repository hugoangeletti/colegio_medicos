<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
//require_once ('../html/head.php');
//require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/informesLogic.php');

$continua = TRUE;
$mensaje = "";
if (isset($_POST['tipoListado']) && $_POST['tipoListado'] <> "") {
    $tipoListado = $_POST['tipoListado'];
    $fechaDesde = $_POST['fechaDesde'];
    $fechaHasta = $_POST['fechaHasta'];
    //generar listado
    if ($fechaDesde <= $fechaHasta) {
        $informesLogic = new informesLogic();
        $resListado = $informesLogic->obtenerMatriculadosPorUniversidad($fechaDesde, $fechaHasta);
        if ($resListado['estado'] && sizeof($resListado['datos']) > 0) {
            //se encontro resultado se imprime
            //define(FECHA_DESDE, $fechaDesde);
            //define(FECHA_HASTA, $fechaHasta);
            require_once('../tcpdf/config/lang/spa.php');
            require_once('../tcpdf/tcpdf.php');
            class MYPDF extends TCPDF {
                    //Page header
                    public function Header() 
                    {
                        $this->SetFont('dejavusans', 'B', 10);
                        // Title
                        $this->Ln(4);
                        $this->MultiCell(0, 0, 'Colegio de Médicos - Distrito I', 0, 'L', false, 0, '10', '');
                        $this->MultiCell(0, 0, cambiarFechaFormatoParaMostrar(date('Y-m-d')), 0, 'L', false, 1, '160', '');
                        $this->Ln(5);
                        $this->SetFont('dejavusans', 'B', 8);
                        $this->MultiCell(0, 0, 'Listado de matriculados', 0, 'C', false, 0, '10', '');
                        //$this->MultiCell(0, 0, 'Listado de matriculados (entre el '.cambiarFechaFormatoParaMostrar(FECHA_DESDE).' y el '.cambiarFechaFormatoParaMostrar(FECHA_HASTA).')', 0, 'C', false, 0, '10', '');
                        $this->Ln(5);
                        $this->SetFont('dejavusans', 'B', 12);
                        /*
                        $this->Ln(2);
                        $this->MultiCell(0, 5, 'Título otorgado por '.NOMBRE_UNIVERSIDAD, 0, 'C', false, 1, '10', '');
                        */
                        $this->Ln(2);
                        $this->SetFont('dejavusans', 'B', 8);
                        $this->MultiCell(20, 0, 'Matrícula', 0, 'R', false, 0, '8', '');
                        $this->MultiCell(0, 0, 'Apellido y Nombre', 0, 'L', false, 0, '32', '');
                        $this->MultiCell(0, 0, 'Universidad', 0, 'L', false, 1, '80', '');

                        $y = $this->GetY() + 2;
                        $this->Line(10, $y, 200, $y, array('width' => 1));
                    }

                    // Page footer
                    public function Footer() {
                        // Position at 15 mm from bottom
                        $this->SetY(-15);
                        // Set font
                        $this->SetFont('dejavusans', 'I', 8);

                        // Page number
                        $this->Cell(0, 5, 'Pag. '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
                    }
            }

            $linea = 1;
            $lineaMaximo = 45;
            $universidadAnterior = "";
            foreach ($resListado['datos'] as $colegiado){
                $matricula = $colegiado['matricula'];
                $apellidoNombre = $colegiado['apellidoNombre'];
                $nombreUniversidad = $colegiado['nombreUniversidad'];
                $paisUniversidad = $colegiado['paisUniversidad'];
                $fechaMatriculacion = $colegiado['fechaMatriculacion'];
                $fechaTitulo = $colegiado['fechaTitulo'];

                if ($universidadAnterior <> $nombreUniversidad) {
                    if ($universidadAnterior <> "") {
                        $pdf->lastPage();
                        $destination = 'F'; 
                        ob_clean();

                        $camino = $_SERVER['DOCUMENT_ROOT'];
                        $camino .= PATH_PDF;
                        $pathArchivo = '../archivos/listados/'.PERIODO_ACTUAL;
                        $nombreArchivo = 'Matriculados_'.$universidadAnterior.'.pdf';
                        if (!file_exists($pathArchivo)) {
                            mkdir($pathArchivo, 0777, true);
                        }
                        if (file_exists($pathArchivo."/".$nombreArchivo)) {
                            unlink($pathArchivo."/".$nombreArchivo);
                        } 

                        $pdf->Output($camino.'/archivos/listados/'.PERIODO_ACTUAL.'/'.$nombreArchivo, $destination);      


                        //$pdf->Output('ListadoMatriculados_'.$universidadAnterior.'.pdf', $destination);      
                        //$linea = $lineaMaximo;
                    }
                    $universidadAnterior = $nombreUniversidad;
                    $linea = 1;
                    
                    $pdf = new MYPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
                    $pdf->SetPrintHeader(true);
                    $pdf->SetPrintFooter(true);
                    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
                    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
                    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                    //$pdf->SetMargins(0, 0, 0);
                    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                    //$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
                    //$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                    $pdf->SetAutoPageBreak(TRUE, 0);
                    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                    $pdf->SetFont('dejavusans', '', 8);
                    $pdf->AddPage();
                    //$pdf->Ln(5);

                    $pdf->SetFont('dejavusans', '', 8);
                    $cantidad = 0;
                    $alturaLinea = 7;
                    //$p1y = $pdf->getY();
                    //$inicio = $pdf->getY();
                    $pdf->SetXY(0, 35);
                }

                if ($linea >= $lineaMaximo) {
                    $pdf->AddPage();
                    $pdf->SetFont('dejavusans', '', 6);
                    $linea = 1;
                    $pdf->SetXY(0, 35);
                }

                $pdf->SetFont('dejavusans', '', 6);
                $pdf->MultiCell(20, 0, $matricula, 0, 'R', false, 0, '10', '');
                $pdf->MultiCell(0, 0, $apellidoNombre, 0, 'L', false, 0, '30', '');
                $pdf->MultiCell(0, 0, $nombreUniversidad.' ('.$paisUniversidad.')', 0, 'L', false, 0, '80', '');
                //$pdf->MultiCell(0, 0, cambiarFechaFormatoParaMostrar($fechaMatriculacion), 0, 'L', false, 0, '170', '');
                //$pdf->MultiCell(0, 0, cambiarFechaFormatoParaMostrar($fechaTitulo), 0, 'L', false, 1, '190', '');
                $pdf->Ln(5);
                $linea += 1;
            }

            $pdf->lastPage();
            $destination = 'F'; 
            ob_clean();

            $camino = $_SERVER['DOCUMENT_ROOT'];
            $camino .= PATH_PDF;
            $pathArchivo = '../archivos/listados/'.PERIODO_ACTUAL;
            $nombreArchivo = 'Matriculados_'.$universidadAnterior.'.pdf';
            if (!file_exists($pathArchivo)) {
                mkdir($pathArchivo, 0777, true);
            }
            if (file_exists($pathArchivo."/".$nombreArchivo)) {
                unlink($pathArchivo."/".$nombreArchivo);
            } 

            $pdf->Output($camino.'/archivos/listados/'.PERIODO_ACTUAL.'/'.$nombreArchivo, $destination);

            ?>
            <p style="font-size: x-large;"><b>Archivos para descargar</b></p>
            <?php
            //zipear el directorio completo
            // Initialize archive object
$zip = new ZipArchive();
// Ruta absoluta
$nombreArchivoZip = '../archivos/listados/'.PERIODO_ACTUAL.'/archivos.zip';

if (!$zip->open($nombreArchivoZip, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
    exit("Error abriendo ZIP en $nombreArchivoZip");
}
// Si no hubo problemas, continuamos
// Agregamos el script.js
$rutaAbsoluta = "pathArchivo";
$nombre = basename($rutaAbsoluta);
$zip->addFile($rutaAbsoluta, $nombre);

// No olvides cerrar el archivo
$resultado = $zip->close();
if (!$resultado) {
    exit("Error creando archivo");
}

// Ahora que ya tenemos el archivo lo enviamos como respuesta
// para su descarga

// El nombre con el que se descarga
$nombreAmigable = "simple.zip";
header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary");
header("Content-disposition: attachment; filename=$nombreAmigable");
// Leer el contenido binario del zip y enviarlo
readfile($nombreArchivoZip);

// Si quieres puedes eliminarlo después:
unlink($nombreArchivoZip);            //fin zipear

            //generar lista para descarga
            $directorio = $pathArchivo; 

            // Obtener la lista de archivos
            $archivos = scandir($directorio);

            // Recorrer la lista de archivos
            foreach ($archivos as $archivo) {
                if ($archivo != "." && $archivo != "..") {
                    $ruta_archivo = $directorio . '/' . $archivo;
                    if (is_file($ruta_archivo)) {
                        ?>
                        <p>
                        <label style="font-size: large;"><?php echo $archivo; ?></label> === > 
                        <b><a href="<?php echo $ruta_archivo; ?>" target="_BLANK" class="btn btn-info">Descargar</a></b>
                        </p>

                        <?php
                        /*
                        // Establecer encabezados para la descarga de un archivo individual
                        header('Content-Type: application/octet-stream'); // O el tipo MIME correcto
                        header('Content-Disposition: attachment; filename="' . $archivo . '"');
                        header('Content-Length: ' . filesize($ruta_archivo));
                        readfile($ruta_archivo);
                        */
                    }
                }
            }
            ?>
            <?php
        } else {
            $continua = FALSE;
            $mensaje .= $resListado['mensaje'];
        }
    } else {
        $continua = FALSE;
        $mensaje .= "Fecha desde debe ser menor o igual a Fecha hasta";
    }
} else {
    $mensaje .= "ERROR ingreso";
}
