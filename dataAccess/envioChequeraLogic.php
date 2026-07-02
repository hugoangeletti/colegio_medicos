<?php
class envioChequeraLogic {

    public function crearEnvioMail(){
    try {
        $db = Database::getConnection();
        $sql="INSERT INTO eticaexpediente (IdColegiado, NumeroExpediente, Caratula, Observaciones, IdUsuario, Fecha, IdSumarianteTitular, IdSumarianteSuplente, Estado, IdSecretarioadhoc)
            VALUES (?, ?, ?, ?, ?, now(), ?, ?, ?, ?)";

        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado, $nroExpediente, $caratula, $observaciones, $_SESSION['user_id'], $idSumarianteTitular, $idSumarianteSuplente, $estado, $idSecretarioadhoc]);
        $idEticaExpediente = $db->lastInsertId();

        $sql="INSERT INTO eticaexpedientemovimiento
            (IdEticaExpediente, IdEticaEstado, Fecha, IdUsuario)
            VALUES (?, 1, now(), ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idEticaExpediente, $_SESSION['user_id']]);

        $estadoConsulta = TRUE;
        $mensaje = 'Expediente HA SIDO AGREGADO';
    } catch (PDOException $e) {
        $estadoConsulta = FALSE;
        $mensaje = 'ERROR AL AGREGAR Expediente: ' . $e->getMessage();
    }
    $result = array();
    $result['estado'] = $estadoConsulta;
    $result['mensaje'] = $mensaje;
    return $result;
}
}
