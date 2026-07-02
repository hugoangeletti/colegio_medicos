<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/colegiadoRematriculacionLogic.php');

$continua = TRUE;
$mensaje = "";
$accion = NULL;
if (isset($_POST['accion']) && $_POST['accion'] <> "") {
    $accion = $_POST['accion'];
} else {
    if (isset($_GET['accion']) && $_GET['accion'] <> "") {
        $accion = $_GET['accion'];
    } else {
        $continua = FALSE;
        $mensaje .= 'accion no ingresado - ';
    }
}
if ($accion == 1) {
    $idConsultorio = NULL;
} else {
    //por modificacion debe venir el idConsultorio
    if ($accion == 3 && isset($_POST['idConsultorio']) && $_POST['idConsultorio']) {
        $idConsultorio = $_POST['idConsultorio'];
    } else {
        if ($accion == 2) {
            if (isset($_GET['id']) && $_GET['id'] <> "") {
                $idConsultorio = $_GET['id'];
            } else {
                $continua = FALSE;
                $mensaje .= 'idConsultorio no ingresado - ';
            }
        }
    }
}

$colegiadoRematriculacionLogic = new colegiadoRematriculacionLogic();

if ($accion <> 2 && isset($_POST['idColegiado']) && $_POST['idColegiado'] <> "") {
    $idColegiado = $_POST['idColegiado'];
} else {
    if ($accion <> 2) {
        $continua = FALSE;
        $mensaje .= 'idColegiado no ingresado - ';
    } else {
        $resConsultorio = $colegiadoRematriculacionLogic->obtenerConsultorioDeclaradoPorId($idConsultorio);
        if ($resConsultorio['estado']) {
            $consultorio = $resConsultorio['datos'];
            $idColegiado = $consultorio['idColegiado'];
        } else {
            $idColegiado = NULL;
        }
    }
}
if ($continua){
    if ($accion <> 2) {
        if (isset($_POST['calle']) && $_POST['calle'] <> "") {
            $calle = $_POST['calle'];
        } else {
            $continua = FALSE;
            $calle = NULL;
        }
        if (isset($_POST['numero']) && $_POST['numero'] <> "") {
            $numero = $_POST['numero'];
        } else {
            $numero = NULL;
        }
        if (isset($_POST['piso']) && $_POST['piso'] <> "") {
            $piso = $_POST['piso'];
        } else {
            $piso = NULL;
        }
        if (isset($_POST['departamento']) && $_POST['departamento'] <> "") {
            $departamento = $_POST['departamento'];
        } else {
            $departamento = NULL;
        }
        if (isset($_POST['lateral']) && $_POST['lateral'] <> "") {
            $lateral = $_POST['lateral'];
        } else {
            $departamento = NULL;
        }
        if (isset($_POST['idEntidad']) && $_POST['idEntidad'] <> "") {
            $idEntidad = $_POST['idEntidad'];
        } else {
            $idEntidad = NULL;
        }
        if (isset($_POST['nombreEntidad']) && $_POST['nombreEntidad'] <> "") {
            $nombreEntidad = $_POST['nombreEntidad'];
        } else {
            $nombreEntidad = NULL;
        }
        if (isset($_POST['idLocalidad']) && $_POST['idLocalidad'] <> "") {
            $idLocalidad = $_POST['idLocalidad'];
        } else {
            $idLocalidad = NULL;
        }
        if (isset($_POST['nombreLocalidad']) && $_POST['nombreLocalidad'] <> "") {
            $nombreLocalidad = $_POST['nombreLocalidad'];
        } else {
            $nombreLocalidad = NULL;
        }
        if (isset($_POST['codigoPostal']) && $_POST['codigoPostal'] <> "") {
            $codigoPostal = $_POST['codigoPostal'];
        } else {
            $codigoPostal = NULL;
        }
        if (isset($_POST['telefono']) && $_POST['telefono'] <> "") {
            $telefono = $_POST['telefono'];
        } else {
            $telefono = NULL;
        }

        $resultado = $colegiadoRematriculacionLogic->guardarConsultorioDeclarado($idConsultorio, $idColegiado, $calle, $numero, $piso, $departamento, $lateral, $idEntidad, $idLocalidad, $codigoPostal, $telefono);
    } else {
        $idEstado = 2; //baja de domicilio
        $resultado = $colegiadoRematriculacionLogic->borrarConsultorioDeclarado($idConsultorio, $idEstado);
    }

} else {
    $resultado['mensaje'] = "ERROR EN LOS DATOS INGRESADOS: ".$mensaje;
    $resultado['icono'] = "glyphicon glyphicon-remove";
    $resultado['clase'] = "alert alert-danger";
    $resultado['estado'] = FALSE;
}
/*
var_dump($_GET);
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
        <form name="myForm" method="POST" action="../colegiado_datos_profesionales.php?idColegiado=<?php echo $idColegiado; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../datos_profesionales_consultorio_form.php?idColegiado=<?php echo $idColegiado; ?>&accion=<?php echo $accion; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
            <?php if (isset($idConsultorio) && $idConsultorio <> "") { ?>
                    <input type="hidden"  name="idConsultorio" id="idConsultorio" value="<?php echo $idConsultorio; ?>">
            <?php } ?>
            <input type="hidden"  name="calle" id="calle" value="<?php echo $calle;?>">
            <input type="hidden"  name="numero" id="numero" value="<?php echo $numero;?>">
            <input type="hidden"  name="piso" id="piso" value="<?php echo $piso;?>">
            <input type="hidden"  name="departamento" id="departamento" value="<?php echo $departamento;?>">
            <input type="hidden"  name="lateral" id="lateral" value="<?php echo $lateral;?>">
            <input type="hidden"  name="idEntidad" id="idEntidad" value="<?php echo $idEntidad;?>">
            <input type="hidden"  name="nombreEntidad" id="nombreEntidad" value="<?php echo $nombreEntidad;?>">
            <input type="hidden"  name="idLocalidad" id="idLocalidad" value="<?php echo $idLocalidad;?>">
            <input type="hidden"  name="nombreLocalidad" id="nombreLocalidad" value="<?php echo $nombreLocalidad;?>">
            <input type="hidden"  name="telefono" id="telefono" value="<?php echo $telefono;?>">
        </form>
    <?php
    }
    ?>
</body>

