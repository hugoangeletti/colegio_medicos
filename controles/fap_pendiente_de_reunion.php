<?php 
header('Content-Type" => application/json');
require_once ('../dataAccess/config.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/fapLogic.php');
$fapLogic = new fapLogic();
$resultado = $fapLogic->obtenerFapPenditesDeReunionAutocompletar();	

$data=array('result'=>true,'data'=>$resultado['datos']);
//var_dump($colegiados['datos']);exit;
echo json_encode($data );