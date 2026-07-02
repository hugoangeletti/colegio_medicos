<?php 
header('Content-Type" => application/json');
require_once ('../dataAccess/config.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/entidadLogic.php');
$entidadLogic = new entidadLogic();
$datos = $entidadLogic->obtenerEntidadesAutocompletar(NULL);
$data=array('result'=>true,'data'=>$datos['datos']);
//var_dump($datos['datos']);exit;
echo json_encode($data );