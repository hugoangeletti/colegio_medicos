<?php
class paisLogic {

//accesos a tabla colegiado
    public function obtenerPaisPorId($id) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "SELECT * FROM paises WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        $resultado['estado'] = TRUE;
        if ($row) {
            $datos = array(
                'id' => $row['Id'],
                'nombre' => $row['Pais'],
                'codigo' => $row['Codigo'],
                'nacionalidad' => $row['Nacionalidad'],
                'estado' => $row['Estado']
            );
            $resultado['datos'] = $datos;
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No se encontró la nacionalidad " . $id;
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando nacionalidad: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerNacionalidades() {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "SELECT Id, Nacionalidad
                FROM paises
                WHERE Estado = 'A'
                ORDER BY Nacionalidad";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $datos = array();
        foreach ($rows as $row) {
            $datos[] = array(
                'id' => $row['Id'],
                'nombre' => $row['Nacionalidad']
            );
        }
        $resultado['estado'] = true;
        $resultado['mensaje'] = "OK";
        $resultado['datos'] = $datos;
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando nacionalidades: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}
}
