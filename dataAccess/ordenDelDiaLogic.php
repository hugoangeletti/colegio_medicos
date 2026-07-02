<?php
class ordenDelDiaLogic {

    public function obtenerOrdenDelDia($anio, $estadoOrdenDia) {
    try {
        $db = Database::getConnection();
        $sql = "SELECT *, (SELECT COUNT(*) AS cnt FROM ordendeldiadetalle oddd WHERE oddd.IdOrdenDia = odd.Id AND oddd.Estado = 'A') AS Cantidad
            FROM ordendeldia odd
            WHERE Estado = ? AND SUBSTR(Fecha, 1, 4) = ?
            ORDER BY Fecha DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute([$estadoOrdenDia, $anio]);
        $filas = $stmt->fetchAll();
        $resultado = array();
        if (count($filas) > 0) {
            $datos = array();
            foreach ($filas as $row) {
                $datos[] = array(
                    'id' => $row['Id'],
                    'fecha' => $row['Fecha'],
                    'periodo' => $row['Periodo'],
                    'numero' => $row['Numero'],
                    'fechaCarga' => $row['FechaCarga'],
                    'idUsuario' => $row['IdUsuario'],
                    'estado' => $row['Estado'],
                    'fechaDesde' => $row['FechaDesde'],
                    'fechaHasta' => $row['FechaHasta'],
                    'observaciones' => $row['Observaciones'],
                    'cantidadDetalle' => $row['Cantidad']
                );
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No existen Orden Del Dia.";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando Orden Del Dia";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerOrdenDelDiaPorId($idOrdenDia){
    try {
        $db = Database::getConnection();
        $sql = "SELECT *
            FROM ordendeldia
            WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idOrdenDia]);
        $row = $stmt->fetch();
        $resultado = array();
        if ($row) {
            $datos = array(
                'id' => $row['Id'],
                'fecha' => $row['Fecha'],
                'periodo' => $row['Periodo'],
                'numero' => $row['Numero'],
                'fechaCarga' => $row['FechaCarga'],
                'idUsuario' => $row['IdUsuario'],
                'estado' => $row['Estado'],
                'fechaDesde' => $row['FechaDesde'],
                'fechaHasta' => $row['FechaHasta'],
                'observaciones' => $row['Observaciones']
            );
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No existe Orden Del Dia.";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando Orden Del Dia";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function ordenDelDiaDetallePorIdOrdenDia($idOrdenDia, $tipoPlanilla) {
    try {
        $db = Database::getConnection();
        $sql = "SELECT DISTINCT(me.IdMesaEntrada), oddd.Id, me.IdTipoMesaEntrada, me.FechaIngreso, c.Matricula,
            p.Apellido, p.Nombres, r.Nombre as NombreRemitente, tm.DetalleCompleto,
            tme.Nombre as NombreMovimiento, oddd.TipoPlanilla, me.Observaciones, men.Tema, oddd.Orden
            FROM ordendeldiadetalle as oddd
            INNER JOIN mesaentrada as me ON (me.IdMesaEntrada = oddd.IdMesaEntrada)
            LEFT JOIN colegiado as c ON (c.Id = me.IdColegiado)
            LEFT JOIN persona as p ON (p.Id = c.IdPersona)
            LEFT JOIN remitente as r ON (r.id = me.IdRemitente)
            LEFT JOIN mesaentradanota as men ON (men.IdMesaEntrada = me.IdMesaEntrada)
            LEFT JOIN mesaentradamovimiento as mem ON (mem.IdMesaEntrada = me.IdMesaEntrada)
            LEFT JOIN tipomovimiento as tm ON (tm.Id = mem.IdTipoMovimiento)
            INNER JOIN tipomesaentrada as tme ON (tme.IdTipoMesaEntrada = me.IdTipoMesaEntrada)
            WHERE (oddd.Estado = 'A' OR oddd.Estado = 'P')
            AND oddd.TipoPlanilla = ?
            AND oddd.IdOrdenDia = ?
            ORDER BY oddd.Orden, me.IdMesaEntrada";
        $stmt = $db->prepare($sql);
        $stmt->execute([$tipoPlanilla, $idOrdenDia]);
        $filas = $stmt->fetchAll();
        $resultado = array();
        $datos = array();
        foreach ($filas as $row) {
            $datos[] = array(
                'idMesaEntrada' => $row['IdMesaEntrada'],
                'idOrdenDiaDetalle' => $row['Id'],
                'idTipoMesaEntrada' => $row['IdTipoMesaEntrada'],
                'fechaIngreso' => $row['FechaIngreso'],
                'matricula' => $row['Matricula'],
                'apellido' => $row['Apellido'],
                'nombre' => $row['Nombres'],
                'nombreRemitente' => $row['NombreRemitente'],
                'detalleCompleto' => $row['DetalleCompleto'],
                'nombreMovimiento' => $row['NombreMovimiento'],
                'tipoPlanilla' => $row['TipoPlanilla'],
                'observaciones' => $row['Observaciones'],
                'tema' => $row['Tema'],
                'orden' => $row['Orden']
            );
        }
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['datos'] = $datos;
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando detalle del Orden Del Dia";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

//se busca en mesa de entradas lo pendiente entre las fechas de entrada
    public function obtenerMovimientosParaOrdenDia($fechaDesde, $fechaHasta) {
    try {
        $db = Database::getConnection();
        $sql = "SELECT DISTINCT(me.IdMesaEntrada), me.IdTipoMesaEntrada, me.FechaIngreso, c.Matricula, p.Apellido,
            p.Nombres, r.Nombre as NombreRemitente, tme.Nombre as NombreMovimiento, me.Observaciones,
            men.Tema, tm.DetalleCompleto as DetalleCompleto
            FROM mesaentrada as me
            LEFT JOIN colegiado as c ON (c.Id = me.IdColegiado)
            LEFT JOIN persona as p ON (p.Id = c.IdPersona)
            LEFT JOIN remitente as r ON (r.id = me.IdRemitente)
            LEFT JOIN mesaentradanota as men ON (men.IdMesaEntrada = me.IdMesaEntrada)
            LEFT JOIN mesaentradamovimiento as mem ON (mem.IdMesaEntrada = me.IdMesaEntrada)
            LEFT JOIN tipomovimiento as tm ON (tm.Id = mem.IdTipoMovimiento)
            INNER JOIN tipomesaentrada as tme ON (tme.IdTipoMesaEntrada = me.IdTipoMesaEntrada)
            WHERE (me.FechaIngreso BETWEEN ? AND ?) AND (me.IdTipoMesaEntrada IN (1,3,4,7,8,9)) AND me.Estado = 'A'
            AND me.IdMesaEntrada NOT IN(SELECT oddd.IdMesaEntrada
                                        FROM ordendeldiadetalle as oddd
                                        INNER JOIN ordendeldia as odd ON (odd.Id = oddd.IdOrdenDia)
                                        WHERE oddd.Estado = 'A'
                                        AND odd.Estado IN('A', 'C'))
            ORDER BY me.IdMesaEntrada";
        $stmt = $db->prepare($sql);
        $stmt->execute([$fechaDesde, $fechaHasta]);
        $filas = $stmt->fetchAll();
        $resultado = array();
        if (count($filas) > 0) {
            $datos = array();
            foreach ($filas as $row) {
                $datos[] = array(
                    'idMesaEntrada' => $row['IdMesaEntrada'],
                    'idTipoMesaEntrada' => $row['IdTipoMesaEntrada'],
                    'fechaIngreso' => $row['FechaIngreso'],
                    'matricula' => $row['Matricula'],
                    'apellido' => $row['Apellido'],
                    'nombre' => $row['Nombres'],
                    'nombreRemitente' => $row['NombreRemitente'],
                    'nombreMovimiento' => $row['NombreMovimiento'],
                    'observaciones' => $row['Observaciones'],
                    'tema' => $row['Tema'],
                    'detalleCompleto' => $row['DetalleCompleto']
                );
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No existen items para generar Orden Del Dia.";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando items para generar Orden Del Dia";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function agregarOrdenDelDia($fecha, $periodo, $numero, $fechaDesde, $fechaHasta, $observaciones) {
    try {
        $db = Database::getConnection();
        $sql = "INSERT INTO ordendeldia
                (Fecha, Periodo, Numero, FechaCarga, IdUsuario, Estado, FechaDesde, FechaHasta, Observaciones)
                VALUES (?, ?, ?, DATE(NOW()), ?, 'A', ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$fecha, $periodo, $numero, $_SESSION['user_id'], $fechaDesde, $fechaHasta, $observaciones]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "SE REGISTRO ORDEN DEL DIA CORRECTAMENTE";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL REGISTRAR ORDEN DEL DIA " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function agregarOrdenDelDiaDetalle($idOrdenDia, $tipoPlanilla, $idMesaEntrada) {
    try {
        $db = Database::getConnection();
        $sql = "INSERT INTO ordendeldiadetalle
                (IdOrdenDia, TipoPlanilla, IdMesaEntrada)
                VALUES (?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idOrdenDia, $tipoPlanilla, $idMesaEntrada]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "SE REGISTRO DETALLE ORDEN DEL DIA CORRECTAMENTE";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL REGISTRAR ORDEN DEL DIA " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function borrarDetallePorIdOrdenDia($idOrdenDia) {
    try {
        $db = Database::getConnection();
        $sql = "DELETE FROM ordendeldiadetalle
                WHERE IdOrdenDia = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idOrdenDia]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "SE BORRO DETALLE ORDEN DEL DIA CORRECTAMENTE";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL BORRAR ORDEN DEL DIA " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function ordenDelDiaConDetalle($idOrdenDia) {
    try {
        $db = Database::getConnection();
        $sql = "SELECT COUNT(*) AS cnt
            FROM ordendeldiadetalle
            WHERE IdOrdenDia = ? AND Estado = 'A'";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idOrdenDia]);
        $row = $stmt->fetch();
        if ($row && $row['cnt'] > 0) {
            $resultado = TRUE;
        } else {
            $resultado = FALSE;
        }
    } catch (PDOException $e) {
        $resultado = FALSE;
    }
    return $resultado;
}

    public function asignarTipoPlanillaAlDetalle($idOrdenDiaDetalle, $tipoPlanilla) {
    try {
        $db = Database::getConnection();
        $sql = "UPDATE ordendeldiadetalle
        		SET TipoPlanilla = ?
                WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$tipoPlanilla, $idOrdenDiaDetalle]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "SE CAMBIO DETALLE ORDEN DEL DIA CORRECTAMENTE";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL CAMBIAR ORDEN DEL DIA " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerNumeroOrdenDelDia($periodo) {
    try {
        $db = Database::getConnection();
        $sql = "SELECT MAX(Numero) AS maxNumero
            FROM ordendeldia
            WHERE Periodo = ? AND Estado <> 'B'";
        $stmt = $db->prepare($sql);
        $stmt->execute([$periodo]);
        $row = $stmt->fetch();
        if ($row && isset($row['maxNumero']) && $row['maxNumero'] > 0) {
            $numero = $row['maxNumero'] + 1;
        } else {
            $numero = 1;
        }
    } catch (PDOException $e) {
        $numero = 1;
    }
    return $numero;
}

    public function borrarOrdenDelDia($idOrdenDia) {
    try {
        $db = Database::getConnection();
        $sql = "UPDATE ordendeldia
                SET Estado = 'B'
                WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idOrdenDia]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "SE CERRO ORDEN DEL DIA CORRECTAMENTE";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL CERRAR ORDEN DEL DIA " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function cerrarOrdenDelDia($idOrdenDia) {
    try {
        $db = Database::getConnection();
        $sql = "UPDATE ordendeldia
                SET Estado = 'C'
                WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idOrdenDia]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "SE CERRO ORDEN DEL DIA CORRECTAMENTE";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL CERRAR ORDEN DEL DIA " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}
}
