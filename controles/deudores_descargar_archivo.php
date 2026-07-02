<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/deudoresLogic.php');
require_once ('../dataAccess/usuarioLogic.php');

$continua = TRUE;
$mensaje = '';
$continua = TRUE;
if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idDeudores = $_GET['id'];
} else {
    $continua = FALSE;
    $mensaje .= "Ingreso Incorrecto - falta idDeudores";
}
$deudoresLogic = new deudoresLogic();
if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idSeguro = $_GET['id'];
    $resDeudores = $deudoresLogic->obtenerListadoDeudoresPorId($idDeudores);
    if ($resDeudores['estado']) {
        $deudores = $resDeudores['datos'];
        $tipo_filtro = $deudoresLogic->tipoFiltro($deudores['tipo_filtro']);
        $periodo_limite = $deudores['periodo_limite'];
        $cuotas_adeudadas = $deudores['cuotas_adeudadas'];
    } else {
        $mensaje .= $deudores['mensaje'];
        $continua = FALSE;
    }
} else {
    $continua = FALSE;
    $mensaje .= "Falta idSeguro - ";
}

if ($continua) {
    $deudoresLogic = new deudoresLogic();
    $resColegiados = $deudoresLogic->obtenerDetalleListadoDeudores($idDeudores);
    if ($resColegiados['estado']) {
        $fecha_archivo = date('YmdHis');
        $nombreArchivo = 'Deudores_'.$fecha_archivo.'.xls';
        //echo $nombreArchivo; exit;
        header('Content-type: application/vnd.ms-excel');
        header('Content-disposition: attachment; filename='.$nombreArchivo);
        $isPrintHeader = false;
        foreach ($resColegiados['datos'] as $dato) {
            if (! $isPrintHeader ) {
                echo implode("\t", array_keys($dato)) . "\n";
                $isPrintHeader = true;
            }
            $dato['apellidoNombre'] = utf8_decode($dato['apellidoNombre']);
            echo implode("\t", array_values($dato)) . "\n";
        }
    } else {
    ?>
        <div class="<?php echo $resColegiados['clase']; ?>" role="alert">
            <span class="<?php echo $resColegiados['icono']; ?>" ></span>
            <span><strong><?php echo $resColegiados['mensaje']; ?></strong></span>
        </div>
    <?php
    }
} else {
    echo "ERROR AL GENERAR EL ARCHIVO, VUELVA A INTENTAR";
}

