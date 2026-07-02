<?php
class tramiteLogic {

    public function obtenerTramites($estado) {
    try {
        $db = Database::getConnection();
        $sql="SELECT * FROM tramite WHERE Estado = ? ORDER BY Fecha DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute([$estado]);
        $resultado = array();
        $datos = array();
        $rows = $stmt->fetchAll();
        foreach ($rows as $r) {
            $row = array (
                'idTramite' => $r['Id'],
                'detalle' => $r['Detalle'],
                'fecha' => $r['Fecha'],
                'fechaDesde' => $r['FechaDesde'],
                'fechaHasta' => $r['FechaHasta'],
                'destino' => $r['Destino'],
                'idUsuario' => $r['IdUsuario'],
                'tipoTramite' => $r['TipoTramite']
            );
            array_push($datos, $row);
        }
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['datos'] = $datos;
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando Tramites: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }
    return $resultado;
}

    public function obtenerTramitePorId($idTramite) {
    try {
        $db = Database::getConnection();
        $sql="SELECT * FROM tramite WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idTramite]);
        $resultado = array();
        $datos = array();
        $r = $stmt->fetch();
        if ($r) {
            $datos = array(
                'idTramite' => $r['Id'],
                'detalle' => $r['Detalle'],
                'fecha' => $r['Fecha'],
                'fechaDesde' => $r['FechaDesde'],
                'fechaHasta' => $r['FechaHasta'],
                'destino' => $r['Destino'],
                'idUsuario' => $r['IdUsuario'],
                'tipoTramite' => $r['TipoTramite']
            );
        }
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['datos'] = $datos;
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando Tramites: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }
    return $resultado;
}

    public function obtenerTramiteDetalle($idTramite) {
    try {
        $db = Database::getConnection();
        $sql="SELECT td.Id, td.IdTipoMovimiento, td.Fecha, c.Matricula, p.Apellido, p.Nombres, tm.Detalle, td.DistritoCambio, c.Id
        FROM tramitedetalle td
        INNER JOIN colegiado c ON c.Id = td.IdColegiado
        LEFT JOIN persona p ON p.Id = c.IdPersona
        LEFT JOIN tipomovimiento tm ON tm.Id = td.IdTipoMovimiento
        LEFT JOIN tipotramite tt ON tt.Id = td.IdTipoTramite
        WHERE td.IdTramite = ?
        ORDER BY td.Fecha";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idTramite]);
        $resultado = array();
        $datos = array();
        $rows = $stmt->fetchAll();
        foreach ($rows as $r) {
            $row = array (
                'idTramiteDetalle' => $r['Id'],
                'idTipoMovimiento' => $r['IdTipoMovimiento'],
                'fecha' => $r['Fecha'],
                'matricula' => $r['Matricula'],
                'apellido' => $r['Apellido'],
                'nombre' => $r['Nombres'],
                'nombreMovimiento' => $r['Detalle'],
                'distritoCambio' => $r['DistritoCambio'],
                'idColegiado' => $r['Id']
            );
            array_push($datos, $row);
        }
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['datos'] = $datos;
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando Detalle del Tramite: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }
    return $resultado;
}

    public function agregarTramites($fechaDesde, $fechaHasta, $detalle, $tipoTramite) {
    try {
        $db = Database::getConnection();
        $db->beginTransaction();
        $resultado = array();

        $continuar = TRUE;
        echo 'tipoTramite->'.$tipoTramite.'-<br>';
        switch ($tipoTramite) {
            case 'M':
                $sql="SELECT 'ALTA' as Tipo, Id AS IdColegiado, FechaMatriculacion AS FechaDesde, NULL AS FechaHasta, 1 AS IdMovimiento, NULL AS DistritoCambio
                    FROM colegiado
                    WHERE (FechaMatriculacion >= ? AND FechaMatriculacion <= ?)
                    AND DistritoOrigen = 1

                    UNION ALL

                    SELECT 'MOVIMIENTO' as Tipo, IdColegiado, FechaDesde, FechaHasta, IdMovimiento, DistritoCambio
                    FROM colegiadomovimiento
                    WHERE ((fechacarga >= ? AND fechacarga <= ?)
                    OR (FechaCargaRehabilitacion >= ? AND FechaCargaRehabilitacion <= ?))
                    AND  estado<>'A'";
                $stmt1 = $db->prepare($sql);
                $params1 = [$fechaDesde, $fechaHasta, $fechaDesde, $fechaHasta, $fechaDesde, $fechaHasta];
                break;

            case 'A':
                $sql="SELECT 'ALTA' as Tipo, Id AS IdColegiado, FechaMatriculacion AS FechaDesde, NULL AS FechaHasta, 1 AS IdMovimiento, NULL AS DistritoCambio
                    FROM colegiado
                    WHERE FechaMatriculacion BETWEEN ? AND ?
                    AND DistritoOrigen = 1";
                $stmt1 = $db->prepare($sql);
                $params1 = [$fechaDesde, $fechaHasta];
                break;

            case 'F':
                $sql = "SELECT 'FALLECIDOS' as Tipo, cm.IdColegiado, cm.FechaDesde, cm.FechaHasta, cm.IdMovimiento, cm.DistritoCambio
                    FROM colegiadomovimiento cm
                    INNER JOIN tipomovimiento tm ON tm.Id = cm.IdMovimiento
                    WHERE ((cm.FechaCarga >= ? AND cm.FechaCarga <= ?)
                    OR (cm.FechaCargaRehabilitacion >= ? AND cm.FechaCargaRehabilitacion <= ?))
                    AND cm.Estado <> 'A'
                    AND tm.Estado = 'F'";
                $stmt1 = $db->prepare($sql);
                $params1 = [$fechaDesde, $fechaHasta, $fechaDesde, $fechaHasta];
                break;

            case 'J':
                $sql = "SELECT 'FALLECIDOS' as Tipo, cm.IdColegiado, cm.FechaDesde, cm.FechaHasta, cm.IdMovimiento, cm.DistritoCambio
                    FROM colegiadomovimiento cm
                    INNER JOIN tipomovimiento tm ON tm.Id = cm.IdMovimiento
                    WHERE ((cm.FechaCarga >= ? AND cm.FechaCarga <= ?)
                    OR (cm.FechaCargaRehabilitacion >= ? AND cm.FechaCargaRehabilitacion <= ?))
                    AND cm.Estado <> 'A'
                    AND tm.Estado = 'J'";
                $stmt1 = $db->prepare($sql);
                $params1 = [$fechaDesde, $fechaHasta, $fechaDesde, $fechaHasta];
                break;

            default:
                $continuar = FALSE;
                break;
        }

        if ($continuar) {
            $stmt1->execute($params1);
            $rows1 = $stmt1->fetchAll();

            $resultado = array();
            $idTramite = NULL;
            $continua = TRUE;
            foreach ($rows1 as $rowData) {
                if (!$continua) break;
                $tipo = $rowData['Tipo'];
                $idColegiado = $rowData['IdColegiado'];
                $fecha = $rowData['FechaDesde'];
                $fechaHastaMovimiento = $rowData['FechaHasta'];
                $idTipoMovimiento = $rowData['IdMovimiento'];
                $distritoCambio = $rowData['DistritoCambio'];

                if (!isset($idTramite)) {
                    $sql="INSERT INTO tramite
                        (Detalle, Fecha, FechaDesde, FechaHasta, Estado)
                        VALUES (?, date(now()), ?, ?, 'G')";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$detalle, $fechaDesde, $fechaHasta]);
                    $idTramite = $db->lastInsertId();
                }
                if ($continua) {
                    $cargar = TRUE;
                    if ($tipo == "MOVIMIENTO") {
                        $fechaVer = sumarRestarSobreFecha($fechaDesde, 30, 'day', '-');
                        if (isset($fechaHastaMovimiento)) {
                            if ($fechaHastaMovimiento > $fechaVer) {
                                $idTipoMovimiento = 20;
                                $fecha = $fechaHastaMovimiento;
                            } else {
                                $cargar = FALSE;
                            }
                        } else {
                            if ($fecha < $fechaVer) {
                                $cargar = FALSE;
                            }
                        }
                    }
                    if ($cargar) {
                        $sql="INSERT INTO tramitedetalle
                            (IdTramite, IdTipoTramite, IdTipoMovimiento, Fecha, DistritoCambio, IdColegiado)
                            VALUES (?, 1, ?, ?, ?, ?)";
                        $stmt = $db->prepare($sql);
                        $stmt->execute([$idTramite, $idTipoMovimiento, $fecha, $distritoCambio, $idColegiado]);
                    }
                }
            }
            if (!isset($idTramite)) {
                $resultado['estado'] = FALSE;
                $resultado['mensaje'] = "NO EXISTEN MOVIMIENTOS.";
                $resultado['clase'] = 'alert alert-danger';
                $resultado['icono'] = 'glyphicon glyphicon-remove';
            } else {
                $resultado['estado'] = TRUE;
                $resultado['mensaje'] = "OK";
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            }
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "ERROR DATOS DE ENTRADA";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
        if ($resultado['estado']) {
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = 'SE GENERO EL LISTADO CORRECTAMENTE';
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
            $resultado['idTramite'] = $idTramite;
            $db->commit();
            return $resultado;
        } else {
            $db->rollback();
            return $resultado;
        }
    } catch (PDOException $e) {
        if (isset($db) && $db->inTransaction()) $db->rollback();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
        return $resultado;
    }
}
}
