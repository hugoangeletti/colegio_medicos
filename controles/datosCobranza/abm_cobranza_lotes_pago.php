<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/cobranzaLogic.php');
$cobranzaLogic = new cobranzaLogic();

$continua = TRUE;
$mensaje = "";
if (isset($_GET['idCobranza']) && $_GET['idCobranza'] <> "") {
    $idCobranza = $_GET['idCobranza'];
} else {
    $continua = FALSE;
    $mensaje .= "Falta idCobranza. ";
}
if (isset($_POST['accion']) && $_POST['accion'] <> "") {
    $accion = $_POST['accion'];
    if ($accion == 1) {
	    if (isset($_POST['tipoPago']) && $_POST['tipoPago'] <> "") {
	        $tipoPago = $_POST['tipoPago'];
	    } else {
	        $continua = FALSE;
	        $mensaje .= "Falta tipoPago. ";
	    }
	    if (isset($_POST['codigoPago']) && $_POST['codigoPago'] <> "") {
	        $codigoPago = $_POST['codigoPago'];
	    } else {
	        $continua = FALSE;
	        $mensaje .= "Falta codigoPago. ";
	    }
	    if (isset($_POST['recibo']) && $_POST['recibo'] <> "") {
	        $recibo = $_POST['recibo'];
	    } else {
	        $continua = FALSE;
	        $mensaje .= "Falta recibo. ";
	    }
	    if (isset($_POST['idColegiado']) && $_POST['idColegiado'] <> "") {
	        $idColegiado = $_POST['idColegiado'];
	    } else {
	        $idColegiado = NULL;
	    }
	    if (isset($_POST['idAsistente']) && $_POST['idAsistente'] <> "") {
	        $idAsistente = $_POST['idAsistente'];
	    } else {
	        $idAsistente = NULL;
	    }
	    if (!isset($idColegiado) && !isset($idAsistente)) {
	    	$continua = FALSE;
	        $mensaje .= "Falta idColegiado o idAsistente. ";
	    }
	    if (isset($_POST['periodo']) && $_POST['periodo'] <> "") {
	        $periodo = $_POST['periodo'];
	    } else {
		    if ($tipoPago == TIPO_PAGO_CURSO) {
		    	$periodo = NULL;
			} else {
		        $continua = FALSE;
		        $mensaje .= "Falta periodo. ";
		    }
	    }
	    if (isset($_POST['cuota']) && $_POST['cuota'] <> "") {
	        $cuota = $_POST['cuota'];
	    } else {
	        $continua = FALSE;
	        $mensaje .= "Falta cuota. ";
	    }
	    if (isset($_POST['importe']) && $_POST['importe'] <> "") {
	        $importe = $_POST['importe'];
	    } else {
	        $continua = FALSE;
	        $mensaje .= "Falta importe. ";
	    }
	    if (isset($_POST['fechaPago']) && $_POST['fechaPago'] <> "") {
	        $fechaPago = $_POST['fechaPago'];
	    } else {
	        $continua = FALSE;
	        $mensaje .= "Falta fechaPago. ";
	    }
    }
} else {
	if (isset($_GET['accion']) && $_GET['accion'] == 2) {
		$accion = $_GET['accion'];
		if (isset($_GET['idPago']) && $_GET['idPago'] <> "") {
		    $idCobranzaDetalle = $_GET['idPago'];
		} else {
		    $continua = FALSE;
		    $mensaje .= "Falta idPago. ";
		}
	} else {
    	$continua = FALSE;
    	$mensaje .= "Falta accion. ";
    }
}

if ($continua) {
    switch ($accion) {
        case '1':
            //$resultado = guardarCobranzaDetalle($idCobranza, $recibo, $periodo, $cuota, $tipoPago, $codigoPago, $fechaPago, $idColegiado, $idAsistente, $importe);
           	if ($tipoPago == TIPO_PAGO_TOTAL) {
           		$tipoComprobante = 8; //pago total de colegiacion
           	} else {
         		$tipoComprobante = 2; //cuota de colegiacion
            }
            $resultado = $cobranzaLogic->cargaPago($importe, $tipoComprobante, $recibo, $periodo, $fechaPago, $idCobranza, $idAsistente, $cuota);
            $linkVolver = "../cobranza_lotes_pago_form.php?id=".$idCobranza;
            break;
        
        case '2':
            $resultado = $cobranzaLogic->eliminarCobranzaDetalle($idCobranzaDetalle);
            $linkVolver = "../cobranza_lotes_detalle.php?id=".$idCobranza;
            break;

        default:
            $resultado['estado'] = FALSE;
            break;
    }
} else {
    $resultado['estado'] = FALSE;
    $resultado['mensaje'] = $mensaje;
}
/*
var_dump($_POST);
echo '<br>';
var_dump($resultado);
exit;
*/
?>
<body onLoad="document.forms['myForm'].submit()">	
    <form name="myForm"  method="POST" action="<?php echo $linkVolver; ?>">
        <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
        <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
        <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
    </form>
</body>


