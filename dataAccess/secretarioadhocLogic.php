<?php
class secretarioadhocLogic {

    public function obtenerSecretarioadhocBuscar($id) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql="SELECT Id, Nombre FROM secretarioadhoc WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['nombre'] = $row['Nombre'];
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = true;
            $resultado['mensaje'] = "No hay secretarioadhoc";
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

    public function obtenerSecretarioadhoPorId($id) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql="SELECT * FROM secretarioadhoc WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $datos = array(
                        'idSecretarioadhoc' => $row['Id'],
                        'nombre' => $row['Nombre'],
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
            $resultado['estado'] = true;
            $resultado['mensaje'] = "No hay secretarioadhoc";
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

    public function obtenerSecretariosadhoc(){
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "SELECT * FROM secretarioadhoc ORDER BY Nombre";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $datos[] = array(
                        'id' => $r['Id'],
                        'nombre' => $r['Nombre'],
                        'estado' => $r['Estado'],
                        'fechaCarga' => $r['FechaCarga'],
                        'idUsuario' => $r['IdUsuario']
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
            $resultado['mensaje'] = "No hay secretarioadhoc";
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

    public function obtenerSecretarioadhocAutocompletar(){
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "SELECT Id, Nombre FROM secretarioadhoc WHERE Estado = 'A' ORDER BY Nombre";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $datos = array();
        foreach ($rows as $r) {
            $datos[] = array(
                'id' => $r['Id'],
                'nombre' => $r['Nombre']
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

    public function agregarSecretarioadhoc($nombre) {
    $result = array();
    try {
        $db = Database::getConnection();
        $sql="INSERT INTO secretarioadhoc (Nombre, FechaCarga, IdUsuario)
            VALUES (?, now(), ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$nombre, $_SESSION['user_id']]);
        $result['estado'] = TRUE;
        $result['mensaje'] = 'secretarioadhoc HA SIDO AGREGADO';
    } catch (PDOException $e) {
        $result['estado'] = FALSE;
        $result['mensaje'] = 'ERROR AL AGREGAR secretarioadhoc';
    }
    return $result;
}

    public function editarSecretarioadhoc($idSecretarioadhoc, $nombre, $estado) {
    $result = array();
    try {
        $db = Database::getConnection();
        $sql="UPDATE secretarioadhoc
                SET Nombre = ?,
                    Estado = ?,
                    IdUsuario = ?,
                    FechaCarga = now()
                WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$nombre, $estado, $_SESSION['user_id'], $idSecretarioadhoc]);
        $result['estado'] = TRUE;
        $result['mensaje'] = 'secretarioadhoc HA SIDO MODIFICADO';
    } catch (PDOException $e) {
        $result['estado'] = FALSE;
        $result['mensaje'] = 'ERROR AL MODIFICAR secretarioadhoc';
    }
    return $result;
}
}
