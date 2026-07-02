<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/colegiadoLogic.php');
require_once ('../../dataAccess/colegiadoMovimientoLogic.php');
$colegiadoMovimientoLogic = new colegiadoMovimientoLogic();

$continua = TRUE;
if (isset($_GET['idColegiado']) && isset($_POST['fechaDesde'])) {
    $idColegiado = $_GET['idColegiado'];
    $fechaDesde = $_POST['fechaDesde'];

    if (isset($_POST['distritoOrigen'])) {
        $distritoOrigen = $_POST['distritoOrigen'];
    } else {
        $distritoOrigen = NULL;
    }
    if (isset($_POST['distritoCambio'])) {
        $distritoCambio = $_POST['distritoCambio'];
    } else {
        $distritoCambio = NULL;
    }
    if (isset($_POST['nota'])) {
        $observaciones = $_POST['nota'];
    } else {
        $observaciones = NULL;
    }
    if (isset($_POST['fechaHasta'])) {
        $fechaHasta = $_POST['fechaHasta'];
    } else {
        $fechaHasta = NULL;
    }
    
} else {
    $resultado['mensaje'] = "ERROR EN LOS DATOS INGRESADOS";
    $continua = FALSE;
}

if ($continua){
    $resultado = $colegiadoMovimientoLogic->agregarMovimientoOtroDistrito($idColegiado, $distritoOrigen, $distritoCambio, $fechaDesde, $fechaHasta, $observaciones);
} else {
    $resultado['icono'] = "glyphicon glyphicon-remove";
    $resultado['clase'] = "alert alert-error";
}

?>
<body onLoad="document.forms['myForm'].submit()">
    <form name="myForm" method="POST" action="../colegiado_movimientos_distritos.php?idColegiado=<?php echo $idColegiado;?>"></form>
</body>

