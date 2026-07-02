<?php
class tipoResolucionLogic {

    public function obtenerTiposResoluciones() {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "SELECT Id, Detalle, TipoEspecialista
                FROM tiporesolucion
                WHERE Estado = 'A'
                ORDER BY Detalle";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $datos = array();
        foreach ($rows as $row) {
            $datos[] = array(
                'id' => $row['Id'],
                'nombre' => $row['Detalle'],
                'tipoEspecialista' => $row['TipoEspecialista']
            );
        }
        $resultado['estado'] = true;
        $resultado['mensaje'] = "OK";
        $resultado['datos'] = $datos;
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando Tipos de resoluciones: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerTipoResolucionPorId($idTipoResolucion) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "SELECT Id, Detalle, TipoEspecialista
                FROM tiporesolucion
                WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idTipoResolucion]);
        $row = $stmt->fetch();
        if ($row) {
            $datos = array(
                'id' => $row['Id'],
                'nombre' => $row['Detalle'],
                'tipoEspecialista' => $row['TipoEspecialista'],
                'idIngresado' => $idTipoResolucion
            );
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No hay Tipo de resolucion";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando Tipos de resoluciones: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }
    return $resultado;
}
}
