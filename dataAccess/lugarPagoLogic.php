<?php
class lugarPagoLogic {

//accesos a tabla colegiado
    public function obtenerLugarPagoPorId($id) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "SELECT * FROM lugarpago WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        $resultado['estado'] = TRUE;
        if ($row) {
            $datos = array(
                'id' => $row['Id'],
                'nombre' => $row['Nombre'],
                'codigoCaja' => $row['CodigoCaja'],
                'estado' => $row['Estado'],
                'validarArchivo' => $row['ValidarArchivo']
            );
            $resultado['datos'] = $datos;
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No se encontró Lugar de Pago " . $id;
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando Lugar de Pago: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerLugaresDePago() {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "SELECT Id, Detalle, CodigoCaja
                FROM lugarpago
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
                'codigoCaja' => $row['CodigoCaja']
            );
        }
        $resultado['estado'] = true;
        $resultado['mensaje'] = "OK";
        $resultado['datos'] = $datos;
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando Lugares de Pago: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerAgremiaciones() {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "SELECT Id, Detalle
                FROM lugarpago
                WHERE Estado = 'A' AND CodigoCaja = 'AGRE'
                ORDER BY Detalle";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $datos = array();
        foreach ($rows as $row) {
            $datos[] = array(
                'id' => $row['Id'],
                'nombre' => $row['Detalle']
            );
        }
        $resultado['estado'] = true;
        $resultado['mensaje'] = "OK";
        $resultado['datos'] = $datos;
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando Lugares de Pago: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}
}
