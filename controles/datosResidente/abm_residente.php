<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/colegiadoResidenteLogic.php');
$colegiadoResidenteLogic = new colegiadoResidenteLogic();
require_once ('../../dataAccess/colegiadoDomicilioLogic.php');
$colegiadoDomicilioLogic = new colegiadoDomicilioLogic();
require_once ('../../dataAccess/colegiadoContactoLogic.php');
$colegiadoContactoLogic = new colegiadoContactoLogic();
require_once ('../../dataAccess/colegiadoDeudaAnualLogic.php');
$colegiadoDeudaAnualLogic = new colegiadoDeudaAnualLogic();
require_once ('../../dataAccess/colegiadoLogic.php');

$continua = TRUE;
$mensaje = "";
if (isset($_GET['anular'])) {
    $accion = 2;
    if (isset($_GET['id']) && $_GET['id'] <> "") {
        $idColegiadoResidente = $_GET['id'];
    } else {
        $continua = FALSE;
        $mensaje .= 'Falta idColegiadoResidente - ';
    }
    if (isset($_GET['idColegiado']) && $_GET['idColegiado'] <> "") {
        $idColegiado = $_GET['idColegiado'];
    } else {
        $continua = FALSE;
        $mensaje .= 'Falta idColegiado - ';
    }
} else {
    if (isset($_POST['accion']) && $_POST['accion'] <> "") {
        $accion = $_POST['accion'];
    } else {
        $accion = "";
    }

    if (isset($_POST['idColegiadoResidente']) && $_POST['idColegiadoResidente'] <> "") {
        $idColegiadoResidente = $_POST['idColegiadoResidente'];
        $idColegiado = $_POST['idColegiado'];
    } else {
        $idColegiadoResidente = NULL;
        $idColegiado = $_POST['idColegiado'];
    }
    if (isset($idColegiadoResidente) && $idColegiadoResidente <> "") {
        $accion = 3;
    } else {
        $accion = 1;
    }
    if (isset($_POST['opcion']) && isset($_POST['opcion'])) {
        $opcion = $_POST['opcion'];
    } else {
        $continua = FALSE;
        $tipoMensaje = 'alert alert-danger';
        $mensaje .= "Faltan opcion, verifique. ";
    }
    if (isset($_POST['anio']) && isset($_POST['anio'])) {
        $anio = $_POST['anio'];
    } else {
        $continua = FALSE;
        $tipoMensaje = 'alert alert-danger';
        $mensaje .= "Faltan anio, verifique. ";
    }
    if (isset($_POST['idEntidad']) && isset($_POST['idEntidad'])) {
        $idEntidad = $_POST['idEntidad'];
    } else {
        $continua = FALSE;
        $tipoMensaje = 'alert alert-danger';
        $mensaje .= "Faltan idEntidad, verifique. ";
    }
    if (isset($_POST['adjunto']) && isset($_POST['adjunto'])) {
        $adjunto = $_POST['adjunto'];
    } else {
        $continua = FALSE;
        $tipoMensaje = 'alert alert-danger';
        $mensaje .= "Faltan adjunto, verifique. ";
    }
}

if ($continua){
    switch ($accion) 
    {
        case '1':
            $fechaInicio = date('Y-m-d');
            $anioFin = $_SESSION['periodoActual'] + 1;
            $fechaFin = $anioFin.'-06-30';
            $resultado = $colegiadoResidenteLogic->agregarColegiadoResidente($idColegiado, $opcion, $anio, $idEntidad, $adjunto, $fechaInicio, $fechaFin);
            $idColegiadoResidente = $resultado['idColegiadoResidente'];
            break;

        case '3':
            $resultado = $colegiadoResidenteLogic->editarColegiadoResidente($idColegiadoResidente, $opcion, $anio, $idEntidad, $adjunto);
            break;

        case '2':
            $resultado = $colegiadoResidenteLogic->anularColegiadoResidente($idColegiadoResidente);
            break;

        default:
            break;
    }

    if($resultado['estado'] && $accion == 1) {
        $tipoMensaje = 'alert alert-success';
        if (isset($_POST['calle']) && isset($_POST['lateral']) && isset($_POST['localidad_buscar']) && isset($_POST['idLocalidad'])) {
            $calle = $_POST['calle'];
            $numero = $_POST['numero'];
            $lateral = $_POST['lateral'];
            $piso = $_POST['piso'];
            $depto = $_POST['depto'];
            $localidad_buscar = $_POST['localidad_buscar'];
            $idLocalidad = $_POST['idLocalidad'];
            $codigoPostal = $_POST['codigoPostal'];
        } else {
            $continua = FALSE;
            $tipoMensaje = 'alert alert-danger';
            $mensaje = "Faltan datos en el expediente, verifique.";
        }

        if ($continua){
            $accionDomicilio = 'modificar';
            $resultado = $colegiadoDomicilioLogic->agregarColegiadoDomicilio($idColegiado, $calle, $numero, $lateral, $piso, $depto, $idLocalidad, $codigoPostal, $accionDomicilio);
        } else {
            $resultado['mensaje'] = "ERROR EN LOS DATOS INGRESADOS";
            $resultado['icono'] = "glyphicon glyphicon-remove";
            $resultado['clase'] = "alert alert-error";
        }

        if (isset($_POST['telefonoFijo']) && isset($_POST['telefonoMovil']) && isset($_POST['mail'])) {
            $telefonoFijo = $_POST['telefonoFijo'];
            $telefonoMovil = $_POST['telefonoMovil'];
            $mail = $_POST['mail'];
        } else {
            $continua = FALSE;
            $tipoMensaje = 'alert alert-danger';
            $mensaje = "Faltan datos en el expediente, verifique.";
        }

        if ($continua){
            $accionContacto = 'modificar';
            $resultado = $colegiadoContactoLogic->agregarColegiadoContacto($idColegiado, $telefonoFijo, $telefonoMovil, $mail, $accionContacto);
        } else {
            $resultado['mensaje'] = "ERROR EN LOS DATOS INGRESADOS";
            $resultado['icono'] = "glyphicon glyphicon-remove";
            $resultado['clase'] = "alert alert-error";
        }
    } else {
        $tipoMensaje = 'alert alert-danger';
    }

    if($resultado['estado']) {
        if ($accion == 2) {
            $opcion = 'PAGO_CUOTA';
        }
        $resultado = $colegiadoDeudaAnualLogic->marcarOpcionResidentePorIdColegiado($idColegiado, $_SESSION['periodoActual'], $opcion);
    } else {
        $tipoMensaje = 'alert alert-danger';
    }
    $mensaje = $resultado['mensaje'];
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
        if ($accion == 1 || $accion == 3) {
            $linkDerivar = '../colegiado_residente_imprimir.php?id='.$idColegiadoResidente;
        } else {
            $linkDerivar = '../colegiado_residente_opcion.php?idColegiado='.$idColegiado;
        }
    ?>
        <form name="myForm"  method="POST" action="<?php echo $linkDerivar; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../colegiado_residente_opcion.php?idColegiado=<?php echo $idColegiado; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
            <input type="hidden"  name="idTipoObservacion" id="idTipoObservacion" value="<?php echo $idTipoObservacion;?>">
            <input type="hidden"  name="observaciones" id="observaciones" value="<?php echo $observaciones;?>">
            <input type="hidden"  name="estado" id="estado" value="<?php echo $estado;?>">
            <input type="hidden"  name="accion" id="accion" value="<?php echo $accion;?>">
        </form>
    <?php
    }
    ?>
</body>

