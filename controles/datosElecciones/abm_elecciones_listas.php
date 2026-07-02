<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/eleccionesLocalidadesListasLogic.php');
$eleccionesLocalidadesListasLogic = new eleccionesLocalidadesListasLogic();

$continua = TRUE;
$accion = $_POST['accion'];
if (isset($_POST['idElecciones']) && isset($_POST['idEleccionesLocalidad'])){
    $idElecciones = $_POST['idElecciones'];
    $idEleccionesLocalidad = $_POST['idEleccionesLocalidad'];
    if (isset($_POST['idEleccionesLocalidadLista'])){
        $idEleccionesLocalidadLista = $_POST['idEleccionesLocalidadLista'];
    } else {
        $idEleccionesLocalidadLista = NULL;
        $accion = 1;
    }
    
    if (isset($_POST['nombre']) && isset($_POST['tipoLista'])) {
        $nombre = $_POST['nombre'];
        $tipoLista = $_POST['tipoLista'];
    } else {
        $continua = FALSE;
        $tipoMensaje = 'alert alert-danger';
        $mensaje = "Faltan datos, verifique.";
    }
} else {
    $continua = FALSE;
    $tipoMensaje = 'alert alert-danger';
    $mensaje = "Mal acceso.";
}

if ($continua){
    switch ($accion) 
    {
        case '1':
            $resultado = $eleccionesLocalidadesListasLogic->agregarEleccionesLocalidadesLista($idEleccionesLocalidad, $nombre, $tipoLista);
            break;
        case '2':
            $resultado = $eleccionesLocalidadesListasLogic->borrarEleccionesLocalidadesLista($idEleccionesLocalidadLista);
            break;
        case '3':
            $resultado = $eleccionesLocalidadesListasLogic->editarEleccionesLocalidadesLista($idEleccionesLocalidadLista, $nombre, $tipoLista);
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
        <form name="myForm"  method="POST" action="../elecciones_listas_lista.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
            <input type="hidden" id="idElecciones" name="idElecciones" value="<?php echo $idElecciones; ?>">
            <input type="hidden" id="idEleccionesLocalidad" name="idEleccionesLocalidad" value="<?php echo $idEleccionesLocalidad; ?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../elecciones_listas_form.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
            <input type="hidden" id="idElecciones" name="idElecciones" value="<?php echo $idElecciones; ?>">
            <input type="hidden" id="idEleccionesLocalidad" name="idEleccionesLocalidad" value="<?php echo $idEleccionesLocalidad; ?>">
            <input type="hidden"  name="nombre" id="nombre" value="<?php echo $nombre;?>">
            <input type="hidden"  name="tipoLista" id="tipoLista" value="<?php echo $tipoLista;?>">
            <input type="hidden"  name="accion" id="accion" value="<?php echo $accion;?>">
        </form>
    <?php
    }
    ?>
</body>

