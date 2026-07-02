<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/colegiadoCertificadosLogic.php');
$colegiadoCertificadosLogic = new colegiadoCertificadosLogic();

$continua = TRUE;
$mensaje = "";
if (isset($_POST['accion']) && $_POST['accion'] <> "") {
    $accion = $_POST['accion'];
    //si es una modificacion, verifico que venga el idSapCaratula
    if ($accion == "EDITAR") {
        if (isset($_POST['idEntidad']) && $_POST['idEntidad'] <> "") {
            $idEntidad = $_POST['idEntidad'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idEntidad";
            $tipoMensaje = 'alert alert-danger';
        }
    } else {
        if ($accion == "AGREGAR") {
            $idEntidad = NULL;
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
    if (isset($_POST['visible']) && ($_POST['visible'] == "1" || $_POST['visible'] == "0")) {
        $visible = $_POST['visible'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta visible";
        $tipoMensaje = 'alert alert-danger';
    }
    if (isset($_POST['borrado']) && ($_POST['borrado'] == "1" || $_POST['borrado'] == "0")) {
        $borrado = $_POST['borrado'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta borrado";
        $tipoMensaje = 'alert alert-danger';
    }
} else {
    $continua = FALSE;
    $mensaje .= "Falta accion";
    $tipoMensaje = 'alert alert-danger';    
}

if ($continua) {
    switch ($accion) {
        case 'AGREGAR':
            $datosAnteriores = NULL;
            $resultado = $colegiadoCertificadosLogic->guardarEntidad($idEntidad, $nombre, $visible, $borrado, $datosAnteriores);
            break;

        case 'EDITAR':
            $resEntidad = $colegiadoCertificadosLogic->obtenerSolicitudCertificadoWebEntidadPorId($idEntidad);
            if ($resEntidad['estado']){
                $datosAnteriores = $resEntidad['datos'];
                $resultado = $colegiadoCertificadosLogic->guardarEntidad($idEntidad, $nombre, $visible, $borrado, $datosAnteriores);
            } else {
                $continua = FALSE;
                $mensaje = $resEntidad['mensaje'];
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
        <form name="myForm"  method="POST" action="../certificado_online_entidad_lista.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
        </form>
    <?php
    } else {
        $link = "../certificado_online_entidad_form.php";
        if ($accion == "AGREGAR") {
            $link .= "?agregar";
        } else {
            if ($accion == "EDITAR") {
                $link .= "?editar&id=".$idRemitente;
            } else {
                $link = "../certificado_online_entidad_lista.php";
            }
        }
        ?>
        <form name="myForm"  method="POST" action="<?php echo $link; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
            <input type="hidden"  name="nombre" id="nombre" value="<?php echo $nombre; ?>">
            <input type="hidden"  name="visible" id="visible" value="<?php echo $visible; ?>">
            <input type="hidden"  name="borrado" id="borrado" value="<?php echo $borrado; ?>">
        </form>
    <?php
    }
    ?>
</body>

