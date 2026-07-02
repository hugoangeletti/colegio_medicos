<?php
class notificacionDeudaLogic {

    private $deudaAnualLogic;
    private function getDeudaAnualLogic() {
        if (!$this->deudaAnualLogic) {
            $this->deudaAnualLogic = new colegiadoDeudaAnualLogic();
        }
        return $this->deudaAnualLogic;
    }

    public function obtenerNotificacionDeudaPorId($idNotificacion){
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "SELECT notificacioncolegiadodeuda.*, colegiadodeudaanual.Periodo, colegiadodeudaanualcuotas.Cuota,
                colegiadodeudaanualcuotas.Importe, colegiadodeudaanualcuotas.FechaVencimiento,
                IF(notificacioncolegiadodeuda.IdColegiadoDeudaAnualCuota is null, 'P', 'C') AS Origen,
                planpagoscuotas.IdPlanPagos, planpagoscuotas.Cuota AS CuotaPP, planpagoscuotas.Importe AS ImportePP, planpagoscuotas.Vencimiento
                FROM notificacioncolegiadodeuda
                INNER JOIN notificacioncolegiado ON(notificacioncolegiado.IdNotificacionColegiado = notificacioncolegiadodeuda.IdNotificacionColegiado)
                LEFT JOIN colegiadodeudaanualcuotas ON(colegiadodeudaanualcuotas.Id = notificacioncolegiadodeuda.IdColegiadoDeudaAnualCuota)
                LEFT JOIN colegiadodeudaanual ON(colegiadodeudaanual.Id = colegiadodeudaanualcuotas.IdColegiadoDeudaAnual)
                LEFT JOIN planpagoscuotas ON(planpagoscuotas.Id = notificacioncolegiadodeuda.IdPlanPagosCuota)
                WHERE notificacioncolegiado.IdNotificacion = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idNotificacion]);
        $filas = $stmt->fetchAll();
        if (count($filas) > 0) {
            $datos = array();
            foreach ($filas as $row) {
                $datos[] = array(
                    'idNotificacionColegiadoDeuda' => $row['IdNotificacionColegiadoDeuda'],
                    'idNotificacionColegiado' => $row['IdNotificacionColegiado'],
                    'idColegiadoDeudaAnualCuota' => $row['IdColegiadoDeudaAnualCuota'],
                    'idPlanPagosCuota' => $row['IdPlanPagosCuota'],
                    'valorActualizado' => $row['ValorActualizado'],
                    'periodo' => $row['Periodo'],
                    'cuota' => $row['Cuota'],
                    'importe' => $row['Importe'],
                    'vencimiento' => $row['FechaVencimiento'],
                    'origen' => $row['Origen'],
                    'idPlanPagos' => $row['IdPlanPagos'],
                    'cuotaPlanPagos' => $row['CuotaPP'],
                    'importePlanPagos' => $row['ImportePP'],
                    'vencimientoPlanPagos' => $row['Vencimiento']
                );
            }
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No se encontro notificacion del colegiado";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando notificacion del colegiado";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerIdNotificacionVigente($idColegiado) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "SELECT MAX(n.IdNotificacion) AS maxId
                FROM notificacion n
                INNER JOIN notificacioncolegiado nc ON nc.IdNotificacion = n.IdNotificacion
            WHERE nc.IdColegiado = ?
                AND n.FechaVencimiento > DATE(NOW()) AND n.FechaCreacion = DATE(NOW())
            AND n.Estado IN('A')";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $row = $stmt->fetch();
        $resultado['estado'] = true;
        if ($row) {
            $resultado['mensaje'] = "OK";
            $resultado['idNotificacion'] = $row['maxId'];
        } else {
            $resultado['idNotificacion'] = 0;
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando notificacion del colegiado";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerColegiadoNotificacionDeuda($rango){
    $resultado = array();
    $fechaLimite = date('Y').'-05-31';
    try {
        $db = Database::getConnection();
        $sql = "SELECT n.IdNotificacion, nc.IdNotificacionColegiado, nc.IdColegiado, c.Matricula, p.Sexo, p.Apellido, p.Nombres, cc.CorreoElectronico, n.FechaCreacion, n.FechaVencimiento
        FROM notificacion n
        INNER JOIN notificacioncolegiado nc ON(nc.IdNotificacion = n.IdNotificacion AND nc.TipoEnvio = 'E')
        INNER JOIN colegiado c ON(c.Id = nc.IdColegiado)
        INNER JOIN tipomovimiento ON(tipomovimiento.Id = c.Estado AND tipomovimiento.Estado = 'A')
        INNER JOIN persona p ON(p.Id = c.IdPersona)
        INNER JOIN colegiadocontacto cc ON(cc.IdColegiado = c.Id
            AND cc.IdEstado = 1
            AND cc.CorreoElectronico is not null
            AND UPPER(cc.CorreoElectronico) <> 'NR'
            AND cc.CorreoElectronico <> '')
        LEFT JOIN enviomaildiariocolegiado emdc ON(emdc.IdColegiado = nc.IdColegiado AND emdc.IdReferencia = nc.IdNotificacionColegiado)
        LEFT JOIN agremiacionesdebito ad ON ad.IdColegiado = c.Id AND ad.Borrado = 0
        WHERE n.Estado = 'E'
            AND emdc.Id IS NULL
            AND ad.IdColegiado IS NULL
            AND (SELECT COUNT(colegiadodeudaanualcuotas.id) as cantidad
                FROM colegiadodeudaanualcuotas
                INNER JOIN colegiadodeudaanual ON(colegiadodeudaanual.Id = colegiadodeudaanualcuotas.IdColegiadoDeudaAnual)
                LEFT JOIN pagosnoregistrados ON(pagosnoregistrados.Recibo = colegiadodeudaanualcuotas.Id AND pagosnoregistrados.TipoPago='C')
                WHERE colegiadodeudaanual.IdColegiado = c.Id
                AND colegiadodeudaanualcuotas.Estado = 1 AND colegiadodeudaanualcuotas.FechaVencimiento <= ?
                AND pagosnoregistrados.IdColegiado IS NULL) > 0
        GROUP BY c.Matricula
        ORDER BY c.Matricula
        LIMIT ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$fechaLimite, $rango]);
        $filas = $stmt->fetchAll();
        $resultado['cantidad'] = count($filas);
        if ($resultado['cantidad'] > 0) {
            $datos = array();
            foreach ($filas as $row) {
                $datos[] = array(
                    'idNotificacion' => $row['IdNotificacion'],
                    'idReferencia' => $row['IdNotificacionColegiado'],
                    'idColegiado' => $row['IdColegiado'],
                    'matricula' => $row['Matricula'],
                    'sexo' => $row['Sexo'],
                    'apellido' => $row['Apellido'],
                    'nombres' => $row['Nombres'],
                    'mail' => $row['CorreoElectronico'],
                    'fechaCreacion' => $row['FechaCreacion'],
                    'fechaVencimiento' => $row['FechaVencimiento']
                );
            }
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No se encontro notificacion del colegiado";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando notificacion del colegiado";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function generarNotificacionDeudores($idNotificacion, $idNotificacionNota, $fechaVencimiento, $matricula, $fechaCortePago, $periodoDesde, $periodoHasta, $filtroDeudor, $cantidadCuotas){
    $resultado = array();
    try {
        $db = Database::getConnection();

        $continua = TRUE;
        if (!isset($idNotificacion) || $idNotificacion = 0) {
            $sql = "INSERT INTO notificacion
                (IdNotificacionNota, FechaCreacion, IdUsuario, Estado, FechaVencimiento, Matricula, CuotasAdeudadas, PeriodoDesde, PeriodoHasta)
                VALUES (?, date(now()), ?, 'A', ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idNotificacionNota, $_SESSION['user_id'], $fechaVencimiento, $matricula, $cantidadCuotas, $periodoDesde, $periodoHasta]);
            $idNotificacion = $db->lastInsertId();
        }
        if ($continua) {
            $sql = "INSERT INTO notificaciondeuda (IdNotificacion, FechaCortePago, FechaVencimiento, PeriodoDesde, PeriodoHasta, FiltroDeudores)
            VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idNotificacion, $fechaCortePago, $fechaVencimiento, $periodoDesde, $periodoHasta, $filtroDeudor]);

            if (isset($matricula)) {
                $colegiadoLogic = new colegiadoLogic();
                $resColegiado = $colegiadoLogic->obtenerIdColegiado($matricula);
                if ($resColegiado['estado']) {
                    $idColegiado = $resColegiado['idColegiado'];
                    $resGeneraDetalleNotificacion = $this->generarNotificacionDetalle($db, $idNotificacion, $idColegiado, $periodoHasta, $fechaVencimiento);
                    if ($resGeneraDetalleNotificacion['estado']) {
                        $resultado['estado'] = true;
                        $resultado['mensaje'] = "OK";
                        $resultado['clase'] = 'alert alert-success';
                        $resultado['icono'] = 'glyphicon glyphicon-ok';
                    } else {
                        $resultado['estado'] = false;
                        $resultado['mensaje'] = "(".$idColegiado.") Mat.".$matricula.". Error al generar detalle de notificacion - ".$resGeneraDetalleNotificacion['mensaje'];
                        $resultado['clase'] = 'alert alert-danger';
                        $resultado['icono'] = 'glyphicon glyphicon-remove';
                    }
                } else {
                    $resultado['estado'] = false;
                    $resultado['mensaje'] = "Error buscando IdColegiado ".$matricula;
                    $resultado['clase'] = 'alert alert-danger';
                    $resultado['icono'] = 'glyphicon glyphicon-remove';
                }
            } else {
                $resColegiado = $this->getDeudaAnualLogic()->obtenerIdColegiadoConDeuda($periodoHasta, $fechaVencimiento, $cantidadCuotas);
                if ($resColegiado['estado']) {
                    $idColegiado = $resColegiado['idColegiado'];
                    $resGeneraDetalleNotificacion = $this->generarNotificacionDetalle($db, $idNotificacion, $idColegiado, $periodoHasta, $fechaVencimiento);
                } else {
                    $resultado['estado'] = false;
                    $resultado['mensaje'] = "Error buscado colegiados con deuda";
                    $resultado['clase'] = 'alert alert-danger';
                    $resultado['icono'] = 'glyphicon glyphicon-remove';
                }
            }
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error agregando Notificacion";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
        if ($resultado['estado']) {
            $resultado['idNotificacion'] = $idNotificacion;
            $resultado['mensaje'] .= '('.$idNotificacion.')';
        }
        return $resultado;
    } catch (PDOException $e) {
        return array(
            'estado' => false,
            'mensaje' => "Error: " . $e->getMessage(),
            'clase' => 'alert alert-danger',
            'icono' => 'glyphicon glyphicon-remove'
        );
    }
}

    public function generarNotificacionDetalle($db, $idNotificacion, $idColegiado, $periodoHasta, $fechaVencimiento) {
    $resultado = array();
    try {
        $sql = 'INSERT INTO notificacioncolegiado (IdNotificacion, IdColegiado)
                VALUES (?, ?)';
        $stmt = $db->prepare($sql);
        $stmt->execute([$idNotificacion, $idColegiado]);
        $resultado['estado'] = true;
        $idNotificacionColegiado = $db->lastInsertId();

        $sql = "(SELECT 'C' AS Origen, colegiadodeudaanualcuotas.Id AS Indice, colegiadodeudaanualcuotas.FechaVencimiento,
                colegiadodeudaanualcuotas.Importe
                FROM colegiadodeudaanualcuotas
                INNER JOIN colegiadodeudaanual
                   ON(colegiadodeudaanual.Id = colegiadodeudaanualcuotas.IdColegiadoDeudaAnual)
                WHERE colegiadodeudaanual.IdColegiado = ?
                AND colegiadodeudaanual.Periodo < ?  AND colegiadodeudaanualcuotas.FechaVencimiento <= date(now())
                AND colegiadodeudaanual.Estado = 'A'
                AND colegiadodeudaanualcuotas.Estado = 1)
                UNION
                (SELECT 'P' AS Origen, planpagoscuotas.Id, planpagoscuotas.Vencimiento, planpagoscuotas.Importe
                FROM planpagoscuotas
                INNER JOIN planpagos
                   ON(planpagos.Id = planpagoscuotas.IdPlanPagos)
                WHERE planpagos.IdColegiado = ?
                AND planpagos.Estado = 'A'
                AND planpagoscuotas.IdTipoEstadoCuota = 1)
                ORDER BY Origen, Indice";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado, $periodoHasta, $idColegiado]);
        $filas = $stmt->fetchAll();

        if (count($filas) > 0) {
            foreach ($filas as $row) {
                $origen = $row['Origen'];
                $indice = $row['Indice'];
                $vencimiento = $row['FechaVencimiento'];
                $importe = $row['Importe'];

                if ($vencimiento < date('Y-m-d')) {
                    $importeActualizado = $this->getDeudaAnualLogic()->obtenerRecargoCuota($vencimiento, $fechaVencimiento, $importe);
                } else {
                    $importeActualizado = $importe;
                }
                if ($origen == 'C') {
                    $idColegiadoDeudaAnualCuota = $indice;
                    $idPlanPagosCuota = NULL;
                } else {
                    $idColegiadoDeudaAnualCuota = NULL;
                    $idPlanPagosCuota = $indice;
                }

                $sql1 = "INSERT INTO notificacioncolegiadodeuda (IdNotificacionColegiado, IdColegiadoDeudaAnualCuota, IdPlanPagosCuota, ValorActualizado)
                        VALUES (? , ?, ?, ?)";
                $stmt1 = $db->prepare($sql1);
                $stmt1->execute([$idNotificacionColegiado, $idColegiadoDeudaAnualCuota, $idPlanPagosCuota, $importeActualizado]);
            }
            if ($resultado['estado']) {
                $resultado['mensaje'] = "OK";
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            }
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error agregando notificacion del colegiado";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "(".$idNotificacion."-".$idColegiado."). Error al agregar Notificacion del Colegiado: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}
}
