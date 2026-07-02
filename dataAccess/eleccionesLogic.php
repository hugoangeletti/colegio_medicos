<?php
class elecciones {

    function obtenerEleccionesPorId($id) {
        try {
            $db = Database::getConnection();
            $sql="SELECT IdElecciones, Detalle, Estado, Anio FROM elecciones WHERE IdElecciones = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$id]);
            $row = $stmt->fetch();

            $resultado = array();
            if ($row) {
                $datos = array(
                            'idElecciones' => $row['IdElecciones'],
                            'detalle' => $row['Detalle'],
                            'estado' => $row['Estado'],
                            'anio' => $row['Anio']
                        );
                $resultado['estado'] = true;
                $resultado['mensaje'] = "OK";
                $resultado['datos'] = $datos;
                $resultado['clase'] = 'alert alert-success';
                $resultado['icono'] = 'glyphicon glyphicon-ok';
            } else {
                $resultado['estado'] = false;
                $resultado['mensaje'] = "No hay elecciones";
                $resultado['clase'] = 'alert alert-info';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando sumariante: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-error';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }

        return $resultado;
    }

    function obtenerEleccionesPorEstado($estado){
        try {
            $db = Database::getConnection();
            if (isset($estado)) {
                $conEstado = "WHERE Estado = ?";
                $params = [$estado];
            } else {
                $conEstado = "";
                $params = [];
            }

            $sql = "SELECT IdElecciones, Detalle, Estado, Anio FROM elecciones ".$conEstado." ORDER BY Anio DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();

            $resultado = array();
            if (count($rows) > 0) {
                $datos = array();
                foreach ($rows as $row) {
                    $item = array (
                        'idElecciones' => $row['IdElecciones'],
                        'detalle' => $row['Detalle'],
                        'estado' => $row['Estado'],
                        'anio' => $row['Anio']
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
                $resultado['datos'] = NULL;
                $resultado['mensaje'] = "No hay elecciones";
                $resultado['clase'] = 'alert alert-warning';
                $resultado['icono'] = 'glyphicon glyphicon-exclamation-sign';
            }
        } catch (PDOException $e) {
            $resultado['estado'] = false;
            $resultado['mensaje'] = "Error buscando sumariantes: " . $e->getMessage();
            $resultado['clase'] = 'alert alert-error';
            $resultado['icono'] = 'glyphicon glyphicon-remove';
        }
        return $resultado;
    }

    function agregarElecciones($detalle, $anio) {
        try {
            $db = Database::getConnection();
            $sql="INSERT INTO elecciones (Detalle, Estado, Anio)
                VALUES (?, 'A',?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$detalle, $anio]);
            $estadoConsulta = TRUE;
            $mensaje = 'Elecciones HA SIDO AGREGADO';
        } catch (PDOException $e) {
            $estadoConsulta = FALSE;
            $mensaje = 'ERROR AL AGREGAR Elecciones: ' . $e->getMessage();
        }
        $result = array();
        $result['estado'] = $estadoConsulta;
        $result['mensaje'] = $mensaje;
        return $result;
    }

    function editarElecciones($idElecciones, $detalle, $estado, $anio) {
        try {
            $db = Database::getConnection();
            $sql="UPDATE elecciones
                    SET Detalle = ?, Estado = ?, Anio = ?
                    WHERE IdElecciones = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$detalle, $estado, $anio, $idElecciones]);
            $estadoConsulta = TRUE;
            $mensaje = 'Elecciones HA SIDO MODIFICADO';
        } catch (PDOException $e) {
            $estadoConsulta = FALSE;
            $mensaje = 'ERROR AL MODIFICAR Elecciones: ' . $e->getMessage();
        }
        $result = array();
        $result['estado'] = $estadoConsulta;
        $result['mensaje'] = $mensaje;
        return $result;
    }

    function borrarElecciones($idElecciones){
        try {
            $db = Database::getConnection();
            $sql="UPDATE elecciones SET
                        Estado = 'B'
                        WHERE IdElecciones = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idElecciones]);
            $estadoConsulta = TRUE;
            $mensaje = 'Elecciones HA SIDO BORRADO';
        } catch (PDOException $e) {
            $estadoConsulta = FALSE;
            $mensaje = 'ERROR AL BORRAR Elecciones: ' . $e->getMessage();
        }
        $result = array();
        $result['estado'] = $estadoConsulta;
        $result['mensaje'] = $mensaje;
        return $result;
    }

    function eleccionesActiva(){
        try {
            $db = Database::getConnection();
            $sql="SELECT MAX(IdElecciones) AS MaxId
                FROM elecciones
                WHERE Estado = 'A'";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch();
            if ($row) {
                return $row['MaxId'];
            } else {
                return NULL;
            }
        } catch (PDOException $e) {
            return NULL;
        }
    }

}
