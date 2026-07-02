<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/colegiadoLogic.php');
require_once ('../../dataAccess/colegiadoMovimientoLogic.php');
$colegiadoMovimientoLogic = new colegiadoMovimientoLogic();

$continua = TRUE;
if (isset($_GET['idColegiado'])) {
    $idColegiado = $_GET['idColegiado'];
    
    $resUltimoMovimiento = $colegiadoMovimientoLogic->obtenerUltimoMovimiento($idColegiado);
    if ($resUltimoMovimiento['estado']) {
        $idTipoMovimiento = $resUltimoMovimiento['idTipoMovimiento'];
    } else {
        $resultado['mensaje'] = "NO SE ENCONTRARON MOVIMIENTOS";
        $continua = FALSE;
    }
} else {
    $resultado['mensaje'] = "ERROR EN LOS DATOS INGRESADOS";
    $continua = FALSE;
}

if ($continua){
    $colegiadoLogic = new colegiadoLogic();
    $resultado = $colegiadoLogic->actualizarEstado($idColegiado, $idTipoMovimiento);
} else {
    $resultado['icono'] = "glyphicon glyphicon-remove";
    $resultado['clase'] = "alert alert-error";
}

?>
<body onLoad="document.forms['myForm'].submit()">
    <?php
    if ($resultado['estado']) {
    ?>
        <form name="myForm" method="POST" action="../colegiado_consulta.php?idColegiado=<?php echo $idColegiado;?>"></form>
    <?php
    } else {
        //vuelve al formulario de solicitud por error
    ?>
        <form name="myForm" method="POST" action="../colegiado_nuevo_baja.php?idColegiado=<?php echo $idColegiado;?>">
            <input type="hidden" name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden" name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden" name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
        </form>
    <?php
    }
    ?>
</body>

