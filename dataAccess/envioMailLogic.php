<?php
class envioMailLogic {

    public function obtenerEnvioDisponible()
{
    try {
        $db = Database::getConnection();
        $sql="SELECT enviomail.IdEnvioMail, enviomail.IdNotificacion, enviomail.CantidadEnviar, enviomail.Rango,
            enviomail.CantidadEnviados, enviomail.Pdf
            FROM enviomail
            WHERE enviomail.FechaInicioEnvio <= date(now())
            and enviomail.Estado = 'A'";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $resultado = array();
        if (count($rows) > 0) {
            $row = $rows[0];
            $datos = array(
                    'idEnvioMail' => $row['IdEnvioMail'],
                    'idNotificacion' => $row['IdNotificacion'],
                    'cantidadEnviar' => $row['CantidadEnviar'],
                    'rango' => $row['Rango'],
                    'cantidadEnviados' => $row['CantidadEnviados'],
                    'pdf' => $row['Pdf']
                    );

            $resultado['estado'] = TRUE;
            $resultado['datos'] = $datos;
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No hay envios";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando envios: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function guardarEnvios($idEnvioMail, $cantidadEnviados, $cantidadEnviar)
{
    $hoy = date('Y-m-d');
    $hora = date('H:i:s');
    if ($cantidadEnviados >= $cantidadEnviar){
        $estado = 'E';
    }else{
        $estado = 'A';
    }
    try {
        $db = Database::getConnection();
        $sql = "UPDATE enviomail
            SET enviomail.CantidadEnviados = enviomail.CantidadEnviados + ?,
                enviomail.FechaUltimoEnvio = ?,
                enviomail.HoraUltimoEnvio = ?,
                enviomail.Estado = ?
            WHERE enviomail.IdEnvioMail = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$cantidadEnviados, $hoy, $hora, $estado, $idEnvioMail]);
        $resultado = array();
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL ACTUALIZAR EL ENVIO: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}
}
