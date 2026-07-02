<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
include_once '../../dataAccess/funcionesConector.php';
include_once '../../dataAccess/reunionConsejoLogic.php';

$continua = TRUE;
$mensaje = "";
$reunionConsejoLogic = new reunionConsejoLogic();
if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idReunionConsejoAsistente = $_GET['id'];
    $resReunion = $reunionConsejoLogic->obtenerReunionConsejoPorIdAsistente($idReunionConsejoAsistente);
    if ($resReunion['estado']) {
        $reunion = $resReunion['datos'];
        $idReunionConsejo = $reunion['idReunionConsejo'];
    } else {
        $idReunionConsejo = NULL;
        $continua = FALSE;
        $mensaje .= $resReunion['mensaje'];
    }
} else {
    $continua = FALSE;
    $mensaje .= "Falta idReunionConsejoAsistente - ";
}
if (isset($_GET['asiste'])) {
    $asiste = 'asiste';
    $presente = 'N';
} else {
    if (isset($_GET['no_asiste'])) {
        $asiste = 'no_asiste';
        $presente = 'S';
    } else {
        $continua = FALSE;
        $mensaje .= "Falta accion - ";
    }
}

if ($continua) {
    $datosAnteriores = array(
                            'idReunionConsejoAsistente' => $idReunionConsejoAsistente,
                            'idReunionConsejo' => $idReunionConsejo,
                            'presente' => $presente
                            );

    $resultado = $reunionConsejoLogic->guardarAsistenteEnReunionConsejo($idReunionConsejoAsistente, $asiste, $datosAnteriores);
} else {
    $resultado['estado'] = $continua;
    $resultado['mensaje'] = $mensaje;
    $resultado['clase'] = 'alert alert-danger'; 
    $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
}
/*
var_dump($_GET);
echo '<br>';
var_dump($resultado);
exit;
*/
?>
<body onLoad="document.forms['myForm'].submit()">
    <?php
    if (isset($idReunionConsejo)) {
    ?>
        <form name="myForm"  method="POST" action="../reunion_consejo_asistencia.php?id=<?php echo $idReunionConsejo ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase'];?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono'];?>">
        </form>
    <?php 
    } else {
    ?>
        <form name="myForm"  method="POST" action="../reunion_consejo_lista.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase'];?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono'];?>">
        </form>
    <?php 
    }
    ?>
</body>