<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/fapLogic.php');
$continua = TRUE;
$mensaje = "";
$fapLogic = new fapLogic();
if (isset($_GET['agregar'])) {
    $idSapReunion = NULL;
    $accion = 'agregar';
} else {
    //si es una modificacion, verifico que venga el idSapCaratula
    if (isset($_GET['editar']) || isset($_GET['cerrar'])) {
        if (isset($_GET['id']) && $_GET['id'] <> "") {
            $idSapReunion = $_GET['id'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idSapReunion - ";
            $tipoMensaje = 'alert alert-danger';
        }
        if (isset($_GET['cerrar'])) {
            $accion = 'cerrar';
        } else {
            $accion = 'editar';
        }
    } else {
        $idSapReunion = NULL;
    }
}
if ($accion == 'cerrar') {
    $estadoReunion = 'C';
    //se inicializan en NULL porque no se actualizan estos datos al cerrar, solo el estadoReunion se pone en C
    $fechaReunion = NULL;
    $resolucion = NULL;
    $observaciones = NULL;
} else {
    if (isset($_POST['estadoReunion']) && $_POST['estadoReunion'] <> "") {
        $estadoReunion = $_POST['estadoReunion'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta estadoReunion - ";
        $tipoMensaje = 'alert alert-danger';
    }
    if (isset($_POST['fechaReunion']) && $_POST['fechaReunion'] <> "") {
        $fechaReunion = $_POST['fechaReunion'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta fechaReunion - ";
        $tipoMensaje = 'alert alert-danger';
    }
    if (isset($_POST['resolucion']) && $_POST['resolucion'] <> "") {
        $resolucion = $_POST['resolucion'];
    } else {
        $resolucion = NULL;
    }
    if (isset($_POST['observaciones']) && $_POST['observaciones'] <> "") {
        $observaciones = $_POST['observaciones'];
    } else {
        $observaciones = NULL;
    }
}
if ($continua) {
    if (isset($idSapReunion)) {
        $resReunion = $fapLogic->obtenerSapReunionPorId($idSapReunion);
        if ($resReunion['estado']) {
            $datosAnteriores = $resReunion['datos'];
        } else {
            $continua = FALSE;
            $mensaje .= $resReunion['mensaje'];
        }
    } else {
        $datosAnteriores = NULL;
    }
    $resultado = $fapLogic->guardarFapReunion($accion, $idSapReunion, $fechaReunion, $estadoReunion, $resolucion, $observaciones, $datosAnteriores);
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
        <form name="myForm"  method="POST" action="../fap_reuniones.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../fap_reuniones_form.php?<?php echo $accion; if ($accion == 'editar') { echo '&id='.$idSapReunion; } ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
        </form>
    <?php
    }
    ?>
</body>

