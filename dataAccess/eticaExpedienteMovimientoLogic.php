<?php
class eticaExpedienteMovimientoLogic {

    public function obtenerMovimientosPorIdEticaExpediente($id, $tipoUsuario){
    try {
        $db = Database::getConnection();
        $sql="SELECT eticaexpedientemovimiento.Id, eticaexpedientemovimiento.Derivado, eticaexpedientemovimiento.Fecha, eticaexpedientemovimiento.FechaMovimiento,
            eticaestado.Nombre as Estado, usuario.NombreCompleto as NombreUsuario, eticaexpedientemovimiento.Observacion,
            eticaexpedientemovimiento.IdEticaEstado
            FROM eticaexpedientemovimiento
            LEFT JOIN eticaestado ON(eticaestado.Id = eticaexpedientemovimiento.IdEticaEstado)
            INNER JOIN usuario ON(usuario.Id = eticaexpedientemovimiento.IdUsuario)
            WHERE eticaexpedientemovimiento.IdEticaExpediente = ?
            AND usuario.TipoUsuario = ? AND eticaexpedientemovimiento.Borrado = 0";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id, $tipoUsuario]);
        $rows = $stmt->fetchAll();
        $resultado = array();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $row = array (
                    'idEticaExpedienteMovimiento' => $r['Id'],
                    'derivado' => $r['Derivado'],
                    'fecha' => $r['Fecha'],
                    'fechaMovimiento' => $r['FechaMovimiento'],
                    'idEticaEstado' => $r['IdEticaEstado'],
                    'estado' => $r['Estado'],
                    'usuario' => $r['NombreUsuario'],
                    'observacion' => $r['Observacion']
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
            $resultado['mensaje'] = "No hay movimientos";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando movimientos: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerEticaExpedienteMovimientoPorId($idEticaExpedienteMovimiento) {
    try {
        $db = Database::getConnection();
        $sql="SELECT * FROM eticaexpedientemovimiento WHERE eticaexpedientemovimiento.Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idEticaExpedienteMovimiento]);
        $resultado = array();
        $r = $stmt->fetch();
        if ($r) {
            $datos = array (
                    'idEticaExpedienteMovimiento' => $r['Id'],
                    'idEticaExpediente' => $r['IdEticaExpediente'],
                    'idEticaEstado' => $r['IdEticaEstado'],
                    'derivado' => $r['Derivado'],
                    'fecha' => $r['Fecha'],
                    'fechaMovimiento' => $r['FechaMovimiento'],
                    'idUsuario' => $r['IdUsuario'],
                    'observacion' => $r['Observacion'],
                    'borrado' => $r['Borrado']
            );
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No hay movimientos";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando movimientos: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function agregarEticaExpedienteMovimiento($idEticaExpediente, $idEticaEstado, $derivado, $observacion, $fecha, $db = null) {
    try {
        if (!isset($db)) {
            $db = Database::getConnection();
        }
        $sql="INSERT INTO eticaexpedientemovimiento
                (IdEticaExpediente, IdEticaEstado, Derivado, Observacion, Fecha, FechaMovimiento, IdUsuario)
                VALUES (?, ?, ?, ?, ?, now(), ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idEticaExpediente, $idEticaEstado, $derivado, $observacion, $fecha, $_SESSION['user_id']]);
        //modifico el estadoetica en eticaexpediente
        if (isset($idEticaEstado)) {
            $sql="UPDATE eticaexpediente
                    SET IdEticaEstado = ?
                    WHERE Id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idEticaEstado, $idEticaExpediente]);
        }
        $result['estado'] = TRUE;
        $result['mensaje'] = 'Movimiento HA SIDO AGREGADO';
    } catch (PDOException $e) {
        $result['estado'] = FALSE;
        $result['mensaje'] = 'ERROR AL AGREGAR Movimiento: ' . $e->getMessage();
    }
    return $result;
}

    public function borrarEticaExpedienteMovimiento($idEticaExpedienteMovimiento){
    try {
        $db = Database::getConnection();
        $sql="UPDATE eticaexpedientemovimiento
            SET Borrado = 1,
                FechaMovimiento = NOW(),
                IdUsuario = ".$_SESSION['user_id']."
                WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idEticaExpedienteMovimiento]);
        $resultado = array();
        $sql="SELECT eem.IdEticaExpediente, ee.IdEticaEstado
            FROM eticaexpedientemovimiento eem
            INNER JOIN eticaexpediente ee ON ee.Id = eem.IdEticaExpediente
            WHERE eem.Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idEticaExpedienteMovimiento]);
        $r = $stmt->fetch();
        if ($r) {
            $datos = array (
                    'idEticaExpediente' => $r['IdEticaExpediente'],
                    'idEticaEstado' => $r['IdEticaEstado'],
            );
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = 'Expediente NO ENCONTRADO';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = 'ERROR AL BORRAR Expediente: ' . $e->getMessage();
    }
    return $resultado;
}

    public function modificaEticaExpedienteMovimiento($idEticaExpedienteMovimiento, $idEticaExpediente, $idEticaEstado, $derivado, $observacion, $fecha) {
    try {
        $db = Database::getConnection();
        $sql="UPDATE eticaexpedientemovimiento
            SET Borrado = 1
            WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idEticaExpedienteMovimiento]);
        //agregar el movimiento con los nuevos datos
        $resultado = $this->agregarEticaExpedienteMovimiento($idEticaExpediente, $idEticaEstado, $derivado, $observacion, $fecha, $db);
        if ($resultado['estado']) {
            $result['estado'] = TRUE;
            $result['mensaje'] = 'idEticaExpedienteMovimiento HA SIDO MODIFICADO';
        } else {
            $result['estado'] = FALSE;
            $result['mensaje'] = 'ERROR AL AGREGAR idEticaExpedienteMovimiento';
        }
    } catch (PDOException $e) {
        $result['estado'] = FALSE;
        $result['mensaje'] = 'ERROR AL MODIFICAR idEticaExpedienteMovimiento: ' . $e->getMessage();
    }
    return $result;
}
}
