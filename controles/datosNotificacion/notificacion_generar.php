<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/colegiadoLogic.php');
require_once ('../../dataAccess/notificacionLogic.php');
require_once ('../../dataAccess/deudoresLogic.php');
require_once ('../../dataAccess/colegiadoDeudaAnualLogic.php');

$continua = TRUE;
$mensaje = "";
$resultado = NULL;

if (isset($_POST['idNotificacionNota']) && $_POST['idNotificacionNota'] <> "") {
    $idNotificacionNota = $_POST['idNotificacionNota'];
} else {
    $mensaje .= 'Falta idNotificacionNota - ';
    $continua = FALSE;
}
if (isset($_POST['periodoHasta']) && $_POST['periodoHasta'] <> "") {
    $periodoHasta = $_POST['periodoHasta'];
} else {
    $mensaje .= 'Falta periodoHasta - ';
    $continua = FALSE;
}
if (isset($_POST['fechaVencimiento']) && $_POST['fechaVencimiento'] <> "") {
    $fechaVencimiento = $_POST['fechaVencimiento'];
} else {
    $mensaje .= 'Falta fechaVencimiento - ';
    $continua = FALSE;
}
if (isset($_POST['cuotasAdeudadas']) && $_POST['cuotasAdeudadas'] <> "") {
    $cuotasAdeudadas = $_POST['cuotasAdeudadas'];
} else {
    $mensaje .= 'Falta cuotasAdeudadas - ';
    $continua = FALSE;
}

$matricula = NULL;
if (isset($_POST['idColegiado']) && $_POST['idColegiado'] <> "") {
    $idColegiado = $_POST['idColegiado'];
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado'] && $resColegiado['datos']) {
        $colegiado = $resColegiado['datos'];
        $matricula = $colegiado['matricula'];
        $apellidoNombre = trim($colegiado['apellido']).', '.trim($colegiado['nombre']);
        $sexo = $colegiado['sexo'];
    }
}

$idDeudores = NULL;
if (isset($_POST['idDeudores']) && $_POST['idDeudores'] <> "") {
    $idDeudores = $_POST['idDeudores'];
}

if ($continua) {
    $estado = 'E'; //se envia mail
    $notificacionLogic = new notificacionLogic();
    $resultado = $notificacionLogic->generarNotificacionDeuda($idNotificacionNota, $estado, $periodoHasta, $matricula, $cuotasAdeudadas, $fechaVencimiento, $idDeudores);
}
/*
var_dump($_POST);
echo '<br>';
var_dump($resultado);
echo '<br>';
exit;
*/
?>
<body onLoad="document.forms['myForm'].submit()">
    <?php
    $link_listado = "../notificacion_lista.php";
    if (isset($idDeudores)) {
        $link_listado = "../deudores_listado.php";
    }
    if ($resultado['estado']) {
    ?>
        <form name="myForm"  method="POST" action="<?php echo $link_listado; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
            <input type="hidden" id="estadoElecciones" name="estadoElecciones" value="<?php echo $estadoElecciones; ?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../notificacion_generar_form.php<?php if (isset($idDeudores)) { echo '?idDeudores='.$idDeudores; } ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $mensaje; ?>">
            <input type="hidden"  name="tipomensaje" id="tipomensaje" value="<?php echo $tipoMensaje;?>">
            <input type="hidden"  name="periodoHasta" id="periodoHasta" value="<?php echo $periodoHasta;?>">
            <input type="hidden"  name="cuotasAdeudadas" id="cuotasAdeudadas" value="<?php echo $cuotasAdeudadas;?>">
            <input type="hidden"  name="fechaVencimiento" id="fechaVencimiento" value="<?php echo $fechaVencimiento;?>">
        </form>
    <?php
    }
    ?>
</body>
