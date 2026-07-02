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
    if (isset($_POST['idColegiadoTitulo']) && $_POST['idColegiadoTitulo'] <> "") {
        $idColegiadoTitulo = $_POST['idColegiadoTitulo'];
        $colegiadoLogic = new colegiadoLogic();
        $resTitulo = $colegiadoLogic->obtenerTitulosPorIdColegiadoTitulo($idColegiadoTitulo);
        if ($resTitulo['estado']) {
            $datosAnteriores = $resTitulo['datos'];
        } else {
            $continua = FALSE;
            $mensaje = $resTitulo['mensaje'];
        }
    } else {
        $idColegiadoTitulo = NULL;
        $datosAnteriores['idTipoTitulo'] = NULL;
        $datosAnteriores['fechaTitulo'] = NULL;
        $datosAnteriores['idUniversidad'] = NULL;
        $datosAnteriores['tituloDigital'] = NULL;
        //$continua = FALSE;
        //$mensaje .= 'ColegiadoTitulo no ingresado - ';
    }
} else {
    $continua = FALSE;
    $mensaje .= 'Colegiado no ingresado - ';
}

if (isset($_POST['idTipoTitulo']) && $_POST['idTipoTitulo'] <> "") {
    $idTipoTitulo = $_POST['idTipoTitulo'];
} else {
    $continua = FALSE;
    $mensaje .= 'Tipo de Título no ingresado - ';
}
if (isset($_POST['fechaTitulo']) && $_POST['fechaTitulo'] <> "") {
    $fechaTitulo = $_POST['fechaTitulo'];
} else {
    $continua = FALSE;
    $mensaje .= 'Fecha de Titulo no ingresado - ';
}
if (isset($_POST['idUniversidad']) && $_POST['idUniversidad'] <> "") {
    $idUniversidad = trim($_POST['idUniversidad']);
} else {
    $continua = FALSE;
    $mensaje .= 'Universidad no ingresado - ';
}
if (isset($_POST['universidad_buscar']) && $_POST['universidad_buscar'] <> "") {
    $universidad = trim($_POST['universidad_buscar']);
} else {
    $continua = FALSE;
    $mensaje .= 'Universidad no ingresado - ';
}
if (isset($_POST['tituloDigital']) && $_POST['tituloDigital'] <> "") {
    $tituloDigital = trim($_POST['tituloDigital']);
} else {
    $continua = FALSE;
    $mensaje .= 'tituloDigital no ingresado - ';
}

if ($continua){
    //si hubo cambio hacemos la actualizacion
    if ($datosAnteriores['idTipoTitulo'] <> $idTipoTitulo || $datosAnteriores['fechaTitulo'] <> $fechaTitulo || $datosAnteriores['idUniversidad'] <> $idUniversidad 
        || $datosAnteriores['tituloDigital'] <> $tituloDigital) {
        //echo 'cambia<br>';
        $resultado = $colegiadoLogic->modificarTitulo($idColegiadoTitulo, $idColegiado, $idTipoTitulo, $fechaTitulo, $idUniversidad, $datosAnteriores, $tituloDigital);
    } else {
        $resultado['estado'] = $continua;
    }
} else {
    $resultado['mensaje'] = "ERROR EN LOS DATOS INGRESADOS: ".$mensaje;
    $resultado['icono'] = "glyphicon glyphicon-remove";
    $resultado['clase'] = "alert alert-danger";
    $resultado['estado'] = $continua;
}
/*
var_dump($_POST);
echo '<br>';
var_dump($datosAnteriores);
echo '<br>';
var_dump($resultado);
exit;
*/
?>

<body onLoad="document.forms['myForm'].submit()">
    <?php
    if ($resultado['estado']) {
    ?>
        <form name="myForm"  method="POST" action="../colegiado_consulta.php?idColegiado=<?php echo $idColegiado; ?>&id=<?php echo $idColegiadoTitulo; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
        </form>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="../titulo_actualizar.php?idColegiado=<?php echo $idColegiado ?>&id=<?php echo $idColegiadoTitulo; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
            <input type="hidden"  name="idTipoTitulo" id="idTipoTitulo" value="<?php echo $idTipoTitulo;?>">
            <input type="hidden"  name="fechaTitulo" id="fechaTitulo" value="<?php echo $fechaTitulo;?>">
            <input type="hidden"  name="idUniversidad" id="idUniversidad" value="<?php echo $idUniversidad;?>">
            <input type="hidden"  name="universidad" id="universidad" value="<?php echo $universidad;?>">
        </form>
    <?php
    }
    ?>
</body>

