<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/cobranzaLogic.php');
$cobranzaLogic = new cobranzaLogic();

$continua = TRUE;
$mensaje = "";
if (isset($_POST['accion']) && $_POST['accion'] <> "") {
    $accion = $_POST['accion'];
	if ($accion <> 1) {
		if (isset($_POST['idCobranza']) && $_POST['idCobranza'] <> "") {
	    	$idCobranza = $_POST['idCobranza'];
	    } else {
	    	$continua = FALSE;
	        $mensaje .= "Falta idCobranza. ";
	    }
	} else {
	    $idCobranza = NULL;
	}
    if ($accion <> 2) {
	    if (isset($_POST['idLugarPago']) && $_POST['idLugarPago'] <> "") {
	        $idLugarPago = $_POST['idLugarPago'];
	    } else {
	        $continua = FALSE;
	        $mensaje .= "Falta idLugarPago. ";
	    }
	    if (isset($_POST['totalRecaudacion']) && $_POST['totalRecaudacion'] <> "") {
	        $totalRecaudacion = $_POST['totalRecaudacion'];
	    } else {
	        $continua = FALSE;
	        $mensaje .= "Falta totalRecaudacion. ";
	    }
	    if (isset($_POST['fechaApertura']) && $_POST['fechaApertura'] <> "") {
	        $fechaApertura = $_POST['fechaApertura'];
	    } else {
	        $continua = FALSE;
	        $mensaje .= "Falta fechaApertura. ";
	    }
	    if ($accion == 1) {
		    if (isset($_POST['periodo']) && $_POST['periodo'] <> "") {
		        $periodo = $_POST['periodo'];
		    } else {
		        $continua = FALSE;
		        $mensaje .= "Falta periodo. ";
		    }
		    if (isset($_POST['cuota']) && $_POST['cuota'] <> "") {
		        $cuota = $_POST['cuota'];
		    } else {
		        $continua = FALSE;
		        $mensaje .= "Falta cuota. ";
		    }
		} else {
			if (isset($_POST['cantidadComprobantes']) && $_POST['cantidadComprobantes'] <> "") {
		        $cantidadComprobantes = $_POST['cantidadComprobantes'];
		    } else {
		        $continua = FALSE;
		        $mensaje .= "Falta cantidadComprobantes. ";
		    }
		}
	    if (isset($_POST['numeroLoteManual']) && $_POST['numeroLoteManual'] <> "") {
	        $numeroLoteManual = $_POST['numeroLoteManual'];
	    } else {
	        $continua = FALSE;
	        $mensaje .= "Falta numeroLoteManual. ";
	    }
	}
} else {
	$continua = FALSE;
	$mensaje .= "Falta accion. ";
}

if ($continua) {
    switch ($accion) {
        case '1':
        case '3':
       		$estado = 'A';
            $resultado = $cobranzaLogic->guardarCobranzaManual($idCobranza, $idLugarPago, $cantidadComprobantes, $totalRecaudacion, $diferenciaImporte, $fechaApertura, $periodo, $cuota, $numeroLoteManual, $estado, $_SESSION['user_id']);
            if ($resultado['estado'] && $accion == 1) {
            	$idCobranza = $resultado['idCobranza'];
	            $linkVolver = "../cobranza_lotes_detalle.php?id=".$idCobranza;
            } else {
            	$linkVolver = "../cobranza_lotes.php";
            }
            break;
        
        case '2':
            $resultado = eliminarCobranzaManual($idCobranza);
            $linkVolver = "../cobranza_lotes.php?id=".$idCobranza;
            break;
       
        default:
            $resultado['estado'] = FALSE;
		    $resultado['mensaje'] = $mensaje.'accion inconrrecta';
		    $resultado['clase'] = 'alert alert-danger'; 
		    $resultado['icono'] = 'glyphicon glyphicon-remove'; 
           	$linkVolver = "../cobranza_lotes.php";
            break;
    }
} else {
    $resultado['estado'] = FALSE;
    $resultado['mensaje'] = $mensaje;
    $resultado['clase'] = 'alert alert-danger'; 
    $resultado['icono'] = 'glyphicon glyphicon-remove'; 
   	$linkVolver = "../cobranza_lotes.php";
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
        <input type="hidden"  name="idLugarPago" id="idLugarPago" value="<?php echo $idLugarPago; ?>">
        <input type="hidden"  name="anioCobranza" id="anioCobranza" value="<?php echo substr($fechaApertura, 0, 4); ?>">
    </form>
</body>


