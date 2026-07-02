<?php
define('CONCEPTO_PERIODO_ACTUAL', 4);
define('CONCEPTO_PERIODOS_ANTERIORES', 6);
define('CONCEPTO_PLAN_PAGO', 5);
define('CONCEPTO_DEVOLUCION', 12);

class tesoreriaLogic {

    public function totalPorAgremiaciones($periodoProcesar, $mesProcesar, $tipoDetalle) {
    if (count($mesProcesar) == 1) {
	    if ($mesProcesar[0] == '0') {
	    	//procesa todos los meses
	    	$fechaDesde = $periodoProcesar.'-05-01';
	    	$fechaHasta = ($periodoProcesar + 1).'-04-30';
	    	$periodoDesde = $periodoProcesar.'-05';
	    	$periodoHasta = ($periodoProcesar + 1).'-04';
	    	$agrupamiento = "";
	    	$filtro = "WHERE SUBSTR(cobranza.FechaApertura, 1, 7) BETWEEN '".$periodoDesde."' AND '".$periodoHasta."'";
	    } else {
	    	if ($mesProcesar[0] < '05') {
	    		$periodoDesde = ($periodoProcesar + 1)."-".$mesProcesar[0];
	    	} else {
	    		$periodoDesde = $periodoProcesar."-".$mesProcesar[0];
	    	}
			$filtro = "WHERE SUBSTR(cobranza.FechaApertura, 1, 7) = '".$periodoDesde."'";
	    	$agrupamiento = "";
	    }
    } else {
    	$filtro = "WHERE (";
    	$i = 0;
    	foreach ($mesProcesar as $mes) {
	    	if ($mes < '05') {
	    		$periodoDesde = ($periodoProcesar + 1).'-'.$mes;
	    	} else {
	    		$periodoDesde = $periodoProcesar.'-'.$mes;
	    	}
			if ($i > 0) {
				$filtro .= " OR ";
			}
			$filtro .= "SUBSTR(cobranza.FechaApertura, 1, 7) = '".$periodoDesde."'";
			$i++;
    	}
    	$filtro .= ")";
    	$agrupamiento = "";
    }

	$sql = "SELECT ".$agrupamiento." cobranzadetalle.Periodo, SUM(cobranzadetalle.Importe)
		FROM cobranzadetalle
		INNER JOIN cobranza ON (cobranza.Id = cobranzadetalle.IdLoteCobranza)
		".$filtro."
		AND cobranza.NumeroLoteManual > 0
		GROUP BY ".$agrupamiento." cobranzadetalle.Periodo
		ORDER BY ".$agrupamiento." cobranzadetalle.Periodo DESC";

    $resultado = array();
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $datos = $stmt->fetchAll(PDO::FETCH_NUM);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['datos'] = array();
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
        foreach ($datos as $row) {
            if ($tipoDetalle == 'MENSUAL') {
                $resultado['datos'][] = array(
                    'mesPago' => $row[0],
                    'periodo' => $row[1],
                    'importe' => $row[2]
                );
            } else {
                $resultado['datos'][] = array(
                    'periodo' => $row[0],
                    'importe' => $row[1]
                );
            }
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando cobranza";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }

    return $resultado;
}

    public function totalPorMediosPago($periodoProcesar, $mesProcesar, $tipoDetalle) {
    if (count($mesProcesar) == 1) {
	    if ($mesProcesar[0] == '0') {
	    	//procesa todos los meses
	    	$fechaDesde = $periodoProcesar.'-05-01';
	    	$fechaHasta = ($periodoProcesar + 1).'-04-30';
	    	$periodoDesde = $periodoProcesar.'-05';
	    	$periodoHasta = ($periodoProcesar + 1).'-04';
	    	$agrupamiento = "";
	    	$filtro = "WHERE SUBSTR(cobranza.FechaApertura, 1, 7) BETWEEN '".$periodoDesde."' AND '".$periodoHasta."'";
	    } else {
	    	if ($mesProcesar[0] < '05') {
	    		$periodoDesde = ($periodoProcesar + 1)."-".$mesProcesar[0];
	    	} else {
	    		$periodoDesde = $periodoProcesar."-".$mesProcesar[0];
	    	}
			$filtro = "WHERE SUBSTR(cobranza.FechaApertura, 1, 7) = '".$periodoDesde."'";
	    	$agrupamiento = "";
	    }
    } else {
    	$filtro = "WHERE (";
    	$i = 0;
    	foreach ($mesProcesar as $mes) {
	    	if ($mes < '05') {
	    		$periodoDesde = ($periodoProcesar + 1).'-'.$mes;
	    	} else {
	    		$periodoDesde = $periodoProcesar.'-'.$mes;
	    	}
			if ($i > 0) {
				$filtro .= " OR ";
			}
			$filtro .= "SUBSTR(cobranza.FechaApertura, 1, 7) = '".$periodoDesde."'";
			$i++;
    	}
    	$filtro .= ")";
    	$agrupamiento = "";
    }

	$sql = "SELECT ".$agrupamiento." cobranzadetalle.Periodo, SUM(cobranzadetalle.Importe)
		FROM cobranzadetalle
		INNER JOIN cobranza ON (cobranza.Id = cobranzadetalle.IdLoteCobranza)
		".$filtro."
		AND cobranza.NumeroLoteManual IS NULL
		GROUP BY ".$agrupamiento." cobranzadetalle.Periodo
		ORDER BY ".$agrupamiento." cobranzadetalle.Periodo DESC";

    $resultado = array();
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $datos = $stmt->fetchAll(PDO::FETCH_NUM);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['datos'] = array();
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
        foreach ($datos as $rawRow) {
            $periodo = $rawRow[0];
            $importe = $rawRow[1];
            if ($tipoDetalle == 'MENSUAL') {
                $mesPago = $rawRow[0];
                $periodo = $rawRow[1];
                $importe = $rawRow[2];
            }
        	if ($periodo > 3000) {
        		$periodoDetalle = "Plan de Pagos Nro. ".$periodo;
        		$periodo = NULL;
        	} else {
        		if (isset($periodo) && $periodo > 0) {
        			$periodoDetalle = "Período abonado ".$periodo;
        		} else {
        			$periodoDetalle = "Cursos";
	        		$periodo = NULL;
        		}
        	}
	    	if ($tipoDetalle == 'MENSUAL') {
	            $resultado['datos'][] = array(
	            	'mesPago' => $mesPago,
	                'periodo' => $periodo,
	                'periodoDetalle' => $periodoDetalle,
	                'importe' => $importe
	            );
	        } else {
	        	$resultado['datos'][] = array(
	                'periodo' => $periodo,
	                'periodoDetalle' => $periodoDetalle,
	                'importe' => $importe
	            );
	        }
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando cobranza";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }

    return $resultado;
}

    public function totalPorCaja($periodoProcesar, $mesProcesar, $tipoDetalle) {
    if (count($mesProcesar) == 1) {
	    if ($mesProcesar[0] == '0') {
	    	//procesa todos los meses
	    	$fechaDesde = $periodoProcesar.'-05-01';
	    	$fechaHasta = ($periodoProcesar + 1).'-04-30';
	    	$periodoDesde = $periodoProcesar.'-05';
	    	$periodoHasta = ($periodoProcesar + 1).'-04';
	    	$agrupamiento = "";
	    	$filtro = "WHERE SUBSTR(cajadiaria.FechaApertura, 1, 7) BETWEEN '".$periodoDesde."' AND '".$periodoHasta."'";
	    } else {
	    	if ($mesProcesar[0] < '05') {
	    		$periodoDesde = ($periodoProcesar + 1)."-".$mesProcesar[0];
	    	} else {
	    		$periodoDesde = $periodoProcesar."-".$mesProcesar[0];
	    	}
			$filtro = "WHERE SUBSTR(cajadiaria.FechaApertura, 1, 7) = '".$periodoDesde."'";
	    	$agrupamiento = "";
	    }
    } else {
    	$filtro = "WHERE (";
    	$i = 0;
    	foreach ($mesProcesar as $mes) {
	    	if ($mes < '05') {
	    		$periodoDesde = ($periodoProcesar + 1).'-'.$mes;
	    	} else {
	    		$periodoDesde = $periodoProcesar.'-'.$mes;
	    	}
			if ($i > 0) {
				$filtro .= " OR ";
			}
			$filtro .= "SUBSTR(cajadiaria.FechaApertura, 1, 7) = '".$periodoDesde."'";
			$i++;
    	}
    	$filtro .= ")";
    	$agrupamiento = "";
    }

	$sql = "SELECT ".$agrupamiento." concepto.Nombre, SUM(cajadiariamovimientodetalle.Monto), concepto.Id
		FROM cajadiariamovimientodetalle
		INNER JOIN cajadiariamovimiento ON(cajadiariamovimiento.Id = cajadiariamovimientodetalle.IdCajaDiariaMovimiento)
		INNER JOIN cajadiaria ON(cajadiaria.Id = cajadiariamovimiento.IdCajaDiaria)
		INNER JOIN tipopago ON(tipopago.Id = cajadiariamovimientodetalle.CodigoPago)
		INNER JOIN concepto ON(concepto.Id = tipopago.IdConcepto)
		".$filtro."
		GROUP BY ".$agrupamiento." concepto.Nombre
		ORDER BY ".$agrupamiento." concepto.Id";

    $resultado = array();
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $datos = $stmt->fetchAll(PDO::FETCH_NUM);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['datos'] = array();
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
        foreach ($datos as $row) {
	    	if ($tipoDetalle == 'MENSUAL') {
	            $resultado['datos'][] = array(
	            	'mesPago' => $row[0],
	                'concepto' => $row[1],
	                'idConcepto' => $row[3],
	                'importe' => $row[2]
	            );
	        } else {
	        	$resultado['datos'][] = array(
	                'concepto' => $row[0],
	                'idConcepto' => $row[2],
	                'importe' => $row[1]
	            );
	        }
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando cobranza";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }

    return $resultado;
}

    public function totalDetalleColegiacion($periodoProcesar, $mesProcesar, $tipoDetalle){
    if (count($mesProcesar) == 1) {
	    if ($mesProcesar[0] == '0') {
	    	//procesa todos los meses
	    	$fechaDesde = $periodoProcesar.'-05-01';
	    	$fechaHasta = ($periodoProcesar + 1).'-04-30';
	    	$periodoDesde = $periodoProcesar.'-05';
	    	$periodoHasta = ($periodoProcesar + 1).'-04';
	    	$agrupamiento = "";
	    	$filtro = "WHERE SUBSTR(cajadiariamovimiento.Fecha, 1, 7) BETWEEN '".$periodoDesde."' AND '".$periodoHasta."'";
	    } else {
	    	if ($mesProcesar[0] < '05') {
	    		$periodoDesde = ($periodoProcesar + 1)."-".$mesProcesar[0];
	    	} else {
	    		$periodoDesde = $periodoProcesar."-".$mesProcesar[0];
	    	}
			$filtro = "WHERE SUBSTR(cajadiariamovimiento.Fecha, 1, 7) = '".$periodoDesde."'";
	    	$agrupamiento = "";
	    }
    } else {
    	$filtro = "WHERE (";
    	$i = 0;
    	foreach ($mesProcesar as $mes) {
	    	if ($mes < '05') {
	    		$periodoDesde = ($periodoProcesar + 1).'-'.$mes;
	    	} else {
	    		$periodoDesde = $periodoProcesar.'-'.$mes;
	    	}
			if ($i > 0) {
				$filtro .= " OR ";
			}
			$filtro .= "SUBSTR(cajadiariamovimiento.Fecha, 1, 7) = '".$periodoDesde."'";
			$i++;
    	}
    	$filtro .= ")";
    	$agrupamiento = "";
    }

	$sql = "SELECT ".$agrupamiento." cajadiariamovimientodetalle.Periodo, sum(cajadiariamovimientodetalle.Monto)
		FROM cajadiariamovimientodetalle
		INNER JOIN cajadiariamovimiento ON(cajadiariamovimiento.Id = cajadiariamovimientodetalle.IdCajaDiariaMovimiento)
		".$filtro."
		AND cajadiariamovimientodetalle.CodigoPago = 3
		GROUP BY ".$agrupamiento." cajadiariamovimientodetalle.Periodo";

    $resultado = array();
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $datos = $stmt->fetchAll(PDO::FETCH_NUM);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['datos'] = array();
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
        foreach ($datos as $row) {
	    	if ($tipoDetalle == 'MENSUAL') {
	            $resultado['datos'][] = array(
	            	'mesPago' => $row[0],
	                'periodo' => $row[1],
	                'importe' => $row[2]
	            );
	        } else {
	        	$resultado['datos'][] = array(
	                'periodo' => $row[0],
	                'importe' => $row[1]
	            );
	        }
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando cobranza";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }

    return $resultado;

}
}
