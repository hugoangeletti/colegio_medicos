<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/colegiadoDomicilioLogic.php');
$colegiadoDomicilioLogic = new colegiadoDomicilioLogic();

$idColegiado = $_POST['idColegiado'];
$continua = TRUE;

if (isset($_POST['calle']) && $_POST['calle'] <> "") {
    $calle = $_POST['calle'];
} else {
    $calle = NULL;
    $continua = FALSE;
    $mensaje .= 'Calle no ingresado - ';
}
if (isset($_POST['numero']) && $_POST['numero'] <> "") {
    $numero = $_POST['numero'];
} else {
    $numero = NULL;
    $continua = FALSE;
    $mensaje .= 'Numero de casa no ingresado - ';
}
if (isset($_POST['lateral']) && $_POST['lateral'] <> "") {
    $lateral = $_POST['lateral'];
} else {
    $lateral = NULL;
    //$continua = FALSE;
    //$mensaje .= 'Lateral no ingresado - ';
}
if (isset($_POST['piso'])) {
    $piso = $_POST['piso'];
} else {
    $piso = NULL;
}
if (isset($_POST['depto'])) {
    $depto = $_POST['depto'];
} else {
    $depto = NULL;
}
if (isset($_POST['idLocalidad']) && $_POST['idLocalidad'] <> "") {
    $idLocalidad = $_POST['idLocalidad'];
} else {
    $idLocalidad = NULL;
    $continua = FALSE;
    $mensaje .= 'Localidad no ingresada - ';
}
if (isset($_POST['localidad_buscar']) && $_POST['localidad_buscar'] <> "") {
    $localidad_buscar = $_POST['localidad_buscar'];
} else {
    $localidad_buscar = NULL;
    $continua = FALSE;
    $mensaje .= 'Localidad no ingresada - ';
}
if (isset($_POST['codigoPostal']) && $_POST['codigoPostal'] <> "") {
    $codigoPostal = $_POST['codigoPostal'];
} else {
    $codigoPostal = NULL;
    $continua = FALSE;
    $mensaje .= 'Codigo Postal no ingresado - ';
}

if ($continua){
    $origenForm = $_POST['origenForm'];
    $accion = 'modificar';
    $resultado = $colegiadoDomicilioLogic->agregarColegiadoDomicilio($idColegiado, $calle, $numero, $lateral, $piso, $depto, $idLocalidad, $codigoPostal, $accion);
} else {
    $resultado['mensaje'] = "ERROR EN LOS DATOS INGRESADOS: ".$mensaje;
    $resultado['icono'] = "glyphicon glyphicon-remove";
    $resultado['clase'] = "alert alert-error";
}

?>

<body onLoad="document.forms['myForm'].submit()">
    <?php
    if ($resultado['estado']) {
        if ($origenForm == 'consulta') {
            $formulario = "colegiado_consulta.php?idColegiado=".$idColegiado;
        } else {
            $formulario = "colegiado_domicilio.php?idColegiado=".$idColegiado;
        }
    ?>
        <form name="myForm"  method="POST" action="../<?php echo $formulario; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../domicilio_actualizar.php?idColegiado=<?php echo $idColegiado; ?>&ori=<?php echo $origenForm; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="glyphicon glyphicon-exclamation-sign">
            <input type="hidden"  name="clase" id="clase" value="alert alert-info">
            <input type="hidden"  name="calle" id="calle" value="<?php echo $calle;?>">
            <input type="hidden"  name="idLocalidad" id="idLocalidad" value="<?php echo $idLocalidda;?>">
            <input type="hidden"  name="localidad_buscar" id="localidad_buscar" value="<?php echo $localidad_buscar;?>">
            <input type="hidden"  name="codigoPostal" id="codigoPostal" value="<?php echo $codigoPostal;?>">
            <input type="hidden"  name="numero" id="numero" value="<?php echo $numero;?>">
            <input type="hidden"  name="lateral" id="lateral" value="<?php echo $lateral;?>">
            <input type="hidden"  name="piso" id="piso" value="<?php echo $piso;?>">
            <input type="hidden"  name="depto" id="depto" value="<?php echo $depto;?>">
        </form>
    <?php
    }
    ?>
</body>

