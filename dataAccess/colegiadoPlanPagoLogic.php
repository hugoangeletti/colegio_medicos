<?php
class colegiadoPlanPagoLogic {

    private $deudaAnualLogic;

    public function __construct() {
        $this->deudaAnualLogic = new colegiadoDeudaAnualLogic();
    }

    public function obtenerPlanPagoPorId($idPlanPago) {
    $sql = "SELECT * FROM planpagos WHERE planpagos.Id = ?";

    $resultado = array();
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$idPlanPago]);
        $r = $stmt->fetch();
        if ($r) {
            $datos = array(
                    'idPlanPago' => $r['Id'],
                    'fechaCreacion' => $r['FechaCreacion'],
                    'idUsuario' => $r['IdUsuario'],
                    'importeTotal' => $r['ImporteTotal'],
                    'cuotas' => $r['Cuotas'],
                    'importePeriodoActual' => $r['ImportePeriodoActual'],
                    'importePeriodos' => $r['ImportePeriodos'],
                    'importeOtroPP' => $r['ImporteOtroPP'],
                    'recargoFinanciero' => $r['RecargoFinanciero'],
                    'estado' => $r['Estado'],
                    'importeSAP' => $r['ImporteSAP'],
                    'recargoExtensionCuotas' => $r['RecargoExtensionCuotas'],
                    'idColegiado' => $r['IdColegiado']
                 );
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "No hay cuotas de Plan de pagos";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando Plan de pagos";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerPlanPagoCuotaPorId($id) {
    $sql = "SELECT * FROM planpagoscuotas WHERE Id = ?";

    $resultado = array();
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        $r = $stmt->fetch();
        if ($r) {
            $vencimiento = $r['Vencimiento'];
            $importe = $r['Importe'];
            if ($vencimiento < date('Y-m-d')){
                $importeActualizado = $this->deudaAnualLogic->obtenerRecargoCuota($vencimiento, date('Y-m-d'), $importe);
            } else {
                $importeActualizado = $importe;
            }

            $datos = array(
                    'idPlaPagoCuota' => $r['Id'],
                    'idPlanPago' => $r['IdPlanPagos'],
                    'cuota' => $r['Cuota'],
                    'importe' => $importe,
                    'vencimiento' => $vencimiento,
                    'fechaPago' => $r['FechaPago'],
                    'estado' => $r['Estado'],
                    'importeActualizado' => $importeActualizado,
                    'idRefinanciado' => $r['IdRefinanciado'],
                    'segundoVencimiento' => $r['SegundoVencimiento'],
                    'segundoImporte' => $r['SegundoImporte'],
                    'idTipoEstadoCuota' => $r['IdTipoEstadoCuota'],
                    'fechaActualizacion' => $r['FechaActualizacion']
                 );
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "No hay cuotas de Plan de pagos";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando Plan de pagos";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerColegiadoPlanPago($idColegiado){
    $sql = "SELECT planpagos.Id, planpagoscuotas.Cuota, planpagoscuotas.Importe, planpagoscuotas.Vencimiento
            FROM planpagoscuotas
            INNER JOIN planpagos ON(planpagos.Id = planpagoscuotas.IdPlanPagos)
            WHERE planpagos.IdColegiado = ?
            AND planpagos.Estado = 'A'
            AND planpagoscuotas.IdTipoEstadoCuota = 1";

    $resultado = array();
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $rows = $stmt->fetchAll();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $vencimiento = $r['Vencimiento'];
                $importe = $r['Importe'];
                //verifico el vencimiento, sino le calculo el recargo
                if ($vencimiento < date('Y-m-d')){
                    $importeActualizado = $this->deudaAnualLogic->obtenerRecargoCuota($vencimiento, date('Y-m-d'), $importe);
                } else {
                    $importeActualizado = $importe;
                }
                $row = array (
                    'idPlanPago' => $r['Id'],
                    'cuota' => $r['Cuota'],
                    'importe' => $importe,
                    'importeActualizado' => $importeActualizado,
                    'vencimiento' => $vencimiento
                 );
                array_push($datos, $row);
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "No hay cuotas de Plan de pagos";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando Plan de pagos";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function tienePlanPagos($idColegiado){
    $sql = "SELECT count(planpagoscuotas.Cuota) AS Cantidad
            FROM planpagoscuotas
            INNER JOIN planpagos ON(planpagos.Id = planpagoscuotas.IdPlanPagos)
            WHERE planpagos.IdColegiado = ?
            AND planpagos.Estado = 'A'
            AND planpagoscuotas.IdTipoEstadoCuota = 1";

    $resultado = FALSE;
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $r = $stmt->fetch();
        if ($r && $r['Cantidad'] > 0) {
            $resultado['estado'] = TRUE;
        }
    } catch (PDOException $e) {
        $resultado = FALSE;
    }

    return $resultado;
}

    public function obtenerPlanPagoPorIdColegiado($idColegiado){
    $sql = "SELECT planpagos.Id, planpagos.FechaCreacion, planpagos.ImporteTotal, planpagos.Cuotas, planpagos.Estado
            FROM planpagos
            WHERE planpagos.IdColegiado = ? AND planpagos.Estado = 'A'";

    $resultado = array();
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $rows = $stmt->fetchAll();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $row = array (
                    'idPlanPago' => $r['Id'],
                    'cuotas' => $r['Cuotas'],
                    'importe' => $r['ImporteTotal'],
                    'fechaCreacion' => $r['FechaCreacion'],
                    'estado' => $r['Estado']
                 );
                array_push($datos, $row);
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "No hay Plan de pagos";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando Plan de pagos";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerPlanPagosCuotasPorIdPlanPago($idPlanPago){
    $sql = "SELECT planpagoscuotas.Id, planpagoscuotas.Cuota, planpagoscuotas.Importe,
            planpagoscuotas.Vencimiento, planpagoscuotas.IdTipoEstadoCuota, planpagoscuotas.FechaPago
            FROM planpagoscuotas
            WHERE planpagoscuotas.IdPlanPagos = ?";

    $resultado = array();
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$idPlanPago]);
        $rows = $stmt->fetchAll();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $estado = $r['IdTipoEstadoCuota'];
                $vencimiento = $r['Vencimiento'];
                $importe = $r['Importe'];
                //verifico el vencimiento, sino le calculo el recargo si no esta paga
                if ($estado == 1 && $vencimiento < date('Y-m-d')){
                    $importeActualizado = $this->deudaAnualLogic->obtenerRecargoCuota($vencimiento, date('Y-m-d'), $importe);
                    $vencimiento = sumarRestarSobreFecha(date('Y-m-d'), 7, 'day', '+');
                } else {
                    $importeActualizado = $importe;
                }
                $row = array (
                    'idPlanPagoCuota' => $r['Id'],
                    'cuota' => $r['Cuota'],
                    'importe' => $importe,
                    'importeActualizado' => $importeActualizado,
                    'vencimiento' => $vencimiento,
                    'fechaPago' => $r['FechaPago'],
                    'estado' => $estado
                 );
                array_push($datos, $row);
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "No hay cuotas de Plan de pagos";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando Plan de pagos";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerCuotaActualPlanPagosPorIdColegiado($idColegiado, $conect) {
    $sql = "SELECT ppc.Id, ppc.Importe, ppc.Vencimiento, ppc.Cuota, ppc.IdPlanPagos
        FROM planpagoscuotas ppc
        INNER JOIN planpagos pp ON pp.Id = ppc.IdPlanPagos
        LEFT JOIN pagosnoregistrados pnr ON pnr.Recibo = ppc.Id AND pnr.TipoPago='P'
        WHERE pp.IdColegiado = ? AND pp.Estado = 'A'
        AND ppc.IdTipoEstadoCuota = 1 AND ppc.Vencimiento <= DATE(NOW())
        AND pnr.Id IS NULL
        ORDER BY ppc.IdPlanPagos, ppc.Cuota";

    $resultado = array();
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $rows = $stmt->fetchAll();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $vencimiento = $r['Vencimiento'];
                $importe = $r['Importe'];
                $vencimientoActual = ultmioDiaDelMes(date('Y-m-d'));
                $recargo = $this->deudaAnualLogic->obtenerRecargoCuota($vencimiento, $vencimientoActual, $importe);
                $row = array (
                    'idPlanPagosCuotas' => $r['Id'],
                    'periodo' => $r['IdPlanPagos'],
                    'cuota' => $r['Cuota'],
                    'importe' => $importe,
                    'fechaVencimiento' => $vencimiento,
                    'importeSegundoVto' => $importe,
                    'fechaSegundoVencimiento' => $vencimiento,
                    'recargo' => $recargo,
                    'vencimiento' => $vencimientoActual,
                    'idDeuda' => $r['Id']
                 );
                array_push($datos, $row);
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "No hay cuotas de Plan de pagos";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando Plan de pagos";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerProximaCuotaPlanPagoIdColegiado($idColegiado, $conect) {
    $sql = "SELECT ppc.Id, ppc.Importe, ppc.Vencimiento, ppc.Cuota, ppc.IdPlanPagos
        FROM planpagoscuotas ppc
        INNER JOIN planpagos pp ON pp.Id = ppc.IdPlanPagos
        LEFT JOIN pagosnoregistrados pnr ON pnr.Recibo = ppc.Id AND pnr.TipoPago='P'
        WHERE pp.IdColegiado = ? AND pp.Estado = 'A'
        AND ppc.IdTipoEstadoCuota = 1 AND ppc.Vencimiento > DATE(NOW())
        AND pnr.Id IS NULL
        ORDER BY ppc.IdPlanPagos, ppc.Cuota
          LIMIT 1";

    $resultado = array();
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $r = $stmt->fetch();
        if ($r) {
            $importe = $r['Importe'];
            $vencimiento = $r['Vencimiento'];
            $recargo = $importe;
            $vencimientoActual = ultmioDiaDelMes(date('Y-m-d'));
            $linea = array(
                    'idPlanPagosCuotas' => $r['Id'],
                    'periodo' => $r['IdPlanPagos'],
                    'cuota' => $r['Cuota'],
                    'importe' => $importe,
                    'fechaVencimiento' => $vencimiento,
                    'importeSegundoVto' => $importe,
                    'fechaSegundoVencimiento' => $vencimiento,
                    'recargo' => $recargo,
                    'vencimiento' => $vencimientoActual,
                    'idDeuda' => $r['Id']
                 );
            $datos = array();
            array_push($datos, $linea);
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "No hay cuotas de Plan de pagos";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando Plan de pagos";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerDeudaPlanPagosPorIdColegiado($idColegiado) {
    $sql = "SELECT planpagoscuotas.Id, planpagoscuotas.Importe, planpagoscuotas.Vencimiento, planpagoscuotas.Cuota,
        planpagoscuotas.IdPlanPagos
        FROM planpagoscuotas
        INNER JOIN planpagos ON(planpagos.Id = planpagoscuotas.IdPlanPagos)
        LEFT JOIN pagosnoregistrados ON(pagosnoregistrados.Recibo = planpagoscuotas.Id AND pagosnoregistrados.TipoPago='P')
        WHERE planpagos.IdColegiado = ?
        AND planpagoscuotas.IdTipoEstadoCuota=1
        AND pagosnoregistrados.Id IS NULL
        ORDER BY planpagoscuotas.IdPlanPagos, planpagoscuotas.Cuota";

    $resultado = array();
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $rows = $stmt->fetchAll();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $vencimiento = $r['Vencimiento'];
                $importe = $r['Importe'];
                if ($vencimiento < date('Y-m-d')){
                    $importeActualizado = $this->deudaAnualLogic->obtenerRecargoCuota($vencimiento, date('Y-m-d'), $importe);
                } else {
                    $importeActualizado = $importe;
                }
                $row = array (
                    'idPlanPagosCuotas' => $r['Id'],
                    'importe' => $importe,
                    'importeActualizado' => $importeActualizado,
                    'vencimiento' => $vencimiento,
                    'cuota' => $r['Cuota'],
                    'idPlanPagos' => $r['IdPlanPagos']
                 );
                array_push($datos, $row);
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "No hay cuotas de Plan de pagos";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando Plan de pagos";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function agregarColegiadoPlanPagos($idColegiado, $deudaPlanPago, $deudaAnterior, $cuotas, $totalFinanciar, $valorCuota, $recargoExtension){
    $resultado = array();
    try {
        $db = Database::getConnection();
        $db->beginTransaction();

        //agregamos el plan de pagos
        $sql = "INSERT INTO planpagos (FechaCreacion, IdUsuario, ImporteTotal, Cuotas,
            ImportePeriodos, ImporteOtroPP, Estado, RecargoExtensionCuotas, IdColegiado)
            VALUES (date(now()), ?, ?, ?, ?, ?, 'A', ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$_SESSION['user_id'], $totalFinanciar, $cuotas, $deudaAnterior, $deudaPlanPago, $recargoExtension, $idColegiado]);
        $resultado['estado'] = TRUE;
        $idPlanPago = $db->lastInsertId();

        //marcamos las cuotas que se incluyen, de cuotas de colegiacion y si tiene de plan anterior
        $sql = "UPDATE colegiadodeudaanualcuotas, colegiadodeudaanual
                SET colegiadodeudaanualcuotas.IdPlanPago = ?,
                    colegiadodeudaanualcuotas.Estado = 3
                WHERE colegiadodeudaanual.IdColegiado = ?
                    AND colegiadodeudaanual.Id = colegiadodeudaanualcuotas.IdColegiadoDeudaAnual
                    AND colegiadodeudaanual.Periodo <> ?
                    AND colegiadodeudaanualcuotas.Estado = 1
                    AND (colegiadodeudaanualcuotas.IdPlanPago = 0
                        OR colegiadodeudaanualcuotas.IdPlanPago is null)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idPlanPago, $idColegiado, $_SESSION['periodoActual']]);

        $sql = "UPDATE planpagoscuotas, planpagos
                SET planpagoscuotas.IdRefinanciado = ?,
                    planpagoscuotas.IdTipoEstadoCuota = 3,
                    planpagos.Estado = 'R'
                WHERE planpagos.IdColegiado = ?
                    AND planpagos.Id = planpagoscuotas.IdPlanPagos
                    AND planpagoscuotas.IdTipoEstadoCuota = 1
                    AND (planpagoscuotas.IdRefinanciado = 0
                        OR planpagoscuotas.IdRefinanciado is null)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idPlanPago, $idColegiado]);

        //generamos las cuotas
        $cuota = 1;
        $fecha = new DateTime();
        date_add($fecha, date_interval_create_from_date_string('10 days'));
        $fechaVencimiento = $fecha->format('Y-m-d');
        $segundoImporte = round($valorCuota * 1.015, 0);
        while ($cuota <= $cuotas) {
            date_add($fecha, date_interval_create_from_date_string('10 days'));
            $segundoVencimiento = $fecha->format('Y-m-d');
            $sql = "INSERT INTO planpagoscuotas
                (IdPlanPagos, Cuota, Importe, Vencimiento, SegundoVencimiento, SegundoImporte, IdTipoEstadoCuota)
                VALUES (?, ?, ?, ?, ?, ?, 1)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idPlanPago, $cuota, $valorCuota, $fechaVencimiento, $segundoVencimiento, $segundoImporte]);
            $fecha = new DateTime($fechaVencimiento);
            $intervalo = new DateInterval('P1M');

            $fecha->add($intervalo);
            $fechaVencimiento = $fecha->format('Y-m-d');
            $cuota++;
        }

        $resultado['idPlanPago'] = $idPlanPago;
        $resultado['mensaje'] = "OK(".$idPlanPago.")";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';

        $db->commit();
        return $resultado;

    } catch (PDOException $e) {
        $db->rollBack();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL GENERAR PLAN DE PAGOS";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
        return $resultado;
    }
}

    public function anularColegiadoPlanPagos($idPlanPago){
    $resultado = array();
    $resultado['estado'] = TRUE;
    try {
        $db = Database::getConnection();
        $db->beginTransaction();

        //marcamos las cuotas que se incluyen, de cuotas de colegiacion y si tiene de plan anterior
        $sql = "UPDATE colegiadodeudaanualcuotas
                SET colegiadodeudaanualcuotas.IdPlanPago = NULL,
                    colegiadodeudaanualcuotas.Estado = 1
                WHERE colegiadodeudaanualcuotas.IdPlanPago = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idPlanPago]);

        $sql = "UPDATE planpagoscuotas
                SET planpagoscuotas.IdRefinanciado = NULL,
                    planpagoscuotas.IdTipoEstadoCuota = 1
                WHERE planpagoscuotas.IdRefinanciado = ?
                    AND planpagoscuotas.IdTipoEstadoCuota = 3";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idPlanPago]);

        $sql = "UPDATE planpagos
                SET planpagos.Estado = 'N'
                WHERE planpagos.Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idPlanPago]);

        //borramos las cuotas
        $sql = "DELETE FROM planpagoscuotas
            WHERE planpagoscuotas.IdPlanPagos = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idPlanPago]);

        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
        $db->commit();
        return $resultado;

    } catch (PDOException $e) {
        $db->rollBack();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL GENERAR PLAN DE PAGOS";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
        return $resultado;
    }
}

    public function obtenerCuotasEnPlanPagos($idPlanPago) {
    $sql = "(SELECT 'C' AS Tipo, colegiadodeudaanual.Periodo, colegiadodeudaanualcuotas.Cuota, colegiadodeudaanualcuotas.Importe
        FROM colegiadodeudaanualcuotas
        INNER JOIN colegiadodeudaanual ON(colegiadodeudaanual.Id = colegiadodeudaanualcuotas.IdColegiadoDeudaAnual)
        WHERE colegiadodeudaanualcuotas.IdPlanPago = ?)
        UNION
        (SELECT 'PP' AS Tipo, planpagoscuotas.IdPlanPagos AS Periodo, planpagoscuotas.Cuota, planpagoscuotas.Importe
        FROM planpagoscuotas
        WHERE planpagoscuotas.IdRefinanciado = ?)";

    $resultado = array();
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$idPlanPago, $idPlanPago]);
        $rows = $stmt->fetchAll();
        if (count($rows) > 0) {
            $periodoAnterior = 0;
            $tipoAnterior = '';
            $datos = array();
            foreach ($rows as $r) {
                $tipo = $r['Tipo'];
                $periodo = $r['Periodo'];
                $cuota = $r['Cuota'];
                if ($periodo <> $periodoAnterior) {
                    if ($periodoAnterior <> 0) {
                        if ($tipoAnterior == 'C') {
                            $elTipo = 'Periodo: ';
                        } else {
                            $elTipo = 'Plan de Pagos: ';
                        }
                        $lineaPeriodo = $elTipo.'<b>'.$periodoAnterior.'</b> - cuotas: <b>'.$cuotas.'</b>';
                        array_push($datos, $lineaPeriodo);
                    }
                    $periodoAnterior = $periodo;
                    $tipoAnterior = $tipo;
                    $cuotas = '';
                }
                $cuotas .= $cuota.'-';
            }
            if ($periodoAnterior <> 0) {
                if ($tipoAnterior == 'C') {
                    $elTipo = 'Periodo: ';
                } else {
                    $elTipo = 'Plan de Pagos: ';
                }
                $lineaPeriodo = $elTipo.'<b>'.$periodoAnterior.'</b> - cuotas: <b>'.$cuotas.'</b>';
                array_push($datos, $lineaPeriodo);
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "No hay cuotas de Plan de pagos";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando Plan de pagos";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}
}
