<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/mesaEntradaLogic.php');

$continua = TRUE;
$mensaje = "";
if (isset($_POST['idColegiado']) && $_POST['idColegiado'] <> "") {
    $idColegiado = $_POST['idColegiado'];
} else {
    $continua = FALSE;
    $mensaje = "Falta idColegiado - ";
} 
if (isset($_POST['estadoMatricular']) && $_POST['estadoMatricular'] <> "") {
    $estadoMatricular = $_POST['estadoMatricular'];
} else {
    $continua = FALSE;
    $mensaje = "Falta estadoMatricular - ";
} 
if (isset($_POST['codigoDeudor']) && $_POST['codigoDeudor'] <> "") {
    $estadoTesoreria = $_POST['codigoDeudor'];
} else {
    $continua = FALSE;
    $mensaje = "Falta estadoTesoreria - ";
} 
if (isset($_POST['observaciones']) && $_POST['observaciones'] <> "") {
    $observaciones = $_POST['observaciones'];
} else {
    $observaciones = NULL;
}
if (isset($_GET['agregar'])) {
    $accion = "AGREGAR";
    if (isset($_POST['idTipoMovimiento']) && $_POST['idTipoMovimiento'] <> "") {
        $idTipoMovimiento = $_POST['idTipoMovimiento'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta idTipoMovimiento - ";
    }
    if (isset($_POST['fechaMovimiento']) && $_POST['fechaMovimiento'] <> "") {
        $fechaMovimiento = $_POST['fechaMovimiento'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta fechaMovimiento - ";
    }
    if (isset($_POST['idMotivoCancelacion']) && $_POST['idMotivoCancelacion'] <> "") {
        $idMotivoCancelacion = $_POST['idMotivoCancelacion'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta idMotivoCancelacion - ";
    }
    if (isset($_POST['distrito']) && $_POST['distrito'] <> "") {
        $distrito = $_POST['distrito'];
    } else {
        $aMotivoCancelacion = array(7, 8, 11);
        if (in_array($idMotivoCancelacion, $aMotivoCancelacion)) {
            $continua = FALSE;
            $mensaje .= "Falta distrito - ";
        } else {
            $distrito = NULL;
        }
    }
} else {
    if (isset($_GET['borrar'])) {
        $accion = "BORRAR";
        if (isset($_GET['id']) && $_GET['id'] <> "") {
            $idMesaEntrada = $_GET['id'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idMesaEntrada - ";
        }
    } else {
        if (isset($_GET['anular'])) {
            $accion = "ANULAR";
            if (isset($_GET['id']) && $_GET['id'] <> "") {
                $idMesaEntrada = $_GET['id'];
            } else {
                $continua = FALSE;
                $mensaje .= "Falta idMesaEntrada - ";
            }
        } else {
            $continua = FALSE;
            $mensaje .= "Falta accion - ";
        }
    }
}
if ($continua) {
    $mesaEntradaLogic = new mesaEntradaLogic();
    switch ($accion) {
        case "AGREGAR":
            //continuar con el alta       
            $idTipoMesaEntrada = 1;
            $tipoRemitente = 'C'; //colegiado
            $idRemitente = NULL;
            $datosTipoMesaEntrada = array(
                                        'idTipoMovimiento' => $idTipoMovimiento,
                                        'fechaMovimiento' => $fechaMovimiento,
                                        'idMotivoCancelacion' => $idMotivoCancelacion,
                                        'distrito' => $distrito
                                    );
            $resultado = $mesaEntradaLogic->agregarMesaEntrada($idTipoMesaEntrada, $tipoRemitente, $idColegiado, $idRemitente, $estadoMatricular, $estadoTesoreria, $observaciones, $datosTipoMesaEntrada);
            break;

        case "ANULAR":
            //anula movimiento
            $idTipoMesaEntrada = 8;
            $tipoRemitente = 'C'; //colegiado
            $idRemitente = NULL;
            $datosTipoMesaEntrada = array(
                                    'idMesaEntradaAnular' => $idMesaEntrada
                                    );
            $resultado = $mesaEntradaLogic->agregarMesaEntrada($idTipoMesaEntrada, $tipoRemitente, $idColegiado, $idRemitente, $estadoMatricular, $estadoTesoreria, $observaciones, $datosTipoMesaEntrada);
            break;

        case "BORRAR":
            //borrar       
            $resultado = $mesaEntradaLogic->borrarMesaEntradaAutoprescripcion($idMesaEntrada);
            break;

        default:
            $continua = FALSE;
            break;
    }
} else {
    $resultado['mensaje'] = $mensaje;
    $resultado['clase'] = 'alert alert-danger';
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
        $idMesaEntrada = $resultado['idMesaEntrada'];
        ?>
        <form name="myForm"  method="POST" action="../mesa_entrada_imprimir.php?id=<?php echo $idMesaEntrada; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase'];?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../mesa_entrada_movimientos_matriculares.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase'];?>">
            <input type="hidden"  name="idColegiado" id="idColegiado" value="<?php echo $idColegiado;?>">
            <input type="hidden"  name="colegiado_buscar" id="colegiado_buscar" value="<?php echo $colegiado_buscar;?>">
            <input type="hidden"  name="observaciones" id="observaciones" value="<?php echo $observaciones;?>">
            <input type="hidden"  name="fechaMovimiento" id="fechaMovimiento" value="<?php echo $fechaMovimiento;?>">
            <input type="hidden"  name="idTipoMovimiento" id="idTipoMovimiento" value="<?php echo $idTipoMovimiento;?>">
            <input type="hidden"  name="idMotivoCancelacion" id="idMotivoCancelacion" value="<?php echo $idMotivoCancelacion;?>">
            <input type="hidden"  name="distrito" id="distrito" value="<?php echo $distrito;?>">
            <input type="hidden"  name="estadoMatricular" id="estadoMatricular" value="<?php echo $estadoMatricular;?>">
            <input type="hidden"  name="estadoTesoreria" id="estadoTesoreria" value="<?php echo $estadoTesoreria;?>">
        </form>
    <?php
    }
    ?>
</body>

