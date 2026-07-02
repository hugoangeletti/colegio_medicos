<?php
require_once '../../dataAccess/config.php';
permisoLogueado();
require_once '../../dataAccess/funcionesPhp.php';
require_once '../../dataAccess/colegiado_seguro_Logic.php';
require_once '../../dataAccess/amepla_seguro_logic.php';
set_time_limit(0);

$continuar = TRUE;
if (isset($_POST['procesoAnio']) && $_POST['procesoAnio'] <> "" && isset($_POST['procesoMes']) && $_POST['procesoMes'] <> "") {
    $procesoAnio = $_POST['procesoAnio'];
    $procesoMes = $_POST['procesoMes'];
} else {
    $continuar = FALSE;
    $resultado['mensaje'] = "INGRESO INCORRECTO, FALTA EL PERIODO.";
    $resultado['clase'] = 'alert alert-danger'; 
    $resultado['icono'] = 'glyphicon glyphicon-remove';
}
if (isset($_POST['fechaLimiteProceso']) && $_POST['fechaLimiteProceso'] <> "") {
    $fechaLimiteProceso = $_POST['fechaLimiteProceso'];
} else {
    $continuar = FALSE;
    $resultado['mensaje'] = "INGRESO INCORRECTO, FALTA EL FECHA LIMITE.";
}
if (isset($_POST['idSeguroPraxisMedicaEnvios']) && $_POST['idSeguroPraxisMedicaEnvios'] <> "") {
    $idSeguroPraxisMedicaEnvios = $_POST['idSeguroPraxisMedicaEnvios'];
} else {
    $continuar = FALSE;
    $resultado['mensaje'] = "INGRESO INCORRECTO, FALTA EL idSeguroPraxisMedicaEnvios.";
}

if ($continuar) {
    //primer paso se verifica el estado matricular de colegiado_seguro, en caso no estar activo, se lo marca y se carga la fecha de actualizacion y fecha de carga del nuevo estado
    $colegiado_seguro_Logic = new colegiado_seguro_Logic();
    $resultado = $colegiado_seguro_Logic->generarSeguroPraxisMedica($idSeguroPraxisMedicaEnvios, $procesoAnio, $procesoMes, $fechaLimiteProceso);
} else {
    $resultado['estado'] = $continuar;
    $resultado['clase'] = 'alert alert-danger'; 
    $resultado['icono'] = 'glyphicon glyphicon-remove';
}
var_dump($_POST);
var_dump($resultado);
?>
<body onLoad="document.forms['myForm'].submit()">
    <?php
    if ($resultado['estado']) {
    ?>
        <form name="myForm"  method="POST" action="../seguro_praxis_medica_listado.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../seguro_praxis_medica_form.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
            <input type="hidden"  name="procesoAnio" id="procesoAnio" value="<?php echo $procesoAnio; ?>">
            <input type="hidden"  name="procesoMes" id="procesoMes" value="<?php echo $procesoMes; ?>">
            <input type="hidden"  name="fechaLimiteProceso" id="fechaLimiteProceso" value="<?php echo $fechaLimiteProceso; ?>">
        </form>
    <?php
    }
    ?>
</body>
