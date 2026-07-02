<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/colegiadoEspecialistaLogic.php');
$colegiadoEspecialistaLogic = new colegiadoEspecialistaLogic();

$continua = TRUE;
$mensaje = "";

if (isset($_GET['accion']) && $_GET['accion'] <> "") {
    $accion = $_GET['accion'];
    $ingreso = "GET";
} else {
    if (isset($_POST['accion']) && $_POST['accion'] <> "") {
        $accion = $_POST['accion'];
        $ingreso = "POST";
    } else {
        $continua = FALSE;
        $mensaje .= "Falta accion - ";
    }
}

switch ($ingreso) {
    case 'GET':
        if (isset($_GET['id']) && $_GET['id'] <> "") {
            $idColegiadoEspecialistaTipo = $_GET['id'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta accion - ";
        }
        if (isset($_GET['idColegiado']) && $_GET['idColegiado'] <> "") {
            $idColegiado = $_GET['idColegiado'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta accion - ";
        }
        break;
    
    case 'POST':
        if (isset($_POST['idColegiadoEspecialistaTipo']) && $_POST['idColegiadoEspecialistaTipo'] <> "") {
            $idColegiadoEspecialistaTipo = $_POST['idColegiadoEspecialistaTipo'];
        } else {
            $idColegiadoEspecialistaTipo = NULL;
            $accion = 1;
        }
        if (isset($_POST['idColegiadoEspecialista']) && $_POST['idColegiadoEspecialista'] <> "") {
            $idColegiadoEspecialista = $_POST['idColegiadoEspecialista'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idColegiadoEspecialista - ";
        }
        if (isset($_POST['idColegiado']) && $_POST['idColegiado'] <> "") {
            $idColegiado = $_POST['idColegiado'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idColegiado - ";
            $tipoMensaje = 'alert alert-danger';
        }
        if (isset($_POST['idTipoEspecialista']) && $_POST['idTipoEspecialista'] <> "") {
            $idTipoEspecialista = $_POST['idTipoEspecialista'];
        } else {
            $continua = FALSE;
            $tipoMensaje = 'alert alert-danger';
            $mensaje = "Falta idTipoEspecialista - ";
        }
        if (isset($_POST['fecha']) && $_POST['fecha'] <> "") {
            $fecha = $_POST['fecha'];
        } else {
            $continua = FALSE;
            $tipoMensaje = 'alert alert-danger';
            $mensaje = "Falta fecha - ";
        }
        if (isset($_POST['distritoOtorgante']) && $_POST['distritoOtorgante'] <> "") {
            $distritoOtorgante = $_POST['distritoOtorgante'];
        } else {
            $continua = FALSE;
            $tipoMensaje = 'alert alert-danger';
            $mensaje = "Falta distritoOtorgante - ";
        }

        break;

    default:
        // code...
        break;
}

if ($continua) {
    switch ($accion) 
    {
        case '1':
            $resultado = $colegiadoEspecialistaLogic->guardarColegiadoEspecialistaTipo($idColegiadoEspecialistaTipo, $idColegiadoEspecialista, $fecha, $idTipoEspecialista, $distritoOtorgante);
            break;
        case '3':
            $resultado = $colegiadoEspecialistaLogic->guardarColegiadoEspecialistaTipo($idColegiadoEspecialistaTipo, $idColegiadoEspecialista, $fecha, $idTipoEspecialista, $distritoOtorgante);
            break;
        case '2':
            $resultado = $colegiadoEspecialistaLogic->borrarColegiadoEspecialistaTipo($idColegiadoEspecialistaTipo);
            break;
        default:
            break;
    }
} else {
    $resultado['clase'] = $tipoMensaje;
    $resultado['mensaje'] = $mensaje;
    $resultado['icono'] = "";
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

