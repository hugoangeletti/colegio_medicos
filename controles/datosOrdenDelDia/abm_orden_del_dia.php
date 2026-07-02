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
    if (isset($_POST['idOrdenDia']) && $_POST['idOrdenDia'] <> "") {
        $idOrdenDia = $_POST['idOrdenDia'];
    }
} else {
    if (isset($_GET['accion']) && $_GET['accion'] <> "") {
        $accion = $_GET['accion'];
        if (isset($_GET['id']) && $_GET['id'] <> "") {
            $idOrdenDia = $_GET['id'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idOrdenDia. ";        
        }
    } else {
        $continua = FALSE;
        $mensaje .= "Falta accion. ";        
    }
}

if ($accion == 1 || $accion == 3) {
    //si es alta o modificacion, valido los campos cargados
    if (isset($_POST['fecha']) && $_POST['fecha'] <> "") {
        $fecha = $_POST['fecha'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta fecha. ";
    }
    if (isset($_POST['periodo']) && $_POST['periodo'] <> "") {
        $periodo = $_POST['periodo'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta periodo. ";
    }
    if (isset($_POST['numero']) && $_POST['numero'] <> "") {
        $numero = $_POST['numero'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta numero. ";
    }
    if (isset($_POST['fechaDesde']) && $_POST['fechaDesde'] <> "") {
        $fechaDesde = $_POST['fechaDesde'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta fechaDesde. ";
    }
    if (isset($_POST['fechaHasta']) && $_POST['fechaHasta'] <> "") {
        $fechaHasta = $_POST['fechaHasta'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta fechaHasta. ";
    }
    if (isset($_POST['observaciones']) && $_POST['observaciones'] <> "") {
        $observaciones = $_POST['observaciones'];
    } else {
        $observaciones = NULL;
    }
}

if ($continua){
    switch ($accion) 
    {
        case '1':
            $resultado = $ordenDelDiaLogic->agregarOrdenDelDia($fecha, $periodo, $numero, $fechaDesde, $fechaHasta, $observaciones);
            break;
        case '3':
            $resultado = modificarOrdenDelDia($fecha, $periodo, $numero, $fechaDesde, $fechaHasta, $observaciones, $idOrdenDia);
            break;
        case '2':
            $resultado = $ordenDelDiaLogic->borrarOrdenDelDia($idOrdenDia);
            break;
        case '5':
            $resultado = $ordenDelDiaLogic->cerrarOrdenDelDia($idOrdenDia);
            break;
        default:
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
    <?php
    if ($resultado['estado']) {
    ?>
        <form name="myForm"  method="POST" action="../orden_del_dia_listado.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../orden_del_dia_listado.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
            <input type="hidden"  name="idOrdenDia" id="idOrdenDia" value="<?php echo $idOrdenDia;?>">
            <input type="hidden"  name="observaciones" id="observaciones" value="<?php echo $observaciones;?>">
            <input type="hidden"  name="fecha" id="fecha" value="<?php echo $fecha;?>">
            <input type="hidden"  name="fechaDesde" id="fechaDesde" value="<?php echo $fechaDesde;?>">
            <input type="hidden"  name="fechaHasta" id="fechaHasta" value="<?php echo $fechaHasta;?>">
            <input type="hidden"  name="numero" id="numero" value="<?php echo $numero;?>">
            <input type="hidden"  name="periodo" id="periodo" value="<?php echo $periodo;?>">
            <input type="hidden"  name="accion" id="accion" value="<?php echo $accion;?>">
        </form>
    <?php
    }
    ?>
</body>

