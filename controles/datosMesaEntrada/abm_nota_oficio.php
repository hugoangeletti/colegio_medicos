<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/colegiadoLogic.php');
require_once ('../../dataAccess/mesaEntradaLogic.php');

$continua = TRUE;
$mensaje = "";
if (isset($_POST['accion']) && $_POST['accion'] <> "") {
    $accion = $_POST['accion'];
} else {
    $continua = FALSE;
    $mensaje .= "Falta accion - ";
}
if (isset($_POST['idMesaEntradaNota']) && $_POST['idMesaEntradaNota'] <> "") {
    //da de alta la nueva solicitud de especialista
    $idMesaEntradaNota = $_POST['idMesaEntradaNota'];
} else {
    if ($accion == "AGREGAR") {
        $idMesaEntradaNota = NULL;
    } else {
        $continua = FALSE;
        $mensaje = "Falta idMesaEntradaNota - ";
    }
} 
$accedePor = NULL;
if (isset($_GET['ingreso']) && $_GET['ingreso'] <> ""){
    $accedePor = $_GET['ingreso'];
}

$estadoTesoreria = NULL;
$estadoMatricular = NULL;
$incluyeMovimiento = NULL;
if (isset($_POST['esColegiado']) && $_POST['esColegiado']) {
    $esColegiado = $_POST['esColegiado'];
    if ($esColegiado == "S") {
        $tipoRemitente = "C";
        if (isset($_POST['idColegiado']) && $_POST['idColegiado'] <> "") {
            $idColegiado = $_POST['idColegiado'];
            $colegiadoLogic = new colegiadoLogic();
            $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
            if ($resColegiado['estado']) {
                $colegiado = $resColegiado['datos'];
                $estadoMatricular = $colegiado['idEstadoMatricular'];
            }
            $idRemitente = NULL;
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idColegiado - ";
        }
        if (isset($_POST['colegiado_buscar']) && $_POST['colegiado_buscar'] <> "") {
            $colegiado_buscar = $_POST['colegiado_buscar'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta colegiado_buscar - ";
        }
    } else {
        if ($esColegiado == "N") {
            $tipoRemitente = "O";
            if (isset($_POST['idRemitente']) && $_POST['idRemitente'] <> "") {
                $idRemitente = $_POST['idRemitente'];
                $idColegiado = NULL;

                $arrayRemitentes = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11');
                if (in_array($idRemitente, $arrayRemitentes)) {
                    if (isset($_POST['incluyeMovimiento']) && $_POST['incluyeMovimiento'] <> "") {
                        $incluyeMovimiento = $_POST['incluyeMovimiento'];
                    } else {
                        $continua = FALSE;
                        $mensaje .= "Falta incluyeMovimiento - ";
                    }
                }
            } else {
                $continua = FALSE;
                $mensaje .= "Falta idRemitente - ";
            }
            if (isset($_POST['remitente_buscar']) && $_POST['remitente_buscar'] <> "") {
                $remitente_buscar = $_POST['remitente_buscar'];
            } else {
                $continua = FALSE;
                $mensaje .= "Falta remitente_buscar - ";
            }
        } else {
            $continua = FALSE;
            $mensaje .= "Falta seleccionar tipo de remitente - ";
        }
    }
} else {
    $continua = FALSE;
    $mensaje .= "Falta seleccionar tipo de remitente - ";
}

if (isset($_POST['tema']) && $_POST['tema'] <> "") {
    $tema = $_POST['tema'];
} else {
    $continua = FALSE;
    $mensaje = "Falta tema - ";
}
if (isset($_POST['observaciones']) && $_POST['observaciones'] <> "") {
    $observaciones = $_POST['observaciones'];
} else {
    $observaciones = NULL;
}

if ($continua) {
    $mesaEntradaLogic = new mesaEntradaLogic();
    switch ($accion) {
        case "AGREGAR":
            //continuar con el alta       
            $idTipoMesaEntrada = 3; //NOTAS
            $datosTipoMesaEntrada = array(
                                    'tema' => $tema, 
                                    'incluyeMovimiento' => $incluyeMovimiento
                                );
            $resultado = $mesaEntradaLogic->agregarMesaEntrada($idTipoMesaEntrada, $tipoRemitente, $idColegiado, $idRemitente, $estadoMatricular, $estadoTesoreria, $observaciones, $datosTipoMesaEntrada);
            break;

        case "MODIFICAR":
            //continuar con modificacion   
            $resNota = $mesaEntradaLogic->obtenerMesaEntradaNotaPorId($idMesaEntradaNota, NULL);
            if ($resNota['estado']) {
                $datosAnteriores = $resNota['datos'];
                $idMesaEntrada = $datosAnteriores['idMesaEntrada'];
                $resMesaEntrada = $mesaEntradaLogic->obtenerMesaEntradaPorId($idMesaEntrada);
                if ($resMesaEntrada['estado']) {
                    $mesaEntrada = $resMesaEntrada['datos'];
                    $datosAnteriores['observaciones'] = $mesaEntrada['observaciones'];
                }
            } else {
              $datosAnteriores = NULL;
            }                            
            $resultado = $mesaEntradaLogic->modificarMesaEntradaNota($idMesaEntradaNota, $tema, $incluyeMovimiento, $observaciones, $datosAnteriores);
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
        if (!isset($idMesaEntrada) || $idMesaEntrada == "") {
            $idMesaEntrada = $resultado['idMesaEntrada'];
        }
    ?>        
        <form name="myForm"  method="POST" action="../mesa_entrada_imprimir.php?id=<?php echo $idMesaEntrada; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase'];?>">
        </form>
    <?php
    } else {
        if ($accion == 'MODIFICAR') {
            $link = '../mesa_entrada_ver.php??id='.$idMesaEntrada.'&ingreso='.$accedePor;
        } else {
            $link = '../mesa_entrada_notas_oficios.php';
        }
        ?>
        <form name="myForm"  method="POST" action="<?php echo $link; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase'];?>">
            <input type="hidden"  name="esColegiado" id="esColegiado" value="<?php echo $esColegiado;?>">
            <?php 
            if ($esColegiado == "S") {
            ?>
                <input type="hidden"  name="idColegiado" id="idColegiado" value="<?php echo $idColegiado;?>">
                <input type="hidden"  name="colegiado_buscar" id="colegiado_buscar" value="<?php echo $colegiado_buscar;?>">
            <?php 
            } else { 
            ?>
                <input type="hidden"  name="idRemitente" id="idRemitente" value="<?php echo $idRemitente;?>">
                <input type="hidden"  name="remitente_buscar" id="remitente_buscar" value="<?php echo $remitente_buscar;?>">
            <?php 
            }
            ?>
            <input type="hidden"  name="tema" id="tema" value="<?php echo $tema;?>">
            <input type="hidden"  name="incluyeMovimiento" id="incluyeMovimiento" value="<?php echo $incluyeMovimiento;?>">
            <input type="hidden"  name="observaciones" id="observaciones" value="<?php echo $observaciones;?>">
            <input type="hidden"  name="incluyeMovimiento" id="incluyeMovimiento" value="<?php echo $incluyeMovimiento;?>">
            <input type="hidden"  name="accion" id="accion" value="<?php echo $accion;?>">
        </form>
    <?php
    }
    ?>
</body>

