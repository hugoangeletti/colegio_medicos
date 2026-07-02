<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/habilitacionConsultorioLogic.php');
$habilitacionConsultorioLogic = new habilitacionConsultorioLogic();
//var_dump($_POST);
$continua = TRUE;
if (isset($_POST['accion']) && isset($_POST['estadoInspectores']) && isset($_POST['idColegiado'])){
    $accion = $_POST['accion'];
    $estadoInspectores = $_POST['estadoInspectores'];
    $idColegiado = $_POST['idColegiado'];
    $idInspector = NULL;
    if (isset($_POST['idInspector']) && $_POST['idInspector'] != "") {
        $idInspector = $_POST['idInspector'];
    } else {
        $idInspector = $habilitacionConsultorioLogic->existeInspector($idColegiado);
        //var_dump($idInspector); 
        if ($accion == '1' && isset($idInspector)) {
            //si es un alta de inspector debo verificar que ya no existe
            $continua = FALSE;
            $tipoMensaje = 'alert alert-danger';
            $mensaje = 'YA EXISTE EL INSPECTOR';
        }
    }
} else {
    $continua = FALSE;
    $tipoMensaje = 'alert alert-danger';
    $mensaje = 'MAL INGRESO';
}

if ($continua){
    switch ($accion) 
    {
        case '1':
            $resultado = $habilitacionConsultorioLogic->agregarInspector($idColegiado);
            break;
        case '2':
            $resultado = $habilitacionConsultorioLogic->borrarInspector($idInspector, $estadoInspectores);
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
    //if ($resultado['estado']) {
    ?>
    <form name="myForm"  method="POST" action="../habilitaciones_inspectores_lista.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
            <input type="hidden" id="estadoInspectores" name="estadoInspectores" value="<?php echo $estadoInspectores; ?>">
        </form>
    <?php
    //} else {
    ?>
      <!--  <form name="myForm"  method="POST" action="../habilitaciones_inspectores_form.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
            <input type="hidden" id="estadoInspectores" name="estadoInspectores" value="<?php echo $estadoInspectores; ?>">
            <input type="hidden"  name="accion" id="accion" value="<?php echo $accion;?>">
            <input type="hidden" id="idInspector" name="idInspector" value="<?php echo $idInspector; ?>">
        </form>-->
    <?php
    //}
    ?>
</body>

