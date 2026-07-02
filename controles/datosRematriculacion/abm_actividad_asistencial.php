<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/colegiadoRematriculacionLogic.php');

$continua = TRUE;
$mensaje = "";
$accion = NULL;
$colegiadoRematriculacionLogic = new colegiadoRematriculacionLogic();

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
    $idActividadAsistencial = NULL;
} else {
    //por modificacion debe venir el idConsultorio
    if ($accion == 3 && isset($_POST['idActividadAsistencial']) && $_POST['idActividadAsistencial']) {
        $idActividadAsistencial = $_POST['idActividadAsistencial'];
    } else {
        if ($accion == 2) {
            if (isset($_GET['id']) && $_GET['id'] <> "") {
                $idActividadAsistencial = $_GET['id'];
            } else {
                $continua = FALSE;
                $mensaje .= 'idActividadAsistencial no ingresado - ';
            }
        }
    }
}
if ($accion <> 2 && isset($_POST['idColegiado']) && $_POST['idColegiado'] <> "") {
    $idColegiado = $_POST['idColegiado'];
} else {
    if ($accion <> 2) {
        $continua = FALSE;
        $mensaje .= 'idColegiado no ingresado - ';
    } else {
        $resActividad = $colegiadoRematriculacionLogic->obtenerActividadAsistencialPorId($idActividadAsistencial);
        if ($resActividad['estado']) {
            $actividadAsistencia = $resActividad['datos'];
            $idColegiado = $actividadAsistencia['idColegiado'];
        } else {
            $idColegiado = NULL;
        }
    }
}
if ($continua){
    if ($accion <> 2) {
        if (isset($_POST['tipoInstitucion']) && $_POST['tipoInstitucion'] <> "") {
            $tipoInstitucion = $_POST['tipoInstitucion'];
        } else {
            $continua = FALSE;
            $mensaje .= 'tipoInstitucion no ingresado - ';
        }
        if (isset($_POST['numero']) && $_POST['numero'] <> "") {
            $numero = $_POST['numero'];
        } else {
            $continua = FALSE;
            $mensaje .= 'tipoInstitucion no ingresado - ';
        }
        if (isset($_POST['idEntidad']) && $_POST['idEntidad'] <> "") {
            $idEntidad = $_POST['idEntidad'];
        } else {
            $continua = FALSE;
            $mensaje .= 'idEntidad no ingresado - ';
        }
        if (isset($_POST['nombreEntidad']) && $_POST['nombreEntidad'] <> "") {
            $nombreEntidad = $_POST['nombreEntidad'];
        } else {
            $continua = FALSE;
            $mensaje .= 'nombreEntidad no ingresado - ';
        }
        if (isset($_POST['cargo']) && $_POST['cargo'] <> "") {
            $cargo = $_POST['cargo'];
        } else {
            $continua = FALSE;
            $mensaje .= 'cargo no ingresado - ';
        }
        if (isset($_POST['servicio']) && $_POST['servicio'] <> "") {
            $servicio = $_POST['servicio'];
        } else {
            $continua = FALSE;
            $mensaje .= 'servicio no ingresado - ';
        }
        if (isset($_POST['fechaDesdeHasta']) && $_POST['fechaDesdeHasta'] <> "") {
            $fechaDesdeHasta = $_POST['fechaDesdeHasta'];
        } else {
            $continua = FALSE;
            $mensaje .= 'fechaDesdeHasta no ingresado - ';
        }

        $resultado = $colegiadoRematriculacionLogic->guardarActividadAsistencial($idActividadAsistencial, $idColegiado, $tipoInstitucion, $idEntidad, $cargo, $servicio, $fechaDesdeHasta);
    } else {
        $estado = 'B'; //baja
        $resultado = $colegiadoRematriculacionLogic->borrarActividadAsistencial($idActividadAsistencial, $estado);
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
        if ($accion == 3) {
            //si entra por modificar
            $datosGet = "?id=".$idActividadAsistencial."&accion=".$accion;
        } else {
            $datosGet = "?idColegiado=".$idColegiado."&accion=".$accion;
        }
    ?>
        <form name="myForm"  method="POST" action="../datos_profesionales_actividad_form.php<?php echo $datosGet; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
            <?php if (isset($idActividadAsistencial) && $idActividadAsistencial <> "") { ?>
                    <input type="hidden"  name="idActividadAsistencial" id="idActividadAsistencial" value="<?php echo $idActividadAsistencial; ?>">
            <?php } ?>
            <input type="hidden"  name="tipoInstitucion" id="tipoInstitucion" value="<?php echo $tipoInstitucion;?>">
            <input type="hidden"  name="idEntidad" id="idEntidad" value="<?php echo $idEntidad;?>">
            <input type="hidden"  name="nombreEntidad" id="nombreEntidad" value="<?php echo $nombreEntidad;?>">
            <input type="hidden"  name="cargo" id="cargo" value="<?php echo $cargo;?>">
            <input type="hidden"  name="servicio" id="servicio" value="<?php echo $servicio;?>">
            <input type="hidden"  name="fechaDesdeHasta" id="fechaDesdeHasta" value="<?php echo $fechaDesdeHasta;?>">
        </form>
    <?php
    }
    ?>
</body>

