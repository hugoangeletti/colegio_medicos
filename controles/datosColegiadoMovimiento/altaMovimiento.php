<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/colegiadoLogic.php');
require_once ('../../dataAccess/colegiadoMovimientoLogic.php');
$colegiadoMovimientoLogic = new colegiadoMovimientoLogic();

$continua = TRUE;
if (isset($_GET['idColegiado']) && isset($_POST['idTipoMovimiento']) && isset($_POST['fechaDesde'])) {
    $idColegiado = $_GET['idColegiado'];
    $idTipoMovimiento = $_POST['idTipoMovimiento'];
    $fechaDesde = $_POST['fechaDesde'];

    if (isset($_POST['distritoOrigen'])) {
        $distritoOrigen = $_POST['distritoOrigen'];
    } else {
        $distritoOrigen = NULL;
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
    $resultado = $colegiadoMovimientoLogic->agregarColegiadoMovimiento($idColegiado, $idTipoMovimiento, $distritoOrigen, $fechaDesde, $fechaHasta);
} else {
    $resultado['icono'] = "glyphicon glyphicon-remove";
    $resultado['clase'] = "alert alert-error";
}

?>
<body onLoad="document.forms['myForm'].submit()">
    <?php
    if ($resultado['estado']) {
        //imprime el certificado
    ?>
        <form name="myForm" method="POST" action="../colegiado_nuevo_baja.php?idColegiado=<?php echo $idColegiado;?>"></form>
    <?php
    } else {
        //vuelve al formulario de solicitud por error
    ?>
        <form name="myForm" method="POST" action="../colegiado_nuevo_baja.php?idColegiado=<?php echo $idColegiado;?>">
            <input type="hidden" name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden" name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden" name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
            <input type="hidden"  name="idTipoMovimiento" id="idTipoMovimiento" value="<?php echo $idTipoMovimiento;?>">
            <input type="hidden"  name="distritoOrigen" id="distritoOrigen" value="<?php echo $distritoOrigen;?>">
            <input type="hidden"  name="fechaDesde" id="fechaDesde" value="<?php echo $fechaDesde;?>">
            <input type="hidden"  name="fechaHasta" id="fechaHasta" value="<?php echo $fechaHasta;?>">
        </form>
    <?php
    }
    ?>
</body>

