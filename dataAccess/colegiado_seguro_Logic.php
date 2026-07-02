<?php
class colegiado_seguro_Logic {

    public function obtenerSeguroProcesadoPorId($idSeguro) {
    try {
        $db = Database::getConnection();

        $sql = "SELECT spm.ProcesoAnio, spm.ProcesoMes, spm.FechaLimiteProceso, spm.FechaCarga, spm.CantidadVigentes, spm.Borrado
        FROM seguro_praxis_medica_envios spm
        WHERE spm.Id = ?";

        $stmt = $db->prepare($sql);
        $stmt->execute([$idSeguro]);
        $row = $stmt->fetch();

        if ($row) {
            $datos = array(
                    'procesoAnio' => $row['ProcesoAnio'],
                    'procesoMes' => $row['ProcesoMes'],
                    'fechaLimiteProceso' => $row['FechaLimiteProceso'],
                    'fechaCarga' => $row['FechaCarga'],
                    'cantidadVigentes' => $row['CantidadVigentes'],
                    'borrado' => $row['Borrado']
                    );
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "NO EXISTEN PROCESO";
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR BUSCANDO PROCESO: " . $e->getMessage();
    }

    return $resultado;
}

    public function generarSeguroPraxisMedica($idSeguroPraxisMedicaEnviosUltimo, $procesoAnio, $procesoMes, $fechaLimiteProceso) {
    $db = Database::getConnection();

    //obtengo las matriculas de AMEPLA y se cargan en colegiado_agremiado_asegurado
    //obtengo el id del ultimo envio
    $continua = TRUE;
    $mensaje = "";
    $resSeguroEnvio = $this->obtenerUltimoProcesoEnvioSeguro();
    if ($resSeguroEnvio['estado']) {
        $seguroEnvio = $resSeguroEnvio['datos'];
        $idSeguroPraxisMedicaEnvios = $seguroEnvio['idSeguroPraxisMedicaEnvios'];

        //obtengo las matriculas por el servicio de AMEPLA
        $amepla_seguro_logic = new amepla_seguro_logic();
        $resMatriculasAmepla = $amepla_seguro_logic->obtenerMatriculasWsAmepla($procesoAnio, $procesoMes);
        if ($resMatriculasAmepla['estado']) {
            $serial_number = $resMatriculasAmepla['serial_number'];
            $agregados = 0;
            foreach ($resMatriculasAmepla['datos'] as $value) {
                $matricula = $value['matricula'];
                $resAmepla = $this->guardarColegiadoAgremiadoAsegurado($idSeguroPraxisMedicaEnvios, $matricula, $serial_number);
                if ($resAmepla['estado']) {
                    $agregados += 1;
                } else {
                    $continua = FALSE;
                    $mensaje .= "Error agregando Agremiados";
                    exit;
                }
            }
        } else {
            $continua = FALSE;
            $mensaje .= "Error WS-Agremiados";
            exit;
        }
    } else {
        $continua = FALSE;
        $mensaje .= $resSeguroEnvio['mensaje'];
    }
    //cierra AMEPLA

    if ($continua) {
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

        $resSeguro = $this->guardarSegurosProcesados($idSeguroPraxisMedicaEnvios, $procesoAnio, $procesoMes, $fechaLimiteProceso, $cantidadVigentes, $cantidadAltas, $cantidadBajas, $pathNombre, $archivoAltas, $archivoBajas, $archivoCompleto);
        if ($resSeguro['estado']) {
            $idSeguroPraxisMedicaEnvios = $resSeguro['idSeguroPraxisMedicaEnvios'];
            $fechaResidente = PERIODO_ACTUAL.'-07-01';
            //primero se actualizan los datos de los ya registrados, si hay alguna actualizacion de estado matricular o de estado con tesoreria
            $sql = "SELECT cs.Id AS idColegiadoSeguro, cs.Matricula, cs.IdEstadoMatricular, cs.EstadoTesoreria, cs.FechaActualizacion AS cseFechaActualizacion, cs.Activo, c.Id AS idColegiado, c.Estado AS estadoActual, c.FechaActualizacion AS fechaActualizacionActual, tm.Estado AS codigoEstadoMatricular, cs.CuotasAdeudadas, cs.MotivoBaja,
                (SELECT COUNT(a.Id)
                    FROM colegiadodeudaanualcuotas a
                    INNER JOIN colegiadodeudaanual b ON b.Id = a.IdColegiadoDeudaAnual
                    WHERE b.IdColegiado = c.Id AND a.Estado = 1 AND a.FechaVencimiento < DATE(NOW())) AS CuotasAdudadas,
                if (c.Estado = 2, if (c.FechaActualizacion IS NOT NULL, TIMESTAMPDIFF(DAY, c.FechaActualizacion, DATE(NOW())), TIMESTAMPDIFF(DAY, c.FechaCarga, DATE(NOW()))), 0) AS CantidadDias,
                (SELECT COUNT(cr.Id) FROM colegiadoresidente cr WHERE cr.IdColegiado = c.Id AND cr.FechaInicio >= ? AND cr.Opcion = 'EXENCION') AS Residente
                    FROM colegiado_seguro cs
                    INNER JOIN colegiado c ON c.Matricula = cs.Matricula
                    INNER JOIN tipomovimiento tm ON tm.Id = c.Estado
                    WHERE cs.IdSeguroPraxisMedicaEnvios = ? AND cs.Borrado = 0 AND (cs.Activo = 1 OR cs.Activo IS NULL)
                    ORDER BY cs.Matricula";

            $resultado = array();
            try {
                $stmt = $db->prepare($sql);
                $stmt->execute([$fechaResidente, $idSeguroPraxisMedicaEnviosUltimo]);
                $rows = $stmt->fetchAll();

                if (count($rows) > 0) {
                    foreach ($rows as $r) {
                    	$idColegiadoSeguro = $r['idColegiadoSeguro'];
                    	$matricula = $r['Matricula'];
                    	$idEstadoMatricular = $r['IdEstadoMatricular'];
                    	$estadoTesoreria = $r['EstadoTesoreria'];
                    	$fechaActualizacion = $r['cseFechaActualizacion'];
                    	$activo = $r['Activo'];
                    	$idColegiado = $r['idColegiado'];
                    	$estadoActual = $r['estadoActual'];
                    	$fechaActualizacionActual = $r['fechaActualizacionActual'];
                    	$codigoEstadoMatricular = $r['codigoEstadoMatricular'];
                    	$motivoBaja = $r['MotivoBaja'];
                    	$cuotasAdeudadas = $r['CuotasAdudadas'];
                    	$cantidadDiasCancelacionTransitoria = $r['CantidadDias'];
                    	$residente = $r['Residente'];

                    	//verifica el estado actual del colegiado
                		if ($codigoEstadoMatricular == 'A') {
                			//si es activo el estado matricular, se pone en ACTIVO el seguro
                			$activo = 1;
                			$motivoBaja = '';
                            $estadoTesoreria = 'AL_DIA';
                		} else {
                			if ($estadoActual == 2) {
                				//si es cancelacion transitoria el estado matricular, se pone en ACTIVO el seguro, ver si pasa los 45 dias
                                if ($cantidadDiasCancelacionTransitoria > 45) {
                                    $activo = 0;
                                    $motivoBaja = 'CANCELACION';
                                } else {
    	           			        $activo = 1;
    	            			    $motivoBaja = '';
                                }
                                $estadoTesoreria = 'AL_DIA';
                			} else {
    		           			$activo = 0;
    	            			$motivoBaja = 'CANCELACION';
                			}
                		}

                        if ($cuotasAdeudadas > 6) {
                            $estadoTesoreria = 'DEUDOR';
                            if ($activo == 1) {
                                $activo = 0;
                                $motivoBaja = 'DEUDA';
                            }
                        }

                        if ($residente > 0) {
                            if ($activo == 1) {
                                $activo = 0;
                                $motivoBaja = 'RESIDENTE_EXENCION';
                            }
                        }

                		$fechaActualizacion = $fechaActualizacionActual;
                		$idEstadoMatricular = $estadoActual;

                        //agrega colegiado
                        $sql_2 = "INSERT INTO colegiado_seguro (IdSeguroPraxisMedicaEnvios, Matricula, IdEstadoMatricular, FechaActualizacion, EstadoTesoreria, CuotasAdeudadas, Activo, MotivoBaja, FechaCarga) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                        $stmt_2 = $db->prepare($sql_2);
                        $stmt_2->execute([$idSeguroPraxisMedicaEnvios, $matricula, $idEstadoMatricular, $fechaActualizacion, $estadoTesoreria, $cuotasAdeudadas, $activo, $motivoBaja]);
                    }
                }

                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } catch (PDOException $e) {
                $resultado['estado'] = false;
                $resultado['mensaje'] = "Error buscando colegiados: " . $e->getMessage();
                $resultado['clase'] = 'alert alert-danger';
                $resultado['icono'] = 'glyphicon glyphicon-remove';
            }

            //se agregan las altas que no existen en el original de colegiado_seguro ni en colegiado_agremiado_asegurado
            $sql = "SELECT c.Matricula, c.Estado AS idEstadoMatricular, c.FechaActualizacion AS fechaActualizacionActual, c.Id AS idColegiado, tm.Estado AS codigoEstadoMatricular,
            	(SELECT COUNT(a.Id)
        			FROM colegiadodeudaanualcuotas a
        			INNER JOIN colegiadodeudaanual b ON b.Id = a.IdColegiadoDeudaAnual
        			WHERE b.IdColegiado = c.Id AND a.Estado = 1 AND a.FechaVencimiento < DATE(NOW())) AS CuotasAdudadas,
            	if (c.Estado = 2, if (c.FechaActualizacion IS NOT NULL, TIMESTAMPDIFF(DAY, c.FechaActualizacion, DATE(NOW())), TIMESTAMPDIFF(DAY, c.FechaCarga, DATE(NOW()))), 0) AS CantidadDias,
                (SELECT COUNT(cr.Id) FROM colegiadoresidente cr WHERE cr.IdColegiado = c.Id AND cr.FechaInicio >= ? AND cr.Opcion = 'EXENCION') AS Residente
        		FROM colegiado c
                INNER JOIN tipomovimiento tm ON tm.Id = c.Estado
        		LEFT JOIN colegiado_agremiado_asegurado caa ON caa.Matricula = c.Matricula AND caa.IdSeguroPraxisMedicaEnvios = (SELECT MAX(a.IdSeguroPraxisMedicaEnvios) FROM colegiado_agremiado_asegurado a)
        		LEFT JOIN colegiado_seguro cs ON cs.Matricula = c.Matricula AND cs.IdSeguroPraxisMedicaEnvios = ?
        		WHERE c.Estado IN(1, 2, 5, 10) AND caa.Id IS NULL AND cs.Id IS NULL
        		HAVING CantidadDias <= 45 AND CuotasAdudadas <= 6 AND Residente = 0";

            try {
                $stmt = $db->prepare($sql);
                $stmt->execute([$fechaResidente, $idSeguroPraxisMedicaEnvios]);
                $rows = $stmt->fetchAll();

                if (count($rows) > 0) {
                    foreach ($rows as $r) {
                        $matricula = $r['Matricula'];
                        $idEstadoMatricular = $r['idEstadoMatricular'];
                        $fechaActualizacionActual = $r['fechaActualizacionActual'];

        	            $sql_2 = "INSERT INTO colegiado_seguro (IdSeguroPraxisMedicaEnvios, Matricula, IdEstadoMatricular, FechaActualizacion, EstadoTesoreria, CuotasAdeudadas, Activo, MotivoBaja, FechaCarga) VALUES (?, ?, ?, ?, 'AL_DIA', 0, 1, '', NOW())";
        		        $stmt_2 = $db->prepare($sql_2);
        		        $stmt_2->execute([$idSeguroPraxisMedicaEnvios, $matricula, $idEstadoMatricular, $fechaActualizacion]);
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
            //$resTotales = obtenerTotalesPeriodoProcesado($idSeguroPraxisMedicaEnvios);

            $resSeguro = $this->guardarSegurosProcesados($idSeguroPraxisMedicaEnvios, $procesoAnio, $procesoMes, $fechaLimiteProceso, $cantidadVigentes, $cantidadAltas, $cantidadBajas, $pathNombre, $archivoAltas, $archivoBajas, $archivoCompleto);
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
    } else {
        $resultado['estado'] = false;
        $resultado['mensaje'] = $mensaje;
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function verificarEstadoTesoreria($idColegiado, $fechaLimiteProceso) {
    try {
        $db = Database::getConnection();

    	$sql_1 = "SELECT COUNT(a.Id) AS cuotasAdeudadas, MAX(a.FechaPago) AS ultimaFechaPago
    			FROM colegiadodeudaanualcuotas a
    			INNER JOIN colegiadodeudaanual b ON b.Id = a.IdColegiadoDeudaAnual
    			WHERE b.IdColegiado = ? AND a.Estado = 1 AND a.FechaVencimiento < ?";

        $stmt_1 = $db->prepare($sql_1);
        $stmt_1->execute([$idColegiado, $fechaLimiteProceso]);
        $row = $stmt_1->fetch();

        if ($row) {
            $cuotasAdeudadas = $row['cuotasAdeudadas'];
            $ultimaFechaPago = $row['ultimaFechaPago'];
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

    public function obtenerSeguroPorColegiado($idColegiado) {
    try {
        $db = Database::getConnection();

    	$sql_1 = "(SELECT 'COLEGIO' AS Origen, cs.Activo, cs.MotivoBaja, cs.Matricula, cs.PathArchivo, cs.NombreArchivo, cs.FechaCarga, CONCAT(c.Matricula, '-', REPLACE(TRIM(p.Apellido), ' ', '-'), '-', REPLACE(TRIM(p.Nombres), ' ', '-'), '.pdf') AS NombreArchivoCompleto
        FROM colegiado_seguro cs
        INNER JOIN colegiado c ON c.Matricula = cs.Matricula
        INNER JOIN persona p ON p.Id = c.IdPersona
        WHERE cs.IdSeguroPraxisMedicaEnvios = (SELECT MAX(Id) FROM seguro_praxis_medica_envios WHERE Borrado = 0) AND c.Id = ? AND cs.Borrado = 0)
        UNION ALL
        (SELECT 'AGREMIACION' AS Origen, 1 as Activo, '' AS MotivoBaja, cas.Matricula, '' AS PathArchivo, '' AS NombreArchivo, NOW() as FechaCarga, '' AS NombreArchivoCompleto
        FROM colegiado_agremiado_asegurado cas
        INNER JOIN colegiado c ON c.Matricula = cas.Matricula
        WHERE cas.IdSeguroPraxisMedicaEnvios = (SELECT MAX(a.IdSeguroPraxisMedicaEnvios) FROM colegiado_agremiado_asegurado a) AND c.Id = ?)";

        $stmt_1 = $db->prepare($sql_1);
        $stmt_1->execute([$idColegiado, $idColegiado]);
        $row = $stmt_1->fetch();

        if ($row) {
            $pathArchivo = $row['PathArchivo'];
            if (!isset($pathArchivo) || $pathArchivo == "") {
                $pathArchivo = "seguro/2024";
            }
    		$datos = array(
                    'origen' => $row['Origen'],
                    'activo' => $row['Activo'],
                    'motivoBaja' => $row['MotivoBaja'],
                    'matricula' => $row['Matricula'],
                    'pathArchivo' => $pathArchivo,
                    'nombreArchivo' => $row['NombreArchivo'],
                    'fechaCarga' => $row['FechaCarga'],
                    'nombreArchivoCompleto' => $row['NombreArchivoCompleto']
                	);
    		$resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
    	} else {
    		$resultado['estado'] = true;
    		$resultado['datos'] = NULL;
            $resultado['mensaje'] = "NO EXISTE CERTIFICADO";
    	}
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR BUSCANDO CERTIFICADO: " . $e->getMessage();
    }

    return $resultado;
}

    public function elColegiadoSeEncuentraAgremiado($matricula) {
    try {
        $db = Database::getConnection();

        $sql_1 = "SELECT SUBSTR(cas.SerialNumber, 1, 6) AS periodo
                FROM colegiado_agremiado_asegurado cas
                WHERE cas.Matricula = ?
                ORDER BY cas.Id DESC
                LIMIT 1";

        $stmt_1 = $db->prepare($sql_1);
        $stmt_1->execute([$matricula]);
        $row = $stmt_1->fetch();

        if ($row) {
            $periodoProceso = $row['periodo'];
            if ($periodoProceso >= date('Ym')) {
                $resultado = true;
            } else {
                $resultado = false;
            }
        } else {
            $resultado = false;
        }
    } catch (PDOException $e) {
        $resultado = false;
    }
    return $resultado;
}

    public function guardarSegurosProcesados($idSeguroPraxisMedicaEnvios, $procesoAnio, $procesoMes, $fechaLimiteProceso, $cantidadVigentes, $cantidadAltas, $cantidadBajas, $pathNombre, $archivoAltas, $archivoBajas, $archivoCompleto){
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

    public function obtenerSegurosProcesados(){
    try {
        $db = Database::getConnection();

        //primero se actualizan los datos de los ya registrados, si hay alguna actualizacion de estado matricular o de estado con tesoreria
        $sql = "SELECT s.Id, s.ProcesoAnio, s.ProcesoMes, s.FechaLimiteProceso, (SELECT COUNT(cs.Id) FROM colegiado_seguro cs WHERE cs.IdSeguroPraxisMedicaEnvios = s.Id AND cs.Activo = 1 AND cs.Borrado = 0) AS CantidadVigentes, s.CantidadAltas, s.CantidadBajas, s.PathNombre, s.ArchivoAltas, s.ArchivoBajas, s.ArchivoCompleto
                FROM seguro_praxis_medica_envios s
                WHERE s.Borrado = 0
                ORDER BY s.Id DESC";
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

    public function obtenerUltimoProcesoEnvioSeguro() {
    try {
        $db = Database::getConnection();

        $sql_1 = "SELECT a.Id, a.ProcesoAnio, a.ProcesoMes, a.FechaLimiteProceso
                FROM seguro_praxis_medica_envios a
                WHERE a.Borrado = 0
                ORDER BY a.ProcesoAnio DESC, a.ProcesoMes DESC
                LIMIT 1";

        $stmt_1 = $db->prepare($sql_1);
        $stmt_1->execute([]);
        $row = $stmt_1->fetch();

        if ($row) {
            $datos = array(
                    'idSeguroPraxisMedicaEnvios' => $row['Id'],
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

    public function obtenerCancelacionDeuda($idSeguroPraxisMedicaEnvios, $fechaLimiteProceso) {
    try {
        $db = Database::getConnection();

        $fechaDesde = substr($fechaLimiteProceso, 0, 8).'01';
        $fechaHasta = ultmioDiaDelMes($fechaLimiteProceso);
        $periodo = PERIODO_ACTUAL;
        /*
        $sql = "SELECT c.Matricula, COUNT(dac.Id) AS Cuotas_Abonadas, '' AS Origen, p.Apellido, p.Nombres
            FROM colegiadodeudaanualcuotas dac
            INNER JOIN colegiadodeudaanual da ON da.Id = dac.IdColegiadoDeudaAnual
            INNER JOIN colegiado c ON c.Id = da.IdColegiado AND c.Estado IN(1, 5, 10, 2, 3)
            INNER JOIN persona p ON p.Id = c.IdPersona
            LEFT JOIN colegiado_agremiado_asegurado caa ON caa.Matricula = c.Matricula
                                    AND caa.IdSeguroPraxisMedicaEnvios = (SELECT MAX(a.IdSeguroPraxisMedicaEnvios) FROM colegiado_agremiado_asegurado a)
            LEFT JOIN colegiado_seguro cs ON cs.Matricula = c.Matricula AND cs.IdSeguroPraxisMedicaEnvios = ?
            WHERE dac.Estado = 2 AND dac.FechaActualizacion BETWEEN ? AND ?
            AND caa.Id IS NULL AND cs.Id IS NOT NULL
            GROUP BY c.Matricula
            HAVING COUNT(dac.Id) > 6";
        */
        $sql = "SELECT c.Matricula, COUNT(dac.Id) AS Cuotas_Abonadas, '' AS Origen, p.Apellido, p.Nombres
            FROM colegiado_seguro cs
            INNER JOIN colegiado c ON c.Matricula = cs.Matricula
            INNER JOIN persona p ON p.Id = c.IdPersona
            INNER JOIN colegiadodeudaanual da ON da.IdColegiado = c.Id
              INNER JOIN colegiadodeudaanualcuotas dac ON da.Id = dac.IdColegiadoDeudaAnual
            LEFT JOIN colegiado_agremiado_asegurado caa ON caa.Matricula = c.Matricula
                                    AND caa.IdSeguroPraxisMedicaEnvios = (SELECT MAX(a.IdSeguroPraxisMedicaEnvios) FROM colegiado_agremiado_asegurado a)
            WHERE cs.IdSeguroPraxisMedicaEnvios = ?
              AND dac.Estado = 2 AND dac.FechaActualizacion BETWEEN ? AND ?
            AND caa.Id IS NULL
            GROUP BY c.Matricula
            HAVING COUNT(dac.Id) > 6";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idSeguroPraxisMedicaEnvios, $fechaDesde, $fechaLimiteProceso]);
        $rows = $stmt->fetchAll();

        $resultado = array();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $row = array (
                        'matricula' => $r['Matricula'],
                        'cuotasAbonadas' => $r['Cuotas_Abonadas'],
                        'lugarPago' => $r['Origen'],
                        'apellido' => $r['Apellido'],
                        'nombre' => $r['Nombres']
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
            $resultado['mensaje'] = "No hay Cuotas Abonadas";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado = array();
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando Cuotas Abonadas: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerSegurosProcesadosPorMatricula($matricula){
    try {
        $db = Database::getConnection();

        $sql = "SELECT cs.Id, cs.IdSeguroPraxisMedicaEnvios, cs.CuotasAdeudadas, cs.MotivoBaja, cs.Activo, a.ProcesoAnio, a.ProcesoMes,
            if (cs.CuotasAdeudadas = 0, 'Al dia', 'Debe cuotas') AS EstadoTesoreria,
            if (cs.CuotasAdeudadas = 0,
                (SELECT COUNT(dac.Id)
                    FROM colegiadodeudaanualcuotas dac
                    INNER JOIN colegiadodeudaanual da ON da.Id = dac.IdColegiadoDeudaAnual
                    INNER JOIN colegiado c ON c.Id = da.IdColegiado AND c.Estado IN(1, 5, 10, 2, 3)
                WHERE c.Matricula = ? AND dac.Estado = 2 AND dac.FechaActualizacion BETWEEN DATE_FORMAT(a.FechaLimiteProceso, '%Y-%m-01') AND a.FechaLimiteProceso), 'Sin Pagos') AS CuotasAbonadas,
                    DATE_FORMAT(a.FechaLimiteProceso, '%Y-%m-01') AS FechaDesde,
                    a.FechaLimiteProceso AS FechaHasta
            FROM colegiado_seguro cs
            INNER JOIN seguro_praxis_medica_envios a ON a.Id = cs.IdSeguroPraxisMedicaEnvios AND a.Borrado = 0
            WHERE cs.Matricula = ?
            ORDER BY cs.Id DESC";

        /*
        $sql = "SELECT cs.Id, cs.IdSeguroPraxisMedicaEnvios, p.Apellido, p.Nombres, cs.CuotasAdeudadas, cs.MotivoBaja, cs.Activo, spme.ProcesoAnio, spme.ProcesoMes, spme.FechaLimiteProceso
                FROM colegiado_seguro cs
                INNER JOIN colegiado c ON c.Matricula = cs.Matricula
                INNER JOIN persona p ON p.Id = c.IdPersona
                INNER JOIN seguro_praxis_medica_envios spme ON spme.Id = cs.IdSeguroPraxisMedicaEnvios
                WHERE cs.Matricula = ? AND cs.Borrado = 0
                AND spme.Borrado = 0
                ORDER BY cs.Id DESC";
        */
        $stmt = $db->prepare($sql);
        $stmt->execute([$matricula, $matricula]);
        $rows = $stmt->fetchAll();

        $resultado = array();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $row = array (
                        'idSeguroColegiado' => $r['Id'],
                        'idEnvio' => $r['IdSeguroPraxisMedicaEnvios'],
                        'cuotasAdeudadas' => $r['CuotasAdeudadas'],
                        'motivoBaja' => $r['MotivoBaja'],
                        'activo' => $r['Activo'],
                        'procesoAnio' => $r['ProcesoAnio'],
                        'procesoMes' => $r['ProcesoMes'],
                        'estadoTesoreria' => $r['EstadoTesoreria'],
                        'cuotasAbonadas' => $r['CuotasAbonadas'],
                        'fechaDesde' => $r['FechaDesde'],
                        'fechaHasta' => $r['FechaHasta']
                 );
                array_push($datos, $row);
            }
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = true;
            $resultado['datos'] = array();
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

    public function anularProceso($idSeguro) {
    try {
        $db = Database::getConnection();

        $sql = "UPDATE seguro_praxis_medica_envios
                    SET Borrado = 1
                    WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idSeguro]);

        $resultado['estado'] = true;
        $resultado['mensaje'] = "OK";
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR BORRANDO PROCESO: " . $e->getMessage();
    }

    return $resultado;

}

    public function obtenerSegurosProcesadosVigentes($idSeguro) {
    try {
        $db = Database::getConnection();

        //primero se actualizan los datos de los ya registrados, si hay alguna actualizacion de estado matricular o de estado con tesoreria
        $sql = "SELECT c.Matricula, p.Apellido, p.Nombres, td.Nombre AS Tipo_de_documento, p.NumeroDocumento AS Numero_de_documento, p.FechaNacimiento, cc.CorreoElectronico,
            (SELECT GROUP_CONCAT(DISTINCT e.Especialidad SEPARATOR ' - ') AS 'Especialidades' FROM especialidad e INNER JOIN colegiadoespecialista ce ON ce.Especialidad = e.Id WHERE ce.IdColegiado = c.Id) AS Especialidades,
            if (cs.Activo IS NULL, SUBSTR(cs.FechaCarga, 1, 10), if (cs.Activo = 0, if (SUBSTR(cs.FechaCarga, 1, 10) > cs.FechaActualizacion, SUBSTR(cs.FechaCarga, 1, 10), cs.FechaActualizacion), NULL)) AS Fecha_Actualizacion,
            if (cs.Activo IS NULL, 'ALTA', if (cs.Activo = 0, 'BAJA', 'ACTIVO')) AS EstadoSeguro
            FROM colegiado_seguro cs
            INNER JOIN colegiado c ON c.Matricula = cs.Matricula
            INNER JOIN persona p ON p.Id = c.IdPersona
            LEFT JOIN colegiadocontacto cc ON (cc.IdColegiado = c.Id AND cc.IdEstado = 1)
            LEFT JOIN tipodocumento td ON td.IdTipoDocumento = p.TipoDocumento
            #LEFT JOIN colegiado_agremiado_asegurado caa ON (caa.Matricula = c.Matricula AND caa.IdSeguroPraxisMedicaEnvios = ?)
            WHERE cs.IdSeguroPraxisMedicaEnvios = ? AND cs.Borrado = 0
            AND (cs.Activo IS NULL OR cs.Activo = 1)
            #AND caa.Id IS NULL
            ORDER BY c.Matricula";
        $stmt = $db->prepare($sql);
        //$stmt->execute([$idSeguro, $idSeguro]);
        $stmt->execute([$idSeguro]);
        $rows = $stmt->fetchAll();

        $resultado = array();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $row = array (
                        'matricula' => $r['Matricula'],
                        'apellido' => trim($r['Apellido']),
                        'nombre' => trim($r['Nombres']),
                        'tipoDocumento' => $r['Tipo_de_documento'],
                        'numeroDocumento' => $r['Numero_de_documento'],
                        'fechaNacimiento' => $r['FechaNacimiento'],
                        'correoElectronico' => $r['CorreoElectronico'],
                        'especialidades' => $r['Especialidades'],
                        'fechaActualizacion' => $r['Fecha_Actualizacion'],
                        'estadoSeguro' => $r['EstadoSeguro']
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
            $resultado['datos'] = NULL;
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

    public function obtenerSegurosProcesadosAltas($idSeguro) {
    try {
        $db = Database::getConnection();

        //primero se actualizan los datos de los ya registrados, si hay alguna actualizacion de estado matricular o de estado con tesoreria
        $sql = "SELECT c.Matricula, p.Apellido, p.Nombres, td.Nombre AS Tipo_de_documento, p.NumeroDocumento AS Numero_de_documento, p.FechaNacimiento, cc.CorreoElectronico,
            (SELECT GROUP_CONCAT(DISTINCT e.Especialidad SEPARATOR ' - ') AS 'Especialidades' FROM especialidad e INNER JOIN colegiadoespecialista ce ON ce.Especialidad = e.Id WHERE ce.IdColegiado = c.Id) AS Especialidades,
            if (cs.Activo IS NULL, SUBSTR(cs.FechaCarga, 1, 10), if (cs.Activo = 0, if (SUBSTR(cs.FechaCarga, 1, 10) > cs.FechaActualizacion, SUBSTR(cs.FechaCarga, 1, 10), cs.FechaActualizacion), NULL)) AS Fecha_Actualizacion,
            if (cs.Activo IS NULL, 'ALTA', if (cs.Activo = 0, 'BAJA', 'ACTIVO')) AS EstadoSeguro
            FROM colegiado_seguro cs
            LEFT JOIN colegiado_seguro b ON b.Matricula = cs.Matricula AND b.IdSeguroPraxisMedicaEnvios = (SELECT MAX(a.Id) FROM (SELECT cs.Id FROM seguro_praxis_medica_envios cs ORDER BY cs.Id DESC LIMIT 1, 2) AS a)
            INNER JOIN colegiado c ON c.Matricula = cs.Matricula
            INNER JOIN persona p ON p.Id = c.IdPersona
            LEFT JOIN colegiadocontacto cc ON (cc.IdColegiado = c.Id AND cc.IdEstado = 1)
            LEFT JOIN tipodocumento td ON td.IdTipoDocumento = p.TipoDocumento
            WHERE cs.IdSeguroPraxisMedicaEnvios = ?
            AND b.Id IS NULL
            ORDER BY c.Matricula";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idSeguro]);
        $rows = $stmt->fetchAll();

        $resultado = array();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $row = array (
                        'matricula' => $r['Matricula'],
                        'apellido' => trim($r['Apellido']),
                        'nombre' => trim($r['Nombres']),
                        'tipoDocumento' => $r['Tipo_de_documento'],
                        'numeroDocumento' => $r['Numero_de_documento'],
                        'fechaNacimiento' => $r['FechaNacimiento'],
                        'correoElectronico' => $r['CorreoElectronico'],
                        'especialidades' => $r['Especialidades'],
                        'fechaActualizacion' => $r['Fecha_Actualizacion'],
                        'estadoSeguro' => $r['EstadoSeguro']
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
            $resultado['datos'] = NULL;
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

    public function obtenerSegurosProcesadosBajas($idSeguro) {
    try {
        $db = Database::getConnection();

        //primero se actualizan los datos de los ya registrados, si hay alguna actualizacion de estado matricular o de estado con tesoreria
        $sql = "SELECT c.Matricula, p.Apellido, p.Nombres, td.Nombre AS Tipo_de_documento, p.NumeroDocumento AS Numero_de_documento, p.FechaNacimiento, cc.CorreoElectronico,
            (SELECT GROUP_CONCAT(DISTINCT e.Especialidad SEPARATOR ' - ') AS 'Especialidades' FROM especialidad e INNER JOIN colegiadoespecialista ce ON ce.Especialidad = e.Id WHERE ce.IdColegiado = c.Id) AS Especialidades,
            if (cs.Activo IS NULL, SUBSTR(cs.FechaCarga, 1, 10), if (cs.Activo = 0, if (SUBSTR(cs.FechaCarga, 1, 10) > cs.FechaActualizacion, SUBSTR(cs.FechaCarga, 1, 10), cs.FechaActualizacion), NULL)) AS Fecha_Actualizacion,
            if ((SELECT DISTINCT a1.Matricula FROM colegiado_agremiado_asegurado a1 WHERE a1.Matricula = cs.Matricula) IS NULL, 'BAJA', 'EN AMEPLA') AS EstadoSeguro
            FROM colegiado_seguro cs
            LEFT JOIN colegiado_seguro b ON b.Matricula = cs.Matricula AND b.IdSeguroPraxisMedicaEnvios = ?
            INNER JOIN colegiado c ON c.Matricula = cs.Matricula
            INNER JOIN persona p ON p.Id = c.IdPersona
            LEFT JOIN colegiadocontacto cc ON (cc.IdColegiado = c.Id AND cc.IdEstado = 1)
            LEFT JOIN tipodocumento td ON td.IdTipoDocumento = p.TipoDocumento
            WHERE cs.IdSeguroPraxisMedicaEnvios = (SELECT MAX(a.Id) FROM (SELECT cs.Id FROM seguro_praxis_medica_envios cs ORDER BY cs.Id DESC LIMIT 1, 2) AS a)
            AND b.Id IS NULL
            ORDER BY c.Matricula";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idSeguro]);
        $rows = $stmt->fetchAll();

        $resultado = array();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $row = array (
                        'matricula' => $r['Matricula'],
                        'apellido' => trim($r['Apellido']),
                        'nombre' => trim($r['Nombres']),
                        'tipoDocumento' => $r['Tipo_de_documento'],
                        'numeroDocumento' => $r['Numero_de_documento'],
                        'fechaNacimiento' => $r['FechaNacimiento'],
                        'correoElectronico' => $r['CorreoElectronico'],
                        'especialidades' => $r['Especialidades'],
                        'fechaActualizacion' => $r['Fecha_Actualizacion'],
                        'estadoSeguro' => $r['EstadoSeguro']
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
            $resultado['datos'] = NULL;
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

    public function guardarColegiadoAgremiadoAsegurado($idSeguroPraxisMedicaEnvios, $matricula, $serial_number) {
    try {
        $db = Database::getConnection();

        $sql = "INSERT INTO colegiado_agremiado_asegurado (IdSeguroPraxisMedicaEnvios, Matricula, SerialNumber)
                VALUES (?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idSeguroPraxisMedicaEnvios, $matricula, $serial_number]);

        $resultado['estado'] = true;
        $resultado['mensaje'] = "OK";
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR BORRANDO PROCESO: " . $e->getMessage();
    }

    return $resultado;
}

    public function obtenerCertificadoPorWS($matricula, $numeroDocumento, $path) {
    $continuar = true;
    if (isset($matricula) && isset($numeroDocumento)) {
        ini_set('xdebug.var_display_max_depth', -1);
        ini_set('xdebug.var_display_max_children', -1);
        ini_set('xdebug.var_display_max_data', -1);
        set_time_limit(0);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://vps-4498059-x.dattaweb.com/api/get-coberturas?dni='.$numeroDocumento.'&matricula='.$matricula);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        //curl_setopt($ch, CURLOPT_POSTFIELDS,$data_string);

        $headers = ['X-App-Key:' . '65fa2fcfbe50c606f3a6920e2fdece0398132876c8cdaad292ae3ee0ca4be54c'];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $respuesta = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);

        if ($err) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando certificado - cURL Error #:" . $err;
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        } else {
            switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                case 200:
                    $rta=(json_decode($respuesta,true));
                    if (isset($rta) && isset($rta['respuesta'])) {
                        $respuesta = $rta['respuesta'];
                        if (isset($respuesta['error'])) {
                            $resultado['estado'] = false;
                            $resultado['mensaje'] = "Error buscando certificado - " . $respuesta['error'];
                            $resultado['clase'] = 'alert alert-danger';
                            $resultado['icono'] = 'glyphicon glyphicon-remove';
                        } else {
                            $resultado['estado'] = true;
                            $resultado['mensaje'] = "OK - ".print_r($respuesta);
                            $resultado['archivo'] = NULL;
                            $resultado['clase'] = 'alert alert-success';
                            $resultado['icono'] = 'glyphicon glyphicon-ok';
                        }
                    } else {
                        $pdf_data = $respuesta;

                        // And a path where the file will be created
                        //$path = '../archivos/seguro/2024/';
                        $path = "../archivos/seguro/".PERIODO_ACTUAL.'/';
                        if (!file_exists($path)) {
                            mkdir($path, 0777, true);
                        }
                        $archivo = $matricula.'.pdf';

                        // Then just save it like this
                        file_put_contents( $path.$archivo, $pdf_data );

                        $resultado['estado'] = true;
                        $resultado['mensaje'] = "OK";
                        //guardar datos del archivo en colegiado_seguro
                        $pathArchivo = 'seguro/'.PERIODO_ACTUAL;
                        /*
                        echo 'pathArchivo->'.$pathArchivo.' - nombreArchivo->'.$archivo.' - matricula->'.$matricula.'<br>';
                        $db = Database::getConnection();
                        $sql = "UPDATE colegiado_seguro
                                SET PathArchivo = ?, NombreArchivo = ?
                                WHERE Id = (SELECT MAX(cs.Id) FROM colegiado_seguro cs WHERE cs.Matricula = ? AND cs.Activo = 1 AND cs.Borrado = 0)";
                        $stmt = $db->prepare($sql);
                        $stmt->execute([$pathArchivo, $archivo, $matricula]);

                        if ($stmt->rowCount() == 0) {
                            $resultado['estado'] = FALSE;
                            $resultado['mensaje'] = "ERROR GUARDANDO colegiado_seguro";
                        }
                        */
                        $resultado['archivo'] = $archivo;
                        $resultado['clase'] = 'alert alert-success';
                        $resultado['icono'] = 'glyphicon glyphicon-ok';
                    }
                    break;

            case 400:
                $resultado['estado'] = false;
                $resultado['mensaje'] = "Error buscando certificado - " . $respuesta['error'];
                $resultado['clase'] = 'alert alert-danger';
                $resultado['icono'] = 'glyphicon glyphicon-remove';
                //$rta=(json_decode($respuesta,true));
                //var_dump($rta);
                //$continuar = FALSE;
                break;

            case 404:
                $rta=(json_decode($respuesta,true));
                $resultado['estado'] = false;
                $resultado['mensaje'] = $rta['error'];
                $resultado['clase'] = 'alert alert-danger';
                $resultado['icono'] = 'glyphicon glyphicon-remove';
                //$rta=(json_decode($respuesta,true));
                //var_dump($rta);
                //$continuar = FALSE;
                break;

            default:
                //echo 'Codigo HTTP inesperado: '.$http_code."<br>";
                //$continuar = FALSE;
                $resultado['estado'] = false;
                $resultado['mensaje'] = "Error buscando certificado - Codigo HTTP inesperado: ".$http_code;
                $resultado['clase'] = 'alert alert-danger';
                $resultado['icono'] = 'glyphicon glyphicon-remove';
                break;
            }

        }
    } else {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR EN LOS PARAMETROS";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function solicitarAmpliacionCoberturaPorWS($dni, $matricula, $nombre, $apellido, $telefono, $email) {
    $continuar = true;
    if (isset($dni) && isset($matricula) && isset($nombre) && isset($apellido) && isset($telefono)  && isset($email)) {
        if (ENV == 'prod') {
            $data = array(
                        'dni' => $dni,
                        'matricula' => $matricula,
                        'nombre' => $nombre,
                        'apellido' => $apellido,
                        'telefono' => $telefono,
                        'email' => $email
                    );
        } else {
            $data = array(
                        'dni' => 1111111,
                        'matricula' => 11111,
                        'nombre' => "claudio",
                        'apellido' => "cabrera",
                        'telefono' => "2494582711",
                        'email' => "claudiocabrera12@gmail.com"
                    );
        }
        //$data_json = json_encode(array("user" => $data));
        ini_set('xdebug.var_display_max_depth', -1);
        ini_set('xdebug.var_display_max_children', -1);
        ini_set('xdebug.var_display_max_data', -1);
        set_time_limit(0);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://vps-4498059-x.dattaweb.com/api/aumentar-suma');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data);

        $headers = ['X-App-Key:' . '65fa2fcfbe50c606f3a6920e2fdece0398132876c8cdaad292ae3ee0ca4be54c'];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $respuesta = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);

        if ($err) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error enviado solicitud de aumento de suma - cURL Error #:" . $err;
        } else {
            switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                case 200:
                    $rta=(json_decode($respuesta,true));
                    $resultado['estado'] = true;
                    $resultado['datos'] = $rta;
                    break;

                case 400:
                    $resultado['estado'] = false;
                    $resultado['mensaje'] = "Error enviado solicitud de aumento de suma - " . $respuesta['error'];
                    break;

                default:
                    $resultado['estado'] = false;
                    $resultado['mensaje'] = "Error buscando certificado - Codigo HTTP inesperado: ".$http_code;
                    break;
            }

        }
    } else {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR EN LOS PARAMETROS";
    }

    return $resultado;
}

    public function noHayLiquidacionEnElMes($fechaActual) {
    try {
        $db = Database::getConnection();

        $mesVerificar = substr($fechaActual, 0, 7);
        $sql = "SELECT s.Id
            FROM seguro_praxis_medica_envios s
            WHERE SUBSTR(s.FechaLimiteProceso, 1, 7) = ? AND s.Borrado = 0";
        $stmt = $db->prepare($sql);
        $stmt->execute([$mesVerificar]);
        $row = $stmt->fetch();

        $resultado = TRUE;
        if ($row) {
            $idSeguro = $row['Id'];
            if (isset($idSeguro) && $idSeguro <> "") {
                $resultado = FALSE;
            }
        }
    } catch (PDOException $e) {
        $resultado = TRUE;
    }
    return $resultado;
}
}
