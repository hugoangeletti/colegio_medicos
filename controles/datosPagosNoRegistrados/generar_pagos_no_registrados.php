<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/pagosNoRegistradosLogic.php');
$pagosNoRegistradosLogic = new pagosNoRegistradosLogic();

$continua = TRUE;

if (isset($_GET['idColegiado']) && isset($_POST['idLugarPago']) && isset($_POST['fechaPago'])
        && (!empty($_POST['lasCuotas']) || !empty($_POST['lasCuotasPP']))) {
    $idColegiado = $_GET['idColegiado'];
    $idLugarPago = $_POST['idLugarPago'];
    $fechaPago = $_POST['fechaPago'];
    $observaciones = $_POST['detalle'];
    if (!empty($_POST['lasCuotas'])) {
        $lasCuotas = $_POST['lasCuotas'];
    } else {
        $lasCuotas = array();
    }
    if (!empty($_POST['lasCuotasPP'])) {
        $lasCuotasPP = $_POST['lasCuotasPP'];
    } else {
        $lasCuotasPP = array();
    }
} else {
    $mensaje = 'Faltan datos';
    $continua = FALSE;
}

if ($continua){
    $resultado = $pagosNoRegistradosLogic->agregarPagosNoRegistrados($idColegiado, $idLugarPago, $fechaPago, $observaciones, $lasCuotas, $lasCuotasPP);
} else {
    $resultado['mensaje'] = "ERROR EN LOS DATOS INGRESADOS. ".$mensaje;
    $resultado['icono'] = "glyphicon glyphicon-remove";
    $resultado['clase'] = "alert alert-error";
}
?>

<body onLoad="document.forms['myForm'].submit()">
    <?php
    if ($resultado['estado']) {
    ?>
        <form name="myForm"  method="POST" action="../tesoreria_pagosnoregistrados.php?idColegiado=<?php echo $idColegiado; ?>">
            <input type="hidden" name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden" name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden" name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../tesoreria_pagosnoregistrados_nuevo.php?idColegiado=<?php echo $idColegiado; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="glyphicon glyphicon-exclamation-sign">
            <input type="hidden"  name="clase" id="clase" value="alert alert-info">
        </form>
    <?php
    }
    ?>
</body>

