<?php
class entidadLogic {

//accesos a tabla colegiado
    public function obtenerEntidadPorId($idEntidad) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        $sql = "SELECT e.Id, e.IdTipoEntidad, e.Nombre, e.Domicilio, e.IdLocalidad, e.CodigoPostal, e.Telefonos, e.Email, te.Nombre AS NombreTipoEntidad, l.Nombre AS NombreLocalidad
                FROM entidad e
                LEFT JOIN tipoentidad te ON te.Id = e.IdTipoEntidad
                LEFT JOIN localidad l ON l.Id = e.IdLocalidad
                WHERE e.Id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idEntidad]);
        $row = $stmt->fetch();
        $resultado['estado'] = TRUE;
        if ($row) {
            $datos = array(
                'idEntidad' => $row['Id'],
                'idTipoEntidad' => $row['IdTipoEntidad'],
                'nombreEntidad' => $row['Nombre'],
                'domicilio' => $row['Domicilio'],
                'idLocalidad' => $row['IdLocalidad'],
                'codigoPostal' => $row['CodigoPostal'],
                'telefonos' => $row['Telefonos'],
                'mail' => $row['Email'],
                'nombreTipoEntidad' => $row['NombreTipoEntidad'],
                'nombreLocalidad' => $row['NombreLocalidad']
            );
            $resultado['datos'] = $datos;
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No hay localidad " . $idEntidad;
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando entidad: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerEntidadesAutocompletar($idTipoEntidad) {
    $resultado = array();
    try {
        $db = Database::getConnection();
        if (isset($idTipoEntidad)) {
            $filtro = " WHERE e.idTipoEntidad = " . $idTipoEntidad;
        } else {
            $filtro = "";
        }
        $sql = "SELECT e.Id, e.Nombre, l.Nombre AS NombreLocalidad
                FROM entidad e
                LEFT JOIN localidad l ON l.Id = e.IdLocalidad" . $filtro . "
                ORDER BY e.Nombre";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $datos = array();
        foreach ($rows as $row) {
            $nombre = trim($row['Nombre']);
            if (isset($row['NombreLocalidad']) && $row['NombreLocalidad'] <> "") {
                $nombre .= ' (' . trim($row['NombreLocalidad']) . ')';
            }
            $datos[] = array(
                'id' => $row['Id'],
                'nombre' => $nombre
            );
        }
        $resultado['estado'] = true;
        $resultado['mensaje'] = "OK";
        $resultado['datos'] = $datos;
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando entidades: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}
}
