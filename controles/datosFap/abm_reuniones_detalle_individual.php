<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/fapLogic.php');
$continua = TRUE;
$mensaje = "";
$fapLogic = new fapLogic();
var_dump($_GET);
if (isset($_GET['agregar'])) {
    $idSapConsejoDetalle = NULL;
    if (isset($_GET['id']) && $_GET['id'] <> "") {
        $idSapConsejo = $_GET['id'];
        $id = $idSapConsejo;
    } else {
        $continua = FALSE;
        $mensaje .= "Falta idSapConsejo - ";
        $tipoMensaje = 'alert alert-danger';
    }
    $accion = 'agregar';
} else {
    //si es una modificacion o borrado, verifico que venga el idSapCaratula
    if (isset($_GET['editar']) || isset($_GET['borrar'])) {
        if (isset($_GET['editar'])) {
            $accion = 'editar';
        } else {
            $accion = 'borrar';
        }
        if (isset($_GET['id']) && $_GET['id'] <> "") {
            $idSapConsejoDetalle = $_GET['id'];
            $id = $idSapConsejoDetalle;
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idSapConsejoDetalle - ";
            $tipoMensaje = 'alert alert-danger';
        }
    } else {
        $idSapConsejoDetalle = NULL;
    }
}

if ($accion <> 'borrar') {
    if (isset($_POST['idSapCaratula']) && $_POST['idSapCaratula'] <> "") {
        $idSapCaratula = $_POST['idSapCaratula'];
        if ($accion == 'agregar') {
            //verifica si ya no existe en otra reunion de consejo
            if ($fapLogic->existeEnReunionConsejo($idSapCaratula)) {
                $continua = FALSE;
                $mensaje .= "Ya existe en otra reunión de consejo - ";
                $tipoMensaje = 'alert alert-danger';
            }
        }
    } else {
        $continua = FALSE;
        $mensaje .= "Falta idSapCaratula - ";
        $tipoMensaje = 'alert alert-danger';
    }
    if (isset($_POST['estado']) && $_POST['estado'] <> "") {
        $estado = $_POST['estado'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta estado - ";
        $tipoMensaje = 'alert alert-danger';
    }
} else {
    $idSapCaratula = NULL;
    $estado = NULL;
}

if (isset($_POST['fechaAprobacion']) && $_POST['fechaAprobacion'] <> "") {
    $fechaAprobacion = $_POST['fechaAprobacion'];
} else {
    $fechaAprobacion = NULL;
}
if (isset($_POST['observacion']) && $_POST['observacion'] <> "") {
    $observacion = $_POST['observacion'];
} else {
    $observacion = NULL;
}

if ($continua) {
    if (isset($idSapConsejoDetalle)) {
        $resDetalle = $fapLogic->obtenerSapReunionDetallePorId($idSapConsejoDetalle);
        if ($resDetalle['estado']) {
            $datosAnteriores = $resDetalle['datos'];
            $idSapConsejo = $datosAnteriores['idSapConsejo'];
        } else {
            $continua = FALSE;
            $mensaje .= $resDetalle['mensaje'];
        }
    } else {
        $datosAnteriores = NULL;
    }
    $resultado = $fapLogic->guardarFapReunionDetalle($accion, $idSapConsejo, $idSapConsejoDetalle, $idSapCaratula, $estado, $fechaAprobacion, $observacion, $datosAnteriores);
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
    ?>
        <form name="myForm"  method="POST" action="../fap_reuniones_detalle.php?id=<?php echo $idSapConsejo; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../fap_reuniones_detalle_form.php?<?php echo $accion; ?>&id=<?php echo $id; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
        </form>
    <?php
    }
    ?>
</body>

