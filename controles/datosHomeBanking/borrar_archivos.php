<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/homeBankingLogic.php');
$homeBankingLogic = new homeBankingLogic();

$continua = TRUE;
$mensaje = "";
if (isset($_GET['id']) && $_GET['id']) {
    $idHomeBanking = $_GET['id'];
    $resArchivo = $homeBankingLogic->obtenerHomaBankingPorId($idHomeBanking);
    if ($resArchivo['estado']) {
        $periodo = $resArchivo['datos']['periodoProceso'];
        $anio = substr($periodo, 0, 4);
    } else {
        $anio = date('Y');
    }
} else {
    $continua = FALSE;
    $mensaje .= 'idHomeBanking no ingresado - ';
}

if ($continua) {
    $resultado = $homeBankingLogic->borrarHomeBankingArchivo($idHomeBanking);
} else {
    $resultado['mensaje'] = "ERROR EN LOS DATOS INGRESADOS: ".$mensaje;
    $resultado['icono'] = "glyphicon glyphicon-remove";
    $resultado['clase'] = "alert alert-danger";
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
    <form name="myForm" method="POST" action="../home_banking.php">
        <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
        <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
        <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
        <input type="hidden"  name="anio" id="anio" value="<?php echo $anio; ?>">
    </form>
</body>

