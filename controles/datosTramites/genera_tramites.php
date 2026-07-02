<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/tramiteLogic.php');
$tramiteLogic = new tramiteLogic();

if (isset($_POST['accion'])) {
    $accion = $_POST['accion'];
} else {
    $idSumariante = NULL;
    $accion = 1;
}

$continua = TRUE;
/*
if (isset($_POST['detalle']) && $_POST['detalle'] <> "") {
    $detalle = $_POST['detalle'];
} else {
    $continua = FALSE;
    $mensaje .= "Falta ingresar detalle - ";
}
*/
if (isset($_POST['fechaDesde']) && $_POST['fechaDesde'] <> "") {
    $fechaDesde = $_POST['fechaDesde'];
} else {
    $continua = FALSE;
    $mensaje .= "Falta ingresar fechaDesde - ";
}
if (isset($_POST['fechaHasta']) && $_POST['fechaHasta'] <> "") {
    $fechaHasta = $_POST['fechaHasta'];
} else {
    $continua = FALSE;
    $mensaje .= "Falta ingresar fechaHasta - ";
}
if (isset($_POST['tipoTramite']) && $_POST['tipoTramite'] <> "") {
    $tipoTramite = $_POST['tipoTramite'];
    switch ($tipoTramite) {
        case 'M':
            $detalle = "Movimientos matriculares y Altas";
            break;
        
        case 'A':
            $detalle = "Altas";
            break;
        
        case 'F':
            $detalle = "Fallecidos";
            break;
        
        case 'J':
            $detalle = "Jubilados";
            break;
        
        default:
            $detalle = "Movimientos matriculares";
            $tipoTramite = "M";
            break;
    }
} else {
    $continua = FALSE;
    $mensaje .= "Falta ingresar tipoTramite - ";
}

if ($continua){
    switch ($accion) 
    {
        case '1':
            $resultado = $tramiteLogic->agregarTramites($fechaDesde, $fechaHasta, $detalle, $tipoTramite);
            break;
        case '2':
            $resultado['estado'] = false;
            $resultado['mensaje'] = "pendiente".$accion;
            break;
        case '3':
            $resultado['estado'] = false;
            $resultado['mensaje'] = "pendiente".$accion;
            break;
        default:
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
var_dump($resultado);
echo '<br>';
var_dump($_POST);
exit;
*/
?>


<body onLoad="document.forms['myForm'].submit()">
    <?php
    if ($resultado['estado']) {
    ?>
        <form name="myForm"  method="POST" action="../tramites_listado.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $resultado['mensaje'];?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../tramites_form.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
            <input type="hidden"  name="fechaDesde" id="fechaDesde" value="<?php echo $fechaDesde;?>">
            <input type="hidden"  name="fechaHasta" id="fechaHasta" value="<?php echo $fechaHasta;?>">
            <input type="hidden"  name="detalle" id="detalle" value="<?php echo $detalle;?>">
            <input type="hidden"  name="accion" id="accion" value="<?php echo $accion;?>">
        </form>
    <?php
    }
    ?>
</body>

