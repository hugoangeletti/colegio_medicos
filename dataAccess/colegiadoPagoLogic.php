<?php
class colegiadoPagoLogic {

    public function obtenerPagosColegiacionPorIdColegiado($idColegiado, $fechaDesde, $fechaHasta){
    $db = Database::getConnection();
    $sql = "(SELECT cobranzadetalle.Periodo, cobranzadetalle.Cuota, cobranzadetalle.FechaPago AS FechaPago,
            cobranzadetalle.Importe, cobranzadetalle.Recibo, lugarpago.Detalle AS DetalleLugarPago,
            tipopago.Detalle AS TipoPago, cobranzadetalle.TipoPago AS IdTipoPago
            FROM cobranzadetalle
            INNER JOIN cobranza on(cobranza.Id = cobranzadetalle.IdLoteCobranza)
            INNER JOIN lugarpago on(lugarpago.Id = cobranza.IdLugarPago)
            LEFT JOIN tipopago ON(tipopago.Id = cobranzadetalle.TipoPago)
            WHERE cobranzadetalle.IdColegiado = ?
            AND cobranzadetalle.FechaPago BETWEEN ? AND ?
            AND cobranzadetalle.TipoPago IN(1, 2, 3, 4, 5, 8))
            UNION
            (SELECT cajadiariamovimientodetalle.Periodo, cajadiariamovimientodetalle.Cuota, cajadiariamovimiento.Fecha AS FechaPago,
            cajadiariamovimientodetalle.Monto, cajadiariamovimiento.Numero, 'Caja Diaria',
            tipopago.Detalle AS TipoPago, cajadiariamovimientodetalle.CodigoPago AS IdTipoPago
            FROM cajadiariamovimientodetalle
            INNER JOIN cajadiariamovimiento on(cajadiariamovimiento.Id = cajadiariamovimientodetalle.IdCajaDiariaMovimiento)
            LEFT JOIN tipopago ON(tipopago.Id = cajadiariamovimientodetalle.CodigoPago)
            WHERE cajadiariamovimiento.IdColegiado = ?
            AND cajadiariamovimiento.Estado <> 'A'
            AND cajadiariamovimiento.Fecha BETWEEN ? AND ?
            AND cajadiariamovimientodetalle.CodigoPago IN(1, 2, 3, 5, 8))
            ORDER BY FechaPago DESC";

    $resultado = array();
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado, $fechaDesde, $fechaHasta, $idColegiado, $fechaDesde, $fechaHasta]);
        $dados = $stmt->fetchAll();
        if (count($dados) > 0) {
            $datos = array();
            foreach ($dados as $row) {
                $datos[] = array(
                    'periodo' => $row['Periodo'],
                    'cuota' => $row['Cuota'],
                    'importe' => $row['Importe'],
                    'fechaPago' => $row['FechaPago'],
                    'recibo' => $row['Recibo'],
                    'lugarPago' => $row['DetalleLugarPago'],
                    'tipoPago' => $row['TipoPago'],
                    'idTipoPago' => $row['IdTipoPago']
                );
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "No hay pagos registrados";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando pagos";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerPagosPorIdColegiado($idColegiado){
    $db = Database::getConnection();
    $sql = "(SELECT cobranzadetalle.Periodo, cobranzadetalle.Cuota, cobranzadetalle.FechaPago AS FechaPago,
            cobranzadetalle.Importe, cobranzadetalle.Recibo, lugarpago.Detalle AS DetalleLugarPago,
            tipopago.Detalle AS TipoPago, cobranzadetalle.TipoPago AS IdTipoPago
            FROM cobranzadetalle
            INNER JOIN cobranza on(cobranza.Id = cobranzadetalle.IdLoteCobranza)
            INNER JOIN lugarpago on(lugarpago.Id = cobranza.IdLugarPago)
            INNER JOIN tipopago ON(tipopago.Id = cobranzadetalle.TipoPago)
            WHERE cobranzadetalle.IdColegiado = ?
            AND (cobranzadetalle.FechaPago >= ADDDATE(date(now()), INTERVAL -3 YEAR) AND cobranzadetalle.FechaPago <= date(now())))
            UNION
            (SELECT cajadiariamovimientodetalle.Periodo, cajadiariamovimientodetalle.Cuota, cajadiariamovimiento.Fecha AS FechaPago,
            cajadiariamovimientodetalle.Monto, cajadiariamovimiento.Numero, 'Caja Diaria',
            tipopago.Detalle AS TipoPago, cajadiariamovimientodetalle.CodigoPago AS IdTipoPago
            FROM cajadiariamovimientodetalle
            INNER JOIN cajadiariamovimiento on(cajadiariamovimiento.Id = cajadiariamovimientodetalle.IdCajaDiariaMovimiento)
            INNER JOIN tipopago ON(tipopago.Id = cajadiariamovimientodetalle.CodigoPago)
            WHERE cajadiariamovimiento.IdColegiado = ?
            AND (cajadiariamovimiento.Fecha >= ADDDATE(date(now()), INTERVAL -3 YEAR) AND cajadiariamovimiento.Fecha <= date(now())))
            ORDER BY FechaPago DESC";

    $resultado = array();
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado, $idColegiado]);
        $dados = $stmt->fetchAll();
        if (count($dados) > 0) {
            $datos = array();
            foreach ($dados as $row) {
                $datos[] = array(
                    'periodo' => $row['Periodo'],
                    'cuota' => $row['Cuota'],
                    'importe' => $row['Importe'],
                    'fechaPago' => $row['FechaPago'],
                    'recibo' => $row['Recibo'],
                    'lugarPago' => $row['DetalleLugarPago'],
                    'tipoPago' => $row['TipoPago'],
                    'idTipoPago' => $row['IdTipoPago']
                );
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "No hay pagos registrados";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando pagos";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerPagoNoRegistrado($idColegiadoDeudaAnualCuota, $tipoPago){
    $db = Database::getConnection();
    $sql = "select Estado from pagosnoregistrados where Recibo = ? and TipoPago = ?";

    $resultado = array();
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiadoDeudaAnualCuota, $tipoPago]);
        $dados = $stmt->fetchAll();
        if (count($dados) > 0) {
            $row = $dados[0];
            $pagoNoRegistrado = '';
            if ($row['Estado'] == 'C'){
                $pagoNoRegistrado = 'Pago No Registrado';
            }
            $datos = array(
                'pagoNoRegistrado' => $pagoNoRegistrado
            );
            $resultado['estado'] = TRUE;
            $resultado['datos'] = $datos;
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "No hay pagos no registrados";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando pagos no registrados";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerPagosPorOtrosConceptos($idColegiado){
    $db = Database::getConnection();
    $sql = "(SELECT cobranzadetalle.FechaPago AS FechaPago, cobranzadetalle.Importe, cobranzadetalle.Recibo,
            tipopago.Detalle AS TipoPago, lugarpago.Detalle AS LugarPago
            FROM cobranzadetalle
            INNER JOIN cobranza on(cobranza.Id = cobranzadetalle.IdLoteCobranza)
            INNER JOIN lugarpago on(lugarpago.Id = cobranza.IdLugarPago)
            LEFT JOIN tipopago ON(tipopago.Id = cobranzadetalle.TipoPago)
            WHERE cobranzadetalle.IdColegiado = ?
            AND cobranzadetalle.TipoPago NOT IN(1, 2, 3, 5, 8))

            UNION ALL

            (SELECT cajadiariamovimiento.Fecha AS FechaPago, cajadiariamovimientodetalle.Monto, cajadiariamovimiento.Numero,
            tipopago.Detalle, 'Caja Diaria'
            FROM cajadiariamovimientodetalle
            INNER JOIN cajadiariamovimiento on(cajadiariamovimiento.Id = cajadiariamovimientodetalle.IdCajaDiariaMovimiento)
            LEFT JOIN tipopago ON(tipopago.Id = cajadiariamovimientodetalle.CodigoPago)
            WHERE cajadiariamovimiento.IdColegiado = ?
            AND cajadiariamovimientodetalle.CodigoPago NOT IN(1, 2, 3, 5, 8))
            ORDER BY FechaPago DESC";

    $resultado = array();
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado, $idColegiado]);
        $dados = $stmt->fetchAll();
        if (count($dados) > 0) {
            $datos = array();
            foreach ($dados as $row) {
                $datos[] = array(
                    'importe' => $row['Importe'],
                    'fechaPago' => $row['FechaPago'],
                    'numeroRecibo' => $row['Recibo'],
                    'tipoPago' => $row['TipoPago'],
                    'lugarPago' => $row['LugarPago']
                );
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "No hay pagos registrados";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando pagos";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerCuotasPorNotaDeuda($idNotificacionColegiado) {
    $db = Database::getConnection();
    $sql = "SELECT dac.Id, da.Periodo, dac.Cuota, dac.Importe, ncd.ValorActualizado, dac.FechaVencimiento
            FROM colegiadodeudaanualcuotas dac
            INNER JOIN colegiadodeudaanual da ON da.Id = dac.IdColegiadoDeudaAnual
            INNER JOIN notificacioncolegiadodeuda ncd ON ncd.IdColegiadoDeudaAnualCuota = dac.Id
            WHERE ncd.IdNotificacionColegiado = ?";

    $resultado = array();
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute([$idNotificacionColegiado]);
        $dados = $stmt->fetchAll();
        if (count($dados) > 0) {
            $datos = array();
            foreach ($dados as $row) {
                $datos[] = array(
                    'idColegiadoDeudaAnualCuota' => $row['Id'],
                    'periodo' => $row['Periodo'],
                    'cuota' => $row['Cuota'],
                    'importe' => $row['Importe'],
                    'importeActualizado' => $row['ValorActualizado'],
                    'fechaVencimiento' => $row['FechaVencimiento']
                );
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "No hay pagos registrados";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando pagos";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}
}
