<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/colegiadoLogic.php');

$continua = TRUE;
$mensaje = "";

//obtengo a la persona actual para verificar los campos modificados
if (isset($_GET['idColegiado']) && $_GET['idColegiado'] <> "") {
    $idColegiado = $_GET['idColegiado'];
    $colegiadoLogic = new colegiadoLogic();
    $resPersona = $colegiadoLogic->obtenerMatriculaPorIdColegiado($idColegiado);
    if ($resPersona['estado']) {
        $datosAnteriores = $resPersona['datos'];
    } else {
        $continua = FALSE;
        $mensaje = $resPersona['mensaje'];
    }
} else {
    $continua = FALSE;
    $mensaje .= 'Colegiado no ingresado - ';
}

if (isset($_POST['fechaMatriculacion']) && $_POST['fechaMatriculacion'] <> "") {
    $fechaMatriculacion = $_POST['fechaMatriculacion'];
} else {
    $continua = FALSE;
    $mensaje .= 'Fecha de Matriculacion no ingresado - ';
}
if (isset($_POST['tomo']) && $_POST['tomo'] <> "") {
    $tomo = $_POST['tomo'];
} else {
    $continua = FALSE;
    $mensaje .= 'Tomo no ingresado - ';
}
if (isset($_POST['folio']) && $_POST['folio'] <> "") {
    $folio = trim($_POST['folio']);
} else {
    $continua = FALSE;
    $mensaje .= 'Folio no ingresado - ';
}
if (isset($_POST['matriculaNacional']) && $_POST['matriculaNacional'] <> "") {
    $matriculaNacional = trim($_POST['matriculaNacional']);
} else {
    $matriculaNacional = "";
}

if ($continua){
    $resultado = $colegiadoLogic->modificarMatricula($idColegiado, $fechaMatriculacion, $tomo, $folio, $matriculaNacional, $datosAnteriores);
} else {
    $resultado['mensaje'] = "ERROR EN LOS DATOS INGRESADOS: ".$mensaje;
    $resultado['icono'] = "glyphicon glyphicon-remove";
    $resultado['clase'] = "alert alert-danger";
    $resultado['estado'] = $continua;
}
?>

<body onLoad="document.forms['myForm'].submit()">
    <?php
    if ($resultado['estado']) {
    ?>
        <form name="myForm"  method="POST" action="../colegiado_consulta.php?idColegiado=<?php echo $idColegiado; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../matricula_actualizar.php?idColegiado=<?php echo $idColegiado ?>&id=<?php echo $idPersona; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
            <input type="hidden"  name="fechaMatriculacion" id="fechaMatriculacion" value="<?php echo $fechaMatriculacion;?>">
            <input type="hidden"  name="tomo" id="tomo" value="<?php echo $tomo;?>">
            <input type="hidden"  name="folio" id="folio" value="<?php echo $folio;?>">
            <input type="hidden"  name="matriculaNacional" id="matriculaNacional" value="<?php echo $matriculaNacional;?>">
        </form>
    <?php
    }
    ?>
</body>

