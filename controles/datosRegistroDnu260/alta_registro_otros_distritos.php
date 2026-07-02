<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/registroDNU260OtrosDistritosLogic.php');
$registroDNU260OtrosDistritosLogic = new registroDNU260OtrosDistritosLogic();

$continua = TRUE;
$mensaje = "";

$accion = $_POST['accion'];

if (isset($_POST['distrito']) && $_POST['distrito'] <> "") {
    $distrito = $_POST['distrito'];
} else {
    $distrito = NULL;
    $continua = FALSE;
    $mensaje .= "Distrito no ingresado - ";
}
if (isset($_POST['numeroRegistro']) && $_POST['numeroRegistro'] <> "") {
    $numero = $_POST['numeroRegistro'];
} else {
    $numero = NULL;
    $continua = FALSE;
    $mensaje .= "numeroRegistro no ingresado - ";
}

if (isset($_POST['idTipoDocumento']) && $_POST['idTipoDocumento'] <> "") {
    $idTipoDocumento = $_POST['idTipoDocumento'];
} else {
    $idTipoDocumento = NULL;
}

if (isset($_POST['numeroDocumento']) && $_POST['numeroDocumento'] <> "") {
    $numeroDocumento = $_POST['numeroDocumento'];
    //valida que el numero de documento ya no exista    
    $resDocumento = $registroDNU260OtrosDistritosLogic->numeroDocumentoExiste($idTipoDocumento, $numeroDocumento);
    if ($resDocumento['estado'] && $accion == '1') {
        $continua = FALSE;
        $mensaje .= $resDocumento['mensaje'].' - ';
    }
} else {
    $numeroDocumento = NULL;
}
if (isset($_POST['numeroPasaporte']) && $_POST['numeroPasaporte'] <> "") {
    $numeroPasaporte = trim($_POST['numeroPasaporte']);    
} else {
    $numeroPasaporte = NULL;
}
if (isset($_POST['apellido'])) {
    $apellido = $_POST['apellido'];
} else {
    $apellido = NULL;
    $continua = FALSE;
    $mensaje .= 'Apellido no ingresado - ';
}
if (isset($_POST['nombre'])) {
    $nombre = $_POST['nombre'];
} else {
    $nombre = NULL;
    $continua = FALSE;
    $mensaje .= 'Nombre no ingresado - ';
}
if (isset($_POST['fechaNacimiento'])) {
    $fechaNacimiento = $_POST['fechaNacimiento'];
    $fechaLimite = sumarRestarSobreFecha(date('Y-m-d'), 23, 'year', '-');
    if ($fechaNacimiento > $fechaLimite) {
        $continua = FALSE;
        $mensaje .= 'Fecha de Nacimiento no es valida - ';
    }
} else {
    $fechaNacimiento = NULL;
}
if (isset($_POST['fechaAlta']) && $_POST['fechaAlta'] <> "") {
    $fechaAlta  = $_POST['fechaAlta'];
} else {
    $fechaAlta  = NULL;
}
if (isset($_POST['idPais']) && $_POST['idPais'] <> "") {
    $idPais = $_POST['idPais'];
} else {
    $idPais = NULL;
}
if (isset($_POST['nacionalidad_buscar']) && $_POST['nacionalidad_buscar'] <> "") {
    $nacionalidad_buscar = $_POST['nacionalidad_buscar'];
} else {
    $nacionalidad_buscar = NULL;
}
if (isset($_POST['fechaTitulo']) && $_POST['fechaTitulo'] <> "") {
    $fechaTitulo = $_POST['fechaTitulo'];
    $fechaLimite = date('Y-m-d');
    if ($fechaTitulo > $fechaLimite) {
        $continua = FALSE;
        $mensaje .= 'Fecha de Titulo no es valida - ';
    }
} else {
    $fechaTitulo = NULL;
}
if (isset($_POST['fechaBaja']) && $_POST['fechaBaja'] <> "") {
    $fechaBaja = $_POST['fechaBaja'];
    $fechaLimite = date('Y-m-d');
    if ($fechaBaja > $fechaLimite) {
        $continua = FALSE;
        $mensaje .= 'Fecha de Baja no es valida - ';
    }
} else {
    $fechaBaja = NULL;
}
if (isset($_POST['universidad']) && $_POST['universidad'] <> "") {
    $universidad = $_POST['universidad'];
} else {
    $universidad = NULL;
}
if (isset($_POST['especialidad']) && $_POST['especialidad'] <> "") {
    $especialidad = $_POST['especialidad'];
} else {
    $especialidad = NULL;
}
if (isset($_POST['sexo']) && $_POST['sexo'] <> "") {
    $sexo = $_POST['sexo'];
} else {
    $sexo = NULL;
    $continua = FALSE;
    $mensaje .= 'Sexo no ingresado - ';
}
if (isset($_POST['observacion']) && $_POST['observacion'] <> "") {
    $observacion = $_POST['observacion'];
} else {
    $observacion = NULL;
}

if ($continua){
    switch ($accion) {
        case 1:
            $resultado = $registroDNU260OtrosDistritosLogic->agregarRegistroOtrosDistritos($numero, $apellido, $nombre, $idPais, $sexo, $fechaNacimiento, $idTipoDocumento, $numeroDocumento, $numeroPasaporte, $fechaAlta, $universidad, $fechaTitulo, $especialidad, $fechaBaja, $distrito, $observacion);
            break;

        case 2:
            
            break;
            
        case 3:
            $idRegistro = $_POST['id'];
            $resRegistro = $registroDNU260OtrosDistritosLogic->obtenerRegistroOtrosDistritosPorId($idRegistro);
            if ($resRegistro['estado']) {
                $datosAnteriores = $resRegistro['datos'];
                $resultado = $registroDNU260OtrosDistritosLogic->modificarRegistroOtrosDistritos($idRegistro, $apellido, $nombre, $idPais, $sexo, $fechaNacimiento, $idTipoDocumento, $numeroDocumento, $numeroPasaporte, $fechaAlta, $universidad, $fechaTitulo, $especialidad, $fechaBaja, $distrito, $numero, $observacion, $datosAnteriores);
            } else {
                $resultado['estado'] = FALSE;
            }
        default:
            break;
    }
    
} else {
    $resultado['mensaje'] = "ERROR EN LOS DATOS INGRESADOS: ".$mensaje;
    $resultado['icono'] = "glyphicon glyphicon-remove";
    $resultado['clase'] = "alert alert-danger";
    $resultado['estado'] = $continua;
}

var_dump($resultado);
exit;
?>

<body onLoad="document.forms['myForm'].submit()">
    <?php
    if ($resultado['estado']) {
    ?>
        <form name="myForm"  method="POST" action="../registro_dnu260_otros_distritos_lista.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
        </form>
    <?php
    } else {        
    ?>
        <form name="myForm"  method="POST" action="../registro_dnu260_otros_distritos_form.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
            <input type="hidden"  name="accion" id="accion" value="<?php echo $accion;?>">
            <input type="hidden"  name="apellido" id="apellido" value="<?php echo $apellido;?>">
            <input type="hidden"  name="nombre" id="nombre" value="<?php echo $nombre;?>">
            <input type="hidden"  name="fechaNacimiento" id="fechaNacimiento" value="<?php echo $fechaNacimiento;?>">
            <input type="hidden"  name="fechaAlta" id="fechaAlta" value="<?php echo $fechaAlta;?>">
            <input type="hidden"  name="idTipoDocumento" id="idTipoDocumento" value="<?php echo $idTipoDocumento;?>">
            <input type="hidden"  name="idPais" id="idPais" value="<?php echo $idPais;?>">
            <input type="hidden"  name="nacionalidad_buscar" id="nacionalidad_buscar" value="<?php echo $nacionalidad_buscar;?>">
            <input type="hidden"  name="universidad" id="universidad" value="<?php echo $universidad;?>">
            <input type="hidden"  name="fechaTitulo" id="fechaTitulo" value="<?php echo $fechaTitulo;?>">
            <input type="hidden"  name="especialidad" id="especialidad" value="<?php echo $especialidad;?>">
            <input type="hidden"  name="numeroDocumento" id="numeroDocumento" value="<?php echo $numeroDocumento;?>">
            <input type="hidden"  name="numeroPasaporte" id="numeroPasaporte" value="<?php echo $numeroPasaporte;?>">
            <input type="hidden"  name="sexo" id="sexo" value="<?php echo $sexo;?>">
            <input type="hidden"  name="fechaBaja" id="fechaBaja" value="<?php echo $fechaBaja; ?>">
            <input type="hidden"  name="distrito" id="distrito" value="<?php echo $distrito; ?>">
            <input type="hidden"  name="numeroRegistro" id="numeroRegistro" value="<?php echo $numero; ?>">
            <input type="hidden"  name="observacion" id="observacion" value="<?php echo $observacion; ?>">
        </form>
    <?php
    }
    ?>
</body>

