<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/remitenteLogic.php');

$continua = TRUE;
$mensaje = "";
if (isset($_POST['accion']) && $_POST['accion'] <> "") {
    $accion = $_POST['accion'];
    //si es una modificacion, verifico que venga el idSapCaratula
    if ($accion == "EDITAR") {
        if (isset($_POST['idRemitente']) && $_POST['idRemitente'] <> "") {
            $idRemitente = $_POST['idRemitente'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idRemitente";
            $tipoMensaje = 'alert alert-danger';
        }
    } else {
        if ($accion == "AGREGAR") {
            $idRemitente = NULL;
        } else {
            $continua = FALSE;
            $mensaje .= "Accion erronea";
            $tipoMensaje = 'alert alert-danger';
        }
    }

    //verificar datos
    if (isset($_POST['nombre']) && $_POST['nombre'] <> "") {
        $nombre = $_POST['nombre'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta nombre";
        $tipoMensaje = 'alert alert-danger';
    }
} else {
    $continua = FALSE;
    $mensaje .= "Falta accion";
    $tipoMensaje = 'alert alert-danger';    
}

if ($continua) {
    $remitenteLogic = new remitenteLogic();
    switch ($accion) {
        case 'AGREGAR':
            $datosAnteriores = NULL;
            $resultado = $remitenteLogic->guardarRemitente($idRemitente, $nombre, $datosAnteriores);
            break;

        case 'EDITAR':
            $resRemitente = $remitenteLogic->obtenerRemitentePorId($idRemitente);
            if ($resRemitente['estado']) {
                $datosAnteriores = $resRemitente['datos'];
                $resultado = $remitenteLogic->guardarRemitente($idRemitente, $nombre, $datosAnteriores);
            } else {
                $continua = FALSE;
                $mensaje = $resRemitente['mensaje'];
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
        <form name="myForm"  method="POST" action="../remitente_lista.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
        </form>
    <?php
    } else {
        $link = "../remitente_form.php";
        if ($accion == "AGREGAR") {
            $link .= "?agregar";
        } else {
            if ($accion == "EDITAR") {
                $link .= "?editar&id=".$idRemitente;
            } else {
                $link = "../remitente_lista.php";
            }
        }
        ?>
        <form name="myForm"  method="POST" action="<?php echo $link; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
            <input type="hidden"  name="nombre" id="nombre" value="<?php echo $nombre; ?>">
        </form>
    <?php
    }
    ?>
</body>

