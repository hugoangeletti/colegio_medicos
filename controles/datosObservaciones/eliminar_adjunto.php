<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/colegiadoObservacionLogic.php');
$colegiadoObservacionLogic = new colegiadoObservacionLogic();

$continua = TRUE;
$mensaje = "OK";
if (isset($_GET['idColegiado'])) {
    $idColegiado = $_GET['idColegiado'];
    if (isset($_GET['id']) && $_GET['id'] <> "") {
        $idColegiadoObservacion = $_GET['id'];
        if (isset($_GET['idAdjunto']) && $_GET['idAdjunto'] <> "") {
            $idAdjunto = $_GET['idAdjunto'];
        } else {
            $continua = FALSE;
            $mensaje = "NO INICIALIZO ADJUNTO";
        }
    } else {
        $continua = FALSE;
        $mensaje = "NO INICIALIZO LA OBSERVACION";
    }
} else {
    $continua = FALSE;
    $mensaje = "NO INICIALIZO MATRICULA";
}

if ($continua) {
    $resultado = $colegiadoObservacionLogic->eliminarAdjunto($idAdjunto);
    if (!$resultado['estado']) {
        $continua = FALSE;
        $mensaje = $resultado['mensaje'];                            
    }
}

if($continua) {
    $tipoMensaje = 'alert alert-success';
} else {
    $tipoMensaje = 'alert alert-danger';
}
?>

<body onLoad="document.forms['myForm'].submit()">
    <form name="myForm"  method="POST" action="../colegiado_observaciones_adjunto.php?idColegiado=<?php echo $idColegiado; ?>&id=<?php echo $idColegiadoObservacion; ?>">
        <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
        <input type="hidden"  name="clase" id="clase" value="<?php echo $tipoMensaje;?>">
    </form>
</body>


