<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/envios_caja_medicosLogic.php');

$continua = TRUE;
if (isset($_POST['fechaDesde']) && $_POST['fechaDesde'] <> "") {
    $fechaDesde = $_POST['fechaDesde'];
} else {
    //si no viene por post, tomamos el mes anterior
    $mes_y_año = date('Y-m', strtotime("-1 month"));
    $fechaDesde = $mes_y_año."-01";
}
if (isset($_POST['fechaHasta']) && $_POST['fechaHasta'] <> "") {
    $fechaHasta = $_POST['fechaHasta'];
} else {
    echo date('t', strtotime("-1 month")); // Días que tuvo el mes pasado
    $fechaHasta = date('Y-m-t', strtotime("-1 month"));
}

if ($continua){
    $envioLogic = new enviosCajaMedicosLogic();
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] <> "") {
        $idUsuario = $_SESSION['user_id'];
    } else {
        $idUsuario = 1;
    }

    $resultado = $envioLogic->agregarEnvio($fechaDesde, $fechaHasta, $idUsuario);
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
        <form name="myForm"  method="POST" action="../envios_caja_medicos.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../envios_caja_medicos_form.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
            <input type="hidden"  name="fechaDesde" id="fechaDesde" value="<?php echo $fechaDesde;?>">
            <input type="hidden"  name="fechaHasta" id="fechaHasta" value="<?php echo $fechaHasta;?>">
        </form>
    <?php
    }
    ?>
</body>

