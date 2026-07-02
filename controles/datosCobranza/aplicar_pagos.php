<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/cobranzaLogic.php');
$cobranzaLogic = new cobranzaLogic();

$continua = TRUE;
$mensaje = "";
if (isset($_POST['idCobranza']) && $_POST['idCobranza'] <> "") {
	$idCobranza = $_POST['idCobranza'];
	$resLote = $cobranzaLogic->obtenerLotePorId($idCobranza);
	//echo '<br>resLote<br>';
	//var_dump($resLote);
	if ($resLote['estado']) {
		$lote = $resLote['datos'];
		$idLugarPago = $lote['idLugarPago'];
		$cantidadComprobantes = $lote['cantidadComprobantes'];
		$totalRecaudacion = $lote['totalRecaudacion'];
		$fechaApertura = $lote['fechaApertura'];
		$diferenciaImporte = $lote['diferenciaImporte'];
		$periodo = $lote['periodo'];
		$numeroLoteManual = $lote['numeroLoteManual'];
		$cuota = $lote['cuota'];

		//cargo los pagos		
		$resPagos = $cobranzaLogic->obtenerCobranzaDetallePorIdCobranza($idCobranza);
		//echo '<br>resPagos<br>';
		//var_dump($resPagos);
		if ($resPagos['estado']) {
			$totalPorCuota = 0;
			$pagosAplicados = 0;
			foreach ($resPagos['datos'] as $dato) {
				$comprobante = $dato['recibo'];
				$fechaPago = $dato['fechaPago'];
				$importe = $dato['importe'];
				$totalPorCuota += $importe;
				$resultado = $cobranzaLogic->aplicarPagoDeudaAnual($comprobante, $fechaPago);
		//echo '<br>aplicarPagoDeudaAnual<br>';
		//var_dump($resultado);
				if ($resultado['estado']) {
				   	$pagosAplicados += 1;
				}
			}
		} else {
			$continua = FALSE;
		    $mensaje .= $resPagos['mensaje'];
		}
	} else {
		$continua = FALSE;
	    $mensaje .= $resLote['mensaje'];
	}
} else {
	$continua = FALSE;
    $mensaje .= "Falta idCobranza. ";
}

if ($continua) {
    if ($pagosAplicados > 0) {
		//$resultado = $cobranzaLogic->cerrarCobranzaManual($idCobranza, $cantidadComprobantes);
		$diferenciaImporte = $totalRecaudacion - $totalPorCuota;
		$estado = 'C';
		$resultado = $cobranzaLogic->guardarCobranzaManual($idCobranza, $idLugarPago, $cantidadComprobantes, $totalRecaudacion, $diferenciaImporte, $fechaApertura, $periodo, $cuota, $numeroLoteManual, $estado, $_SESSION['user_id']);
       	$linkVolver = "../cobranza_lotes.php";
    } else {
		$linkVolver = "../cobranza_lotes_detalle.php?id=".$idCobranza;
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
        <input type="hidden"  name="anioCobranza" id="anioCobranza" value="<?php echo substr($fechaPago, 0, 4); ?>">
    </form>
</body>


