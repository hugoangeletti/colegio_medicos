<?php
class colegiadoContactoLogic {

    public function obtenerColegiadoContactoPorIdColegiado($idColegiado) {
    try {
        $db = Database::getConnection();
        $sql="SELECT cc.IdColegiadoContacto, cc.TelefonoFijo, cc.TelefonoMovil, cc.CorreoElectronico, cc.FechaCarga, od.Nombre, cmr.Id
            FROM colegiadocontacto cc
            INNER JOIN origendomicilio od ON(od.idOrigenDomicilio = cc.IdOrigen)
            LEFT JOIN colegiadomailrechazado cmr ON cmr.IdColegiado = cc.IdColegiado
            WHERE cc.IdColegiado = ? and cc.IdEstado = 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);
        $row = $stmt->fetch();

        $resultado = array();
        $resultado['estado'] = TRUE;
        if ($row) {
            $correoElectronico = $row['CorreoElectronico'];
            $idMailRechazado = $row['Id'];
            if (!isset($correoElectronico) || strtoupper($correoElectronico) == 'NR' || isset($idMailRechazado)) {
                $noEnviaMail = TRUE;
            } else {
                $noEnviaMail = FALSE;
            }

            $datos = array(
                    'idColegiadoContacto' => $row['IdColegiadoContacto'],
                    'telefonoFijo' => $row['TelefonoFijo'],
                    'telefonoMovil' => $row['TelefonoMovil'],
                    'email' => $correoElectronico,
                    'fechaCarga' => $row['FechaCarga'],
                    'origen' => $row['Nombre'],
                    'idMailRechazado' => $idMailRechazado,
                    'noEnviaMail' => $noEnviaMail
                    );

            $resultado['datos'] = $datos;
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No hay colegiado ".$idColegiado;
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando colegiado";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function agregarColegiadoContacto($idColegiado, $telefonoFijo, $telefonoMovil, $mail){
    $resultado = array();
    try {
        $db = Database::getConnection();
        $db->beginTransaction();

        //marco como anulado el domicilio actualmente activo y luego doy de alta el nuevo domicilio
        $sql="UPDATE colegiadocontacto
            SET IdEstado = 2
            WHERE IdColegiado = ? AND IdEstado = 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado]);

        $sql="INSERT INTO colegiadocontacto
            (IdColegiado, TelefonoFijo, TelefonoMovil, CorreoElectronico, IdEstado, FechaCarga, IdUsuario, IdOrigen)
            VALUE (?, ?, ?, ?, 1, date(now()), ?, 2)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idColegiado, $telefonoFijo, $telefonoMovil, $mail, $_SESSION['user_id']]);
        $idColegiadoContacto = $db->lastInsertId();
        $colegiadoLogic = new colegiadoLogic();
        $correRechazado = $colegiadoLogic->tieneCorreoRechazado($idColegiado);
        if ($correRechazado){
            $sql="DELETE FROM colegiadomailrechazado
                WHERE IdColegiado = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idColegiado]);
        }

        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';

        $resultado['mensaje'] .= '('.$idColegiadoContacto.')';
        $db->commit();
        return $resultado;
    } catch (PDOException $e) {
        $db->rollBack();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL ACTUALIZAR CONTACTO";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
        return $resultado;
    }
}

    public function modificarMail($idColegiado, $mail){
    $resultado = array();
    try {
        $db = Database::getConnection();
        $db->beginTransaction();

        $sql="UPDATE colegiadocontacto
            SET CorreoElectronico = ?
            WHERE IdColegiado = ? AND IdEstado = 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([$mail, $idColegiado]);
        $idColegiadoContacto = $db->lastInsertId();
        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "OK";
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';

        $resultado['mensaje'] .= '('.$idColegiadoContacto.')';
        $db->commit();
        return $resultado;
    } catch (PDOException $e) {
        $db->rollBack();
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "ERROR AL ACTUALIZAR MAIL.";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
        return $resultado;
    }
}
}
