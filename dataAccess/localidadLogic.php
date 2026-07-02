<?php
class localidadLogic {

//accesos a tabla colegiado
    public function obtenerLocalidadPorId($idLocalidad) {
    try {
        $db = Database::getConnection();
        $sql="SELECT localidad.Nombre, localidad.CodigoPostal, localidad.idZona, zonas.Nombre AS NombreZona
                FROM localidad
                LEFT JOIN zonas ON(zonas.Id = localidad.IdZona)
                WHERE localidad.Id = ?
                ORDER BY localidad.Nombre";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idLocalidad]);
        $row = $stmt->fetch();

        $resultado = array();
        $resultado['estado'] = TRUE;
        if ($row) {
            $datos = array(
                    'nombreLocalidad' => $row['Nombre'],
                    'codigoPostal' => $row['CodigoPostal'],
                    'idZona' => $row['idZona'],
                    'nombreZona' => $row['NombreZona']
                    );

            $resultado['datos'] = $datos;
            $resultado['mensaje'] = "OK";
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['datos'] = NULL;
            $resultado['mensaje'] = "No hay localidad ".$idLocalidad;
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando localidad";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerLocalidadBuscar($idLocalidad) {
    try {
        $db = Database::getConnection();
        $sql="SELECT localidad.Nombre, localidad.CodigoPostal
                FROM localidad
                LEFT JOIN zonas ON(zonas.Id = localidad.IdZona)
                WHERE localidad.Id = ?
                ORDER BY localidad.Nombre";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idLocalidad]);
        $row = $stmt->fetch();

        $resultado = array();
        if ($row) {
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['colegiadoBuscar'] = $row['Nombre'];
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = true;
            $resultado['mensaje'] = "No hay localidad ".$idLocalidad;
            $resultado['clase'] = 'alert alert-info';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando localidad";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerLocalidadesAutocompletar(){
    try {
        $db = Database::getConnection();
        $sql = "SELECT localidad.Id, localidad.Nombre, localidad.CodigoPostal
                FROM localidad
                ORDER BY localidad.Nombre";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $resultado = array();
        $datos = array();
        foreach ($rows as $row) {
            $r = array(
                'id' => $row['Id'],
                'nombre' => $row['Nombre']
            );
            array_push($datos, $r);
        }
        $resultado['estado'] = true;
        $resultado['mensaje'] = "OK";
        $resultado['datos'] = $datos;
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando localidades";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerLocalidadesPorZona($idZona){
    try {
        $db = Database::getConnection();
        $sql = "SELECT l.Id, l.Nombre, l.CodigoPostal
                FROM localidad l
                WHERE l.idZona = ?
                ORDER BY l.CodigoPostal, l.Nombre";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idZona]);
        $rows = $stmt->fetchAll();

        $resultado = array();
        $datos = array();
        foreach ($rows as $row) {
            $r = array(
                'id' => $row['Id'],
                'nombre' => trim($row['CodigoPostal']).' - '.trim($row['Nombre']),
                'codigoPostal' => $row['CodigoPostal']
            );
            array_push($datos, $r);
        }
        $resultado['estado'] = true;
        $resultado['mensaje'] = "OK";
        $resultado['datos'] = $datos;
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando localidades";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}

    public function obtenerLocalidadesConIdPorZona($idZona){
    try {
        $db = Database::getConnection();
        $sql = "SELECT l.Id, l.Nombre
                FROM localidad l
                WHERE l.idZona = ?
                ORDER BY l.Nombre";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idZona]);
        $rows = $stmt->fetchAll();

        $resultado = array();
        $datos = array();
        foreach ($rows as $row) {
            $r = array(
                'id' => $row['Id'],
                'nombre' => trim($row['Nombre'])
            );
            array_push($datos, $r);
        }
        $resultado['estado'] = true;
        $resultado['mensaje'] = "OK";
        $resultado['datos'] = $datos;
        $resultado['clase'] = 'alert alert-success';
        $resultado['icono'] = 'glyphicon glyphicon-ok';
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando localidades";
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }
    return $resultado;
}
}
