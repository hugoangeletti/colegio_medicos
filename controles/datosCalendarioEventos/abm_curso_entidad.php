<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/calendario_eventos_Logic.php');

$continua = TRUE;
$mensaje = "";
if (isset($_POST['accion']) && $_POST['accion'] <> "") {
    $accion = $_POST['accion'];
    if ($accion == "EDITAR") {
        if (isset($_POST['idCursoEntidad']) && $_POST['idCursoEntidad'] <> "") {
            $idCursoEntidad = $_POST['idCursoEntidad'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idCursoEntidad";
            $tipoMensaje = 'alert alert-danger';
        }
    } else {
        if ($accion == "AGREGAR") {
            $idCursoEntidad = NULL;
        }
    }
    //verificar datos
    if (isset($_POST['titulo']) && $_POST['titulo'] <> "") {
        $titulo = $_POST['titulo'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta titulo";
        $tipoMensaje = 'alert alert-danger';
    }
    if (isset($_POST['director']) && $_POST['director'] <> "") {
        $director = $_POST['director'];
    } else {
        $director = NULL;
    }
    if (isset($_POST['fechaInicio']) && $_POST['fechaInicio'] <> "") {
        $fechaInicio = $_POST['fechaInicio'];
    } else {
        $fechaInicio = NULL;
    }
    if (isset($_POST['vigenciaHasta']) && $_POST['vigenciaHasta'] <> "") {
        $vigenciaHasta = $_POST['vigenciaHasta'];
    } else {
        $vigenciaHasta = NULL;
    }
    if (isset($_POST['observacion']) && $_POST['observacion'] <> "") {
        $observacion = $_POST['observacion'];
    } else {
        $observacion = NULL;
    }
} else {
    $accion = NULL;
    if (isset($_GET['borrar'])) {
        $accion = "BORRAR";
    } else {
        if (isset($_GET['finalizar'])) {
            $accion = "FINALIZAR";
        } else {
            if (isset($_GET['abrir'])) {
                $accion = "ABRIR";
            } else {
                $continua = FALSE;
                $mensaje .= "Error de acceso";
                $tipoMensaje = 'alert alert-danger';    
            }
        }
    }

    if (isset($_GET['id']) && $_GET['id'] <> "") {
        $idCursoEntidad = $_GET['id'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta id - ";
        $tipoMensaje = 'alert alert-danger';
    }        
}

if ($continua) {
    $calendarioLogic = new calendario_eventosLogic();
    if (isset($idCursoEntidad)) {
        $resCursoEntidad = $calendarioLogic->obtenerCursoEntidadPorId($idCursoEntidad);
        if ($resCursoEntidad['estado']) {
            $datosAnteriores = $resCursoEntidad['datos'];
        } else {
            $continua = FALSE;
            $mensaje = $resCursoEntidad['mensaje'];
        }
    } else {
        $datosAnteriores = NULL;
    }
    if ($continua) {

        switch ($accion) {
            case 'AGREGAR':
                $resultado = $calendarioLogic->guardarCursoEntidad($idCursoEntidad, $titulo, $director, $fechaInicio, $vigenciaHasta, $observacion, $datosAnteriores);
                break;

            case 'EDITAR':
                $resultado = $calendarioLogic->guardarCursoEntidad($idCursoEntidad, $titulo, $director, $fechaInicio, $vigenciaHasta, $observacion, $datosAnteriores);
                break;

            case 'BORRAR':
                $estado = 'B';
                $resultado = $calendarioLogic->editarCursoEntidad($idCursoEntidad, $estado, $datosAnteriores);
                break;

            case 'FINALIZAR':
                $estado = 'F';
                $resultado = $calendarioLogic->editarCursoEntidad($idCursoEntidad, $estado, $datosAnteriores);
                break;

            case 'ABRIR':
                $estado = 'A';
                $resultado = $calendarioLogic->editarCursoEntidad($idCursoEntidad, $estado, $datosAnteriores);
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
        <form name="myForm"  method="POST" action="../calendario_eventos.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
        </form>
    <?php
    } else {
        if ($accion == "AGREGAR") {
            $link = "../calendario_eventos_form.php?id=".$idCursoEntidad."&agregar";
        } else {
            if ($accion == "EDITAR") {
                $link = "../calendario_eventos_form.php?id=".$idCursoEntidad.'&editar';
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
            <input type="hidden"  name="titulo" id="titulo" value="<?php echo $titulo; ?>">
            <input type="hidden"  name="director" id="director" value="<?php echo $director; ?>">
            <input type="hidden"  name="fechaInicio" id="fechaInicio" value="<?php echo $fechaInicio; ?>">
            <input type="hidden"  name="vigenciaHasta" id="vigenciaHasta" value="<?php echo $vigenciaHasta; ?>">
            <input type="hidden"  name="observacion" id="observacion" value="<?php echo $observacion; ?>">
        </form>
    <?php
    }
    ?>
</body>

