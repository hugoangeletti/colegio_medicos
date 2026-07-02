<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/colegiadoCargoLogic.php');

$continua = TRUE;
$mensaje = "";
if (isset($_POST['accion']) && $_POST['accion'] <> "") {
    $accion = $_POST['accion'];
    //si es una modificacion, verifico que venga el idSapCaratula
    if ($accion == "EDITAR") {
        if (isset($_POST['idColegiadoCargo']) && $_POST['idColegiadoCargo'] <> "") {
            $idColegiadoCargo = $_POST['idColegiadoCargo'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idColegiadoCargo";
            $tipoMensaje = 'alert alert-danger';
        }
    } else {
        if ($accion == "AGREGAR") {
            $idColegiadoCargo = NULL;
        } else {
            $continua = FALSE;
            $mensaje .= "Accion erronea";
            $tipoMensaje = 'alert alert-danger';
        }
    }
    if (isset($_POST['idColegiado']) && $_POST['idColegiado'] <> "") {
        $idColegiado = $_POST['idColegiado'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta idColegiado";
        $tipoMensaje = 'alert alert-danger';
    }

    //verificar datos
    if (isset($_POST['fechaDesde']) && $_POST['fechaDesde'] <> "") {
        $fechaDesde = $_POST['fechaDesde'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta fechaDesde";
        $tipoMensaje = 'alert alert-danger';
    }
    if (isset($_POST['fechaHasta']) && $_POST['fechaHasta'] <> "") {
        $fechaHasta = $_POST['fechaHasta'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta fechaHasta";
        $tipoMensaje = 'alert alert-danger';
    }
    if (isset($_POST['idCargoColegioSeleccionado']) && $_POST['idCargoColegioSeleccionado'] <> "") {
        $idCargoColegioSeleccionado = $_POST['idCargoColegioSeleccionado'];

        //verificamos si es integrante de Mesa, la fecha desde hasta
        if ($idCargoColegioSeleccionado <> 11) {
            if (isset($_POST['fechaMesaDesde']) && $_POST['fechaMesaDesde'] <> "") {
                $fechaMesaDesde = $_POST['fechaMesaDesde'];
            } else {
                $continua = FALSE;
                $mensaje .= "Falta fechaMesaDesde";
                $tipoMensaje = 'alert alert-danger';
            }
            if (isset($_POST['fechaMesaHasta']) && $_POST['fechaMesaHasta'] <> "") {
                $fechaMesaHasta = $_POST['fechaMesaHasta'];
            } else {
                $continua = FALSE;
                $mensaje .= "Falta fechaMesaHasta";
                $tipoMensaje = 'alert alert-danger';
            }
        } else {
            $fechaMesaDesde = NULL;
            $fechaMesaHasta = NULL;
        }
    } else {
        $continua = FALSE;
        $mensaje .= "Falta idCargoColegioSeleccionado";
        $tipoMensaje = 'alert alert-danger';
    }
} else {
    if (isset($_GET['baja'])) {
        $accion = 'BAJA';
        if (isset($_GET['id']) && $_GET['id'] <> "") {
            $idColegiadoCargo = $_GET['id'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idColegiadoCargo";
            $tipoMensaje = 'alert alert-danger';    
        }
    } else {
        $continua = FALSE;
        $mensaje .= "Acceso erroneo";
        $tipoMensaje = 'alert alert-danger';    
    }
}

if ($continua) {
    $colegiadoCargoLogic = new colegiadoCargoLogic();
    switch ($accion) {
        case 'AGREGAR':
            $datosAnteriores = NULL;
            $resultado = $colegiadoCargoLogic->guardarColegiadoCargo($idColegiadoCargo, $idCargoColegioSeleccionado, $fechaDesde, $fechaHasta, $fechaMesaDesde, $fechaMesaHasta, $idColegiado);
            break;

        case 'EDITAR':
            $resColegiadoCargo = $colegiadoCargoLogic->obtenerColegiadoCargoPorId($idColegiadoCargo);
            if ($resColegiadoCargo['estado']) {
                $datosAnteriores = $resColegiadoCargo['datos'];
                $resultado = $colegiadoCargoLogic->guardarColegiadoCargo($idColegiadoCargo, $idCargoColegioSeleccionado, $fechaDesde, $fechaHasta, $fechaMesaDesde, $fechaMesaHasta, $idColegiado);
            } else {
                $continua = FALSE;
                $mensaje = $resColegiadoCargo['mensaje'];
            }

            break;

        case 'BAJA':
            $resColegiadoCargo = $colegiadoCargoLogic->obtenerColegiadoCargoPorId($idColegiadoCargo);
            if ($resColegiadoCargo['estado']) {
                $datosAnteriores = $resColegiadoCargo['datos'];
                $resultado = $colegiadoCargoLogic->bajaColegiadoCargo($idColegiadoCargo, $datosAnteriores);
            } else {
                $continua = FALSE;
                $mensaje = $resColegiadoCargo['mensaje'];
            }

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
    ?>
        <form name="myForm"  method="POST" action="../secretaria_consejeros.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
        </form>
    <?php
    } else {
        $link = "../secretaria_consejeros_form.php";
        if ($accion == "AGREGAR") {
            $link .= "?agregar";
        } else {
            if ($accion == "EDITAR") {
                $link .= "?editar&id=".$idColegiadoCargo;
            } else {
                $link = "../secretaria_consejeros.php";
            }
        }
        ?>
        <form name="myForm"  method="POST" action="<?php echo $link; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
            <input type="hidden"  name="idCargoColegioSeleccionado" id="idCargoColegioSeleccionado" value="<?php echo $idCargoColegioSeleccionado; ?>">
            <input type="hidden"  name="fechaDesde" id="fechaDesde" value="<?php echo $fechaDesde; ?>">
            <input type="hidden"  name="fechaHasta" id="fechaHasta" value="<?php echo $fechaHasta; ?>">
            <input type="hidden"  name="fechaMesaDesde" id="fechaMesaDesde" value="<?php echo $fechaMesaDesde; ?>">
            <input type="hidden"  name="fechaMesaHasta" id="fechaMesaHasta" value="<?php echo $fechaMesaHasta; ?>">
        </form>
    <?php
    }
    ?>
</body>

