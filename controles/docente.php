<?php 
header('Content-Type" => application/json');
require_once ('../dataAccess/config.php');
require_once ('../dataAccess/conection_pdo.php');
require_once ('../dataAccess/cursos_pdo.php');
$cursos_pdo = new cursos_pdo();
$docentes = $cursos_pdo->obtenerDocentesAutocompletar();
$data=array('result'=>true,'data'=>$docentes['datos']);
//var_dump($docentes['datos']);exit;
echo json_encode($data );
