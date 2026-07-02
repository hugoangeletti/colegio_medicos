<?php
class formaPagoLogic {

    function obtenerFormasPago() {
        $resultado = array();
        try {
            $db = Database::getConnection();
            $sql = "SELECT Id, Detalle, Leyenda, OrdenReporte FROM formapago";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            $datos = array();
            foreach ($rows as $row) {
                $datos[] = array(
                    'id' => $row['Id'],
                    'nombre' => $row['Detalle'],
                    'leyenda' => $row['Leyenda'],
                    'ordenReporte' => $row['OrdenReporte']
                );
            }
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "Error buscando Formas de Pago: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-error';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
        return $resultado;
    }

    function obtenerFormaPagoPorId($id) {
        $resultado = array();
        try {
            $db = Database::getConnection();
            $sql = "SELECT Id, Detalle, Leyenda, OrdenReporte FROM formapago WHERE Id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if ($row) {
                $datos = array(
                    'id' => $row['Id'],
                    'nombre' => $row['Detalle'],
                    'leyenda' => $row['Leyenda'],
                    'ordenReporte' => $row['OrdenReporte']
                );
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = false;
                $resultado['mensaje'] = "No se ecntontro la forma de pago";
                $resultado['clase'] = 'alert alert-error';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando forma de pago: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-error';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
        return $resultado;
    }

}
