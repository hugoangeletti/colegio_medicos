<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/habilitacionConsultorioLogic.php');
$habilitacionConsultorioLogic = new habilitacionConsultorioLogic();

$continua = TRUE;
if (isset($_POST['idMesaEntradaConsultorio']) && isset($_POST['idMesaEntrada'])){
    $idMesaEntradaConsultorio = $_POST['idMesaEntradaConsultorio'];
    $idMesaEntrada = $_POST['idMesaEntrada'];
} else {
    $continua = FALSE;
    $tipoMensaje = 'alert alert-danger';
    $mensaje = 'MAL INGRESO';
}

if ($continua){
    $resultado = $habilitacionConsultorioLogic->borrarSolicitudHabilitacion($idMesaEntrada);
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
        <form name="myForm"  method="POST" action="../habilitaciones_solicitadas_lista.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../habilitaciones_eliminar_form.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
            <input type="hidden" id="idMesaEntrada" name="idMesaEntrada" value="<?php echo $idMesaEntrada; ?>">
        </form>
    <?php
    }
    ?>
</body>

