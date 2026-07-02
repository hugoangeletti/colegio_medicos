<?php
class presidenteLogic {

    public function obtenerPresidenteDistrito($distrito){
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = 'SELECT Presidente, Romanos FROM distritos WHERE Distrito = ?';
        $stmt = $db->prepare($sql);
        $stmt->execute([$distrito]);
        $row = $stmt->fetch();
        $resultado['estado'] = TRUE;
        if ($row) {
            $datos = array(
                    'nombre' => $row['Presidente'],
                    'romanos' => $row['Romanos']
                    );

            $resultado['datos'] = $datos;
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No hay Presidente ".$distrito;
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
