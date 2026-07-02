<?php
class notificacionNotaLogic {

    public function obtenerNotificacionNota($idNotificacionNota)
{
    try {
        $db = Database::getConnection();
        $sql = "SELECT * FROM notificacionnota WHERE IdNotificacionNota = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idNotificacionNota]);
        $row = $stmt->fetch();
        if ($row === false) {
            return "0";
        } else {
            return $row;
        }
    } catch (PDOException $e) {
        return "-1";
    }
}

    public function obtenerNotificacionNotaPorIdNotificacion($idNotificacion)
{
    try {
        $db = Database::getConnection();
        $sql="SELECT * FROM notificacionnota WHERE IdNotificacionNota = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idNotificacion]);
        $row = $stmt->fetch();
        if ($row) {
            $resultado['estado'] = TRUE;
            $resultado['datos'] = array(
                        'idNotificacionNota' => $row['IdNotificacionNota'],
                        'tema' => $row['Texto'],
                        'estado' => $row['Estado'],
                        'texto' => $row['Texto'],
                        'from' => $row['From'],
                        'subject' => $row['Subject']
                    );
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No hay NOTA";
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando NOTA";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function guardarEnvioColegiado($idNotificacionColegiado)
{
    try {
        $db = Database::getConnection();
        $sql = "UPDATE notificacioncolegiado
        SET Estado = 'V', FechaEnvio = date(now()), HoraEnvio = time(now())
        WHERE IdNotificacionColegiado = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idNotificacionColegiado]);
        return "0";
    } catch (PDOException $e) {
        return "-1";
    }
}
}
