<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../dataAccess/conection_pdo.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/cursos_pdo.php');

$continua = TRUE;
$mensaje = "";
if (isset($_GET['idCurso']) && $_GET['idCurso'] <> "") {
    $idCurso = $_GET['idCurso'];
} else {
    $continua = FALSE;
    $mensaje .= "Falta idCurso";
    $tipoMensaje = 'alert alert-danger';
}

if ($continua) {
    $cursos_pdo = new cursos_pdo();
    $resultado = $cursos_pdo->actualizarCuotasAsistentesPorCurso($idCurso);
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
    <form name="myForm"  method="POST" action="../curso_cuotas.php?id=<?php echo $idCurso; ?>">
        <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
        <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
        <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
    </form>
</body>

