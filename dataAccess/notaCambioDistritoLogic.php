<?php
class notaCambioDistritoLogic {

    public function obtenerNotasCambioDistrito() {
    try {
        $db = Database::getConnection();
        $sql="SELECT * FROM notacambiodistrito ORDER BY Detalle";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $resultado = array();
        $resultado['estado'] = TRUE;
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $r) {
                $row = array (
                    'id' => $r['Id'],
                    'tipoNota' => $r['TipoNota'],
                    'nombre' => $r['Detalle'],
                    'nota' => $r['Nota']
                );
                array_push($datos, $row);
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No hay Notas Cambio Distrito.";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando Notas Cambio Distrito: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }
    return $resultado;
}

    public function obtenerNotaCambioDistritoPorId($id) {
    try {
        $db = Database::getConnection();
        $sql="select * from notacambiodistrito where Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        $resultado = array();
        $r = $stmt->fetch();
        if ($r) {
            $datos = array(
                'id' => $r['Id'],
                'tipoNota' => $r['TipoNota'],
                'nombre' => $r['Detalle'],
                'nota' => $r['Nota']
            );
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No se encontro Notas Cambio Distrito";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando Notas Cambio Distrito: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }
    return $resultado;
}
}
