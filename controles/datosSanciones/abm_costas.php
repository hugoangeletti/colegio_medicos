<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/colegiadoSancionLogic.php');
$colegiadoSancionLogic = new colegiadoSancionLogic();

if (isset($_POST['idCostas'])) {
    $idCostas = $_POST['idCostas'];
    $accion = $_POST['accion'];
} else {
    $idCostas = NULL;
    $accion = 1;
}

if (isset($_POST['estado'])) {
    $estado = $_POST['estado'];
} else {
    $estado = 'A';
}

$continua = TRUE;
if (isset($_POST['idColegiadoSancion']) && isset($_POST['cantidadGalenos']) && isset($_POST['fechaVencimiento'])) {
    $idColegiadoSancion = $_POST['idColegiadoSancion'];
    $cantidadGalenos = $_POST['cantidadGalenos'];
    $fechaVencimiento = $_POST['fechaVencimiento'];
} else {
    $continua = FALSE;
    $tipoMensaje = 'alert alert-danger';
    $mensaje = "Faltan datos, verifique.";
}

if ($continua){
    switch ($accion) 
    {
        case '1':
            $resultado = $colegiadoSancionLogic->agregarCostas($idColegiadoSancion, $cantidadGalenos, $fechaVencimiento, $estado);
            break;
        case '3':
            $resultado = $colegiadoSancionLogic->editarCostas($idCostas, $cantidadGalenos, $fechaVencimiento, $estado);
            break;
        case '2':
            $resultado = $colegiadoSancionLogic->editarCostas($idCostas, $cantidadGalenos, $fechaVencimiento, 'B');
            break;
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
        <form name="myForm"  method="POST" action="../secretaria_sanciones.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../secretaria_sanciones_form.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
            <input type="hidden"  name="idColegiadoSancion" id="idColegiadoSancion" value="<?php echo $idColegiadoSancion;?>">
            <input type="hidden"  name="idCostas" id="idCostas" value="<?php echo $idCostas;?>">
            <input type="hidden"  name="cantidadGalenos" id="cantidadGalenos" value="<?php echo $cantidadGalenos;?>">
            <input type="hidden"  name="fechaVencimiento" id="fechaVencimiento" value="<?php echo $fechaVencimiento;?>">
            <input type="hidden"  name="estado" id="estado" value="<?php echo $estado;?>">
            <input type="hidden"  name="accion" id="accion" value="<?php echo $accion;?>">
        </form>
    <?php
    }
    ?>
</body>

