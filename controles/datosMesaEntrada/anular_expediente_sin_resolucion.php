<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/mesaEntradaEspecialistaLogic.php');
$mesaEntradaEspecialistaLogic = new mesaEntradaEspecialistaLogic();

$continua = TRUE;
$mensaje = "";
if (isset($_POST['accion']) && $_POST['accion'] <> "") {
    $accion = $_POST['accion'];
    if (isset($_POST['idMesaEntrada']) && $_POST['idMesaEntrada'] <> "") {
        $idMesaEntrada = $_POST['idMesaEntrada'];
    } else {
        $continua = FALSE;
        $mensaje = "Falta idMesaEntrada - ";
    } 
    if (isset($_POST['observacion']) && $_POST['observacion'] <> "") {
        $observacion = $_POST['observacion'];
    } else {
        $continua = FALSE;
        $mensaje = "Falta observacion - ";
    } 
} else {
    $continua = FALSE;
    $mensaje .= 'Ingreso incorrecto';
}
if ($continua) {
    $resultado = $mesaEntradaEspecialistaLogic->anularExpedienteEspecialistaPendientePago($idMesaEntrada, $observacion, $accion);
} else {
    $resultado['mensaje'] = $mensaje;
    $resultado['clase'] = 'alert alert-danger';
}
/*
var_dump($_POST);
echo '<br>';
var_dump($resultado);
exit;
*/
?>

<body onLoad="document.forms['myForm'].submit()">
    <form name="myForm"  method="POST" action="../especialidades_expediente_sin_resolucion.php">
        <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
        <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase'];?>">
    </form>
</body>

