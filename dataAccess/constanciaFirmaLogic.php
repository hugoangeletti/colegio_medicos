<?php
class constanciaFirmaLogic {

    public function agregarConstanciaFirma($idColegiado, $importe, $nombreArchivo) {
    try {
        $db = Database::getConnection();
        $db->beginTransaction();
        $sql = "INSERT INTO constanciafirma
                (Fecha, IdUsuario, Importe, Estado, IdColegiado, NombreArchivo)
                VALUES (date(now()), ?, ?, 'A', ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$_SESSION['user_id'], $importe, $idColegiado, $nombreArchivo]);
        $resultado['estado'] = TRUE;
        $resultado['idConstanciaFirma'] = $db->lastInsertId();
        $resultado['mensaje'] = "SE REGISTRO LA CONSTANCIA DE FIRMA CORRECTAMENTE";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
        $db->commit();
        return $resultado;
    } catch (PDOException $e) {
        if (isset($db) && $db->inTransaction()) $db->rollback();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL REGISTRAR CONSTANCIA DE FIRMA: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
        return $resultado;
    }
}

    public function obtenerCertificacionFirmaPorFecha($fecha) {
    try {
        $db = Database::getConnection();
        $sql = "SELECT cf.Id, cf.Fecha, cf.IdUsuario, cf.Importe, cf.Estado, cf.IdColegiado, cf.NombreArchivo, c.Matricula, p.Apellido, p.Nombres, cdm.Tipo, cdm.Numero
		FROM constanciafirma cf
		INNER JOIN colegiado c ON c.Id = cf.IdColegiado
		INNER JOIN persona p ON p.Id = c.IdPersona
        LEFT JOIN cajadiariamovimiento cdm ON cdm.Id = cf.IdCajaDiariaMovimiento
		WHERE cf.Fecha = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$fecha]);
        $rows = $stmt->fetchAll();
        $resultado = array();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $row = array (
                    'idCertificacionFirma' => $r['Id'],
                    'fecha' => $r['Fecha'],
                    'idUsuario' => $r['IdUsuario'],
                    'importe' => $r['Importe'],
                    'estado' => $r['Estado'],
                    'idColegiado' => $r['IdColegiado'],
                    'nombreArchivo' => $r['NombreArchivo'],
                    'matricula' => $r['Matricula'],
                    'apellido' => $r['Apellido'],
                    'nombre' => $r['Nombres'],
                    'tipoComprobante' => $r['Tipo'],
                    'numeroComprobante' => $r['Numero']
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
            $resultado['mensaje'] = "No existen certificaciones de firma.";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando certificaciones de firma: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerCertificacionFirmaPorId($idConstanciaFirma) {
    try {
        $db = Database::getConnection();
        $sql = "SELECT cf.Id, cf.Fecha, cf.IdUsuario, cf.Importe, cf.Estado, cf.IdColegiado, cf.NombreArchivo, c.Matricula, p.Apellido, p.Nombres
        FROM constanciafirma cf
        INNER JOIN colegiado c ON c.Id = cf.IdColegiado
        INNER JOIN persona p ON p.Id = c.IdPersona
        WHERE cf.Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idConstanciaFirma]);
        $resultado = array();
        $r = $stmt->fetch();
        if ($r) {
            $datos = array (
                'idCertificacionFirma' => $r['Id'],
                'fecha' => $r['Fecha'],
                'idUsuario' => $r['IdUsuario'],
                'importe' => $r['Importe'],
                'estado' => $r['Estado'],
                'idColegiado' => $r['IdColegiado'],
                'nombreArchivo' => $r['NombreArchivo'],
                'matricula' => $r['Matricula'],
                'apellido' => $r['Apellido'],
                'nombre' => $r['Nombres']
            );
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No existen certificaciones de firma.";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando certificaciones de firma: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function anularConstanciaFirmaPorId($id) {
    try {
        $db = Database::getConnection();
        $sql1 = "UPDATE constanciafirma
                SET Estado = 'A'
                WHERE Id = ?";
        $stmt1 = $db->prepare($sql1);
        $stmt1->execute([$id]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL ANULAR RECIBO: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function agregarArchivoEnConstanciaFirma($idConstanciaFirma, $nombreArchivo) {
    try {
        $db = Database::getConnection();
        $sql1 = "UPDATE constanciafirma
                SET NombreArchivo = ?
                WHERE Id = ?";
        $stmt1 = $db->prepare($sql1);
        $stmt1->execute([$nombreArchivo, $idConstanciaFirma]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL GUARDAR ARCHIVO: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}
}
