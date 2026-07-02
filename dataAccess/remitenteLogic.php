<?php
class remitenteLogic {
    function obtenerRemitentePorId($idRemitente) {
        $resultado = array();
        try {
            $db = Database::getConnection();
            $sql = "SELECT r.Id, r.Nombre FROM remitente r WHERE r.id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idRemitente]);
            $row = $stmt->fetch();
            if ($row) {
                $datos = array(
                    'idRemitente' => $row['Id'],
                    'nombre' => $row['Nombre']
                );
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = false;
                $resultado['mensaje'] = "No hay remitente";
                $resultado['clase'] = 'alert alert-info';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando remitente: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
        return $resultado;
    }

    function obtenerRemitentes() {
        $resultado = array();
        try {
            $db = Database::getConnection();
            $sql = "SELECT Id, Nombre
                    FROM remitente
                    ORDER BY Nombre";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            if (count($rows) > 0) {
                $datos = array();
                foreach ($rows as $row) {
                    $datos[] = array(
                        'id' => $row['Id'],
                        'nombre' => $row['Nombre']
                    );
                }
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = true;
                $resultado['datos'] = NULL;
                $resultado['mensaje'] = "No hay remitentes";
                $resultado['clase'] = 'alert alert-warning';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando remitentes: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
        return $resultado;
    }

    function guardarRemitente($idRemitente, $nombre, $datosAnteriores) {
        $resultado = array();
        try {
            $db = Database::getConnection();
            $db->beginTransaction();
            if (isset($idRemitente)) {
                $sql = "UPDATE remitente
                        SET Nombre = ?
                        WHERE Id = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$nombre, $idRemitente]);
                $tipoMovimiento = 'modificacion';
            } else {
                $sql = "INSERT INTO remitente (Nombre) VALUES (?)";
                $stmt = $db->prepare($sql);
                $stmt->execute([$nombre]);
                $idRemitente = $db->lastInsertId();
                $datosAnteriores = array();
                $tipoMovimiento = 'alta';
            }
            $datos = serialize($datosAnteriores);
            $sql = "INSERT INTO log_tabla (Tabla, IdTabla, Fecha, TipoMovimiento, IdUsuario, Datos)
                    VALUES ('remitente', ?, NOW(), ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idRemitente, $tipoMovimiento, $_SESSION['user_id'], $datos]);
            $db->commit();
            $resultado['estado'] = TRUE;
            $resultado['idRemitente'] = $idRemitente;
            $resultado['mensaje'] = 'EL REMITENTE HA SIDO GUARDADO';
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } catch (PDOException $e) {
            $db->rollBack();
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error guardando remitente -> " . $e->getMessage();
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
        return $resultado;
    }

}
