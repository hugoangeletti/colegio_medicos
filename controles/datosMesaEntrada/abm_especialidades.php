<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/mesaEntradaEspecialistaLogic.php');
$mesaEntradaEspecialistaLogic = new mesaEntradaEspecialistaLogic();
require_once ('../../dataAccess/tipoPagoLogic.php');
require_once ('../../dataAccess/resolucionesLogic.php');
$resolucionesLogic = new resolucionesLogic();

$accion = $_GET['accion'];
$continua = TRUE;
$mensaje = "";
if (isset($_POST['tipoEspecialista']) && $_POST['tipoEspecialista'] <> "") {
    //da de alta la nueva solicitud de especialista
    $tipoEspecialista = $_POST['tipoEspecialista'];
    if ($accion <> 1) {
        if (isset($_POST['id']) && $_POST['id'] <> "") {
            $idMesaEntradaEspecialidad = $_POST['id'];
        } else {
            $continua = FALSE;
            $mensaje = "FALTA SELECCIONAR IDMESAENTRADAESPECIALIDAD";
        }
    } 
    if (isset($_POST['idTipoEspecialista']) && $_POST['idTipoEspecialista']) {
        $idTipoEspecialista = $_POST['idTipoEspecialista'];
    } else {
        $resTipoEspecialista = $resolucionesLogic->obtenerTipoEspecialistaPorCodigo($tipoEspecialista);
        if ($resTipoEspecialista['estado']) {
            $idTipoEspecialista = $resTipoEspecialista['datos']['id'];
        } else {
            $mensaje .= 'Falta idTipoEspecialista - ';
            $continua = FALSE;
        }
    }
    $idColegiado = $_POST['idColegiado'];
    $idEstadoMatricular = $_POST['idEstadoMatricular'];
    $estadoTesoreria = $_POST['estadoTesoreria'];
    if ($tipoEspecialista == "X" && (!isset($_POST['inciso']) || $_POST['inciso'] == "")) {
        $continua = FALSE;
        $mensaje = "FALTA SELECCIONAR INCISO";
    } else {
        $inciso = $_POST['inciso'];
    }
    if ($tipoEspecialista == 'A') {
        if (!isset($_POST['especialidadCalificacion']) || $_POST['especialidadCalificacion'] == "") {
            $continua = FALSE;
            $mensaje = "FALTA SELECCIONAR CALIFICACION AGREGADA";
        } else {
            $idEspecialidad = $_POST['especialidadCalificacion'];
        }
    } else {
        if (!isset($_POST['especialidad']) || $_POST['especialidad'] == "") {
            $continua = FALSE;
            $mensaje = "FALTA SELECCIONAR ESPECIALIDAD";
        } else {
            $idEspecialidad = $_POST['especialidad'];
        }
    }
    if ($tipoEspecialista == "O" && (!isset($_POST['distrito']) || $_POST['distrito'] == "")) {
        $continua = FALSE;
        $mensaje = "FALTA SELECCIONAR DISTRITO ORIGEN";
    } else {
        $distrito = $_POST['distrito'];
    }
} else {
    $continua = FALSE;
    $tipoMensaje = 'alert alert-danger';
    $mensaje = "Faltan datos, verifique.";
}

if ($continua) {
    switch ($accion) {
        case 1:
            //continuar con el alta                                
            $resultado = $mesaEntradaEspecialistaLogic->realizarAltaMesaEntrada($idColegiado, $tipoEspecialista, $idEspecialidad, $idEstadoMatricular, $estadoTesoreria, $distrito, $inciso, $idTipoEspecialista);
            if ($resultado['estado']) {
                $expediente = $resultado['datos'];
                $numeroExpediente = $expediente['numeroExpediente'];
                $anioExpediente = $expediente['anioExpediente'];
                $idMesaEntradaEspecialidad = $expediente['idMesaEntradaEspecialidad'];
            } else {
                $continua = FALSE;
            }
            break;

        case 3:
            //continuar con modificacion                               
            $resultado = $mesaEntradaEspecialistaLogic->realizarModificacionMesaEntrada($idMesaEntradaEspecialidad, $tipoEspecialista, $idEspecialidad, $distrito, $inciso, $idTipoEspecialista);
            if (!$resultado['estado']) {
                $continua = FALSE;
            }
            break;

        default:
            $continua = FALSE;
            break;
    }

    if($resultado['estado']) {
        $tipoMensaje = 'alert alert-success';
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
    ?>        
        <form name="myForm"  method="POST" action="../especialidades_expedientes_nuevo.php?idColegiado=<?php echo $idColegiado; ?>.&id=<?php echo $idMesaEntradaEspecialidad; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
            <input type="hidden"  name="estadoSancion" id="estadoSancion" value="<?php echo $estadoSancion;?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../especialidades_expedientes_alta.php?accion=<?php echo $accion; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
            <input type="hidden"  name="observaciones" id="observaciones" value="<?php echo $observaciones;?>">
            <input type="hidden"  name="accion" id="accion" value="<?php echo $accion;?>">
        </form>
    <?php
    }
    ?>
</body>

