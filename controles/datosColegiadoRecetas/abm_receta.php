<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/colegiadoRecetariosLogic.php');
$colegiadoRecetariosLogic = new colegiadoRecetariosLogic();

$continua = TRUE;
$mensaje = 'OK';
$accion = 1;
if (isset($_GET['accion'])){
    $accion = $_GET['accion'];
}
if (isset($_GET['idColegiado']) && isset($_GET['idReceta'])) {
    $idColegiado = $_GET['idColegiado'];
    $idReceta = $_GET['idReceta'];
} else {
    $mensaje = "ERROR EN LOS DATOS INGRESADOS";
    $continua = FALSE;
}

if ($continua){
    $resultado = $colegiadoRecetariosLogic->borrarEntregaReceta($idReceta);
} else {
    $resultado['estado'] = FALSE;
    $resultado['icono'] = "glyphicon glyphicon-remove";
    $resultado['clase'] = "alert alert-error";
    $resultado['mensaje'] = $mensaje;
}

?>
<body onLoad="document.forms['myForm'].submit()">
    <form name="myForm" method="POST" action="../colegiado_recetarios.php?idColegiado=<?php echo $idColegiado;?>"></form>
</body>

