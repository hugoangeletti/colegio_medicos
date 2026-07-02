<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/mesaEntradaLogic.php');

$continua = TRUE;
$mensaje = "";
if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idMesaEntrada = $_GET['id'];
} else {
    $continua = FALSE;
    $mensaje .= "Falta idMesaEntrada - ";
}
if (isset($_GET['ingreso']) && $_GET['ingreso'] <> "") {
    $ingreso = $_GET['ingreso'];
} else {
    $continua = FALSE;
    $mensaje .= "Falta ingreso - ";
}
if (isset($_GET['borrar'])) {
    $accion = "BORRAR";
} else {
    if (isset($_GET['anular'])) {
        $accion = "ANULAR";
    } else {
        $continua = FALSE;
        $mensaje .= "Falta accion - ";
    }
}
if ($continua) {
    $mesaEntradaLogic = new mesaEntradaLogic();
    switch ($accion) {
        case "ANULAR":
            $resultado = $mesaEntradaLogic->anularMesaEntrada($idMesaEntrada);
            break;

        case "BORRAR":
            $resultado = $mesaEntradaLogic->borrarMesaEntrada($idMesaEntrada);

        default:
            $continua = FALSE;
            break;
    }
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
    <?php
    $link = '../mesa_entrada_listado.php';
    if ($ingreso == 'notas') {
        $link = '../mesa_entrada_notas_listado.php';
    }
    ?>
    <form name="myForm"  method="POST" action="<?php echo $link; ?>">
        <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
        <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase'];?>">
    </form>
</body>

