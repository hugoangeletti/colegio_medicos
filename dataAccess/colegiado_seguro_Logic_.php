<?php
//accesos a tabla colegiado
function verificarEstadoMatricularTesoreria($procesoAnio, $procesoMes, $fechaLimiteProceso) {
    $db = Database::getConnection();

    $cantidadActualizados = 0;
    $cantidadNoActualizados = 0;
    $cantidadAltas = 0;
    $cantidadBajas = 0;
    $cantidadVigentes = 0;

    $pathNombre = 'seguro/lotes/'.$procesoAnio;
    $archivoAltas = 'Altas_'.$procesoAnio.'-'.$procesoMes;
    $archivoBajas = 'Bajas_'.$procesoAnio.'-'.$procesoMes;
    $archivoCompleto = 'Vigentes_'.$procesoAnio.'-'.$procesoMes;
    $idSeguroPraxisMedicaEnvios = NULL;

    $colegiado_seguro_Logic = new colegiado_seguro_Logic();
    $resSeguro = $colegiado_seguro_Logic->guardarSegurosProcesados($idSeguroPraxisMedicaEnvios, $procesoAnio, $procesoMes, $fechaLimiteProceso, $cantidadVigentes, $cantidadAltas, $cantidadBajas, $pathNombre, $archivoAltas, $archivoBajas, $archivoCompleto);
    if ($resSeguro['estado']) {
        $idSeguroPraxisMedicaEnvios = $resSeguro['idSeguroPraxisMedicaEnvios'];
        /*
        $resultado['cantidadActualizados'] = $cantidadActualizados;
        $resultado['cantidadNoActualizados'] = $cantidadNoActualizados;
        $resultado['cantidadAltas'] = $cantidadAltas;
        */
        //primero se actualizan los datos de los ya registrados, si hay alguna actualizacion de estado matricular o de estado con tesoreria
        $sql = "SELECT cs.Id, cs.Matricula, cs.IdEstadoMatricular, cs.EstadoTesoreria, cs.FechaActualizacion, cs.Activo, c.Id, c.Estado, c.FechaActualizacion, tm.Estado, cs.CuotasAdeudadas, cs.MotivoBaja
                FROM colegiado_seguro cs
                INNER JOIN colegiado c ON c.Matricula = cs.Matricula
                INNER JOIN tipomovimiento tm ON tm.Id = c.Estado
                WHERE cs.Borrado = 0";

        $resultado = array();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([]);
            $rows = $stmt->fetchAll();

            if (count($rows) > 0) {
                foreach ($rows as $r) {
                	$idColegiadoSeguro = $r[0];
                	$matricula = $r[1];
                	$idEstadoMatricular = $r[2];
                	$estadoTesoreria = $r[3];
                	$fechaActualizacion = $r[4];
                	$activo = $r[5];
                	$idColegiado = $r[6];
                	$estadoActual = $r[7];
                	$fechaActualizacionActual = $r[8];
                	$codigoEstadoMatricular = $r[9];
                	$cuotasAdeudadas = $r[10];
                	$motivoBaja = $r[11];

                	$actualizarSeguro = FALSE;
                	$idEstadoMatricularOrigen = $idEstadoMatricular;
                	$estadoTesoreriaOrigen = $estadoTesoreria;
                	$cuotasAdeudadasOrigen = $cuotasAdeudadas;
                	$activoOrigen = $activo;
                	$motivoBajaOrigen = $motivoBaja;

                	//echo 'id->'.$idColegiadoSeguro.', matricula->'.$matricula.', idEstadoMatricular->'.$idEstadoMatricular.', estadoTesoreria->'.$estadoTesoreria.', fechaActualizacion->'.$fechaActualizacion.', activo->'.$activo.', idColegiado->'.$idColegiado.', estadoActual->'.$estadoActual.', fechaActualizacionActual->'.$fechaActualizacionActual.', codigoEstadoMatricular->'.$codigoEstadoMatricular.', cuotasAdeudadas->'.$cuotasAdeudadas.', motivoBaja->'.$motivoBaja;

                	//verifica el estado actual del colegiado
                	if (!isset($idEstadoMatricular) || $idEstadoMatricular <> $estadoActual) {
                		//echo ' cambio estado ';
                		$actualizaEstadoMatricular = TRUE;
                		if ($codigoEstadoMatricular == 'A') {
                			//si es activo el estado matricular, se pone en ACTIVO el seguro
                			$activo = 1;
                			$motivoBaja = '';
                		} else {
                			if ($estadoActual == 2) {
                				//si es cancelacion transitoria el estado matricular, se pone en ACTIVO el seguro, ver si pasa los 45 dias
    		           			$activo = 1;
    	            			$motivoBaja = '';
                			} else {
    		           			$activo = 0;
    	            			$motivoBaja = 'CANCELACION';
                			}
                		}
                		$actualizarSeguro = TRUE;
                		$fechaActualizacion = $fechaActualizacionActual;
                		$idEstadoMatricular = $estadoActual;
                	} else {
                		$actualizaEstadoMatricular = FALSE;
                	}

                	/*
                	si esta ACTIVO el seguro, verifica el estado con tesoreria
                	no esta ACTIVO por deudor, debo volver a verificar el estado con tesoreria
                	*/
                	if ($activo == 1 || ($activo == 0 && $estadoTesoreria == 'DEUDOR')) {
                		//echo ' ver deuda '.'<br>';
                		$resTesoreria = $colegiado_seguro_Logic->verificarEstadoTesoreria($idColegiado, $fechaLimiteProceso);
                		//var_dump($resTesoreria);
                		if ($resTesoreria['estado']) {
                			$tesoreria = $resTesoreria['datos'];
                			$estadoTesoreria = $tesoreria['estadoTesoreria'];
                			$cuotasAdeudadas = $tesoreria['cuotasAdeudadas'];
                			$activo = $tesoreria['activo'];
                			$estadoSeguro = $tesoreria['estadoSeguro'];
    		           		$motivoBaja = $tesoreria['motivoBaja'];
                            if ($motivoBaja == 'AL_DIA') {
                                $fechaActualizacion = $tesoreria['ultimaFechaPago'];
                            }
    		           		$actualizarSeguro = TRUE;
                		} else {
                			$actualizaEstadoTesoreria = FALSE;
                		}
    				}

    			    //actualiza los datos del seguro
                	if ($actualizarSeguro) {
                		if ($idEstadoMatricularOrigen <> $idEstadoMatricular || $estadoTesoreriaOrigen <> $estadoTesoreria || $cuotasAdeudadasOrigen <> $cuotasAdeudadas || $activoOrigen <> $activo || $motivoBajaOrigen <> $motivoBaja) {
    	            		$sql_2 = "UPDATE colegiado_seguro
    					            SET IdEstadoMatricular = ?, FechaActualizacion = ?,
    					            	EstadoTesoreria = ?,
    					            	CuotasAdeudadas = ?,
    					            	Activo = ?,
    					            	MotivoBaja = ?,
    					            	FechaCarga = NOW()
    					            WHERE Id = ?";
    				        $stmt_2 = $db->prepare($sql_2);
    				        $stmt_2->execute([$idEstadoMatricular, $fechaActualizacion, $estadoTesoreria, $cuotasAdeudadas, $activo, $motivoBaja, $idColegiadoSeguro]);
    				        if($stmt_2->rowCount() >= 0) {
    				        	$cantidadActualizados += 1;

    				        	//se guardan el original
    				        	$datosOrigen = '{ "idEstadoMatricular": '.$idEstadoMatricularOrigen.',
    	            						"estadoTesoreria": '.$estadoTesoreriaOrigen.',
    	            						"cuotasAdeudadas": '.$cuotasAdeudadasOrigen.',
    	            						"activo": '.$activoOrigen.',
    	            						"motivoBaja": '.$motivoBajaOrigen.' }';
    							//print_r($datosOrigen);

    	            			$sql_2 = "INSERT INTO colegiado_seguro_movimiento (IdColegiadoSeguro, DatosOriginales, FechaCarga) VALUES (?, ?, NOW())";
    					        $stmt_2 = $db->prepare($sql_2);
    					        $stmt_2->execute([$idColegiadoSeguro, $datosOrigen]);
    				        }
    				    } else {
    				    	$cantidadNoActualizados += 1;
    				    }
                	}
                }
            }

            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['cantidadActualizados'] = $cantidadActualizados;
            $resultado['cantidadNoActualizados'] = $cantidadNoActualizados;
            $resultado['cantidadAltas'] = $cantidadAltas;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando colegiados: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        //se agregan las altas que no existen en el original de colegiado_seguro ni en colegiado_agremiado_asegurado
        $sql = "SELECT c.Matricula, c.Estado, c.FechaActualizacion, c.Id, tm.Estado,
        	(SELECT COUNT(a.Id)
    			FROM colegiadodeudaanualcuotas a
    			INNER JOIN colegiadodeudaanual b ON b.Id = a.IdColegiadoDeudaAnual
    			WHERE b.IdColegiado = c.Id AND a.Estado = 1 AND a.FechaVencimiento < DATE(NOW())) AS CuotasAdudadas,
        	if (c.Estado = 2, if (c.FechaActualizacion IS NOT NULL, TIMESTAMPDIFF(DAY, c.FechaActualizacion, DATE(NOW())), TIMESTAMPDIFF(DAY, c.FechaCarga, DATE(NOW()))), 0) AS CantidadDias
    		FROM colegiado c
            INNER JOIN tipomovimiento tm ON tm.Id = c.Estado
    		LEFT JOIN colegiado_agremiado_asegurado caa ON caa.Matricula = c.Matricula
    		LEFT JOIN colegiado_seguro cs ON cs.Matricula = c.Matricula
    		WHERE c.Estado IN(1, 2, 5, 10) AND caa.Id IS NULL AND cs.Id IS NULL
    		HAVING CantidadDias <= 45 AND CuotasAdudadas <= 6";

        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([]);
            $rows = $stmt->fetchAll();

            if (count($rows) > 0) {
                foreach ($rows as $r) {
                    $matricula = $r[0];
                    $idEstadoMatricular = $r[1];
                    $fechaActualizacionActual = $r[2];

    	            $sql_2 = "INSERT INTO colegiado_seguro (Matricula, IdEstadoMatricular, FechaActualizacion, EstadoTesoreria, CuotasAdeudadas, Activo, MotivoBaja, FechaCarga) VALUES (?, ?, ?, 'AL_DIA', 0, NULL, '', NOW())";
    		        $stmt_2 = $db->prepare($sql_2);
    		        $stmt_2->execute([$matricula, $idEstadoMatricular, $fechaActualizacion]);
    		        $cantidadAltas += 1;
                }
            }
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['cantidadAltas'] = $cantidadAltas;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando altas: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        //guarda los totales
        $resTotales = obtenerTotalesPeriodoProcesado($idSeguroPraxisMedicaEnvios);

        $resSeguro = $colegiado_seguro_Logic->guardarSegurosProcesados($idSeguroPraxisMedicaEnvios, $procesoAnio, $procesoMes, $fechaLimiteProceso, $cantidadVigentes, $cantidadAltas, $cantidadBajas, $pathNombre, $archivoAltas, $archivoBajas, $archivoCompleto);
        if ($resSeguro['estado']) {
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error guardando envios de seguro praxis medica";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
    } else {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error guardando envios de seguro praxis medica -> ".$resSeguro['mensaje'];
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

function verificarEstadoTesoreria($idColegiado, $fechaLimiteProceso) {
    try {
        $db = Database::getConnection();

    	$sql_1 = "SELECT COUNT(a.Id), MAX(a.FechaPago)
    			FROM colegiadodeudaanualcuotas a
    			INNER JOIN colegiadodeudaanual b ON b.Id = a.IdColegiadoDeudaAnual
    			WHERE b.IdColegiado = ? AND a.Estado = 1 AND a.FechaVencimiento < ?";

        $stmt_1 = $db->prepare($sql_1);
        $stmt_1->execute([$idColegiado, $fechaLimiteProceso]);
        $row = $stmt_1->fetch();

        if ($row) {
            $cuotasAdeudadas = $row[0];
            $ultimaFechaPago = $row[1];
            if ($cuotasAdeudadas <= 6) {
            	$activo = 1;
            	$estadoTesoreria = 'AL_DIA';
            	$motivoBaja = '';
    		} else {
    			$activo = 0;
    			$estadoTesoreria = 'DEUDOR';
       			$motivoBaja = 'DEUDA';
    		}

    		$actualizarSeguro = TRUE;
    		$actualizaEstadoTesoreria = TRUE;

    		$datos = array(
                    'activo' => $activo,
                    'estadoTesoreria' => $estadoTesoreria,
                    'actualizarSeguro' => $actualizarSeguro,
                    'actualizaEstadoTesoreria' => $actualizaEstadoTesoreria,
                    'cuotasAdeudadas' => $cuotasAdeudadas,
                    'motivoBaja' => $motivoBaja,
                    'ultimaFechaPago' => $ultimaFechaPago
                		);
    		$resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
    	} else {
    		$actualizaEstadoTesoreria = FALSE;
    		$resultado['estado'] = $actualizaEstadoTesoreria;
            $resultado['mensaje'] = "NO EXISTEN CUOTAS";
    	}
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR BUSCANDO CUOTAS: " . $e->getMessage();
    }

    return $resultado;
}

function obtenerSeguroPorColegiado($idColegiado) {
    try {
        $db = Database::getConnection();

    	$sql_1 = "(SELECT 'COLEGIO' AS Origen, cs.Activo, cs.MotivoBaja, cs.Matricula, cs.PathArchivo, cs.NombreArchivo, cs.FechaCarga
        FROM colegiado_seguro cs
        INNER JOIN colegiado c ON c.Matricula = cs.Matricula
        WHERE c.Id = ? AND cs.Borrado = 0)
        UNION ALL
        (SELECT 'AGREMIACION' AS Origen, cas.Activo, '' AS MotivoBaja, cas.Matricula, '' AS PathArchivo, '' AS NombreArchivo, cas.FechaCarga
        FROM colegiado_agremiado_asegurado cas
        INNER JOIN colegiado c ON c.Matricula = cas.Matricula
        WHERE c.Id = ? AND cas.Borrado = 0 AND cas.Activo = 1)";

        $stmt_1 = $db->prepare($sql_1);
        $stmt_1->execute([$idColegiado, $idColegiado]);
        $row = $stmt_1->fetch();

        if ($row) {
    		$datos = array(
                    'origen' => $row['Origen'],
                    'activo' => $row['Activo'],
                    'motivoBaja' => $row['MotivoBaja'],
                    'matricula' => $row['Matricula'],
                    'pathArchivo' => $row['PathArchivo'],
                    'nombreArchivo' => $row['NombreArchivo'],
                    'fechaCarga' => $row['FechaCarga']
                	);
    		$resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
    	} else {
    		$resultado['estado'] = true;
    		$resultado['datos'] = NULL;
            $resultado['mensaje'] = "NO EXISTEN CUOTAS";
    	}
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR BUSCANDO CUOTAS: " . $e->getMessage();
    }

    return $resultado;
}

function guardarSegurosProcesados($idSeguroPraxisMedicaEnvios, $procesoAnio, $procesoMes, $fechaLimiteProceso, $cantidadVigentes, $cantidadAltas, $cantidadBajas, $pathNombre, $archivoAltas, $archivoBajas, $archivoCompleto){
    try {
        $db = Database::getConnection();

        //si entra para agregar viene sin idSeguroPraxisMedicaEnvios
        if (!isset($idSeguroPraxisMedicaEnvios)) {
            $sql = "INSERT INTO seguro_praxis_medica_envios (ProcesoAnio, ProcesoMes, FechaLimiteProceso, CantidadVigentes, CantidadAltas, CantidadBajas, PathNombre, ArchivoAltas, ArchivoBajas, ArchivoCompleto, FechaCarga)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $db->prepare($sql);
            $stmt->execute([$procesoAnio, $procesoMes, $fechaLimiteProceso, $cantidadVigentes, $cantidadAltas, $cantidadBajas, $pathNombre, $archivoAltas, $archivoBajas, $archivoCompleto]);
        } else {
            $sql = "UPDATE seguro_praxis_medica_envios
                    SET CantidadVigentes = ?, CantidadAltas = ?, CantidadBajas = ?
                    WHERE Id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$cantidadVigentes, $cantidadAltas, $cantidadBajas, $idSeguroPraxisMedicaEnvios]);
        }

        $resultado = array();
        if (!isset($idSeguroPraxisMedicaEnvios)) {
            $resultado['idSeguroPraxisMedicaEnvios'] = $db->lastInsertId();
        }
        $resultado['estado'] = true;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado = array();
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error guardando envio de seguro -> " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

function obtenerSegurosProcesados(){
    try {
        $db = Database::getConnection();

        //primero se actualizan los datos de los ya registrados, si hay alguna actualizacion de estado matricular o de estado con tesoreria
        $sql = "SELECT s.Id, s.ProcesoAnio, s.ProcesoMes, s.FechaLimiteProceso, s.CantidadVigentes, s.CantidadAltas, s.CantidadBajas, s.PathNombre, s.ArchivoAltas, s.ArchivoBajas, s.ArchivoCompleto
                FROM seguro_praxis_medica_envios s
                WHERE s.Borrado = 0";
        $stmt = $db->prepare($sql);
        $stmt->execute([]);
        $rows = $stmt->fetchAll();

        $resultado = array();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $row = array (
                        'idSeguro' => $r['Id'],
                        'procesoAnio' => $r['ProcesoAnio'],
                        'procesoMes' => $r['ProcesoMes'],
                        'fechaLimiteProceso' => $r['FechaLimiteProceso'],
                        'cantidadVigentes' => $r['CantidadVigentes'],
                        'cantidadAltas' => $r['CantidadAltas'],
                        'cantidadBajas' => $r['CantidadBajas'],
                        'pathNombre' => $r['PathNombre'],
                        'archivoAltas' => $r['ArchivoAltas'],
                        'archivoBajas' => $r['ArchivoBajas'],
                        'archivoCompleto' => $r['ArchivoCompleto']
                 );
                array_push($datos, $row);
            }
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "No hay envio de seguro";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado = array();
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando envio de seguro: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

function obtenerUltimoProcesoEnvioSeguro() {
    try {
        $db = Database::getConnection();

        $sql_1 = "SELECT a.ProcesoAnio, a.ProcesoMes, a.FechaLimiteProceso
                FROM seguro_praxis_medica_envios a
                ORDER BY a.ProcesoAnio DESC, a.ProcesoMes DESC
                LIMIT 1";

        $stmt_1 = $db->prepare($sql_1);
        $stmt_1->execute([]);
        $row = $stmt_1->fetch();

        if ($row) {
            $datos = array(
                    'procesoAnio' => $row['ProcesoAnio'],
                    'procesoMes' => $row['ProcesoMes'],
                    'fechaDesde' => $row['FechaLimiteProceso']
                    );
        } else {
            $datos = NULL;
        }
        $resultado['estado'] = true;
        $resultado['mensaje'] = "OK";
        $resultado['datos'] = $datos;
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR BUSCANDO PROCESOS: " . $e->getMessage();
    }

    return $resultado;
}
