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
    if ($accion == "EDITAR") {
        if (isset($_POST['idCursoCuota']) && $_POST['idCursoCuota'] <> "") {
            $idCursoCuota = $_POST['idCursoCuota'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idCursoCuota";
            $tipoMensaje = 'alert alert-danger';
        }
    } else {
        if ($accion == "AGREGAR") {
            $idCursoCuota = NULL;
        }
    }
    if (isset($_POST['idCurso']) && $_POST['idCurso'] <> "") {
        $idCurso = $_POST['idCurso'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta idCurso - ";
        $tipoMensaje = 'alert alert-danger';
    }

    //verificar datos
    if (isset($_POST['cuota']) && $_POST['cuota'] <> "") {
        $cuota = $_POST['cuota'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta cuota";
        $tipoMensaje = 'alert alert-danger';
    }
    if (isset($_POST['detalleCuota']) && $_POST['detalleCuota'] <> "") {
        $detalleCuota = $_POST['detalleCuota'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta detalleCuota";
        $tipoMensaje = 'alert alert-danger';
    }
    if (isset($_POST['importe']) && $_POST['importe'] <> "") {
        $importe = $_POST['importe'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta importe";
        $tipoMensaje = 'alert alert-danger';
    }
    if (isset($_POST['fechaVencimiento']) && $_POST['fechaVencimiento'] <> "") {
        $fechaVencimiento = $_POST['fechaVencimiento'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta fechaVencimiento";
        $tipoMensaje = 'alert alert-danger';
    }
} else {
    if (isset($_GET['borrar'])) {
        $accion = "BORRAR";
        if (isset($_GET['idCurso']) && $_GET['idCurso'] <> "") {
            $idCurso = $_GET['idCurso'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idCurso - ";
            $tipoMensaje = 'alert alert-danger';
        }        
        if (isset($_GET['id']) && $_GET['id'] <> "") {
            $idCursoCuota = $_GET['id'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta id - ";
            $tipoMensaje = 'alert alert-danger';
        }        
    } else {
        $continua = FALSE;
        $mensaje .= "Falta idColegiado";
        $tipoMensaje = 'alert alert-danger';    
    }
}

if ($continua) {
    $cursos_pdo = new cursos_pdo();
    switch ($accion) {
        case 'AGREGAR':
            $datosAnteriores = NULL;
            $resultado = $cursos_pdo->guardarCuotaCurso($idCursoCuota, $idCurso, $cuota, $detalleCuota, $importe, $fechaVencimiento, $datosAnteriores);
            break;

        case 'EDITAR':
            $resCuota = $cursos_pdo->obtenerCuotaCursoPorIdCursoCuota($idCursoCuota);
            if ($resCuota['estado']) {
                $datosAnteriores = $resCuota['datos'];
                $resultado = $cursos_pdo->guardarCuotaCurso($idCursoCuota, $idCurso, $cuota, $detalleCuota, $importe, $fechaVencimiento, $datosAnteriores);
            } else {
                $continua = FALSE;
                $mensaje = $resCuota['mensaje'];
            }

            break;

        case 'BORRAR':
            $resCuota = $cursos_pdo->obtenerCuotaCursoPorIdCursoCuota($idCursoCuota);
            if ($resCuota['estado']) {
                $datosAnteriores = $resCuota['datos'];
                $resultado = $cursos_pdo->borrarCuotaCurso($idCursoCuota, $datosAnteriores);
            } else {
                $continua = FALSE;
                $mensaje = $resCuota['mensaje'];
            }
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
        <form name="myForm"  method="POST" action="../curso_cuotas.php?id=<?php echo $idCurso; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
        </form>
    <?php
    } else {
        if ($accion == "AGREGAR") {
            $link = "../curso_cuotas_form.php?idCurso=".$idCurso."&agregar";
        } else {
            if ($accion == "EDITAR") {
                $link = "../curso_cuotas_form.php?idCurso=".$idCurso."&editar&id=".$idCursoCuota;
            } else {
                if ($accion == "BORRAR") {
                    $link = "../curso_cuotas.php?id=".$idCurso;
                } else {
                    $link = "../curso_listado.php";
                }
            }
        }
        ?>
        <form name="myForm"  method="POST" action="<?php echo $link; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
            <input type="hidden"  name="cuota" id="cuota" value="<?php echo $cuota; ?>">
            <input type="hidden"  name="detalleCuota" id="detalleCuota" value="<?php echo $detalleCuota; ?>">
            <input type="hidden"  name="importe" id="importe" value="<?php echo $importe; ?>">
            <input type="hidden"  name="fechaVencimiento" id="fechaVencimiento" value="<?php echo $fechaVencimiento; ?>">
        </form>
    <?php
    }
    ?>
</body>

