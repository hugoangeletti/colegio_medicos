<?php
class eticaEstadoLogic {

 //HUGO
    public function obtenerEticaEstado($id) {
    try {
        $db = Database::getConnection();
        $sql="SELECT * FROM eticaestado WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        $resultado = array();
        $r = $stmt->fetch();
        if ($r) {
            $datos = array(
                'id' => $r['Id'],
                'nombre' => $r['Nombre']
            );
            $resultado['datos'] = $datos;
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = true;
            $resultado['mensaje'] = "No hay estado";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando estado: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerEticaEstados(){
    try {
        $db = Database::getConnection();
        $sql = "SELECT * FROM eticaestado";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $resultado = array();
        $datos = array();
        foreach ($rows as $r) {
            $row = array (
                'id' => $r['Id'],
                'nombre' => $r['Nombre']
            );
            array_push($datos, $row);
        }
        $resultado['estado'] = true;
        $resultado['mensaje'] = "OK";
        $resultado['datos'] = $datos;
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando estados: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}
}
