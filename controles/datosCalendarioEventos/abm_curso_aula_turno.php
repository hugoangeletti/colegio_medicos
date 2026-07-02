<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/calendario_eventos_Logic.php');

$continua = TRUE;
$mensaje = "";
if (isset($_POST['accion']) && $_POST['accion'] <> "") {
    if (isset($_POST['idCursoAula']) && $_POST['idCursoAula'] <> "") {
        $idCursoAula = $_POST['idCursoAula'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta idCursoAula";
        $tipoMensaje = 'alert alert-danger';
    }
    $accion = $_POST['accion'];
    if ($accion == "EDITAR") {
        if (isset($_POST['idCursoAulaTurno']) && $_POST['idCursoAulaTurno'] <> "") {
            $idCursoAulaTurno = $_POST['idCursoAulaTurno'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idCursoAulaTurno";
            $tipoMensaje = 'alert alert-danger';
        }
    }
    //verificar datos
    if (isset($_POST['fecha']) && $_POST['fecha'] <> "") {
        $fecha = $_POST['fecha'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta fecha";
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
        $idCursoAulaTurno = $_GET['id'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta id - ";
        $tipoMensaje = 'alert alert-danger';
    }        
}

if ($continua) {
    $calendarioLogic = new calendario_eventosLogic();
    if (isset($idCursoAulaTurno)) {
        $resCursoAulaTurno = $calendarioLogic->obtenerCursoAulaTurnoPorId($idCursoAulaTurno);
        if ($resCursoAulaTurno['estado']) {
            $datosAnteriores = $resCursoAulaTurno['datos'];
            $idCursoAula = $datosAnteriores['idCursoAula'];
        } else {
            $continua = FALSE;
            $mensaje = $resCursoAula['mensaje'];
        }
    } else {
        $datosAnteriores = NULL;
    }
    if ($continua) {
        switch ($accion) {
            case 'EDITAR':
                //primero se verifica si ya no se encuentran ocupados esos turnos por aula
                $resultado = $calendarioLogic->guardarCursoAulaTurno($idCursoAulaTurno, $idCursoAula, $fecha, $horaInicio, $horaFin, $datosAnteriores);
                break;

            case 'BORRAR':
                $estado = 'B';
                $resultado = $calendarioLogic->borrarCursoAulaTurno($idCursoAulaTurno, $estado, $datosAnteriores);
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
        <form name="myForm"  method="POST" action="../calendario_eventos_ver_turnos.php?id=<?php echo $idCursoAula; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
            <input type="hidden"  name="periodoSeleccionado" id="periodoSeleccionado" value="<?php echo $periodoSeleccionado; ?>">
        </form>
    <?php
    } else {
        if ($accion == "EDITAR") {
            $link = "../calendario_eventos_curso_aula_turno_form.php?idCursoAula=".$idCursoAula."&editar&periodo=<?php echo $periodoSeleccionado; ?>";
        } else {
            $link = "../calendario_eventos.php";
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

