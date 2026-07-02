<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/resolucionesLogic.php');
$resolucionesLogic = new resolucionesLogic();


$continua = TRUE;
$mensaje = 'OK';

if (isset($_GET['id']) && isset($_GET['idResolucion'])) {
    $idResolucionDetalle = $_GET['id'];
    $idResolucion = $_GET['idResolucion'];
} else {
    $mensaje = "ERROR EN LOS DATOS INGRESADOS";
    $continua = FALSE;
}

if ($continua) {
    //agrega el movimiento en mesa de entradas
    $resultado = $resolucionesLogic->realizarBajaResolucionDetalle($idResolucionDetalle);
} else {
    $resultado['estado'] = FALSE;
    $resultado['icono'] = "glyphicon glyphicon-remove";
    $resultado['clase'] = "alert alert-error";
    $resultado['mensaje'] = $mensaje;
}
?>
<body onLoad="document.forms['myForm'].submit()">
    <form name="myForm" method="POST" action="especialidades_resoluciones_matriculas.php?idResolucion=<?php echo $idResolucion; ?>">
        <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
        <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $resultado['clase']; ?>">
    </form>
</body>
