<?php
class falsosMedicosLogic {

    public function obtenerFalsosMedicosPorEstado($estado) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql="SELECT * FROM falsosmedicos WHERE Estado = ? ORDER BY Apellido, Nombre";
        $stmt = $db->prepare($sql);
        $stmt->execute([$estado]);
        $rows = $stmt->fetchAll();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $datos[] = array(
                    'id' => $r['Id'],
                    'apellido' => $r['Apellido'],
                    'nombre' => $r['Nombre'],
                    'nroDocumento' => $r['NumeroDocumento'],
                    'matricula' => $r['Matricula'],
                    'origenMatricula' => $r['OrigenMatricula'],
                    'fechaDenuncia' => $r['FechaDenuncia'],
                    'observaciones' => $r['Observaciones'],
                    'remitido' => $r['Remitido'],
                    'estado' => $r['Estado'],
                    'fechaCarga' => $r['FechaCarga'],
                    'idUsuario' => $r['IdUsuario']
                );
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "NO HAY REGISTRO DE DENUNCIAS DE FALSOS MEDICOS";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }

    return $resultado;
}

    public function obtenerFalsosMedicosPorId($id) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql="SELECT * FROM falsosmedicos WHERE ID = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $datos = array(
                'id' => $row['Id'],
                'apellido' => $row['Apellido'],
                'nombre' => $row['Nombre'],
                'nroDocumento' => $row['NumeroDocumento'],
                'matricula' => $row['Matricula'],
                'origenMatricula' => $row['OrigenMatricula'],
                'fechaDenuncia' => $row['FechaDenuncia'],
                'observaciones' => $row['Observaciones'],
                'remitido' => $row['Remitido'],
                'estado' => $row['Estado'],
                'fechaCarga' => $row['FechaCarga'],
                'idUsuario' => $row['IdUsuario']
                );

            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No se ecntontro el banco";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }

    return $resultado;
}

    public function agregarFalsosMedicos($apellido, $nombre, $nroDocumento, $matricula, $origenMatricula, $fechaDenuncia, $observaciones, $remitido) {
    try {
        $db = Database::getConnection();
        $sql = "INSERT INTO falsosmedicos
                (Apellido, Nombre, NumeroDocumento, Matricula, OrigenMatricula, FechaDenuncia, Observaciones, Remitido, Estado, FechaCarga, IdUsuario)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'A', now(), ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$apellido, $nombre, $nroDocumento, $matricula, $origenMatricula, $fechaDenuncia, $observaciones, $remitido, $_SESSION['user_id']]);
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "SE REGISTRO FALSO MEDICO CORRECTAMENTE";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
        return $resultado;
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL REGISTRAR FALSO MEDICO";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
        return $resultado;
    }
}

    public function editarFalsosMedicos($idFalsoMedicos, $apellido, $nombre, $nroDocumento, $matricula, $origenMatricula, $fechaDenuncia, $observaciones, $remitido, $estado) {
    try {
        $db = Database::getConnection();
        $sql = "UPDATE falsosmedicos
                SET Apellido = ?,
                    Nombre = ?,
                    NumeroDocumento = ?,
                    Matricula = ?,
                    OrigenMatricula = ?,
                    FechaDenuncia = ?,
                    Observaciones = ?,
                    Remitido = ?,
                    Estado = ?,
                    FechaCarga = now(),
                    IdUsuario = ?
                WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$apellido, $nombre, $nroDocumento, $matricula, $origenMatricula,
                $fechaDenuncia, $observaciones, $remitido, $estado, $_SESSION['user_id'], $idFalsoMedicos]);
        $resultado['estado'] = TRUE;
        if ($estado == 'A') {
            $resultado['mensaje'] = "SE ACTUALIZO FALSO MEDICO CORRECTAMENTE";
        } else {
            $resultado['mensaje'] = "SE ANULO FALSO MEDICO CORRECTAMENTE";
        }
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
        return $resultado;
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL ACTUALIZAR FALSO MEDICO";
        $resultado['clase'] = 'alert alert-warning';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
        return $resultado;
    }
}
}
