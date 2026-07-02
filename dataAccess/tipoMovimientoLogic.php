<?php
class tipoMovimientoLogic {

    public function obtenerTipoMovimiento() {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "SELECT * FROM tipomovimiento ORDER BY DetalleCompleto";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $datos = array();
        foreach ($rows as $row) {
            $datos[] = array(
                'id' => $row['Id'],
                'detalle' => $row['Detalle'],
                'detalleCompleto' => $row['DetalleCompleto'],
                'rehabilitable' => $row['Rehabilitable'],
                'paraExterior' => $row['GeneraCtaCte'],
                'estado' => $row['Estado'],
                'mesaEntradas' => $row['MesaEntradas'],
                'temporalidad' => $row['Temporalidad'],
                'motivoInactividad' => $row['MotivoInactividad']
            );
        }
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['datos'] = $datos;
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando Tipo de Movimiento: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }
    return $resultado;
}

    public function obtenerTipoMovimientoPorId($id) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "SELECT * FROM tipomovimiento WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $datos = array(
                'id' => $row['Id'],
                'detalle' => $row['Detalle'],
                'detalleCompleto' => $row['DetalleCompleto'],
                'rehabilitable' => $row['Rehabilitable'],
                'paraExterior' => $row['GeneraCtaCte'],
                'estado' => $row['Estado'],
                'mesaEntradas' => $row['MesaEntradas'],
                'temporalidad' => $row['Temporalidad'],
                'motivoInactividad' => $row['MotivoInactividad']
            );
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No se encontro Tipo Movimiento";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando Tipo Movimiento: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }
    return $resultado;
}
}
