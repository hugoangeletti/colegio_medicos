<?php 
header('Content-Type" => application/json');
require_once ('../dataAccess/config.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/remitenteLogic.php');
$remitenteLogic = new remitenteLogic();
$remitentes = $remitenteLogic->obtenerRemitentes();

$data = array('result'=>true,'data'=>$remitentes['datos']);
//var_dump($remitentes['datos']);exit;
echo json_encode($data );