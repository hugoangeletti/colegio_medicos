<?php
class colegiadoDeudaAnualLogic {

    public function obtenerColegiadoDeudaAnualPorPeriodo($periodo) {
    $sql = "SELECT cda.Id, cda.Importe, cda.Cuotas, cda.Antiguedad
        FROM colegiadodeudaanual cda
        WHERE cda.Periodo = ? AND cda.Estado <> 'B'
        ORDER BY cda.Antiguedad, cda.Id";

    $resultado = array();
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$periodo]);
        $rows = $stmt->fetchAll();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $row = array (
                    'idColegiadoDeudaAnual' => $r['Id'],
                    'importe' => $r['Importe'],
                    'cuotas' => $r['Cuotas'],
                    'antiguedad' => $r['Antiguedad']
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
            $resultado['mensaje'] = "No hay colegiacion anual";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando cuotas de colegiacion";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerDeudaPeriodosAnterioresPorIdColegiado($idColegiado) {
    $sql = "SELECT cda.Id, cda.Periodo, cdac.Cuota, cdac.Importe, cdac.FechaVencimiento, cdac.Recargo, cdac.SegundoVencimiento, cdac.Id
        FROM colegiadodeudaanual cda
        INNER JOIN colegiadodeudaanualcuotas cdac ON cdac.IdColegiadoDeudaAnual = cda.Id
        WHERE cda.IdColegiado = ?
        AND cda.Periodo < ?
        AND cda.Estado = 'A'
        AND cdac.Estado = 1
        ORDER BY cda.Periodo, cdac.Cuota";

    $resultado = array();
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado, $_SESSION['periodoActual']]);
        $rows = $stmt->fetchAll();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $fechaVencimiento = $r['FechaVencimiento'];
                $importe = $r['Importe'];
                $importeSegundoVto = $r['Recargo'];
                $fechaSegundoVencimiento = $r['SegundoVencimiento'];
                //calcula recargo a la fecha actual
                $vencimientoActual = ultmioDiaDelMes(date('Y-m-d'));
                $recargo = $this->obtenerRecargoCuota($fechaVencimiento, $vencimientoActual, $importe);
                $row = array (
                    'idColegiadoDeudaAnual' => $r['Id'],
                    'periodo' => $r['Periodo'],
                    'cuota' => $r['Cuota'],
                    'importe' => $importe,
                    'fechaVencimiento' => $fechaVencimiento,
                    'importeSegundoVto' => $importeSegundoVto,
                    'fechaSegundoVencimiento' => $fechaSegundoVencimiento,
                    'recargo' => $recargo,
                    'vencimientoActual' => $vencimientoActual,
                    'idDeuda' => $r['Id']
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
            $resultado['mensaje'] = "No hay colegiacion anual";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando cuotas de colegiacion";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerColegiadosEmisionAnualTotal($periodoActual){
    $sql = "SELECT DISTINCT cda.IdColegiado
        FROM colegiadodeudaanual cda
        INNER JOIN colegiado c ON c.Id = cda.IdColegiado
        LEFT JOIN agremiacionesdebito ad ON ad.IdColegiado = c.Id
        LEFT JOIN debitocbu d1 ON d1.IdColegiado = c.Id AND d1.Estado = 'A'
        LEFT JOIN debitotarjeta d2 ON d2.IdColegiado = c.Id AND d2.Estado = 'A'
        WHERE cda.Periodo = ?
        AND (SELECT COUNT(cdac.Id) FROM colegiadodeudaanualcuotas cdac WHERE cdac.IdColegiadoDeudaAnual = cda.Id AND cdac.Estado = 1) > 0
            AND ad.Id IS NULL AND d1.Id IS NULL AND d2.id IS NULL
        AND cda.EmisionTotal = 'N'
          ORDER BY c.Matricula";

    $resultado = array();
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$periodoActual]);
        $rows = $stmt->fetchAll();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $row = array (
                    'idColegiado' => $r['IdColegiado']
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
            $resultado['mensaje'] = "No hay colegiacion anual";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando cuotas de colegiacion";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerColegiadoDeudaAnualPorIdColegiado($idColegiado, $matricula, $periodoDesde, $periodoHasta){
    $sql = "(SELECT Id, Periodo, Importe, Cuotas, Estado
        FROM colegiadodeudaanual
        WHERE IdColegiado = ?
        AND Periodo >= ? AND Periodo <= ?) ";

    $params = [$idColegiado, $periodoDesde, $periodoHasta];

    if ($matricula <> 0) {
        $sql .= "UNION ALL

        (SELECT 'Id', colegiadodeudahistorico.Periodo, SUM(colegiadodeudahistorico.Importe) AS Importe, 0, 'C'
        FROM colegiadodeudahistorico
        WHERE colegiadodeudahistorico.Matricula = ?
        AND colegiadodeudahistorico.FechaPago IS NOT NULL AND colegiadodeudahistorico.FechaPago <> '0000-00-00'
        GROUP BY colegiadodeudahistorico.Periodo) ";
        $params[] = $matricula;
    }

    $sql .= "ORDER BY Periodo DESC";

    $resultado = array();
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $row = array (
                    'id' => $r['Id'],
                    'periodo' => $r['Periodo'],
                    'importe' => $r['Importe'],
                    'cuotas' => $r['Cuotas'],
                    'estado' => $r['Estado']
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
            $resultado['mensaje'] = "No hay colegiacion anual";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando cuotas de colegiacion";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerColegiadoDeudaAnualPorIdColegiadoEstado($idColegiado, $estado){
    $sql = "SELECT Id, Periodo, Importe, Cuotas, Estado
        FROM colegiadodeudaanual
        WHERE IdColegiado = ?
        AND Estado = ?";

    $resultado = array();
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado, $estado]);
        $rows = $stmt->fetchAll();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $row = array (
                    'id' => $r['Id'],
                    'periodo' => $r['Periodo'],
                    'importe' => $r['Importe'],
                    'cuotas' => $r['Cuotas'],
                    'estado' => $r['Estado']
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
            $resultado['mensaje'] = "No hay colegiacion anual";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando cuotas de colegiacion";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerDeudaAnualCuotas($idColegiadoDeudaAnual, $fechaVencimiento = NULL){
    if (isset($fechaVencimiento) && $fechaVencimiento <> "") {
        $conVencimiento = " AND cdac.FechaVencimiento <= '".$fechaVencimiento."'";
    } else {
        $conVencimiento = "";
    }

    $sql = "SELECT cdac.Id, cda.Periodo, cdac.Cuota, cdac.Importe, cdac.FechaVencimiento, cdac.Recargo, cdac.SegundoVencimiento, cdac.Estado, planpagos.Estado AS EstadPP, cdac.IdPlanPago, cdac.FechaPago, sc.FechaSolicitud
            FROM colegiadodeudaanualcuotas cdac
            INNER JOIN colegiadodeudaanual cda ON(cda.Id = cdac.IdColegiadoDeudaAnual)
            LEFT JOIN planpagos ON(planpagos.Id = cdac.IdPlanPago)
            LEFT JOIN solicitudcondonaciondetalle scd ON scd.IdColegiadoDeudaCondonada = cdac.Id
            LEFT JOIN solicitudcondonacion sc ON sc.Id = scd.IdSolicitudCondonacion
            WHERE cda.Id = ? ".$conVencimiento."
            AND cdac.Estado <= 4";

    $resultado = array();
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiadoDeudaAnual]);
        $rows = $stmt->fetchAll();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $vencimientoDos = $r['SegundoVencimiento'];
                $importeDos = $r['Recargo'];
                //verifico el vencimiento, sino le calculo el recargo
                if ($vencimientoDos < date('Y-m-d')){
                    $importeActualizado = $this->obtenerRecargoCuota($vencimientoDos, date('Y-m-d'), $importeDos);
                } else {
                    $importeActualizado = $importeDos;
                }

                $row = array (
                    'idColegiadoDeudaAnualCuota' => $r['Id'],
                    'periodo' => $r['Periodo'],
                    'cuota' => $r['Cuota'],
                    'importe' => $r['Importe'],
                    'importeActualizado' => $importeActualizado,
                    'vencimiento' => $vencimientoDos,
                    'estado' => $r['Estado'],
                    'estadoPP' => $r['EstadPP'],
                    'idPlanPago' => $r['IdPlanPago'],
                    'fechaPago' => $r['FechaPago'],
                    'fechaCondonacion' => $r['FechaSolicitud']
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
            $resultado['mensaje'] = "No hay cuotas de colegiacion pendiente de pago";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando cuotas de colegiacion";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerColegiadoDeudaAnualAPagar($idColegiado, $fechaVencimiento = NULL){
    if (isset($fechaVencimiento) && $fechaVencimiento <> "") {
        $conVencimiento = " AND colegiadodeudaanualcuotas.FechaVencimiento <= '".$fechaVencimiento."'";
    } else {
        $conVencimiento = "";
    }

    $sql = "SELECT colegiadodeudaanualcuotas.Id, colegiadodeudaanual.Periodo, colegiadodeudaanualcuotas.Cuota, colegiadodeudaanualcuotas.Importe,
            colegiadodeudaanualcuotas.FechaVencimiento, colegiadodeudaanualcuotas.Recargo, colegiadodeudaanualcuotas.SegundoVencimiento, pagosnoregistrados.Id
            FROM colegiadodeudaanualcuotas
            INNER JOIN colegiadodeudaanual ON(colegiadodeudaanual.Id = colegiadodeudaanualcuotas.IdColegiadoDeudaAnual)
            LEFT JOIN pagosnoregistrados ON(pagosnoregistrados.Recibo = colegiadodeudaanualcuotas.Id AND pagosnoregistrados.TipoPago='C')
            WHERE colegiadodeudaanual.IdColegiado = ? ".$conVencimiento."
            AND colegiadodeudaanualcuotas.Estado = 1
            AND pagosnoregistrados.Id IS NULL
            ORDER BY colegiadodeudaanual.Periodo, colegiadodeudaanualcuotas.Cuota";

    $resultado = array();
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $rows = $stmt->fetchAll();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $importeUno = $r['Importe'];
                $importeDos = $r['Recargo'];
                $vencimientoDos = $r['SegundoVencimiento'];
                //verifico el vencimiento, sino le calculo el recargo
                if (!isset($importeDos) || $importeDos == 0) {
                    $importeDos = $importeUno;
                }
                if ($vencimientoDos < date('Y-m-d')){
                    $importeActualizado = $this->obtenerRecargoCuota($vencimientoDos, date('Y-m-d'), $importeDos);
                } else {
                    $importeActualizado = $importeDos;
                }
                $row = array (
                    'idColegiadoDeudaAnualCuota' => $r['Id'],
                    'periodo' => $r['Periodo'],
                    'cuota' => $r['Cuota'],
                    'importeUno' => $importeUno,
                    'importeActualizado' => $importeActualizado,
                    'vencimiento' => $vencimientoDos,
                    'idPagoNoRegistrado' => $r['Id'],
                    'fechaVencimiento' => $vencimientoDos
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
            $resultado['mensaje'] = "No hay cuotas de colegiacion pendiente de pago";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando cuotas de colegiacion";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerIdColegiadoConDeuda($periodo, $fechaVencimiento, $cantidadCuotas){
    $sql = "SELECT colegiado.Id, count(colegiadodeudaanualcuotas.Id) as Cantidad, colegiadocontacto.CorreoElectronico
            FROM colegiado
            INNER JOIN colegiadodeudaanual
                ON(colegiado.Id = colegiadodeudaanual.IdColegiado
                AND colegiadodeudaanual.Estado='A'
                AND colegiadodeudaanual.Periodo <= ?)
            INNER JOIN colegiadodeudaanualcuotas
                ON(colegiadodeudaanualcuotas.IdColegiadoDeudaAnual = colegiadodeudaanual.Id
                AND colegiadodeudaanualcuotas.SegundoVencimiento <= ?
                AND colegiadodeudaanualcuotas.Estado = 1)
            LEFT JOIN colegiadocontacto
                ON(colegiadocontacto.IdColegiado = colegiado.Id
                AND colegiadocontacto.IdEstado = 1)
            LEFT JOIN agremiacionesdebito
                ON(agremiacionesdebito.IdColegiado = colegiado.Id AND agremiacionesdebito.Borrado = 0)
            WHERE colegiado.Estado IN(0, 1, 5, 8, 10)
            AND agremiacionesdebito.Id is null
            GROUP BY colegiado.Id, colegiado.Matricula
            HAVING Cantidad > ?";

    $resultado = array();
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$periodo, $fechaVencimiento, $cantidadCuotas]);
        $rows = $stmt->fetchAll();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $row = array (
                    'idColegiado' => $r['Id'],
                    'cantidad' => $r['Cantidad'],
                    'mail' => $r['CorreoElectronico']
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
            $resultado['mensaje'] = "No hay cuotas de colegiacion pendiente de pago";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando cuotas de colegiacion";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerColegiadoDeudaAnual($periodoActual){
    $sql = "SELECT colegiadodeudaanual.IdColegiado, colegiadodeudaanual.Id, colegiado.Matricula,
        persona.Apellido, persona.Nombres
        FROM colegiadodeudaanual
        INNER JOIN colegiado ON(colegiado.Id = colegiadodeudaanual.IdColegiado)
		  INNER JOIN persona ON(persona.Id = colegiado.IdPersona)
        WHERE colegiadodeudaanual.Periodo = ?
        ORDER BY colegiado.Matricula";

    $resultado = array();
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$periodoActual]);
        $rows = $stmt->fetchAll();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $row = array (
                    'idColegiado' => $r['IdColegiado'],
                    'idColegiadoDeudaAnual' => $r['Id'],
                    'matricula' => $r['Matricula'],
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
            $resultado['mensaje'] = "No hay colegiacion anual";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando cuotas de colegiacion";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerColegiadoParaEmisionChequera($periodo, $emitirPor, $idZona, $codigoPostal, $idAgremiacion, $calleDesde, $calleHasta) {
    $resultado = array();
    $continuar = TRUE;
    if (isset($emitirPor)) {
        if (isset($idZona)) {
            $filtroEmitirPor = "l.idZona = ".$idZona;
            if ($idZona == 4) {
                if (isset($calleDesde) && isset($calleHasta) && $calleDesde <= $calleHasta) {
                    $filtroEmitirPor .= " AND cdr.CodigoPostal = '".$codigoPostal."' AND cdr.Calle >= '".trim($calleDesde)."' AND cdr.Calle <= '".trim($calleHasta)."' AND ad.Id IS NULL AND dt.id IS NULL AND dc.id IS NULL";
                } else {
                    $continuar = FALSE;
                }
            }
            $filtroEmitirPor .= " AND (cc.CorreoElectronico IS NULL OR cc.CorreoElectronico = '' OR cc.CorreoElectronico = 'NR')";
        } else {
            if (isset($idAgremiacion)) {
                $filtroEmitirPor = "ad.IdLugarPago = ".$idAgremiacion. " AND ad.Periodo = ".$periodo;
            } else {
                $continuar = FALSE;
            }
        }
    } else {
        $continuar = FALSE;
    }
    if ($continuar) {
        $sql = "SELECT c.Id
            FROM colegiadodeudaanual cda
            INNER JOIN colegiado c ON (c.Id = cda.IdColegiado)
            LEFT JOIN colegiadodomicilioreal cdr ON (cdr.IdColegiado = c.Id and cdr.IdEstado = 1)
            LEFT JOIN colegiadocontacto cc ON (cc.IdColegiado = c.Id and cc.IdEstado = 1)
            LEFT JOIN localidad l ON (l.Id = cdr.IdLocalidad)
            LEFT JOIN agremiacionesdebito ad ON (ad.IdColegiado = c.Id AND ad.Periodo = ? AND ad.Borrado = 0)
            LEFT JOIN debitotarjeta dt ON (dt.IdColegiado = c.Id and dt.Estado = 'A')
            LEFT JOIN debitocbu dc ON (dc.IdColegiado = c.Id and dc.Estado = 'A')
            WHERE cda.Periodo = ? AND cda.Estado = 'A' AND c.Estado IN(1, 5, 10) AND ".$filtroEmitirPor." ORDER BY cdr.CodigoPostal, l.Nombre, cdr.Calle, cdr.Numero";

        try {
            $db = Database::getConnection();
            $stmt = $db->prepare($sql);
            $stmt->execute([$periodo, $periodo]);
            $rows = $stmt->fetchAll();
            if (count($rows) > 0) {
                $datos = array();
                foreach ($rows as $r) {
                    $row = array($r['Id']);
                    array_push($datos, $row);
                }
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "No hay colegiacion anual";
                $resultado['clase'] = 'alert alert-info';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando cuotas de colegiacion";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
    } else {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error en los datos ingresados";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

//recargo cuota
    public function obtenerRecargoCuota($vencimiento, $vencimientoActual, $importeRecargo) {
    if ($vencimiento < $vencimientoActual) {
        if (isset($_SESSION['indiceRecargo']) && $_SESSION['indiceRecargo'] > 0) {
            $recargo = $_SESSION['indiceRecargo'];
        } else {
            $resRecargo = $this->obtenerIndiceRecargo(date('Y-m-d'), 20);
            if ($resRecargo['estado']) {
                $recargo = $resRecargo['indiceRecargo'];
            } else {
                $recargo = 1.5;
            }
            $_SESSION['indiceRecargo'] = $recargo;
        }

        $meses = (substr($vencimientoActual, 0, 4) - substr($vencimiento, 0, 4)) * 12;
        $meses +=(substr($vencimientoActual, 5, 2) - substr($vencimiento, 5, 2));

        if ($meses < 0) {
            $meses = 0;
        }
        $recargo *= $meses;
        $recargoCuota = round($importeRecargo * $recargo / 100);
        $recargoCuota += $importeRecargo;
    } else {
        $recargoCuota = $importeRecargo;
    }
    return($recargoCuota);
}

    public function obtenerIndiceRecargo($fecha, $codigoPago){
    $sql = "SELECT Valor FROM tablavalores WHERE IdValor = ? AND Fecha <= ? ORDER BY Fecha DESC";

    $resultado = array();
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$codigoPago, $fecha]);
        $r = $stmt->fetch();
        if ($r) {
            $resultado['estado'] = TRUE;
            $resultado['indiceRecargo'] = $r['Valor'];
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "No hay indice de recargo";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando indice de recargo";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

//obtiene el detalle del estado en tesoreria
    public function estadoTesoreria($codigo){
    $sql = "SELECT Nombre FROM estadotesoreria WHERE Codigo = ?";

    $resultado = array();
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$codigo]);
        $resultado['estado'] = TRUE;
        $r = $stmt->fetch();
        if ($r) {
            $resultado['estadoTesoreria'] = $r['Nombre'];
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No hay estado";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando estado";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}


//obtiene si es deudor
    public function estadoTesoreriaPorColegiado($idColegiado, $periodoActual){
    $cantidadPeriodoActual = 0;
    $cantidadPeriodosAnteriores = 0;
    $cantidadPlanPagos = 0;

    $codigoDeudor = 0;
    $resultado = array();
    $resultado['estado'] = TRUE;

    try {
        $db = Database::getConnection();
        $sql = "SELECT COUNT(colegiadodeudaanualcuotas.id) as cantidad
                FROM colegiadodeudaanualcuotas
                INNER JOIN colegiadodeudaanual ON(colegiadodeudaanual.Id = colegiadodeudaanualcuotas.IdColegiadoDeudaAnual)
                LEFT JOIN agremiacionesdebito ON(agremiacionesdebito.IdColegiado = colegiadodeudaanual.IdColegiado AND agremiacionesdebito.Borrado = 0)
                LEFT JOIN pagosnoregistrados ON(pagosnoregistrados.Recibo = colegiadodeudaanualcuotas.Id AND pagosnoregistrados.TipoPago='C')
                WHERE colegiadodeudaanual.IdColegiado = ?
                AND colegiadodeudaanual.Periodo = ?
                AND colegiadodeudaanualcuotas.Estado = 1
                AND colegiadodeudaanualcuotas.SegundoVencimiento < ADDDATE(date(now()), INTERVAL -5 DAY)
                AND agremiacionesdebito.IdColegiado is null
                AND pagosnoregistrados.IdColegiado is null";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado, $periodoActual]);
        $r = $stmt->fetch();
        if ($r) {
            $cantidadPeriodoActual = $r['cantidad'];
            if ($cantidadPeriodoActual >= 1) {
                $codigoDeudor = 1;
            }
        }

        //verifica la deuda de periodos anteriores
        $fechaVencimiento = date('Y-m-d');
        $sql = "SELECT COUNT(colegiadodeudaanualcuotas.id) as cantidad
                FROM colegiadodeudaanualcuotas
                INNER JOIN colegiadodeudaanual ON(colegiadodeudaanual.Id = colegiadodeudaanualcuotas.IdColegiadoDeudaAnual)
                LEFT JOIN agremiacionesdebito ON(agremiacionesdebito.IdColegiado = colegiadodeudaanual.IdColegiado AND agremiacionesdebito.Borrado = 0)
                LEFT JOIN pagosnoregistrados ON(pagosnoregistrados.Recibo = colegiadodeudaanualcuotas.Id AND pagosnoregistrados.TipoPago='C')
                WHERE colegiadodeudaanual.IdColegiado = ?
                AND colegiadodeudaanual.Periodo < ?
                AND colegiadodeudaanualcuotas.Estado = 1 AND colegiadodeudaanualcuotas.FechaVencimiento < ?
                AND pagosnoregistrados.IdColegiado is null";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado, $periodoActual, $fechaVencimiento]);
        $r = $stmt->fetch();
        if ($r) {
            $cantidadPeriodosAnteriores = $r['cantidad'];
            if ($cantidadPeriodosAnteriores >= 1) {
                if($codigoDeudor == 1) {
                    $codigoDeudor = 2;
                } else {
                    $codigoDeudor = 3;
                }
            }
        }

        // verifico los planes de pago
        $sql = "SELECT COUNT(planpagoscuotas.id) as cantidad
                FROM planpagoscuotas
                INNER JOIN planpagos ON(planpagos.id = planpagoscuotas.idplanpagos)
                LEFT JOIN pagosnoregistrados ON(pagosnoregistrados.Recibo = planpagoscuotas.Id AND pagosnoregistrados.TipoPago='P')
                WHERE planpagos.IdColegiado = ?
                AND planpagoscuotas.IdTipoEstadoCuota = 1
                AND planpagoscuotas.Vencimiento <= '".date("Y-m-d")."'
                AND pagosnoregistrados.IdColegiado is null";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $r = $stmt->fetch();
        if ($r) {
            $cantidadPlanPagos = $r['cantidad'];
            if($cantidadPlanPagos >= 1)
            {
                switch ($codigoDeudor){
                    case 0: $codigoDeudor = 7;
                        break;
                    case 1: $codigoDeudor = 4;
                        break;
                    case 2: $codigoDeudor = 5;
                        break;
                    case 3: $codigoDeudor = 6;
                        break;
                    default : $codigoDeudor = 8;
                        break;
                }
            }
        }

        $resultado['codigoDeudor'] = $codigoDeudor;
        $resultado['cuotasAdeudadas'] = $cantidadPeriodoActual + $cantidadPeriodosAnteriores + $cantidadPlanPagos;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando estado";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

//obtiene si es deudor para los certificados
    public function estadoTesoreriaParaCertificadosPorColegiado($idColegiado, $periodoActual){
    $cantidadPeriodoActual = 0;
    $cantidadPeriodosAnteriores = 0;
    $cantidadPlanPagos = 0;

    $codigoDeudor = 0;
    $resultado = array();
    $resultado['estado'] = TRUE;

    try {
        $db = Database::getConnection();
        $sql = "SELECT COUNT(colegiadodeudaanualcuotas.id) as cantidad
                FROM colegiadodeudaanualcuotas
                INNER JOIN colegiadodeudaanual ON(colegiadodeudaanual.Id = colegiadodeudaanualcuotas.IdColegiadoDeudaAnual)
                LEFT JOIN agremiacionesdebito ON(agremiacionesdebito.IdColegiado = colegiadodeudaanual.IdColegiado AND agremiacionesdebito.Borrado = 0)
                LEFT JOIN pagosnoregistrados ON(pagosnoregistrados.Recibo = colegiadodeudaanualcuotas.Id AND pagosnoregistrados.TipoPago='C')
                WHERE colegiadodeudaanual.IdColegiado = ?
                AND colegiadodeudaanual.Periodo <= ?
                AND colegiadodeudaanualcuotas.Estado = 1
                AND colegiadodeudaanualcuotas.SegundoVencimiento < ADDDATE(date(now()), INTERVAL -5 DAY)
                AND agremiacionesdebito.IdColegiado is null
                AND pagosnoregistrados.IdColegiado is null";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado, $periodoActual]);
        $r = $stmt->fetch();
        if ($r) {
            $cantidadPeriodoActual = $r['cantidad'];
            if ($cantidadPeriodoActual >= 1) {
                $codigoDeudor = 1;
            }
        }

        // verifico los planes de pago
        $sql = "SELECT COUNT(planpagoscuotas.id) as cantidad
                FROM planpagoscuotas
                INNER JOIN planpagos ON(planpagos.id = planpagoscuotas.idplanpagos)
                LEFT JOIN pagosnoregistrados ON(pagosnoregistrados.Recibo = planpagoscuotas.Id AND pagosnoregistrados.TipoPago='P')
                WHERE planpagos.IdColegiado = ?
                AND planpagoscuotas.IdTipoEstadoCuota = 1
                AND planpagoscuotas.Vencimiento <= '".date("Y-m-d")."'
                AND pagosnoregistrados.IdColegiado is null";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $r = $stmt->fetch();
        if ($r) {
            $cantidadPlanPagos = $r['cantidad'];
            if($cantidadPlanPagos >= 1)
            {
                switch ($codigoDeudor){
                    case 0: $codigoDeudor = 7;
                        break;
                    case 1: $codigoDeudor = 4;
                        break;
                    case 2: $codigoDeudor = 5;
                        break;
                    case 3: $codigoDeudor = 6;
                        break;
                    default : $codigoDeudor = 8;
                        break;
                }
            }
        }

        $resultado['codigoDeudor'] = $codigoDeudor;
        $resultado['cuotasAdeudadas'] = $cantidadPeriodoActual + $cantidadPeriodosAnteriores + $cantidadPlanPagos;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando estado";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerColegiadoDeudaAnualCuotaPorId($idColegiadoDeudaAnualCuota){
    $sql = "SELECT cdac.IdColegiadoDeudaAnual, cda.Periodo, cdac.Cuota, cdac.Importe, cdac.FechaVencimiento, cdac.Recargo, cdac.SegundoVencimiento
            FROM colegiadodeudaanualcuotas cdac
            INNER JOIN colegiadodeudaanual cda ON cda.Id = cdac.IdColegiadoDeudaAnual
            WHERE cdac.Id = ?";

    $resultado = array();
    $resultado['estado'] = TRUE;
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiadoDeudaAnualCuota]);
        $r = $stmt->fetch();
        if ($r) {
            $datos['idColegiadoDeudaAnual'] = $r['IdColegiadoDeudaAnual'];
            $datos['periodo'] = $r['Periodo'];
            $datos['cuota'] = $r['Cuota'];
            $datos['importe'] = $r['Importe'];
            $datos['fechaVencimiento'] = $r['FechaVencimiento'];
            $datos['recargo'] = $r['Recargo'];
            $datos['segundoVencimiento'] = $r['SegundoVencimiento'];

            $resultado['datos'] = $datos;
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando estado";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando estado";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerPagoTotalPorIdDeudaAnual($idColegiadoDeudaAnual){
    $sql = "SELECT Id, Importe, FechaVencimiento
            FROM colegiadodeudaanualtotal WHERE IdColegiadoDeudaAnual = ? AND FechaVencimiento > date(now())";

    $resultado = array();
    $resultado['estado'] = TRUE;
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiadoDeudaAnual]);
        $r = $stmt->fetch();
        if ($r) {
            $datos['idColegiadoDeudaAnualTotal'] = 8000000 + $r['Id'];
            $datos['cuota'] = 0;
            $datos['importe'] = $r['Importe'];
            $datos['fechaVencimiento'] = $r['FechaVencimiento'];
            $datos['codigoBarra'] = $this->obtenerCodigoBarra44($datos['idColegiadoDeudaAnualTotal'], $r['Importe'], $r['Importe'], $r['FechaVencimiento'], $r['FechaVencimiento'], NULL);

            $resultado['datos'] = $datos;
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando estado";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando estado";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerPagoTotalVigentePorIdColegiado($idColegiado, $periodoActual){
    $sql = "SELECT dat.Id, dat.Importe, dat.FechaVencimiento
        FROM colegiadodeudaanualtotal dat
        INNER JOIN colegiadodeudaanual da ON da.Id = dat.IdColegiadoDeudaAnual
        WHERE da.IdColegiado = ?
        AND da.Periodo = ?
        AND FechaVencimiento >= date(NOW())";

    $resultado = array();
    $resultado['estado'] = TRUE;
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado, $periodoActual]);
        $r = $stmt->fetch();
        if ($r) {
            $datos['idColegiadoDeudaAnualTotal'] = 8000000 + $r['Id'];
            $datos['cuota'] = 0;
            $datos['importe'] = $r['Importe'];
            $datos['fechaVencimiento'] = $r['FechaVencimiento'];
            $datos['codigoBarra'] = $this->obtenerCodigoBarra44($datos['idColegiadoDeudaAnualTotal'], $r['Importe'], $r['Importe'], $r['FechaVencimiento'], $r['FechaVencimiento'], NULL);

            $resultado['datos'] = $datos;
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando pago toal";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando pago toal";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerPagoTotalPorIdDeudaAnual_2021($idColegiadoDeudaAnual, $importeAgregarPagoTotal){
    $sql = "SELECT Id, Importe, FechaVencimiento
            FROM colegiadodeudaanualtotal WHERE IdColegiadoDeudaAnual = ? AND FechaVencimiento > date(now())";

    $resultado = array();
    $resultado['estado'] = TRUE;
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiadoDeudaAnual]);
        $r = $stmt->fetch();
        if ($r) {
            $datos['idColegiadoDeudaAnualTotal'] = 8000000 + $r['Id'];
            $datos['cuota'] = 0;
            $importe = $r['Importe'];
            $fechaVencimiento = $r['FechaVencimiento'];
            $continua = TRUE;
            if ($importeAgregarPagoTotal > 0) {
                $importe += $importeAgregarPagoTotal;
                $resGuardaPagoTotal = $this->actualizarMontoPagoTotal($idColegiadoDeudaAnual, $importeAgregarPagoTotal);
                if (!$resGuardaPagoTotal['estado']) {
                    $continua = FALSE;
                }
            }

            //solo para el periodo 2021 se le suma el importeAgregarPagoToal
            if ($continua) {
                $datos['importe'] = $importe;
                $datos['fechaVencimiento'] = $fechaVencimiento;
                $datos['codigoBarra'] = $this->obtenerCodigoBarra44($datos['idColegiadoDeudaAnualTotal'], $importe, $importe, $fechaVencimiento, $fechaVencimiento, NULL);

                $resultado['datos'] = $datos;
                $resultado['mensaje'] = "OK";
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = false;
                $resultado['mensaje'] = "Error actualizando pago total";
                $resultado['clase'] = 'alert alert-info';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando pago toal";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function actualizarMontoPagoTotal($idColegiadoDeudaAnual, $importeActualizado){
    $sql = "UPDATE colegiadodeudaanualtotal
        SET CuotasImpagas = ?
        WHERE IdColegiadoDeudaAnual = ?";

    $resultado = array();
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$importeActualizado, $idColegiadoDeudaAnual]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando estado";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerColegiadoDeudaAnualCuotas($idColegiadoDeudaAnual){
    $sql = "SELECT Importe, FechaVencimiento
            FROM colegiadodeudaanualcuotas
            WHERE colegiadodeudaanualcuotas.IdColegiadoDeudaAnual = ?
            AND colegiadodeudaanualcuotas.Estado = 1";

    $resultado = array();
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiadoDeudaAnual]);
        $rows = $stmt->fetchAll();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $row = array (
                    'importe' => $r['Importe'],
                    'vencimiento' => $r['FechaVencimiento']
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
            $resultado['mensaje'] = "No hay cuotas de colegiacion pendiente de pago";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando cuotas de colegiacion";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function noTieneDeudaAnual($idColegiado) {
    $sql = "SELECT Id
            FROM colegiadodeudaanual WHERE IdColegiado = ? AND Periodo = ?";

    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado, $_SESSION['periodoActual']]);
        $r = $stmt->fetch();
        if ($r) {
            return FALSE;
        }
    } catch (PDOException $e) {
        // en caso de error se asume que no tiene deuda
    }
    return TRUE;
}

    public function marcarEmitidoColegiadoDeudaAnual($idColegiadoDeudaAnual) {
    $sql = "UPDATE colegiadodeudaanual
            SET EmisionTotal = 'S'
            WHERE Id = ?";

    $resultado = array();
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiadoDeudaAnual]);
        $resultado['estado'] = TRUE;
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
    }
    return $resultado;
}

    public function obtenerCodigoBarra44($idrecibo, $imp1, $imp2, $fecha1, $fecha2, $reciboPP) {
    $dia1 = new DateTime($fecha1);

    $cvto10 = sprintf("%'03s", $dia1->format('z') + 1);
    $cvto10 = substr($fecha1, 2, 2) . $cvto10;

    $dia2 = new DateTime($fecha2);

    $cvto20 = sprintf("%'03s", $dia2->format('z') + 1);
    $cvto20 = substr($fecha2, 2, 2) . $cvto20;

    $Utility = "093";
    if ($idrecibo > 0) {
        $Cuenta = "40" . sprintf("%'07s", $idrecibo);
    } else {
        $Cuenta = "40" . sprintf("%'07s", $reciboPP);
    }
    $Entidad = "70108";

    $imp1 = number_format($imp1, 2, '', '');
    $imp2 = number_format($imp2, 2, '', '');

    $lineacodebar = $Utility . $Entidad . sprintf("%'08s", $imp1) . $cvto10 . $Cuenta . sprintf("%'08s", $imp2) . $cvto20;

    $referencia = '135793579357935793579357935793579357935793579';
    $total = 0;
    for ($i = 0; $i <= 44; $i++) {
        $parcial = intval(substr($lineacodebar, $i, 1)) * intval(substr($referencia, $i, 1));
        $total = $total + $parcial;
    }

    $resultadosuma = $total / 2;
    $modulo10 = intval($resultadosuma) / 10;
    $digito = ($modulo10 - intval($modulo10)) * 10;

    $codigo = $lineacodebar . $digito;

    return($codigo);
}

    public function obtenerCodigoBarra($idrecibo, $imp1, $imp2, $fecha1, $fecha2, $reciboPP) {
    $dia1 = new DateTime($fecha1);

    $cvto10 = sprintf("%'03s", $dia1->format('z') + 1);
    $cvto10 = substr($fecha1, 2, 2) . $cvto10;

    $dia2 = new DateTime($fecha2);

    $cvto20 = sprintf("%'03s", $dia2->format('z') + 1);
    $cvto20 = substr($fecha2, 2, 2) . $cvto20;

    $Utility = "093";
    if ($idrecibo > 0) {
        $Cuenta = "40" . sprintf("%'07s", $idrecibo);
    } else {
        $Cuenta = "40" . sprintf("%'07s", $reciboPP);
    }
    $Entidad = "70108";

    $imp1 = number_format($imp1, 2, '', '');
    $imp2 = number_format($imp2, 2, '', '');

    $lineacodebar = $Utility . $Entidad . sprintf("%'06s", $imp1) . $cvto10 . $Cuenta . sprintf("%'06s", $imp2) . $cvto20;

    $referencia = '135793579357935793579357935793579357935793579';
    $total = 0;
    for ($i = 0; $i <= 40; $i++) {
        $parcial = substr($lineacodebar, $i, 1) * substr($referencia, $i, 1);
        $total = $total + $parcial;
    }

    $resultadosuma = $total / 2;
    $modulo10 = intval($resultadosuma) / 10;
    $digito = ($modulo10 - intval($modulo10)) * 10;

    $codigo = $lineacodebar . $digito;

    return($codigo);
}

    public function obtenerColegiadoEnvioChequera($periodoActual, $rango, $idEnvio){
    $resultado = array();
    //agrego en notificacion los datos del colegiado
    $sql = "SELECT cda.Id, cda.IdColegiado, c.Matricula, p.Sexo, p.Apellido, p.Nombres, cc.CorreoElectronico, cda.Importe, cda.Cuotas, cdat.Importe, cdat.FechaVencimiento, dc.Id, dt.id
        FROM colegiadodeudaanual cda
        INNER JOIN colegiadodeudaanualtotal cdat ON cdat.IdColegiadoDeudaAnual = cda.Id AND cdat.IdEstado = 1
        INNER JOIN colegiado c ON(c.Id = cda.IdColegiado)
        INNER JOIN tipomovimiento tm ON(tm.Id = c.Estado AND tm.Estado = 'A')
        INNER JOIN persona p ON(p.Id = c.IdPersona)
        INNER JOIN colegiadocontacto cc ON(cc.IdColegiado = c.Id AND cc.IdEstado = 1 AND cc.CorreoElectronico is NOT NULL AND UPPER(cc.CorreoElectronico) <> 'NR' AND cc.CorreoElectronico <> '')
        LEFT JOIN enviomaildiariocolegiado emdc ON(emdc.IdColegiado = c.Id AND emdc.IdReferencia = cda.Id AND emdc.IdEnvioMailDiario = ?)
        LEFT JOIN agremiacionesdebito ad ON(ad.IdColegiado = c.Id AND ad.Borrado = 0)
        LEFT JOIN debitocbu dc ON(dc.IdColegiado = c.Id AND dc.Estado = 'A')
        LEFT JOIN debitotarjeta dt ON(dt.IdColegiado = c.Id AND dt.Estado = 'A')
        LEFT JOIN colegiadomailrechazado cmr ON cmr.IdColegiado = c.Id
        WHERE cda.Periodo = ?
            AND emdc.Id IS NULL AND ad.Id is NULL AND dc.Id IS NULL AND dt.id IS NULL AND cmr.Id IS NULL AND cda.Estado = 'A'
        ORDER BY c.Matricula
        LIMIT ?";

    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$idEnvio, $periodoActual, $rango]);
        $rows = $stmt->fetchAll();
        $resultado['cantidad'] = count($rows);
        if ($resultado['cantidad'] > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $row = array (
                    'idColegiadoDeudaAnual' => $r['Id'],
                    'idReferencia' => $r['Id'],
                    'idColegiado' => $r['IdColegiado'],
                    'matricula' => $r['Matricula'],
                    'sexo' => $r['Sexo'],
                    'apellido' => $r['Apellido'],
                    'nombres' => $r['Nombres'],
                    'mail' => $r['CorreoElectronico'],
                    'importe' => $r['Importe'],
                    'cuotas' => $r['Cuotas'],
                    'importeTotal' => $r['Importe'],
                    'fechaVencimiento' => $r['FechaVencimiento'],
                    'idDebitoCBU' => $r['Id'],
                    'idDebitoTarjeta' => $r['id']
                    );
                array_push($datos, $row);
            }
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No se encontro deuda anual";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando deuda anual";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerColegiadoEnvioChequera2021($periodoActual, $rango){
    $resultado = array();
    //agrego en notificacion los datos del colegiado
    $sql = "SELECT cda.Id, cda.IdColegiado, c.Matricula, p.Sexo, p.Apellido, p.Nombres, cc.CorreoElectronico, cda.Importe, cda.ImporteDescuento, dc.Id, dt.id
        FROM colegiadodeudaanual cda
        INNER JOIN colegiado c ON(c.Id = cda.IdColegiado)
        INNER JOIN tipomovimiento tm ON(tm.Id = c.Estado AND tm.Estado = 'A')
        INNER JOIN persona p ON(p.Id = c.IdPersona)
        INNER JOIN colegiadocontacto cc ON(cc.IdColegiado = c.Id AND cc.IdEstado = 1 AND cc.CorreoElectronico is not null AND UPPER(cc.CorreoElectronico) <> 'NR' AND cc.CorreoElectronico <> '')
        LEFT JOIN enviomaildiariocolegiado emdc ON(emdc.IdColegiado = c.Id AND emdc.IdReferencia = cda.Id)
        LEFT JOIN agremiacionesdebito ad ON(ad.IdColegiado = c.Id AND ad.Borrado = 0)
        LEFT JOIN debitocbu dc ON(dc.IdColegiado = c.Id AND dc.Estado = 'A')
        LEFT JOIN debitotarjeta dt ON(dt.IdColegiado = c.Id AND dt.Estado = 'A')
        WHERE cda.Periodo = ? AND cda.EmisionTotal = 'S'
            AND emdc.Id IS NULL AND ad.Id is NULL
        ORDER BY c.Matricula
        LIMIT ?";

    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$periodoActual, $rango]);
        $rows = $stmt->fetchAll();
        $resultado['cantidad'] = count($rows);
        if ($resultado['cantidad'] > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $row = array (
                    'idColegiadoDeudaAnual' => $r['Id'],
                    'idReferencia' => $r['Id'],
                    'idColegiado' => $r['IdColegiado'],
                    'matricula' => $r['Matricula'],
                    'sexo' => $r['Sexo'],
                    'apellido' => $r['Apellido'],
                    'nombres' => $r['Nombres'],
                    'mail' => $r['CorreoElectronico'],
                    'importe' => $r['Importe'],
                    'importeDescuento' => $r['ImporteDescuento'],
                    'idDebitoCBU' => $r['Id'],
                    'idDebitoTarjeta' => $r['id']
                    );
                array_push($datos, $row);
            }
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No se encontro deuda anual";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando deuda anual";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerValorCuotaPuraNotificacionDeuda($idNotificacionColegiado){
    $sql = "SELECT cda.IdColegiado, SUM(cdac.Importe) AS importe
        FROM colegiadodeudaanualcuotas cdac
        INNER JOIN colegiadodeudaanual cda ON cda.Id = cdac.IdColegiadoDeudaAnual
        INNER JOIN notificacioncolegiadodeuda ncd ON ncd.IdColegiadoDeudaAnualCuota = cdac.id
        WHERE ncd.IdNotificacionColegiado = ?
        GROUP BY cda.IdColegiado";

    $resultado = array();
    $resultado['estado'] = TRUE;
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$idNotificacionColegiado]);
        $r = $stmt->fetch();
        if ($r && isset($r['importe']) && $r['importe'] > 0) {
            $datos = array('idColegiado' => $r['IdColegiado'], 'importe' => $r['importe']);
            $resultado['datos'] = $datos;
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No se encontro importe de cuota pura";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando deuda anual";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerComprobantePorMatriculaCuota($matricula, $periodo, $cuota) {
    $sql = "SELECT cdac.Id
        FROM colegiadodeudaanualcuotas cdac
        INNER JOIN colegiadodeudaanual cda ON cda.Id = cdac.IdColegiadoDeudaAnual
        INNER JOIN colegiado c ON c.Id = cda.IdColegiado
        WHERE c.Matricula = ? AND cda.Periodo = ? AND cdac.Cuota = ?
        GROUP BY cda.IdColegiado";

    $resultado = NULL;
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$matricula, $periodo, $cuota]);
        $r = $stmt->fetch();
        if ($r && isset($r['Id']) && $r['Id'] > 0) {
            $resultado = $r['Id'];
        }
    } catch (PDOException $e) {
        $resultado = NULL;
    }
    return $resultado;
}

    public function marcarOpcionResidentePorIdColegiado($idColegiado, $periodo, $opcion) {
    $continua = TRUE;
    switch ($opcion) {
        case 'EXENCION':
            $estadoActual = 1;
            $estadoNuevo = 6;
            break;

        case 'PAGO_CUOTA':
            $estadoActual = 6;
            $estadoNuevo = 1;
            break;

        default:
            $continua = FALSE;
            break;
    }

    if ($continua) {
        $sql = "UPDATE colegiadodeudaanualcuotas cdac
                INNER JOIN colegiadodeudaanual cda ON cda.Id = cdac.IdColegiadoDeudaAnual
                SET cdac.Estado = ?
                WHERE cda.IdColegiado = ? AND cda.Periodo = ? AND cdac.Estado = ?";

        try {
            $db = Database::getConnection();
            $stmt = $db->prepare($sql);
            $stmt->execute([$estadoNuevo, $idColegiado, $periodo, $estadoActual]);
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando deuda anual";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
    } else {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error sin opcion";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerCuotasPeriodoActualParaHomeBanking($idColegiado, $periodoActual, $fechaVencimiento) {
    if (isset($fechaVencimiento) && $fechaVencimiento <> "") {
        $conVencimiento = " AND cdac.FechaVencimiento <= '".$fechaVencimiento."'";
    } else {
        $conVencimiento = "";
    }

    $sql = "SELECT dac.Id, da.Periodo, dac.Cuota, dac.Importe, dac.FechaVencimiento, dac.Importe, dac.FechaVencimiento, hbc.Codigo, hbc.MensajeTicket, hbc.MensajePantalla
            FROM colegiadodeudaanualcuotas dac
            INNER JOIN colegiadodeudaanual da ON da.Id = dac.IdColegiadoDeudaAnual
            INNER JOIN home_banking_concepto hbc ON hbc.CuotaPeriodo = dac.Cuota
            WHERE da.IdColegiado = ? AND da.Periodo = ?
            AND dac.Estado = 1 AND dac.FechaVencimiento <= ?";

    $resultado = array();
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado, $periodoActual, $fechaVencimiento]);
        $rows = $stmt->fetchAll();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $vencimientoDos = $r['FechaVencimiento'];
                $importeDos = $r['Importe'];
                //verifico el vencimiento, sino le calculo el recargo
                if ($vencimientoDos < date('Y-m-d')){
                    $importeActualizado = $this->obtenerRecargoCuota($vencimientoDos, date('Y-m-d'), $importeDos);
                } else {
                    $importeActualizado = $importeDos;
                }

                $row = array (
                    'idColegiadoDeudaAnualCuota' => $r['Id'],
                    'periodo' => $r['Periodo'],
                    'cuota' => $r['Cuota'],
                    'importe' => $r['Importe'],
                    'importeActualizado' => $importeActualizado,
                    'fechaVencimiento' => $vencimientoDos,
                    'concepto' => $r['Codigo'],
                    'mensajeTicket' => $r['MensajeTicket'],
                    'mensajePantalla' => $r['MensajePantalla']
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
            $resultado['mensaje'] = "No hay cuotas de colegiacion pendiente de pago";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando cuotas de colegiacion";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}
}
