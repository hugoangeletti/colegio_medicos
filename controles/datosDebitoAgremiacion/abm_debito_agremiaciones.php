<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/agremiacionesDebitoLogic.php');

$continua = TRUE;
$mensaje = "";

if (isset($_POST['periodoSeleccionado']) && $_POST['periodoSeleccionado'] <> "") {
    $periodoSeleccionado = $_POST['periodoSeleccionado'];
} else {
    $continua = FALSE;
    $mensaje .= 'periodoSeleccionado no ingresado - ';
}

if (isset($_POST['idLugarPago']) && $_POST['idLugarPago'] <> "") {
    $idLugarPago = $_POST['idLugarPago'];
} else {
    $continua = FALSE;
    $mensaje .= 'idLugarPago de Matriculacion no ingresado - ';
}

if (isset($_POST['idAgremiacionesDebito']) && $_POST['idAgremiacionesDebito'] <> "") {
    $idAgremiacionesDebito = $_POST['idAgremiacionesDebito'];
} else {
    $idAgremiacionesDebito = NULL;
    if (isset($_POST['idColegiado']) && $_POST['idColegiado'] <> "") {
        $idColegiado = $_POST['idColegiado'];
    } else {
        $continua = FALSE;
        $mensaje .= 'idColegiado de Matriculacion no ingresado - ';
    }
}

if ($continua){
    $agremiacionesDebitoLogic = new agremiacionesDebitoLogic();
    if (isset($idAgremiacionesDebito) && $idAgremiacionesDebito > 0) {
        $resultado = $agremiacionesDebitoLogic->borrarColegiadoDebitoAgremiacion($idAgremiacionesDebito);
    } else {
        $resultado = $agremiacionesDebitoLogic->agregarColegiadoDebitoAgremiacion($idColegiado, $idLugarPago, $periodoSeleccionado);
    }
} else {
    $resultado['mensaje'] = "ERROR EN LOS DATOS INGRESADOS: ".$mensaje;
    $resultado['icono'] = "glyphicon glyphicon-remove";
    $resultado['clase'] = "alert alert-danger";
    $resultado['estado'] = $continua;
}
/*
echo '<br>';
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
        <form name="myForm"  method="POST" action="../debito_agremiaciones.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
            <input type="hidden" name="idLugarPago" id="idLugarPago" value="<?php echo $idLugarPago; ?>" />
            <input type="hidden" name="periodoSeleccionado" id="periodoSeleccionado" value="<?php echo $periodoSeleccionado; ?>" />
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../debito_agremiaciones_form.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
            <input type="hidden" name="idColegiado" id="idColegiado" value="<?php echo $idColegiado; ?>" />
            <input type="hidden" name="idLugarPago" id="idLugarPago" value="<?php echo $idLugarPago; ?>" />
            <input type="hidden" name="periodoSeleccionado" id="periodoSeleccionado" value="<?php echo $periodoSeleccionado; ?>" />
        </form>
    <?php
    }
    ?>
</body>

