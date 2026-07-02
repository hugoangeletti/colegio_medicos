<?php 
require_once ('../dataAccess/config.php');
require_once ('../dataAccess/conection_pdo.php');
require_once ('../dataAccess/especialidades_pdo.php');

$objEspecialidades = new especialidades_pdo();

//tomo la query
$query = isset($_POST['query']) ? $_POST['query'] : '';
$todos = isset($_GET['todos']) ? 'todos' : NULL;
$data = array('result' => true,'data' => array());

if (strlen($query) >= 3) {
	$especialidades = $objEspecialidades->obtenerEspecialidadesAutocompletar($todos, $query);	
	$data = array('result'=>true,'data'=>$especialidades['datos']);
	//var_dump($consultorios['datos']);exit;
}
header('Content-Type" => application/json');
echo json_encode($data );