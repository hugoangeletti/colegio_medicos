<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/calendario_eventos_Logic.php');

$continua = TRUE;
$mensaje = "";
$accion = NULL;

if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idCursoAula = $_GET['id'];
} else {
    $continua = FALSE;
    $mensaje .= "Falta id - ";
    $tipoMensaje = 'alert alert-danger';
}        

if (isset($_POST['borrar']) && $_POST['borrar'] <> "") {
    $borrar = $_POST['borrar'];
} else {
    $continua = FALSE;
    $mensaje .= "Debe sellecionar turnos a borrar - ";
    $tipoMensaje = 'alert alert-danger';
}        

if ($continua) {
    $calendarioLogic = new calendario_eventosLogic();
    $eliminados = 0;
    foreach ($borrar as $idCursoAulaTurno) {
        $estado = 'B';
        $datosAnteriores = array();
        $resultado = $calendarioLogic->borrarCursoAulaTurno($idCursoAulaTurno, $estado, $datosAnteriores);
        if (!$resultado['estado']) { break; }
        $eliminados += 1;
    }
    if ($eliminados == 0) {
        $resultado['clase'] = 'alert alert-danger';
        $resultado['mensaje'] = 'ERROR al borrar turnosnos';
        $resultado['icono'] = "";
        $resultado['estado'] = FALSE;
    }
} else {
    $resultado['clase'] = $tipoMensaje;
    $resultado['mensaje'] = $mensaje;
    $resultado['icono'] = "";
    $resultado['estado'] = FALSE;
}
/*
var_dump($_GET);
echo '<br>';
var_dump($_POST);
echo '<br>';
var_dump($resultado);
exit;
*/
?>
<body onLoad="document.forms['myForm'].submit()">
    <form name="myForm"  method="POST" action="../calendario_eventos_ver_turnos.php?id=<?php echo $idCursoAula; ?>">
        <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
        <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
        <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
    </form>
</body>

