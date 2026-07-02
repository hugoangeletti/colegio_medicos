<?php 
header('Content-Type" => application/json');
require_once ('../dataAccess/config.php');
require_once ('../dataAccess/conection_pdo.php');
require_once ('../dataAccess/cursos_pdo.php');
$cursos_pdo = new cursos_pdo();
if (isset($_GET['por_docente'])) {
	//obtengo los cursos por id_docente
	$id_docente = isset($_POST['id_docente']) ? $_POST['id_docente'] : NULL;
	$cursos = $cursos_pdo->obtenerCursosPorDocente($id_docente);
	if (count($cursos['datos']) > 0) {
        echo '<table class="table table-striped table-bordered">';
        echo '<thead><tr><th>Código</th><th>Nombre del Curso</th></tr></thead>';
        echo '<tbody>';
        foreach ($cursos['datos'] as $curso) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($curso['id_cursos']) . '</td>';
            echo '<td>' . htmlspecialchars($curso['titulo']) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<div class="alert alert-warning text-center">Este docente no tiene cursos asignados actualmente.</div>';
    }	
} else {
	$cursos = $cursos_pdo->obtenerCursosAutocompletar('A');	
	$data=array('result'=>true,'data'=>$cursos['datos']);
	//var_dump($cursos['datos']);exit;
	echo json_encode($data );
}
