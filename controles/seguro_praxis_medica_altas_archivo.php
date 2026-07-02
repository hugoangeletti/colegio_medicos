<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiado_seguro_Logic.php');
require_once ('../dataAccess/usuarioLogic.php');

$continua = TRUE;
$mensaje = '';
if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idSeguro = $_GET['id'];
    $colegiado_seguro_Logic = new colegiado_seguro_Logic();
    $resSeguroProcesado = $colegiado_seguro_Logic->obtenerSeguroProcesadoPorId($idSeguro);
    if ($resSeguroProcesado['estado']) {
        $seguroProcesado = $resSeguroProcesado['datos'];
        $fechaLimiteProceso = $seguroProcesado['fechaLimiteProceso'];
        $procesoAnio = $seguroProcesado['procesoAnio'];
        $procesoMes = $seguroProcesado['procesoMes'];
    }
} else {
    $continua = FALSE;
    $mensaje .= "Falta idSeguro - ";
}

if ($continua) {
    $resSeguroProcesado = $colegiado_seguro_Logic->obtenerSegurosProcesadosAltas($idSeguro);
    if ($resSeguroProcesado['estado']) {
        $nombreArchivo = 'PadronAltas_'.$procesoAnio.rellenarCeros($procesoMes, 2).'.xls';
        //echo $nombreArchivo; exit;
        header('Content-type: application/vnd.ms-excel');
        header('Content-disposition: attachment; filename='.$nombreArchivo);
        $isPrintHeader = false;
        foreach ($resSeguroProcesado['datos'] as $dato) {
            $matricula = $dato['matricula'];
            $apellidoNombre = trim($dato['apellido']).', '.trim($dato['nombre']);
            $tipoDocumento = $dato['tipoDocumento'];
            $numeroDocumento = $dato['numeroDocumento'];
            $fechaNacimiento = cambiarFechaFormatoParaMostrar($dato['fechaNacimiento']);
            $correoElectronico = $dato['correoElectronico'];
            $especialidades = $dato['especialidades'];
            $fechaActualizacion = $dato['fechaActualizacion'];
            $estadoSeguro = $dato['estadoSeguro'];
            if (! $isPrintHeader ) {
                echo implode("\t", array_keys($dato)) . "\n";
                $isPrintHeader = true;
            }
            $dato['apellido'] = utf8_decode($dato['apellido']);
            $dato['nombre'] = utf8_decode($dato['nombre']);
            $dato['especialidades'] = utf8_decode($dato['especialidades']);
            echo implode("\t", array_values($dato)) . "\n";
        }
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

