<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/colegiadoCertificadosLogic.php');
$colegiadoCertificadosLogic = new colegiadoCertificadosLogic();

$continua = TRUE;
$mensaje = "";
if (isset($_GET['id'])) {
    $idSolicitudCertificadoWeb = $_GET['id'];
} else {
    $mensaje .= "Falta idSolicitudCertificadoWeb - ";
    $continua = FALSE;
}
if (isset($_GET['borrar'])) {
    $accion = "BORRAR";
} else {
    $mensaje .= "Falta accion - ";
    $continua = FALSE;
}

if ($continua){
    switch ($accion) {
        case 'BORRAR':
            $resultado = $colegiadoCertificadosLogic->anularSolicitudCertificadoWeb($idSolicitudCertificadoWeb);
            break;

        default:
            break;
    }
} else {
    $resultado['mensaje'] = $mensaje;
    $resultado['icono'] = "glyphicon glyphicon-remove";
    $resultado['clase'] = "alert alert-error";
}
?>

<body onLoad="document.forms['myForm'].submit()">
    <form name="myForm"  method="POST" action="../certificados_online.php">
        <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
        <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
        <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
    </form>
</body>

