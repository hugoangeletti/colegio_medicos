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
$periodoActual = $_SESSION['periodoActual'];
echo "Hora de inicio: ".date('H:i:s').'<br>';
$hayRegistro = true;
$cantidad = 0;
//while ($hayRegistro) {
//    $resColegiados = $colegiadoDeudaAnualLogic->obtenerColegiadosEmisionAnualTotal($periodoActual);
//    if ($resColegiados['estado']) {
//        $cantidad += sizeof($resColegiados['datos']);
//        echo "Cantidad acumulada: ".$cantidad.'<br>';
        include_once 'emision_colegiacion_anual_imprimir_2021.php';
//    } else {
//        $hayRegistro = false;
//    }
//}
//echo "Total procesados: ".$cantidad.'<br>';
echo "Hora de Fin: ".date('H:i:s');
?>
<h1>Emisión finalizada</h1>
<?php
require_once '../html/footer.php';
