<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/eleccionesLogic.php');

$estadoElecciones = $_POST['estadoElecciones'];
if (isset($_POST['idElecciones'])) {
    $idElecciones = $_POST['idElecciones'];
    $accion = $_POST['accion'];
} else {
    $idElecciones = NULL;
    $accion = 1;
}

$continua = TRUE;
if (isset($_POST['detalle']) && isset($_POST['estado']) && isset($_POST['anio'])) {
    $detalle = $_POST['detalle'];
    $estado = $_POST['estado'];
    $anio = $_POST['anio'];
} else {
    $continua = FALSE;
    $tipoMensaje = 'alert alert-danger';
    $mensaje = "Faltan datos en el expediente, verifique.";
}

if ($continua){
    $eleccionesLogic = new elecciones();
    switch ($accion) 
    {
        case '1':
            $resultado = $eleccionesLogic->agregarElecciones($detalle, $anio);
            break;
        case '2':
            $resultado = $eleccionesLogic->borrarElecciones($idElecciones);
            break;
        case '3':
            $resultado = $eleccionesLogic->editarElecciones($idElecciones, $detalle, $estado, $anio);
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
        <form name="myForm"  method="POST" action="../elecciones_lista.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
            <input type="hidden" id="estadoElecciones" name="estadoElecciones" value="<?php echo $estadoElecciones; ?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../elecciones_form.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
            <input type="hidden"  name="idElecciones" id="idElecciones" value="<?php echo $idElecciones;?>">
            <input type="hidden"  name="detalle" id="detalle" value="<?php echo $detalle;?>">
            <input type="hidden"  name="anio" id="anio" value="<?php echo $anio;?>">
            <input type="hidden"  name="estado" id="estado" value="<?php echo $estado;?>">
            <input type="hidden"  name="accion" id="accion" value="<?php echo $accion;?>">
            <input type="hidden" id="estadoElecciones" name="estadoElecciones" value="<?php echo $estadoElecciones; ?>">
        </form>
    <?php
    }
    ?>
</body>

