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

if (isset($_POST['distrito']) && $_POST['distrito'] <> "") {
    $distrito = $_POST['distrito'];
    if ($distrito <> '1') {
        if (isset($_POST['numeroRegistro']) && $_POST['numeroRegistro'] <> "") {
            $numero = $_POST['numeroRegistro'];
        } else {
            $numero = NULL;
            $continua = FALSE;
            $mensaje .= "numeroRegistro no ingresado - ";
        }
    } else {
        $numero = NULL;
    }
} else {
    $distrito = NULL;
    $continua = FALSE;
    $mensaje .= "Distrito no ingresado - ";
}

if (isset($_POST['idTipoDocumento']) && $_POST['idTipoDocumento'] <> "") {
    $idTipoDocumento = $_POST['idTipoDocumento'];
} else {
    $idTipoDocumento = NULL;
    $continua = FALSE;
    $mensaje .= 'Tipo de Documento no ingresado - ';
}

if (isset($_POST['numeroDocumento']) && $_POST['numeroDocumento'] <> "") {
    $numeroDocumento = $_POST['numeroDocumento'];
    //valida que el numero de documento ya no exista    
    $resDocumento = $registroDNU260Logic->numeroDocumentoExiste($idTipoDocumento, $numeroDocumento);
    if ($resDocumento['estado'] && $accion == '1') {
        $continua = FALSE;
        $mensaje .= $resDocumento['mensaje'].' - ';
    }
} else {
    $numeroDocumento = NULL;
    $continua = FALSE;
    $mensaje .= 'Numero de Documento no ingresado - ';
}
if (isset($_POST['numeroPasaporte']) && $_POST['numeroPasaporte'] <> "") {
    $numeroPasaporte = trim($_POST['numeroPasaporte']);    
} else {
    $numeroPasaporte = NULL;
    $continua = FALSE;
    $mensaje .= 'numeroPasaporte no ingresada - ';
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
    $continua = FALSE;
    $mensaje .= 'Fecha de Nacimiento no ingresada - ';
}
if (isset($_POST['fechaIngreso']) && $_POST['fechaIngreso'] <> "") {
    $fechaIngreso  = $_POST['fechaIngreso'];
} else {
    $fechaIngreso  = NULL;
    $continua = FALSE;
    $mensaje .= 'fechaIngreso  no ingresada - ';
}
if (isset($_POST['idPais']) && $_POST['idPais'] <> "") {
    $idPais = $_POST['idPais'];
} else {
    $idPais = NULL;
    $continua = FALSE;
    $mensaje .= 'idPais no ingresada - ';
}
if (isset($_POST['nacionalidad_buscar']) && $_POST['nacionalidad_buscar'] <> "") {
    $nacionalidad_buscar = $_POST['nacionalidad_buscar'];
} else {
    $nacionalidad_buscar = NULL;
    $continua = FALSE;
    $mensaje .= 'Nacionalidad no ingresada - ';
}
if (isset($_POST['estadoCivil']) && $_POST['estadoCivil'] <> "") {
    $estadoCivil = $_POST['estadoCivil'];
} else {
    $estadoCivil = NULL;
    $continua = FALSE;
    $mensaje .= 'estadoCivil no ingresada - ';
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
    $continua = FALSE;
    $mensaje .= 'Fecha de Titulo no ingresado - ';
}
if (isset($_POST['fechaInicioValidaTitulo']) && $_POST['fechaInicioValidaTitulo'] <> "") {
    $fechaInicioValidaTitulo = $_POST['fechaInicioValidaTitulo'];
    $fechaLimite = date('Y-m-d');
    if ($fechaInicioValidaTitulo > $fechaLimite) {
        $continua = FALSE;
        $mensaje .= 'Fecha de Inicio Valida Titulo no es valida - ';
    }
} else {
    $fechaInicioValidaTitulo = NULL;
}
if (isset($_POST['universidad']) && $_POST['universidad'] <> "") {
    $universidad = $_POST['universidad'];
} else {
    $universidad = NULL;
    $continua = FALSE;
    $mensaje .= 'Universidad no ingresada - ';
}
if (isset($_POST['especialidad']) && $_POST['especialidad'] <> "") {
    $especialidad = $_POST['especialidad'];
} else {
    $especialidad = NULL;
    $continua = FALSE;
    $mensaje .= 'especialidad no ingresada - ';
}
if (isset($_POST['sexo']) && $_POST['sexo'] <> "") {
    $sexo = $_POST['sexo'];
} else {
    $sexo = NULL;
    $continua = FALSE;
    $mensaje .= 'Sexo no ingresado - ';
}
if ($accion == 1) {
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
    if (isset($_POST['telefonoProfesional']) && $_POST['telefonoProfesional'] <> "") {
        $telefonoProfesional = $_POST['telefonoProfesional'];
    } else {
        $telefonoProfesional = NULL;
    }
}
$codigoPostalParticular = $_POST['codigoPostalParticular'];
if (isset($_POST['domicilioParticular']) && $_POST['domicilioParticular'] <> "") {
    $domicilioParticular = $_POST['domicilioParticular'];
} else {
    $domicilioParticular= NULL;
    $continua = FALSE;
    $mensaje .= 'domicilioParticular no ingresado - ';
}
if (isset($_POST['localidadParticular']) && $_POST['localidadParticular'] <> "") {
    $localidadParticular = $_POST['localidadParticular'];
} else {
    $localidadParticular = NULL;
    $continua = FALSE;
    $mensaje .= 'localidadParticular no ingresada - ';
}
if (isset($_POST['mail']) && $_POST['mail'] <> "") {
    $mail = $_POST['mail'];
} else {
    $mail = NULL;
    $continua = FALSE;
    $mensaje .= 'E-mail no ingresado - ';
}
if (isset($_POST['telefonoFijo']) && $_POST['telefonoFijo'] <> "") {
    $telefonoFijo = $_POST['telefonoFijo'];
} else {
    $telefonoFijo = NULL;
}
if (isset($_POST['telefonoMovil']) && $_POST['telefonoMovil'] <> "") {
    $telefonoMovil = $_POST['telefonoMovil'];
} else {
    $telefonoMovil = NULL;
}

if ($continua){
    switch ($accion) {
        case 1:
            if ($distrito == '1') {
                $resultado = $registroDNU260Logic->obtenerNumeroRegistro();
                if ($resultado['estado']) {
                    $numero = $resultado['numero'];
                } else {
                    $numero = NULL;
                }
            }
            if (isset($numero)) {
                $resultado = $registroDNU260Logic->agregarRegistro($numero, $apellido, $nombre, $idPais, $sexo, $fechaNacimiento, $estadoCivil, $idTipoDocumento, $numeroDocumento, $numeroPasaporte, $fechaIngreso, $universidad, $fechaTitulo, $especialidad, 
                    $domicilioParticular, $localidadParticular, $codigoPostalParticular, $entidad, $domicilioProfesional, $localidadProfesional, $codigoPostalProfesional, $telefonoFijo, $telefonoMovil, $mail, $fechaInicioValidaTitulo, $telefonoProfesional, $distrito);
            }
            break;

        case 2:
            $resultado = $registroDNU260Logic->borrarDatoLaboral($idRegistroLaboral, $idRegistro, $motivo, $matricula, $revalida, $convalida, $constanciaLaboral);
            break;
            
        case 3:
            $idRegistro = $_POST['id'];
            $resRegistro = $registroDNU260Logic->obtenerRegistroPorId($idRegistro);
            if ($resRegistro['estado']) {
                $datosAnteriores = $resRegistro['datos'];
                $numero = $datosAnteriores['numero'];
                $resultado = $registroDNU260Logic->modificarRegistro($apellido, $nombre, $idPais, $sexo, $fechaNacimiento, $estadoCivil, $idTipoDocumento, 
                    $numeroDocumento, $numeroPasaporte, $fechaIngreso, $universidad, $fechaTitulo, $especialidad, 
                    $domicilioParticular, $localidadParticular, $codigoPostalParticular, $telefonoFijo, $telefonoMovil, $mail, $fechaInicioValidaTitulo, $idRegistro, $numero, $distrito, $datosAnteriores);
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

//var_dump($resultado);
//exit;
?>

<body onLoad="document.forms['myForm'].submit()">
    <?php
    if ($resultado['estado']) {
    ?>
        <form name="myForm"  method="POST" action="../registro_dnu260_lista.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
        </form>
    <?php
    } else {        
    ?>
        <form name="myForm"  method="POST" action="../registro_dnu260_form.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
            <input type="hidden"  name="accion" id="accion" value="<?php echo $accion;?>">
            <input type="hidden"  name="apellido" id="apellido" value="<?php echo $apellido;?>">
            <input type="hidden"  name="nombre" id="nombre" value="<?php echo $nombre;?>">
            <input type="hidden"  name="estadoCivil" id="estadoCivil" value="<?php echo $estadoCivil;?>">
            <input type="hidden"  name="fechaNacimiento" id="fechaNacimiento" value="<?php echo $fechaNacimiento;?>">
            <input type="hidden"  name="fechaIngreso" id="fechaIngreso" value="<?php echo $fechaIngreso;?>">
            <input type="hidden"  name="idTipoDocumento" id="idTipoDocumento" value="<?php echo $idTipoDocumento;?>">
            <input type="hidden"  name="idPais" id="idPais" value="<?php echo $idPais;?>">
            <input type="hidden"  name="nacionalidad_buscar" id="nacionalidad_buscar" value="<?php echo $nacionalidad_buscar;?>">
            <input type="hidden"  name="universidad" id="universidad" value="<?php echo $universidad;?>">
            <input type="hidden"  name="fechaTitulo" id="fechaTitulo" value="<?php echo $fechaTitulo;?>">
            <input type="hidden"  name="especialidad" id="especialidad" value="<?php echo $especialidad;?>">
            <input type="hidden"  name="numeroDocumento" id="numeroDocumento" value="<?php echo $numeroDocumento;?>">
            <input type="hidden"  name="numeroPasaporte" id="numeroPasaporte" value="<?php echo $numeroPasaporte;?>">
            <input type="hidden"  name="sexo" id="sexo" value="<?php echo $sexo;?>">
            <input type="hidden"  name="domicilioParticular" id="domicilioParticular" value="<?php echo $domicilioParticular;?>">
            <input type="hidden"  name="localidadParticular" id="localidadParticular" value="<?php echo $localidadParticular;?>">
            <input type="hidden"  name="codigoPostalParticular" id="codigoPostalParticular" value="<?php echo $codigoPostalParticular;?>">
            <input type="hidden"  name="entidad" id="entidad" value="<?php echo $entidad;?>">
            <input type="hidden"  name="domicilioProfesional" id="domicilioProfesional" value="<?php echo $domicilioProfesional;?>">
            <input type="hidden"  name="localidadProfesional" id="localidadProfesional" value="<?php echo $localidadProfesional;?>">
            <input type="hidden"  name="codigoPostalProfesional" id="codigoPostalProfesional" value="<?php echo $codigoPostalProfesional;?>">
            <input type="hidden"  name="telefonoFijo" id="telefonoFijo" value="<?php echo $telefonoFijo;?>">
            <input type="hidden"  name="telefonoMovil" id="telefonoMovil" value="<?php echo $telefonoMovil;?>">
            <input type="hidden"  name="mail" id="mail" value="<?php echo $mail;?>">
            <input type="hidden"  name="fechaInicioValidaTitulo" id="fechaInicioValidaTitulo" value="<?php echo $fechaInicioValidaTitulo; ?>">
            <input type="hidden"  name="distrito" id="distrito" value="<?php echo $distrito; ?>">
            <input type="hidden"  name="numeroRegistro" id="numeroRegistro" value="<?php echo $numero; ?>">
        </form>
    <?php
    }
    ?>
</body>

