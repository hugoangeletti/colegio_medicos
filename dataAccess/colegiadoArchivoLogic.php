<?php
class colegiadoArchivoLogic {

    public function obtenerColegiadoArchivo($idColegiado, $idTipo) {
        try {
            $db = Database::getConnection();
            $sql="SELECT Carpeta, Nombre
                FROM colegiadoarchivo
                WHERE IdColegiado = ?
                AND TipoArchivo = ?
                AND IdEstado = 1
                ORDER BY FechaCarga DESC
                LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idColegiado, $idTipo]);
            $row = $stmt->fetch();

            $resultado = array();
            if ($row) {
                $resultado['estado'] = TRUE;
                $datos = array(
                        'carpeta' => $row['Carpeta'],
                        'nombre' => $row['Nombre']
                        );
                $resultado['datos'] = $datos;
                $resultado['mensaje'] = "OK";
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = FALSE;
                $resultado['datos'] = NULL;
                $resultado['mensaje'] = "No hay colegiado ".$idColegiado;
                $resultado['clase'] = 'alert alert-info';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando colegiado";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        return $resultado;
    }

    public function agregarArchivo($idColegiado, $tipoArchivo, $nombreArchivo, $idEstado, $idRematriculacionColegiado, $idOrigen) {
        $resultado = array();
        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            $sql="UPDATE colegiadoarchivo
                SET IdEstado = 2, FechaCarga = date(NOW()), IdUsuario = ?
                WHERE IdColegiado = ? AND TipoArchivo = ? AND IdEstado = 1";
            $stmt = $db->prepare($sql);
            $stmt->execute([$_SESSION['user_id'], $idColegiado, $tipoArchivo]);

            $sql="INSERT INTO colegiadoarchivo
                (IdColegiado, TipoArchivo, Nombre, IdEstado, IdRematriculacionColegiado, IdOrigen,
                FechaCarga, IdUsuario)
                VALUE (?, ?, ?, ?, ?, ?, date(now()), ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idColegiado, $tipoArchivo, $nombreArchivo, $idEstado, $idRematriculacionColegiado, $idOrigen, $_SESSION['user_id']]);
            $resultado['estado'] = TRUE;
            $resultado['idColegiadoArchivo'] = $db->lastInsertId();
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';

            $db->commit();
            return $resultado;
        } catch (PDOException $e) {
            $db->rollBack();
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "ERROR AL GENERAR COLEGIADO ARCHIVO";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
            return $resultado;
        }
    }
}
