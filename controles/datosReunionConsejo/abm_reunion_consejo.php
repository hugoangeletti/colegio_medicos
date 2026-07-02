<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/conection_pdo.php');
require_once ('../../dataAccess/reunion_consejo_pdo.php');

$continua = TRUE;
$mensaje = "";
if (isset($_POST['accion']) && $_POST['accion'] <> "") {
    $accion = $_POST['accion'];
    //si es una modificacion, verifico que venga el idReunionConsejo
    if ($accion == "EDITAR") {
        if (isset($_POST['idReunionConsejo']) && $_POST['idReunionConsejo'] <> "") {
            $idReunionConsejo = $_POST['idReunionConsejo'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idReunionConsejo";
            $tipoMensaje = 'alert alert-danger';
        }
    } else {
        if ($accion == "AGREGAR") {
            $idReunionConsejo = NULL;
        } else {
            $continua = FALSE;
            $mensaje .= "Accion erronea";
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
    if (isset($_POST['tipoReunion']) && $_POST['tipoReunion'] <> "") {
        $tipoReunion = $_POST['tipoReunion'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta tipoReunion";
        $tipoMensaje = 'alert alert-danger';
    }
    if (isset($_POST['numeroActa']) && $_POST['numeroActa'] <> "") {
        $numeroActa = $_POST['numeroActa'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta numeroActa";
        $tipoMensaje = 'alert alert-danger';
    }
    if (isset($_POST['observacion']) && $_POST['observacion'] <> "") {
        $observacion = $_POST['observacion'];
    } else {
        $observacion = NULL;
    }
} else {
    if (isset($_GET['id']) && $_GET['id'] <> "" && isset($_GET['borrar'])) {
        $idReunionConsejo = $_GET['id'];
        $accion = 'BORRAR';
    } else {
        $continua = FALSE;
        $mensaje .= "Falta accion";
        $tipoMensaje = 'alert alert-danger';
    }
}

if ($continua) {
    $reunionConsejoLogic = new reunion_consejo_pdo();
    $periodoSeleccionado = date('Y');
    switch ($accion) {
        case 'AGREGAR':
            $datosAnteriores = NULL;
            $resultado = $reunionConsejoLogic->guardarReunionConsejo($idReunionConsejo, $fecha, $numeroActa, $tipoReunion, $observacion, $datosAnteriores);
            $idReunionConsejo = $resultado['idReunionConsejo'];
            break;

        case 'EDITAR':
            $resReunion = $reunionConsejoLogic->obtenerReunionConsejoPorId($idReunionConsejo);
            if ($resReunion['estado']) {
                $datosAnteriores = $resReunion['datos'];
                $resultado = $reunionConsejoLogic->guardarReunionConsejo($idReunionConsejo, $fecha, $numeroActa, $tipoReunion, $observacion, $datosAnteriores);
                $periodoSeleccionado = substr($datosAnteriores['fecha'], 0, 4);
            } else {
                $continua = FALSE;
                $mensaje = $resCurso['mensaje'];
            }
            break;

        case 'BORRAR':
            $resReunion = $reunionConsejoLogic->obtenerReunionConsejoPorId($idReunionConsejo);
            if ($resReunion['estado']) {
                $datosAnteriores = $resReunion['datos'];
                $resultado = $reunionConsejoLogic->borrarReunionConsejo($idReunionConsejo, $datosAnteriores);
                $periodoSeleccionado = substr($datosAnteriores['fecha'], 0, 4);
            } else {
                $continua = FALSE;
                $mensaje = $resCurso['mensaje'];
            }
            break;

        case 'CERRAR':
            $resultado = $reunionConsejoLogic->cerrarReunion($idReunionConsejo);
            break;

        default:
            break;
    }
} else {
    $resultado['clase'] = $tipoMensaje;
    $resultado['mensaje'] = $mensaje;
    $resultado['icono'] = "";
    $resultado['estado'] = FALSE;
}
/*
var_dump($_POST);
echo '<br>';
var_dump($resultado);
exit;
*/
?>
<body onLoad="document.forms['myForm'].submit()">
    <?php
    if ($resultado['estado']) {
        if ($accion == "AGREGAR") {
            $link = "../reunion_consejo_asistencia.php?id=".$idReunionConsejo;
        } else {
            $link = "../reunion_consejo_lista.php";
        }
        ?>
        <form name="myForm"  method="POST" action="<?php echo $link; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
            <input type="hidden"  name="periodoSeleccionado" id="periodoSeleccionado" value="<?php echo $periodoSeleccionado; ?>">
        </form>
    <?php
    } else {
        $link = "../reunion_consejo_form.php";
        if ($accion == "AGREGAR") {
            $link .= "?agregar";
        } else {
            if ($accion == "EDITAR") {
                $link .= "?editar&id=".$idReunionConsejo;
            } else {
                $link = "../reunion_consejo_lista.php";
            }
        }
        ?>
        <form name="myForm"  method="POST" action="<?php echo $link; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
            <input type="hidden"  name="fecha" id="fecha" value="<?php echo $fecha; ?>">
            <input type="hidden"  name="numeroActa" id="numeroActa" value="<?php echo $numeroActa; ?>">
            <input type="hidden"  name="tipoReunion" id="tipoReunion" value="<?php echo $tipoReunion; ?>">
            <input type="hidden"  name="observacion" id="observacion" value="<?php echo $observacion; ?>">
        </form>
    <?php
    }
    ?>
</body>

