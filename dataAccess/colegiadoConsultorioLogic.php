<?php
class colegiadoConsultorioLogic {

    public function obtenerTodos() {
    try {
        $db = Database::getConnection();
        $sql = "SELECT colegiadoconsultorio.*, localidad.Nombre AS NombreLocalidad FROM colegiadoconsultorio
                LEFT JOIN localidad ON(localidad.Id = colegiadoconsultorio.IdLocalidad)";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $resultado = array();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $row) {
                $estado = $row['Estado'];
                if ($estado == 'A') {
                    $estadoDetalle = 'Activo';
                } else {
                    $estadoDetalle = 'Dado de baja';
                }
                $r = array(
                    'id' => $row['Id'],
                    'calle' => $row['Calle'],
                    'lateral' => $row['Lateral'],
                    'numero' => $row['Numero'],
                    'piso' => $row['Piso'],
                    'departamento' => $row['Departamento'],
                    'telefono' => $row['Telefono'],
                    'codigoPostal' => $row['CodigoPostal'],
                    'fechaHabilitacion' => $row['FechaHabilitacion'],
                    'ultimaInspeccion' => $row['UltimaInspeccion'],
                    'estado' => $estado,
                    'estadoDetalle' => $estadoDetalle,
                    'observacion' => $row['Observacion'],
                    'fechaBaja' => $row['FechaBaja'],
                    'idLocalidad' => $row['IdLocalidad'],
                    'idColegiado' => $row['IdColegiado'],
                    'resolucion' => $row['Resolucion'],
                    'idRematriculacionColegiado' => $row['IdRematriculacionColegiado'],
                    'hash_qr' => $row['HashQR'],
                    'nombreLocalidad' => $row['NombreLocalidad']
                );
                array_push($datos, $r);
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No se encontraron consultorios";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando consultorios";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerConsultoriosPorIdColegiado($idColegiado) {
    try {
        $db = Database::getConnection();
        $sql = "SELECT colegiadoconsultorio.*, localidad.Nombre AS NombreLocalidad FROM colegiadoconsultorio
                LEFT JOIN localidad ON(localidad.Id = colegiadoconsultorio.IdLocalidad)
                WHERE colegiadoconsultorio.IdColegiado = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $rows = $stmt->fetchAll();

        $resultado = array();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $row) {
                $estado = $row['Estado'];
                if ($estado == 'A') {
                    $estadoDetalle = 'Activo';
                } else {
                    $estadoDetalle = 'Dado de baja';
                }
                $r = array(
                    'id' => $row['Id'],
                    'calle' => $row['Calle'],
                    'lateral' => $row['Lateral'],
                    'numero' => $row['Numero'],
                    'piso' => $row['Piso'],
                    'departamento' => $row['Departamento'],
                    'telefono' => $row['Telefono'],
                    'codigoPostal' => $row['CodigoPostal'],
                    'fechaHabilitacion' => $row['FechaHabilitacion'],
                    'ultimaInspeccion' => $row['UltimaInspeccion'],
                    'estado' => $estado,
                    'estadoDetalle' => $estadoDetalle,
                    'observacion' => $row['Observacion'],
                    'fechaBaja' => $row['FechaBaja'],
                    'idLocalidad' => $row['IdLocalidad'],
                    'idColegiado' => $row['IdColegiado'],
                    'resolucion' => $row['Resolucion'],
                    'idRematriculacionColegiado' => $row['IdRematriculacionColegiado'],
                    'fechaCarga' => $row['FechaCarga'],
                    'idUsuario' => $row['IdUsuario'],
                    'hash_qr' => $row['HashQR'],
                    'nombreLocalidad' => $row['NombreLocalidad']
                );
                array_push($datos, $r);
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No se encontraron consultorios";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando consultorios";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerConsultorioPorId($idConsultorio) {
    try {
        $db = Database::getConnection();
        $sql = "SELECT colegiadoconsultorio.*, localidad.Nombre AS NombreLocalidad FROM colegiadoconsultorio
                LEFT JOIN localidad ON(localidad.Id = colegiadoconsultorio.IdLocalidad)
                WHERE colegiadoconsultorio.Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idConsultorio]);
        $row = $stmt->fetch();

        $resultado = array();
        if ($row) {
            $estado = $row['Estado'];
            $estadoDetalle = 'Activo';
            if ($estado == 'B') {
                $estadoDetalle = 'Dado de baja';
            }
            $datos = array(
                    'id' => $row['Id'],
                    'calle' => $row['Calle'],
                    'lateral' => $row['Lateral'],
                    'numero' => $row['Numero'],
                    'piso' => $row['Piso'],
                    'departamento' => $row['Departamento'],
                    'telefono' => $row['Telefono'],
                    'codigoPostal' => $row['CodigoPostal'],
                    'fechaHabilitacion' => $row['FechaHabilitacion'],
                    'ultimaInspeccion' => $row['UltimaInspeccion'],
                    'estado' => $estado,
                    'estadoDetalle' => $estadoDetalle,
                    'observacion' => $row['Observacion'],
                    'fechaBaja' => $row['FechaBaja'],
                    'idLocalidad' => $row['IdLocalidad'],
                    'idColegiado' => $row['IdColegiado'],
                    'resolucion' => $row['Resolucion'],
                    'idRematriculacionColegiado' => $row['IdRematriculacionColegiado'],
                    'hash_qr' => $row['HashQR'],
                    'nombreLocalidad' => $row['NombreLocalidad']
            );

            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "No se encontro consultorio";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando consultorio";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function agregarColegiadoConsultorio($idColegiado, $calle, $numero, $lateral, $piso, $depto, $telefono, $idLocalidad, $codigoPostal, $fechaHabilitacion, $ultimaInspeccion, $observacion, $resolucion, $fechaBaja) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $db->beginTransaction();

        $sql="INSERT INTO colegiadoconsultorio
            (idColegiado, Calle, Lateral, Numero, Piso, Departamento, Telefono, idLocalidad, CodigoPostal,
            Estado, FechaHabilitacion, UltimaInspeccion, Observacion, FechaBaja, Resolucion, FechaCarga, idUsuario)
            VALUE (?, ?, ?, ?, ?, ?, ?, ?, ?, 'A', ?, ?, ?, ?, ?, date(now()), ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado, $calle, $lateral, $numero, $piso, $depto, $telefono,
                $idLocalidad, $codigoPostal, $fechaHabilitacion, $ultimaInspeccion, $observacion, $fechaBaja, $resolucion, $_SESSION['user_id']]);
        $idColegiadoConsultorio = $db->lastInsertId();
        $resultado['estado'] = TRUE;
        $resultado['idColegiadoConsultorio'] = $idColegiadoConsultorio;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';

        $db->commit();
        return $resultado;
    } catch (PDOException $e) {
        $db->rollBack();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL AGREGAR CONSULTORIO";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
        return $resultado;
    }
}

    public function eliminarColegiadoConsultorio($idConsultorio, $fechaBaja, $observacion){
    $resultado = array();
    try {
        $db = Database::getConnection();
        $db->beginTransaction();

        $sql="UPDATE colegiadoconsultorio
            SET Estado = 'B',
                FechaBaja = ?,
                IdUsuario = ?,
                FechaCarga = now(),
                Observacion = ?
            WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$fechaBaja, $_SESSION['user_id'], $observacion, $idConsultorio]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';

        $resultado['mensaje'] .= '('.$idConsultorio.')';
        $db->commit();
        return $resultado;
    } catch (PDOException $e) {
        $db->rollBack();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL ELIMINAR CONSULTORIO";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
        return $resultado;
    }
}

    public function modificarColegiadoConsultorio($idConsultorio, $calle, $numero, $lateral, $piso, $depto, $telefono, $idLocalidad, $codigoPostal, $fechaHabilitacion, $ultimaInspeccion, $observacion, $resolucion, $fechaBaja) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $db->beginTransaction();

        $sql="UPDATE colegiadoconsultorio
            SET Calle = ?, Numero = ?, Lateral = ?, Piso = ?, Departamento = ?, Telefono = ?,
            IdLocalidad = ?, CodigoPostal = ?, FechaHabilitacion = ?, UltimaInspeccion = ?, Observacion = ?,
            Resolucion = ?, FechaBaja = ?, FechaCarga = now(), IdUsuario = ?
            WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$calle, $numero, $lateral, $piso, $depto, $telefono,
                $idLocalidad, $codigoPostal, $fechaHabilitacion, $ultimaInspeccion, $observacion,
                $resolucion, $fechaBaja, $_SESSION['user_id'], $idConsultorio]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';

        $resultado['mensaje'] .= ' ('.$idConsultorio.')';
        $db->commit();
        return $resultado;
    } catch (PDOException $e) {
        $db->rollBack();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL MODIFICAR CONSULTORIO";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
        return $resultado;
    }
}

    public function guardarQrColegiadoConsultorio($idColegiadoConsultorio, $hash_qr) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql="UPDATE colegiadoconsultorio
                SET HashQR = ?
                WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$hash_qr, $idColegiadoConsultorio]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL AGREGAR CODIGO QR. ".$e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}
}
