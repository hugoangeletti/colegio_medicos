<?php
function procesarLote($idLugarPago, $path, $archivoProcesar, $archivoLote, $fechaApertura) {
	//verificamo segun el lugar de pago, si el archivo es correcto y si ya no existe, y la fecha del archivo coincide con la ingresada
	switch ($idLugarPago) {
        case '22':
            // bapro
            $arrLineas = array(file($archivoProcesar));
            $cantidadLineas=sizeof($arrLineas[0]);

            //print_r($arrLineas);
            $continua = TRUE;
            $idCobranza = NULL;
            $totalRecaudacion = 0;
            $cantidadComprobantes = 0;
            for ($i=0; $i<$cantidadLineas; $i++) {
                $linea = $arrLineas[0][$i];
                //print_r($linea);
                //echo '<br>';
                //continue;
                //procesar los pagos y aplicarlos segun el tipo de pago que sea
                if (substr($linea, 8, 9) == 'BaproPago') {
                    //es el encabezado
                    if (!isset($idCobranza)) {
                        $comprobantesRendido = substr($linea, 25, 5);
                        $importeRendido = intval(substr($linea, 72, 10)) / 100;
                        //$fechaApertura = substr($linea, 28, 4).'-'.substr($linea, 32, 2).'-'.substr($linea, 34, 2);
                        $resCobranza = $cobranzaLogic->agregarLoteCobranza($idLugarPago, $fechaApertura, $archivoLote);
                        if ($resCobranza['estado']) {
                            $idCobranza = $resCobranza['idCobranza'];
                        } else {
                            $continua = FALSE;
                            $mensaje = $resCobranza['mensaje'];
                            $i = $cantidadLineas + 1;
                        }
                    }
                } else {
                    //es un pago, entonces lo proceso
                    if ($continua) {
                        //verifico el tamaño del codigo de barras para obtener los datos del comprobante
                        $lineacodebar = trim(substr($linea, 0, 60));
                        if (strlen($lineacodebar) == 40) {
                            //codigo de barras corto
                            $comprobante = substr($linea, 21, 7);
                        } else {
                            if (strlen($lineacodebar) == 44) {
                                //codigo de barras largo
                                $comprobante = substr($linea, 23, 7);
                            } else {
                                $comprobante = NULL;
                            }                                            
                        }

                        $matricula = NULL;
                        $concepto = NULL;
                        $fechaPago = substr($linea, 60, 4).'-'.substr($linea, 64, 2).'-'.substr($linea, 66, 2);
                        $importeParcial = intval(substr($linea, 72, 10)) / 100;
                        $idLinkPagos = NULL;
                        $idAsistente = NULL;
                        $periodo = NULL;
                        $cuota = NULL;
                        //echo $linea[2].'->'.$comprobante;

                        //procesar pago
                        if (isset($comprobante)) {
                            switch (substr($comprobante, 0, 1)) {
                                case '0':
                                    $tipoComprobante = 1;   //si empieza con 9 es una cuota de plan de pagos    
                                    break;

                                case '9':
                                    $tipoComprobante = 4;   //si empieza con 9 es una cuota de plan de pagos
                                    $comprobante = intval(substr($comprobante, 1, 7));
                                    break;

                                case '8':
                                    $tipoComprobante = 8;   //pago total
                                    $comprobante = intval(substr($comprobante, 1, 7));
                                    break;

                                case '7';
                                    $tipoComprobante = 6;   //pago curso
                                    $idCursosAsistenteCuota = intval(substr($comprobante, 1, 7));
                                    $comprobante = $idCursosAsistenteCuota;
                                    $cursos_pdo = new cursos_pdo();
                                    $resAsistente = $cursos_pdo->obtenerAsistentePorIdCuotaCurso($idCursosAsistenteCuota);
                                    //var_dump($resAsistente); exit;
                                    if ($resAsistente['estado']) {
                                        $idAsistente = $resAsistente['datos']['idAsistente'];
                                    } else {
                                        $idAsistente = NULL;
                                    }
                                    break; 

                                default:
                                    $tipoComprobante = 2;   //es cuota de colegiacion
                                    break;
                            }
                            $resPago = $cobranzaLogic->cargaPago($importeParcial, $tipoComprobante, $comprobante, $periodo, $fechaPago, $idCobranza, $idAsistente, $cuota);
                            if ($resPago['estado']) {
                                $totalRecaudacion += $importeParcial;
                                $cantidadComprobantes += 1;
                            } else {
                                echo var_dump($resPago);
                            }
                        } else {
                            echo 'Error en la linea del lote. Longitud erronea '.strlen($linea).'<br>';
                        }
                    }
                }                                
            }
            if ($cantidadComprobantes > 0) {
                //echo $cantidadComprobantes.' - '.$totalRecaudacion;
                //$comprobantesRendido = $cantidadComprobantes;
                //$comprobantesRendido = intval($comprobantesRendido) - 2;
                //$importeRendido = $totalRecaudacion;
                $observacion = NULL;
                $resCobranza = $cobranzaLogic->modificarLoteCobranza($idCobranza, $comprobantesRendido, $importeRendido, $cantidadComprobantes, $totalRecaudacion, $observacion);
                if (!$resCobranza['estado']) {
                    $continua = FALSE;
                    $mensaje = $resCobranza['mensaje'];
                }
            }

            //elimina el archivo y lo pasa al año correspondiente si fue procesado
            if ($continua) {
                $path .= "/procesado";
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }
                $archivoProcesado = $path . '/' . $archivoLote;
                rename($archivoProcesar, $archivoProcesado);
            }
            
            break;

        case '23':
            // PagoFacil
            $arrLineas = array(file($archivoProcesar));
            $cantidadLineas=sizeof($arrLineas[0]);

            //print_r($arrLineas);

            $continua = TRUE;
            $idCobranza = NULL;
            $totalRecaudacion = 0;
            $cantidadComprobantes = 0;
            for ($i=0; $i<$cantidadLineas; $i++) {
                $linea = explode(',', $arrLineas[0][$i]);
                //print_r($linea);
                //echo '<br>';
                //continue;
                //procesar los pagos y aplicarlos segun el tipo de pago que sea
                if ($linea[0] == '70108') {
                    //es un pago, entonces lo proceso
                    if ($continua) {
                        if (strlen($linea[2]) == 11) {
                            //saco las "" para obtener el numero
                            $comprobante = str_replace('"', '', $linea[2]);
                            $matricula = NULL;
                            $concepto = NULL;
                            $fechaPago = substr($linea[4], 6, 4).'-'.substr($linea[4], 3, 2).'-'.substr($linea[4], 0, 2);
                            $importeParcial = $linea[7];
                            $idLinkPagos = NULL;
                            $comprobante = substr($comprobante, 2, 7);
                            $idAsistente = NULL;
                            $periodo = NULL;
                            $cuota = NULL;
                        } else {
                            $comprobante = NULL;
                        }
                            //echo $linea[2].'->'.$comprobante;

                        //procesar pago
                        if (isset($comprobante)) {
                            switch (substr($comprobante, 0, 1)) {
                                case '0':
                                    $tipoComprobante = 1;   //si empieza con 9 es una cuota de plan de pagos    
                                    break;

                                case '9':
                                    $tipoComprobante = 4;   //si empieza con 9 es una cuota de plan de pagos
                                    $comprobante = intval(substr($comprobante, 1, 7));
                                    break;

                                case '8':
                                    $tipoComprobante = 8;   //pago total
                                    $comprobante = intval(substr($comprobante, 1, 7));
                                    break;

                                case '7';
                                    $tipoComprobante = 6;   //pago curso
                                    $idCursosAsistenteCuota = intval(substr($comprobante, 1, 7));
                                    $comprobante = $idCursosAsistenteCuota;
                                    $cursos_pdo = new cursos_pdo();
                                    $resAsistente = $cursos_pdo->obtenerAsistentePorIdCuotaCurso($idCursosAsistenteCuota);
                                    //var_dump($resAsistente); exit;
                                    if ($resAsistente['estado']) {
                                        $idAsistente = $resAsistente['datos']['idAsistente'];
                                    } else {
                                        $idAsistente = NULL;
                                    }
                                    break; 

                                default:
                                    $tipoComprobante = 2;   //es cuota de colegiacion
                                    break;
                            }
                            $resPago = $cobranzaLogic->cargaPago($importeParcial, $tipoComprobante, $comprobante, $periodo, $fechaPago, $idCobranza, $idAsistente, $cuota);
                            if ($resPago['estado']) {
                                $totalRecaudacion += $importeParcial;
                                $cantidadComprobantes += 1;
                            } else {
                                echo var_dump($resPago);
                            }
                        } else {
                            echo 'Error en la linea del lote. Longitud erronea '.strlen($linea).'<br>';
                        }
                    }
                } else {
                    if (!isset($idCobranza)) {
                        //$fechaApertura = substr($linea, 28, 4).'-'.substr($linea, 32, 2).'-'.substr($linea, 34, 2);
                        $resCobranza = $cobranzaLogic->agregarLoteCobranza($idLugarPago, $fechaApertura, $archivoLote);
                        if ($resCobranza['estado']) {
                            $idCobranza = $resCobranza['idCobranza'];
                        } else {
                            $continua = FALSE;
                            $mensaje = $resCobranza['mensaje'];
                            $i = $cantidadLineas + 1;
                        }
                    }
                }                                
            }
            if ($cantidadComprobantes > 0) {
                //echo $cantidadComprobantes.' - '.$totalRecaudacion;
                $comprobantesRendido = $cantidadComprobantes;
                //$comprobantesRendido = intval($comprobantesRendido) - 2;
                $importeRendido = $totalRecaudacion;
                $observacion = NULL;
                $resCobranza = $cobranzaLogic->modificarLoteCobranza($idCobranza, $comprobantesRendido, $importeRendido, $cantidadComprobantes, $totalRecaudacion, $observacion);
                if (!$resCobranza['estado']) {
                    $continua = FALSE;
                    $mensaje = $resCobranza['mensaje'];
                }
            }

            //elimina el archivo y lo pasa al año correspondiente si fue procesado
            if ($continua) {
                $path .= "/procesado";
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }
                $archivoProcesado = $path . '/' . $archivoLote;
                rename($archivoProcesar, $archivoProcesado);
            }
            
            break;

        case '24':
            // Agremiacion de la plata
            $arrLineas = array(file($archivoProcesar));
            $cantidadLineas=sizeof($arrLineas[0]);

            $continua = TRUE;
            $idCobranza = NULL;
            $fechaApertura = date('Y-m-d');
            $totalRecaudacion = 0;
            $cantidadComprobantes = 0;
            for ($i=0; $i<$cantidadLineas; $i++) {
                $linea = $arrLineas[0][$i];
                $lineaPago = explode(";", $linea);
                if (!isset($idCobranza) || $idCobranza == 0) {
                    $resCobranza = $cobranzaLogic->agregarLoteCobranza($idLugarPago, $fechaApertura, $archivoLote);
                    if ($resCobranza['estado']) {
                        $idCobranza = $resCobranza['idCobranza'];
                    } else {
                        $continua = FALSE;
                        $mensaje = "Error al procesar el archivo ".$archivoProcesar;
                        break;
                    }
                }

                if ($idCobranza > 0) {
                    $matricula = $lineaPago[0];
                    $concepto = NULL;
                    $fechaPago = $fechaApertura;
                    $importeParcial = $lineaPago[3];
                    $idLinkPagos = NULL;
                    $idAsistente = NULL;
                    $periodo = $lineaPago[1];
                    $cuota = $lineaPago[2];
                    $comprobante = $colegiadoDeudaAnualLogic->obtenerComprobantePorMatriculaCuota($matricula, $periodo, $cuota);
                    $tipoComprobante = 2;   //es cuota de colegiacion
                    $resPago = $cobranzaLogic->cargaPago($importeParcial, $tipoComprobante, $comprobante, $periodo, $fechaPago, $idCobranza, $idAsistente, $cuota);
                    if ($resPago['estado']) {
                        $totalRecaudacion += $importeParcial;
                        $cantidadComprobantes += 1;
                    } else {
                        echo var_dump($resPago);
                    }
                }
            }

            if ($cantidadComprobantes > 0) {
                $comprobantesRendido = $cantidadComprobantes;
                $importeRendido = $totalRecaudacion;
                $observacion = NULL;
                $resCobranza = $cobranzaLogic->modificarLoteCobranza($idCobranza, $comprobantesRendido, $importeRendido, $cantidadComprobantes, $totalRecaudacion, $observacion);
                if (!$resCobranza['estado']) {
                    $continua = FALSE;
                    $mensaje = $resCobranza['mensaje'];
                }
            }

            //elimina el archivo y lo pasa al año correspondiente si fue procesado
            if ($continua) {
                $path .= "/procesado";
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }
                $archivoProcesado = $path . '/' . $archivoLote;
                rename($archivoProcesar, $archivoProcesado);
            }

            break;

        case '25':
            // RapiPago
            $arrLineas = array(file($archivoProcesar));
            $cantidadLineas=sizeof($arrLineas[0]);

            //print_r($arrLineas);

            $continua = TRUE;
            $idCobranza = NULL;
            $totalRecaudacion = 0;
            $cantidadComprobantes = 0;
            for ($i=0; $i<$cantidadLineas; $i++) {
                $linea = trim($arrLineas[0][$i]);
                //echo $linea.' -> '.strlen($linea).'<br>';

                //procesar los pagos y aplicarlos segun el tipo de pago que sea
                switch (substr($linea, 0, 1)) {
                    case '0':
                        //es encabezado
                        if (!isset($idCobranza)) {
                            $fechaApertura = substr($linea, 28, 4).'-'.substr($linea, 32, 2).'-'.substr($linea, 34, 2);
                            $resCobranza = $cobranzaLogic->agregarLoteCobranza($idLugarPago, $fechaApertura, $archivoLote);
                            if ($resCobranza['estado']) {
                                $idCobranza = $resCobranza['idCobranza'];
                            } else {
                                $continua = FALSE;
                                $mensaje = $resCobranza['mensaje'];
                                $i = $cantidadLineas + 1;
                            }
                        }
                        break;

                    case '9':
                        if ($continua) {
                            $comprobantesRendido = substr($linea, 8, 8);
                            //$comprobantesRendido = intval($comprobantesRendido) - 2;
                            $importeRendido = substr($linea, 16, 18) / 100;
                            $observacion = NULL;
                            $resCobranza = $cobranzaLogic->modificarLoteCobranza($idCobranza, $comprobantesRendido, $importeRendido, $cantidadComprobantes, $totalRecaudacion, $observacion);
                            if (!$resCobranza['estado']) {
                                $continua = FALSE;
                                $mensaje = $resCobranza['mensaje'];
                            }
                        }
                        break;

                    default:
                        //es linea de pago
                        if ($continua) {
                            if (strlen($linea) == 63) {
                                //codigo de barras corto
                                $matricula = NULL;
                                $concepto = NULL;
                                $fechaPago = substr($linea, 0, 4).'-'.substr($linea, 4, 2).'-'.substr($linea, 6, 2);
                                $importeParcial = substr($linea, 8, 15) / 100;
                                $idLinkPagos = NULL;
                                $comprobante = substr($linea, 44, 7);
                                $idAsistente = NULL;
                                $periodo = NULL;
                                $cuota = NULL;
                            } else {
                                if (strlen($linea) == 67) {
                                    //codigo de barras corto
                                    $matricula = NULL;
                                    $concepto = NULL;
                                    $fechaPago = substr($linea, 0, 4).'-'.substr($linea, 4, 2).'-'.substr($linea, 6, 2);
                                    $importeParcial = substr($linea, 8, 15) / 100;
                                    $idLinkPagos = NULL;
                                    $comprobante = substr($linea, 46, 7);
                                    $idAsistente = NULL;
                                    $periodo = NULL;
                                    $cuota = NULL;
                                } else {
                                    $comprobante = NULL;
                                }                                            
                            }

                            //procesar pago
                            if (isset($comprobante)) {
                                switch (substr($comprobante, 0, 1)) {
                                    case '0':
                                        $tipoComprobante = 1;   //si empieza con 9 es una cuota de plan de pagos    
                                        break;

                                    case '9':
                                        $tipoComprobante = 4;   //si empieza con 9 es nota de deuda
                                        $comprobante = intval(substr($comprobante, 1, 7));
                                        break;

                                    case '8':
                                        $tipoComprobante = 8;   //pago total
                                        $comprobante = intval(substr($comprobante, 1, 7));
                                        break;

                                    case '7';
                                        $tipoComprobante = 6;   //pago curso
                                        $idCursosAsistenteCuota = intval(substr($comprobante, 1, 7));
                                        $comprobante = $idCursosAsistenteCuota;
                                        $cursos_pdo = new cursos_pdo();
                                        $resAsistente = $cursos_pdo->obtenerAsistentePorIdCuotaCurso($idCursosAsistenteCuota);
                                    //var_dump($resAsistente); 
                                        if ($resAsistente['estado']) {
                                            $idAsistente = $resAsistente['datos']['idAsistente'];
                                        } else {
                                            $idAsistente = NULL;
                                        }
                                        break; 

                                    default:
                                        //verifico que si la longitud el comprobante es menor a 7, es cuota de plan de pagos
                                        $tipoComprobante = 2;   //es cuota de colegiacion
                                        break;
                                }
                                $resPago = $cobranzaLogic->cargaPago($importeParcial, $tipoComprobante, $comprobante, $periodo, $fechaPago, $idCobranza, $idAsistente, $cuota);
                                if ($resPago['estado']) {
                                    $totalRecaudacion += $importeParcial;
                                    $cantidadComprobantes += 1;
                                } else {
                                    echo var_dump($resPago);
                                }
                            } else {
                                echo 'Error en la linea del lote. Longitud erronea '.strlen($linea).'<br>';
                            }
                        }
                        break;
                    
                }
            }

            //elimina el archivo y lo pasa al año correspondiente si fue procesado
            if ($continua) {
                $path .= "/procesado";
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }
                $archivoProcesado = $path . '/' . $archivoLote;
                rename($archivoProcesar, $archivoProcesado);
            }

            break;

        case '26':
            // Link
            $arrLineas = array(file($archivoProcesar));
            $cantidadLineas=sizeof($arrLineas[0]);

            //print_r($arrLineas);

            $continua = TRUE;
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
                            $fechaApertura = substr($linea, 4, 4).'-'.substr($linea, 8, 2).'-'.substr($linea, 10,2);
                            $resCobranza = $cobranzaLogic->agregarLoteCobranza($idLugarPago, $fechaApertura, $archivoLote);
                            if ($resCobranza['estado']) {
                                $idCobranza = $resCobranza['idCobranza'];
                            } else {
                                $continua = FALSE;
                                $mensaje = $resCobranza['mensaje'];
                                $i = $cantidadLineas + 1;
                            }
                        }
                        break;

                    case '1':
                        //es linea de pago
                        if ($continua) {
                            $matricula_idAsistente = intval(substr($linea, 9, 8));
                            $concepto = substr($linea, 6, 3);
                            //$fechaPago = substr($linea, 29, 4).'-'.substr($linea, 33, 2).'-'.substr($linea, 35, 2); ENTE 0182 hasta el 07/09/2022
                            $fechaPago = substr($linea, 40, 4).'-'.substr($linea, 44, 2).'-'.substr($linea, 46, 2); //ENTE 0GHR desde el 08/09/2022
                            //$importeParcial = substr($linea, 17, 12) / 100; ENTE 0182 hasta el 07/09/2022
                            $importeParcial = substr($linea, 28, 12) / 100; //ENTE 0GHR desde el 08/09/2022
                            $idLinkPagos = NULL;
                            $comprobante = NULL;

                            //procesar pago de homeBanking (tabla linkpagos)
                            //echo $matricula.', '.$concepto.'<br>';
                            $resProcesaHomeBanking = $cobranzaLogic->procesarPagoHomeBanking($idCobranza, $idLinkPagos, $matricula_idAsistente, $fechaPago, $importeParcial, $comprobante, $concepto);
                            if ($resProcesaHomeBanking['estado']) {
                                $totalRecaudacion += $importeParcial;
                                $cantidadComprobantes += 1;
                            }
                        }
                        break;
                    
                    case '2':
                        if ($continua) {
                            $comprobantesRendido = substr($linea, 1, 6);
                            $comprobantesRendido = intval($comprobantesRendido) - 2;
                            $importeRendido = substr($linea, 7, 16) / 100;
                            $observacion = NULL;
                            $resCobranza = $cobranzaLogic->modificarLoteCobranza($idCobranza, $comprobantesRendido, $importeRendido, $cantidadComprobantes, $totalRecaudacion, $observacion);
                            if (!$resCobranza['estado']) {
                                $continua = FALSE;
                                $mensaje = $resCobranza['mensaje'];
                            }
                        }
                        break;

                    default:
                        // code...
                        echo 'error->'.substr($linea, 0, 1).'-><br>';
                        break;
                }
            }
            //elimina el archivo y lo pasa al año correspondiente si fue procesado
            if ($continua) {
                $path .= "/procesado";
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }
                $archivoProcesado = $path . '/' . $archivoLote;
                rename($archivoProcesar, $archivoProcesado);
            }

            break;

        case '28':
            // Debito Tarjeta
            $arrLineas = array(file($archivoProcesar));
            $cantidadLineas=sizeof($arrLineas[0]);

            //print_r($arrLineas);

            $continua = TRUE;
            $idCobranza = NULL;
            $totalRecaudacion = 0;
            $cantidadComprobantes = 0;
            $observacion = "";
            for ($i=0; $i<$cantidadLineas; $i++) {
                $linea = $arrLineas[0][$i];

                //procesar los pagos y aplicarlos segun el tipo de pago que sea
                switch (substr($linea, 0, 1)) {
                    case '0':
                        //es linea de encabezado
                        $tipoTarjeta = '';
                        if (substr($linea, 0, 9) == '0LDEBLIQD') {
                            $tipoTarjeta = 'D';
                        } else {
                            if (substr($linea, 0, 9) == '0RDEBLIQC') {
                                $tipoTarjeta = 'C';
                            }
                        }

                        if (!isset($idCobranza)) {
                            $resCobranza = $cobranzaLogic->agregarLoteCobranza($idLugarPago, $fechaApertura, $archivoLote);
                            if ($resCobranza['estado']) {
                                $idCobranza = $resCobranza['idCobranza'];
                            } else {
                                $continua = FALSE;
                                $mensaje = $resCobranza['mensaje'];
                                $i = $cantidadLineas + 1;
                            }                                        
                        }
                        break;

                    case '1':
                        //es linea de pago
                        if ($continua) {
                            switch ($tipoTarjeta) {
                                case 'C':
                                    //tarjeta de Credito
                                    if (substr($linea, 129, 3) == '000') {
                                        $comprobante = substr($linea, 42, 8);
                                        $idEnvioDebitoDetalle = substr($linea, 42, 8);
                                        $anioPago = 2000 + substr($linea, 228, 2);
                                        $mesPago = substr($linea, 226, 2);
                                        $diaPago = substr($linea, 224, 2);
                                        $importeParcial = substr($linea, 62, 15) / 100;

                                        if (substr($comprobante, 0, 1) == '9') {
                                            $tipoComprobante = 1;   //si empieza con 9 es una cuota de plan de pagos
                                        } else {
                                            if (substr($comprobante, 1, 1) == '8') {
                                                //pago total
                                                $tipoComprobante = 8;
                                            } else {
                                                //es cuota de colegiacion
                                                $tipoComprobante = 2;   
                                            }
                                        }
                                        $fechaPago = $anioPago.'-'.$mesPago.'-'.$diaPago;
                                        //$comprobante = substr($linea, 43, 5);
                                        //$resultado = $cobranzaLogic->procesarPagoDebitoTarjeta($idCobranza, $fechaPago, $importeParcial, $comprobante, $tipoTarjeta, $tipoComprobante);
                                        $resultado = $cobranzaLogic->procesarPagoDebitoAutomatico($idCobranza, $fechaPago, $importeParcial, $idEnvioDebitoDetalle);
                                        $cantidadComprobantes++;
                                        $totalRecaudacion += $importeParcial;
                                    } else {
                                        //no se pudo hacer el debito, entonces se carga en novedades para enviar el mail
                                        $numeroDocumento = substr($linea, 94, 15);
                                        $idColegiado = NULL;
                                        $detalle = substr($linea, 130, 62).' Nº: '.substr($linea, 26, 16);

                                        $resultado = $cobranzaLogic->cargarNovedades($idCobranza, $idColegiado, $numeroDocumento, $detalle);
                                        $observacion = 'NOVEDADES: hay debitos que no se pudieron realizar.';
                                    }
                                    break;
                                case 'D':
                                    if (substr($linea, 101, 2) == '  ') {
                                        $comprobante = substr($linea, 20, 8);   //para procesarPagoDebitoTarjeta
                                        $idEnvioDebitoDetalle = substr($linea, 20, 8);  //para procesarPagoDebitoAutomatico
                                        $anioPago = substr($linea, 28, 4);
                                        $mesPago = substr($linea, 32, 2);
                                        $diaPago = substr($linea, 34, 2);
                                        $importeParcial = substr($linea, 40, 15) / 100;

                                        if (substr($comprobante, 0, 1) =='9') {
                                            $tipoComprobante = 1;   //si empieza con 9 es una cuota de plan de pagos
                                        } else {
                                            $tipoComprobante = 2;   //es cuota de colegiacion
                                        }
                                        
                                        $fechaPago = $anioPago.'-'.$mesPago.'-'.$diaPago;

                                        //$resultado = $cobranzaLogic->procesarPagoDebitoTarjeta($idCobranza, $fechaPago, $importeParcial, $comprobante, $tipoTarjeta, $tipoComprobante);
                                        $resultado = $cobranzaLogic->procesarPagoDebitoAutomatico($idCobranza, $fechaPago, $importeParcial, $idEnvioDebitoDetalle);
                                        $cantidadComprobantes++;
                                        $totalRecaudacion += $importeParcial;
                                    } else {
                                        //no se pudo hacer el debito, entonces se carga en novedades para enviar el mail
                                        $numeroDocumento = substr($linea, 55, 15);
                                        $idColegiado = NULL;
                                        $detalle = substr($linea, 100, 50).' Nº: '.substr($linea, 2, 16);

                                        $resultado = $cobranzaLogic->cargarNovedades($idCobranza, $idColegiado, $numeroDocumento, $detalle);
                                        $observacion = 'NOVEDADES: hay debitos que no se pudieron realizar.';
                                    }
                                    break;

                                default:
                                    // code...
                                    break;
                            }
                        }
                        break;

                    case '9':
                        //echo $cantidadComprobantes.' - '.$totalRecaudacion;
                        if ($continua) {
                            $comprobantesRendido = $cantidadComprobantes;
                            $importeRendido = $totalRecaudacion;
                            $resCobranza = $cobranzaLogic->modificarLoteCobranza($idCobranza, $comprobantesRendido, $importeRendido, $cantidadComprobantes, $totalRecaudacion, $observacion);
                            if (!$resCobranza['estado']) {
                                $continua = FALSE;
                                $mensaje = $resCobranza['mensaje'];
                            }
                        }
                        break;

                    default:
                        // code...
                        break;
                }
            }

            //elimina el archivo y lo pasa al año correspondiente si fue procesado
            if ($continua) {
                $path .= "/procesado";
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }
                $archivoProcesado = $path . '/' . $archivoLote;
                rename($archivoProcesar, $archivoProcesado);
            }

            break;

		case '29':
            $arrLineas = array(file($archivoProcesar));
            $cantidadLineas=sizeof($arrLineas[0]);

            //print_r($arrLineas);

            $continua = TRUE;
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
                                $mensaje = $resCobranza['mensaje'];
                                $i = $cantidadLineas + 1;
                            }
                        }
                        break;

                    case '5':
                        //es linea de pago
                        if ($continua) {
                            $matricula_idAsistente = intval(substr($linea, 1, 8));
                            $idLinkPagos = substr($linea,20, 10);
                            $fechaPago = substr($linea, 49, 4).'-'.substr($linea, 53, 2).'-'.substr($linea, 55, 2);
                            $importeParcial = substr($linea, 57, 11) / 100;
                            $comprobante = substr($linea, 20, 10);
                            $concepto = NULL;

                            //procesar pago de homeBanking (tabla linkpagos)
                            $resProcesaHomeBanking = $cobranzaLogic->procesarPagoHomeBanking($idCobranza, $idLinkPagos, $matricula_idAsistente, $fechaPago, $importeParcial, $comprobante, $concepto);
                            if ($resProcesaHomeBanking['estado']) {
                                $totalRecaudacion += $importeParcial;
                                $cantidadComprobantes += 1;
                            }
                        }
                        break;
                    
                    case '9':
                        //echo $cantidadComprobantes.' - '.$totalRecaudacion;
                        if ($continua) {
                            $comprobantesRendido = substr($linea, 16, 7);
                            $importeRendido = substr($linea, 23, 18) / 100;
                            $observacion = NULL;
                            $resCobranza = $cobranzaLogic->modificarLoteCobranza($idCobranza, $comprobantesRendido, $importeRendido, $cantidadComprobantes, $totalRecaudacion, $observacion);
                            if (!$resCobranza['estado']) {
                                $continua = FALSE;
                                $mensaje = $resCobranza['mensaje'];
                            }
                        }
                        break;

                    default:
                        // code...
                        break;
                }
			}

            //elimina el archivo y lo pasa al año correspondiente si fue procesado
            if ($continua) {
        		$path .= "/procesado";
    			if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }
                $archivoProcesado = $path . '/' . $archivoLote;
                rename($archivoProcesar, $archivoProcesado);
            }

			break;
		
        case '30': //debito por CBU
            $arrLineas = array(file($archivoProcesar));
            $cantidadLineas=sizeof($arrLineas[0]);

            //print_r($arrLineas);

            $observacion = NULL;
            $idCobranza = NULL;
            $totalRecaudacion = 0;
            $cantidadComprobantes = 0;
            for ($i=0; $i<$cantidadLineas; $i++) {
                $linea = $arrLineas[0][$i];

                //procesar los pagos y aplicarlos segun el tipo de pago que sea
                switch (substr($linea, 0, 1)) {
                    case '1':
                        //es linea de pago
                        if (!isset($idCobranza)) {
                            $anio = substr($linea, 21, 4);
                            $mes = substr($linea, 25, 2);
                            $dia = substr($linea, 27, 2);
                            $fechaApertura = $anio.'-'.$mes.'-'.$dia;
                            $importeRendido = substr($linea, 29, 18) / 100;
                            $resCobranza = $cobranzaLogic->agregarLoteCobranza($idLugarPago, $fechaApertura, $archivoLote);
                            $comprobantesRendido = $cantidadLineas - 1;
                            if ($resCobranza['estado']) {
                                $idCobranza = $resCobranza['idCobranza'];
                                $continua = TRUE;
                            } else {
                                $continua = FALSE;
                                $mensaje = $resCobranza['mensaje'];
                                $i = $cantidadLineas + 1;
                            }
                        }
                        break;

                    case '0':
                        //es linea de pago
                        if ($continua) {
                            $detalleRechazo = trim(substr($linea, 83, 4));
                            if ($detalleRechazo == "") {
                                $fechaPago = substr($linea, 87, 4).'-'.substr($linea, 91, 2).'-'.substr($linea, 93, 2);
                                $importeParcial = substr($linea, 97, 14) / 100;
                                $idEnvioDebitoDetalle = intval(substr($linea, 44, 22));
                                $concepto = NULL;
                                $idLinkPagos = NULL;
                                $matricula = NULL;


                                //echo 'idCobranza->'.$idCobranza.', fechaPago->'.$fechaPago.', importeParcial->'.$importeParcial.', idEnvioDebitoDetalle->'.$idEnvioDebitoDetalle.'<br>';

                                //procesar pago de homeBanking (tabla linkpagos)
                                //$resProcesaHomeBanking = $cobranzaLogic->procesarPagoCBU($idCobranza, $fechaPago, $importeParcial, $idEnvioDebitoDetalle);
                                $resProcesaHomeBanking = $cobranzaLogic->procesarPagoDebitoAutomatico($idCobranza, $fechaPago, $importeParcial, $idEnvioDebitoDetalle);
                                if ($resProcesaHomeBanking['estado']) {
                                    $totalRecaudacion += $importeParcial;
                                    $cantidadComprobantes += 1;
                                }
                            } else {
                                //vino rechazado
                            //echo "rechazo ->".$detalleRechazo.'<br>';
                                //no se pudo hacer el debito, entonces se carga en novedades para enviar el mail
                                $idDebito = intval(substr($linea, 44, 8));
                                $resDebito = $colegiadoDebitosLogic->obtenerDebitoCBUporIdDebito($idDebito);
                                if ($resDebito['estado']) {
                                    $numeroDocumento = $resDebito['datos']['numeroDocumento'];
                                    $idColegiado = $resDebito['datos']['idColegiado'];
                                } else {
                                    $numeroDocumento = NULL;
                                    $idColegiado = NULL;
                                }
                                switch ($detalleRechazo) {
                                    case 'R10':
                                        $tipoRechazo = "FALTA DE FONDOS";
                                        break;
                                    case 'R02':
                                        $tipoRechazo = "CUENTA CERRADA O SUSPENDIDA";
                                        break;
                                    case 'R03':
                                        $tipoRechazo = "CUENTA INEXISTENTE";
                                        break;
                                    case 'R04':
                                        $tipoRechazo = "NUMERO DE CUENTA INVALIDO";
                                        break;
                                    case 'R13':
                                        $tipoRechazo = "El codigo de banco no existe";
                                        break;
                                    case 'R17':
                                        $tipoRechazo = "ERROR DE FORMATO";
                                        break;
                                    case '034':
                                        $tipoRechazo = "NUMERO DE CUENTA INVALIDO";
                                        break;
                                    case 'R78':
                                        $tipoRechazo = "ERROR EN CTA A DEB / ACRED - REG. INDIVID";
                                        break;
                                    default:
                                        $tipoRechazo = "";
                                        break;
                                }
                                
                                $detalleRechazo .= " - ".$tipoRechazo;
                                $cantidadComprobantes += 1;

                                $resultado = $cobranzaLogic->cargarNovedades($idCobranza, $idColegiado, $numeroDocumento, $detalleRechazo);
                                $observacion = 'NOVEDADES: hay debitos que no se pudieron realizar.';
                            }
                        }
                        break;
                    
                    default:
                        // code...
                        break;
                }
            }
                //cierra el lote
                //echo $cantidadComprobantes.' - '.$totalRecaudacion;
                $resCobranza = $cobranzaLogic->modificarLoteCobranza($idCobranza, $comprobantesRendido, $importeRendido, $cantidadComprobantes, $totalRecaudacion, $observacion);
                if (!$resCobranza['estado']) {
                    $continua = FALSE;
                    $mensaje = $resCobranza['mensaje'];
                }

            //elimina el archivo y lo pasa al año correspondiente si fue procesado
            if ($continua) {
                $path .= "/procesado";
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }
                $archivoProcesado = $path . '/' . $archivoLote;
                rename($archivoProcesar, $archivoProcesado);
            }

            break;
        
		default:
			$continua = FALSE;
            $mensaje = "idLugarPago erroneo (".$idLugarPago.")";
			break;
	}

    $resultado['estado'] = $continua;
    if (!isset($mensaje) || $mensaje == "") {
        $resultado['mensaje'] = "OK - Archivo procesado: ".$archivoLote;
        $resultado['clase'] = "alert alert-success";
    } else {
        $resultado['mensaje'] = $mensaje;
    }

    return $resultado;
}
