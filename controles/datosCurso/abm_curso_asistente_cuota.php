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
        if (isset($_POST['idCursosAsistenteCuota']) && $_POST['idCursosAsistenteCuota'] <> "") {
            $idCursosAsistenteCuota = $_POST['idCursosAsistenteCuota'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idCursosAsistenteCuota";
            $tipoMensaje = 'alert alert-danger';
        }
    } else {
        if ($accion == "AGREGAR") {
            $idCursosAsistenteCuota = NULL;
        }
    }
    if (isset($_POST['idCursosAsistente']) && $_POST['idCursosAsistente'] <> "") {
        $idCursosAsistente = $_POST['idCursosAsistente'];
    } else {
        $continua = FALSE;
        $mensaje .= "Falta idCursosAsistente - ";
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
    if (isset($_POST['fechaPago']) && $_POST['fechaPago'] <> "") {
        $fechaPago = $_POST['fechaPago'];
    } else {
        $fechaPago = NULL;
    }
    if (isset($_POST['recibo']) && $_POST['recibo'] <> "" && $_POST['recibo'] <> "0") {
        $recibo = $_POST['recibo'];
    } else {
        $recibo = NULL;
    }
} else {
    if (isset($_GET['borrar']) || isset($_GET['asiste'])) {
        if (isset($_GET['borrar'])) {
            $accion = "BORRAR";
        } else {
            $accion = "ASISTE";
        }
        if (isset($_GET['idCursosAsistente']) && $_GET['idCursosAsistente'] <> "") {
            $idCursosAsistente = $_GET['idCursosAsistente'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idCursosAsistente - ";
            $tipoMensaje = 'alert alert-danger';
        }        
        if (isset($_GET['id']) && $_GET['id'] <> "") {
            $idCursosAsistenteCuota = $_GET['id'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idCursosAsistenteCuota - ";
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
    if (isset($idCursosAsistenteCuota)) {
        $resCuota = $cursos_pdo->obtenerCursosAsistenteCuotaPorId($idCursosAsistenteCuota);
        if ($resCuota['estado']) {
            $datosAnteriores = $resCuota['datos'];
        } else {
            $continua = FALSE;
            $mensaje = $resCuota['mensaje'];
        }
    } else {
        $datosAnteriores = NULL;
    }
    if ($continua) {
        switch ($accion) {
            case 'AGREGAR':
                $asiste = 'S';
                $borrado = 0;
                $resultado = $cursos_pdo->guardarCuotaAsistente($idCursosAsistenteCuota, $idCursosAsistente, $cuota, $detalleCuota, $importe, $fechaVencimiento, $fechaPago, $recibo, $datosAnteriores);
                break;

            case 'EDITAR':
                $asiste = 'S';
                $borrado = 0;
                $resultado = $cursos_pdo->guardarCuotaAsistente($idCursosAsistenteCuota, $idCursosAsistente, $cuota, $detalleCuota, $importe, $fechaVencimiento, $fechaPago, $recibo, $datosAnteriores);
                break;

            case 'BORRAR':
                $resultado = $cursos_pdo->borrarCuotaAsistenteCurso($idCursosAsistenteCuota, $datosAnteriores);
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
        <form name="myForm"  method="POST" action="../curso_asistentes_cuotas.php?id=<?php echo $idCursosAsistente; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
        </form>
    <?php
    } else {
        if ($accion == "AGREGAR") {
            $link = "../curso_asistentes_cuotas_form.php?idCursosAsistente=".$idCursosAsistente."&id=".$idCursosAsistenteCuota."&agregar";
        } else {
            if ($accion == "EDITAR") {
                $link = "../curso_asistentes_cuotas_form.php?idCursosAsistente=".$idCursosAsistente."&editar&id=".$idCursosAsistenteCuota;
            } else {
                if ($accion == "BORRAR") {
                    $link = "../curso_asistentes_cuotas.php?id=".$idCursosAsistente;
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
            <input type="hidden"  name="fechaPago" id="fechaPago" value="<?php echo $fechaPago; ?>">
            <input type="hidden"  name="recibo" id="recibo" value="<?php echo $recibo; ?>">
        </form>
    <?php
    }
    ?>
</body>

