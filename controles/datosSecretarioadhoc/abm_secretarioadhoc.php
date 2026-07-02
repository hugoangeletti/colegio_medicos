<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/secretarioadhocLogic.php');
$secretarioadhocLogic = new secretarioadhocLogic();

if (isset($_POST['idSecretarioadhoc'])) {
    $idSecretarioadhoc = $_POST['idSecretarioadhoc'];
    $accion = $_POST['accion'];
} else {
    $idSecretarioadhoc = NULL;
    $accion = 1;
}

$continua = TRUE;
if (isset($_POST['nombre'])) {
    $nombre = $_POST['nombre'];
    $estado = $_POST['estado'];
} else {
    $continua = FALSE;
    $tipoMensaje = 'alert alert-danger';
    $mensaje = "Faltan datos, verifique.";
}

if ($continua){
    switch ($accion) 
    {
        case '1':
            $resultado = $secretarioadhocLogic->agregarSecretarioadhoc($nombre);
            break;
        case '3':
            $resultado = $secretarioadhocLogic->editarSecretarioadhoc($idSecretarioadhoc, $nombre, $estado);
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
        <form name="myForm"  method="POST" action="../secretarioadhoc_lista.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../secretarioadhoc_form.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
            <input type="hidden"  name="idSecretarioadhoc" id="idSecretarioadhoc" value="<?php echo $idSecretarioadhoc;?>">
            <input type="hidden"  name="nombre" id="nombre" value="<?php echo $nombre;?>">
            <input type="hidden"  name="estado" id="estado" value="<?php echo $estado;?>">
            <input type="hidden"  name="accion" id="accion" value="<?php echo $accion;?>">
        </form>
    <?php
    }
    ?>
</body>

