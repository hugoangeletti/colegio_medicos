<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/fapLogic.php');
$continua = TRUE;
$mensaje = "";
$fapLogic = new fapLogic();
if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idSapConsejo = $_GET['id'];
    $id = $idSapConsejo;
} else {
    $continua = FALSE;
    $mensaje .= "Falta idSapConsejo - ";
    $tipoMensaje = 'alert alert-danger';
}
if ($continua) {
    if (isset($_POST['fap_seleccionado']) && !empty($_POST['fap_seleccionado'])) {
        $fap_seleccionado = $_POST['fap_seleccionado'];
        $accion = 'agregar';
        foreach ($fap_seleccionado as $idSapCaratula) {
            $idSapConsejoDetalle = NULL;
            $estado = 'P';
            $fechaAprobacion = NULL;
            $observacion = NULL;
            $datosAnteriores = NULL;

            $resultado = $fapLogic->guardarFapReunionDetalle($accion, $idSapConsejo, $idSapConsejoDetalle, $idSapCaratula, $estado, $fechaAprobacion, $observacion, $datosAnteriores);
        }
    } else {
        $continua = FALSE;
        $mensaje .= "No hay Fap seleccionado.";
    }
}

if (!$continua) {
    $resultado['estado'] = FALSE;
    $resultado['mensaje'] = $mensaje;
    $resultado['clase'] = 'alert alert-danger';
    $resultado['icono'] = 'glyphicon glyphicon-remove';
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
        <form name="myForm"  method="POST" action="../fap_reuniones_detalle.php?id=<?php echo $idSapConsejo; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../fap_reuniones_detalle_form.php?id=<?php echo $idSapConsejo; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
        </form>
    <?php
    }
    ?>
</body>

