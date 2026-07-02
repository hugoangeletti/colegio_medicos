<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/ordenDelDiaLogic.php');
$ordenDelDiaLogic = new ordenDelDiaLogic();


$idOrdenDia = NULL;
$continua = TRUE;
$mensaje = "";
if (isset($_POST['accion']) && $_POST['accion'] <> "") {
	$accion = $_POST['accion'];
} else {
    if (isset($_GET['accion']) && $_GET['accion'] <> "") {
        $accion = $_GET['accion'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta accion. ";
    }
}
if ($accion == 2) {
    if (isset($_GET['id']) && $_GET['id'] <> "") {
        $idOrdenDia = $_GET['id'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta idOrdenDia. ";
    }
} else {
    if ($accion == 4) {
        if (isset($_POST['idOrdenDia']) && $_POST['idOrdenDia']) {
            $idOrdenDia = $_POST['idOrdenDia'];
            if (isset($_POST['tipoPlanillaOrigen']) && $_POST['tipoPlanillaOrigen']) {
                $tipoPlanillaOrigen = $_POST['tipoPlanillaOrigen'];
            } else {
                $continua = FALSE;
                $mensaje .= "Falta tipoPlanilla. ";
            }
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idOrdenDiaDetalle. ";
        }
    } else {
        $continua = FALSE;
        $mensaje .= "Accion erronea. ";
    }
}

if ($continua) {
    switch ($accion) {
        case '2':
            $resultado = $ordenDelDiaLogic->borrarDetallePorIdOrdenDia($idOrdenDia);
            $linkDerivar = "../orden_del_dia_listado.php";
            break;
        
        case '4':
            //verificamos si deriva
            if (isset($_POST['deriva1']) && !empty($_POST['deriva1'])) {
                $derivar = $_POST['deriva1'];
                foreach ($derivar as $idOrdenDiaDetalle) {
                    $resultado = $ordenDelDiaLogic->asignarTipoPlanillaAlDetalle($idOrdenDiaDetalle, 1);
                    if (!$resultado['estado']) { break; }
                }
            }
            if (isset($_POST['deriva2']) && !empty($_POST['deriva2'])) {
                $derivar = $_POST['deriva2'];
                foreach ($derivar as $idOrdenDiaDetalle) {
                    $resultado = $ordenDelDiaLogic->asignarTipoPlanillaAlDetalle($idOrdenDiaDetalle, 2);
                    if (!$resultado['estado']) { break; }
                }
            }
            if (isset($_POST['deriva3']) && !empty($_POST['deriva3'])) {
                $derivar = $_POST['deriva3'];
                foreach ($derivar as $idOrdenDiaDetalle) {
                    $resultado = $ordenDelDiaLogic->asignarTipoPlanillaAlDetalle($idOrdenDiaDetalle, 3);
                    if (!$resultado['estado']) { break; }
                }
            }
            if (isset($_POST['deriva4']) && !empty($_POST['deriva4'])) {
                $derivar = $_POST['deriva4'];
                foreach ($derivar as $idOrdenDiaDetalle) {
                    $resultado = $ordenDelDiaLogic->asignarTipoPlanillaAlDetalle($idOrdenDiaDetalle, 4);
                    if (!$resultado['estado']) { break; }
                }
            }
            if (isset($_POST['deriva5']) && !empty($_POST['deriva5'])) {
                $derivar = $_POST['deriva5'];
                foreach ($derivar as $idOrdenDiaDetalle) {
                    $resultado = $ordenDelDiaLogic->asignarTipoPlanillaAlDetalle($idOrdenDiaDetalle, 5);
                    if (!$resultado['estado']) { break; }
                }
            }
            //exit;
            /*
            if ($tipoPlanilla <> 5) {
                $resultado = $ordenDelDiaLogic->asignarTipoPlanillaAlDetalle($idOrdenDiaDetalle, $tipoPlanilla);
            } else {
                $resultado = desasignarTipoPlanillaAlDetalle($idOrdenDiaDetalle);
            }
            */
            $linkDerivar = "../orden_del_dia_detalle.php?id=".$idOrdenDia;
            break;
        
        default:
            // code...
            break;
    }
}
/*
var_dump($_POST);
echo '<br>';
var_dump($resultado);
exit;
*/
?>
<body onLoad="document.forms['myForm'].submit()">
    <form name="myForm"  method="POST" action="<?php echo $linkDerivar; ?>">
        <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
        <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
        <input type="hidden"  name="tipoPlanilla" id="tipoPlanilla" value="<?php echo $tipoPlanillaOrigen;?>">
    </form>
</body>

