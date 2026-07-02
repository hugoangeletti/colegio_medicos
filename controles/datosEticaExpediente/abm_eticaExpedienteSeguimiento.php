<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/eticaExpedienteMovimientoLogic.php');
$eticaExpedienteMovimientoLogic = new eticaExpedienteMovimientoLogic();

$estadoExpediente = $_POST['estadoExpediente'];
$continua = TRUE;
if (isset($_POST['idEticaExpediente']) && isset($_POST['fecha']) && isset($_POST['observacion'])) {
    $idEticaExpediente = $_POST['idEticaExpediente'];
    $observacion = $_POST['observacion'];
    $fecha = $_POST['fecha'];
    $idEticaEstado = NULL;
    $derivado = NULL;
} else {
    $continua = FALSE;
    $tipoMensaje = 'alert alert-danger';
    $mensaje = "Faltan datos en el movimiento, verifique.";
}

if ($continua){
    $resultado = $eticaExpedienteMovimientoLogic->agregarEticaExpedienteMovimiento($idEticaExpediente, $idEticaEstado, $derivado, $observacion, $fecha, NULL);

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
        <form name="myForm"  method="POST" action="../eticaExpediente_seguimiento.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
            <input type="hidden"  name="idEticaExpediente" id="idEticaExpediente" value="<?php echo $idEticaExpediente;?>">
            <input type="hidden"  name="estadoExpediente" id="estadoExpediente" value="<?php echo $estadoExpediente;?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../eticaExpediente_seguimiento_form.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
            <input type="hidden"  name="idEticaExpediente" id="idEticaExpediente" value="<?php echo $idEticaExpediente;?>">
            <input type="hidden"  name="observacion" id="observacion" value="<?php echo $observacion;?>">
            <input type="hidden"  name="fecha" id="fecha" value="<?php echo $fecha;?>">
            <input type="hidden"  name="estadoExpediente" id="estadoExpediente" value="<?php echo $estadoExpediente;?>">
        </form>
    <?php
    }
    ?>
</body>

