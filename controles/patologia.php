<?php 
header('Content-Type" => application/json');
require_once ('../dataAccess/config.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/patologiasLogic.php');
$patologiasLogic = new patologiasLogic();
$res = $patologiasLogic->obtenerPatologiasAutocompletar();
$data=array('result'=>true,'data'=>$res['datos']);
//var_dump($colegiados['datos']);exit;
echo json_encode($data);