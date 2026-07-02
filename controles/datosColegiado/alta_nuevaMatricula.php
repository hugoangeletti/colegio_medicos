<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/personaLogic.php');
$personaLogic = new personaLogic();
require_once ('../../dataAccess/colegiadoLogic.php');
require_once ('../../dataAccess/colegiadoContactoLogic.php');
require_once ('../../dataAccess/colegiadoDomicilioLogic.php');

$continua = TRUE;
$mensaje = "";
$tipoDocumento = 3;

if (isset($_POST['numeroDocumento']) && $_POST['numeroDocumento'] <> "") {
    $numeroDocumento = $_POST['numeroDocumento'];
    //valida que el numero de documento ya no exista
    $resDocumento = $personaLogic->numeroDocumentoExiste($tipoDocumento, $numeroDocumento);
    if ($resDocumento['estado']) {
        $continua = FALSE;
        $mensaje .= $resDocumento['mensaje'].' - ';
    }
} else {
    $numeroDocumento = NULL;
    $continua = FALSE;
    $mensaje .= 'Numero de Documento no ingresado - ';
}
if (isset($_POST['tomo'])) {
    $tomo = $_POST['tomo'];
} else {
    $tomo = NULL;
    $continua = FALSE;
    $mensaje .= 'Tomo no ingresado - ';
}
if (isset($_POST['folio'])) {
    $folio = $_POST['folio'];
} else {
    $folio = NULL;
    $continua = FALSE;
    $mensaje .= 'Folio no ingresado - ';
}
if (isset($_POST['matricula'])) {
    $matricula = trim($_POST['matricula']);
    //verificar que la matricula ya no existe
    $colegiadoLogic = new colegiadoLogic();
    $resMatricula = $colegiadoLogic->matriculaExiste($matricula);
    if ($resMatricula['estado']) {
        $continua = FALSE;
        $mensaje .= $resMatricula['mensaje'].' - ';
    }
} else {
    $matricula = NULL;
    $continua = FALSE;
    $mensaje .= 'Matricula no ingresada - ';
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
if (isset($_POST['fechaMatriculacion'])) {
    $fechaMatriculacion = $_POST['fechaMatriculacion'];
} else {
    $fechaMatriculacion = NULL;
    $continua = FALSE;
    $mensaje .= 'Fecha de Matriculacion no ingresada - ';
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
if (isset($_POST['idTipoTitulo']) && $_POST['idTipoTitulo'] <> "") {
    $idTipoTitulo = $_POST['idTipoTitulo'];
} else {
    $idTipoTitulo = NULL;
    $continua = FALSE;
    $mensaje .= 'Tipo de Titulo no ingresado - ';
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
if (isset($_POST['tituloDigital']) && $_POST['tituloDigital'] <> "") {
    $tituloDigital = $_POST['tituloDigital'];
} else {
    $tituloDigital = NULL;
    $continua = FALSE;
    $mensaje .= 'tituloDigital no ingresado - ';
}
if (isset($_POST['idUniversidad']) && $_POST['idUniversidad'] <> "") {
    $idUniversidad = $_POST['idUniversidad'];
} else {
    $idUniversidad = NULL;
    $continua = FALSE;
    $mensaje .= 'Universidad no ingresada - ';
}
if (isset($_POST['universidad_buscar']) && $_POST['universidad_buscar'] <> "") {
    $universidad_buscar = $_POST['universidad_buscar'];
} else {
    $universidad_buscar = NULL;
    $continua = FALSE;
    $mensaje .= 'Universidad no ingresada - ';
}
if (isset($_POST['sexo']) && $_POST['sexo'] <> "") {
    $sexo = $_POST['sexo'];
} else {
    $sexo = NULL;
    $continua = FALSE;
    $mensaje .= 'Sexo no ingresado - ';
}
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
if (isset($_POST['mail']) && $_POST['mail'] <> "") {
    $mail = $_POST['mail'];
} else {
    $mail = NULL;
    $continua = FALSE;
    $mensaje .= 'E-mail no ingresado - ';
}
if (isset($_POST['telefonoFijo']) && $_POST['telefonoFijo'] <> "") {
//if (isset($_POST['telefonoFijoPrefijo']) && $_POST['telefonoFijoPrefijo'] <> "" 
//        && isset($_POST['telefonoFijo1']) && $_POST['telefonoFijo1'] <> ""
//        && isset($_POST['telefonoFijo2']) && $_POST['telefonoFijo2'] <> "") {
    $telefonoFijo = $_POST['telefonoFijo'];
//    $telefonoFijoPrefijo = $_POST['telefonoFijoPrefijo'];
//    $telefonoFijo1 = $_POST['telefonoFijo1'];
//    $telefonoFijo2 = $_POST['telefonoFijo2'];
//    $telefonoFijo = $telefonoFijoPrefijo.'-'.$telefonoFijo1.'-'.$telefonoFijo2;
} else {
    $telefonoFijo = NULL;
    $continua = FALSE;
    $mensaje .= 'Telefono fijo no ingresado - ';
}
if (isset($_POST['telefonoMovil']) && $_POST['telefonoMovil'] <> "") {
    $telefonoMovil = $_POST['telefonoMovil'];
} else {
    $telefonoMovil = NULL;
    $continua = FALSE;
    $mensaje .= 'Telefono Movil no ingresado - ';
}
if (isset($_POST['matriculaNacional']) && $_POST['matriculaNacional'] != "") {
    $matriculaNacional = $_POST['matriculaNacional'];
} else {
    $matriculaNacional = NULL;
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

if (!isset($_GET['tipo'])) {
    $distritoOrigen = 1;
    //$tipoMovimiento = NULL;
    //$fechaOtroDistrito = NULL;
    $tipoIngreso = "";
} else {
    //$tipoMovimiento = $_POST['tipoMovimiento'];
    //$fechaOtroDistrito = $_POST['fechaOtroDistrito'];
    //$distritoOrigen = $_POST['distritoOrigen'];
    $distritoOrigen = substr($matricula, 0, 1);
    $tipoIngreso = "&tipo=".$_GET['tipo'];
}

if ($continua){
    //if ($otroDistrito) {
    //    $estadoMatricular = $tipoMovimiento;
    //} else {
    //le asigno activo siempre, luego para los que vienen de otro distrito le cambiamos el estado 
    //por el correspondiente al movimiento que se le genera
    $estadoMatricular = 1;
    if (isset($_GET['tipo']) && $_GET['tipo'] == 'baja') {
        $estadoMatricular = 36;
    }
    //}
    $resultado = $colegiadoLogic->agregarColegiado($tomo, $folio, $fechaMatriculacion, $matricula, $estadoMatricular, 
            $apellido, $nombre, $sexo, $tipoDocumento, $numeroDocumento, $fechaNacimiento, 
            $idPaises, $matriculaNacional, $distritoOrigen, $calle, $numero, $piso, $depto, $lateral, $idLocalidad, 
            $codigoPostal, $telefonoFijo, $telefonoMovil, $mail, $idTipoTitulo, $fechaTitulo, $idUniversidad, $tituloDigital);
    if ($resultado['estado']) {
        $idColegiado = $resultado['idColegiado'];

        /*
        if (isset($tipoMovimiento) && $tipoMovimiento <> '') {
            //si es algun cambio de distrito, genero el movimiento en mesa de entradas
            $resultadoME = $mesaEntradaAltaMatriculaLogic->realizarAltaMesaEntrada($idColegiado, $tipoMovimiento, $distritoOrigen);
            if ($resultadoME['estado']) {
                $idMesaEntrada = $resultadoME['idMesaEntrada'];
            } else {
                $idMesaEntrada = NULL;
            }
        }
         * 
         */
    } else {
        $idColegiado = NULL;
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
        if (isset($_GET['tipo']) && $_GET['tipo'] == 'baja') {
            //$action = "../colegiado_consulta.php?idColegiado=".$idColegiado;
            $action = "../colegiado_nuevo_baja.php?idColegiado=".$idColegiado;
        } else {
            $action = "../colegiado_nuevo_archivos.php?idColegiado=".$idColegiado.$tipoIngreso;
        }
    ?>
        <form name="myForm"  method="POST" action="<?php echo $action; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
        </form>
    <?php
    } else {
        if ($tipoIngreso != "") {
            $action = "../colegiado_nuevo.php?tipo=".$_GET['tipo'];
        } else {
            $action = "../colegiado_nuevo.php";
        }
    ?>
        <form name="myForm"  method="POST" action="<?php echo $action; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
            <input type="hidden"  name="tomo" id="tomo" value="<?php echo $tomo;?>">
            <input type="hidden"  name="folio" id="folio" value="<?php echo $folio;?>">
            <input type="hidden"  name="matricula" id="matricula" value="<?php echo $matricula;?>">
            <input type="hidden"  name="apellido" id="apellido" value="<?php echo $apellido;?>">
            <input type="hidden"  name="nombre" id="nombre" value="<?php echo $nombre;?>">
            <input type="hidden"  name="fechaMatriculacion" id="fechaMatriculacion" value="<?php echo $fechaMatriculacion;?>">
            <input type="hidden"  name="fechaNacimiento" id="fechaNacimiento" value="<?php echo $fechaNacimiento;?>">
            <input type="hidden"  name="tipoDocumento" id="tipoDocumento" value="<?php echo $tipoDocumento;?>">
            <input type="hidden"  name="idPaises" id="idPaises" value="<?php echo $idPaises;?>">
            <input type="hidden"  name="nacionalidad_buscar" id="nacionalidad_buscar" value="<?php echo $nacionalidad_buscar;?>">
            <input type="hidden"  name="idTipoTitulo" id="idTipoTitulo" value="<?php echo $idTipoTitulo;?>">
            <input type="hidden"  name="idUniversidad" id="idUniversidad" value="<?php echo $idUniversidad;?>">
            <input type="hidden"  name="universidad_buscar" id="universidad_buscar" value="<?php echo $universidad_buscar;?>">
            <input type="hidden"  name="fechaTitulo" id="fechaTitulo" value="<?php echo $fechaTitulo;?>">
            <input type="hidden"  name="numeroDocumento" id="numeroDocumento" value="<?php echo $numeroDocumento;?>">
            <input type="hidden"  name="matriculaNacional" id="matriculaNacional" value="<?php echo $matriculaNacional;?>">
            <input type="hidden"  name="sexo" id="sexo" value="<?php echo $sexo;?>">
            <input type="hidden"  name="calle" id="calle" value="<?php echo $calle;?>">
            <input type="hidden"  name="numero" id="numero" value="<?php echo $numero;?>">
            <input type="hidden"  name="lateral" id="lateral" value="<?php echo $lateral;?>">
            <input type="hidden"  name="idLocalidad" id="idLocalidad" value="<?php echo $idLocalidad;?>">
            <input type="hidden"  name="localidad_buscar" id="localidad_buscar" value="<?php echo $localidad_buscar;?>">
            <input type="hidden"  name="codigoPostal" id="codigoPostal" value="<?php echo $codigoPostal;?>">
            <input type="hidden"  name="telefonoFijo" id="telefonoFijo" value="<?php echo $telefonoFijo;?>">
            <input type="hidden"  name="telefonoMovil" id="telefonoMovil" value="<?php echo $telefonoMovil;?>">
            <input type="hidden"  name="mail" id="mail" value="<?php echo $mail;?>">
            <input type="hidden"  name="estadoMatricular" id="estadoMatricular" value="<?php echo $estadoMatricular;?>">
            <?php
            /*
            if ($otroDistrito) {
                ?>
                <input type="hidden"  name="tipoMovimiento" id="tipoMovimiento" value="<?php echo $tipoMovimiento;?>">
                <input type="hidden"  name="distritoOrigen" id="distritoOrigen" value="<?php echo $distritoOrigen;?>">
                <?php
            }
             * 
             */
            ?>
        </form>
    <?php
    }
    ?>
</body>

