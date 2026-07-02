<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/reconocimientoAntiguedadLogic.php');
require_once ('../dataAccess/colegiadoDeudaAnualLogic.php');
$colegiadoDeudaAnualLogic = new colegiadoDeudaAnualLogic();
require_once ('../dataAccess/colegiadoContactoLogic.php');
$colegiadoContactoLogic = new colegiadoContactoLogic();
require_once ('../dataAccess/colegiadoDomicilioLogic.php');
$colegiadoDomicilioLogic = new colegiadoDomicilioLogic();

$continua = TRUE;
$mensaje = "";
if (isset($_GET['id']) && isset($_GET['filtro'])) {
    $estado = $_GET['filtro'];
    $margen_top = 42;
    $idReconocimientoAntiguedad = $_GET['id'];
    $actosLogic = new reconocimientoAntiguedadLogic();
    $resActos = $actosLogic->obtenerActoPorId($idReconocimientoAntiguedad);            
    if ($resActos['estado']){
        $acto = $resActos['datos'];
        $fechaActo = $acto['fechaActo'];
        $lugarActo = $acto['lugarActo'];
        $antiguedad = $acto['antiguedad'];
    } else {
        $continua = FALSE;
        $mensaje .= $resActos['mensaje'];
    }        
} else {
    $continua = FALSE;
    $mensaje .= 'Falta idReconocimientoAntiguedad - ';
}        

if ($continua) {
        $resActoDetalle = $actosLogic->obtenerColegiadosPorActo($idReconocimientoAntiguedad, $estado);
        if ($resActoDetalle['estado'] && sizeof($resActoDetalle['datos']) > 0){
            $nombreArchivo = 'Acto_'.$antiguedad.'_Años_'.PERIODO_ACTUAL.'.csv';
            $path = "../../archivos/tmp/";

            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            if (file_exists($path."/".$nombreArchivo)) {
                unlink($path."/".$nombreArchivo);
            }

            $fileColegiados = fopen($path."/".$nombreArchivo, "w")or  die("Problemas en la creacion del archivo ".$path."/".$nombreArchivo);
            /*
            header('Content-type: application/vnd.ms-excel');
            header('Content-disposition: attachment; filename='.$nombreArchivo);
            $isPrintHeader  = false;
            */

            //carga encabezado en el archivo
            $linea = utf8_decode('Matrícula;Apellido y Nombre;Teléfono Fijo;Teléfono Movil;Correo Electrónico;Estado Matricular;Estado con Tesorería;Domicilio;Localidad');

            //inserto el colegiado
            if (fwrite($fileColegiados, $linea."\n") === FALSE) {
                $claseMensaje="alert alert-danger";
                $mensaje="NO SE PUDO GENERAR EL ARCHIVO colegiado.";
                $continua = FALSE;
                $hayColegiados = false;
            }

            foreach ($resActoDetalle['datos'] as $dato) {
                $idColegiado = $dato['idColegiado'];
                $codigoDeudor = $dato['codigoDeudor'];
                $resEstadoTesoreria = $colegiadoDeudaAnualLogic->estadoTesoreria($codigoDeudor);
                if ($resEstadoTesoreria['estado']){
                  $dato['estadoTesoreria'] = utf8_decode($resEstadoTesoreria['estadoTesoreria']);
                } else {
                  $dato['estadoTesoreria'] = '';
                }

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
                        //$domicilioCompleto .= ' ( '.$domicilio['nombreLocalidad'].' )';
                        $dato['localidad'] = utf8_decode($domicilio['nombreLocalidad']);
                    } else {
                        $dato['localidad'] = "";
                    }
                } else {
                    $domicilioCompleto = '';
                }
                $dato['domicilio'] = utf8_decode($domicilioCompleto);

                $resContacto = $colegiadoContactoLogic->obtenerColegiadoContactoPorIdColegiado($idColegiado);
                if ($resContacto['estado']) {
                    $contacto = $resContacto['datos'];
                    $dato['telefonoFijo'] = $contacto['telefonoFijo'];
                    $dato['telefonoMovil'] = $contacto['telefonoMovil'];
                    $dato['email'] = $contacto['email'];
                } else {
                    $dato['telefonoFijo'] = '';
                    $dato['telefonoMovil'] = '';
                    $dato['email'] = '';
                }
                $dato['apellidoNombre'] = utf8_decode($dato['apellidoNombre']);

                //carga linea en el archivo
                $linea = $dato['matricula'].';'.$dato['apellidoNombre'].';'.$dato['telefonoFijo'].';'.$dato['telefonoMovil'].';'.$dato['email'].';'.$dato['estadoMatricular'].';'.$dato['estadoTesoreria'].';'.$dato['domicilio'].';'.$dato['localidad'];

                //inserto el colegiado
                if (fwrite($fileColegiados, $linea."\n") === FALSE) {
                    $claseMensaje="alert alert-danger";
                    $mensaje="NO SE PUDO GENERAR EL ARCHIVO colegiado.";
                    $continua = FALSE;
                    $hayColegiados = false;
                }

            /*
            if (! $isPrintHeader ) {
                echo implode("\t", array_keys($dato)) . "\n";
                $isPrintHeader = true;
            }
            echo implode("\t", array_values($dato)) . "\n";
            */
        }
        $file = $path.'/'.$nombreArchivo;
        $fileDescarga = $nombreArchivo;
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $fileDescarga . '"');
        header('Content-Length: ' . filesize($file));

        readfile($file);
    } else {
    ?>
        <div class="<?php echo $resSeguroProcesado['clase']; ?>" role="alert">
            <span class="<?php echo $resSeguroProcesado['icono']; ?>" ></span>
            <span><strong><?php echo $resSeguroProcesado['mensaje']; ?></strong></span>
        </div>
    <?php
    }
} else {
    echo "ERROR AL GENERAR EL ARCHIVO, VUELVA A INTENTAR";
}

