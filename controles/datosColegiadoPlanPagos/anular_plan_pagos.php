<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/colegiadoPlanPagoLogic.php');
$colegiadoPlanPagoLogic = new colegiadoPlanPagoLogic();

if (isset($_POST['idPlanPagos']) && isset($_POST['idColegiado'])) {
    $idPlanPagos = $_POST['idPlanPagos'];
    $idColegiado = $_POST['idColegiado'];
    $resultado = $colegiadoPlanPagoLogic->anularColegiadoPlanPagos($idPlanPagos);
} else {
    $resultado['estado'] = FALSE;
    $resultado['mensaje'] = "ERROR EN LOS DATOS INGRESADOS.";
    $resultado['icono'] = "glyphicon glyphicon-remove";
    $resultado['clase'] = "alert alert-error";
}
?>

<body onLoad="document.forms['myForm'].submit()">
    <?php
    if ($resultado['estado']) {
    ?>
        <form name="myForm"  method="POST" action="../tesoreria_planesdepago.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../tesoreria_planesdepago_anular.php?idColegiado=<?php echo $idColegiado; ?>&idPP=<?php echo $idPlanPagos; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="glyphicon glyphicon-exclamation-sign">
            <input type="hidden"  name="clase" id="clase" value="alert alert-info">
        </form>
    <?php
    }
    ?>
</body>

