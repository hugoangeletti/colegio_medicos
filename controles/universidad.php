<?php 
header('Content-Type" => application/json');
require_once ('../dataAccess/config.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/universidadLogic.php');
$universidadLogic = new universidadLogic();
$datos = $universidadLogic->obtenerUniversidades();
$data=array('result'=>true,'data'=>$datos['datos']);
//var_dump($datos['datos']);exit;
echo json_encode($data );