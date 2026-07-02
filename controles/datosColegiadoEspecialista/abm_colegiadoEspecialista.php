<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/colegiadoEspecialistaLogic.php');
$colegiadoEspecialistaLogic = new colegiadoEspecialistaLogic();

$continua = TRUE;
$mensaje = "";
if (isset($_POST['accion']) && $_POST['accion'] <> "") {
    $accion = $_POST['accion'];
    if (isset($_POST['idColegiado']) && $_POST['idColegiado'] <> "") {
        $idColegiado = $_POST['idColegiado'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta idColegiado";
        $tipoMensaje = 'alert alert-danger';
    }
} else {
    if (isset($_GET['accion']) && $_GET['accion'] <> "") {
        $accion = $_GET['accion'];
        if (isset($_GET['idColegiado']) && $_GET['idColegiado'] <> "") {
            $idColegiado = $_GET['idColegiado'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idColegiado";
            $tipoMensaje = 'alert alert-danger';
        }
    } else {
        $mensaje .= "Falta accion";
        $tipoMensaje = 'alert alert-danger';
    }
}
if ($accion <> 1) {
    if ($accion == 2) {
        if (isset($_GET['id']) && $_GET['id'] <> "") {
            $idColegiadoEspecialista = $_GET['id'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idColegiadoEspecialista";
            $tipoMensaje = 'alert alert-danger';
        }
    } else {
        if (isset($_POST['idColegiadoEspecialista']) && $_POST['idColegiadoEspecialista'] <> "") {
            $idColegiadoEspecialista = $_POST['idColegiadoEspecialista'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idColegiadoEspecialista";
            $tipoMensaje = 'alert alert-danger';
        }
        if (isset($_POST['fechaVencimiento']) && $_POST['fechaVencimiento'] <> "") {
            $fechaVencimiento = $_POST['fechaVencimiento'];
        } else {
            $fechaVencimiento = NULL;
        }
        $idEspecialidad = NULL;
    }
} else {
/*    if (isset($_POST['idEspecialidad']) && $_POST['idEspecialidad'] <> "") {
        $idEspecialidad = $_POST['idEspecialidad'];
    } else {
        $continua = FALSE;
        $tipoMensaje = 'alert alert-danger';
        $mensaje = "Falta idEspecialidad.";
    }
    if (isset($_POST['fechaEspecialista']) && $_POST['fechaEspecialista'] <> "") {
        $fechaEspecialista = $_POST['fechaEspecialista'];
    } else {
        $continua = FALSE;
        $tipoMensaje = 'alert alert-danger';
        $mensaje = "Falta fechaEspecialista.";
    }
    if (isset($_POST['fechaVencimiento']) && $_POST['fechaVencimiento'] <> "") {
        $fechaVencimiento = $_POST['fechaVencimiento'];
    } else {
        $fechaVencimiento = NULL;
    }
    if (isset($_POST['distritoOtorgante']) && $_POST['distritoOtorgante'] <> "") {
        $distritoOtorgante = $_POST['distritoOtorgante'];
    } else {
        $continua = FALSE;
        $tipoMensaje = 'alert alert-danger';
        $mensaje = "Falta distritoOrigen.";
    }
*/
    if (isset($_POST['idEspecialidad']) && $_POST['idEspecialidad'] <> "") {
        $idEspecialidad = $_POST['idEspecialidad'];
    } else {
        $continua = FALSE;
        $tipoMensaje = 'alert alert-danger';
        $mensaje = "Falta idEspecialidad.";
    }
    
}
if ($accion <> 2) {
    if (isset($_POST['fechaRecertificacion']) && $_POST['fechaRecertificacion'] <> "") {
        $fechaRecertificacion = $_POST['fechaRecertificacion'];
    } else {
        $fechaRecertificacion = NULL;
    }
    if (isset($_POST['idTipoEspecialista']) && $_POST['idTipoEspecialista'] <> "") {
        $idTipoEspecialista = $_POST['idTipoEspecialista'];
    } else {
        $continua = FALSE;
        $tipoMensaje = 'alert alert-danger';
        $mensaje = "Falta idTipoEspecialista.";
    }
    if (isset($_POST['inciso']) && $_POST['inciso'] <> "") {
        $inciso = $_POST['inciso'];
    } else {
        $inciso = NULL;
    }
    if (isset($_POST['fechaEspecialista']) && $_POST['fechaEspecialista'] <> "") {
        $fechaEspecialista = $_POST['fechaEspecialista'];
    } else {
        $continua = FALSE;
        $tipoMensaje = 'alert alert-danger';
        $mensaje = "Falta fechaEspecialista.";
    }
    if (isset($_POST['fechaVencimiento']) && $_POST['fechaVencimiento'] <> "") {
        $fechaVencimiento = $_POST['fechaVencimiento'];
    } else {
        $fechaVencimiento = NULL;
    }
    if (isset($_POST['distritoOtorgante']) && $_POST['distritoOtorgante'] <> "") {
        $distritoOtorgante = $_POST['distritoOtorgante'];
    } else {
        $continua = FALSE;
        $tipoMensaje = 'alert alert-danger';
        $mensaje = "Falta distritoOrigen.";
    }
    $idResolucionDetalle = NULL;
}
if ($continua) {
    if (isset($idColegiadoEspecialista)) {
        //obtengo el registro anterior
        $resEspecialista = $colegiadoEspecialistaLogic->obtenerColegiadoEspecialistaPorId($idColegiadoEspecialista);
        if ($resEspecialista['estado']) {
            $datosAnteriores = $resEspecialista['datos'];
        } else {
            $datosAnteriores = NULL;
        }
    } else {
        $datosAnteriores = NULL;
    }
    switch ($accion) 
    {
        case '1':
            $resultado = $colegiadoEspecialistaLogic->guardarColegiadoEspecialista($idColegiadoEspecialista, $idColegiado, $idEspecialidad, $idTipoEspecialista, $fechaEspecialista, $fechaRecertificacion, $fechaVencimiento, $inciso, $distritoOtorgante, $idResolucionDetalle, $datosAnteriores);
            //$resultado = $colegiadoEspecialistaLogic->agregarColegiadoEspecialista($idColegiado, $idEspecialidad, $idTipoEspecialista, $fechaEspecialista, $fechaRecertificacion, $fechaVencimiento, $inciso, $distritoOtorgante, $idResolucionDetalle);
            break;
        case '3':
            $resultado = $colegiadoEspecialistaLogic->guardarColegiadoEspecialista($idColegiadoEspecialista, $idColegiado, $idEspecialidad, $idTipoEspecialista, $fechaEspecialista, $fechaRecertificacion, $fechaVencimiento, $inciso, $distritoOtorgante, $idResolucionDetalle, $datosAnteriores);
            //$resultado = $colegiadoEspecialistaLogic->editarColegiadoEspecialista($idColegiadoEspecialista, $idTipoEspecialista, $inciso, $fechaVencimiento, $fechaRecertificacion);
            break;
        case '2':
            $resultado = $colegiadoEspecialistaLogic->borrarColegiadoEspecialista($idColegiadoEspecialista);
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
        <form name="myForm"  method="POST" action="../colegiado_especialista.php?idColegiado=<?php echo $idColegiado; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../colegiado_especialista_form.php?id=<?php echo $idColegiadoEspecialista; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
            <input type="hidden"  name="inciso" id="inciso" value="<?php echo $inciso;?>">
            <input type="hidden"  name="accion" id="accion" value="<?php echo $accion;?>">
        </form>
    <?php
    }
    ?>
</body>

