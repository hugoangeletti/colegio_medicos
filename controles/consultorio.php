<?php 
header('Content-Type" => application/json');
require_once ('../dataAccess/config.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/consultorioLogic.php');
$consultorioLogic = new consultorioLogic();
$consultorios = $consultorioLogic->obtenerConsultoriosAutocompletar();

$data = array('result'=>true,'data'=>$consultorios['datos']);
//var_dump($consultorios['datos']);exit;
echo json_encode($data );