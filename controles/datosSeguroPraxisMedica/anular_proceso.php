<?php
require_once '../../dataAccess/config.php';
permisoLogueado();
require_once '../../dataAccess/funcionesConector.php';
require_once '../../dataAccess/funcionesPhp.php';
require_once '../../dataAccess/colegiado_seguro_Logic.php';
set_time_limit(0);

$continuar = TRUE;
if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idSeguro = $_GET['id'];
} else {
    $continuar = FALSE;
    $resultado['mensaje'] = "INGRESO INCORRECTO, FALTA EL idSeguro.";
    $resultado['clase'] = 'alert alert-danger'; 
    $resultado['icono'] = 'glyphicon glyphicon-remove';
}

if ($continuar) {
    //primer paso se verifica el estado matricular de colegiado_seguro, en caso no estar activo, se lo marca y se carga la fecha de actualizacion y fecha de carga del nuevo estado
    $colegiado_seguro_Logic = new colegiado_seguro_Logic();
    $resultado = $colegiado_seguro_Logic->anularProceso($idSeguro);
} else {
    $resultado['estado'] = $continuar;
    $resultado['clase'] = 'alert alert-danger'; 
    $resultado['icono'] = 'glyphicon glyphicon-remove';
}
?>
<body onLoad="document.forms['myForm'].submit()">
    <form name="myForm"  method="POST" action="../seguro_praxis_medica_listado.php">
        <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
        <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
        <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
    </form>
</body>
