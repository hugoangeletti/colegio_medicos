<?php 
header('Content-Type" => application/json');
require_once ('../dataAccess/config.php');
require_once ('../dataAccess/conection_pdo.php');
require_once ('../dataAccess/cursos_pdo.php');
$cursos_pdo = new cursos_pdo();
$asistentes = $cursos_pdo->obtenerAsistentesAutocompletar();	
$data=array('result'=>true,'data'=>$asistentes['datos']);
//var_dump($asistentes['datos']);exit;
echo json_encode($data );