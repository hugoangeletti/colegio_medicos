<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/conection_pdo.php');
require_once ('../../dataAccess/cursos_pdo.php');

$continua = TRUE;
$mensaje = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['id_docente']) && !empty($_POST['id_curso'])) {
    
    $id_docente = intval($_POST['id_docente']);
    $id_curso = intval($_POST['id_curso']);
    $accion = $_POST['accion'];

    switch ($accion) {
		case 'agregar': 
			$id_cursos_docente = NULL;
			if (!empty($_POST['id_cursos_cargo'])) {
		    	$id_cursos_cargo = intval($_POST['id_cursos_cargo']);
		    } else {
		    	$continua = FALSE;
	        	$mensaje .= 'Falta id_cursos_cargo - ';
			    $tipoMensaje = 'alert alert-danger';
		    }
        	break;

		case 'borrar': 
	        if (!empty($_POST['id_cursos_docente'])) {
	            $id_cursos_docente = $_POST['id_cursos_docente'];
	        } else {
	        	$continua = FALSE;
	        	$mensaje .= 'Falta id_cursos_docente - ';
			    $tipoMensaje = 'alert alert-danger';    
	        }
        	break;

    	default:
        	$continua = FALSE;
        	$mensaje .= 'Ingreso incorrecto - ';
		    $tipoMensaje = 'alert alert-danger';    
    		break;
    }
} else {
    $continua = FALSE;
    $mensaje .= "Faltan datos - ";
    $tipoMensaje = 'alert alert-danger';    
}

if ($continua) {
    $cursos_pdo = new cursos_pdo();
    $id_usuario = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;
    switch ($accion) {
        case 'agregar':
        	$borrado = 0;
            $resultado = $cursos_pdo->guardarCursosDocente($id_cursos_docente, $id_docente, $id_cursos_cargo, $id_curso, $id_usuario, $borrado);
            break;

        case 'borrar':
        	$borrado = 1;
            $resultado = $cursos_pdo->guardarCursosDocente($id_cursos_docente, $id_docente, $id_cursos_cargo, $id_curso, $id_usuario, $borrado);
            break;

        default:
	        $resultado['mensaje'] = 'Ingreso incorrecto al guardar!';
	    	$resultado['estado'] = FALSE;
            break;
    }
} else {
    $resultado['mensaje'] = $mensaje;
    $resultado['estado'] = FALSE;
}
/*
var_dump($_GET);
echo '<br>';
var_dump($_POST);
echo '<br>';
var_dump($resultado);
exit;
*/
?>
<body onLoad="document.forms['myForm'].submit()">
    <?php
    if ($resultado['estado']) {
        // Redirigir de forma exitosa
        header("Location: ../docente_asignar_curso.php?id_docente=" . $id_docente . "&status=success&msg=" . $resultado['mensaje']);
    } else {
        // Redirigir enviando el error controlado por la URL
        header("Location: ../docente_asignar_curso.php?id_docente=" . $id_docente . "&status=error&msg=" . $resultado['mensaje']);
    }
	exit;
    ?>
</body>

