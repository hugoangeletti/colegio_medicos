<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/conection_pdo.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/cursos_pdo.php');
require_once ('../../dataAccess/colegiadoLogic.php');
require_once ('../../dataAccess/colegiadoDeudaAnualLogic.php');
$colegiadoDeudaAnualLogic = new colegiadoDeudaAnualLogic();
require_once ('../../dataAccess/colegiadoPlanPagoLogic.php');
$colegiadoPlanPagoLogic = new colegiadoPlanPagoLogic();
require_once ('../../dataAccess/homeBankingLogic.php');
$homeBankingLogic = new homeBankingLogic();
set_time_limit(0);
$continua = TRUE;
$mensaje = "";

if (isset($_GET['id']) && $_GET['id'] <> "") {
	$idHomeBankingArchivo = $_GET['id'];
} else {
	if (isset($_POST['codigoLiquidacion']) && $_POST['codigoLiquidacion'] <> "") {
		$codigoLiquidacion = $_POST['codigoLiquidacion'];
	} else {
		$continua = FALSE;
		$mensaje .= "Falta codigoLiquidacion - ";
	}
	if (isset($_POST['fechaVencimiento']) && $_POST['fechaVencimiento'] <> "") {
		$fechaVencimiento = $_POST['fechaVencimiento'];
	} else {
		$continua = FALSE;
		$mensaje .= "Falta fechaVencimiento - ";
	}
	if (isset($_POST['archivoControl']) && $_POST['archivoControl'] <> "") {
		$control = $_POST['archivoControl'];
	} else {
		$continua = FALSE;
		$mensaje .= "Falta archivoControl - ";
	}
	if (isset($_POST['archivoRefresh']) && $_POST['archivoRefresh'] <> "") {
		$refresh = $_POST['archivoRefresh'];
	} else {
		$continua = FALSE;
		$mensaje .= "Falta archivoRefresh - ";
	}
	if (isset($_POST['archivoPMC']) && $_POST['archivoPMC'] <> "") {
		$pagoMisCuentas = $_POST['archivoPMC'];
	} else {
		$continua = FALSE;
		$mensaje .= "Falta archivoPMC - ";
	}
	$idHomeBankingArchivo = NULL;
}

if ($continua) {
	$fechaPrimerVencimiento = $fechaVencimiento;
	$fechaSegundoVencimiento = $fechaVencimiento;
	if (isset($idHomeBankingArchivo)) {
		//solo genera los archivos, borra los actuales y los vuelve a crear
		$resProcesaHomeBanking = $homeBankingLogic->obtenerHomaBankingPorId($idHomeBankingArchivo);
		if ($resProcesaHomeBanking['estado']) {
			$homeBankingArchivo = $resProcesaHomeBanking['datos'];
			$control = $homeBankingArchivo['control'];
			$refresh = $homeBankingArchivo['refresh'];
			$total = $homeBankingArchivo['importe'];
			$pagoMisCuentas = $homeBankingArchivo['pagoMisCuentas'];
			$path = $homeBankingArchivo['pathArchivo'];

		    $pathLINK = "../../".$path.'/LINK/';
		    $pathPMC = "../../".$path.'/PMC/';
		}
	} else {
		//$control = NULL;
		//$refresh = NULL;
		//$pagoMisCuentas = NULL;
        $periodoProceso = date('Y').date('m');
		$path = NULL;
		//verifica que haya colegiados para generar los archivos del perido seleccionado
		$periodoActual = PERIODO_ACTUAL;
		$total = 0;
		$idHomeBankingArchivo = NULL;

		//Concepto '001' -> Deuda Periodos Anteriores
		//echo "Concepto '001' -> Deuda Periodos Anteriores<br>".
		$resColegiados = $homeBankingLogic->obtenerColegiadoConDeudaPeriodosAnteriores($periodoProceso);
		if ($resColegiados['estado']) {
			//se genera la liquidacion en las tablas linkpagos y linkpagosdetalle
			$concepto = '001';
	        $mensajeTicket='DEUDA ANTERIOR                          ';
	        $mensajePantalla='DEUDA ANTERIOR ';
		    foreach ($resColegiados['datos'] as $colegiado) {
		    	$matricula = $colegiado['matricula'];
		    	$idColegiado = $colegiado['idColegiado'];
		    	$idAsistente = NULL;

		    	$resDeudaAnterior = $colegiadoDeudaAnualLogic->obtenerDeudaPeriodosAnterioresPorIdColegiado($idColegiado);
		    	if ($resDeudaAnterior['estado'] && sizeof($resDeudaAnterior['datos']) > 0) {
		    		//tiene deuda anterior
		    		$totalDeuda = 0;
		    		$arrayCuotas = $resDeudaAnterior['datos'];
		    		foreach ($resDeudaAnterior['datos'] as $deuda) {
		    			//sumo el total de la deuda
		    			$totalDeuda += $deuda['recargo'];
		    		}
		    		if ($totalDeuda > 0) {
		    			//si NO hay archivo abierto, entonces lo genero
		    			if (!isset($idHomeBankingArchivo) || $idHomeBankingArchivo == 0) {
		    				$resArchivo = $homeBankingLogic->agregarHomeBankingArchivo($fechaPrimerVencimiento, $fechaSegundoVencimiento, $codigoLiquidacion, $control, $refresh, $pagoMisCuentas, $path, NULL);
		    				if ($resArchivo['estado']) {
		    					$idHomeBankingArchivo = $resArchivo['idHomeBankingArchivo'];
		    				} else {
		    					$mensaje .= $resArchivo['mensaje'];
		    					$exit;
		    				}
		    			}
			    		//agrega linkpagos
			    		$codigobarra = "";
			    		$resLink = $homeBankingLogic->agregarLinkPagos($idHomeBankingArchivo, $concepto, $matricula, $idAsistente, $fechaPrimerVencimiento, $totalDeuda, $fechaSegundoVencimiento, $mensajeTicket, $mensajePantalla, $codigobarra, $arrayCuotas, NULL);
			    		//$resLink = $homeBankingLogic->agregarEnvioHomeBanking($idHomeBankingArchivo, $concepto, $idColegiado, $idAsistente, $fechaPrimerVencimiento, $totalDeuda, $fechaSegundoVencimiento, $mensajeTicket, $mensajePantalla, $codigobarra, $arrayCuotas, NULL);
						if ($resLink['estado']) {
							$total += $totalDeuda;
						} else {
			    			echo 'error al generar linkpagos: matricula ->'.$matricula.', concepto -> '.$concepto.'<br>';
			    		}
			    	}
		    	}
		    } 
		} else {
		   	var_dump($resColegiados);
		}
		    	
		//Concepto '002' -> DEUDA PLAN DE PAGOS
		//echo "Concepto '002' -> DEUDA PLAN DE PAGOS<br>".
		$resColegiados = $homeBankingLogic->obtenerColegiadoConDeudaPlanPagos($periodoProceso);
		if ($resColegiados['estado']) {
			//se genera la liquidacion en las tablas linkpagos y linkpagosdetalle
			$concepto = '002';
	        $mensajeTicket='DEUDA PLAN DE PAGOS                     ';
	        $mensajePantalla='DEUDA PLAN PAGO';
		    foreach ($resColegiados['datos'] as $colegiado) {
		    	$matricula = $colegiado['matricula'];
		    	$idColegiado = $colegiado['idColegiado'];
		    	$idAsistente = NULL;
				//primero verificamos que el concepto no este en otro archivo ya enviado
		    	$resPlanPago = $colegiadoPlanPagoLogic->obtenerCuotaActualPlanPagosPorIdColegiado($idColegiado, NULL);
		    	if ($resPlanPago['estado'] && sizeof($resPlanPago['datos']) > 0) {
		    		//tiene cuota de plan de pagos dentro del vencimiento
		    		$totalDeuda = 0;
		    		$arrayCuotas = $resPlanPago['datos'];
		    		//echo 'PLAN PAGO DEUDA';
		    		//var_dump($resPlanPago);
		    		//echo '<br>';
		    		foreach ($resPlanPago['datos'] as $deuda) {
		    			//sumo el total de la deuda
		    			$totalDeuda += $deuda['recargo'];
		    		}
		    		if ($totalDeuda > 0) {
		    			//si NO hay archivo abierto, entonces lo genero
		    			if (!isset($idHomeBankingArchivo) || $idHomeBankingArchivo == 0) {
		    				$resArchivo = $homeBankingLogic->agregarHomeBankingArchivo($fechaPrimerVencimiento, $fechaSegundoVencimiento, $codigoLiquidacion, $control, $refresh, $pagoMisCuentas, $path);
		    				if ($resArchivo['estado']) {
		    					$idHomeBankingArchivo = $resArchivo['idHomeBankingArchivo'];
		    				} else {
		    					$mensaje .= $resArchivo['mensaje'];
		    					$exit;
		    				}
		    			}
			    		//agrega linkpagos
			    		$codigobarra = "";
			    		$resLink = $homeBankingLogic->agregarLinkPagos($idHomeBankingArchivo, $concepto, $matricula, $idAsistente, $fechaPrimerVencimiento, $totalDeuda, $fechaSegundoVencimiento, $mensajeTicket, $mensajePantalla, $codigobarra, $arrayCuotas, NULL);
			    		//$resLink = $homeBankingLogic->agregarEnvioHomeBanking($idHomeBankingArchivo, $concepto, $idColegiado, $idAsistente, $fechaPrimerVencimiento, $totalDeuda, $fechaSegundoVencimiento, $mensajeTicket, $mensajePantalla, $codigobarra, $arrayCuotas, NULL);
			    		if ($resLink['estado']) {
			    			$total += $totalDeuda;
			    		} else {
			    			echo 'error al generar linkpagos: matricula ->'.$matricula.', concepto -> '.$concepto.'<br>';
			    		}
			    	}
		    	}
			}
		} else {
			var_dump($resColegiados);
		}

		//Concepto '003' -> CUOTA PLAN DE PAGOS
		//echo "Concepto '003' -> CUOTA PLAN DE PAGOS<br>".
		$resColegiados = $homeBankingLogic->obtenerColegiadoCuotaVigentePlanPagos($periodoProceso);
		if ($resColegiados['estado']) {
			//se genera la liquidacion en las tablas linkpagos y linkpagosdetalle
			$concepto = '003';
	        $mensajeTicket='CUOTA PLAN DE PAGOS                     ';
			$mensajePantalla='CUOTA PLAN PAGO';
		    foreach ($resColegiados['datos'] as $colegiado) {
		    	$matricula = $colegiado['matricula'];
		    	$idColegiado = $colegiado['idColegiado'];
		    	$idAsistente = NULL;
				//primero verificamos que el concepto no este en otro archivo ya enviado
		    	$resPlanPago = $colegiadoPlanPagoLogic->obtenerProximaCuotaPlanPagoIdColegiado($idColegiado, NULL);
		    	if ($resPlanPago['estado']) {
		    		//tiene cuota de plan de pagos dentro del vencimiento
		    		$arrayCuotas = $resPlanPago['datos'];
		    		$totalDeuda = 0;

		    		foreach ($resPlanPago['datos'] as $deuda) {
		    			//sumo el total de la deuda
		    			$totalDeuda += $deuda['recargo'];
		    		}

		    		if ($totalDeuda > 0) {
		    			//si NO hay archivo abierto, entonces lo genero
		    			if (!isset($idHomeBankingArchivo) || $idHomeBankingArchivo == 0) {
		    				$resArchivo = $homeBankingLogic->agregarHomeBankingArchivo($fechaPrimerVencimiento, $fechaSegundoVencimiento, $codigoLiquidacion, $control, $refresh, $pagoMisCuentas, $path, NULL);
		    				if ($resArchivo['estado']) {
		    					$idHomeBankingArchivo = $resArchivo['idHomeBankingArchivo'];
		    				} else {
		    					$mensaje .= $resArchivo['mensaje'];
		    					$exit;
		    				}
		    			}
			    		//agrega linkpagos
			    		$codigobarra = "";
			    		$resLink = $homeBankingLogic->agregarLinkPagos($idHomeBankingArchivo, $concepto, $matricula, $idAsistente, $fechaPrimerVencimiento, $totalDeuda, $fechaSegundoVencimiento, $mensajeTicket, $mensajePantalla, $codigobarra, $arrayCuotas, NULL);
			    		//$resLink = $homeBankingLogic->agregarEnvioHomeBanking($idHomeBankingArchivo, $concepto, $idColegiado, $idAsistente, $fechaPrimerVencimiento, $totalDeuda, $fechaSegundoVencimiento, $mensajeTicket, $mensajePantalla, $codigobarra, $arrayCuotas, NULL);
			    		if ($resLink['estado']) {
			    			$total += $totalDeuda;
			    		} else {
			    			echo 'error al generar linkpagos: matricula ->'.$matricula.', concepto -> '.$concepto.'<br>';
			    		}
			    	}
		    	}
		    }
		} else {
			var_dump($resColegiados);
		}

	    //Concepto '004' al '013' y '015' al '016' -> Cuotas Periodo Actual
		//echo "Concepto '004' al '013' y '015' al '016' -> Cuotas Periodo Actual<br>".
		$resColegiados = $homeBankingLogic->obtenerColegiadoConDeudaPeriodoActual($periodoProceso, $fechaVencimiento);
		if ($resColegiados['estado']) {
		    foreach ($resColegiados['datos'] as $colegiado) {
		    	$matricula = $colegiado['matricula'];
		    	$idColegiado = $colegiado['idColegiado'];
		    	$idAsistente = NULL;
			    $resCuotas = $colegiadoDeudaAnualLogic->obtenerCuotasPeriodoActualParaHomeBanking($idColegiado, $periodoActual, $fechaVencimiento);
			    if ($resCuotas['estado'] && sizeof($resCuotas['datos']) > 0) {
			    	//si NO hay archivo abierto, entonces lo genero
	    			if (!isset($idHomeBankingArchivo) || $idHomeBankingArchivo == 0) {
	    				$resArchivo = $homeBankingLogic->agregarHomeBankingArchivo($fechaPrimerVencimiento, $fechaSegundoVencimiento, $codigoLiquidacion, $control, $refresh, $pagoMisCuentas, $path, NULL);
	    				if ($resArchivo['estado']) {
	    					$idHomeBankingArchivo = $resArchivo['idHomeBankingArchivo'];
	    				} else {
	    					$mensaje .= $resArchivo['mensaje'];
	    					var_dump($resArchivo);
	    					exit;
	    				}
	    			}
		    		//tiene cuota de perido actual para enviar
		    		$arrayCuotas = array();
		    		foreach ($resCuotas['datos'] as $deuda) {
		    			//if (substr($deuda['fechaVencimiento'], 0, 7) < '2024-12') {
			    			$idColegiadoDeudaAnualCuota = $deuda['idColegiadoDeudaAnualCuota'];
			    			$cuota = $deuda['cuota'];
			    			$periodo = $deuda['periodo'];
			    			$importePrimerVto = $deuda['importeActualizado'];
			    			//$fechaPrimerVencimiento = $deuda['fechaVencimiento'];
			    			$importeSegundoVto = $deuda['importeActualizado'];
			    			//$fechaSegundoVencimiento = $deuda['fechaVencimiento'];
			    			$concepto = $deuda['concepto'];
			    			$mensajeTicket = $deuda['mensajeTicket'].' '.$periodoActual;
			    			$mensajePantalla = $deuda['mensajePantalla'].' '.$periodoActual;
			    			$arrayCuotas[0]['idDeuda'] = $idColegiadoDeudaAnualCuota;
			    			$arrayCuotas[0]['recargo'] = $importeSegundoVto;
				    		//agrega linkpagos
				    		$codigobarra = "";

							$resLink = $homeBankingLogic->agregarLinkPagos($idHomeBankingArchivo, $concepto, $matricula, $idAsistente, $fechaPrimerVencimiento, $importeSegundoVto, $fechaSegundoVencimiento, $mensajeTicket, $mensajePantalla, $codigobarra, $arrayCuotas, NULL);
				    		if ($resLink['estado']) {
				    			$total += $importeSegundoVto;
				    		} else {
				    			echo 'error al generar linkpagos: '.$resLink['mensaje'];
				    		}
			    		//}
		    		}
			    } 
		    }
	    } else {
	    	echo $resColegiados;
	    }

		//Concepto '014' -> PAGO TOTAL
		//echo "Concepto '014' -> PAGO TOTAL<br>".
		//se procesa si el vencimiento es Julio, sino no se carga el pagototal
		if (substr($fechaPrimerVencimiento, 5, 2) == '07') {
			$resColegiados = $homeBankingLogic->obtenerColegiadoPagoTotal($periodoProceso);
			if ($resColegiados['estado']) {
				if (sizeof($resColegiados['datos']) > 0) {
			    	//si NO hay archivo abierto, entonces lo genero
					if (!isset($idHomeBankingArchivo) || $idHomeBankingArchivo == 0) {
						$resArchivo = $homeBankingLogic->agregarHomeBankingArchivo($fechaPrimerVencimiento, $fechaSegundoVencimiento, $codigoLiquidacion, $control, $refresh, $pagoMisCuentas, $path, NULL);
						if ($resArchivo['estado']) {
							$idHomeBankingArchivo = $resArchivo['idHomeBankingArchivo'];
						} else {
							$mensaje .= $resArchivo['mensaje'];
							var_dump($resArchivo);
							exit;
						}
					}
					//se genera la liquidacion en las tablas linkpagos y linkpagosdetalle
					$concepto = '014';
			        $mensajeTicket='PAGO TOTAL PERIODO '.$periodoActual.'                 ';
			        $mensajePantalla='PAGO TOTAL '.$periodoActual;
				    foreach ($resColegiados['datos'] as $colegiado) {
				    	$idColegiadoDeudaAnualTotal = $colegiado['idColegiadoDeudaAnualTotal'];
				    	$matricula = $colegiado['matricula'];
				    	$idColegiado = $colegiado['idColegiado'];
				    	$idAsistente = NULL;

			    		$arrayCuotas = array();
		    			$importePrimerVto = $colegiado['importe'];
		    			$importeSegundoVto = $colegiado['importe'];
		    			$arrayCuotas[0]['idDeuda'] = $idColegiadoDeudaAnualTotal;
		    			$arrayCuotas[0]['recargo'] = $importeSegundoVto;
			    		//agrega linkpagos
			    		$codigobarra = "";

						$resLink = $homeBankingLogic->agregarLinkPagos($idHomeBankingArchivo, $concepto, $matricula, $idAsistente, $fechaPrimerVencimiento, $importeSegundoVto, $fechaSegundoVencimiento, $mensajeTicket, $mensajePantalla, $codigobarra, $arrayCuotas, NULL);
			    		if ($resLink['estado']) {
			    			$total += $importeSegundoVto;
			    		} else {
			    			echo 'error al generar linkpagos: '.$resLink['mensaje'];
			    		}
				    } 
				}
			} else {
			   	var_dump($resColegiados);
			}
		}		    	
	    /*
	    //busco las cuotas de cursos
		//echo "Concepto cursos<br>".
	    $resCursos = $homeBankingLogic->obtenerCuotasCursosParaHomeBanking($fechaVencimiento, $periodoProceso);
	    if ($resCursos['estado']) {
	    	foreach ($resCursos['datos'] as $dato) {
	    		$idCursosAsistenteCuota = $dato['idCursosAsistenteCuota'];
	    		$idAsistente = $dato['idCursosAsistente'];
	    		$idCursos = $dato['idCursos'];
	    		$cuota = $dato['cuota'];
	    		$importe = $dato['importe'];
	    		$fechaVencimiento = $dato['fechaVencimiento'];
	   			$concepto = 200 + intval($dato['cuota']);
	   			$mensajeTicket = 'CUOTA '.rellenarCeros($cuota, 2).' CURSO';
	   			$mensajePantalla = 'CUOTA '.rellenarCeros($cuota, 2).' CURSO';
				$arrayCuotas[0]['idDeuda'] = $idCursosAsistenteCuota;
				$arrayCuotas[0]['recargo'] = $importe;
	    		$codigobarra = "";
	    		$matricula = NULL;

				//primero verificamos que el concepto no este en otro archivo ya enviado
				if (!isset($idHomeBankingArchivo) || $idHomeBankingArchivo == 0) {
    				$resArchivo = $homeBankingLogic->agregarHomeBankingArchivo($fechaPrimerVencimiento, $fechaSegundoVencimiento, $codigoLiquidacion, $control, $refresh, $pagoMisCuentas, $path, NULL);
    				if ($resArchivo['estado']) {
    					$idHomeBankingArchivo = $resArchivo['idHomeBankingArchivo'];
    				} else {
    					$mensaje .= $resArchivo['mensaje'];
    					$exit;
    				}
    			}

    			$resLink = $homeBankingLogic->agregarLinkPagos($idHomeBankingArchivo, $concepto, $matricula, $idAsistente, $fechaPrimerVencimiento, $importe, $fechaSegundoVencimiento, $mensajeTicket, $mensajePantalla, $codigobarra, $arrayCuotas, NULL);
		    	//$resLink = $homeBankingLogic->agregarEnvioHomeBanking($idHomeBankingArchivo, $concepto, $idColegiado, $idCursosAsistente, $fechaPrimerVencimiento, $importe, $fechaSegundoVencimiento, $mensajeTicket, $mensajePantalla, $codigobarra, $arrayCuotas, NULL);
		    	if ($resLink['estado']) {
		    			$total += $importe;
		    	} else {
		    		echo 'error al generar linkpagos: '.$resLink['mensaje'];
		    	}
	    	}
	    } else {
	    	var_dump($resCursos);
	    }
	    */
	}

	/// genero los archivos txt
	if ((!isset($control) || $control == "") && (!isset($refresh) || $refresh == "") && (!isset($pagoMisCuentas) || $pagoMisCuentas == "")) {		
		//arma los nombre de los archivos a enviar
	    $mes = date('m');
		switch ($mes) {
	         case '10':
	             $mes = '0A';
	             break;
	         
	         case '11':
	             $mes = '0B';
	             break;
	         
	         case '12':
	             $mes = '0C';
	             break;
	         
	         default:
	             break;
	    }
	    $dia = rellenarCeros(date('d'), 2);
	    $refresh = 'PGHR'.$mes.$dia;
	    $control = 'CGHR'.$mes.$dia;

	    $diaPMC = rellenarCeros(date('d'), 2);
	    $mesPMC = rellenarCeros(date('m'), 2);
	    $anioPMC = substr(date('Y'), 2, 2);
	    $pagoMisCuentas = 'FAC2199.'.$diaPMC.$mesPMC.$anioPMC;
	}
	if (!isset($path)) {
	    $anio = date('Y');
	    $path = "archivos/homeBanking/".$anio;
	}    
    //agrego los totales y los nombres de los archivos
    $resArchivo = $homeBankingLogic->actualizarHomeBankingArchivos($idHomeBankingArchivo, $total, $control, $refresh, $pagoMisCuentas, $path, NULL);

    $pathLINK = "../../".$path.'/LINK/';
    //echo 'pathLINK->'.$pathLINK. ' pathPMC->'.$pathPMC.'<br>';
    if (!file_exists($pathLINK)) {
        mkdir($pathLINK, 0777, true);
    }
    //genero los archivos para enviar a homebanking
    //echo 'control->'.$control. ' refresh->'.$refresh.'<br>';
    if (file_exists($pathLINK.$control)) {
	    unlink($pathLINK.$control);
	}
	if (file_exists($pathLINK.$refresh)) {
	    unlink($pathLINK.$refresh);
	}

	$pathPMC = "../../".$path.'/PMC/';
    if (!file_exists($pathPMC)) {
        mkdir($pathPMC, 0777, true);
    }
	if (file_exists($pathPMC.$pagoMisCuentas)) {
	    unlink($pathPMC.$pagoMisCuentas);
	}

	$fileControl = fopen($pathLINK.$control, "w")or  die("Problemas en la creacion del archivo ".$path.'/LINK/'.$control);
	$fileRefresh = fopen($pathLINK.$refresh, "w")or  die("Problemas en la creacion del archivo ".$path.'/LINK/'.$refresh);
	$filePMC = fopen($pathPMC.$pagoMisCuentas, "w")or  die("Problemas en la creacion del archivo ".$path.'/PMC/'.$pagoMisCuentas);

	$resHomeBanking = $homeBankingLogic->obtenerHomeBankingConceptoPorIdArchivo($idHomeBankingArchivo);
	$cantidadRegistros = sizeof($resHomeBanking['datos']);
	if ($resHomeBanking['estado'] && $cantidadRegistros > 0){
		$continua = TRUE;
		//inserto el encabezado de refresh
		$fecha_linea = substr(date('Y'), 2, 2).date('m').date('d');
		$en_blanco_encabezado = str_pad(' ',104," ",STR_PAD_LEFT);
        $linea = 'HRFACTURACIONGHR'.$fecha_linea.'00001'.$en_blanco_encabezado;
        if (fwrite($fileRefresh, $linea."\r\n") === FALSE) {
            $claseMensaje="alert alert-danger";
            $ok=false;   
            $mensaje="NO SE PUDO GENERAR EL ARCHIVO refresh.";
            $continua = FALSE;
        } else {
        	//insert el encabezado en pagoMisCuentas
			$fecha_linea = date('Ymd');
			$en_blanco_encabezado = str_pad('', 264, '0', STR_PAD_LEFT);
	        $linea = '04002199'.$fecha_linea.$en_blanco_encabezado;
	        if (fwrite($filePMC, $linea."\r\n") === FALSE) {
	            $claseMensaje="alert alert-danger";
	            $ok=false;   
	            $mensaje="NO SE PUDO GENERAR EL ARCHIVO pagoMisCuentas.";
	            $continua = FALSE;
	        }
        }

        if ($continua) {
        	$total = 0;
		    foreach ($resHomeBanking['datos'] as $dato) {
		    	$idLinkPagos = $dato['idLinkPagos'];
		    	$concepto = $dato['concepto'];
	    		$matricula = $dato['matricula'];
			    $mensajeTicket = $dato['mensajeTicket'];
			    $mensajePantalla = $dato['mensajePantalla'];
			    $total += $dato['importe'];

		        $matricula=str_pad($matricula,8,"0",STR_PAD_LEFT);
		        $fechaVencimiento = $dato['fechaVencimiento'];
		        $fechaPrimerVencimiento = substr($fechaVencimiento, 2, 2).substr($fechaVencimiento, 5, 2).substr($fechaVencimiento, 8, 2);
		        $fechaSegundoVencimiento = $fechaPrimerVencimiento;
		        $importePrimerVto = str_pad((intval($dato['importe']) * 100), 12, '0', STR_PAD_LEFT);
		        $importeSegundoVto = $importePrimerVto;

		        //pata LINK
				$fecha_linea1 = date('m').substr(date('Y'), 2, 2);
				$en_blanco_linea1 = str_pad('', 11, ' ', STR_PAD_LEFT);
				$en_blanco_linea2 = str_pad('', 50, ' ', STR_PAD_LEFT);
				$linea = '0'.$fecha_linea1.$concepto.$matricula.$en_blanco_linea1.$fechaPrimerVencimiento.$importePrimerVto.$fechaSegundoVencimiento.$importeSegundoVto.'000000'.'000000000000'.$en_blanco_linea2;
		        //fwrite($file, $linea."\r\n");
		        if (fwrite($fileRefresh, $linea."\r\n") === FALSE) {
		                $claseMensaje="alert alert-danger";
		                $ok=false;   
		                $mensaje="NO SE PUDO GENERAR EL ARCHIVO TXT.";
		        }

        		//para pago mis cuentas
				$en_blanco_linea1 = str_pad('', 11, ' ', STR_PAD_LEFT);
				$en_blanco_linea2 = str_pad('', 10, ' ', STR_PAD_LEFT);
				$en_blanco_linea3 = str_pad('', 20, ' ', STR_PAD_LEFT);
		        $idLinkPagos = str_pad($idLinkPagos, 10, "0", STR_PAD_LEFT);
		        $fechaPrimerVencimiento = substr($fechaVencimiento, 0, 4).substr($fechaVencimiento, 5, 2).substr($fechaVencimiento, 8, 2);
		        $fechaSegundoVencimiento = $fechaPrimerVencimiento;
		        $importePrimerVto = str_pad((intval($dato['importe']) * 100), 11, '0', STR_PAD_LEFT);
		        $importeSegundoVto = $importePrimerVto;
				$en_cero_linea1 = str_pad('', 19, '0', STR_PAD_LEFT);
				$en_cero_linea2 = str_pad('', 29, '0', STR_PAD_LEFT);
				$mensajeTicket = str_pad($mensajeTicket, 40, ' ', STR_PAD_RIGHT);
				$mensajePantalla = str_pad($mensajePantalla, 15, ' ', STR_PAD_RIGHT);
	    		$codigoBarra = $colegiadoDeudaAnualLogic->obtenerCodigoBarra($idLinkPagos, $dato['importe'], $dato['importe'], $fechaVencimiento, $fechaVencimiento, NULL);
				$codigoBarra = str_pad($codigoBarra, 60, ' ', STR_PAD_RIGHT);
				$linea = '5'.$matricula.$en_blanco_linea1.$idLinkPagos.$en_blanco_linea2.'0'.$fechaPrimerVencimiento.$importePrimerVto.$fechaSegundoVencimiento.$importeSegundoVto.$fechaSegundoVencimiento.$importeSegundoVto.$en_cero_linea1.$matricula.$en_blanco_linea1.$mensajeTicket.$mensajePantalla.$codigoBarra.$en_cero_linea2;
		        //fwrite($file, $linea."\r\n");
		        if (fwrite($filePMC, $linea."\r\n") === FALSE) {
	                $claseMensaje="alert alert-danger";
	                $ok=false;   
	                $mensaje="NO SE PUDO GENERAR EL ARCHIVO TXT.";
		        }

		    }

		    //guardo el registro final de LINK
		    $cantidadRegistros += 2;
		    $cantidadRegistrosLinea = str_pad($cantidadRegistros, 8, '0', STR_PAD_LEFT);
		    $importePrimerVto = str_pad((intval($total) * 100), 18, '0', STR_PAD_LEFT);
	        $importeSegundoVto = $importePrimerVto;
			$en_blanco_linea2 = str_pad('', 56, ' ', STR_PAD_LEFT);
			//para LINK
			$linea='TRFACTURACION'.$cantidadRegistrosLinea.$importePrimerVto.$importeSegundoVto.'000000000000000000'.$en_blanco_linea2;
	        if (fwrite($fileRefresh, $linea."\r\n") === FALSE) {
                $claseMensaje="alert alert-danger";
                $ok=false;   
                $mensaje="NO SE PUDO GENERAR EL ARCHIVO TXT.";
	        }
		    fclose($fileRefresh);

		    //guarda control
			$fechaActual = date('Ymd');
		    $cantidadRegistrosControl = str_pad((intval($cantidadRegistros) * 133), 10, '0', STR_PAD_LEFT);
			$en_blanco_linea = str_pad('', 37, ' ', STR_PAD_LEFT);
			$linea='HRPASCTRL'.$fechaActual.'GHR'.$refresh.$cantidadRegistrosControl.$en_blanco_linea;
	        if (fwrite($fileControl, $linea."\r\n") === FALSE) {
                $claseMensaje="alert alert-danger";
                $ok=false;   
                $mensaje="NO SE PUDO GENERAR EL ARCHIVO TXT.";
	        }
		    $cantidadRegistrosControl = str_pad($cantidadRegistros, 8, '0', STR_PAD_LEFT);
			$linea='LOTES00001'.$cantidadRegistrosControl.$importePrimerVto.$importeSegundoVto.'000000000000000000   ';
	        if (fwrite($fileControl, $linea."\r\n") === FALSE) {
                $claseMensaje="alert alert-danger";
                $ok=false;   
                $mensaje="NO SE PUDO GENERAR EL ARCHIVO TXT.";
	        }
	        $fechaPrimerVencimiento = substr($fechaVencimiento, 0, 4).substr($fechaVencimiento, 5, 2).substr($fechaVencimiento, 8, 2);
			$linea='FINAL'.$cantidadRegistrosControl.$importePrimerVto.$importeSegundoVto.'000000000000000000'.$fechaPrimerVencimiento;
	        if (fwrite($fileControl, $linea."\r\n") === FALSE) {
                $claseMensaje="alert alert-danger";
                $ok=false;   
                $mensaje="NO SE PUDO GENERAR EL ARCHIVO TXT.";
	        }
		    fclose($fileControl);

			//para pagoMisCuentas
			$fechaActual = date('Ymd');
		    $cantidadRegistros -= 2;
		    $cantidadRegistrosLinea = str_pad($cantidadRegistros, 7, '0', STR_PAD_LEFT);
			$en_cero_linea1 = str_pad('', 7, '0', STR_PAD_LEFT);
		    $importePrimerVto = str_pad((intval($total) * 100), 11, '0', STR_PAD_LEFT);
			$en_cero_linea2 = str_pad('', 239, '0', STR_PAD_LEFT);
			$linea='94002199'.$fechaActual.$cantidadRegistrosLinea.$en_cero_linea1.$importePrimerVto.$en_cero_linea2;
	        if (fwrite($filePMC, $linea."\r\n") === FALSE) {
                $claseMensaje="alert alert-danger";
                $ok=false;   
                $mensaje="NO SE PUDO GENERAR EL ARCHIVO TXT.";
	        }
		    fclose($filePMC);
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
    <form name="myForm"  method="POST" action="../home_banking.php">
        <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
        <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
        <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
    </form>
</body>

