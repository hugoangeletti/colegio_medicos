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
$mensaje = 'OK';
if (isset($_POST['accion']) || isset($_GET['accion'])) {
    if (isset($_POST['accion'])) {
        $accion = $_POST['accion'];

        if (isset($_POST['idResolucion'])){
            $idResolucion = $_POST['idResolucion'];
        } 

        if (isset($_POST['numero']) && isset($_POST['detalle']) && isset($_POST['tipoResolucion']) && isset($_POST['idTipoResolucion']) && isset($_POST['fecha'])) {
            $detalle = $_POST['detalle'];
            $numero = $_POST['numero'];
            $fecha = $_POST['fecha'];
            $tipoResolucion = $_POST['tipoResolucion'];
            $idTipoResolucion = $_POST['idTipoResolucion'];       
            $anioResoluciones = date("Y",strtotime($fecha));        
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
        $accion = $_GET['accion'];
        if (isset($_GET['idResolucion'])){
            $idResolucion = $_GET['idResolucion'];
        } else {
            $continua = FALSE;
            $tipoMensaje = 'alert alert-danger';
            $mensaje = "Faltan datos en el formulario, verifique.";
        } 
    }
    /*
    if ($accion == 1 && !empty($_POST['ids_MesaEntrada'])) {
        $idsMesaEntrada = $_POST['ids_MesaEntrada'];
    } else {
        $idsMesaEntrada = array();
    }
    */
} else {
    $continua = FALSE;
    $tipoMensaje = 'alert alert-danger';
    $mensaje = "Mal ingreso.";
}

if ($continua){
    switch ($accion) 
    {
        case '1':
            $resultado = $resolucionesLogic->agregarResolucion($numero, $fecha, $detalle, $idTipoResolucion);
            break;
        /*
        case '2':
            $resultado = borrarResolucion($idResolucion);
            break;
         * 
         */
        case '3':
            $resultado = $resolucionesLogic->modificarResolucion($idResolucion, $numero, $fecha, $detalle);
            break;
        
        case 'A':
            $resultado = $resolucionesLogic->cambiarEstadoResolucion($idResolucion, 'E', 'A');
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
    if ($accion == 'A') {
    ?>
        <form name="myForm"  method="POST" action="../especialidades_resoluciones_matriculas.php?idResolucion=<?php echo $idResolucion; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
            <input type="hidden" id="estadoResoluciones" name="estadoResoluciones" value="A">
            <input type="hidden" id="anioResoluciones" name="anioResoluciones" value="<?php echo $anioResoluciones; ?>">
        </form>
    <?php
    } else {
        if ($resultado['estado']) {
        ?>
            <form name="myForm"  method="POST" action="../especialidades_resoluciones.php">
                <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
                <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
                <input type="hidden" id="estadoResoluciones" name="estadoResoluciones" value="A">
                <input type="hidden" id="anioResoluciones" name="anioResoluciones" value="<?php echo $anioResoluciones; ?>">
            </form>
        <?php
        } else {
        ?>
            <form name="myForm"  method="POST" action="../especialidades_resoluciones_form.php?idResolucion=<?php echo $idResolucion; ?>&estado=<?php echo $estadoResoluciones; ?>&accion=<?php echo $accion; ?>&anio=<?php echo $anioResoluciones ?>">
                <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
                <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
                <?php 
                if ($idResolucion) {
                    ?>
                    <input type="hidden"  name="idResolucion" id="idResolucion" value="<?php echo $idResolucion;?>">
                <?php
                }
                ?>
                <input type="hidden"  name="detalle" id="detalle" value="<?php echo $detalle;?>">
                <input type="hidden"  name="fecha" id="fecha" value="<?php echo $fecha;?>">
                <input type="hidden"  name="estado" id="estado" value="<?php echo $estado;?>">
                <input type="hidden"  name="tipoResolucion" id="tipoResolucion" value="<?php echo $tipoResolucion;?>">
                <input type="hidden"  name="idTipoResolucion" id="idTipoResolucion" value="<?php echo $idTipoResolucion;?>">
                <input type="hidden"  name="numero" id="numero" value="<?php echo $numero;?>">
            </form>
        <?php
        }
    }
    ?>
</body>

