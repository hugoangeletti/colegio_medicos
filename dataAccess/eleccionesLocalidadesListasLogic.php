<?php
class eleccionesLocalidadesListasLogic {

    public function obtenerListasPorIdEleccionesLocalidad($idEleccionesLocalidad) {
    try {
        $db = Database::getConnection();
        $sql="SELECT eleccioneslocalidadlista.IdELLista, eleccioneslocalidadlista.Nombre,
                eleccioneslocalidadlista.TipoLista,
                COUNT(eleccioneslocalidadlistaintegrantes.IdELListaIntegrante) AS Integrantes
            FROM eleccioneslocalidadlista
            LEFT JOIN eleccioneslocalidadlistaintegrantes ON(eleccioneslocalidadlistaintegrantes.IdELLista = eleccioneslocalidadlista.IdELLista)
            WHERE IdEleccionesLocalidad = ?
            GROUP BY eleccioneslocalidadlista.IdELLista, eleccioneslocalidadlista.Nombre, eleccioneslocalidadlista.TipoLista";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idEleccionesLocalidad]);
        $rows = $stmt->fetchAll();

        $resultado = array();
        if (count($rows) > 0) {
            $datos = array();
            foreach ($rows as $row) {
                $item = array (
                        'idEleccionesLocalidadLista' => $row['IdELLista'],
                        'nombre' => $row['Nombre'],
                        'tipoLista' => $row['TipoLista'],
                        'cantIntegrantes' => $row['Integrantes']
                    );
                array_push($datos, $item);
            }
            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No hay listas";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando listas de la localidad: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function obtenerEleccionesLocalidadListaPorId($idEleccionesLocalidadLista) {
    try {
        $db = Database::getConnection();
        $sql="SELECT IdELLista, IdEleccionesLocalidad, Nombre, TipoLista FROM eleccioneslocalidadlista WHERE IdELLista = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idEleccionesLocalidadLista]);
        $row = $stmt->fetch();

        $resultado = array();
        if ($row) {
            $datos = array (
                        'idEleccionesLocalidadLista' => $row['IdELLista'],
                        'idEleccionesLocalidad' => $row['IdEleccionesLocalidad'],
                        'nombre' => $row['Nombre'],
                        'tipoLista' => $row['TipoLista']
                    );

            $resultado['estado'] = true;
            $resultado['mensaje'] = "OK";
            $resultado['datos'] = $datos;
            $resultado['clase'] = 'alert alert-success';
            $resultado['icono'] = 'glyphicon glyphicon-ok';
        } else {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "No hay listas";
            $resultado['clase'] = 'alert alert-warning';
            $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
        }
    } catch (PDOException $e) {
        $resultado['estado'] = false;
        $resultado['mensaje'] = "Error buscando listas de la localidad: " . $e->getMessage();
        $resultado['clase'] = 'alert alert-danger';
        $resultado['icono'] = 'glyphicon glyphicon-remove';
    }

    return $resultado;
}

    public function agregarEleccionesLocalidadesLista($idEleccionesLocalidad, $nombre, $tipoLista) {
    try {
        $db = Database::getConnection();
        $sql="INSERT INTO eleccioneslocalidadlista (IdEleccionesLocalidad, Nombre, TipoLista)
            VALUES (?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idEleccionesLocalidad, $nombre, $tipoLista]);
        $estadoConsulta = TRUE;
        $mensaje = 'Lista HA SIDO AGREGADO';
    } catch (PDOException $e) {
        $estadoConsulta = FALSE;
        $mensaje = 'ERROR AL AGREGAR Lista: ' . $e->getMessage();
    }
    $result = array();
    $result['estado'] = $estadoConsulta;
    $result['mensaje'] = $mensaje;
    return $result;
}

    public function editarEleccionesLocalidadesLista($idEleccionesLocalidadLista, $nombre, $tipoLista) {
    try {
        $db = Database::getConnection();
        $sql="UPDATE eleccioneslocalidadlista
                SET Nombre = ?, TipoLista = ?
                WHERE IdELLista = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$nombre, $tipoLista, $idEleccionesLocalidadLista]);
        $estadoConsulta = TRUE;
        $mensaje = 'Lista HA SIDO MODIFICADO';
    } catch (PDOException $e) {
        $estadoConsulta = FALSE;
        $mensaje = 'ERROR AL MODIFICAR Lista: ' . $e->getMessage();
    }
    $result = array();
    $result['estado'] = $estadoConsulta;
    $result['mensaje'] = $mensaje;
    return $result;
}

    public function borrarEleccionesLocalidadesLista($idEleccionesLocalidadLista){
    try {
        $db = Database::getConnection();
        $sql="DELETE FROM eleccioneslocalidadlista WHERE IdELLista = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idEleccionesLocalidadLista]);
        $estadoConsulta = TRUE;
        $mensaje = 'Lista HA SIDO BORRADO';
    } catch (PDOException $e) {
        $estadoConsulta = FALSE;
        $mensaje = 'ERROR AL BORRAR Lista: ' . $e->getMessage();
    }
    $result = array();
    $result['estado'] = $estadoConsulta;
    $result['mensaje'] = $mensaje;
    return $result;
}
}
