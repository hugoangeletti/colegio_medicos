<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/conection_pdo.php');
require_once ('../../dataAccess/cursos_pdo.php');

$continua = TRUE;
$mensaje = "";
$accion =  isset($_POST['accion']) ? $_POST['accion'] : 'AGREGAR';

$id_docente = NULL;
switch ($accion) {
    case 'EDITAR':
        $validar_datos = true;
        if (isset($_POST['idDocente']) && $_POST['idDocente'] <> "") {
            $id_docente = $_POST['idDocente'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idDocente";
            $tipoMensaje = 'alert alert-danger';
        }
        break;
    
    case 'AGREGAR':
        $validar_datos = true;
        break;

    case 'BORRAR':
        if (isset($_POST['idDocente']) && $_POST['idDocente'] <> "") {
            $id_docente = $_POST['idDocente'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idDocente";
            $tipoMensaje = 'alert alert-danger';
        }
        $validar_datos = FALSE;
        break;

    default:
        // code...
        break;
}
//verificar datos
if ($validar_datos) {
    if (isset($_POST['esColegiado']) && $_POST['esColegiado'] <> "") {
        $esColegiado = $_POST['esColegiado'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta esColegiado";
        $tipoMensaje = 'alert alert-danger';
    }
    if ($esColegiado == "S") {
        if (isset($_POST['idColegiado']) && $_POST['idColegiado'] <> "") {
            $idColegiado = $_POST['idColegiado'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idColegiado";
            $tipoMensaje = 'alert alert-danger';
        }
        $apellidoNombre = $_POST['colegiado_buscar'];
        $apellidoNombre = substr($apellidoNombre, strpos($apellidoNombre, '-')+2, 100);
        $apellidoNombre = substr($apellidoNombre, 0, strpos($apellidoNombre, '('));
    } else {
        if ($esColegiado == "N") {
            if (isset($_POST['apellidoNombre']) && $_POST['apellidoNombre'] <> "") {
                $apellidoNombre = $_POST['apellidoNombre'];
                $idColegiado = NULL;
            } else {
                $continua = FALSE;
                $mensaje .= "Falta apellidoNombre";
                $tipoMensaje = 'alert alert-danger';
            }
        } else {
            $continua = FALSE;
            $mensaje .= "esColegiado erroneo - ";
        }
    }
}

if ($continua) {
    $cursos_pdo = new cursos_pdo();
    switch ($accion) {
        case 'AGREGAR':
        case 'EDITAR':
            $borrado = 0;
            $resultado = $cursos_pdo->guardarDocente($id_docente, $idColegiado, $apellidoNombre, $borrado);
            break;

        case 'BORRAR':
            $borrado = 1;
            $resultado = $cursos_pdo->guardarDocente($id_docente, $idColegiado, $apellidoNombre, $borrado);
            break;

        default:
            $resultado['clase'] = 'alert alert-danger';
            $resultado['mensaje'] = "ERROR ACCION.";
            $resultado['icono'] = "";
            $resultado['estado'] = FALSE;
            break;
    }
} else {
    $resultado['clase'] = $tipoMensaje;
    $resultado['mensaje'] = $mensaje;
    $resultado['icono'] = "";
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
$_SESSION['alerta'] = $resultado;
header("Location: ../docente_lista.php"); 
exit;
