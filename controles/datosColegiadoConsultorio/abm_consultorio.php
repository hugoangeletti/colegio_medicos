<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/colegiadoConsultorioLogic.php');
$colegiadoConsultorioLogic = new colegiadoConsultorioLogic();

$idColegiado = $_POST['idColegiado'];
$accion = $_POST['accion'];
if (isset($_POST['idColegiadoConsultorio'])) {
    $idConsultorio = $_POST['idColegiadoConsultorio'];
} else {
    $idConsultorio = NULL;
}
$continua = TRUE;

if (isset($_POST['calle']) && isset($_POST['localidad_buscar']) 
        && isset($_POST['idLocalidad']) && isset($_POST['fechaHabilitacion']) && isset($_POST['resolucion'])){
    $calle = $_POST['calle'];
    if ($_POST['numero']) {
        $numero = $_POST['numero'];
    } else {
        $numero = NULL;
    }
    if ($_POST['lateral']) {
        $lateral = $_POST['lateral'];
    } else {
        $lateral = NULL;
    }
    if ($_POST['piso']) {
        $piso = $_POST['piso'];
    } else {
        $piso = NULL;
    }
    if ($_POST['departamento']) {
        $depto = $_POST['departamento'];
    } else {
        $depto = NULL;
    }
    if ($_POST['telefono']) {
        $telefono = $_POST['telefono'];
    } else {
        $telefono = NULL;
    }
    $localidad_buscar = $_POST['localidad_buscar'];
    $idLocalidad = $_POST['idLocalidad'];
    $codigoPostal = $_POST['codigoPostal'];
    if ($_POST['fechaHabilitacion']) {
        $fechaHabilitacion = $_POST['fechaHabilitacion'];
    } else {
        $fechaHabilitacion = NULL;
    }
    if ($_POST['ultimaInspeccion']) {
        $ultimaInspeccion = $_POST['ultimaInspeccion'];
    } else {
        $ultimaInspeccion = NULL;
    }
    $observacion = $_POST['observacion'];
    if ($_POST['fechaBaja']) {
        $fechaBaja = $_POST['fechaBaja'];
    } else {
        $fechaBaja = NULL;
    }
    $resolucion = $_POST['resolucion'];
} else {
    $continua = FALSE;
    $tipoMensaje = 'alert alert-danger';
    $mensaje = "Faltan datos, verifique.";
}

if ($continua){
    switch ($accion) {
        case 1:
            $resultado = $colegiadoConsultorioLogic->agregarColegiadoConsultorio($idColegiado, $calle, $numero, $lateral, $piso, $depto, $telefono, $idLocalidad, $codigoPostal, $fechaHabilitacion, $ultimaInspeccion, $observacion, $resolucion, $fechaBaja);
        break;

        case 2:
            $resultado = $colegiadoConsultorioLogic->eliminarColegiadoConsultorio($idConsultorio, $fechaBaja, $observacion);
        break;

        case 3:
            $resultado = $colegiadoConsultorioLogic->modificarColegiadoConsultorio($idConsultorio, $calle, $numero, $lateral, $piso, $depto, $telefono, $idLocalidad, $codigoPostal, $fechaHabilitacion, $ultimaInspeccion, $observacion, $resolucion, $fechaBaja);
        break;

        default:
            
            break;
    }
} else {
    $resultado['mensaje'] = "ERROR EN LOS DATOS INGRESADOS";
    $resultado['icono'] = "glyphicon glyphicon-remove";
    $resultado['clase'] = "alert alert-error";
}
?>

<body onLoad="document.forms['myForm'].submit()">
    <?php
    if ($resultado['estado']) {
    ?>
        <form name="myForm"  method="POST" action="../colegiado_consultorios.php?idColegiado=<?php echo $idColegiado; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../consultorio_form.php?idColegiado=<?php echo $idColegiado; ?>&id=<?php echo $idConsultorio; ?>&accion=<?php echo $accion; ?>">
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
            <input type="hidden"  name="departamento" id="departamento" value="<?php echo $depto;?>">
            <input type="hidden"  name="telefono" id="telefono" value="<?php echo $telefono;?>">
            <input type="hidden"  name="fechaHabilitacion" id="fechaHabilitacion" value="<?php echo $fechaHabilitacion;?>">
            <input type="hidden"  name="ultimaInspeccion" id="ultimaInspeccion" value="<?php echo $ultimaInspeccion;?>">
            <input type="hidden"  name="observacion" id="observacion" observacion="<?php echo $observacion;?>">
            <input type="hidden"  name="fechaBaja" id="fechaBaja" value="<?php echo $fechaBaja;?>">
            <input type="hidden"  name="resolucion" id="resolucion" value="<?php echo $resolucion;?>">
        </form>
    <?php
    }
    ?>
</body>

