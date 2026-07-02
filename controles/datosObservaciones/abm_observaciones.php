<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/colegiadoObservacionLogic.php');
$colegiadoObservacionLogic = new colegiadoObservacionLogic();

if (isset($_POST['idColegiadoObservacion'])) {
    $idColegiadoObservacion = $_POST['idColegiadoObservacion'];
    $idColegiado = $_POST['idColegiado'];
    $accion = $_POST['accion'];
} else {
    $idColegiadoObservacion = NULL;
    $idColegiado = $_POST['idColegiado'];
    $accion = 1;
}

$continua = TRUE;
$mensaje = "";
if (isset($_POST['observaciones']) && isset($_POST['observaciones'])) {
    $observaciones = $_POST['observaciones'];
} else {
    $continua = FALSE;
    $tipoMensaje = 'alert alert-danger';
    $mensaje .= "Faltan observaciones, verifique. ";
}
if (isset($_POST['idTipoObservacion']) && isset($_POST['idTipoObservacion'])) {
    $idTipoObservacion = $_POST['idTipoObservacion'];
} else {
    $continua = FALSE;
    $tipoMensaje = 'alert alert-danger';
    $mensaje .= "Faltan idTipoObservacion, verifique. ";
}
if (isset($_POST['estado']) && isset($_POST['estado'])) {
    $estado = $_POST['estado'];
} else {
    $continua = FALSE;
    $tipoMensaje = 'alert alert-danger';
    $mensaje .= "Faltan estado, verifique. ";
}

if ($continua){
    switch ($accion) 
    {
        case '1':
            $resultado = $colegiadoObservacionLogic->agregarColegiadoObservacion($idColegiado, $observaciones, $idTipoObservacion);
            break;
        case '3':
            $resultado = $colegiadoObservacionLogic->editarColegiadoObservacion($idColegiadoObservacion, $observaciones, $estado, $idTipoObservacion);
            break;
        //case '2':
        //    $resultado = $colegiadoObservacionLogic->editarColegiadoObservacion($idColegiadoObservacion, $observaciones, 'B', $idTipoObservacion);
        //    break;
        default:
            break;
    }

    if($resultado['estado']) {
        $tipoMensaje = 'alert alert-success';
    } else {
        $tipoMensaje = 'alert alert-danger';
    }
    $mensaje = $resultado['mensaje'];
}

?>


<body onLoad="document.forms['myForm'].submit()">
    <?php
    if ($resultado['estado']) {
    ?>
        <form name="myForm"  method="POST" action="../colegiado_observaciones.php?idColegiado=<?php echo $idColegiado; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../colegiado_observaciones_form.php?idColegiado=<?php echo $idColegiado; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
            <input type="hidden"  name="idTipoObservacion" id="idTipoObservacion" value="<?php echo $idTipoObservacion;?>">
            <input type="hidden"  name="observaciones" id="observaciones" value="<?php echo $observaciones;?>">
            <input type="hidden"  name="estado" id="estado" value="<?php echo $estado;?>">
            <input type="hidden"  name="accion" id="accion" value="<?php echo $accion;?>">
        </form>
    <?php
    }
    ?>
</body>

