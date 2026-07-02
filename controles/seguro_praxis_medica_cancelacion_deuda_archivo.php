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
    $resCancelaDeuda = $colegiado_seguro_Logic->obtenerCancelacionDeuda($idSeguro, $fechaLimiteProceso);
    //var_dump($resCancelaDeuda);
    if ($resCancelaDeuda['estado']) {
        $nombreArchivo = 'CancelacionDeudaAl_'.$fechaLimiteProceso.'.xls';
        //echo $nombreArchivo; exit;
        header('Content-type: application/vnd.ms-excel');
        header('Content-disposition: attachment; filename='.$nombreArchivo);
        $isPrintHeader = false;
        foreach ($resCancelaDeuda['datos'] as $dato) {
            if (! $isPrintHeader ) {
                echo implode("\t", array_keys($dato)) . "\n";
                $isPrintHeader = true;
            }
            //$dato['apellido'] = utf8_decode($dato['apellido']);
            //$dato['nombre'] = utf8_decode($dato['nombre']);
            echo implode("\t", array_values($dato)) . "\n";
        }
    } else {
    ?>
        <div class="<?php echo $resCancelaDeuda['clase']; ?>" role="alert">
            <span class="<?php echo $resCancelaDeuda['icono']; ?>" ></span>
            <span><strong><?php echo $resCancelaDeuda['mensaje']; ?></strong></span>
        </div>
    <?php
    }
} else {
    echo "ERROR AL GENERAR EL ARCHIVO, VUELVA A INTENTAR";
}

