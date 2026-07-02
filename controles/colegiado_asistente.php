<?php 
header('Content-Type" => application/json');
require_once ('../dataAccess/config.php');
require_once ('../dataAccess/conection_pdo.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/cursos_pdo.php');

$cursos_pdo = new cursos_pdo();
if (isset($_GET['colegiado'])) {
	$colegiados = $cursos_pdo->obtenerColegiadosAsistentesAutocompletar();		
} else {
	if (isset($_GET['no_colegiado'])) {
		$colegiados = $cursos_pdo->obtenerAsistentesNoColegiadosAutocompletar();	
	} else {
		$colegiados = array();
	}
}

$data=array('result'=>true,'data'=>$colegiados['datos']);
//var_dump($colegiados['datos']);exit;
echo json_encode($data );