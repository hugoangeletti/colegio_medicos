<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/deudoresLogic.php');

$continua = TRUE;
$mensaje = "";
$resultado = NULL;
$matricula = NULL;

if (isset($_GET['borrar'])) {
    if (isset($_GET['idDeudores']) && $_GET['idDeudores']) {
        $idDeudores = $_GET['idDeudores'];
        $borrar = TRUE;
    } else {
        $mensaje .= 'Falta idDeudores - ';
        $continua = FALSE;
    }
} else {
    if (isset($_POST['tipo_filtro']) && $_POST['tipo_filtro'] <> "") {
        $tipo_filtro = $_POST['tipo_filtro'];
    } else {
        $mensaje .= 'Falta tipo_filtro - ';
        $continua = FALSE;
    }
    if (isset($_POST['periodoHasta']) && $_POST['periodoHasta'] <> "") {
        $periodo_hasta = $_POST['periodoHasta'];
    } else {
        $mensaje .= 'Falta periodoHasta - ';
        $continua = FALSE;
    }
    if (isset($_POST['cuotasAdeudadas']) && $_POST['cuotasAdeudadas'] <> "") {
        $cuotas_adeudadas = $_POST['cuotasAdeudadas'];
    } else {
        $mensaje .= 'Falta cuotasAdeudadas - ';
        $continua = FALSE;
    }
    $borrar = FALSE;
}

if ($continua) {
    $estado = 'E'; //se envia mail
    $deudoresLogic = new deudoresLogic();
    if ($borrar) {
        $resultado = $deudoresLogic->borrarDeudores($idDeudores);    
    } else {
        $resultado = $deudoresLogic->generarDeudores($tipo_filtro, $periodo_hasta, $cuotas_adeudadas);
    }
} else {
    $resultado['mensaje'] = $mensaje;
    $resultado['clase'] = "alert alert-danger";
    $resultado['icono'] = "glyphicon glyphicon-exclamation-sign";
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
    if ($resultado['estado']) {
        if ($borrar) {
        ?>
            <form name="myForm"  method="POST" action="../deudores_listado.php">
                <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
                <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
                <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
            </form>
        <?php
        } else {
        $idDeudores = $resultado['idDeudores'];
        ?>
            <form name="myForm"  method="POST" action="../deudores_detalle.php?id=<?php echo $idDeudores; ?>">
                <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
                <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
                <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
            </form>
        <?php
        }
    } else {
    ?>
        <form name="myForm"  method="POST" action="../deudores_form.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
            <input type="hidden" id="tipoFiltro" name="tipoFiltro" value="<?php echo $tipo_filtro; ?>">
            <input type="hidden" id="periodoHasta" name="periodoHasta" value="<?php echo $periodo_hasta; ?>">
            <input type="hidden" id="cuotasAdeudadas" name="cuotasAdeudadas" value="<?php echo $cuotas_adeudadas; ?>">
        </form>
    <?php
    }
    ?>
</body>
