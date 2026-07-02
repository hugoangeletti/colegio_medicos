<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoDeudaAnualLogic.php');
$colegiadoDeudaAnualLogic = new colegiadoDeudaAnualLogic();
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/zonaLogic.php');
$zonaLogic = new zonaLogic();
require_once ('../dataAccess/lugarPagoLogic.php');
$lugarPagoLogic = new lugarPagoLogic();

set_time_limit(0);
$periodo = $_POST['periodo'];
$emitirPor = $_POST['emitirPor'];
$continuar = TRUE;
$idZona = NULL;
$idLocalidad = NULL;
$idAgremiacion = NULL;
$codigoPostal = NULL;
$calleDesde = NULL;
$calleHasta = NULL;
$mensaje = "";
$archivo = "";
if ($emitirPor == "P") {
    if (isset($_POST['idZona']) && $_POST['idZona'] <> "") {
        $idZona = $_POST['idZona'];
        if ($idZona == 4) {
            if (isset($_POST['codigoPostal']) && $_POST['codigoPostal'] <> "") {
                $codigoPostal = $_POST['codigoPostal'];
                if ($codigoPostal == "1900") {
                    $archivo = "LA_PLATA_";
                }
            } else {
                $continuar = FALSE;
                $mensaje .= "DEBE SELECCIONAR UN CODIGO POSTAL - ";
            }
            if (isset($_POST['calleDesde']) && $_POST['calleDesde'] <> "") {
                $calleDesde = $_POST['calleDesde'];
                $archivo .= $calleDesde;
            } else {
                $continuar = FALSE;
                $mensaje .= "DEBE SELECCIONAR UNA CALLE DESDE - ";
            }
            if (isset($_POST['calleHasta']) && $_POST['calleHasta'] <> "") {
                $calleHasta = $_POST['calleHasta'];
                $archivo .= "-".$calleHasta;
            } else {
                $continuar = FALSE;
                $mensaje .= "DEBE SELECCIONAR UNA CALLE HASTA - ";
            }
            if ($continuar && $calleHasta < $calleDesde) {
                $continuar = FALSE;
                $mensaje .= "DEBE SELECCIONAR UNA CALLE HASTA MAYOR A CALLE DESDE - ";    
            }
        } else {
            $resZona = $zonaLogic->obtenerZonaPorId($idZona);
            if ($resZona['estado']) {
                $archivo = $resZona['datos']['nombre']."_";
            } else {
                $archivo = $idZona."_";
            }
        }
    } else {
        $continuar = FALSE;
        $mensaje .= "DEBE SELECCIONAR UN PARTIDO - ";
    }
} else {
    if ($emitirPor == "A") {
        if (isset($_POST['idAgremiacion']) && $_POST['idAgremiacion'] <> "") {
            $idAgremiacion = $_POST['idAgremiacion'];
            $resAgremiacion = $lugarPagoLogic->obtenerLugarPagoPorId($idAgremiacion);
            if ($resAgremiacion['estado']) {
                $archivo = $resAgremiacion['datos']['nombre']."_";
            } else {
                $archivo = $idAgremiacion."_";
            }
        } else {
            $continuar = FALSE;
            $mensaje .= "DEBE SELECCIONAR UNA AGREMIACION - ";
        }
    } else {
        $continuar = FALSE;
        $mensaje .= "DEBE SELECCIONAR UN TIPO DE EMISION - ";
    }
}

if ($continuar) {
    $resultado = $colegiadoDeudaAnualLogic->obtenerColegiadoParaEmisionChequera($periodo, $emitirPor, $idZona, $codigoPostal, $idAgremiacion, $calleDesde, $calleHasta);

} else {
    $resultado['mensaje'] = $mensaje;
    $resultado['icono'] = 'glyphicon glyphicon-remove';
    $resultado['clase'] = 'alert alert-error';
}
//var_dump($resultado);
//exit;
?>
<body onLoad="document.forms['myForm'].submit()">
    <?php
    if (!$resultado['estado']) {
    ?>
        <form name="myForm"  method="POST" action="emision_colegiacion_anual_form.php">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
        </form>
    <?php
    } else {
        $colegiados = $resultado['datos'];
        $colegiados = serialize($colegiados);
    ?>
        <form name="myForm"  method="POST" action="emision_colegiacion_anual_imprimir.php">
            <input type="hidden" name="colegiados" id="colegiados" value="<?php echo $colegiados; ?>">
            <input type="hidden" name="archivo" id="archivo" value="<?php echo $archivo; ?>">
        </form>

    <?php
    }
    ?>
    <div class="row">
        <form name="myForm"  method="POST" action="emision_colegiacion_anual_form.php">
            <div class="col-md-12 text-center">
                <button type="submit"  class="btn btn-success btn-lg" >Volver </button>
            </div>
        </form>
    </div>
</body>

