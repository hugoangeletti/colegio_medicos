<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/pagosNoRegistradosLogic.php');
$pagosNoRegistradosLogic = new pagosNoRegistradosLogic();

if (isset($_POST['idPagoNoRegistrado'])) {
    $idPagoNoRegistrado = $_POST['idPagoNoRegistrado'];
    $resultado = $pagosNoRegistradosLogic->anularPagoNoRegistrado($idPagoNoRegistrado);
    $idColegiado = $_POST['idColegiado'];
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
        <form name="myForm"  method="POST" action="../tesoreria_pagosnoregistrados.php?idColegiado=<?php echo $idColegiado; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="EL PAGO NO REGISTRADO FUE ANULADA CORRECTAMENTE">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../tesoreria_pagosnoregistrados_anular.php?idPago=<?php echo $idPagoNoRegistrado; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="glyphicon glyphicon-exclamation-sign">
            <input type="hidden"  name="clase" id="clase" value="alert alert-info">
        </form>
    <?php
    }
    ?>
</body>

