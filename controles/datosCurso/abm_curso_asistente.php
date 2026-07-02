<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/conection_pdo.php');
require_once ('../../dataAccess/cursos_pdo.php');

$continua = TRUE;
$mensaje = "";
if (isset($_POST['accion']) && $_POST['accion'] <> "") {
    $accion = $_POST['accion'];
    if ($accion == "EDITAR") {
        if (isset($_POST['idCursosAsistente']) && $_POST['idCursosAsistente'] <> "") {
            $idCursosAsistente = $_POST['idCursosAsistente'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idCursosAsistente";
            $tipoMensaje = 'alert alert-danger';
        }
    } else {
        if ($accion == "AGREGAR") {
            $idCursosAsistente = NULL;
        }
    }
    if (isset($_POST['idCurso']) && $_POST['idCurso'] <> "") {
        $idCurso = $_POST['idCurso'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta idCurso - ";
        $tipoMensaje = 'alert alert-danger';
    }

    //verificar datos
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
} else {
    if (isset($_GET['borrar']) || isset($_GET['asiste']) || isset($_GET['no_asiste']) || isset($_GET['observacion'])) {
        if (isset($_GET['borrar'])) {
            $accion = "BORRAR";
        } else {
            $accion = "ASISTE";
            if (isset($_GET['no_asiste'])) {
                if (isset($_POST['fecha_baja']) && $_POST['fecha_baja'] <> "") {
                    $fecha_baja = $_POST['fecha_baja'];
                } else {
                    $continua = FALSE;
                    $mensaje .= "Falta fecha_baja - ";
                    $tipoMensaje = 'alert alert-danger';
                }
                if (isset($_POST['motivo_baja']) && $_POST['motivo_baja'] <> "") {
                    $motivo_baja = $_POST['motivo_baja'];
                    $observaciones = $_POST['motivo_baja'];
                } else {
                    $continua = FALSE;
                    $mensaje .= "Falta motivo_baja - ";
                    $tipoMensaje = 'alert alert-danger';
                }
            } else {
                if (isset($_GET['observacion'])) {
                    $observaciones = $_POST['observaciones'];
                    $accion = "OBSERVACIONES";
                } else {
                    $observaciones = NULL;
                }
                $fecha_baja = NULL;
            }
        }
        if (isset($_GET['idCurso']) && $_GET['idCurso'] <> "") {
            $idCurso = $_GET['idCurso'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idCurso - ";
            $tipoMensaje = 'alert alert-danger';
        }        
        if (isset($_GET['id']) && $_GET['id'] <> "") {
            $idCursosAsistente = $_GET['id'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta id - ";
            $tipoMensaje = 'alert alert-danger';
        }        
    } else {
        $continua = FALSE;
        $mensaje .= "Falta idColegiado";
        $tipoMensaje = 'alert alert-danger';    
    }
}

if ($continua) {
    $cursos_pdo = new cursos_pdo();
    if (isset($idCursosAsistente)) {
        $resAsistente = $cursos_pdo->obtenerAsistentePorId($idCursosAsistente);
        if ($resAsistente['estado']) {
            $datosAnteriores = $resAsistente['datos'];
        } else {
            $continua = FALSE;
            $mensaje = $resAsistente['mensaje'];
        }
    } else {
        $datosAnteriores = NULL;
    }
    if ($continua) {
        switch ($accion) {
            case 'AGREGAR':
                $asiste = 'S';
                $borrado = 0;
                $resultado = $cursos_pdo->guardarAsistenteCurso($idCursosAsistente, $idCurso, $idColegiado, $apellidoNombre, $asiste, $borrado, $datosAnteriores);
                break;

            case 'EDITAR':
                $asiste = 'S';
                $borrado = 0;
                $resultado = $cursos_pdo->guardarAsistenteCurso($idCursosAsistente, $idCurso, $idColegiado, $apellidoNombre, $asiste, $borrado, $datosAnteriores);
                break;

            case 'BORRAR':
                $resultado = $cursos_pdo->borrarAsistenteCurso($idCursosAsistente, $datosAnteriores);
                break;

            case 'ASISTE':
            case 'OBSERVACIONES':
                if ($accion == 'OBSERVACIONES') {
                    $asiste = 'S';
                } else {
                    if ($datosAnteriores['asiste'] == "S") {
                        $asiste = 'N';
                    } else {
                        $asiste = 'S';
                    }
                }
                $resultado = $cursos_pdo->asisteAsistenteCurso($idCursosAsistente, $asiste, $fecha_baja, $observaciones, $datosAnteriores);
                break;

            default:
                break;
        }
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
?>
<body onLoad="document.forms['myForm'].submit()">
    <?php
    if ($resultado['estado']) {
    ?>
        <form name="myForm"  method="POST" action="../curso_asistentes.php?id=<?php echo $idCurso; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
        </form>
    <?php
    } else {
        if ($accion == "AGREGAR") {
            $link = "../curso_asistentes_form.php?idCurso=".$idCurso."&agregar";
        } else {
            if ($accion == "EDITAR") {
                $link = "../curso_asistentes_form.php?idCurso=".$idCurso."&editar&id=".$idCursoCuota;
            } else {
                if ($accion == "BORRAR") {
                    $link = "../curso_asistentes.php?id=".$idCurso;
                } else {
                    $link = "../curso_listado.php";
                }
            }
        }
        ?>
        <form name="myForm"  method="POST" action="<?php echo $link; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
            <input type="hidden"  name="idColegiado" id="idColegiado" value="<?php echo $idColegiado; ?>">
            <input type="hidden"  name="colegiado_buscar" id="colegiado_buscar" value="<?php echo $apellidoNombre; ?>">
            <input type="hidden"  name="apellidoNombre" id="apellidoNombre" value="<?php echo $apellidoNombre; ?>">
            <input type="hidden"  name="esColegiado" id="esColegiado" value="<?php echo $esColegiado; ?>">
        </form>
    <?php
    }
    ?>
</body>

