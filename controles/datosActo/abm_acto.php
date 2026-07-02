<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/reconocimientoAntiguedadLogic.php');

$continua = TRUE;
$mensaje = "";
if (isset($_POST['accion']) && $_POST['accion'] <> "") {
    $accion = $_POST['accion'];
    if ($accion == "EDITAR") {
        if (isset($_POST['idReconocimientoAntiguedad']) && $_POST['idReconocimientoAntiguedad'] <> "") {
            $idReconocimientoAntiguedad = $_POST['idReconocimientoAntiguedad'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idReconocimientoAntiguedad";
            $tipoMensaje = 'alert alert-danger';
        }
    } else {
        if ($accion == "AGREGAR") {
            $idReconocimientoAntiguedad = NULL;
        } else {
            $continua = FALSE;
            $mensaje .= "Accion erronea";
            $tipoMensaje = 'alert alert-danger';
        }
    }

    //verificar datos
    if (isset($_POST['anioActo']) && $_POST['anioActo'] <> "") {
        $anioActo = $_POST['anioActo'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta anioActo";
        $tipoMensaje = 'alert alert-danger';
    }
    if (isset($_POST['fechaActo']) && $_POST['fechaActo'] <> "") {
        $fechaActo = $_POST['fechaActo'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta fechaActo";
        $tipoMensaje = 'alert alert-danger';
    }
    if (isset($_POST['lugarActo']) && $_POST['lugarActo'] <> "") {
        $lugarActo = $_POST['lugarActo'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta lugarActo";
        $tipoMensaje = 'alert alert-danger';
    }
    $antiguedad = NULL;
    if ($accion == 'EDITAR') {
        if (isset($_POST['antiguedad']) && $_POST['antiguedad'] <> "") {
            $antiguedad = $_POST['antiguedad'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta antiguedad";
            $tipoMensaje = 'alert alert-danger';
        }
    }
} else {
    if (isset($_GET['borrar'])) {
        if (isset($_GET['id']) && $_GET['id'] <> "") {
            $idReconocimientoAntiguedadDetalle = $_GET['id'];
            $accion = 'BORRAR';
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idReconocimientoAntiguedadDetalle";
            $tipoMensaje = 'alert alert-danger';    
        }
        if (isset($_GET['idActo']) && $_GET['idActo'] <> "") {
            $idReconocimientoAntiguedad = $_GET['idActo'];
            $accion = 'BORRAR';
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idReconocimientoAntiguedadDetalle";
            $tipoMensaje = 'alert alert-danger';    
        }
    } else {
        $continua = FALSE;
        $mensaje .= "INGRESO INCORRECTO";
        $tipoMensaje = 'alert alert-danger';    
    }
}

if ($continua) {
//echo 'idReconocimientoAntiguedad->'.$idReconocimientoAntiguedad.'<br>';
    $actosLogic = new reconocimientoAntiguedadLogic();
    switch ($accion) {
        case 'AGREGAR':
            $resultado = $actosLogic->agregarActo($anioActo, $fechaActo, $lugarActo);
            break;

        case 'EDITAR':
            $resActos = $actosLogic->obtenerActoPorId($idReconocimientoAntiguedad);
            if ($resActos['estado']) {
                $datosAnteriores = $resActos['datos'];
                $resultado = $actosLogic->guardarActo($idReconocimientoAntiguedad, $anioActo, $fechaActo, $lugarActo, $antiguedad, $datosAnteriores);
            } else {
                $continua = FALSE;
                $mensaje = $resActos['mensaje'];
            }
            break;

        case 'BORRAR':
            $resultado = $actosLogic->borrarActo($idReconocimientoAntiguedadDetalle);
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
        if ($accion == "BORRAR") {
            $link = "../reconocimiento_antiguedad_detalle.php?id=".$idReconocimientoAntiguedad;
        } else {
            $link = "../reconocimiento_antiguedad.php";
        }
    ?>
        <form name="myForm"  method="POST" action="<?php echo $link; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
        </form>
    <?php
    } else {
        $link = "../reconocimiento_antiguedad_form.php";
        if ($accion == "AGREGAR") {
            $link .= "?agregar";
        } else {
            if ($accion == "EDITAR") {
                $link .= "?editar&id=".$idReconocimientoAntiguedad;
            } else {
                $link = "../reconocimiento_antiguedad.php";
            }
        }
        ?>
        <form name="myForm"  method="POST" action="<?php echo $link; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
            <input type="hidden"  name="fechaActo" id="fechaActo" value="<?php echo $fechaActo; ?>">
            <input type="hidden"  name="anioActo" id="anioActo" value="<?php echo $anioActo; ?>">
            <input type="hidden"  name="lugarActo" id="lugarActo" value="<?php echo $lugarActo; ?>">
            <input type="hidden"  name="antiguedad" id="antiguedad" value="<?php echo $antiguedad; ?>">
        </form>
    <?php
    }
    ?>
</body>

