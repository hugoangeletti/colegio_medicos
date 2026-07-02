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
        $continua = FALSE;
        $mensaje .= "Falta observaciones - ";
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
            $idTipoMesaEntrada = 5;
            $tipoRemitente = 'C'; //colegiado
            $idRemitente = NULL;
            $datosTipoMesaEntrada = array();
            $resultado = $mesaEntradaLogic->agregarMesaEntrada($idTipoMesaEntrada, $tipoRemitente, $idColegiado, $idRemitente, $estadoMatricular, $estadoTesoreria, $observaciones, $datosTipoMesaEntrada);
            //$resultado = $mesaEntradaLogic->agregarMesaEntradaMatriculaJ($idColegiado, $idTipoMesaEntrada, $observaciones, $estadoMatricular, $estadoTesoreria);
            break;

        case "BORRAR":
            //continuar con el alta       
            $resultado = $mesaEntradaLogic->borrarMesaEntradaMatriculaJ($idMesaEntrada);
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
        <form name="myForm"  method="POST" action="../mesa_entrada_matricula_j.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase'];?>">
            <input type="hidden"  name="idColegiado" id="idColegiado" value="<?php echo $idColegiado;?>">
            <input type="hidden"  name="colegiado_buscar" id="colegiado_buscar" value="<?php echo $colegiado_buscar;?>">
            <input type="hidden"  name="observaciones" id="observaciones" value="<?php echo $observaciones;?>">
        </form>
    <?php
    }
    ?>
</body>

