<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoDeudaAnualLogic.php');
$colegiadoDeudaAnualLogic = new colegiadoDeudaAnualLogic();

$emisionTotal = "S";
$periodoActual = PERIODO_ACTUAL;
$inicio = date('H:i:s');
$hayRegistro = true;
$cantidad = 0;
$resColegiados = $colegiadoDeudaAnualLogic->obtenerColegiadosEmisionAnualTotal($periodoActual);
if ($resColegiados['estado']) {
    $cantidad += sizeof($resColegiados['datos']);
    echo "Cantidad acumulada: ".$cantidad.'<br>';
    include_once 'emision_colegiacion_anual_imprimir.php';
} else {
    $hayRegistro = false;
}
echo '<br>';
echo "Hora de inicio: ".$inicio.'<br>';
echo "Total procesados: ".$cantidad.'<br>';
echo "Hora de Fin: ".date('H:i:s');
?>
<h1>Emisión finalizada</h1>
<?php
require_once '../html/footer.php';
