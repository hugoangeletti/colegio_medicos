<?php
header('Content-Type" => application/json');
require_once ('../dataAccess/config.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/fapLogic.php');

$fapLogic = new fapLogic();
$anioHasta = date('Y');
$anioDesde = $anioHasta - 15;
$idSapTipoTramite = 4;
$resultado = $fapLogic->obtenerCantidadPorAnio($anioDesde, $anioHasta, $idSapTipoTramite);
if ($resultado['estado'] && sizeof($resultado['datos']) > 0) {
	foreach ($resultado['datos'] as $value) {
		$labels[] = $value['anio'];
		$data[] = $value['cantidad'];
	}
} else {
	$labels[] = null;
	$data[] = null;
}
echo json_encode(['labels' => $labels, 'data' => $data]);

//$data = array('result'=>true,'data'=>$resultado['datos']);
//$data = array('data'=>$resultado['datos']);
//var_dump($consultorios['datos']);exit;
//echo json_encode($data );
?>