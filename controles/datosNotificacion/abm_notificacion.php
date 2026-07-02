<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/colegiadoLogic.php');
require_once ('../../dataAccess/notificacionLogic.php');
require_once ('../../dataAccess/colegiadoDeudaAnualLogic.php');

require_once('../../tcpdf/config/lang/spa.php');
require_once('../../tcpdf/tcpdf.php');

$continua = TRUE;
$mensaje = "";
$resultado = NULL;

if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idNotificacion = $_GET['id'];
} else {
    $mensaje .= 'Falta idNotificacion - ';
    $continua = FALSE;
}

//verificamos la accion
if (isset($_GET['anular'])) {
    $estado = "B";
} else {
    if (isset($_GET['enviar'])) {
        $estado = "E";
    } else {
        if (isset($_GET['finalizar'])) {
            $estado = "F";
        } else {
            $mensaje .= 'Falta accion - ';
            $continua = FALSE;
        }
    }
}

if ($continua) {
    $notificacionLogic = new notificacionLogic();
    $resultado = $notificacionLogic->actualizarNotificacionDeuda($idNotificacion, $estado);
} else {
    $resultado['mensaje'] = $mensaje;
    $resultado['clase'] = 'alert alert-danger';
}
/*
var_dump($_POST);
echo '<br>';
var_dump($resultado);
echo '<br>';
exit;
*/
?>
<body onLoad="document.forms['myForm'].submit()">
    <form name="myForm"  method="POST" action="../notificacion_lista.php">
        <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
        <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase'];?>">
    </form>
</body>
