<?php 
header('Content-Type" => application/json');
require_once ('../dataAccess/config.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/colegiadoLogic.php');
$colegiadoLogic = new colegiadoLogic();
if (isset($_GET['activos']) && $_GET['activos'] == "SI") {
	$colegiados = $colegiadoLogic->obtenerColegiadosAutocompletar('activos');
} else {
	$colegiados = $colegiadoLogic->obtenerColegiadosAutocompletar('todos');
}

$data=array('result'=>true,'data'=>$colegiados['datos']);
//var_dump($colegiados['datos']);exit;
echo json_encode($data );