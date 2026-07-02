<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/cobranzaLogic.php');
$cobranzaLogic = new cobranzaLogic();
require_once ('../../dataAccess/colegiadoDeudaAnualLogic.php');

$continua = TRUE;
$mensaje = "";
if (isset($_POST['idLugarPago']) && $_POST['idLugarPago'] <> "") {
	$idLugarPago = $_POST['idLugarPago'];
} else {
	$continua = FALSE;
	$mensaje .= "Falta idLugarPago - ";
}

/*
if (isset($_POST['fechaApertura']) && $_POST['fechaApertura'] <> "") {
	$fechaApertura = $_POST['fechaApertura'];
} else {
	$continua = FALSE;
	$mensaje .= "Falta fechaApertura - ";
}
*/

if ($_FILES['archivoLote']['name'] != "") {
    $fileTmpPath = $_FILES['archivoLote']['tmp_name'];	
    $archivoLote = $_FILES['archivoLote']['name'];
    $tipoArchivo = $_FILES['archivoLote']['type'];
    $tamanioArchivo = $_FILES['archivoLote']['size'];
} else {
	$continua = FALSE;
	$mensaje .= "Falta archivoLote - ";	
}

if ($continua) {
	//verificamo segun el lugar de pago, si el archivo es correcto y si ya no existe, y la fecha del archivo coincide con la ingresada
	switch ($idLugarPago) {
        case '26':
            // Link
            $nombreArchivo = $archivoLote;
            $extensionArchivo = '';

            if ($_FILES["archivoLote"]["type"] <> "application/octet-stream") {
                $mensaje = "Archivo no coincide con la estructra";
                $continua = FALSE;
            } else {
                $resLote = $cobranzaLogic->obtenerLote($idLugarPago, $archivoLote);
                if (isset($resLote['idCobranza'])) {
                    $hayArchivos = FALSE;
                    $anio = date('Y');
                    $mes = substr($archivoLote, 4, 2);
                    if ($mes == '12' && date('m') < 12) {
                        $anio -= 1;
                    }
                    $dia = substr($archivoLote, 6, 2);
                    //$fechaApertura = $anio.'-'.$mes.'-'.$dia;
                    $path = "../../archivos/lotes/".$idLugarPago."/".$anio;
                    $archivoProcesar = $path."/".$archivoLote;
                    $procesado = TRUE;
                    echo '<br>'.$path.'<br>';
                    echo '<br>'.$archivoProcesar.'<br>';
                    //subir archivo y procesarlo
                    if (!file_exists($path)) {
                        mkdir($path, 0777, true);
                    }

                    if (move_uploaded_file($fileTmpPath, $archivoProcesar)) {
                    //if (file_exists($archivoProcesar)) {
                        $arrLineas=array();
                        $arrLineas = array(file($archivoProcesar));
                        $cantidadLineas=sizeof($arrLineas[0]);

                        //print_r($arrLineas);

                        $idCobranza = $resLote['idCobranza'];
                        echo 'idLote->'.$idCobranza.'<br>';
                        $totalRecaudacion = 0;
                        $cantidadComprobantes = 0;
                        $encontrado = FALSE;
                        for ($i=0; $i<$cantidadLineas; $i++) {
                            $linea = $arrLineas[0][$i];
                            if (substr($linea, 0, 1) == '1') {
                                //es linea de pago
                                if ($continua) {
                                    $matricula = intval(substr($linea, 9, 8));
                                    $concepto = substr($linea, 6, 3);
                                    $fechaPago = substr($linea, 29, 4).'-'.substr($linea, 33, 2).'-'.substr($linea, 35, 2);
                                    $importeParcial = substr($linea, 17, 12) / 100;
                                    $idLinkPagos = NULL;
                                    $comprobante = NULL;

                                    //si ya existe en el lote, no lo proceso
                                    if ($encontrado) {
                                        //procesar pago de homeBanking (tabla linkpagos)
                                        $resProcesaHomeBanking = $cobranzaLogic->procesarPagoHomeBanking($idCobranza, $idLinkPagos, $matricula, $fechaPago, $importeParcial, $comprobante, $concepto);
                                        if ($resProcesaHomeBanking['estado']) {
                                            $totalRecaudacion += $importeParcial;
                                            $cantidadComprobantes += 1;
                                        }
                                    }
                                    if ($matricula == 119028) {
                                        $encontrado = TRUE;
                                    }
                                }
                            }
                        }
                    } else {
                        $procesado = FALSE;
                        $mensaje = "Error al subir el archivo ";
                    }

                    //elimina el archivo y lo pasa al año correspondiente si fue procesado
                    if ($procesado) {
                        $path .= "/procesado";
                        if (!file_exists($path)) {
                            mkdir($path, 0777, true);
                        }
                        $archivoProcesado = $path . '/' . $archivoLote;
                        rename($archivoProcesar, $archivoProcesado);
                    }
                    /*
                    //abrir archivo por FTP
                    $path = "/AppWin/cobranza/29-PagoMisCuentas/procesados/".$anio."/";
                    $archivoProcesar = @fopen (FTP_ARCHIVOS.$path.$archivoLote, "rb");
                    echo '<br>'.FTP_ARCHIVOS.$path.'<br>';
                    if ($archivoProcesar) {
                        $mensaje = "El Archivo '.$archivoLote.' ya existe en procesados";
                        $continua = FALSE;
                    }
                    */
                } else {
                    $mensaje = "El Archivo '.$archivoLote.' NO fue procesado";
                    $continua = FALSE;
                }
            }

            break;

		case '29':
            $fileName = explode(".", $archivoLote);
            $nombreArchivo = $fileName[0];
            $extensionArchivo = $fileName[1];
			// PagoMisCuentas
            if ($_FILES["archivoLote"]["type"] <> "application/octet-stream") {
            	$mensaje = "Archivo no coincide con la estructra";
            	$continua = FALSE;
            } else {
            	if (!$cobranzaLogic->verificarArchivoExistente($idLugarPago, $archivoLote)) {
            		$hayArchivos = FALSE;
            		$anio = substr($extensionArchivo, 4, 2) + 2000;
            		$mes = substr($extensionArchivo, 2, 2);
            		$dia = substr($extensionArchivo, 0, 2);
            		$fechaApertura = $anio.'-'.$mes.'-'.$dia;
            		$path = "../../archivos/lotes/".$idLugarPago."/".$anio;
            		$archivoProcesar = $path."/".$archivoLote;
                    $procesado = TRUE;
            		echo '<br>'.$path.'<br>';
                    echo '<br>'.$archivoProcesar.'<br>';
        			//subir archivo y procesarlo
        			if (!file_exists($path)) {
                        mkdir($path, 0777, true);
                    }

                    if (move_uploaded_file($fileTmpPath, $archivoProcesar)) {
                    //if (file_exists($archivoProcesar)) {
                        $arrLineas=array();
                        $arrLineas = array(file($archivoProcesar));
                        $cantidadLineas=sizeof($arrLineas[0]);

                        //print_r($arrLineas);

                        $idCobranza = NULL;
                        $totalRecaudacion = 0;
                        $cantidadComprobantes = 0;
                        for ($i=0; $i<$cantidadLineas; $i++) {
                            $linea = $arrLineas[0][$i];

                            //procesar los pagos y aplicarlos segun el tipo de pago que sea
                            switch (substr($linea, 0, 1)) {
                                case '0':
                                    //es linea de pago
                                    if (!isset($idCobranza)) {
                                        $resCobranza = $cobranzaLogic->agregarLoteCobranza($idLugarPago, $fechaApertura, $archivoLote);
                                        if ($resCobranza['estado']) {
                                            $idCobranza = $resCobranza['idCobranza'];
                                        } else {
                                            $continua = FALSE;
                                            $procesado = FALSE;
                                            $mensaje = $resCobranza['mensaje'];
                                            $i = $cantidadLineas + 1;
                                        }
                                    }
                                    break;

                                case '5':
                                    //es linea de pago
                                    if ($continua) {
                                        $matricula = intval(substr($linea, 1, 8));
                                        $idLinkPagos = substr($linea,20, 10);
                                        $fechaPago = substr($linea, 49, 4).'-'.substr($linea, 53, 2).'-'.substr($linea, 55, 2);
                                        $importeParcial = substr($linea, 57, 11) / 100;
                                        $comprobante = substr($linea, 20, 10);
                                        $concepto = NULL;

                                        //procesar pago de homeBanking (tabla linkpagos)
                                        $resProcesaHomeBanking = $cobranzaLogic->procesarPagoHomeBanking($idCobranza, $idLinkPagos, $matricula, $fechaPago, $importeParcial, $comprobante, $concepto);
                                        if ($resProcesaHomeBanking['estado']) {
                                            $totalRecaudacion += $importeParcial;
                                            $cantidadComprobantes += 1;
                                        }
                                    }
                                    break;
                                
                                case '9':
                                    echo $cantidadComprobantes.' - '.$totalRecaudacion;
                                    $comprobantesRendido = substr($linea, 16, 7);
                                    $importeRendido = substr($linea, 23, 18) / 100;
                                    $resCobranza = $cobranzaLogic->modificarLoteCobranza($idCobranza, $comprobantesRendido, $importeRendido, $cantidadComprobantes, $totalRecaudacion);
                                    if (!$resCobranza['estado']) {
                                        $continua = FALSE;
                                        $mensaje = $resCobranza['mensaje'];
                                    }
                                    break;

                                default:
                                    // code...
                                    break;
                            }
        				}
                    } else {
                    	$procesado = FALSE;
                        $mensaje = "Error al subir el archivo ";
                    }

                    //elimina el archivo y lo pasa al año correspondiente si fue procesado
                    if ($procesado) {
	            		$path .= "/procesado";
	        			if (!file_exists($path)) {
	                        mkdir($path, 0777, true);
	                    }
	                    $archivoProcesado = $path . '/' . $archivoLote;
	                    rename($archivoProcesar, $archivoProcesado);
                    }
            		/*
            		//abrir archivo por FTP
            		$path = "/AppWin/cobranza/29-PagoMisCuentas/procesados/".$anio."/";
			        $archivoProcesar = @fopen (FTP_ARCHIVOS.$path.$archivoLote, "rb");
            		echo '<br>'.FTP_ARCHIVOS.$path.'<br>';
			        if ($archivoProcesar) {
	            		$mensaje = "El Archivo '.$archivoLote.' ya existe en procesados";
	            		$continua = FALSE;
			        }
			        */
            	} else {
            		$mensaje = "El Archivo '.$archivoLote.' ya fue procesado";
            		$continua = FALSE;
            	}
            }

			break;
		
		default:
			// code...
			break;
	}
}
var_dump($archivoLote);
echo '<br>';
var_dump($continua);
echo '<br>'.$mensaje;
