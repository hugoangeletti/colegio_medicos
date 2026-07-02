<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/registroDNU260Logic.php');
$registroDNU260Logic = new registroDNU260Logic();

$continua = TRUE;
$mensaje = "";

$accion = $_POST['accion'];

if (isset($_POST['idRegistro']) && $_POST['idRegistro'] <> "") {
    $idRegistro = $_POST['idRegistro'];
} else {
    $idRegistro= NULL;
    $continua = FALSE;
    $mensaje .= 'idRegistro no ingresado - ';
}
if ($accion <> 1) {
    if (isset($_POST['id']) && $_POST['id'] <> "") {
        $idRegistroLaboral = $_POST['id'];
    } else {
        $idRegistroLaboral = NULL;
        $continua = FALSE;
        $mensaje .= 'idRegistroLaboral no ingresado - ';
    }
} else {
    $idRegistroLaboral = NULL;
}
if ($accion <> 2) {
    if (isset($_POST['entidad']) && $_POST['entidad'] <> "") {
        $entidad = $_POST['entidad'];
    } else {
        $entidad= NULL;
        $continua = FALSE;
        $mensaje .= 'entidad no ingresado - ';
    }
    if (isset($_POST['domicilioProfesional']) && $_POST['domicilioProfesional'] <> "") {
        $domicilioProfesional = $_POST['domicilioProfesional'];
    } else {
        $domicilioProfesional= NULL;
        $continua = FALSE;
        $mensaje .= 'domicilioProfesional no ingresado - ';
    }
    if (isset($_POST['localidadProfesional']) && $_POST['localidadProfesional'] <> "") {
        $localidadProfesional = $_POST['localidadProfesional'];
    } else {
        $localidadProfesional = NULL;
        $continua = FALSE;
        $mensaje .= 'localidadProfesional no ingresada - ';
    }
    $codigoPostalProfesional = $_POST['codigoPostalProfesional'];
    $telefonoProfesional = $_POST['telefonoProfesional'];
}
if ($continua){
    switch ($accion) {
        case 1:
            $resultado = $registroDNU260Logic->agregarDatoLaboral($idRegistro, $entidad, $domicilioProfesional, $localidadProfesional, $codigoPostalProfesional, $telefonoProfesional, NULL);
            break;

        case 2:
            $resultado = $registroDNU260Logic->borrarDatoLaboral($idRegistroLaboral);
            break;
            
        case 3:
            $idRegistroLaboral = $_POST['id'];
            $resRegistro = $registroDNU260Logic->obtenerDatosLaboralesPorId($idRegistroLaboral);
            if ($resRegistro['estado']) {
                $datosAnteriores = $resRegistro['datos'];
                $resultado = $registroDNU260Logic->modificarDatoLaboral($entidad, $domicilioProfesional, $localidadProfesional, $codigoPostalProfesional, $telefonoProfesional, $idRegistroLaboral, $datosAnteriores);
            } else {
                $resultado['estado'] = FALSE;
            }
            break;

        default:
            break;
    }
    
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
        <form name="myForm"  method="POST" action="../registro_dnu260_laboral_lista.php?id=<?php echo $idRegistro ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
        </form>
    <?php
    } else {        
        if ($accion == '3' || $accion == '1') {
            if ($accion == '1') {
                $action = '../registro_dnu260_laboral_form.php?idRegistro='.$idRegistro;
            } else {
                $action = '../registro_dnu260_laboral_form.php?idRegistro='.$idRegistro.'&id='.$idRegistroLaboral;
            }
            ?>
            <form name="myForm"  method="POST" action="<?php echo $action; ?>">
                <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
                <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
                <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
                <input type="hidden"  name="accion" id="accion" value="<?php echo $accion;?>">
                <input type="hidden"  name="entidad" id="entidad" value="<?php echo $entidad;?>">
                <input type="hidden"  name="domicilioProfesional" id="domicilioProfesional" value="<?php echo $domicilioProfesional;?>">
                <input type="hidden"  name="localidadProfesional" id="localidadProfesional" value="<?php echo $localidadProfesional;?>">
                <input type="hidden"  name="codigoPostalProfesional" id="codigoPostalProfesional" value="<?php echo $codigoPostalProfesional;?>">
                <input type="hidden"  name="telefonoProfesional" id="telefonoProfesional" value="<?php echo $telefonoProfesional;?>">
            </form>
        <?php
        } else {
        ?>
            <form name="myForm"  method="POST" action="../registro_dnu260_laboral_lista.php?id=<?php echo $idRegistro ?>">
                <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
                <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
                <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
            </form>
        <?php
        }
    }
    ?>
</body>

