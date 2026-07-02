<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/mesaEntradaLogic.php');

$continua = TRUE;
$mensaje = "";
if (isset($_GET['agregar'])) {
    $accion = "AGREGAR";
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
    if (isset($_POST['fechaDenuncia']) && $_POST['fechaDenuncia'] <> "") {
        $fechaDenuncia = $_POST['fechaDenuncia'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta fechaDenuncia - ";
    }
    if (isset($_POST['fechaExtravio']) && $_POST['fechaExtravio'] <> "") {
        $fechaExtravio = $_POST['fechaExtravio'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta fechaExtravio - ";
    }
    if (isset($_POST['idTipoDenuncia']) && $_POST['idTipoDenuncia'] <> "") {
        $idTipoDenuncia = $_POST['idTipoDenuncia'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta idTipoDenuncia - ";
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
        $continua = FALSE;
        $mensaje .= "Falta accion - ";
    }
}
if ($continua) {
    $mesaEntradaLogic = new mesaEntradaLogic();
    switch ($accion) {
        case "AGREGAR":
            //continuar con el alta       
            $idTipoMesaEntrada = 9;
            $tipoRemitente = 'C'; //colegiado
            $idRemitente = NULL;
            $datosTipoMesaEntrada = array(
                                    'idTipoDenuncia' => $idTipoDenuncia,
                                    'fechaDenuncia' => $fechaDenuncia,
                                    'fechaExtravio' => $fechaExtravio
                                );
            $resultado = $mesaEntradaLogic->agregarMesaEntrada($idTipoMesaEntrada, $tipoRemitente, $idColegiado, $idRemitente, $estadoMatricular, $estadoTesoreria, $observaciones, $datosTipoMesaEntrada);

            //$resultado = $mesaEntradaLogic->agregarMesaEntradaDenuncia($idColegiado, $idTipoMesaEntrada, $observaciones, $estadoMatricular, $estadoTesoreria, $idTipoDenuncia, $fechaDenuncia, $fechaExtravio);
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
        if ($accion == "BORRAR") {
        ?>
            <form name="myForm"  method="POST" action="../mesa_entrada_listado.php">
                <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
                <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase'];?>">
            </form>
        <?php 
        } else {
            $idMesaEntrada = $resultado['idMesaEntrada'];
            ?>
            <form name="myForm"  method="POST" action="../mesa_entrada_imprimir.php?id=<?php echo $idMesaEntrada; ?>">
                <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
                <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase'];?>">
            </form>
    <?php
        }
    } else {
    ?>
        <form name="myForm"  method="POST" action="../mesa_entrada_denuncia.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase'];?>">
            <input type="hidden"  name="idColegiado" id="idColegiado" value="<?php echo $idColegiado;?>">
            <input type="hidden"  name="colegiado_buscar" id="colegiado_buscar" value="<?php echo $colegiado_buscar;?>">
            <input type="hidden"  name="observaciones" id="observaciones" value="<?php echo $observaciones;?>">
            <input type="hidden"  name="fechaDenuncia" id="fechaDenuncia" value="<?php echo $fechaDenuncia;?>">
            <input type="hidden"  name="fechaExtravio" id="fechaExtravio" value="<?php echo $fechaExtravio;?>">
            <input type="hidden"  name="idTipoDenuncia" id="idTipoDenuncia" value="<?php echo $idTipoDenuncia;?>">
            <input type="hidden"  name="estadoMatricular" id="estadoMatricular" value="<?php echo $estadoMatricular;?>">
            <input type="hidden"  name="estadoTesoreria" id="estadoTesoreria" value="<?php echo $estadoTesoreria;?>">
        </form>
    <?php
    }
    ?>
</body>

