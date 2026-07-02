<?php
class patologiasLogic {

    public function obtenerPatologias() {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql="SELECT Id AS idPatologia, Codigo AS codigo, Nombre AS nombre FROM patologia ORDER BY Nombre";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $datos = $stmt->fetchAll();
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['datos'] = $datos;
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }

    return $resultado;
}

    public function obtenerPatologiasAutocompletar() {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql="SELECT Id, Codigo, Nombre FROM patologia ORDER BY Nombre";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $datos = array();
        foreach ($rows as $r) {
            $datos[] = array(
                'id' => $r['Id'],
                'nombre' => $r['Codigo'].' - '.trim($r['Nombre'])
            );
        }
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['datos'] = $datos;
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }

    return $resultado;
}

    public function obtenerNombrePatologia($idPatologia) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql="SELECT Id, Codigo, Nombre FROM patologia WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idPatologia]);
        $row = $stmt->fetch();
        $resultado['estado'] = TRUE;
        if ($row) {
            $datos = array(
                'id' => $row['Id'],
                'nombre' => $row['Codigo'].' - '.trim($row['Nombre'])
                );

            $resultado['datos'] = $datos;
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No se encontró la patologia ".$idPatologia;
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
}
