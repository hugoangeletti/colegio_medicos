<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/calendario_eventos_Logic.php');

$continua = TRUE;
$mensaje = "";
if (isset($_GET['periodo']) && $_GET['periodo'] <> "") {
    $periodoSeleccionado = $_GET['periodo'];
} else {
    $periodoSeleccionado = date('Y');
}
if (isset($_POST['accion']) && $_POST['accion'] <> "") {
    if (isset($_POST['idCursoEntidad']) && $_POST['idCursoEntidad'] <> "") {
        $idCursoEntidad = $_POST['idCursoEntidad'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta idCursoEntidad";
        $tipoMensaje = 'alert alert-danger';
    }
    $accion = $_POST['accion'];
    if ($accion == "EDITAR") {
        if (isset($_POST['idCursoAula']) && $_POST['idCursoAula'] <> "") {
            $idCursoAula = $_POST['idCursoAula'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idCursoAula";
            $tipoMensaje = 'alert alert-danger';
        }
    } else {
        if ($accion == "AGREGAR") {
            $idCursoAula = NULL;
        }
    }
    //verificar datos
    if (isset($_POST['idAula']) && $_POST['idAula'] <> "") {
        $idAula = $_POST['idAula'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta idAula";
        $tipoMensaje = 'alert alert-danger';
    }
    if (isset($_POST['idDia']) && $_POST['idDia'] <> "") {
        $idDia = $_POST['idDia'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta idDia";
        $tipoMensaje = 'alert alert-danger';
    }
    if (isset($_POST['fechaInicio']) && $_POST['fechaInicio'] <> "") {
        $fechaInicio = $_POST['fechaInicio'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta fechaInicio";
        $tipoMensaje = 'alert alert-danger';
    }
    if (isset($_POST['fechaFin']) && $_POST['fechaFin'] <> "") {
        $fechaFin = $_POST['fechaFin'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta fechaFin";
        $tipoMensaje = 'alert alert-danger';
    }
    if (isset($_POST['horaInicio']) && $_POST['horaInicio'] <> "") {
        $horaInicio = $_POST['horaInicio'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta horaInicio";
        $tipoMensaje = 'alert alert-danger';
    }
    if (isset($_POST['horaFin']) && $_POST['horaFin'] <> "") {
        $horaFin = $_POST['horaFin'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta horaFin";
        $tipoMensaje = 'alert alert-danger';
    }
    if (isset($_POST['autorizado']) && $_POST['autorizado'] <> "") {
        $autorizado = $_POST['autorizado'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta autorizado";
        $tipoMensaje = 'alert alert-danger';
    }
    if (isset($_POST['periodicidad']) && $_POST['periodicidad'] <> "") {
        $periodicidad = $_POST['periodicidad'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta periodicidad";
        $tipoMensaje = 'alert alert-danger';
    }
} else {
    $accion = NULL;
    if (isset($_GET['borrar'])) {
        $accion = "BORRAR";
    } else {
        $continua = FALSE;
        $mensaje .= "Error de acceso";
        $tipoMensaje = 'alert alert-danger';    
    }

    if (isset($_GET['id']) && $_GET['id'] <> "") {
        $idCursoAula = $_GET['id'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta id - ";
        $tipoMensaje = 'alert alert-danger';
    }        
}

if ($continua) {
    $calendarioLogic = new calendario_eventosLogic();
    if (isset($idCursoAula)) {
        $resCursoAula = $calendarioLogic->obtenerCursoAulaPorId($idCursoAula);
        if ($resCursoAula['estado']) {
            $datosAnteriores = $resCursoAula['datos'];
            $idCursoEntidad = $datosAnteriores['idCurso'];
        } else {
            $continua = FALSE;
            $mensaje = $resCursoAula['mensaje'];
        }
    } else {
        $datosAnteriores = NULL;
    }
    if ($continua) {
        switch ($accion) {
            case 'AGREGAR':
                //primero se verifica si ya no se encuentran ocupados esos turnos por aula
                //if ($calendarioLogic->noHayTurnoOcupado($idAula, $idDia, $fechaInicio, $fechaFin, $horaInicio, $horaFin, $idCursoAula)) {
                    $resultado = $calendarioLogic->guardarCursoAula($idCursoAula, $idCursoEntidad, $idAula, $idDia, $fechaInicio, $fechaFin, $horaInicio, $horaFin, $autorizado, $periodicidad, $datosAnteriores);
                //} else {
                //    $resultado['estado'] = FALSE;
                //    $resultado['mensaje'] = "YA EXISTEN EVENTOS ASIGNADOS AL AULA Y DIA EN ESA FRANJA HORARIA.";
                //}
                break;

            case 'EDITAR':
                //primero se verifica si ya no se encuentran ocupados esos turnos por aula
                $resultado = $calendarioLogic->guardarCursoAula($idCursoAula, $idCursoEntidad, $idAula, $idDia, $fechaInicio, $fechaFin, $horaInicio, $horaFin, $autorizado, $periodicidad, $datosAnteriores);
                break;

            case 'BORRAR':
                $estado = 'B';
                $resultado = $calendarioLogic->editarCursoAula($idCursoAula, $estado, $datosAnteriores);
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
        <form name="myForm"  method="POST" action="../calendario_eventos_administrar_turnos.php?id=<?php echo $idCursoEntidad; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
            <input type="hidden"  name="periodoSeleccionado" id="periodoSeleccionado" value="<?php echo $periodoSeleccionado; ?>">
        </form>
    <?php
    } else {
        if ($accion == "AGREGAR") {
            $link = "../calendario_eventos_curso_aula_form.php?idCursoEntidad=".$idCursoEntidad."&agregar&periodo=<?php echo $periodoSeleccionado; ?>";
        } else {
            if ($accion == "EDITAR") {
                $link = "../calendario_eventos_curso_aula_form.php?idCursoEntidad=".$idCursoEntidad.'&editar&periodo=<?php echo $periodoSeleccionado; ?>';
            } else {
                $link = "../calendario_eventos.php";
            }
        }
        ?>
        <form name="myForm"  method="POST" action="<?php echo $link; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
            <input type="hidden"  name="idCursoEntidad" id="idCursoEntidad" value="<?php echo $idCursoEntidad;?>">
            <input type="hidden"  name="idCursoAula" id="idCursoAula" value="<?php echo $idCursoAula;?>">
            <input type="hidden"  name="idAula" id="idAula" value="<?php echo $idAula; ?>">
            <input type="hidden"  name="idDia" id="idDia" value="<?php echo $idDia; ?>">
            <input type="hidden"  name="fechaInicio" id="fechaInicio" value="<?php echo $fechaInicio; ?>">
            <input type="hidden"  name="fechaFin" id="fechaFin" value="<?php echo $fechaFin; ?>">
            <input type="hidden"  name="horaInicio" id="horaInicio" value="<?php echo $horaInicio; ?>">
            <input type="hidden"  name="horaFin" id="horaFin" value="<?php echo $horaFin; ?>">
            <input type="hidden"  name="autorizado" id="autorizado" value="<?php echo $autorizado; ?>">
        </form>
    <?php
    }
    ?>
</body>

