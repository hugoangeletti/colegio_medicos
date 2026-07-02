<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/eticaExpedienteMovimientoLogic.php');
$eticaExpedienteMovimientoLogic = new eticaExpedienteMovimientoLogic();

$continua = TRUE;
$accion = $_GET['accion'];
//var_dump($_GET);
//var_dump($_POST);
//echo '<br>';
if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idEticaExpedienteMovimiento = $_GET['id'];
}

if ($accion <> 2) {
    if (isset($_POST['idEticaExpediente']) && isset($_POST['derivado']) && isset($_POST['idEticaEstado']) && isset($_POST['fecha'])) {
        $derivado = $_POST['derivado'];
        $idEticaEstado = $_POST['idEticaEstado'];
        $observacion = $_POST['observacion'];
        $fecha = $_POST['fecha'];
    } else {
        $continua = FALSE;
        $tipoMensaje = 'alert alert-danger';
        $mensaje = "Faltan datos en el movimiento, verifique.";
    }
    if (isset($_POST['estadoExpediente'])) {
        $estadoExpediente = $_POST['estadoExpediente'];
        $idEticaExpediente = $_POST['idEticaExpediente'];
    }
}
if ($continua){
    if (isset($accion)) {
        switch ($accion) {
            case '1':
                // alta
                $resultado = $eticaExpedienteMovimientoLogic->agregarEticaExpedienteMovimiento($idEticaExpediente, $idEticaEstado, $derivado, $observacion, $fecha, NULL);
                break;
            
            case '2':
                // baja
                $resultado = $eticaExpedienteMovimientoLogic->borrarEticaExpedienteMovimiento($idEticaExpedienteMovimiento);
                if ($resultado['estado']) {
                    $estadoExpediente = $resultado['datos']['idEticaEstado'];
                    $idEticaExpediente = $resultado['datos']['idEticaExpediente'];
                }
                break;
            
            case '3':
                // modificacion
                $resultado = $eticaExpedienteMovimientoLogic->modificaEticaExpedienteMovimiento($idEticaExpedienteMovimiento, $idEticaExpediente, $idEticaEstado, $derivado, $observacion, $fecha);
                break;
            
            default:
                echo '-->'.$accion;
                break;
        }
    }
}
//var_dump($resultado);
//exit;
?>
<body onLoad="document.forms['myForm'].submit()">
    <?php
    if ($resultado['estado'] || $accion == 2) {
    ?>
        <form name="myForm"  method="POST" action="../eticaExpediente_movimientos.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $resultado['clase'];?>">
            <input type="hidden"  name="idEticaExpediente" id="idEticaExpediente" value="<?php echo $idEticaExpediente;?>">
            <input type="hidden"  name="estadoExpediente" id="estadoExpediente" value="<?php echo $estadoExpediente;?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../eticaExpediente_movimientos_form.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
            <input type="hidden"  name="idEticaExpediente" id="idEticaExpediente" value="<?php echo $idEticaExpediente;?>">
            <input type="hidden"  name="estadoExpediente" id="estadoExpediente" value="<?php echo $estadoExpediente;?>">
            <input type="hidden"  name="idEticaEstado" id="idEticaEstado" value="<?php echo $idEticaEstado;?>">
            <input type="hidden"  name="derivado" id="derivado" value="<?php echo $derivado;?>">
            <input type="hidden"  name="observacion" id="observacion" value="<?php echo $observacion;?>">
            <input type="hidden"  name="fecha" id="fecha" value="<?php echo $fecha;?>">
        </form>
    <?php
    }
    ?>
</body>

