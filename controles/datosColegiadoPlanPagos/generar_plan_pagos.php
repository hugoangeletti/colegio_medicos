<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/colegiadoPlanPagoLogic.php');
$colegiadoPlanPagoLogic = new colegiadoPlanPagoLogic();

$continua = TRUE;
if (isset($_POST['idColegiado']) && isset($_POST['deudaPlanPago']) && isset($_POST['totalActualizado']) && isset($_POST['cuotas'])
        && isset($_POST['totalFinanciar']) && isset($_POST['valorCuota'])) {
    $idColegiado = $_POST['idColegiado'];
    $deudaPlanPago = $_POST['deudaPlanPago'];
    $totalActualizado = $_POST['totalActualizado'];
    $cuotas = $_POST['cuotas'];
    $totalFinanciar = $_POST['totalFinanciar'];
    $valorCuota = $_POST['valorCuota'];
    $recargoExtension = $totalFinanciar - $totalActualizado;
    $deudaAnterior = $totalActualizado - $deudaPlanPago;
} else {
    $mensaje = 'Faltan datos';
    $continua = FALSE;
}
if ($continua){
    $resultado = $colegiadoPlanPagoLogic->agregarColegiadoPlanPagos($idColegiado, $deudaPlanPago, $deudaAnterior, $cuotas, $totalFinanciar, $valorCuota, $recargoExtension);
} else {
    $resultado['mensaje'] = "ERROR EN LOS DATOS INGRESADOS. ".$mensaje;
    $resultado['icono'] = "glyphicon glyphicon-remove";
    $resultado['clase'] = "alert alert-error";
}
?>

<body onLoad="document.forms['myForm'].submit()">
    <?php
    if ($resultado['estado']) {
        $idPlanPago = $resultado['idPlanPago'];
    ?>
        <form name="myForm"  method="POST" action="imprimir_plan_pagos.php?idPP=<?php echo $idPlanPago; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../colegiado_tesoreria.php?idColegiado=<?php echo $idColegiado; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="glyphicon glyphicon-exclamation-sign">
            <input type="hidden"  name="clase" id="clase" value="alert alert-info">
        </form>
    <?php
    }
    ?>
</body>

