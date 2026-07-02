<?php 
header('Content-Type" => application/json');
require_once ('../dataAccess/config.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/secretarioadhocLogic.php');
$secretarioadhocLogic = new secretarioadhocLogic();
$secretarios = $secretarioadhocLogic->obtenerSecretarioadhocAutocompletar();
$data=array('result'=>true,'data'=>$secretarios['datos']);
//var_dump($sumariantes['datos']);exit;
echo json_encode($data );