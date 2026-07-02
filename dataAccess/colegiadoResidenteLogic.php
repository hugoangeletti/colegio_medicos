<?php
class colegiadoResidenteLogic {

    public function obtenerColegiadosResidentes($tipoFiltro) {
    try {
        $db = Database::getConnection();

        $filtro = "";
        if ($tipoFiltro == "VIGENTE") {
            $filtro = " AND cr.FechaFin >= DATE(NOW())";
        }
        if ($tipoFiltro == "NO_VIGENTE") {
            $filtro = " AND cr.FechaFin < DATE(NOW())";
        }
        $sql = "SELECT cr.Id, cr.IdColegiado, cr.FechaInicio, cr.FechaFin, cr.Opcion, cr.Adjunta, cr.Anio, cr.IdEntidad, e.Nombre, c.Matricula, p.Apellido, p.Nombres
                FROM colegiadoresidente cr
                INNER JOIN entidad e ON e.Id = cr.IdEntidad
                INNER JOIN colegiado c ON c.Id = cr.IdColegiado
                INNER JOIN persona p ON p.Id = c.IdPersona
                WHERE cr.Borrado = 0 ".$filtro;
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $resultado['estado'] = FALSE;
        $datos = array();
        if (count($rows) > 0) {
            foreach ($rows as $r) {
                $row = array(
                    'idColegiadoResidente' => $r['Id'],
                    'idColegiado' => $r['IdColegiado'],
                    'fechaInicio' => $r['FechaInicio'],
                    'fechaFin' => $r['FechaFin'],
                    'opcion' => $r['Opcion'],
                    'adjunto' => $r['Adjunta'],
                    'anio' => $r['Anio'],
                    'idEntidad' => $r['IdEntidad'],
                    'nombreEntidad' => $r['Nombre'],
                    'matricula' => $r['Matricula'],
                    'apellidoNombre' => trim($r['Apellido']).' '.trim($r['Nombres'])
                );
                array_push($datos, $row);
            }
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['mensaje'] = "NO SE ENCONTRARON RESIDENTES REGISTRADOS";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
        $resultado['estado'] = TRUE;
        $resultado['datos'] = $datos;
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando RESIDENTES REGISTRADOS";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }

    return $resultado;
}

    public function obtenerColegiadoResidentePorIdColegiado($idColegiado) {
    try {
        $db = Database::getConnection();
        $sql = "SELECT cr.Id, cr.FechaInicio, cr.FechaFin, cr.Opcion, cr.Adjunta, cr.Anio, cr.IdEntidad, e.Nombre
                FROM colegiadoresidente cr
                INNER JOIN entidad e ON e.Id = cr.IdEntidad
                WHERE cr.IdColegiado = ? AND cr.Borrado = 0 AND cr.FechaFin >= DATE(NOW())";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);

        $resultado['estado'] = FALSE;
        $datos = array();
        if ($r !== false) {
            $datos = array(
                'idColegiadoResidente' => $r['Id'],
                'fechaInicio' => $r['FechaInicio'],
                'fechaFin' => $r['FechaFin'],
                'opcion' => $r['Opcion'],
                'adjunto' => $r['Adjunta'],
                'anio' => $r['Anio'],
                'idEntidad' => $r['IdEntidad'],
                'nombreEntidad' => $r['Nombre']
            );
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['mensaje'] = "NO SE ENCONTRARON idColegiadoResidente";
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

    public function obtenerSolicitudesColegiadoResidentePorIdColegiado($idColegiado) {
    try {
        $db = Database::getConnection();
        $sql = "SELECT cr.Id, cr.FechaInicio, cr.FechaFin, cr.Opcion, cr.Adjunta, cr.Anio, cr.IdEntidad, e.Nombre, cr.Borrado
                FROM colegiadoresidente cr
                INNER JOIN entidad e ON e.Id = cr.IdEntidad
                WHERE cr.IdColegiado = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $resultado['estado'] = FALSE;
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $row = array(
                    'idColegiadoResidente' => $r['Id'],
                    'fechaInicio' => $r['FechaInicio'],
                    'fechaFin' => $r['FechaFin'],
                    'opcion' => $r['Opcion'],
                    'adjunto' => $r['Adjunta'],
                    'anio' => $r['Anio'],
                    'idEntidad' => $r['IdEntidad'],
                    'nombreEntidad' => $r['Nombre'],
                    'borrado' => $r['Borrado']
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
            $resultado['mensaje'] = "NO SE ENCONTRARON OPCIONES";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando opción de residente";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }

    return $resultado;
}

    public function obtenerColegiadoResidentePorId($idColegiadoResidente) {
    try {
        $db = Database::getConnection();
        $sql = "SELECT cr.IdColegiado, cr.FechaInicio, cr.FechaFin, cr.Opcion, cr.Adjunta, cr.Anio, cr.IdEntidad, e.Nombre
                FROM colegiadoresidente cr
                INNER JOIN entidad e ON e.Id = cr.IdEntidad
                WHERE cr.Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiadoResidente]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);

        $resultado['estado'] = FALSE;
        if ($r !== false) {
            $datos = array(
                'idColegiado' => $r['IdColegiado'],
                'fechaInicio' => $r['FechaInicio'],
                'fechaFin' => $r['FechaFin'],
                'opcion' => $r['Opcion'],
                'adjunto' => $r['Adjunta'],
                'anio' => $r['Anio'],
                'idEntidad' => $r['IdEntidad'],
                'nombreEntidad' => $r['Nombre']
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

    public function agregarColegiadoResidente($idColegiado, $opcion, $anio, $idEntidad, $adjunta, $fechaInicio, $fechaFin) {
    try {
        $db = Database::getConnection();
        $db->beginTransaction();
        $sql = "INSERT INTO colegiadoresidente
                (IdColegiado, Opcion, FechaInicio, FechaFin, Adjunta, IdUsuario, FechaCarga, Anio, IdEntidad)
                VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado, $opcion, $fechaInicio, $fechaFin, $adjunta, $_SESSION['user_id'], $anio, $idEntidad]);
        $resultado['estado'] = TRUE;
        $resultado['idColegiadoResidente'] = $db->lastInsertId();
        $resultado['mensaje'] = "SE REGISTRO LA OPCION DEL RESIDENTE";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
        $db->commit();
        return $resultado;
    } catch (PDOException $e) {
        $db->rollBack();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL REGISTRAR OPCION DEL RESIDENTE";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
        return $resultado;
    }
}

    public function editarColegiadoResidente($idColegiadoResidente, $opcion, $anio, $idEntidad, $adjunta) {
    try {
        $db = Database::getConnection();
        $sql = "UPDATE colegiadoresidente
                SET Opcion = ?, Adjunta = ?, Anio = ?, IdEntidad = ?
                WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$opcion, $adjunta, $anio, $idEntidad, $idColegiadoResidente]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "SE ACTUALIZO LA OPCION DEL RESIDENTE";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL ACTUALIZAR OPCION DEL RESIDENTE";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function anularColegiadoResidente($idColegiadoResidente) {
    try {
        $db = Database::getConnection();
        $sql = "UPDATE colegiadoresidente
                SET Borrado = 1, FechaBorrado = NOW()
                WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiadoResidente]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "SE ACTUALIZO LA OPCION DEL RESIDENTE";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL ACTUALIZAR OPCION DEL RESIDENTE";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}
}
