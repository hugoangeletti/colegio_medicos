<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/personaLogic.php');
$personaLogic = new personaLogic();
require_once ('../../dataAccess/colegiadoLogic.php');

$continua = TRUE;
$mensaje = "";

//obtengo a la persona actual para verificar los campos modificados
if (isset($_GET['idColegiado']) && $_GET['idColegiado'] <> "") {
    $idColegiado = $_GET['idColegiado'];
    $resPersona = $personaLogic->obtenerPersonaPorIdColegiado($idColegiado);
    if ($resPersona['estado']) {
        $persona = $resPersona['datos'];
    } else {
        $continua = FALSE;
        $mensaje = $resPersona['mensaje'];
    }
} else {
    $idColegiado = NULL;
    $continua = FALSE;
    $mensaje .= 'Colegiado no ingresado - ';
}

if (isset($_POST['numeroDocumento']) && $_POST['numeroDocumento'] <> "") {
    $numeroDocumento = $_POST['numeroDocumento'];
    //valida que el numero de documento ya no exista en caso que lo haya modificado
    if ($persona['numeroDocumento'] <> $numeroDocumento) {
        $resDocumento = $personaLogic->numeroDocumentoExiste(3, $numeroDocumento);
        if ($resDocumento['estado']) {
            $continua = FALSE;
            $mensaje .= $resDocumento['mensaje'].' - ';
        }
    }
} else {
    $numeroDocumento = NULL;
    $continua = FALSE;
    $mensaje .= 'Numero de Documento no ingresado - ';
}
if (isset($_POST['idPersona']) && $_POST['idPersona'] <> "") {
    $idPersona = $_POST['idPersona'];
} else {
    $idPersona = NULL;
    $continua = FALSE;
    $mensaje .= 'Persona no ingresada - ';
}
if (isset($_POST['apellido']) && $_POST['apellido'] <> "") {
    $apellido = trim($_POST['apellido']);
} else {
    $apellido = NULL;
    $continua = FALSE;
    $mensaje .= 'Apellido no ingresado - ';
}
if (isset($_POST['nombre']) && $_POST['nombre'] <> "") {
    $nombre = trim($_POST['nombre']);
} else {
    $nombre = NULL;
    $continua = FALSE;
    $mensaje .= 'Nombre no ingresado - ';
}
if (isset($_POST['fechaNacimiento']) && $_POST['fechaNacimiento'] <> "") {
    $fechaNacimiento = $_POST['fechaNacimiento'];
    $fechaLimite = sumarRestarSobreFecha(date('Y-m-d'), 23, 'year', '-');
    if ($fechaNacimiento > $fechaLimite) {
        $continua = FALSE;
        $mensaje .= 'Fecha de Nacimiento no es valida - ';
    }
} else {
    $fechaNacimiento = NULL;
    $continua = FALSE;
    $mensaje .= 'Fecha de Nacimiento no ingresada - ';
}
if (isset($_POST['idPaises']) && $_POST['idPaises'] <> "") {
    $idPaises = $_POST['idPaises'];
} else {
    $idPaises = NULL;
    $continua = FALSE;
    $mensaje .= 'Nacionalidad no ingresada - ';
}
if (isset($_POST['nacionalidad_buscar']) && $_POST['nacionalidad_buscar'] <> "") {
    $nacionalidad_buscar = $_POST['nacionalidad_buscar'];
} else {
    $nacionalidad_buscar = NULL;
    $continua = FALSE;
    $mensaje .= 'Nacionalidad no ingresada - ';
}
if (isset($_POST['sexo']) && $_POST['sexo'] <> "") {
    $sexo = $_POST['sexo'];
} else {
    $sexo = NULL;
    $continua = FALSE;
    $mensaje .= 'Sexo no ingresado - ';
}

if ($continua){
    $resultado = $personaLogic->modificarPersona($idPersona, $apellido, $nombre, $sexo, $numeroDocumento, $fechaNacimiento, $idPaises, $persona);
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
        <form name="myForm"  method="POST" action="../persona_actualizar.php?idColegiado=<?php echo $idColegiado ?>&id=<?php echo $idPersona; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
            <input type="hidden"  name="idPersona" id="idPersona" value="<?php echo $idPersona;?>">
            <input type="hidden"  name="apellido" id="apellido" value="<?php echo $apellido;?>">
            <input type="hidden"  name="nombre" id="nombre" value="<?php echo $nombre;?>">
            <input type="hidden"  name="fechaNacimiento" id="fechaNacimiento" value="<?php echo $fechaNacimiento;?>">
            <input type="hidden"  name="tipoDocumento" id="tipoDocumento" value="<?php echo $tipoDocumento;?>">
            <input type="hidden"  name="idPaises" id="idPaises" value="<?php echo $idPaises;?>">
            <input type="hidden"  name="nacionalidad_buscar" id="nacionalidad_buscar" value="<?php echo $nacionalidad_buscar;?>">
            <input type="hidden"  name="numeroDocumento" id="numeroDocumento" value="<?php echo $numeroDocumento;?>">
            <input type="hidden"  name="sexo" id="sexo" value="<?php echo $sexo;?>">
        </form>
    <?php
    }
    ?>
</body>

