<?php
class zonaLogic {

//accesos a tabla colegiado
    public function obtenerZonaPorId($id) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "SELECT * FROM zonas WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        $resultado['estado'] = TRUE;
        if ($row) {
            $datos = array(
                'id' => $row['Id'],
                'codigo' => $row['Zona'],
                'nombre' => $row['Nombre']
            );
            $resultado['datos'] = $datos;
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No se encontró Zona " . $id;
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando Zona: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerZonas() {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "SELECT * FROM zonas ORDER BY Nombre";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $datos = array();
        foreach ($rows as $row) {
            $datos[] = array(
                'id' => $row['Id'],
                'codigo' => $row['Zona'],
                'nombre' => $row['Nombre']
            );
        }
        $resultado['estado'] = true;
        $resultado['mensaje'] = "OK";
        $resultado['datos'] = $datos;
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando Zonas: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerZonasPorIdElecciones($idEleccion) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "SELECT zonas.*
                FROM zonas
                INNER JOIN eleccioneslocalidad ON(eleccioneslocalidad.Localidad = zonas.Zona)
                WHERE eleccioneslocalidad.IdElecciones = ?
                ORDER BY zonas.Nombre";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idEleccion]);
        $rows = $stmt->fetchAll();
        $datos = array();
        foreach ($rows as $row) {
            $datos[] = array(
                'id' => $row['Id'],
                'codigo' => $row['Zona'],
                'nombre' => $row['Nombre']
            );
        }
        $resultado['estado'] = true;
        $resultado['mensaje'] = "OK";
        $resultado['datos'] = $datos;
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando Zonas: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}
}
