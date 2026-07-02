<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/colegiacionAnualLogic.php');
$colegiacionAnualLogic = new colegiacionAnualLogic();

$accion = $_POST['accion'];
if (isset($_POST['idColegiacionAnual'])) {
    $idColegiacionAnual = $_POST['idColegiacionAnual'];
} else {
    $idColegiacionAnual = NULL;
    $accion = 1;
}

$continua = TRUE;
if (isset($_POST['periodo']) && isset($_POST['cuotas']) && isset($_POST['antiguedad']) && isset($_POST['importe']) && isset($_POST['vencimientoCuotaUno']) && isset($_POST['pagoTotal']) && isset($_POST['vencimientoPagoTotal'])) {
    
    $periodo = $_POST['periodo'];
    $cuotas = $_POST['cuotas'];
    $antiguedad = $_POST['antiguedad'];
    $importe = $_POST['importe'];
    $vencimientoCuotaUno = $_POST['vencimientoCuotaUno'];
    $pagoTotal = $_POST['pagoTotal'];
    $vencimientoPagoTotal = $_POST['vencimientoPagoTotal'];
} else {
    $continua = FALSE;
    $tipoMensaje = 'alert alert-danger';
    $mensaje = "Faltan datos, verifique.";
}

if ($continua) {
    switch ($accion) 
    {
        case '1':
            $resultado = $colegiacionAnualLogic->agregarColegiacionAnual($periodo, $cuotas, $antiguedad, $importe, $vencimientoCuotaUno, $pagoTotal, $vencimientoPagoTotal);
            break;
        case '3':
            $resultado = $colegiacionAnualLogic->editarColegiacionAnual($idColegiacionAnual, $periodo, $cuotas, $antiguedad, $importe, $vencimientoCuotaUno, $pagoTotal, $vencimientoPagoTotal);
            break;
        case '2':
            $resultado = borrarColegiacionAnual($idColegiacionAnual);
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

?>


<body onLoad="document.forms['myForm'].submit()">
    <?php
    if ($resultado['estado']) {
    ?>
        <form name="myForm"  method="POST" action="../colegiacion_anual_lista.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../colegiacion_anual__form.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
            <input type="hidden"  name="idColegiacionAnual" id="idColegiacionAnual" value="<?php echo $idColegiacionAnual;?>">
            <input type="hidden"  name="periodo" id="periodo" value="<?php echo $periodo;?>">
            <input type="hidden"  name="cuotas" id="cuotas" value="<?php echo $cuotas;?>">
            <input type="hidden"  name="antiguedad" id="antiguedad" value="<?php echo $antiguedad;?>">
            <input type="hidden"  name="importe" id="importe" value="<?php echo $importe;?>">
            <input type="hidden"  name="vencimientoCuotaUno" id="vencimientoCuotaUno" value="<?php echo $vencimientoCuotaUno;?>">
            <input type="hidden"  name="pagoTotal" id="pagoTotal" value="<?php echo $pagoTotal;?>">
            <input type="hidden"  name="vencimientoPagoTotal" id="vencimientoPagoTotal" value="<?php echo $vencimientoPagoTotal;?>">
            <input type="hidden"  name="accion" id="accion" value="<?php echo $accion;?>">
        </form>
    <?php
    }
    ?>
</body>

