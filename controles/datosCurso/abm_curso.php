<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/conection_pdo.php');
require_once ('../../dataAccess/cursos_pdo.php');

$continua = TRUE;
$mensaje = "";
if (isset($_POST['accion']) && $_POST['accion'] <> "") {
    $accion = $_POST['accion'];
    //si es una modificacion, verifico que venga el idSapCaratula
    if ($accion == "EDITAR") {
        if (isset($_POST['idCurso']) && $_POST['idCurso'] <> "") {
            $idCurso = $_POST['idCurso'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idCurso";
            $tipoMensaje = 'alert alert-danger';
        }
    } else {
        if ($accion == "AGREGAR") {
            $idCurso = $_POST['idCurso'];
        } else {
            $continua = FALSE;
            $mensaje .= "Accion erronea";
            $tipoMensaje = 'alert alert-danger';
        }
    }

    //verificar datos
    if (isset($_POST['titulo']) && $_POST['titulo'] <> "") {
        $titulo = $_POST['titulo'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta titulo";
        $tipoMensaje = 'alert alert-danger';
    }
    if (isset($_POST['fechaInicio']) && $_POST['fechaInicio'] <> "") {
        $fechaInicio = $_POST['fechaInicio'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta fechaInicio";
        $tipoMensaje = 'alert alert-danger';
    }
    if (isset($_POST['estadoCurso']) && $_POST['estadoCurso'] <> "") {
        $estadoCurso = $_POST['estadoCurso'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta estadoCurso";
        $tipoMensaje = 'alert alert-danger';
    }
    if (isset($_POST['director']) && $_POST['director'] <> "") {
        $director = $_POST['director'];
    } else {
        $director = NULL;
    }
    if (isset($_POST['coordinador']) && $_POST['coordinador'] <> "") {
        $coordinador = $_POST['coordinador'];
    } else {
        $coordinador = NULL;
    }
    if (isset($_POST['tema']) && $_POST['tema'] <> "") {
        $tema = $_POST['tema'];
    } else {
        $tema = NULL;
    }
    if (isset($_POST['dias']) && $_POST['dias'] <> "") {
        $dias = $_POST['dias'];
    } else {
        $dias = NULL;
    }
    if (isset($_POST['fechas']) && $_POST['fechas'] <> "") {
        $fechas = $_POST['fechas'];
    } else {
        $fechas = NULL;
    }
    if (isset($_POST['salon']) && $_POST['salon'] <> "") {
        $salon = $_POST['salon'];
    } else {
        $salon = NULL;
    }
    if (isset($_POST['lugar']) && $_POST['lugar'] <> "") {
        $lugar = $_POST['lugar'];
    } else {
        $lugar = NULL;
    }
    if (isset($_POST['vigenciaHasta']) && $_POST['vigenciaHasta'] <> "") {
        $vigenciaHasta = $_POST['vigenciaHasta'];
    } else {
        $vigenciaHasta = NULL;
    }
    if (isset($_POST['inscripcionDesde']) && $_POST['inscripcionDesde'] <> "") {
        $inscripcionDesde = $_POST['inscripcionDesde'];
    } else {
        $inscripcionDesde = NULL;
    }
    if (isset($_POST['inscripcionHasta']) && $_POST['inscripcionHasta'] <> "") {
        $inscripcionHasta = $_POST['inscripcionHasta'];
    } else {
        $inscripcionHasta = NULL;
    }
} else {
    $idCurso = isset($_GET['id']) ? $_GET['id'] : NULL;
    if (isset($_GET['finalizar'])) {
        if (empty($idCurso)) {
            $continua = FALSE;
            $mensaje .= "Falta idCurso";
            $tipoMensaje = 'alert alert-danger';
        }
    } else {
        if (isset($_GET['tesoreria'])) {
            if (empty($idCurso)) {
                $continua = FALSE;
                $mensaje .= "Falta idCurso";
                $tipoMensaje = 'alert alert-danger';
            } else {
                $idCurso = $_GET['id'];
                $accion = "TESORERIA";

                if (isset($_POST['porcentajeRetencionColegio']) && $_POST['porcentajeRetencionColegio'] <> "") {
                    $porcentajeRetencionColegio = $_POST['porcentajeRetencionColegio'];
                } else {
                    $continua = FALSE;
                    $mensaje .= "Falta porcentajeRetencionColegio";
                    $tipoMensaje = 'alert alert-danger';
                }
                if (isset($_POST['valorCuotaLiquidacion']) && $_POST['valorCuotaLiquidacion'] <> "") {
                    $valorCuotaLiquidacion = $_POST['valorCuotaLiquidacion'];
                } else {
                    $valorCuotaLiquidacion = NULL;
                }
            }
        } else {
            $continua = FALSE;
            $mensaje .= "Ingreso incorrecto";
            $tipoMensaje = 'alert alert-danger';    
        }
    }
}

if ($continua) {
    $cursos_pdo = new cursos_pdo();
    switch ($accion) {
        case 'AGREGAR':
            $datosAnteriores = NULL;
            $resultado = $cursos_pdo->guardarCurso($idCurso, $titulo, $tema, $dias, $fechas, $salon, $lugar, $director, $coordinador, $fechaInicio, $vigenciaHasta, $estadoCurso, $inscripcionDesde, $inscripcionHasta, $datosAnteriores);
            break;

        case 'EDITAR':
        case 'FINALIZAR':
            $resCurso = $cursos_pdo->obtenerCursoPorId($idCurso);
            if ($resCurso['estado']) {
                $datosAnteriores = $resCurso['datos'];
                if ($accion == 'FINALIZAR') {
                    $resultado = $cursos_pdo->finalizarCurso($idCurso, $datosAnteriores);
                } else {
                    $resultado = $cursos_pdo->guardarCurso($idCurso, $titulo, $tema, $dias, $fechas, $salon, $lugar, $director, $coordinador, $fechaInicio, $vigenciaHasta, $estadoCurso, $inscripcionDesde, $inscripcionHasta, $datosAnteriores);
                }
            } else {
                $continua = FALSE;
                $mensaje = $resCurso['mensaje'];
            }
            break;

        case 'TESORERIA':
            $resultado = $cursos_pdo->tesoreriaCurso($idCurso, $valorCuotaLiquidacion, $porcentajeRetencionColegio);
            break;

        default:
            break;
    }
} else {
    $resultado['clase'] = $tipoMensaje;
    $resultado['mensaje'] = $mensaje;
    $resultado['icono'] = "";
    $resultado['estado'] = FALSE;
}
/*
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
        <form name="myForm"  method="POST" action="../curso_listado.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
        </form>
    <?php
    } else {
        $link = "../curso_form.php";
        if ($accion == "AGREGAR") {
            $link .= "?agregar";
        } else {
            if ($accion == "EDITAR") {
                $link .= "?editar&id=".$idCurso;
            } else {
                $link = "../curso_listado.php";
            }
        }
        ?>
        <form name="myForm"  method="POST" action="<?php echo $link; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
            <input type="hidden"  name="titulo" id="titulo" value="<?php echo $titulo; ?>">
            <input type="hidden"  name="tema" id="tema" value="<?php echo $tema; ?>">
            <input type="hidden"  name="dias" id="dias" value="<?php echo $dias; ?>">
            <input type="hidden"  name="fechas" id="fechas" value="<?php echo $fechas; ?>">
            <input type="hidden"  name="director" id="director" value="<?php echo $director; ?>">
            <input type="hidden"  name="coordinador" id="coordinador" value="<?php echo $coordinador; ?>">
            <input type="hidden"  name="lugar" id="lugar" value="<?php echo $lugar; ?>">
            <input type="hidden"  name="salon" id="salon" value="<?php echo $salon; ?>">
            <input type="hidden"  name="fechaInicio" id="fechaInicio" value="<?php echo $fechaInicio; ?>">
            <input type="hidden"  name="vigenciHasta" id="vigenciHasta" value="<?php echo $vigenciHasta; ?>">
            <input type="hidden"  name="estadoCurso" id="estadoCurso" value="<?php echo $estadoCurso; ?>">
        </form>
    <?php
    }
    ?>
</body>

