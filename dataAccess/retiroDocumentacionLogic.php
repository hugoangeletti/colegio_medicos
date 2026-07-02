<?php
class retiroDocumentacionLogic {

    public function obtenerRetiroDocumentacionPorEstado($estado){
    try {
        $db = Database::getConnection();
        $sql = "SELECT rd.Id, rd.FechaCarga, rd.IdTipoDocumentacionRetiro, rd.Observacion, rd.IdUsuarioCarga, rd.FechaRetiro, rd.IdUsuarioRetiro, c.Matricula, p.Apellido, p.Nombres, c.Id AS IdColegiado, tdr.Nombre, p.NumeroDocumento
                FROM retirodocumentacion rd
                Inner join colegiado c on(c.Id = rd.IdColegiado)
                inner join persona p on(p.Id = c.IdPersona)
                inner join tipodocumentacionretiro tdr on tdr.Id = rd.IdTipoDocumentacionRetiro
                WHERE rd.Estado = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$estado]);
        $rows = $stmt->fetchAll();
        $resultado = array();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $row = array (
                    'idRetiro' => $r['Id'],
                    'fechaCarga' => $r['FechaCarga'],
                    'idTipoDocumentacionRetiro' => $r['IdTipoDocumentacionRetiro'],
                    'observacion' => $r['Observacion'],
                    'idUsuarioCarga' => $r['IdUsuarioCarga'],
                    'fechaRetiro' => $r['FechaRetiro'],
                    'idUsuarioRetiro' => $r['IdUsuarioRetiro'],
                    'matricula' => $r['Matricula'],
                    'apellidoNombre' => $r['Apellido'].' '.$r['Nombres'],
                    'idColegiado' => $r['IdColegiado'],
                    'tipoDocumentacionRetiro' => $r['Nombre'],
                    'numeroDocumento' => $r['NumeroDocumento']
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
            $resultado['mensaje'] = "No hay Retiros Documentacion";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando Retiros Documentacion: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerRetiroDocumentacionPorId($id) {
    try {
        $db = Database::getConnection();
        $resultado = array();
        $sql="SELECT rd.Id, rd.FechaCarga, rd.IdTipoDocumentacionRetiro, rd.Observacion, rd.IdUsuarioCarga, rd.FechaRetiro, rd.IdUsuarioRetiro, rd.Estado, c.Matricula, p.Apellido, p.Nombres, c.Id AS IdColegiado, tdr.Nombre, p.NumeroDocumento
                FROM retirodocumentacion rd
                Inner join colegiado c on(c.Id = rd.IdColegiado)
                inner join persona p on(p.Id = c.IdPersona)
                inner join tipodocumentacionretiro tdr on tdr.Id = rd.IdTipoDocumentacionRetiro
                WHERE rd.Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        $r = $stmt->fetch();
        if ($r) {
            $datos = array(
                    'idRetiro' => $r['Id'],
                    'fechaCarga' => $r['FechaCarga'],
                    'idTipoDocumentacionRetiro' => $r['IdTipoDocumentacionRetiro'],
                    'observacion' => $r['Observacion'],
                    'idUsuarioCarga' => $r['IdUsuarioCarga'],
                    'fechaRetiro' => $r['FechaRetiro'],
                    'idUsuarioRetiro' => $r['IdUsuarioRetiro'],
                    'matricula' => $r['Matricula'],
                    'apellidoNombre' => trim($r['Apellido']).' '.trim($r['Nombres']),
                    'idColegiado' => $r['IdColegiado'],
                    'tipoDocumentacionRetiro' => $r['Nombre'],
                    'numeroDocumento' => $r['NumeroDocumento'],
                    'estadoRetiro' => $r['Estado']
                );
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No se encontro el retiro de Documentacion";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando retiro de Documentacion: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }
    return $resultado;
}

    public function obtenerTiposDocumentacion() {
    try {
        $db = Database::getConnection();
        $resultado = array();
        $sql="SELECT Id, Nombre
            FROM tipodocumentacionretiro
            ORDER BY Nombre";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $datos = array();
        foreach ($rows as $r) {
            $row = array (
                'id' => $r['Id'],
                'nombre' => $r['Nombre']
                );
            array_push($datos, $row);
        }
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['datos'] = $datos;
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando Tipo de Documentacion: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }
    return $resultado;
}

    public function obtenerTipoDocumentacionRetiroPorId($id) {
    try {
        $db = Database::getConnection();
        $resultado = array();
        $sql="select *
                FROM tipodocumentacionretiro
                WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        $r = $stmt->fetch();
        if ($r) {
            $datos = array(
                    'idTipoDocumentacionRetiro' => $r['Id'],
                    'nombre' => $r['Nombre']
                );
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No se encontro el tipo de Documentacion";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando tipo de Documentacion: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }
    return $resultado;
}

    public function obtenerTipoDocumentacionRetiro() {
    try {
        $db = Database::getConnection();
        $sql = "SELECT * FROM tipodocumentacionretiro";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $resultado = array();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $row = array (
                    'id' => $r['Id'],
                    'nombre' => $r['Nombre']
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
            $resultado['mensaje'] = "No hay Tipos de Documentacion";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando Tipos de Documentacion: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function agregarRetiroDocumentacion($idColegiado, $idTipoDocumentacionRetiro, $observacion) {
    try {
        $db = Database::getConnection();
        $sql="INSERT INTO retirodocumentacion
            (IdColegiado, IdTipoDocumentacionRetiro, Observacion, IdUsuarioCarga, FechaCarga)
            VALUES (?, ?, ?, ?, now())";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado, $idTipoDocumentacionRetiro, $observacion, $_SESSION['user_id']]);
        $idRetiroDocumentacion = $db->lastInsertId();
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['idRetiroDocumentacion'] = $idRetiroDocumentacion;
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error agregando Retiro de Documentacion: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function editarRetiroDocumentacion($idRetiroDocumentacion, $idColegiado, $idTipoDocumentacionRetiro, $observacion) {
    try {
        $db = Database::getConnection();
        $sql="UPDATE retirodocumentacion
            SET IdColegiado = ?,
                IdTipoDocumentacionRetiro = ?,
                Observacion = ?,
                IdUsuarioCarga = ?,
                FechaCarga = NOW()
            WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado, $idTipoDocumentacionRetiro, $observacion, $_SESSION['user_id'], $idRetiroDocumentacion]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error modificando Retiro de Documentacion: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function borrarRetiroDocumentacion($idRetiroDocumentacion, $estado) {
    try {
        $db = Database::getConnection();
        if ($estado == "B") {
            $idUsuarioBorrado = NULL;
            $fechaBorrado = NULL;
        } else {
            $idUsuarioBorrado = $_SESSION['user_id'];
            $fechaBorrado = date('Y-m-d H:i:s');
        }
        $sql="UPDATE retirodocumentacion
            SET Estado = ?,
                IdUsuarioBorrado = ?,
                FechaBorrado = ?,
                IdUsuarioRetiro = NULL,
                FechaRetiro = NULL
            WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$estado, $idUsuarioBorrado, $fechaBorrado, $idRetiroDocumentacion]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error borrando Retiro de Documentacion: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function marcarEntregaRetiroDocumentacion($idRetiroDocumentacion, $estado) {
    try {
        $db = Database::getConnection();
        if ($estado == "E") {
            $idUsuarioRetiro = $_SESSION['user_id'];
            $fechaRetiro = date('Y-m-d H:i:s');
        } else {
            $idUsuarioRetiro = NULL;
            $fechaRetiro = NULL;
        }
        $sql="UPDATE retirodocumentacion
            SET Estado = ?,
                IdUsuarioRetiro = ?,
                FechaRetiro = ?,
                IdUsuarioBorrado = NULL,
                FechaBorrado = NULL
            WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$estado, $idUsuarioRetiro, $fechaRetiro, $idRetiroDocumentacion]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error marcando la entrega Retiro de Documentacion: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function agregarTipoDocumentacion($nombre) {
    try {
        $db = Database::getConnection();
        $sql="INSERT INTO tipodocumentacionretiro
            (Nombre) VALUES (?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$nombre]);
        $idTipoDocumentacionRetiro = $db->lastInsertId();
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['idTipoDocumentacionRetiro'] = $idTipoDocumentacionRetiro;
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error agregando Tipo de Documentacion: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function editarTipoDocumentacion($idTipoDocumentacionRetiro, $nombre) {
    try {
        $db = Database::getConnection();
        $sql="UPDATE tipodocumentacionretiro
            SET Nombre = ?
            WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$nombre, $idTipoDocumentacionRetiro]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error modificando Tipo de Documentacion: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}
}
