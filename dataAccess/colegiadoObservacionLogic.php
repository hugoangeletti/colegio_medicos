<?php
class colegiadoObservacionLogic {

    public function obtenerTiposObservacion(){
    try {
        $db = Database::getConnection();
        $sql = "SELECT * FROM tipoobservacion";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $resultado['estado'] = FALSE;
        $datos = array();
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $r = array(
                    'id' => $row['Id'],
                    'nombre' => $row['Nombre']
                );
                array_push($datos, $r);
            }
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['mensaje'] = "NO SE ENCONTRARON TIPOS DE OBSERVACION";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
        $resultado['estado'] = TRUE;
        $resultado['datos'] = $datos;
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando tipos de Observacion";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }

    return $resultado;
}

    public function obtenerColegiadoObservaciones($idColegiado){
    try {
        $db = Database::getConnection();
        $sql = "SELECT co.id, co.Observaciones, co.FechaCarga,
                co.Estado, u.Usuario, co.IdTipoObservacion, tob.Nombre
                FROM colegiadoobservacion co
                LEFT JOIN usuario u ON(u.Id = co.IdUsuario)
                INNER JOIN tipoobservacion tob ON tob.Id = co.IdTipoObservacion
                WHERE co.IdColegiado = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $rows = $stmt->fetchAll();

        $resultado['estado'] = FALSE;
        $datos = array();
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $r = array(
                    'id' => $row['id'],
                    'observaciones' => $row['Observaciones'],
                    'nombreUsuario' => $row['Usuario'],
                    'fechaCarga' => $row['FechaCarga'],
                    'estado' => $row['Estado'],
                    'idTipoObservacion' => $row['IdTipoObservacion'],
                    'tipoObservacion' => $row['Nombre']
                );
                array_push($datos, $r);
            }
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['mensaje'] = "NO SE ENCONTRARON OBSERVACIONES";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
        $resultado['estado'] = TRUE;
        $resultado['datos'] = $datos;
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando Observaciones";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }

    return $resultado;
}

    public function obtenerColegiadoObservacionPorId($idColegiadoObservacion){
    try {
        $db = Database::getConnection();
        $sql = "SELECT co.Observaciones, co.FechaCarga,
                co.Estado, u.Usuario, co.IdTipoObservacion, tob.Nombre
                FROM colegiadoobservacion co
                LEFT JOIN usuario u ON(u.Id = co.IdUsuario)
                INNER JOIN tipoobservacion tob ON tob.Id = co.IdTipoObservacion
                WHERE co.id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiadoObservacion]);
        $row = $stmt->fetch();

        $resultado['estado'] = FALSE;
        if ($row) {
            $datos = array(
                'observaciones' => $row['Observaciones'],
                'nombreUsuario' => $row['Usuario'],
                'fechaCarga' => $row['FechaCarga'],
                'estado' => $row['Estado'],
                'idTipoObservacion' => $row['IdTipoObservacion'],
                'tipoObservacion' => $row['Nombre']
            );
            $resultado['estado'] = TRUE;
            $resultado['datos'] = $datos;
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "NO SE ENCONTRO OBSERVACIONES";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando Observaciones";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }

    return $resultado;
}

    public function agregarColegiadoObservacion($idColegiado, $observaciones, $idTipoObservacion) {
    try {
        $db = Database::getConnection();
        $db->beginTransaction();
        $fechaCarga = date('Y-m-d');
        $sql = "INSERT INTO colegiadoobservacion
                (Observaciones, IdColegiado, IdUsuario, FechaCarga, Estado, IdTipoObservacion)
                VALUES (?, ?, ?, ?, 'A', ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$observaciones, $idColegiado, $_SESSION['user_id'], $fechaCarga, $idTipoObservacion]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "SE REGISTRO LA OBSERVACION CORRECTAMENTE";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';

        $db->commit();
        return $resultado;

    } catch (PDOException $e) {
        $db->rollBack();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL REGISTRAR OBSERVACION";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
        return $resultado;
    }
}

    public function agregarAdjunto($idColegiadoObservacion, $archivoSubido, $tipoArchivo, $nombreArchivo, $pathArchivo) {
    try {
        $db = Database::getConnection();
        $sql = "INSERT INTO colegiadoobservacionadjunto
                (IdColegiadoObservacion, ArchivoSubido, TipoArchivo, NombreArchivo, PathArchivo, IdUsuario, FechaCarga, Estado)
                VALUES (?, ?, ?, ?, ?, ?, now(), 'A')";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiadoObservacion, $archivoSubido, $tipoArchivo, $nombreArchivo, $pathArchivo, $_SESSION['user_id']]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "SE REGISTRO ADJUNTO CORRECTAMENTE";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL ADJUNTAR IMAGEN ".$e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function editarColegiadoObservacion($idColegiadoObservacion, $observaciones, $estado, $idTipoObservacion) {
    try {
        $db = Database::getConnection();
        $sql = "UPDATE colegiadoobservacion
                SET Observaciones = ?, Estado = ?, IdUsuario = ?, FechaCarga = now(), IdTipoObservacion = ?
                WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$observaciones, $estado, $_SESSION['user_id'], $idTipoObservacion, $idColegiadoObservacion]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "SE ACTUALIZO LA OBSERVACION CORRECTAMENTE";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL ACTUALIZAR OBSERVACION ".$e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerAdjuntoPorObservacion($idColegiadoObservacion) {
    try {
        $db = Database::getConnection();
        $sql = "SELECT *
                FROM colegiadoobservacionadjunto
                WHERE IdColegiadoObservacion = ? AND Estado = 'A'";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiadoObservacion]);
        $rows = $stmt->fetchAll();

        $resultado['estado'] = FALSE;
        $datos = array();
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $r = array(
                    'id' => $row['Id'],
                    'idColegiadoObservacion' => $row['IdColegiadoObservacion'],
                    'subido' => $row['ArchivoSubido'],
                    'tipoArchivo' => $row['TipoArchivo'],
                    'nombreArchivo' => $row['NombreArchivo'],
                    'pathArchivo' => $row['PathArchivo'],
                    'idUsuario' => $row['IdUsuario'],
                    'fechaCarga' => $row['FechaCarga'],
                    'estado' => $row['Estado']
                );
                array_push($datos, $r);
            }
            $resultado['estado'] = TRUE;
            $resultado['datos'] = $datos;
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['mensaje'] = "NO SE ENCONTRARON ADJUNTOS";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando adjuntos";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }

    return $resultado;
}

    public function eliminarAdjunto($idAdjunto) {
    try {
        $db = Database::getConnection();
        $sql = "UPDATE colegiadoobservacionadjunto
                SET Estado = 'B', IdUsuario = ?, FechaCarga = now()
                WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$_SESSION['user_id'], $idAdjunto]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "SE ELIMINO ADJUNTO CORRECTAMENTE";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL ELIMINAR IMAGEN ".$e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerAdjunto($idAdjunto) {
    try {
        $db = Database::getConnection();
        $sql = "SELECT *
                FROM colegiadoobservacionadjunto
                WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idAdjunto]);
        $row = $stmt->fetch();

        $resultado['estado'] = FALSE;
        if ($row) {
            $datos = array(
                   'id' => $row['Id'],
                    'idColegiadoObservacion' => $row['IdColegiadoObservacion'],
                    'subido' => $row['ArchivoSubido'],
                    'tipoArchivo' => $row['TipoArchivo'],
                    'nombreArchivo' => $row['NombreArchivo'],
                    'pathArchivo' => $row['PathArchivo'],
                    'idUsuario' => $row['IdUsuario'],
                    'fechaCarga' => $row['FechaCarga'],
                    'estado' => $row['Estado']
                );
            $resultado['estado'] = TRUE;
            $resultado['datos'] = $datos;
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['mensaje'] = "NO SE ENCONTRARON ADJUNTOS";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando adjuntos";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }

    return $resultado;
}
}
