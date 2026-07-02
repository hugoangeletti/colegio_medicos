<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/debitoAutomaticoLogic.php');
$debitoAutomaticoLogic = new debitoAutomaticoLogic();

$continua = TRUE;
$mensaje = "";
if (isset($_GET['id']) && $_GET['id']) {
    $idEnvioDebito = $_GET['id'];
    $resArchivo = $debitoAutomaticoLogic->obtenerEnvioDebitoPorId($idEnvioDebito);
    if ($resArchivo['estado']) {
        $fechaEnvio = $resArchivo['datos']['fechaEnvio'];
        $anio = substr($fechaEnvio, 0, 4);
    } else {
        $anio = date('Y');
    }
} else {
    $continua = FALSE;
    $mensaje .= 'idEnvioDebito no ingresado - ';
}

if ($continua) {
    $resultado = $debitoAutomaticoLogic->borrarEnvioDebito($idEnvioDebito);
} else {
    $resultado['mensaje'] = "ERROR EN LOS DATOS INGRESADOS: ".$mensaje;
    $resultado['icono'] = "glyphicon glyphicon-remove";
    $resultado['clase'] = "alert alert-danger";
    $resultado['estado'] = FALSE;
}
/*
var_dump($_GET);
echo '<br>';
var_dump($_POST);
echo '<br>';
var_dump($resultado);
exit;
*/
?>

<body onLoad="document.forms['myForm'].submit()">
    <form name="myForm" method="POST" action="../debito_automatico.php">
        <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
        <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
        <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
        <input type="hidden"  name="anio" id="anio" value="<?php echo $anio; ?>">
    </form>
</body>

