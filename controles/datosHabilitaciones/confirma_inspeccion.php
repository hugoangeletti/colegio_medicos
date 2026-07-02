<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/habilitacionConsultorioLogic.php');
$habilitacionConsultorioLogic = new habilitacionConsultorioLogic();

$continua = TRUE;
if (isset($_POST['accion']) && $_POST['accion'] != ""){
    $accion = $_POST['accion'];
    if (isset($_POST['idInspectorHabilitacion']) && $_POST['idInspectorHabilitacion'] != "") {
        $fechaInspeccion = $_POST['fechaInspeccion'];
        $idInspectorHabilitacion = $_POST['idInspectorHabilitacion'];
        $observaciones = NULL;
        $fechaHabilitacion = NULL;
        if ($accion != 2) {
            if (isset($_POST['habilita']) && $_POST['habilita'] != ""
                && isset($_POST['fechaInspeccion']) && $_POST['fechaInspeccion'] != ""){
                $habilita = $_POST['habilita'];
                if ($_POST['habilita'] == 'S') {
                    if (isset($_POST['fechaHabilitacion']) && $_POST['fechaHabilitacion'] != "") {
                        $fechaHabilitacion = $_POST['fechaHabilitacion'];
                        $estadoInspeccion = 'H';
                        $observaciones = "";
                    } else {
                        $continua = FALSE;
                        $tipoMensaje = 'alert alert-danger';
                        $mensaje = 'Falta Fecha de Habilitacion';
                    }
                } else {
                    if (isset($_POST['observaciones']) && $_POST['observaciones'] != "") {
                        $observaciones = $_POST['observaciones'];
                        $estadoInspeccion = 'N';
                    } else {
                        $continua = FALSE;
                        $tipoMensaje = 'alert alert-danger';
                        $mensaje = 'Falta Motivo de No Habilitacion';
                    }
                }
            } else {
                $continua = FALSE;
                $tipoMensaje = 'alert alert-danger';
                $mensaje = 'Debe marca si Habilita';
            }
        }
    } else {
        $continua = FALSE;
        $tipoMensaje = 'alert alert-danger';
        $mensaje = 'MAL INGRESO';
    }
}

if ($continua){
    switch ($accion) {
        case 1:
            $resultado = $habilitacionConsultorioLogic->confirmarInspeccion($idInspectorHabilitacion, $fechaHabilitacion, $fechaInspeccion, $observaciones, $estadoInspeccion);
            $volver = '../habilitaciones_asignadas_lista.php';
            break;

        case 2:
            $estadoInspeccion = 'B';
            $resultado = $habilitacionConsultorioLogic->confirmarInspeccion($idInspectorHabilitacion, $fechaHabilitacion, $fechaInspeccion, $observaciones, $estadoInspeccion);
            $volver = '../habilitaciones_confirmadas_lista.php';
            break;

        case 3:
            $resultado = $habilitacionConsultorioLogic->confirmarInspeccion($idInspectorHabilitacion, $fechaHabilitacion, $fechaInspeccion, $observaciones, $estadoInspeccion);
            $volver = '../habilitaciones_confirmadas_lista.php';
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
    $resultado['estado'] = FALSE;
}

if (isset($_POST['idInspector'])) {
    $idInspector = $_POST['idInspector'];
} else {
    $idInspector = "";
}
?>


<body onLoad="document.forms['myForm'].submit()">
    <?php
    if ($resultado['estado']) {
    ?>
        <form name="myForm"  method="POST" action="<?php echo $volver; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
            <input type="hidden"  name="idInspector" id="idInspector" value="<?php echo $idInspector;?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../habilitaciones_inspeccion_form.php?id=<?php echo $idInspectorHabilitacion; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
            <input type="hidden" id="habilita" name="habilita" value="<?php echo $habilita; ?>">
            <input type="hidden" id="fechaInspeccion" name="fechaInspeccion" value="<?php echo $fechaInspeccion; ?>">
            <input type="hidden" id="fechaHabilitacion" name="fechaHabilitacion" value="<?php echo $fechaHabilitacion; ?>">
            <input type="hidden"  name="idInspector" id="idInspector" value="<?php echo $idInspector;?>">
            <input type="hidden"  name="observaciones" id="observaciones" value="<?php echo $observaciones;?>">
            <input type="hidden"  name="accion" id="accion" value="<?php echo $accion; ?>">
        </form>
    <?php
    }
    ?>
</body>

