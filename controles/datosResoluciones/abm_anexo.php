<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/resolucionesLogic.php');
$resolucionesLogic = new resolucionesLogic();
require_once ('../../dataAccess/mesaEntradaEspecialistaLogic.php');

$continua = TRUE;
$idResolucion = NULL;
$idAnexo = NULL;
$mensaje = 'OK';
if (isset($_POST['estadoResoluciones']) && $_POST['estadoResoluciones'] != ""){
    $estadoResoluciones = $_POST['estadoResoluciones'];
} else {
    $estadoResoluciones = 'A';
}
if (isset($_POST['anioResoluciones']) && $_POST['anioResoluciones'] != ""){
    $anioResoluciones = $_POST['anioResoluciones'];
} else {
    $anioResoluciones = date('Y');
}
if (isset($_POST['accion'])) {
    $accion = $_POST['accion'];

    if (isset($_POST['idResolucion'])) {
        $idResolucion = $_POST['idResolucion'];
    } 

    if (isset($_POST['idAnexo'])) {
        $idAnexo = $_POST['idAnexo'];
    } 
    if (isset($_POST['observacion']) && isset($_POST['borrado'])) {
        $observacion = $_POST['observacion'];
        $borrado = $_POST['borrado'];
    } else {
        $continua = FALSE;
        $tipoMensaje = 'alert alert-danger';
        $mensaje = "Faltan datos en el formulario, verifique.";
    }
    if (isset($_POST['estado'])) {
        $estado = $_POST['estado'];
    } else {
        $estado = 'A';
    }
} else {
    $continua = FALSE;
    $tipoMensaje = 'alert alert-danger';
    $mensaje = "Faltan datos en el formulario, verifique.";
}

if ($continua){
    switch ($accion) 
    {
        case '1':
            $resultado = $resolucionesLogic->agregarResolucionAnexo($idResolucion, $observacion, $borrado);
            break;
        /*
        case '2':
            $resultado = borrarResolucion($idResolucion);
            break;
         * 
         */
        case '3':
            $resultado = $resolucionesLogic->modificarResolucionAnexo($idAnexo, $observacion, $borrado);
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
} else {
    $resultado['estado'] = $continua;
}
?>


<body onLoad="document.forms['myForm'].submit()">
    <?php
    if ($resultado['estado']) {
    ?>
        <form name="myForm"  method="POST" action="../especialidades_resoluciones_anexos.php?idResolucion=<?php echo $idResolucion; ?>&estado=<?php echo $estadoResoluciones; ?>&accion=1&anio=<?php echo $anioResoluciones ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../especialidades_resoluciones_anexos_form.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
            <?php 
            if ($idAnexo) {
                ?>
                <input type="hidden"  name="idAnexo" id="idAnexo" value="<?php echo $idAnexo;?>">
            <?php
            }
            ?>
            <input type="hidden"  name="idResolucion" id="idResolucion" value="<?php echo $idResolucion;?>">
            <input type="hidden"  name="observacion" id="observacion" value="<?php echo $observacion;?>">
            <input type="hidden"  name="borrado" id="borrado" value="<?php echo $borrado;?>">
            <input type="hidden"  name="accion" id="accion" value="<?php echo $accion;?>">
            <input type="hidden" id="estadoResoluciones" name="estadoResoluciones" value="<?php echo $estadoResoluciones; ?>">
            <input type="hidden" id="anioResoluciones" name="anioResoluciones" value="<?php echo $anioResoluciones; ?>">
        </form>
    <?php
    }
    ?>
</body>

