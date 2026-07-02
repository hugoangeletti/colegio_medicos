<?php
class mesaEntradaAltaMatriculaLogic {

    public function realizarAltaMesaEntrada($idColegiado, $idTipoMovimiento, $distrito) {
    try {
        $db = Database::getConnection();
        $db->beginTransaction();
        $resultado = array();

        $sql = "INSERT INTO mesaentrada(TipoRemitente, IdColegiado, IdTipoMesaEntrada, FechaIngreso,
                Estado, IdUsuario, EstadoMatricular, EstadoTesoreria)
                VALUES('C', ?, 1, date(now()), 'A', ?, ?, 0)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado, $_SESSION['user_id'], $idTipoMovimiento]);
        $idMesaEntrada = $db->lastInsertId();

        switch ($idTipoMovimiento) {
            case 5:
                $idMotivo = 11;
                break;

            case 8:
                $idMotivo = 8;
                break;

            case 10:
                $idMotivo = 7;
                break;

            default:
                $idMotivo = NULL;
                break;
        }
        $sql="INSERT INTO mesaentradamovimiento
            (IdMesaEntrada, IdTipoMovimiento, Fecha, IdMotivoCancelacion, Distrito)
            VALUES (?, ?, date(now()), ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idMesaEntrada, $idTipoMovimiento, $idMotivo, $distrito]);

        $sql = "UPDATE colegiado
                SET Estado = ?
                WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idTipoMovimiento, $idColegiado]);

        $sql = "INSERT INTO colegiadomovimiento(IdColegiado, IdMovimiento, FechaDesde, DistritoCambio,
                IdUsuarioCarga, FechaCarga, Estado)
                VALUES(?, ?, date(now()), ?, ".$_SESSION['user_id'].", date(now()), 'O')";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado, $idTipoMovimiento, $distrito]);
        $idColegiadoMovimiento = $db->lastInsertId();

        $sql = "INSERT INTO colegiadomovimientomesaentrada(IdColegiadoMovimiento, IdMesaEntrada)
                VALUES(?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiadoMovimiento, $idMesaEntrada]);

        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = 'El movimiento en Mesa de Entrada se registro correctamente';
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
        $resultado['idMesaEntrada'] = $idMesaEntrada;
        $db->commit();
        return $resultado;
    } catch (PDOException $e) {
        if (isset($db) && $db->inTransaction()) $db->rollBack();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR: ".$e->getMessage().' (DEBE IR AL SISTEMA DE MESA DE ENTRADAS Y REGISTRAR EL MOVIMIENTO)';
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
        return $resultado;
    }
}

    public function noHayMesaEntradaRegistrada($idColegiado, $idTipoMovimiento){
    try {
        $db = Database::getConnection();
        $sql = "SELECT IdMesaEntrada
                FROM mesaentrada WHERE IdColegiado = ? AND EstadoMatricular = ?
                AND IdTipoMesaEntrada = 1
                AND FechaIngreso = DATE(NOW())
                AND Estado = 'A'
                LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado, $idTipoMovimiento]);

        $resultado = array();
        $r = $stmt->fetch();
        if ($r) {
            $resultado['estado'] = FALSE;
            $resultado['idMesaEntrada'] = $r['IdMesaEntrada'];
        } else {
            $resultado['estado'] = TRUE;
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['idMesaEntrada'] = 0;
    }
    return $resultado;
}
}
