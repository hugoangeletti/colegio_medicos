<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/colegiadoCertificadosLogic.php');
$colegiadoCertificadosLogic = new colegiadoCertificadosLogic();

$continua = TRUE;
$mensaje = "";
$accion = $_GET['accion'];
if (isset($_GET['idCertificado'])) {
    $idCertificado = $_GET['idCertificado'];
} else {
    $mensaje .= "Falta idCertificado - ";
    $continua = FALSE;
}

if (isset($_GET['idColegiado'])) {
    $idColegiado = $_GET['idColegiado'];
} else {
    $mensaje .= "Falta idColegiado - ";
    $continua = FALSE;
}

if ($continua){
    switch ($accion) {
        case 3:
            $resultado = $colegiadoCertificadosLogic->anularSolicitudCertificado($idCertificado);
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
    <form name="myForm"  method="POST" action="../colegiado_certificados.php?idColegiado=<?php echo $idColegiado; ?>">
        <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
        <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
        <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
    </form>
</body>

