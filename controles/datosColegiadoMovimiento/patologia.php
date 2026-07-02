<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/colegiadoLogic.php');
require_once ('../../dataAccess/colegiadoMovimientoLogic.php');
$colegiadoMovimientoLogic = new colegiadoMovimientoLogic();

$continua = TRUE;
if (isset($_GET['idColegiado']) && isset($_GET['id'])) {
    $idColegiado = $_GET['idColegiado'];
    $idColegiadoMovimiento = $_GET['id'];
    if (isset($_POST['idPatologia']) && $_POST['idPatologia'] <> "") {
        $idPatologia = $_POST['idPatologia'];
    } else {
        $idPatologia = NULL;
    }
} else {
    $resultado['mensaje'] = "ERROR EN LOS DATOS INGRESADOS";
    $continua = FALSE;
}

if ($continua){
    $resultado = $colegiadoMovimientoLogic->patologiaColegiadoMovimiento($idColegiadoMovimiento, $idPatologia);
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
        <form name="myForm" method="POST" action="../colegiado_movimientos.php?idColegiado=<?php echo $idColegiado;?>"></form>
    <?php
    } else {
        //vuelve al formulario de solicitud por error
    ?>
        <form name="myForm" method="POST" action="../colegiado_movimientos_patologia.php?idColegiado=<?php echo $idColegiado;?>&id=<?php echo $idColegiadoMovimiento; ?>">
            <input type="hidden" name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden" name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden" name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
        </form>
    <?php
    }
    ?>
</body>

