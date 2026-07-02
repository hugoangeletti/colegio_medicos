<?php
class colegiadoCertificadoSeguroLogic {

    public function obtenerColegiadoPorDocumento($numeroDocumento) {
        $db = Database::getConnection();
        $sql="SELECT c.Id, c.Matricula, p.Apellido, p.Nombres, cc.CorreoElectronico
                FROM colegiado c
                INNER JOIN persona p ON p.Id = c.IdPersona
                INNER JOIN colegiadocontacto cc ON cc.IdColegiado = c.Id AND cc.IdEstado = 1
                WHERE p.NumeroDocumento = ?";
        $resultado = array();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([$numeroDocumento]);
            $resultado['estado'] = TRUE;
            $row = $stmt->fetch();
            if ($row) {
                $datos = array(
                        'idColegiado'       => $row['Id'],
                        'matricula'         => $row['Matricula'],
                        'apellido'          => $row['Apellido'],
                        'correoElectronico' => $row['CorreoElectronico']
                        );
                $resultado['datos'] = $datos;
                $resultado['mensaje'] = "OK";
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['datos'] = NULL;
                $resultado['mensaje'] = "No hay colegiado ".$numeroDocumento;
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

    public function guardarCertificadoPorColegiado($idColegiado, $numeroDocumento, $periodo, $pathOrigen, $nombreArchivo, $correoElectronico) {
        $db = Database::getConnection();
        $resultado = array();
        $sql="INSERT INTO colegiadocertificadoseguro
            (IdColegiado, NumeroDocumento, Periodo, Path, NombreArchivo, CorreoElectronico)
            VALUES (?, ?, ?, ?, ?, ?)";
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([$idColegiado, $numeroDocumento, $periodo, $pathOrigen, $nombreArchivo, $correoElectronico]);
            $resultado['estado'] = TRUE;
            $resultado['mensaje'] = 'ESTADO ACTUALIZADO';
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } catch (PDOException $e) {
            $resultado['estado'] = FALSE;
            $resultado['mensaje'] = "ERROR AL MODIFICAR ESTADO LOG";
            $resultado['clase'] = 'alert alert-danger';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
        return $resultado;
    }
}
