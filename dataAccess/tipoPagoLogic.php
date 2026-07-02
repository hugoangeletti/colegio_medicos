<?php
class tipoPagoLogic {

    public function obtenerTiposPago() {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "SELECT * FROM tipopago WHERE Estado = 'A' ORDER BY Detalle";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $datos = array();
        foreach ($rows as $row) {
            $datos[] = array(
                'id' => $row['Id'],
                'nombre' => $row['Detalle'],
                'importe' => $row['Importe'],
                'cuentaContable' => $row['CuentaContable'],
                'codigoConcepto' => $row['CodigoConcepto'],
                'idConcepto' => $row['IdConcepto'],
                'estado' => $row['Estado'],
                'cantidadHoras' => $row['CantidadHoras']
            );
        }
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['datos'] = $datos;
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando Bancos: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }
    return $resultado;
}

    public function obtenerTipoValorPorId($idTipoPago) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "SELECT * FROM tipopago WHERE Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idTipoPago]);
        $row = $stmt->fetch();
        if ($row) {
            $datos = array(
                'id' => $row['Id'],
                'nombre' => $row['Detalle'],
                'importe' => $row['Importe'],
                'cuentaContable' => $row['CuentaContable'],
                'codigoConcepto' => $row['CodigoConcepto'],
                'idConcepto' => $row['IdConcepto'],
                'estado' => $row['Estado'],
                'cantidadHoras' => $row['CantidadHoras']
            );
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No se ecntontro el tipo de pago";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando tipo de pago: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }
    return $resultado;
}

    public function ontenerTiposPagoParaRecibo() {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "SELECT tp.Id, tp.Detalle, tp.Importe
                FROM tipopago tp
                WHERE tp.IdConcepto IN(7, 8, 9, 11) AND tp.Estado = 'A';";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $datos = array();
        foreach ($rows as $row) {
            $datos[] = array(
                'id' => $row['Id'],
                'nombre' => $row['Detalle'],
                'importe' => $row['Importe']
            );
        }
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['datos'] = $datos;
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "Error buscando Bancos: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
    }
    return $resultado;
}
}
