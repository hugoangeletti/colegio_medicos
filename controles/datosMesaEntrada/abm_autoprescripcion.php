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
    if (isset($_POST['observaciones']) && $_POST['observaciones'] <> "") {
        $observaciones = $_POST['observaciones'];
    } else {
        $observaciones = NULL;
    }
    if (isset($_POST['fecha']) && $_POST['fecha'] <> "") {
        $fecha = $_POST['fecha'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta fecha - ";
    }
    if (isset($_POST['autorizado1']) && $_POST['autorizado1'] <> "") {
        $autorizado1 = $_POST['autorizado1'];
        if (isset($_POST['documentoAutorizado1']) && $_POST['documentoAutorizado1'] <> "") {
            $documentoAutorizado1 = $_POST['documentoAutorizado1'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta documentoAutorizado1 - ";
        }
        if (isset($_POST['parentescoAutorizado1']) && $_POST['parentescoAutorizado1'] <> "") {
            $parentescoAutorizado1 = $_POST['parentescoAutorizado1'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta parentescoAutorizado1 - ";
        }
    } else {
        $autorizado1 = NULL;
        $documentoAutorizado1 = NULL;
        $parentescoAutorizado1 = NULL;
    }
    if (isset($_POST['autorizado2']) && $_POST['autorizado2'] <> "") {
        $autorizado2 = $_POST['autorizado2'];
        if (isset($_POST['documentoAutorizado2']) && $_POST['documentoAutorizado2'] <> "") {
            $documentoAutorizado2 = $_POST['documentoAutorizado2'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta documentoAutorizado2 - ";
        }
        if (isset($_POST['parentescoAutorizado2']) && $_POST['parentescoAutorizado2'] <> "") {
            $parentescoAutorizado2 = $_POST['parentescoAutorizado2'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta parentescoAutorizado2 - ";
        }
    } else {
        $autorizado2 = NULL;
        $documentoAutorizado2 = NULL;
        $parentescoAutorizado2 = NULL;
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
            $idTipoMesaEntrada = 7;
            $tipoRemitente = 'C'; //colegiado
            $idRemitente = NULL;
            $datosTipoMesaEntrada = array(
                                    'fecha' => $fecha,
                                    'autorizado1' => $autorizado1,
                                    'documentoAutorizado1' => $documentoAutorizado1,
                                    'parentescoAutorizado1' => $parentescoAutorizado1,
                                    'autorizado2' => $autorizado2,
                                    'documentoAutorizado2' => $documentoAutorizado2,
                                    'parentescoAutorizado2' => $parentescoAutorizado2
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
        <form name="myForm"  method="POST" action="../mesa_entrada_autoprescripcion.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase'];?>">
            <input type="hidden"  name="idColegiado" id="idColegiado" value="<?php echo $idColegiado;?>">
            <input type="hidden"  name="colegiado_buscar" id="colegiado_buscar" value="<?php echo $colegiado_buscar;?>">
            <input type="hidden"  name="observaciones" id="observaciones" value="<?php echo $observaciones;?>">
            <input type="hidden"  name="fecha" id="fecha" value="<?php echo $fecha;?>">
            <input type="hidden"  name="autorizado1" id="autorizado1" value="<?php echo $autorizado1;?>">
            <input type="hidden"  name="documentoAutorizado1" id="documentoAutorizado1" value="<?php echo $documentoAutorizado1;?>">
            <input type="hidden"  name="parentescoAutorizado1" id="parentescoAutorizado1" value="<?php echo $parentescoAutorizado1;?>">
            <input type="hidden"  name="autorizado2" id="autorizado2" value="<?php echo $autorizado2;?>">
            <input type="hidden"  name="documentoAutorizado2" id="documentoAutorizado2" value="<?php echo $documentoAutorizado2;?>">
            <input type="hidden"  name="parentescoAutorizado2" id="parentescoAutorizado2" value="<?php echo $parentescoAutorizado2;?>">
        </form>
    <?php
    }
    ?>
</body>

