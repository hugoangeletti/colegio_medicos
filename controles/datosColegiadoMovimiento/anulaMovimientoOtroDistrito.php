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
    $idMovimiento = $_GET['id'];
} else {
    $resultado['mensaje'] = "ERROR EN LOS DATOS INGRESADOS";
    $continua = FALSE;
}

if ($continua){
    $resultado = $colegiadoMovimientoLogic->anularMovimientoOtroDistrito($idMovimiento);
} else {
    $resultado['icono'] = "glyphicon glyphicon-remove";
    $resultado['clase'] = "alert alert-error";
}

?>
<body onLoad="document.forms['myForm'].submit()">
    <form name="myForm" method="POST" action="../colegiado_movimientos_distritos.php?idColegiado=<?php echo $idColegiado;?>"></form>
</body>

