<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/eleccionesLocalidadesLogic.php');

$continua = TRUE;
$mensaje = "";
if (isset($_POST['accion'])) {
    $accion = $_POST['accion'];
} else {
    $continua = FALSE;
    $mensaje .= "Falta accion - ";
}
if (isset($_POST['idElecciones'])) {
    $idElecciones = $_POST['idElecciones'];
} else {
    $continua = FALSE;
    $mensaje .= "Falta idElecciones - ";
}
if (isset($_POST['codigoLocalidad']) && $_POST['codigoLocalidad'] <> "") {
    $codigoLocalidad = $_POST['codigoLocalidad'];
} else {
    $continua = FALSE;
    $mensaje .= "Falta codigoLocalidad - ";
}
if (isset($_POST['cantDelegados']) && $_POST['cantDelegados'] <> "") {
    $cantDelegados = $_POST['cantDelegados'];
} else {
    $cantDelegados = NULL;
}

if ($continua){
    $eleccionesLocalidadesLogic = new eleccionesLocalidades();
    switch ($accion) 
    {
        case '1':
            $resultado = $eleccionesLocalidadesLogic->agregarEleccionesLocalidades($idElecciones, $codigoLocalidad, $cantDelegados);
            break;
        case '2':
            $resultado = $eleccionesLocalidadesLogic->borrarEleccionesLocalidades($idEleccionesLocalidad);
            break;
        case '3':
            $resultado = $eleccionesLocalidadesLogic->editarEleccionesLocalidades($idEleccionesLocalidad, $codigoLocalidad, $cantDelegados);
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
/*
var_dump($_POST);
echo '<br>';
var_dump($resultado);
exit;
*/
?>


<body onLoad="document.forms['myForm'].submit()">
    <?php
    if ($resultado['estado']) {
    ?>
        <form name="myForm"  method="POST" action="../elecciones_localidades_lista.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
            <input type="hidden" id="idElecciones" name="idElecciones" value="<?php echo $idElecciones; ?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../elecciones_localidades_form.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase'];?>">
            <input type="hidden"  name="idElecciones" id="idElecciones" value="<?php echo $idElecciones;?>">
            <input type="hidden"  name="codigoLocalidad" id="codigoLocalidad" value="<?php echo $codigoLocalidad;?>">
            <input type="hidden"  name="cantDelegados" id="cantDelegados" value="<?php echo $cantDelegados;?>">
            <input type="hidden"  name="accion" id="accion" value="<?php echo $accion;?>">
        </form>
    <?php
    }
    ?>
</body>

