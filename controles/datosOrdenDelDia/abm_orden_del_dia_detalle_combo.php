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
if (isset($_GET['accion']) && $_GET['accion'] <> "") {
	$accion = $_GET['accion'];
} else {
    $continua = FALSE;
    $mensaje .= "Falta accion. ";
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
        if (isset($_GET['id']) && $_GET['id']) {
            $idGet = explode('_', $_GET['id']);
            $idOrdenDia = $idGet[0];
            $idOrdenDiaDetalle = $idGet[1];
            if (isset($_GET['tipo']) && $_GET['tipo']) {
                $idTipo = explode('_', $_GET['tipo']);
                $tipoPlanillaOrigen = $idTipo[0];
                $tipoPlanilla = $idTipo[1];
                //$tipoPlanilla = $_GET['tipo'];
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
            if ($tipoPlanilla <> 5) {
                $resultado = $ordenDelDiaLogic->asignarTipoPlanillaAlDetalle($idOrdenDiaDetalle, $tipoPlanilla);
            } else {
                $resultado = desasignarTipoPlanillaAlDetalle($idOrdenDiaDetalle);
            }
            $linkDerivar = "../orden_del_dia_detalle.php?id=".$idOrdenDia;
            break;
        
        default:
            // code...
            break;
    }
}
/*
var_dump($_GET);
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

