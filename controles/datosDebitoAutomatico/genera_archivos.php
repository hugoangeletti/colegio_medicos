<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/colegiadoLogic.php');
require_once ('../../dataAccess/colegiadoDeudaAnualLogic.php');
$colegiadoDeudaAnualLogic = new colegiadoDeudaAnualLogic();
require_once ('../../dataAccess/debitoAutomaticoLogic.php');
$debitoAutomaticoLogic = new debitoAutomaticoLogic();
set_time_limit(0);
$continua = TRUE;
$mensaje = "";

if (isset($_GET['id']) && $_GET['id'] <> "") {
	$idEnvioDebito = $_GET['id'];
} else {
	if (isset($_POST['fechaDebito']) && $_POST['fechaDebito'] <> "") {
		$fechaDebito = $_POST['fechaDebito'];
	} else {
		$continua = FALSE;
		$mensaje .= "Falta fechaDebito - ";
	}
	if (isset($_POST['tipoDebito']) && $_POST['tipoDebito'] <> "") {
		$tipoDebito = $_POST['tipoDebito'];
	} else {
		$continua = FALSE;
		$mensaje .= "Falta tipoDebito - ";
	}
	$idEnvioDebito = NULL;
}

if ($continua) {
	if (isset($idEnvioDebito)) {
		//solo genera los archivos, borra los actuales y los vuelve a crear
		$resEnvioDebito = $debitoAutomaticoLogic->obtenerEnvioDebitoPorId($idEnvioDebito);
		if ($resEnvioDebito['estado']) {
			$envioDebito = $resEnvioDebito['datos'];
			$fechaEnvio = $envioDebito['fechaEnvio'];
			$fechaDebito = $envioDebito['fechaDebito'];
			$nombreArchivo = $envioDebito['nombreArchivo'];
			$path = $envioDebito['pathArchivo'];
		}
	} else {
		$path = NULL;
		$nombreArchivo = NULL;
		//verifica que haya colegiados para generar los archivos del perido seleccionado
		$periodoActual = PERIODO_ACTUAL;
		$total = 0;
		$fechaVencimiento = ultmioDiaDelMes($fechaDebito);
		//obtener los colegiados adheridos al debito y activos
		$resColegiados = $debitoAutomaticoLogic->obtenerColegiadosPorDebitoCbu($tipoDebito);
		if ($resColegiados['estado']) {
			//se genera la liquidacion en las tablas endviodebitodetalle y endviodebitodetallecuota
		    foreach ($resColegiados['datos'] as $colegiado) {
		    	//$matricula = $colegiado['matricula'];
		    	$idColegiado = $colegiado['idColegiado'];
		    	$idDebito = $colegiado['idDebito'];

		    	$resDeuda = $colegiadoDeudaAnualLogic->obtenerColegiadoDeudaAnualAPagar($idColegiado, $fechaVencimiento);
		    	if ($resDeuda['estado'] && sizeof($resDeuda['datos']) > 0) {
		    		//tiene deuda 
		    		$totalDeuda = 0;
		    		$arrayCuotas = $resDeuda['datos'];
		    		foreach ($resDeuda['datos'] as $deuda) {
		    			//sumo el total de la deuda
		    			$totalDeuda += $deuda['importeActualizado'];
		    		}
		    		if ($totalDeuda > 0) {
		    			//si NO hay archivo abierto, entonces lo genero
		    			if (!isset($idEnvioDebito) || $idEnvioDebito == 0) {
		    				$resArchivo = $debitoAutomaticoLogic->agregarEnvioDebito($tipoDebito, $fechaDebito, $nombreArchivo, $path);
		    				if ($resArchivo['estado']) {
		    					$idEnvioDebito = $resArchivo['idEnvioDebito'];
		    				} else {
		    					$mensaje .= $resArchivo['mensaje'];
		    					$exit;
		    				}
		    			}
			    		$resDebitoDetalle = $debitoAutomaticoLogic->agregarEnvioDebitoDetalle($idEnvioDebito, $idDebito, $arrayCuotas);
						if ($resDebitoDetalle['estado']) {
							$total += $totalDeuda;
						} else {
			    			echo 'error al generar enviodebitodetalle: idColegiado ->'.$idColegiado.'<br>';
			    		}
			    	}
		    	}
		    } 
		} else {
		   	var_dump($resColegiados);
		}
	}

	/// genero los archivos txt
	if (!isset($nombreArchivo) || $nombreArchivo == "") {		
		//arma los nombre de los archivos a enviar
		$path = 'archivos/debito_automatico/';
		switch ($tipoDebito) {
			case TARJETA_DEBITO:
				$nombreArchivo = 'DEBLIQD.txt';
				$path .= 'debito/'.date('Y').'/'.date('md');
				break;
			
			case TARJETA_CREDITO:
				$nombreArchivo = 'DEBLIQC.txt';
				$path .= 'credito/'.date('Y').'/'.date('md');
				break;
			
			case CBU:
				$nombreArchivo = 'ent3504_'.date('dmy').'_sec_001.txt';
				$path .= 'cbu/'.date('Y');
				break;
			
			default:
				$tipoDebito = NULL;
				break;
		}
	}
    //agrego los totales y los nombres de los archivos
    $resArchivo = $debitoAutomaticoLogic->actualizarEnvioDebito($idEnvioDebito, $nombreArchivo, $path);
    $path = '../../'.$path;

    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }
    if (file_exists($path.'/'.$nombreArchivo)) {
	    unlink($path.'/'.$nombreArchivo);
	}

	$fileControl = fopen($path.'/'.$nombreArchivo, "w")or  die("Problemas en la creacion del archivo ".$path.'/'.$nombreArchivo);

	$resDebitoDetalle = $debitoAutomaticoLogic->obtenerEnvioDebitoDetallePorIdEnvio($idEnvioDebito, $tipoDebito);
	//var_dump($resDebitoDetalle); exit;
	$cantidadRegistros = sizeof($resDebitoDetalle['datos']);
	if ($resDebitoDetalle['estado'] && $cantidadRegistros > 0){
		$continua = TRUE;
		$resTotalEnvio = $debitoAutomaticoLogic->obtenerTotalEnvioDebitoPorId($idEnvioDebito);
		if ($resTotalEnvio['estado']) {		
        	$totalDebitar = $resTotalEnvio['totalDebitar'];
        	$cantidadDebitar = $resTotalEnvio['cantidadDebitar'];
			if ($tipoDebito == CBU) {
				//inserto el encabezado de cbu
				$importe_total = rellenarCeros(($totalDebitar*100), 18); // format((ParC:c1)*100, @n018)
				$fecha = date('Ymd');
				$ceros_linea1 = str_pad('', 98, '0', STR_PAD_LEFT);
				$en_blanco_linea1 = str_pad('', 69, ' ', STR_PAD_LEFT);
		        $linea = '103504          00000'.$fecha.$importe_total.'08001'.$ceros_linea1.$en_blanco_linea1.'0';

		    } else {
		    	//insertar encabezado por debito o credito
		    	$linea = '0DEBLIQ'.$tipoDebito.' 0075341941900000    '.date('YmdHi').'0                                                         *';
		    }
	        if (fwrite($fileControl, $linea."\r\n") === FALSE) {
	            $claseMensaje = "alert alert-danger";
	            $continua = false;   
	            $mensaje = "NO SE PUDO GENERAR EL ARCHIVO ".$debitoAutomaticoLogic->obtenerTipoDebito($tipoDebito)."TXT.";
	        }
		} else {
			$continua = FALSE;
			$mensaje .= $resTotalEnvio['mensaje'];
		}

        if ($continua) {
		    foreach ($resDebitoDetalle['datos'] as $dato) {
	        	if ($tipoDebito == CBU) {
	        		//debito cbu
			    	$idEnvioDebitoDetalle = $dato['idEnvioDebitoDetalle'];
			    	$cbuBloque1 = $dato['cbuBloque1'];
		    		$cbuBloque2 = $dato['cbuBloque2'];
				    $importeDebitar = $dato['importeDebitar'];

				    $timestamp = strtotime($fechaDebito);
					$fecha_debitar = date('Ymd', $timestamp);
					$importe_debitar = rellenarCeros(($importeDebitar * 100), 13);
					$en_blanco_linea1 = str_pad('', 14, ' ', STR_PAD_LEFT);
					$en_blanco_linea2 = str_pad('', 22, ' ', STR_PAD_LEFT);
					$en_blanco_linea3 = str_pad('', 40, ' ', STR_PAD_LEFT);
					$ceros_linea1 = str_pad('', 13, '0', STR_PAD_LEFT);
					$ceros_linea2 = str_pad('', 15, '0', STR_PAD_LEFT);

					$linea = '003504          00000'.substr($cbuBloque1, 0, 3).substr($cbuBloque1, 3, 4).' 0'.substr($cbuBloque2, 0, 14).rellenarCeros($idEnvioDebitoDetalle, 8).$en_blanco_linea1.'COLEGIACION    '.'  '.'    '.$fecha_debitar.'080'.$importe_debitar.'00000000'.$ceros_linea1.'0000'.'0'.$ceros_linea2.$en_blanco_linea2.$en_blanco_linea3.'     '.'0';

			    } else {
			    	//debito tarjetas
			    	$matricula = $dato['matricula'];
			    	$numeroTarjeta = $dato['numeroTarjeta'];
			    	$numeroDocumento = $dato['numeroDocumento'];
			    	$identificador = rellenarCeros($numeroDocumento, 15);
			    	$primerProceso = " "; //$dato['primerProceso'];
			    	$idColegiado = $dato['idColegiado'];
			    	$incluyePlanPagos = $dato['incluyePlanPagos'];
			    	$idDebitoTarjeta = $dato['idDebitoTarjeta'];
			    	$pagoTotal = $dato['pagoTotal'];
			    	$importeDebitar = $dato['importeDebitar'];
					$importe_debitar = rellenarCeros(($importeDebitar * 100), 15);
			    	$idEnvioDebitoDetalle = $dato['idEnvioDebitoDetalle'];

                	$linea = '1'.$numeroTarjeta.'   '.rellenarCeros($idEnvioDebitoDetalle, 8).date('Ymd').'0005'.$importe_debitar.$identificador.$primerProceso.'  '.'                          *';
			    }
		        if (fwrite($fileControl, $linea."\r\n") === FALSE) {
	                $claseMensaje = "alert alert-danger";
	                $continua = false;   
	                $mensaje = "NO SE PUDO GENERAR EL ARCHIVO ".$nombreArchivo." TXT.";
	                break;
		        }
			} 
			//si es debito por tarjetas debo generar la linea de cierre del lote, por cbu no lleva
        	if ($tipoDebito != CBU) {
        		$total_registros = rellenarCeros($cantidadRegistros, 7);
				$importe_total = rellenarCeros(($totalDebitar*100), 15); // format((ParC:c1)*100, @n018)
        		$linea = '9DEBLIQ'.$tipoDebito.' 0075341941900000    '.date('YmdHi').$total_registros.$importe_total.'                                    *';
		        if (fwrite($fileControl, $linea."\r\n") === FALSE) {
	                $claseMensaje = "alert alert-danger";
	                $continua = false;
	                $mensaje = "NO SE PUDO GENERAR EL ARCHIVO ".$nombreArchivo." TXT.";
		        }
        	}
   		    fclose($fileControl);
		}
	}	
} 
/*
	echo '<br>';
	var_dump($continua);
	echo '<br>'.$mensaje;
	exit;
*/
$resultado['estado'] = $continua;
$resultado['mensaje'] = $mensaje;
?>
<body onLoad="document.forms['myForm'].submit()">
    <form name="myForm"  method="POST" action="../debito_automatico.php">
        <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
        <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
        <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
    </form>
</body>

