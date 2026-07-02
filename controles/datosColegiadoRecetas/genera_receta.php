<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/colegiadoLogic.php');
require_once ('../../dataAccess/colegiadoRecetariosLogic.php');
$colegiadoRecetariosLogic = new colegiadoRecetariosLogic();

$continua = TRUE;
$mensaje = 'OK';
if (isset($_POST['idColegiado']) && isset($_POST['idEspecialidad']) && isset($_POST['serie']) && isset($_POST['desde']) && isset($_POST['hasta'])) {
    $idColegiado = $_POST['idColegiado'];
    $idEspecialidad = $_POST['idEspecialidad'];
    $serie = strtoupper($_POST['serie']);
    $desde = $_POST['desde'];
    $hasta = $_POST['hasta'];
    
    //verifico que los numeros sean validos
    if ($desde >= $hasta) {
        $continua = FALSE;
        $mensaje = "EL NUMERO DESDE DEBE SER MENOR AL HASTA";
    } else {
        $limiteRecetas = 1000;
        $cantidadRecetas = $hasta - $desde + 1;
        if ($cantidadRecetas > $limiteRecetas ) {
            $continua = FALSE;
            $mensaje = "NO SE PUEDEN ENTRAGAR MAS DE ".$limiteRecetas." RECETAS";
        }
    }    
    
} else {
    $mensaje = "ERROR EN LOS DATOS INGRESADOS";
    $continua = FALSE;
}

if ($continua){
    $resultado = $colegiadoRecetariosLogic->agregarEntregaReceta($serie, $desde, $hasta, $cantidadRecetas, $idEspecialidad, $idColegiado);
    if ($resultado['estado']) {
        //imprimo el certificado
        $idReceta = $resultado['idReceta'];
    }
} else {
    $resultado['estado'] = FALSE;
    $resultado['icono'] = "glyphicon glyphicon-remove";
    $resultado['clase'] = "alert alert-error";
    $resultado['mensaje'] = $mensaje;
}

?>
<body onLoad="document.forms['myForm'].submit()">
    <?php
    if ($resultado['estado']) {
        //imprime el certificado
    ?>
        <form name="myForm" method="POST" action="imprimir_receta.php?idColegiado=<?php echo $idColegiado;?>&idReceta=<?php echo $idReceta; ?>"></form>
    <?php
    } else {
        //vuelve al formulario de solicitud por error
    ?>
        <h2>Hubo un error en los datos ingresados (<?php echo $resultado['mensaje']; ?>)</h2>
        <h3>Cierre esta pestaña y revise los datos del formulario</h3>
    <?php
    }
    ?>
</body>

