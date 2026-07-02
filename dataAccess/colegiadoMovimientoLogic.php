<?php
class colegiadoMovimientoLogic {

    public function obtenerMovimientosPorIdColegiado($idColegiado) {
    try {
        $db = Database::getConnection();
        $sql="SELECT colegiadomovimiento.Id, colegiadomovimiento.IdMovimiento, colegiadomovimiento.FechaDesde,
            colegiadomovimiento.FechaHasta, colegiadomovimiento.DistritoCambio, colegiadomovimiento.DistritoOrigen,
            colegiadomovimiento.IdPatologia, tipomovimiento.DetalleCompleto, patologia.Nombre, colegiadomovimiento.FechaCarga,
            distritos.Romanos
        FROM colegiadomovimiento
        INNER JOIN tipomovimiento ON(tipomovimiento.Id = colegiadomovimiento.IdMovimiento)
        LEFT JOIN patologia ON(patologia.Id = colegiadomovimiento.IdPatologia)
        LEFT JOIN distritos ON(distritos.Id = colegiadomovimiento.DistritoCambio)
        WHERE IdColegiado = ?
        AND colegiadomovimiento.Estado = 'O'";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $datos_raw = $stmt->fetchAll();
        $resultado = array();
        if (count($datos_raw) > 0) {
            $datos = array();
            foreach ($datos_raw as $r) {
                $row = array(
                    'idColegiadoMovimiento' => $r['Id'],
                    'idTipoMovimietno' => $r['IdMovimiento'],
                    'fechaDesde' => $r['FechaDesde'],
                    'fechaHasta' => $r['FechaHasta'],
                    'distritoCambio' => $r['DistritoCambio'],
                    'distritoOrigen' => $r['DistritoOrigen'],
                    'idPatologia' => $r['IdPatologia'],
                    'detalleMovimiento' => $r['DetalleCompleto'],
                    'nombrePatologia' => $r['Nombre'],
                    'fechaCarga' => $r['FechaCarga'],
                    'romanos' => $r['Romanos']
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
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "El colegiado no tiene Movimientos Matriculares.";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando nombrePatologia";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function colegiadoTieneMovimientos($idColegiado){
    try {
        $db = Database::getConnection();
        $sql="SELECT count(Id) AS Cantidad
                FROM colegiadomovimiento
                WHERE IdColegiado = ?
                AND Estado = 'O'";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $row = $stmt->fetch();
        $resultado = FALSE;
        if ($row && $row['Cantidad'] > 0) {
            $resultado = array('estado' => TRUE);
        }
    } catch (PDOException $e) {
        $resultado = FALSE;
    }
    return $resultado;
}

    public function colegiadoTieneMovimientosOtrosDistritos($idColegiado){
    try {
        $db = Database::getConnection();
        $sql="SELECT count(Id) AS Cantidad
                FROM colegiadomovimientodistritos
                WHERE IdColegiado = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $row = $stmt->fetch();
        $resultado = FALSE;
        if ($row && $row['Cantidad'] > 0) {
            $resultado = array('estado' => TRUE);
        }
    } catch (PDOException $e) {
        $resultado = FALSE;
    }
    return $resultado;
}

    public function obtenerMovimientoPorId($idColegiadoMovimiento){
    try {
        $db = Database::getConnection();
        $sql="SELECT cm.IdColegiado, cm.FechaDesde, cm.FechaHasta, cm.DistritoCambio, cm.DistritoOrigen, cm.IdPatologia, tm.DetalleCompleto
                FROM colegiadomovimiento cm
                INNER JOIN tipomovimiento tm ON(tm.Id = cm.IdMovimiento)
                WHERE cm.Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiadoMovimiento]);
        $row = $stmt->fetch();
        $resultado = FALSE;
        if ($row) {
            $datos['idColegiado'] = $row['IdColegiado'];
            $datos['fechaDesde'] = $row['FechaDesde'];
            $datos['fechaHasta'] = $row['FechaHasta'];
            $datos['distritoCambio'] = $row['DistritoCambio'];
            $datos['distritoOrigen'] = $row['DistritoOrigen'];
            $datos['idPatologia'] = $row['IdPatologia'];
            $datos['detalleMovimiento'] = $row['DetalleCompleto'];

            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "No se encontro el Movimiento.";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando Movimiento";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerMovimientoMatricular($idColegiado, $tipoEstado){
    try {
        $db = Database::getConnection();
        $sql="SELECT Id, FechaDesde, FechaHasta, DistritoCambio, DistritoOrigen, IdPatologia
                FROM colegiadomovimiento
                WHERE IdColegiado = ?
                AND IdMovimiento = ?
                AND Estado = 'O'";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado, $tipoEstado]);
        $row = $stmt->fetch();
        $resultado = FALSE;
        if ($row) {
            $datos['id'] = $row['Id'];
            $datos['fechaDesde'] = $row['FechaDesde'];
            $datos['fechaHasta'] = $row['FechaHasta'];
            $datos['distritoCambio'] = $row['DistritoCambio'];
            $datos['distritoOrigen'] = $row['DistritoOrigen'];
            $datos['idPatologia'] = $row['IdPatologia'];

            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "No se encontro el Movimiento. (".$idColegiado." - ".$tipoEstado.")";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando Movimiento";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerUltimoMovimiento($idColegiado){
    try {
        $db = Database::getConnection();
        $sql="SELECT cm.IdMovimiento
                FROM colegiadomovimiento cm
                WHERE cm.IdColegiado = ? AND cm.Estado = 'O'
                ORDER BY cm.FechaDesde DESC
                LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $row = $stmt->fetch();
        $resultado = array();
        if ($row) {
            $resultado['idTipoMovimiento'] = $row['IdMovimiento'];
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "No se encontró Movimiento.";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando Movimiento";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerMovimientosOtrosDistritosPorIdColegiado($idColegiado) {
    try {
        $db = Database::getConnection();
        $sql="SELECT * FROM colegiadomovimientodistritos WHERE IdColegiado = ? AND (Estado <> 'B' OR ESTADO IS NULL)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $datos_raw = $stmt->fetchAll();
        $resultado = array();
        if (count($datos_raw) > 0) {
            $datos = array();
            foreach ($datos_raw as $r) {
                $row = array(
                    'idColegiadoMovimiento' => $r['Id'],
                    'idMovimietno' => $r['IdMovimiento'],
                    'fechaDesde' => $r['FechaDesde'],
                    'fechaHasta' => $r['FechaHasta'],
                    'distritoCambio' => $r['DistritoCambio'],
                    'distritoOrigen' => $r['DistritoOrigen'],
                    'idUsuario' => $r['IdUsuarioCarga'],
                    'fechaCarga' => $r['FechaCarga'],
                    'observaciones' => $r['ObservacionOtroDistrito']
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
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "El colegiado no tiene Movimientos de Otros Distritos.";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando Movimientos de Otros Distritos";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function agregarColegiadoMovimiento($idColegiado, $idTipoMovimiento, $distritoCambio, $fechaDesde, $fechaHasta){
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql="INSERT INTO colegiadomovimiento
            (IdColegiado, IdMovimiento, FechaDesde, FechaHasta, DistritoCambio, IdUsuarioCarga, FechaCarga, Estado)
            VALUE (?, ?, ?, ?, ?, ?, date(now()), 'O')";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado, $idTipoMovimiento, $fechaDesde, $fechaHasta, $distritoCambio, $_SESSION['user_id']]);
        $resultado['estado'] = TRUE;
        $resultado['idColegiadoMovimiento'] = $db->lastInsertId();
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL AGREGAR MOVIMIENTO";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function modificarColegiadoMovimiento($idColegiadoMovimiento, $idTipoMovimiento, $distritoCambio, $fechaDesde, $fechaHasta, $estado){
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql="UPDATE colegiadomovimiento
            SET IdMovimiento = ?, FechaDesde = ?, FechaHasta = ?, DistritoCambio = ?,
            IdUsuarioCarga = ?, FechaCarga = date(now()), Estado = ?
            WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idTipoMovimiento, $fechaDesde, $fechaHasta, $distritoCambio, $_SESSION['user_id'], $estado, $idColegiadoMovimiento]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "EL MOVIMIENTO DE ACTUALIZO CORRECTAMENTE";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL ACTUALIZAR MOVIMIENTO";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function agregarMovimientoOtroDistrito($idColegiado, $distritoOrigen, $distritoCambio, $fechaDesde, $fechaHasta, $observaciones){
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql="INSERT INTO colegiadomovimientodistritos
            (IdColegiado, FechaDesde, FechaHasta, DistritoCambio, DistritoOrigen, IdUsuarioCarga, FechaCarga, ObservacionOtroDistrito)
            VALUE (?, ?, ?, ?, ?, ?, date(now()), ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado, $fechaDesde, $fechaHasta, $distritoCambio, $distritoOrigen, $_SESSION['user_id'], $observaciones]);
        $resultado['estado'] = TRUE;
        $resultado['idColegiadoMovimientoOtro'] = $db->lastInsertId();
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL AGREGAR MOVIMIENTO";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function anularMovimientoOtroDistrito($idMovimiento) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $observaciones = "ANULADA POR USUARIO ".$_SESSION['user_entidad']['nombreUsuario']." el dia y hora ".date('d-m-Y H:i:s');
        $sql="UPDATE colegiadomovimientodistritos
            SET Estado = 'B',
            ObservacionOtroDistrito = CONCAT(ObservacionOtroDistrito, ' ', ?)
            WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$observaciones, $idMovimiento]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL ANULAR MOVIMIENTO";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function patologiaColegiadoMovimiento($idColegiadoMovimiento, $idPatologia) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql="UPDATE colegiadomovimiento
            SET IdPatologia = ?
            WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idPatologia, $idColegiadoMovimiento]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL MODIFICAR MOVIMIENTO";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}
}
