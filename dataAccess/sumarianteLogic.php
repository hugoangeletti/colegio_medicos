<?php
class sumarianteLogic {

    public function obtenerSumarianteBuscar($id) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql="SELECT colegiado.Matricula, persona.Apellido, persona.Nombres "
                . "FROM sumariante "
                . "INNER JOIN colegiado ON(colegiado.Id = sumariante.IdColegiado)"
                . "INNER JOIN persona ON(persona.Id = colegiado.IdPersona)"
                . "WHERE sumariante.Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['sumarianteBuscar'] = $row['Apellido']." ".$row['Nombres']." (".$row['Matricula'].")";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = true;
            $resultado['mensaje'] = "No hay sumariante";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerSumariantePorId($id) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql="SELECT colegiado.Matricula, persona.Apellido, persona.Nombres, sumariante.Estado,
                sumariante.IdColegiado
                FROM sumariante
                INNER JOIN colegiado ON(colegiado.Id = sumariante.IdColegiado)
                INNER JOIN persona ON(persona.Id = colegiado.IdPersona)
                WHERE sumariante.Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $datos = array(
                        'idSumariante' => $id,
                        'sumarianteBuscar' => $row['Apellido']." ".$row['Nombres']." (".$row['Matricula'].")",
                        'idColegiado' => $row['IdColegiado'],
                        'estado' => $row['Estado']
                    );
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = true;
            $resultado['mensaje'] = "No hay sumariante";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerSumariantes(){
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "SELECT sumariante.Id, colegiado.Matricula, persona.Apellido, persona.Nombres, sumariante.Estado, sumariante.IdColegiado
                FROM sumariante
                INNER JOIN colegiado ON(colegiado.Id = sumariante.IdColegiado)
                INNER JOIN persona ON(persona.Id = colegiado.IdPersona)
                ORDER BY persona.Apellido, persona.Nombres";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $datos[] = array(
                    'id' => $r['Id'],
                    'apellido' => $r['Apellido'],
                    'nombres' => $r['Nombres'],
                    'matricula' => $r['Matricula'],
                    'estado' => $r['Estado'],
                    'idColegiado' => $r['IdColegiado']
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
            $resultado['mensaje'] = "No hay sumariantes";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;

}

    public function obtenerSumarianteAutocompletar(){
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "SELECT sumariante.Id, colegiado.Matricula, persona.Apellido, persona.Nombres
                FROM sumariante
                INNER JOIN colegiado ON(colegiado.Id = sumariante.IdColegiado)
                INNER JOIN persona ON(persona.Id = colegiado.IdPersona)
                ORDER BY persona.Apellido, persona.Nombres";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $datos = array();
        foreach ($rows as $r) {
            $datos[] = array(
                'id' => $r['Id'],
                'nombre' => $r['Apellido']." ".$r['Nombres']." (".$r['Matricula'].")"
            );
        }
        $resultado['estado'] = true;
        $resultado['mensaje'] = "OK";
        $resultado['datos'] = $datos;
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;

}

    public function agregarSumariante($idColegiado, $estado) {
    $result = array();
    try {
        $db = Database::getConnection();
        $sql="INSERT INTO sumariante (IdColegiado, Estado, FechaCarga, IdUsuario)
            VALUES (?, ?, now(), ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado, $estado, $_SESSION['user_id']]);
        $result['estado'] = TRUE;
        $result['mensaje'] = 'Sumariante HA SIDO AGREGADO';
    } catch (PDOException $e) {
        $result['estado'] = FALSE;
        $result['mensaje'] = 'ERROR AL AGREGAR Sumariante';
    }
    return $result;
}

    public function editarSumariante($idSumariante, $idColegiado, $estado) {
    $result = array();
    try {
        $db = Database::getConnection();
        $sql="UPDATE sumariante
                SET IdColegiado = ?, Estado = ?, IdUsuario = ?, FechaCarga = now()
                WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado, $estado, $_SESSION['user_id'], $idSumariante]);
        $result['estado'] = TRUE;
        $result['mensaje'] = 'Sumariante HA SIDO MODIFICADO';
    } catch (PDOException $e) {
        $result['estado'] = FALSE;
        $result['mensaje'] = 'ERROR AL MODIFICAR Sumariante';
    }
    return $result;
}

    public function borrarSumariante($idSumariante){
    $result = array();
    try {
        $db = Database::getConnection();
        $sql="UPDATE sumariante SET
                    Estado = 'B',
                    FechaCarga = now()
                    WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idSumariante]);
        $result['estado'] = TRUE;
        $result['mensaje'] = 'Sumariante HA SIDO BORRADO';
    } catch (PDOException $e) {
        $result['estado'] = FALSE;
        $result['mensaje'] = 'ERROR AL BORRAR Sumariante';
    }
    return $result;
}

    public function esSumariante($nombreUsuario){
    //return FALSE;
    try {
        $db = Database::getConnection();
        $sql="SELECT Id
            FROM sumariante
            WHERE NombreUsuario = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$nombreUsuario]);
        $row = $stmt->fetch();
        if ($row) {
            return $row['Id'];
        } else {
            return NULL;
        }
    } catch (PDOException $e) {
        return NULL;
    }
}
}
