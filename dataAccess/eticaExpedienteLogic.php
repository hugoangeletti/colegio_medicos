<?php
class eticaExpedienteLogic {

    public function obtenerExpedientePorEstado($estado){
    try {
        $db = Database::getConnection();
        $sql="SELECT ee.Id, ee.IdColegiado, ee.NumeroExpediente, ee.Caratula, ee.Observaciones, ee.IdEticaEstado, ee.IdUsuario, ee.Fecha, c.Matricula, p.Apellido, p.Nombres, es.Nombre, ee.Denunciante, ee.FechaReunionConsejo, (SELECT GROUP_CONCAT(c1.Matricula, '-', p1.Apellido, ' ', p1.Nombres, ' ') FROM eticaexpedientedenunciados eed
                LEFT JOIN colegiado c1 ON c1.Id = eed.IdColegiado
                LEFT JOIN persona p1 ON p1.Id = c1.IdPersona
                WHERE eed.IdEticaExpediente = ee.Id AND eed.Borrado = 0) AS OtrosDenunciados
             FROM eticaexpediente ee
             INNER JOIN colegiado c ON c.Id = ee.IdColegiado
             INNER JOIN persona p ON p.Id = c.IdPersona
             INNER JOIN eticaestado es ON es.Id = ee.IdEticaEstado
             where ee.Estado = ? AND Borrado = 0";
        $stmt = $db->prepare($sql);
        $stmt->execute([$estado]);
        $rows = $stmt->fetchAll();
        $resultado = array();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $row = array (
                    'idEticaExpediente' => $r['Id'],
                    'idColegiado' => $r['IdColegiado'],
                    'nroExpediente' => $r['NumeroExpediente'],
                    'caratula' => $r['Caratula'],
                    'observaciones' => $r['Observaciones'],
                    'idEticaEstado' => $r['IdEticaEstado'],
                    'idUsuario' => $r['IdUsuario'],
                    'fecha' => $r['Fecha'],
                    'matricula' => $r['Matricula'],
                    'apellido' => $r['Apellido'],
                    'nombres' => $r['Nombres'],
                    'eticaEstado' => $r['Nombre'],
                    'denunciante' => $r['Denunciante'],
                    'fechaReunionConsejo' => $r['FechaReunionConsejo'],
                    'otrosDenunciados' => $r['OtrosDenunciados']
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
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No hay expedientes";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando expedientes: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerExpedientePorEstadoUsuario($estado, $idUsuario){
    try {
        $db = Database::getConnection();
        $sql="SELECT ee.Id, ee.IdColegiado, ee.NumeroExpediente, ee.Caratula, ee.Observaciones, ee.IdEticaEstado, ee.IdUsuario, ee.Fecha, c.Matricula, p.Apellido, p.Nombres, es.Nombre, ee.Denunciante, ee.FechaReunionConsejo, (SELECT GROUP_CONCAT(c1.Matricula, '-', p1.Apellido, ' ', p1.Nombres, ' ') FROM eticaexpedientedenunciados eed
                LEFT JOIN colegiado c1 ON c1.Id = eed.IdColegiado
                LEFT JOIN persona p1 ON p1.Id = c1.IdPersona
                WHERE eed.IdEticaExpediente = ee.Id AND eed.Borrado = 0) AS OtrosDenunciados
             FROM eticaexpediente ee
             INNER JOIN colegiado c ON c.Id = ee.IdColegiado
             INNER JOIN persona p ON p.Id = c.IdPersona
             INNER JOIN eticaestado es ON es.Id = ee.IdEticaEstado
             WHERE e.Estado = ? AND ee.IdSumarianteTitular = ? AND Borrado = 0";
        $stmt = $db->prepare($sql);
        $stmt->execute([$estado, $idUsuario]);
        $rows = $stmt->fetchAll();
        $resultado = array();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $row = array (
                    'idEticaExpediente' => $r['Id'],
                    'idColegiado' => $r['IdColegiado'],
                    'nroExpediente' => $r['NumeroExpediente'],
                    'caratula' => $r['Caratula'],
                    'observaciones' => $r['Observaciones'],
                    'idEticaEstado' => $r['IdEticaEstado'],
                    'idUsuario' => $r['IdUsuario'],
                    'fecha' => $r['Fecha'],
                    'matricula' => $r['Matricula'],
                    'apellido' => $r['Apellido'],
                    'nombres' => $r['Nombres'],
                    'eticaEstado' => $r['Nombre'],
                    'denunciante' => $r['Denunciante'],
                    'fechaReunionConsejo' => $r['FechaReunionConsejo'],
                    'otrosDenunciados' => $r['OtrosDenunciados']
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
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No hay expedientes";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando expedientes: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}


    public function obtenerEticaExpedientePorId($id){
    try {
        $db = Database::getConnection();
        $sql="select eticaexpediente.Id, eticaexpediente.IdColegiado, eticaexpediente.NumeroExpediente, eticaexpediente.Caratula,
                eticaexpediente.Observaciones, eticaexpediente.IdEticaEstado, eticaexpediente.IdUsuario, eticaexpediente.Fecha,
                colegiado.Matricula, persona.Apellido, persona.Nombres, eticaexpediente.IdSumarianteTitular,
                eticaexpediente.IdSumarianteSuplente, eticaexpediente.IdSecretarioadhoc, eticaexpediente.denunciante,
                eticaexpediente.FechaReunionConsejo, eticaexpediente.Estado
             from eticaexpediente
             inner join colegiado on(colegiado.Id = eticaexpediente.IdColegiado)
             inner join persona on(persona.Id = colegiado.IdPersona)
             where eticaexpediente.Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        $resultado = array();
        $r = $stmt->fetch();
        if ($r) {
            $datos = array(
                        'idEticaExpediente' => $r['Id'],
                        'idColegiado' => $r['IdColegiado'],
                        'nroExpediente' => $r['NumeroExpediente'],
                        'caratula' => $r['Caratula'],
                        'observaciones' => $r['Observaciones'],
                        'idEticaEstado' => $r['IdEticaEstado'],
                        'idUsuario' => $r['IdUsuario'],
                        'fecha' => $r['Fecha'],
                        'matricula' => $r['Matricula'],
                        'apellido' => $r['Apellido'],
                        'nombres' => $r['Nombres'],
                        'idSumarianteTitular' => $r['IdSumarianteTitular'],
                        'idSumarianteSuplente' => $r['IdSumarianteSuplente'],
                        'idSecretarioadhoc' => $r['IdSecretarioadhoc'],
                        'denunciante' => $r['denunciante'],
                        'fechaReunionConsejo' => $r['FechaReunionConsejo'],
                        'estadoExpediente' => $r['Estado']
                    );
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No hay expedientes";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando expedientes: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function agregarEticaExpediente($idColegiado, $caratula, $nroExpediente, $observaciones, $idSumarianteTitular, $idSumarianteSuplente, $estado, $idSecretarioadhoc, $denunciante, $fechaReunionConsejo) {
    try {
        $db = Database::getConnection();
        $sql="INSERT INTO eticaexpediente
            (IdColegiado, Denunciante, NumeroExpediente, FechaReunionConsejo, Caratula, Observaciones, IdUsuario, Fecha,
            IdSumarianteTitular, IdSumarianteSuplente, Estado, IdSecretarioadhoc)
            VALUES (?, ?, ?, ?, ?, ?, ?, now(), ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado, $denunciante, $nroExpediente, $fechaReunionConsejo, $caratula,
                $observaciones, $_SESSION['user_id'], $idSumarianteTitular, $idSumarianteSuplente, $estado,
                $idSecretarioadhoc]);
        $idEticaExpediente = $db->lastInsertId();
        //agrego el movimiento para hacer el seguimiento
        $sql="INSERT INTO eticaexpedientemovimiento
            (IdEticaExpediente, IdEticaEstado, Fecha, IdUsuario)
            VALUES (?, 1, now(), ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idEticaExpediente, $_SESSION['user_id']]);
        $result['estado'] = TRUE;
        $result['mensaje'] = 'Expediente HA SIDO AGREGADO';
    } catch (PDOException $e) {
        $result['estado'] = FALSE;
        $result['mensaje'] = 'ERROR AL AGREGAR Expediente: ' . $e->getMessage();
    }
    return $result;
}

    public function editarEticaExpediente($idEticaExpediente, $idColegiado, $caratula, $nroExpediente, $observaciones, $idSumarianteTitular, $idSumarianteSuplente, $estado, $idSecretarioadhoc, $denunciante, $fechaReunionConsejo) {
    try {
        $db = Database::getConnection();
        $sql="UPDATE eticaexpediente
                SET IdColegiado = ?,
                    NumeroExpediente = ?,
                    Caratula = ?,
                    Observaciones = ?,
                    IdUsuario = ?,
                    Fecha = now(),
                    IdSumarianteTitular = ?,
                    IdSumarianteSuplente = ?,
                    Estado = ?,
                    IdSecretarioadhoc = ?,
                    Denunciante = ?,
                    FechaReunionConsejo = ?
                WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado, $nroExpediente, $caratula, $observaciones, $_SESSION['user_id'], $idSumarianteTitular, $idSumarianteSuplente, $estado, $idSecretarioadhoc, $denunciante, $fechaReunionConsejo, $idEticaExpediente]);
        $result['estado'] = TRUE;
        $result['mensaje'] = 'Expediente HA SIDO MODIFICADO';
    } catch (PDOException $e) {
        $result['estado'] = FALSE;
        $result['mensaje'] = 'ERROR AL MODIFICAR Expediente: ' . $e->getMessage();
    }
    return $result;
}

    public function borrarEticaExpediente($idEticaExpediente){
    try {
        $db = Database::getConnection();
        $sql="UPDATE eticaexpediente SET
                    Estado = 'B'
                    WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idEticaExpediente]);
        $result['estado'] = TRUE;
        $result['mensaje'] = 'Expediente HA SIDO BORRADO';
    } catch (PDOException $e) {
        $result['estado'] = FALSE;
        $result['mensaje'] = 'ERROR AL BORRAR Expediente: ' . $e->getMessage();
    }
    return $result;
}

    public function obtenerExpedientePorSumarianteTipo($idSumariante, $tipoSumariante){
    try {
        $db = Database::getConnection();
        $filtro = "";
        if ($tipoSumariante == "T") {
            $filtro = "where eticaexpediente.IdSumarianteTitular = ".$idSumariante;
        } elseif ($tipoSumariante == "S") {
            $filtro = "where eticaexpediente.IdSumarianteSuplente = ".$idSumariante;
        }
        $sql="select eticaexpediente.Id, eticaexpediente.IdColegiado, eticaexpediente.NumeroExpediente, eticaexpediente.Caratula,
                eticaexpediente.Observaciones, eticaexpediente.IdEticaEstado, eticaexpediente.IdUsuario, eticaexpediente.Fecha,
                colegiado.Matricula, persona.Apellido, persona.Nombres, eticaestado.Nombre, eticaexpediente.Denunciante,
                eticaexpediente.FechaReunionConsejo
             from eticaexpediente
             inner join colegiado on(colegiado.Id = eticaexpediente.IdColegiado)
             inner join persona on(persona.Id = colegiado.IdPersona)
             inner join eticaestado on(eticaestado.Id = eticaexpediente.IdEticaEstado) ".$filtro;
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $resultado = array();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $row = array (
                    'idEticaExpediente' => $r['Id'],
                    'idColegiado' => $r['IdColegiado'],
                    'nroExpediente' => $r['NumeroExpediente'],
                    'caratula' => $r['Caratula'],
                    'observaciones' => $r['Observaciones'],
                    'idEticaEstado' => $r['IdEticaEstado'],
                    'idUsuario' => $r['IdUsuario'],
                    'fecha' => $r['Fecha'],
                    'matricula' => $r['Matricula'],
                    'apellido' => $r['Apellido'],
                    'nombres' => $r['Nombres'],
                    'eticaEstado' => $r['Nombre'],
                    'denunciante' => $r['Denunciante'],
                    'fechaReunionConsejo' => $r['FechaReunionConsejo']
                );
                array_push($datos, $row);
            }
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = true;
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No hay expedientes";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando expedientes: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerOtrosDenunciadosPorIdEticaExpediente($idEticaExpediente){
    try {
        $db = Database::getConnection();
        $sql="SELECT eed.Id, eed.IdColegiado, c.Matricula, p.Apellido, p.Nombres
            FROM eticaexpedientedenunciados eed
            INNER JOIN colegiado c ON c.Id = eed.IdColegiado
            INNER JOIN persona p ON p.Id = c.IdPersona
            WHERE eed.IdEticaExpediente = ? AND eed.Borrado = 0";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idEticaExpediente]);
        $rows = $stmt->fetchAll();
        $resultado = array();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $row = array (
                    'idEticaExpedienteOtroDenunciado' => $r['Id'],
                    'idColegiado' => $r['IdColegiado'],
                    'matricula' => $r['Matricula'],
                    'apellido' => $r['Apellido'],
                    'nombre' => $r['Nombres']
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
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No hay otros denunciados";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando denunciados: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function agregarOtrosDenunciados($idEticaExpediente, $idColegiado) {
    try {
        $db = Database::getConnection();
        $resultado = array();
        $sql="INSERT INTO eticaexpedientedenunciados
            (IdEticaExpediente, IdColegiado, IdUsuario, FechaCarga)
            VALUES (?, ?, ?, NOW())";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idEticaExpediente, $idColegiado, $_SESSION['user_id']]);
        $resultado['estado'] = true;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error agregando denunciado: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function borrarOtrosDenunciados($idEticaExpedienteOtroDenunciado) {
    try {
        $db = Database::getConnection();
        $resultado = array();
        $sql="UPDATE eticaexpedientedenunciados
            SET Borrado = 1,
                IdUsuario = ?,
                FechaCarga = NOW()
            WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$_SESSION['user_id'], $idEticaExpedienteOtroDenunciado]);
        $resultado['estado'] = true;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error borrando denunciados: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerOtroDenunciadoPorId($idEticaExpedienteOtroDenunciado) {
    try {
        $db = Database::getConnection();
        $sql="SELECT *
             FROM eticaexpedientedenunciados
             where Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idEticaExpedienteOtroDenunciado]);
        $resultado = array();
        $r = $stmt->fetch();
        if ($r) {
            $datos = array(
                        'idEticaExpedienteOtroDenunciado' => $r['Id'],
                        'idEticaExpediente' => $r['IdEticaExpediente'],
                        'idColegiado' => $r['IdColegiado']
                    );
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = true;
            $resultado['mensaje'] = "No hay expedientes";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando expedientes: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerExpedientePorIdColegiado($idColegiado){
    try {
        $db = Database::getConnection();
        $sql="SELECT 'DENUNCIADO' AS RolDenuncia, ee.Id, ee.IdColegiado, ee.NumeroExpediente, ee.Caratula, ee.Observaciones, ee.IdEticaEstado, ee.IdUsuario, ee.Fecha, c.Matricula, p.Apellido, p.Nombres, es.Nombre, ee.Denunciante, ee.FechaReunionConsejo, (SELECT GROUP_CONCAT(c1.Matricula, '-', p1.Apellido, ' ', p1.Nombres, ' ') FROM eticaexpedientedenunciados eed
                LEFT JOIN colegiado c1 ON c1.Id = eed.IdColegiado
                LEFT JOIN persona p1 ON p1.Id = c1.IdPersona
                WHERE eed.IdEticaExpediente = ee.Id AND eed.Borrado = 0) AS OtrosDenunciados
             FROM eticaexpediente ee
             INNER JOIN colegiado c ON c.Id = ee.IdColegiado
             INNER JOIN persona p ON p.Id = c.IdPersona
             INNER JOIN eticaestado es ON es.Id = ee.IdEticaEstado
             WHERE ee.IdColegiado = ?

             UNION ALL

            SELECT 'OTRO DENUNCIADO' AS RolDenuncia, ee.Id, ee.IdColegiado, ee.NumeroExpediente, ee.Caratula, ee.Observaciones, ee.IdEticaEstado, ee.IdUsuario, ee.Fecha, c.Matricula, p.Apellido, p.Nombres, es.Nombre, ee.Denunciante, ee.FechaReunionConsejo, (SELECT GROUP_CONCAT(c1.Matricula, '-', p1.Apellido, ' ', p1.Nombres, ' ') FROM eticaexpedientedenunciados eed
                LEFT JOIN colegiado c1 ON c1.Id = eed.IdColegiado
                LEFT JOIN persona p1 ON p1.Id = c1.IdPersona
                WHERE eed.IdEticaExpediente = ee.Id AND eed.Borrado = 0) AS OtrosDenunciados
             FROM eticaexpedientedenunciados eed
                INNER JOIN eticaexpediente ee  ON eed.IdEticaExpediente = ee.Id
             INNER JOIN colegiado c ON c.Id = ee.IdColegiado
             INNER JOIN persona p ON p.Id = c.IdPersona
             INNER JOIN eticaestado es ON es.Id = ee.IdEticaEstado
             WHERE eed.IdColegiado = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado, $idColegiado]);
        $rows = $stmt->fetchAll();
        $resultado = array();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $row = array (
                    'rolDenuncia' => $r['RolDenuncia'],
                    'idEticaExpediente' => $r['Id'],
                    'idColegiado' => $r['IdColegiado'],
                    'nroExpediente' => $r['NumeroExpediente'],
                    'caratula' => $r['Caratula'],
                    'observaciones' => $r['Observaciones'],
                    'idEticaEstado' => $r['IdEticaEstado'],
                    'idUsuario' => $r['IdUsuario'],
                    'fecha' => $r['Fecha'],
                    'matricula' => $r['Matricula'],
                    'apellido' => $r['Apellido'],
                    'nombres' => $r['Nombres'],
                    'eticaEstado' => $r['Nombre'],
                    'denunciante' => $r['Denunciante'],
                    'fechaReunionConsejo' => $r['FechaReunionConsejo'],
                    'otrosDenunciados' => $r['OtrosDenunciados']
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
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No hay expedientes";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando expedientes: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}
}
