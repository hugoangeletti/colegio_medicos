<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/eleccionesLocalidadesLogic.php');

$continua = TRUE;
$mensaje = "";
if (isset($_POST['idEleccionesLocalidad'])) {
    $idEleccionesLocalidad = $_POST['idEleccionesLocalidad'];
} else {
    $continua = FALSE;
    $mensaje .= "Falta idEleccionesLocalidad - ";
}
if (isset($_POST['fechaCorte'])) {
    $fechaCorte = $_POST['fechaCorte'];
} else {
    $continua = FALSE;
    $mensaje .= "Falta fechaCorte - ";
}
if (isset($_POST['codigoLocalidad'])) {
    $codigoLocalidad = $_POST['codigoLocalidad'];
} else {
    $continua = FALSE;
    $mensaje .= "Falta codigoLocalidad - ";
}

if ($continua){
    //generamos el padron por la localidad ingresada
    $eleccionesLocalidadesLogic = new eleccionesLocalidades();
    if (!$eleccionesLocalidadesLogic->existePadronGenerado($idEleccionesLocalidad, $fechaCorte)) {
        $resultado = $eleccionesLocalidadesLogic->generarPadronPorLocalidad($codigoLocalidad, $idEleccionesLocalidad, $fechaCorte);
    } else {
        //verificamos si algun colegiado actualiza el estado con tesoreria
        $resultado = $eleccionesLocalidadesLogic->actualizarEstadoTesoreria($idEleccionesLocalidad, $fechaCorte);
        $resultado['estado'] = TRUE;
    }
} else {
    $resultado['clase'] = 'alert alert-danger';
    $resultado['mensaje'] = $mensaje;
}
/*
var_dump($resultado);
echo '<br>';
exit;
*/
?>
<body onLoad="document.forms['myForm'].submit()">
    <?php
    if ($resultado['estado']) {
    ?>
        <form name="myForm"  method="POST" action="../elecciones_imprimir_padron.php?id=<?php echo $idEleccionesLocalidad; ?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../elecciones_generar_padron.php?id=<?php echo $idEleccionesLocalidad; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase'];?>">
            <input type="hidden"  name="fechaCorte" id="fechaCorte" value="<?php echo $fechaCorte;?>">
            <input type="hidden"  name="codigoLocalidad" id="codigoLocalidad" value="<?php echo $codigoLocalidad;?>">
        </form>
    <?php
    }
    ?>
</body>

